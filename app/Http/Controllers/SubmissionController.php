<?php

namespace App\Http\Controllers;
use App\Models\AdminCongress;
use App\Models\Author;
use App\Models\Mail;
use App\Services\Utils;
use App\Services\AdminServices;
use App\Services\AuthorServices;
use App\Services\CongressServices;
use App\Services\EstablishmentServices;
use App\Services\MailServices;
use App\Services\ServiceServices;
use App\Services\SubmissionServices;
use App\Services\UserServices;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SharedServices;

class SubmissionController extends Controller
{
    protected $submissionServices;
    protected $authorServices;
    protected $adminServices;
    protected $userServices;
    protected $congressServices;
    protected $establishmentServices;
    protected $serviceServices;
    protected $mailServices;
    protected $sharedServices;


    function __construct(
        SubmissionServices $submissionServices,
        AuthorServices $authorServices,
        AdminServices $adminServices,
        UserServices $userServices,
        ServiceServices $serviceServices,
        EstablishmentServices $establishmentServices,
        CongressServices $congressServices,
        MailServices $mailServices,
        SharedServices $sharedServices
    )
    {
        $this->submissionServices = $submissionServices;
        $this->authorServices = $authorServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->establishmentServices = $establishmentServices ;
        $this->serviceServices = $serviceServices ;
        $this->mailServices = $mailServices;
        $this->sharedServices = $sharedServices;



    }

    public function addSubmission(Request $request)
    {

        if (!($request->has('submission.title') && $request->has('submission.type') && $request->has('submission.prez_type')
            && $request->has('submission.description') && $request->has('submission.congress_id') && $request->has('submission.theme_id')
            && $request->has('authors'))) {
            return response()->json(['response' => 'bad request'], 400);
        }

        try {
            $user = $this->userServices->retrieveUserFromToken();
            $submission = $this->submissionServices->addSubmission(
                $request->input('submission.title'),
                $request->input('submission.type'),
                $request->input('submission.prez_type'),
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

            $mailtype = $this->congressServices->getMailType('save_submission');
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
            }
            return response()->json(['response' => 'Enregistrement avec succes'], 200); 
        } catch (Exception $e) {
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function editSubmssion(Request $request, $submission_id)
    {
        try {
            if (!($submission = $this->submissionServices->getSubmissionById($submission_id))) {
                return response()->json(['response' => 'no submission found'], 400);
            }
            $user = $this->userServices->retrieveUserFromToken();
            $submission = $this->submissionServices->editSubmission(
                $submission,
                $request->input('submission.title'),
                $request->input('submission.type'),
                $request->input('submission.prez_type'),
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
            $this->submissionServices->saveResourceSubmission($request->input('resourceIds'), $submission->submission_id);

            $congress=$this->congressServices->getCongressById($submission->congress_id);

            $mailtype = $this->congressServices->getMailType('edit_submission');
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

    public function getCongressSubmission($congressId)
    {
        if (!($congress = $this->congressServices->getCongressById($congressId))) {
            return response()->json(['response' => 'bad request'], 400);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin))) {
                return response()->json(['response' => 'bad request'], 400);
            }
            $privilege_id = $adminCongress->privilege_id;
            $submissions = $this->submissionServices->getCongressSubmissionForAdmin($admin, $congressId, $privilege_id);

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
            return response()->json($submission_detail, 200);


        } catch (Exception $e) {
            return response()->json(['response' => $e->getMessage()], 400);
        }
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
            $evaluation = $this->submissionServices->putEvaluationToSubmission($admin, $submissionId, $note);
            return response()->json($evaluation, 200);
        } catch (Exception $e) {
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function getSubmissionByUserId()
    {
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'],401);
        }
        $submissions = $this->submissionServices->getSubmissionsByUserId($user);
        return response()->json($submissions, 200);
    }


    //ATTESTATION SUBMISSION


    public function  getAttestationSubmissionByCongress($congressId) {

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }

        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress=(AdminCongress::where('congress_id', '=', $congressId)
                ->where('admin_id', '=', $admin->admin_id)->first()))) {
                return response()->json(['error' => 'forbidden'], 400);
            }
            if ($adminCongress->privilege_id != 1) {
                return response()->json(['error' => 'forbidden'], 403);
            }
            $attestationsSubmissions = $this->submissionServices->getAttestationSubmissionByCongress($congressId);
            return response($attestationsSubmissions, 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);

        }

    }

    public function activateAttestationByCongressByType($congressId,Request $request) {
        $attestationSubmissionId = $request->input('attestationSubmissionId');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$attestationSubmission=$this->submissionServices->getAttestationSubmissionById($attestationSubmissionId)) {
            return response(['error' => "attestation submission not found"], 404);
        }

        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress=(AdminCongress::where('congress_id', '=', $congressId)
                ->where('admin_id', '=', $admin->admin_id)->first()))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            if ($adminCongress->privilege_id != 1) {
                return response()->json(['error' => 'forbidden'], 403);
            }
            $attestationsSubmissions = $this->submissionServices->getAttestationsSubmissionsByCongressAndType($congressId, $attestationSubmission->communication_type_id);
            $response = $this->submissionServices->activateAttestationSubmission($attestationsSubmissions,$attestationSubmissionId);
            return response(['response' =>$response], 200);
        }
        catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);

        }
    }

    public function affectAttestationToCongress($congressId,Request $request) {
        $IdGenerator = $request->input('IdGenerator','');
        $IdGeneratorBlank = $request->input('IdGeneratorBlank','');
        $communicationTypeId = $request->input('communicationTypeId');
        $attestationType = $request->input('attestationType','0');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$communicationType = $this->submissionServices->getCommunicationTypeById($communicationTypeId)) {
            return response(['error' => "communication type invalid"], 404);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress=(AdminCongress::where('congress_id', '=', $congressId)
                ->where('admin_id', '=', $admin->admin_id)->first()))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            // Affectation Attestation to Congress
            if ($attestationType === '1') {
                if ($attestationSubmission = $this->submissionServices->getAttestationByGeneratorId($IdGenerator)) {
                    $this->submissionServices->updateOrCreateAttestationParams($IdGeneratorBlank, $request->input('keys'), true);
                    $attestationSubmission->enable = 1;
                    $attestationSubmission->attestation_generator_id_blank = $IdGeneratorBlank;
                    $attestationSubmission->update();
                }
                else if ($attestationSubmission = $this->submissionServices->getAttestationByGeneratorBlankId($IdGeneratorBlank)) {
                    $this->submissionServices->updateOrCreateAttestationParams($IdGeneratorBlank, $request->input('keys'), true);
                    $attestationSubmission->enable = 1;
                    $attestationSubmission->update();
                } else {
                    $attestationSubmission = $this->submissionServices->validerAttestation($congressId, $IdGeneratorBlank, $communicationTypeId, true);
                    $this->submissionServices->updateOrCreateAttestationParams($IdGeneratorBlank, $request->input('keys'), false);
                }
                $attestationsSubmissions = $this->submissionServices->getAttestationsSubmissionsByCongressAndType($congressId, $communicationTypeId);
                $this->submissionServices->activateAttestationSubmission($attestationsSubmissions,$attestationSubmission->attestation_submission_id);
                return response()->json('attestation submission blank affected successfully', 200);

            } else  {
                if ($attestationSubmission = $this->submissionServices->getAttestationByGeneratorBlankId($IdGeneratorBlank)) {
                    $this->submissionServices->updateOrCreateAttestationParams($IdGenerator, $request->input('keys'), true);
                    $attestationSubmission->enable = 1;
                    $attestationSubmission->attestation_generator_id = $IdGenerator;
                    $attestationSubmission->update();
                }
                else if ($attestationSubmission = $this->submissionServices->getAttestationByGeneratorId($IdGenerator)) {
                    $this->submissionServices->updateOrCreateAttestationParams($IdGenerator, $request->input('keys'), true);
                    $attestationSubmission->enable = 1;
                    $attestationSubmission->update();
                } else {
                    $attestationSubmission = $this->submissionServices->validerAttestation($congressId, $IdGenerator, $communicationTypeId, false);
                    $this->submissionServices->updateOrCreateAttestationParams($IdGenerator, $request->input('keys'), false);
                }
                $attestationsSubmissions = $this->submissionServices->getAttestationsSubmissionsByCongressAndType($congressId, $communicationTypeId);
                $this->submissionServices->activateAttestationSubmission($attestationsSubmissions,$attestationSubmission->attestation_submission_id);
                return response()->json('attestation submission affected successfully', 200);
            }

        }
        catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }
    public function getSubmissionType() {

        $submissionType = $this->submissionServices->getSubmissionType();
        return response()->json($submissionType, 200);
    }

    public function getSubmissionAccepted($congressId,$communicationTypeId) {

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$communicationType = $this->submissionServices->getCommunicationTypeById($communicationTypeId)) {
            return response(['error' => "communication type invalid"], 404);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress = (AdminCongress::where('congress_id', '=', $congressId)
                ->where('admin_id', '=', $admin->admin_id)->where('privilege_id','=',1)->first()))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            $submissionAccepted = $this->submissionServices->getSubmissionAcceptedByCongressByCommunicationType($congressId,$communicationTypeId);
            return  response()->json($submissionAccepted, 200);
        }
        catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function getAttestationSubmissionEnabled($congressId,$communicationTypeId) {

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$communicationType = $this->submissionServices->getCommunicationTypeById($communicationTypeId)) {
            return response(['error' => "communication type invalid"], 404);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress = (AdminCongress::where('congress_id', '=', $congressId)
                ->where('admin_id', '=', $admin->admin_id)->where('privilege_id','=',1)->first()))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            $attestationSubmissionEnabled = $this->submissionServices->getAttestationSubmissionEnabled($congressId,$communicationTypeId);
            return  response()->json($attestationSubmissionEnabled, 200);
        }
        catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function senMailAttestationAllSubmission($congressId) {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress = (AdminCongress::where('congress_id', '=', $congressId)
                ->where('admin_id', '=', $admin->admin_id)->where('privilege_id','=',1)->first()))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            $mailtype = $this->congressServices->getMailType('attestation', 'submission');
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);
            $mailId = $mail->mail_id;
            $users = $this->userServices->getUsersSubmissionWithRelations($congressId,   ['submissions' => function ($query) use ($congressId) {
                $query->where("congress_id", "=", $congressId);
                $query->where('status', "=", 1);
                $query->with(['authors']);
            },
                'user_mails' => function ($query) use ($mailId) {
                    $query->where('mail_id', '=', $mailId); // ICI
                }]);
            $attestationSubmissionEposter = $this->submissionServices->getAttestationSubmissionEnabled($congressId,1);
            $attestationSubmissionCommunicationOrale = $this->submissionServices->getAttestationSubmissionEnabled($congressId,2);
            foreach ($users as $user) {
                $requestEposter = array();
                $requestCommunicationOrale = array();
                if ($user->email != null && $user->email != "-" && $user->email != "") {
                    foreach ($user->submissions as $submission) {
                        if ($submission->communication_type_id == 1) { //EPOSTER
                            if ($attestationId = $attestationSubmissionEposter->attestation_generator_id) {
                                // LENA NAAMEL EL mapping mta3 les params
                                array_push($requestEposter,
                                        $this->sharedServices->submissionMapping($submission->title, $user->first_name.' '.$user->last_name, $submission->authors,$attestationSubmissionEposter->attestation_param)
                                    );
                            }
                        }
                        if ($submission->communication_type_id == 2) { //COMMUNICATION ORALE
                            if ($attestationId = $attestationSubmissionCommunicationOrale->attestation_generator_id) {
                                // LENA NAAMEL EL mapping mta3 les params
                                array_push($requestCommunicationOrale,
                                    $this->sharedServices->submissionMapping($submission->title, $user->first_name.' '.$user->last_name, $submission->authors,$attestationSubmissionCommunicationOrale->attestation_param));
                            }
                        }
                    }
                    $this->submissionServices->saveAttestationsSubmissionsInPublic([
                        'badgeIdGenerator' => $attestationSubmissionEposter->attestation_generator_id,
                        'participants' => $requestEposter], 'Eposter');
                    $this->submissionServices->saveAttestationsSubmissionsInPublic([
                        'badgeIdGenerator' => $attestationSubmissionCommunicationOrale->attestation_generator_id,
                        'participants' => $requestCommunicationOrale], 'Communication_Orale');
                    if ($mail) {
                        $userMail = null;
                        if (sizeof($user->user_mails) == 0) {
                            $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                        } else {
                            $userMail = $user->user_mails[0];
                        }
                        if ($userMail->status != 1) {
                            $this->userServices->sendMailAttesationSubmissionZipToUser($user, $congress, $userMail,
                                $mail->object,
                                $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null));
                        }
                    }


                }
            }

            return response()->json(['message' => 'send mail successs']);
        }
        catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function sendMailAttestationById($submissionId, $congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$submission = $this->submissionServices->getSubmissionByIdWithRelation(
            ['authors', 'user'], $submissionId)) {
            return response(['error' => "submission not found"], 404);
        }
        if ($submission->status != 1) {
            return response(['error' => "submission not selected"], 400);
        }

        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress = (AdminCongress::where('congress_id', '=', $congressId)
                ->where('admin_id', '=', $admin->admin_id)->where('privilege_id', '=', 1)->first()))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            $user = $submission['user'];
            $mailtype = $this->congressServices->getMailType('attestation', 'submission');
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);
            $userMail = null;

//            $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);

            $attestationSubmissionEposter = $this->submissionServices->getAttestationSubmissionEnabled($congressId, 1);
            $attestationSubmissionCommunicationOrale = $this->submissionServices->getAttestationSubmissionEnabled($congressId, 2);

            if ($submission->communication_type_id == 1) { //EPOSTER

                $fill = $this->sharedServices->submissionMapping($submission->title,
                    $user->first_name . ' ' . $user->last_name,
                    $submission->authors,
                    $attestationSubmissionEposter->attestation_param);

                $this->submissionServices->saveAttestationSubmissionInPublic($fill,
                    $attestationSubmissionEposter->attestation_generator_id);
            }


            if ($submission->communication_type_id == 2) { //COMMUNICATION ORALE

                $fill = $this->sharedServices->submissionMapping($submission->title,
                    $user->first_name . ' ' . $user->last_name,
                    $submission->authors,
                    $attestationSubmissionCommunicationOrale->attestation_param);

                $this->submissionServices->saveAttestationSubmissionInPublic($fill,
                    $attestationSubmissionCommunicationOrale->attestation_generator_id);

            }
            $this->userServices->sendMailAttesationSubmissionToUser($user, $congress, $userMail, $mail->object,
                $this->congressServices->renderMail($mail->template,
                    $congress, $user, null, null, null));
            return response()->json(['message' => 'send mail successs']);

        } catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }

    }

}
