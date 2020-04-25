<?php

namespace App\Services;

use App\Models\AdminCongress;
use App\Models\Submission;
use App\Models\ConfigSubmission;
use App\Models\SubmissionEvaluation;
use App\Models\ResourceSubmission;

class SubmissionServices
{
    protected $resourcesServices;
    protected $adminServices;
    protected $congressServices;

    function __construct(
        AdminServices $adminServices,
        ResourcesServices $resourcesServices,
        CongressServices $congressServices
)
    {
        $this->resourcesServices = $resourcesServices;
        $this->adminServices = $adminServices;
        $this->congressServices = $congressServices;


    }

    public function addSubmission($title, $type, $prez_type, $description, $congress_id, $theme_id, $user_id)
    {
        $submission = new Submission();
        $submission->title = $title;
        $submission->type = $type;
        $submission->prez_type = $prez_type;
        $submission->description = $description;
        $submission->congress_id = $congress_id;
        $submission->theme_id = $theme_id;
        $submission->user_id = $user_id;
        $submission->save();
        return $submission;
    }

    public function getSubmissionById($submission_id)
    {
        return Submission::where('submission_id', '=', $submission_id)->first();
    }

    public function getConfigSubmission($congress_id)
    {
        return ConfigSubmission::where('congress_id', '=', $congress_id)->first();
    }

    public function saveResourceSubmission($resourceIds, $submission_id)
    {
        foreach ($resourceIds as $resourceId) {
            $resourceSubmission = new ResourceSubmission();
            $resourceSubmission->resource_id = $resourceId;
            $resourceSubmission->Submission_id = $submission_id;
            $resourceSubmission->save();
        }
    }

    public function affectSubmissionToEvaluators($congress_id, $submission_id, $admins)
    {
        $configSubmission = $this->getConfigSubmission($congress_id);

        $loopLength = sizeof($admins) > $configSubmission['num_evaluators'] ? $configSubmission['num_evaluators'] : sizeof($admins);

        for ($i = 0; $i < $loopLength; $i++) {
            $this->addSubmissionEvaluation($admins[$i]->admin_id, $submission_id);
        }
    }

    public function addSubmissionEvaluation($admin_id, $submission_id)
    {
        $submissionEvaluation = new SubmissionEvaluation();
        $submissionEvaluation->submission_id = $submission_id;
        $submissionEvaluation->admin_id = $admin_id;
        $submissionEvaluation->save();
        return $submissionEvaluation;
    }

    public function getCongressSubmissionForAdmin($admin, $congress_id,$privilege_id)
    {
            if ($privilege_id == 1) {

                $allSubmission = Submission::with([
                        'user:user_id,first_name,last_name,email',
                        'authors:submission_id,author_id,first_name,last_name',
                        'theme:theme_id,label',
                        'submissions_evaluations' => function ($query) {
                            $query->select('submission_id', 'submission_evaluation_id', 'admin_id', 'note')
                                ->with(['evaluator:admin_id,name,email']);
                        }
                    ])->where('congress_id', '=', $congress_id)->get();
                    $allSubmissionToRender = $allSubmission->map(function ($submission) {
                        return collect($submission->toArray())
                            ->only(['submission_id', 'title', 'type',
                                'prez_type', 'description', 'global_note',
                                'status', 'theme', 'user', 'authors', 'submissions_evaluations',
                                'congress_id', 'created_at'])
                            ->all();
                    });
                    return $allSubmissionToRender;

            } elseif ($privilege_id == 11) {
                    $allSubmission = Submission::whereHas('submissions_evaluations', function ($query) use ($admin) {
                        $query->where('admin_id', '=', $admin->admin_id);
                    })
                        ->with([
                            'theme:theme_id,label',
                            'submissions_evaluations' => function ($query) use ($admin) {
                                $query->select('submission_id', 'submission_evaluation_id', 'admin_id', 'note')
                                    ->with(['evaluator:admin_id,name,email'])->where('admin_id', '=', $admin->admin_id);
                            }
                        ])->where('congress_id', '=', $congress_id)->get();
                    $allSubmissionToRender = $allSubmission->map(function ($submission) {
                        return collect($submission->toArray())
                            ->only(['submission_id', 'title', 'type',
                                'prez_type', 'description', 'global_note',
                                'status', 'theme', 'submissions_evaluations',
                                'congress_id', 'created_at'])
                            ->all();
                    });
                    return $allSubmissionToRender;

            }

    }


    public function getSubmissionDetailById($admin, $congress_id, $submission_id, $privilege_id) {

        $allSubmissionToRender = $this->getCongressSubmissionForAdmin($admin, $congress_id, $privilege_id);
        $submission_detail = $allSubmissionToRender->where("submission_id","=", $submission_id)->first();
        return $submission_detail;


    }


    public function putEvaluationToSubmission($admin, $submissionEvaluationId, $note)
    {
            $evaluation=$this->getSubmissionEvaluationByAdminId($admin,$submissionEvaluationId);
            $evaluation->note = $note;
            $evaluation->save();
            $submissionId = $evaluation->submission_id;
            $global_note = SubmissionEvaluation::where('submission_id', '=', $submissionId)
                ->where('note', '>=', 0)->average('note');
            $submissionUpdated = Submission::where('submission_id', '=', $submissionId)->first();
            $submissionUpdated->global_note = $global_note;
            $submissionUpdated->save();
            $eval = $evaluation->with([
                'submission:submission_id,title,type,description'])->get();
            $evalUpdate = $eval->map(function ($submissionEvaluation) use ($submissionEvaluationId) {
                return collect($submissionEvaluation->toArray())
                    ->only(['submission_evaluation_id', 'note', 'submission'])->all();
            })->where('submission_evaluation_id', '=', $submissionEvaluationId)->first();
            return $evalUpdate;


    }
    public function  getSubmissionEvaluationById($submissionEvaluationId) {
        return SubmissionEvaluation::where('submission_evaluation_id', '=', $submissionEvaluationId)->first();
    }

    public function getSubmissionEvaluationByAdminId($admin,$submissionEvaluationId) {
        return SubmissionEvaluation::where('admin_id', '=', $admin->admin_id)
            ->where('submission_evaluation_id', '=', $submissionEvaluationId)->first();
    }
}
