<?php

namespace App\Http\Controllers;

use App\Models\SubmissionEvaluation;
use App\Services\AdminServices;
use App\Services\AuthorServices;
use App\Services\ResourcesServices;
use App\Services\SubmissionServices;
use App\Services\ThemeServices;
use App\Services\UserServices;
use App\Services\CongressServices;
use Illuminate\Http\Request;
use Exception;
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
        CongressServices $congressServices
    )
    {
        $this->submissionServices = $submissionServices;
        $this->authorServices = $authorServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;

    }

    public function addSubmission(Request $request)
    {

        if (!($request->has('submission.title') && $request->has('submission.type') && $request->has('submission.prez_type')
            && $request->has('submission.description') && $request->has('submission.congress_id') && $request->has('submission.theme_id')
            && $request->has('authors'))
        ) {
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
                $submission->congress_id,
                $submission->submission_id,
                $admins
            );

            $this->submissionServices->saveResourceSubmission($request->input('resourceIds'), $submission->submission_id);

            return response()->json(['response' => 'Enregistrement avec succes'], 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }

    }

    public function getCongressSubmission($congressId)
    {
        if (!($congress = $this->congressServices->getCongressById($congressId))) {
            return response()->json(['response' => 'bad request'], 400);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress=$this->congressServices->getAdminByCongressId($congressId,$admin))) {
                return response()->json(['response' => 'bad request'], 400);
            }
            $privilege_id = $adminCongress->privilege_id;
            $submissions = $this->submissionServices->getCongressSubmissionForAdmin($admin, $congressId,$privilege_id);

            return response()->json($submissions, 200);


        } catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }


    public function getCongressSubmissionDetailById($submissionId)
    {

        if (!($submission = $this->submissionServices->getSubmissionById($submissionId) ) ) {
            return response()->json(['response' => 'bad request'], 400);
        }
        try {
            $congressId = $submission->congress_id;
            $admin = $this->adminServices->retrieveAdminFromToken();
            if (!($adminCongress=$this->congressServices->getAdminByCongressId($congressId,$admin))) {
                return response()->json(['response' => 'bad request'], 400);
            }
            $privilege_id = $adminCongress->privilege_id;
            $submission_detail = $this->submissionServices->getSubmissionDetailById($admin, $submissionId,$privilege_id);
            return response()->json($submission_detail, 200);



        } catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }


    public function putEvaluationToSubmission($submissionId, Request $request)
    {
        $note = $request->input('note', -1);
        $submissionEvaluationId = $request->input('submission_evaluation_id', -1);
        if ((!($submissionEvaluation = $this->submissionServices->getSubmissionEvaluationById($submissionEvaluationId))) || $note < 0 || $note > 20) {
            return response()->json(['response' => 'bad request'], 400);
        }
            try {
                $admin = $this->adminServices->retrieveAdminFromToken();
                if (!($evaluation=$this->submissionServices->getSubmissionEvaluationByAdminId($admin,$submissionEvaluationId))) {
                    return response()->json(['response' => 'bad request'], 400);
                }
                $evaluation = $this->submissionServices->putEvaluationToSubmission($admin, $submissionEvaluationId, $note);
                return response()->json($evaluation, 200);
            } catch (Exception $e) {

                Log::info($e->getMessage());
                return response()->json(['response' => $e->getMessage()], 400);
            }
    }

}
