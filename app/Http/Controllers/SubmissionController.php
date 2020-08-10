<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Mail;
use App\Services\AdminServices;
use App\Services\AuthorServices;
use App\Services\CommunicationTypeService;
use App\Services\CongressServices;
use App\Services\EstablishmentServices;
use App\Services\MailServices;
use App\Services\ServiceServices;
use App\Services\SubmissionServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use App\Services\Utils;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class SubmissionController extends Controller
{   protected $type = 'submission';
    protected $submissionServices;
    protected $authorServices;
    protected $adminServices;
    protected $userServices;
    protected $congressServices;
    protected $establishmentServices;
    protected $serviceServices;
    protected $communicationTypeService;
    protected $mailServices;

    function __construct(
        SubmissionServices $submissionServices,
        AuthorServices $authorServices,
        AdminServices $adminServices,
        UserServices $userServices,
        ServiceServices $serviceServices,
        EstablishmentServices $establishmentServices,
        CongressServices $congressServices,
        MailServices $mailServices,
        CommunicationTypeService $communicationTypeService
    )
    {
        $this->submissionServices = $submissionServices;
        $this->authorServices = $authorServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->establishmentServices = $establishmentServices;
        $this->serviceServices = $serviceServices;
        $this->mailServices = $mailServices;
        $this->communicationTypeService = $communicationTypeService;
    }

    public function addSubmission(Request $request)
    {

        if (!($request->has('submission.title') && $request->has('submission.type') && $request->has('submission.communication_type_id')
            && $request->has('submission.description') && $request->has('submission.congress_id') && $request->has('submission.theme_id')
            && $request->has('authors'))) {
            return response()->json(['response' => 'bad request'], 400);
        }

        try {
            $configSubmission = $this->congressServices->getConfigSubmission(
                $request->input('submission.congress_id'));
            if ($configSubmission->end_submission_date < date('Y-m-d H:i:s')) {
                return response()->json('deadline has been passed', 400);
            }
            $user = $this->userServices->retrieveUserFromToken();
            $submission = $this->submissionServices->addSubmission(
                $request->input('submission.title'),
                $request->input('submission.type'),
                $request->input('submission.communication_type_id'),
                $request->input('submission.description'),
                $request->input('submission.congress_id'),
                $request->input('submission.theme_id'),
                $user->user_id
            );
            $etablissements = $this->establishmentServices->addMultipleEstablishmentsFromAuthors($request->input('authors'));
            $services = $this->serviceServices->addMultipleServicesFromAuthors($request->input('authors'));
            $this->authorServices->saveAuthorsBySubmission(
                $request->input('authors'),
                $submission->submission_id,
                $etablissements,
                $services

            );

            $admins = $this->adminServices->getEvaluatorsByThemeOrByCongress($submission->theme_id, $submission->congress_id, 11);

            $this->submissionServices->affectSubmissionToEvaluators(
                $this->congressServices->getConfigSubmission($submission->congress_id),
                $submission->submission_id,
                $admins
            );

            $this->submissionServices->saveResourceSubmission($request->input('resourceIds'), $submission->submission_id);

            $congress = $this->congressServices->getCongressById($submission->congress_id);

            $mailtype = $this->congressServices->getMailType('save_submission',$this->type);
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

            if ($mail) {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                }

                $this->userServices->sendMail(
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, null, $userMail
                );
            }
            return response()->json(['response' => 'Enregistrement avec succes'], 200);
        } catch (Exception $e) {
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function deleteSubmission($submission_id)
    {
        if (!$submission = $this->submissionServices->getSubmissionById($submission_id)) {
            return response()->json(['response' => 'no submission found'], 400);
            $submission->delete();
            return response()->json(['response' => 'submssion deleted successfully']);
        }
    }

    public function editSubmssion(Request $request, $submission_id)
    {
        try {
            if (!($submission = $this->submissionServices->getSubmissionById($submission_id))) {
                return response()->json(['response' => 'no submission found'], 400);
            }
            if ($submission->status != 0 && !$request->has('addExternalFiles')) {
                return response()->json('you don\'t have the right to modify the submission', 404);
            }
            $changedTheme = $submission->theme_id == $request->input('submission.theme_id') ? false : true;
            $user = $this->userServices->retrieveUserFromToken();
            $status =  $request->input('addExternalFiles') ? '5' : $submission->status ;
            $name =    $request->input('addExternalFiles') ? 'file_submitted' : 'edit_submission';
            
            $submission = $this->submissionServices->editSubmission(
                $submission,
                $request->input('submission.title'),
                $request->input('submission.type'),
                $status,
                $request->input('submission.communication_type_id'),
                $request->input('submission.description'),
                $request->input('submission.theme_id')
            );
            $etablissements = $this->establishmentServices->addMultipleEstablishmentsFromAuthors($request->input('authors'));
            $services = $this->serviceServices->addMultipleServicesFromAuthors($request->input('authors'));
            $existingAuthors = $this->authorServices->getAuthorsBySubmissionId($submission->submission_id);
            $this->authorServices->editAuthors(
                $existingAuthors,
                $request->input('authors'),
                $submission->submission_id,
                $services,
                $etablissements
            );
            if ($changedTheme) { // le cas ou  il n'ya pas eu d'évaluation et le utilisateur veut changer le theme de sa soumission
                $evaluators = $this->adminServices->getEvaluatorsBySubmissionId($submission_id);
                foreach ($evaluators as $evaluator) {
                    $evaluator->delete();
                }
                $admins = $this->adminServices->getEvaluatorsByThemeOrByCongress($submission->theme_id, $submission->congress_id, 11);

                $this->submissionServices->affectSubmissionToEvaluators(
                    $this->congressServices->getConfigSubmission($submission->congress_id),
                    $submission->submission_id,
                    $admins
                );

            }
            if ($submission->limit_date > date('Y-m-d H:i:s') && $submission->status == 5) {
                $this->submissionServices->saveResourceSubmission($request->input('resourceIds'), $submission->submission_id);
            }
            $congress=$this->congressServices->getCongressById($submission->congress_id);
            $
            $mailtype = $this->congressServices->getMailType($name,$this->type);
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

            if ($mail) {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                }

                $this->userServices->sendMail(
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, null, $userMail
                );
            }
            return response()->json(['response' => 'modification avec success'], 200);
        } catch (Exception $e) {
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function getSubmission($submission_id)
    {

        /* TODO Send Mail When params = evalutor */
        /*$mailtype = $this->congressServices->getMailType('eval_progress_submission');
        $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

        if ($mail)
        {
            $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
            if (!$userMail) {
                $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
            }

            $this->userServices->sendMail(
                $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, null, $userMail
            );
        }*/
        return $this->submissionServices->getSubmission($submission_id);
    }

    public function getCongressSubmission(Request $request, $congressId)
    {
        $status = $request->query('status');
        if (!($congress = $this->congressServices->getCongressById($congressId))) {
            return response()->json(['response' => 'bad request'], 400);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin))) {
                return response()->json(['response' => 'bad request'], 400);
            }
            $privilege_id = $adminCongress->privilege_id;
            $submissions = $this->submissionServices->getCongressSubmissionForAdmin($admin, $congressId, $privilege_id, $status);

            return response()->json($submissions, 200);


        } catch (Exception $e) {
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function getCongressSubmissionDetailById($submissionId)
    {

        if (!($submission = $this->submissionServices->getSubmissionById($submissionId))) {
            return response()->json(['response' => 'bad request'], 400);
        }
        try {
            $congressId = $submission->congress_id;
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin))) {
                return response()->json(['response' => 'bad request'], 400);
            }
            $privilege_id = $adminCongress->privilege_id;
            $submission_detail = $this->submissionServices->getSubmissionDetailById($admin, $submissionId, $privilege_id);
            $user = $submission_detail['user'];
            if ($privilege_id == 11) {
            $mail_type = $this->congressServices->getMailType('blocage',$this->type);
            $mail = $this->congressServices->getMail($congressId,$mail_type->mail_type_id);
            if ($mail)
            {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->userServices->sendMail(
                        $this->congressServices->renderMail($mail->template, null, $user, null, null, null), $user, null, $mail->object, null, $userMail
                    );
                }
            }
            return response()->json($submission_detail, 200);

        }} catch (Exception $e) {
            return response()->json(['response' => $e->getMessage()], 400);
        }
    
}

    public function changeSubmissionStatus($submission_id, $congress_id, Request $request)
    {

        if (!($submission = $this->submissionServices->getSubmissionById($submission_id))) {
            return response()->json(['response' => 'bad request'], 400);
        }
        $user = $this->userServices->getUserById($submission->user_id);
        $submission->status = $request->input('status');
        $submission->update();
        $mail_type = $request->input('status') == 1 ? 
            $this->congressServices->getMailType('acceptation',$this->type) :
            $this->congressServices->getMailType('refus',$this->type);
        $mail = $this->congressServices->getMail($congress_id,$mail_type->mail_type_id);
        if ($mail)
        {
            $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
            if (!$userMail) {
                $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                $this->userServices->sendMail(
                    $this->congressServices->renderMail($mail->template, null, $user, null, null, null), $user, null, $mail->object, null, $userMail
                );
            }
        }

        return response()->json(['response' => 'Submission status changed'], 201);
    }

    public function finalDecisionOnSubmission(Request $request, $submission_id)
    {
        //get submission by id
        if (!$submission = $this->submissionServices->getSubmissionById($submission_id)) {
            return response()->json(['no submission found'], 404);
        }
          // update status,type_id,limit_date
        $submission->status = $request->input('status');
        $submission->communication_type_id = $request->input('communication_type_id');
        $submission->limit_date = $request->input('limit_date');

        // generate code 

        if ($request->has('communication_type_id') && $request->input('status') == '1' ) { // only if selected
            $index = -1;
            $submissions = $this->submissionServices->getSubmissionsByCongressId($submission->congress_id);
            foreach ($submissions as $key => $value) {
                if ($value->submission_id == $submission_id) {
                    $index = $key + 1;
                break;
                }
    
            }
            $type = $this->communicationTypeService->getCommunicationTypeById($request->input('communication_type_id'));
            $code = Utils::generateSubmissionCode($type->abrv,$index);
            $submission->code = $code ;
        }

        $submission->update();
        //send email
            $areFiles = $request->has('areFiles') ? 1 : 0 ;
            $mailName = $request->input('status') == 3  ? 'Refus' : 
            ($request->input('status') == 4 ? 'Attente_de_fichier' : 'Acceptation') ;
            $mailtype = $this->congressServices->getMailType($mailName,$this->type);
            $mail = $this->congressServices->getMail($submission->congress_id, $mailtype->mail_type_id);

            if ($mail)
            {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $submission->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $submission->user_id);
                }
                $link = '';
                if ($areFiles && ($request->input('status')!= 3 )) {
                    $link = UrlUtils::getBaseUrlFrontOffice() 
                    .'/user-profile/submission/submit-resources/'.$submission->submission_id ;
                }
                $user = $this->userServices->getUserById($submission->user_id);
                $this->userServices->sendMail(
                    $this->congressServices->renderMail(
                        $mail->template,
                        null,
                        null,
                        $link,
                        null,
                        null
                    ),
                    $user, 
                    null,
                    $mail->object,
                     null, 
                     $userMail
                );
            }
            $link = '';
            if ($areFiles && ($request->input('status') !== 3)) {
                $link = UrlUtils::getBaseUrlFrontOffice()
                    . '/user-profile/submission/submit-resources/' . $submission->submission_id;
            }
            $user = $this->userServices->getUserById($submission->user_id);
            $this->userServices->sendMail(
                $this->congressServices->renderMail(
                    $mail->template,
                    null,
                    null,
                    $link,
                    null,
                    null
                ),
                $user,
                null,
                $mail->object,
                null,
                $userMail
            );
        
        return response()->json(['final decision made successfully'], 200);

    }

    public function putEvaluationToSubmission($submissionId, Request $request)
    {
        $note = $request->input('note', -1);
        if (!($submission = $this->submissionServices->getSubmissionById($submissionId)) || $note < 0 || $note > 20) {
            return response()->json(['response' => 'bad request'], 400);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($evaluation = $this->submissionServices->getSubmissionEvaluationByAdminId($admin, $submissionId))) {
                return response()->json(['response' => 'bad request'], 400);
            }
            $evaluation = $this->submissionServices->getSubmissionEvaluationByAdminId($admin, $submissionId);
            $evaluation->communication_type_id = $request->input('communication_type_id');
            $evaluation = $this->submissionServices->putEvaluationToSubmission($admin, $submissionId, $note, $evaluation);
            return response()->json($evaluation, 200);
        } catch (Exception $e) {
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function getSubmissionByUserId(Request $request)
    {
        $offset = $request->query('offset', 0);
        $perPage = $request->query('perPage', 6);
        $perCongressId = $request->query('congress_id');
        $perStatus = $request->query('status');
        $search = $request->query('search', '');
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'], 401);
        }
        $submissions = $this->submissionServices->getSubmissionsByUserId(
            $user,
            $offset,
            $perPage,
            $search,
            $perCongressId,
            $perStatus
        );
        return response()->json($submissions, 200);
    }

}
