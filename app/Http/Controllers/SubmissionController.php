<?php

namespace App\Http\Controllers;

use App\Models\SubmissionEvaluation;
use App\Services\AdminServices;
use App\Services\AuthorServices;
use App\Services\ResourcesServices;
use App\Services\SubmissionEvaluationService;
use App\Services\SubmissionServices;
use App\Services\ThemeServices;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class SubmissionController extends Controller
{
    protected $submissionServices;
    protected $authorServices;
    protected $adminServices;
    protected $userServices;

    function __construct(
        SubmissionServices $submissionServices,
        AuthorServices $authorServices,
        AdminServices $adminServices,
        UserServices $userServices
    )
    {
        $this->submissionServices = $submissionServices;
        $this->authorServices = $authorServices;
        $this->adminServices = $adminServices;
        $this->userServices = $userServices;
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

    public function getCongressSubmission(Request $request)
    {
        $congressId = $request->query('congress_id', -1);
        if ($congressId < 1) {
            return response()->json(['response' => 'bad request'], 400);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            $submissions = $this->submissionServices->getCongressSubmissionForAdmin($admin, $congressId);
            if ($submissions) {
                return response()->json($submissions, 200);
            }
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function getCongressSubmissionForEvaluator(Request $request)
    {
        $congressId = $request->query('congress_id', -1);
        if ($congressId < 1) {
            return response()->json(['response' => 'bad request'], 400);
        }
        try {
            $admin = $this->adminServices->retrieveAdminFromToken();
            $submissions = $this->submissionServices->getCongressSubmissionForEvaluator($admin, $congressId);
            return response()->json($submissions, 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response' => $e->getMessage()], 400);
        }
    }

    public function putEvaluationToSubmission(Request $request)
    {
        $submissionEvaluationId = $request->input('submission_evaluation_id', -1);
        $note = $request->input('note', -1);
            if ($submissionEvaluationId < 1 || $note < 0 || $note > 20) {
                return response()->json(['response' => 'bad request'], 400);
            }
            try {
                $admin = $this->adminServices->retrieveAdminFromToken();
                $evaluation = $this->submissionServices->putEvaluationToSubmission($admin, $submissionEvaluationId, $note);
                return response()->json($evaluation, 200);
            } catch (Exception $e) {

                Log::info($e->getMessage());
                return response()->json(['response' => $e->getMessage()], 400);
            }
    }

}
