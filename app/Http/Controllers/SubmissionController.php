<?php

namespace App\Http\Controllers;

use App\Services\AdminServices;
use App\Services\AuthorServices;
use App\Services\CommunicationTypeService;
use App\Services\CongressServices;
use App\Services\EstablishmentServices;
use App\Services\MailServices;
use App\Services\ResourcesServices;
use App\Services\ServiceServices;
use App\Services\SharedServices;
use App\Services\SubmissionServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use App\Services\Utils;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Madnest\Madzipper\Facades\Madzipper;
use Illuminate\Filesystem\Filesystem;

class SubmissionController extends Controller
{
    protected $type = 'submission';
    protected $submissionServices;
    protected $authorServices;
    protected $adminServices;
    protected $userServices;
    protected $congressServices;
    protected $establishmentServices;
    protected $serviceServices;
    protected $mailServices;
    protected $sharedServices;
    protected $communicationTypeService;
    protected $resourcesServices;

    function __construct(
        SubmissionServices $submissionServices,
        AuthorServices $authorServices,
        AdminServices $adminServices,
        UserServices $userServices,
        ServiceServices $serviceServices,
        EstablishmentServices $establishmentServices,
        CongressServices $congressServices,
        MailServices $mailServices,
        SharedServices $sharedServices,
        CommunicationTypeService $communicationTypeService,
        ResourcesServices $resourcesServices
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
        $this->sharedServices = $sharedServices;
        $this->communicationTypeService = $communicationTypeService;
        $this->resourcesServices = $resourcesServices;

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

            $mailtype = $this->congressServices->getMailType('save_submission', $this->type);
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

            if ($mail) {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                }

                $this->mailServices->sendMail(
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, null, null, null, null, null, null, null,  $submission->title), $user, $congress, $mail->object, null, $userMail
                );
            }
            return response()->json(['response' => 'Enregistrement avec succes'], 200);
        } catch (Exception $e) {
            $submission->delete();
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function deleteSubmission($submission_id)
    {
        if (!$submission = $this->submissionServices->getSubmissionById($submission_id)) {
            return response()->json(['response' => 'no submission found'], 400);
        }
        $submission->delete();

        return response()->json(['response' => 'submssion deleted successfully']);

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
            $status = $request->input('addExternalFiles') ? '5' : $submission->status;
            $name = $request->input('addExternalFiles') ? 'file_submitted' : 'edit_submission';
            $code = $request->input('addExternalFiles') ? null : $submission->upload_file_code;
            $submission = $this->submissionServices->editSubmission(
                $submission,
                $request->input('submission.title'),
                $request->input('submission.type'),
                $status,
                $request->input('submission.communication_type_id'),
                $request->input('submission.description'),
                $request->input('submission.theme_id'),
                $code
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

                $congress = $this->congressServices->getCongressById($submission->congress_id);
                $this->submissionServices->affectSubmissionToEvaluators(
                    $this->congressServices->getConfigSubmission($submission->congress_id),
                    $submission->submission_id,
                    $admins
                );

            }
            if (($submission->limit_date > date('Y-m-d H:i:s') && $submission->status == 5) ||
                $submission->status == 0
            ) {
                $this->submissionServices->saveResourceSubmission($request->input('resourceIds'), $submission->submission_id);
            }
            $congress = $this->congressServices->getCongressById($submission->congress_id);
            $mailtype = $this->congressServices->getMailType($name, $this->type);
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

            if ($mail) {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                }

                $this->mailServices->sendMail(
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, null, $userMail
                );
            }
            return response()->json(['response' => 'modification avec success'], 200);
        } catch (Exception $e) {
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function getSubmission($submission_id, Request $request)
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
        $upload_file_code = $request->query('code');
        return $this->submissionServices->getSubmission($submission_id, $upload_file_code);
    }

    public function getCongressSubmission($congressId, Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $search = $request->query('search', '');
        $tri = $request->query('tri', '');
        $order = $request->query('order', '');
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

            $submissions = $this->submissionServices->getCongressSubmissionForAdmin($admin, $congressId, $privilege_id, $status, $perPage, $search, $tri, $order);

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
            $congress = $this->congressServices->getCongressById($congressId);
            if ($privilege_id == 11) {
                $mail_type = $this->congressServices->getMailType('bloc_edit_submission', $this->type);
                $mail = $this->congressServices->getMail($congressId, $mail_type->mail_type_id);
                if ($mail) {
                    $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                    if (!$userMail) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                        $this->mailServices->sendMail(
                            $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, null, $mail->object, null, $userMail
                        );
                    }
                }
            }
            return response()->json($submission_detail, 200);

        } catch (Exception $e) {
            return response()->json(['response' => $e->getMessage()], 400);
        }

    }

    public function changeMultipleSubmissionsStatus(Request $request, $congress_id)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json('no admin found', 404);
        }
        if (!$congress = $this->congressServices->getById($congress_id)) {
            return response()->json('no congress found', 404);
        }
        if (!$request->has('selectedSubmissions')) {
            return response()->json('bad request', 404);
        }
        $selectedSubmissions = $request->input('selectedSubmissions');
        $submissions = $this->submissionServices->getSubmissionsByCongressId($congress_id);

        for ($i = 0; $i < sizeof($selectedSubmissions); $i++) {
            $left = 0;
            $right = sizeof($submissions) - 1;
            $index = -1;
            while ($left <= $right) {
                $midpoint = (int)floor(($left + $right) / 2);

                if ($submissions[$midpoint]['submission_id'] < $selectedSubmissions[$i]) {
                    $left = $midpoint + 1;
                } elseif ($submissions[$midpoint]['submission_id'] > $selectedSubmissions[$i]) {
                    $right = $midpoint - 1;
                } else {
                    $index = $midpoint;
                    $this->finalDecisionOnSubmission($request, $submissions[$midpoint]->submission_id);
                    break;
                }
            }
            if ($index === -1) {
                return response()->json('no submission found', 404);
            }


        }
        return response()->json('success', 200);
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
            $this->congressServices->getMailType('accept_submission', $this->type) :
            $this->congressServices->getMailType('refuse_submission', $this->type);
        $mail = $this->congressServices->getMail($congress_id, $mail_type->mail_type_id);
        $congress = $this->congressServices->getCongressById($congress_id);
        if ($mail) {
            $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
            if (!$userMail) {
                $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                $this->mailServices->sendMail(
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, null, $mail->object, null, $userMail
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

        $submission->limit_date = $request->input('limit_date');

        // generate code 

        $type = $this->communicationTypeService->getCommunicationTypeById(
            $request->has('communication_type_id') ? $request->input('communication_type_id') :
                $submission->communication_type_id
        );
        if ($request->has('communication_type_id')) {
            $submission->communication_type_id = $request->input('communication_type_id');
        }
        if ($type && $request->input('status') == '1' && !$submission->code) {
            $index = -1;
            $submissions = $this->submissionServices->getSubmissionsByCongressId($submission->congress_id);
            foreach ($submissions as $key => $value) {
                if ($value->submission_id == $submission_id) {
                    $index = $key + 1;
                    break;
                }

            }
            $code = Utils::generateSubmissionCode($type->abrv, $index);
            $submission->code = $code;
        }
        $file_upload_code = null;
        if ($request->input('status') == 4) {
            $file_upload_code = $this->adminServices->generateRandomString(10);
            $submission->upload_file_code = $file_upload_code;
        }
        $submission->update();
        //send email
        $areFiles = $request->has('areFiles') ? 1 : 0;
        $mailName = $request->input('status') == 3 ? 'refuse_submission' :
            ($request->input('status') == 4 ? 'Attente_de_fichier' :
                ($request->input('status') == 5 ? 'file_submitted' : 'accept_submission'));

        $mailtype = $this->congressServices->getMailType($mailName, $this->type);
        $mail = $this->congressServices->getMail($submission->congress_id, $mailtype->mail_type_id);

        if ($mail) {
            $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $submission->user_id);
            if (!$userMail) {
                $userMail = $this->mailServices->addingMailUser($mail->mail_id, $submission->user_id);
            }
            $link = '';
            if (($request->input('status') == 4)) {
                $link = UrlUtils::getBaseUrlFrontOffice()
                    . '/user-profile/submission/submit-resources/' . $submission->submission_id . '?code=' . $file_upload_code;
            }
            $user = $this->userServices->getUserById($submission->user_id);
            $congress = $this->congressServices->getCongressById($submission->congress_id);
            $this->mailServices->sendMail(
                $this->congressServices->renderMail(
                    $mail->template,
                    $congress,
                    $user,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $link,
                    $request->input('status') == '1' ? $submission->code : null,
                    $submission->title,
                    $type ? $type->label : null

                ),
                $user,
                null,
                $mail->object,
                null,
                $userMail
            );
        }

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

            $mailtype = $this->congressServices->getMailType('bloc_edit_submission', $this->type);
            $mail = $this->congressServices->getMail($submission->congress_id, $mailtype->mail_type_id);

            if ($mail) {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $submission->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $submission->user_id);
                    $this->mailServices->sendMail(
                        $this->congressServices->renderMail($mail->template, $submission->congress, $submission->user, null, null, null), $submission->user, $submission->congress, $mail->object, null, $userMail
                    );
                }


            }
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

    public function getAllSubmissionsByCongress($congressId, Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', '');
        $offset = $request->query('offset', 0);
        $perPage = $request->query('perPage', 5);
        $communication_type_id = $request->query('communication_type_id');
        if (!$communication_type_id) {
            return response()->json(['response' => 'bad request'], 400);
        }
        if (!($congress = $this->congressServices->getCongressById($congressId))) {
            return response()->json(['response' => 'congress not found'], 400);
        }
        $submissions = $this->submissionServices->getAllSubmissionsByCongress($congressId, $search, $status, $offset, $perPage, $communication_type_id);
        return response()->json($submissions, 200);
    }


    //ATTESTATION SUBMISSION


    public function getAttestationSubmissionByCongress($congressId)
    {

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }

        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!$adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin)) {
                return response()->json(['error' => 'forbidden'], 400);
            }
            $attestationsSubmissions = $this->submissionServices->getAttestationSubmissionByCongress($congressId);
            return response($attestationsSubmissions, 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);

        }

    }

    public function activateAttestationByCongressByType($congressId, Request $request)
    {
        $attestationSubmissionId = $request->input('attestationSubmissionId');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$attestationSubmission = $this->submissionServices->getAttestationSubmissionById($attestationSubmissionId)) {
            return response(['error' => "attestation submission not found"], 404);
        }
        if ($attestationSubmission->congress_id != $congressId) {
            return response(['error' => "an error has occurred"], 400);
        }

        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!$adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin)) {
                return response()->json(['error' => 'forbidden'], 400);
            }
            $attestationsSubmissions = $this->submissionServices->getAttestationsSubmissionsByCongressAndType($congressId, $attestationSubmission->communication_type_id);
            $response = $this->submissionServices->activateAttestationSubmission($attestationsSubmissions, $attestationSubmissionId);
            return response(['response' => $response], 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);

        }
    }

    public function deleteAttestationByCongress($congressId, Request $request)
    {
        $attestationSubmissionId = $request->input('attestationSubmissionId');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$attestationSubmission = $this->submissionServices->getAttestationSubmissionById($attestationSubmissionId)) {
            return response(['error' => "attestation submission not found"], 404);
        }
        if ($attestationSubmission->congress_id != $congressId) {
            return response(['error' => "an error has occurred"], 400);
        }

        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!$adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin)) {
                return response()->json(['error' => 'forbidden'], 400);
            }
            $response = $this->submissionServices->deleteAttestationSubmission($attestationSubmission);
            return response(['response' => $response], 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);

        }
    }

    public function affectAttestationToCongress($congressId, Request $request)
    {
        $IdGenerator = $request->input('IdGenerator', '');
        $IdGeneratorBlank = $request->input('IdGeneratorBlank', '');
        $communicationTypeId = $request->input('communicationTypeId');
        $attestationType = $request->input('attestationType', '0');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$communicationType = $this->submissionServices->getCommunicationTypeById($communicationTypeId)) {
            return response(['error' => "communication type invalid"], 404);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!$adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin)) {
                return response()->json(['error' => 'bad request'], 400);
            }
            // Affectation Attestation to Congress
            if ($attestationType === '1') {
                if ($IdGenerator && ($attestationSubmission = $this->submissionServices->getAttestationByGeneratorId($IdGenerator))) {
                    $this->submissionServices->updateOrCreateAttestationParams($IdGeneratorBlank, $request->input('keys'), true);
                    $attestationSubmission->enable = 1;
                    $attestationSubmission->attestation_generator_id_blank = $IdGeneratorBlank;
                    $attestationSubmission->update();
                } else if ($attestationSubmission = $this->submissionServices->getAttestationByGeneratorBlankId($IdGeneratorBlank)) {
                    $this->submissionServices->updateOrCreateAttestationParams($IdGeneratorBlank, $request->input('keys'), true);
                    $attestationSubmission->enable = 1;
                    $attestationSubmission->update();
                } else {
                    $attestationSubmission = $this->submissionServices->validerAttestation($congressId, $IdGeneratorBlank, $communicationTypeId, true);
                    $this->submissionServices->updateOrCreateAttestationParams($IdGeneratorBlank, $request->input('keys'), false);
                }
                $attestationsSubmissions = $this->submissionServices->getAttestationsSubmissionsByCongressAndType($congressId, $communicationTypeId);
                $this->submissionServices->activateAttestationSubmission($attestationsSubmissions, $attestationSubmission->attestation_submission_id);
                return response()->json('attestation submission blank affected successfully', 200);

            } else {
                if ($IdGeneratorBlank && ($attestationSubmission = $this->submissionServices->getAttestationByGeneratorBlankId($IdGeneratorBlank))) {
                    $this->submissionServices->updateOrCreateAttestationParams($IdGenerator, $request->input('keys'), true);
                    $attestationSubmission->enable = 1;
                    $attestationSubmission->attestation_generator_id = $IdGenerator;
                    $attestationSubmission->update();
                } else if ($attestationSubmission = $this->submissionServices->getAttestationByGeneratorId($IdGenerator)) {
                    $this->submissionServices->updateOrCreateAttestationParams($IdGenerator, $request->input('keys'), true);
                    $attestationSubmission->enable = 1;
                    $attestationSubmission->update();
                } else {
                    $attestationSubmission = $this->submissionServices->validerAttestation($congressId, $IdGenerator, $communicationTypeId, false);
                    $this->submissionServices->updateOrCreateAttestationParams($IdGenerator, $request->input('keys'), false);
                }
                $attestationsSubmissions = $this->submissionServices->getAttestationsSubmissionsByCongressAndType($congressId, $communicationTypeId);
                $this->submissionServices->activateAttestationSubmission($attestationsSubmissions, $attestationSubmission->attestation_submission_id);
                return response()->json('attestation submission affected successfully', 200);
            }

        } catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function getSubmissionType()
    {

        $submissionType = $this->submissionServices->getSubmissionType();
        return response()->json($submissionType, 200);
    }

    public function getSubmissionByStatus(Request $request, $congressId, $status)
    {
        $eligible = $request->input('eligible', '');
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!$adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin)) {
                return response()->json(['error' => 'bad request'], 400);
            }
            $submission = $this->submissionServices->getSubmissionByStatus($congressId, $status, $eligible);
            return response()->json($submission, 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function getAttestationSubmissionEnabled($congressId)
    {

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            if (!$attestationSubmissionEnabled = $this->submissionServices->getAttestationSubmissionEnabled($congressId)) {
                return response()->json(['error' => 'attestation not configured'], 404);
            }
            return response()->json($attestationSubmissionEnabled, 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function makeSubmissionEligible($submissionId, $congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        if (!$submission = $this->submissionServices->getSubmissionByIdWithRelation(
            ['authors', 'user'], $submissionId)) {
            return response(['error' => "submission not found"], 404);
        }
        if ($submission->congress_id != $congressId) {
            return response(['error' => "an error has occurred"], 400);
        }
        if ($submission->status != 1) {
            return response(['error' => "submission not selected"], 400);
        }

        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!$adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin)) {
                return response()->json(['error' => 'bad request'], 400);
            }
            $response = $this->submissionServices->makeSubmissionEligible($submission);
            return response()->json(['response' => $response], 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function sendMailAttestationAllSubmission($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response(['error' => "congress not found"], 404);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin))) {
                return response()->json(['error' => 'bad request'], 400);
            }
            if ($adminCongress->privilege_id != 1) {
                return response()->json(['error' => 'must be admin'], 400);
            }
            $mailtype = $this->congressServices->getMailType('attestation_all', 'submission');

            if (!$mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                return response()->json(['error' => 'mail attestation submission not found'], 400);
            }
            $mailId = $mail->mail_id;
            $users = $this->userServices->getUsersSubmissionWithRelations($congressId, ['submissions' => function ($query) use ($congressId) {
                $query->where("congress_id", "=", $congressId);
                $query->where('status', "=", 1);
                $query->where('eligible', "=", 1);
                $query->with(['authors']);
            },
                'user_mails' => function ($query) use ($mailId) {
                    $query->where('mail_id', '=', $mailId); // ICI
                }]);
            $attestationsSubmissions = $this->submissionServices->getAttestationSubmissionEnabled($congressId);
            foreach ($users as $user) {
                $request = array();
                if ($user->email != null && $user->email != "") {
                    foreach ($user->submissions as $submission) {
                        $attestationSubmission = null;
                        foreach ($attestationsSubmissions as $attestation) {
                            if ($attestation->communication_type_id === $submission->communication_type_id) {
                                $attestationSubmission = $attestation;
                            }
                        }
                        if (!$attestationSubmission->attestation_generator_id) {
                            continue;
                        }
                        $mappedSubmission = $this->sharedServices->submissionMapping($submission->title,
                            Utils::getFullName($user->first_name, $user->last_name),
                            $submission->authors,
                            $attestationSubmission->attestation_param);
                        $mappedSubmission['badgeIdGenerator'] = $attestationSubmission->attestation_generator_id;
                        array_push($request, $mappedSubmission
                        );
                    }
                    $this->sharedServices->saveAttestationsSubmissionsInPublic($request);

                    if ($mail) {
                        $userMail = null;
                        if (sizeof($user->user_mails) == 0) {
                            $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                        } else {
                            $userMail = $user->user_mails[0];
                        }
                        if ($userMail->status != 1) {
                            $fileName = 'attestationsSubmission.zip';
                            $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, null, null, null, null, null, null, null, null, null, $user->submissions),
                                $user,
                                $congress,
                                $mail->object,
                                true,
                                $userMail,
                                null,
                                $fileName);
                           }
                    }
                }
            }

            return response()->json(['message' => 'send mail successs']);
        } catch (Exception $e) {
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
        if ($submission->congress_id != $congressId) {
            return response(['error' => "an error has occurred"], 400);
        }
        if ($submission->status != 1 || $submission->eligible != 1) {
            return response(['error' => "submission not selected"], 400);
        }

        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!$adminCongress = $this->congressServices->getAdminByCongressId($congressId, $admin)) {
                return response()->json(['error' => 'bad request'], 400);
            }
            if ($adminCongress->privilege_id != 1) {
                return response()->json(['error' => 'must be admin'], 400);
            }
            $user = $submission['user'];
            $mailtype = $this->congressServices->getMailType('attestation', 'submission');
            if (!$mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                return response()->json(['error' => 'mail attestation submission not found'], 400);
            }
            $userMail = null;

            $attestationsSubmissions = $this->submissionServices->getAttestationSubmissionEnabled($congressId);
            $attestationSubmission = null;
            foreach ($attestationsSubmissions as $attestation) {
                if ($attestation->communication_type_id === $submission->communication_type_id) {
                    $attestationSubmission = $attestation;
                }
            }
            if (!$attestationSubmission->attestation_generator_id) {
                return response(['error' => "attestation not configured"], 400);
            }

            $fill = $this->sharedServices->submissionMapping($submission->title,
                Utils::getFullName($user->first_name, $user->last_name),
                $submission->authors,
                $attestationSubmission->attestation_param);

            $this->sharedServices->saveAttestationSubmissionInPublic($fill,
                $attestationSubmission->attestation_generator_id);

            $fileName = 'attestationSubmission.png';
            $this->mailServices->sendMail($this->congressServices->renderMail($mail->template,
                $congress,
                $user,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $submission->code,
                $submission->title,
                $submission->communicationType ? $submission->communicationType->label : null),
                $user,
                $congress,
                $mail->object,
                true,
                $userMail,
                null,
                $fileName);
            return response()->json(['message' => 'send mail successs']);

        } catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }

    }

    public function uploadSubmissions($congressId, Request $request)
    {
        $fileSystem = new Filesystem();
        $fileSystem->deleteDirectory(storage_path('app/zip'));

        // Extract Zip File
        $file = $request->file('files');
        $path = $file->store('/zip');
        Madzipper::make(storage_path('app/' . $path))->extractTo(storage_path('app/submissions'));

        $data = json_decode($request->input('data'), true);
        foreach ($data as $item) {
            if (isset($item['author_email'])) {
                $user = $this->userServices->addPrincipalUserAuthorExternal($item);
                $submission = $this->submissionServices->addSubmissionExternal($congressId, $item, $user);
                $this->authorServices->deleteAllAuthorsBySubmission($submission->submission_id);
                $principalAuthor = $this->authorServices->addPrincipalAuthor($submission, $user, $item);
                $authors = $this->authorServices->addAuthorsExternal($submission, $item);
                $this->submissionServices->deleteAllResourcesBySubmission($submission->submission_id);
                $this->resourcesServices->addRessourcesExternal($submission, $item);
            }
        }

        $fileSystem = new Filesystem();
        $fileSystem->deleteDirectory(storage_path('app/submissions'));

        return response()->json(['message' => 'import success'], 200);
    }

    public function getEpostersByCongressPeacksource($congressId, Request $request)
    {
        if (!($congress = $this->congressServices->getCongressById($congressId))) {
            return response()->json(['response' => 'congress not found'], 400);
        }

        $data = $this->submissionServices->getAllSubmissionByCongress($congressId);

        $submissions = $this->submissionServices->mappingPeacksourceData($data);

        return response()->json($submissions, 200);
    }

}
