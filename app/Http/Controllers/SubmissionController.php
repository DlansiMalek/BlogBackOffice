<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Services\AdminServices;
use App\Services\AuthorServices;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\SubmissionServices;
use App\Services\UserServices;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubmissionController extends Controller
{
    protected $submissionServices;
    protected $authorServices;
    protected $adminServices;
    protected $userServices;
    protected $congressServices;

    function __construct(
        SubmissionServices $submissionServices,
        AuthorServices $authorServices,
        AdminServices $adminServices,
        UserServices $userServices,
        CongressServices $congressServices,
        MailServices $mailServices
    )
    {
        $this->submissionServices = $submissionServices;
        $this->authorServices = $authorServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->mailServices = $mailServices;
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
            Log::info($user);
            $submission = $this->submissionServices->addSubmission(
                $request->input('submission.title'),
                $request->input('submission.type'),
                $request->input('submission.prez_type'),
                $request->input('submission.description'),
                $request->input('submission.congress_id'),
                $request->input('submission.theme_id'),
                $user->user_id
            );
            $this->authorServices->saveAuthorsBySubmission($request->input('authors'), $submission->submission_id);

            $admins = $this->adminServices->getEvaluatorsByThemeOrByCongress($submission->theme_id, $submission->congress_id, 11);

            $this->submissionServices->affectSubmissionToEvaluators(
                $this->congressServices->getConfigSubmission($submission->congress_id),
                $submission->submission_id,
                $admins
            );

            $this->submissionServices->saveResourceSubmission($request->input('resourceIds'), $submission->submission_id);
            if($user->gender==1){$gender="Monsieur ";}
            else {$gender="Madame ";}
            $congress=$this->congressServices->getCongressById($submission->congress_id);

            if (!$mailtype= $this->congressServices->getMailType('save_submission'))
            {response()->json(['response' => "mail type not found !"], 400);}

            if (!$mail= $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $mail = new Mail();
                $mail->template = "";
                $mail->object = "Enregistrement de soumission";
                $mail->template = $mail->template . "<br>" .$gender ." " .$user->last_name .",";
                $mail->template = $mail->template . "<br>Votre soumission ".$submission->title ." a été ajoutée avec succés !";
            }
            $this->userServices->sendMail(
            $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null),
            $user,
            $congress,
            $mail->object,
            null,
            null,
            $user->email
            );
        return response()->json(['response' => 'Enregistrement avec succes'], 200); 
        } catch (Exception $e) {

            Log::info($e->getMessage());
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
            $existingAuthors = $this->authorServices->getAuthorsBySubmissionId($submission->submission_id);
            $this->authorServices->editAuthors($existingAuthors, $request->input('authors'), $submission->submission_id);
            $this->submissionServices->saveResourceSubmission($request->input('resourceIds'), $submission->submission_id);

            if($user->gender==1){$gender="Monsieur ";}
            else {$gender="Madame ";}
            $congress=$this->congressServices->getCongressById($submission->congress_id);

            if (!$mailtype= $this->congressServices->getMailType('edit_submission'))
            {response()->json(['response' => "mail type not found !"], 400);}

            if (!$mail= $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                $mail = new Mail();
                $mail->template = "";
                $mail->object = "Modification de soumission";
                $mail->template = $mail->template . "<br>" .$gender ." " .$user->last_name .",";
                $mail->template = $mail->template . "<br>La modification appliquée sur votre soumission ".$submission->title ." a été ajoutée avec succés !";
            }
            $this->userServices->sendMail(
            $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null),
            $user,
            $congress,
            $mail->object,
            null,
            null,
            $user->email
            );
            return response()->json(['response' => 'modification avec success'], 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function getSubmission($submission_id)
    {

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

            Log::info($e->getMessage());
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

            Log::info($e->getMessage());
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

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function changeSubmissionStatusToInProgress($submissionId)
    {
        if (!$submission=$this->submissionServices->getSubmissionById($submissionId))
        {return response()->json(['response' => "submission not found !"], 400);}

        $this->submissionServices->updateSubmissionStatus($submission,3);
        
        $user = $this->userServices->getUserById($submission->user_id);
        if($user->gender==1){$gender="Monsieur ";}
        else {$gender="Madame ";}
        $congress=$this->congressServices->getCongressById($submission->congress_id);

        if(!$mailtype= $this->congressServices->getMailType('bloc_edit_submission'))
        {response()->json(['response' => "mail type not found !"], 400);}

        if (!$mail= $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
            $mail = new Mail();
            $mail->template = "";
            $mail->object = "Soumission en cours de validation";
            $mail->template = $mail->template . "<br>" .$gender ." " .$user->last_name .",";
            $mail->template = $mail->template . "<br>Votre soumission ".$submission->title ." est en cours de validation, merci de ne pas la modifier.";
        }
        $this->userServices->sendMail(
        $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null),
        $user,
        $congress,
        $mail->object,
        null,
        null,
        $user->email
        );

        return response()->json(['response' => 'blocage de modification avec success'], 200);
    }

}
