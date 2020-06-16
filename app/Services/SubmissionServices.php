<?php

namespace App\Services;

use App\Models\ResourceSubmission;
use App\Models\Submission;
use App\Models\SubmissionEvaluation;

class SubmissionServices
{

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

    public function editSubmission($submission, $title, $type, $prez_type, $description, $theme_id)
    {
        $submission->title = $title;
        $submission->type = $type;
        $submission->prez_type = $prez_type;
        $submission->description = $description;
        $submission->theme_id = $theme_id;
        $submission->update();
        return $submission;
    }

    public function getSubmission($submission_id)
    {
        return Submission::with([
            'authors' => function ($query) {
                $query->orderBy('rank');
            },
            'resources',
            'congress.configSubmission'
        ])
            ->where('submission_id', '=', $submission_id)
            ->first();
    }

    public function getSubmissionById($submission_id)
    {
        return Submission::where('submission_id', '=', $submission_id)->first();
    }

    public function saveResourceSubmission($resourceIds, $submission_id)
    {
        $oldResources = ResourceSubmission::where('submission_id', '=', $submission_id)->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resourceIds as $resourceId) {
                foreach ($oldResources as $oldResource) {
                    if ($oldResource['resource_id'] != $resourceId) {
                        $this->addResourceSubmission($resourceId, $submission_id);
                    }
                }
            }
        } else {
            foreach ($resourceIds as $resourceId) {

                $this->addResourceSubmission($resourceId, $submission_id);
            }
        }
    }

    function addResourceSubmission($resourceId, $submissionId)
    {

        $resourceSubmission = new ResourceSubmission();
        $resourceSubmission->resource_id = $resourceId;
        $resourceSubmission->Submission_id = $submissionId;
        $resourceSubmission->save();

        return $resourceSubmission;
    }

    public function affectSubmissionToEvaluators($configSubmission, $submission_id, $admins)
    {

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


    public function renderSubmissionForAdmin()
    {
        return Submission::with([
            'user:user_id,first_name,last_name,email',
            'authors:submission_id,author_id,first_name,last_name',
            'theme:theme_id,label',
            'submissions_evaluations' => function ($query) {
                $query->select('submission_id', 'submission_evaluation_id', 'admin_id', 'note')
                    ->with(['evaluator:admin_id,name,email']);
            }
        ]);
    }

    public function getCongressSubmissionForAdmin($admin, $congress_id, $privilege_id)
    {
        if ($privilege_id == 1) {
            $allSubmission = $this->renderSubmissionForAdmin()
                ->where('congress_id', '=', $congress_id)->get();
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


    public function getSubmissionDetailById($admin, $submission_id, $privilege_id)
    {
        if ($privilege_id == 1) {
            $submissionById = $this->renderSubmissionForAdmin()
                ->where('submission_id', '=', $submission_id)->first();
            if ($submissionById) {
                $submissionToRender = $submissionById
                    ->only(['submission_id', 'title', 'type',
                        'prez_type', 'description', 'global_note',
                        'status', 'theme', 'user', 'authors', 'submissions_evaluations',
                        'congress_id', 'created_at','congress','resources']);
                return $submissionToRender;
            }

        } elseif ($privilege_id == 11) {
            $submissionById = Submission::whereHas('submissions_evaluations', function ($query) use ($admin) {
                $query->where('admin_id', '=', $admin->admin_id);
            })
                ->with([
                    'theme:theme_id,label',
                    'submissions_evaluations' => function ($query) use ($admin) {
                        $query->select('submission_id', 'submission_evaluation_id', 'admin_id', 'note')
                            ->with(['evaluator:admin_id,name,email','congress', 'resources'])->where('admin_id', '=', $admin->admin_id);
                    }
                ])->where('submission_id', '=', $submission_id)->first();
            if ($submissionById) {
                $submissionToRender = $submissionById
                    ->only(['submission_id', 'title', 'type',
                        'prez_type', 'description', 'global_note',
                        'status', 'theme', 'submissions_evaluations',
                        'congress_id', 'created_at', 'resources']);

                return $submissionToRender;
            }
        }
        return null;
    }


    public function putEvaluationToSubmission($admin, $submissionId, $note)
    {
        $evaluation = $this->getSubmissionEvaluationByAdminId($admin, $submissionId);
        $evaluation->note = $note;
        $evaluation->save();
        $global_note = SubmissionEvaluation::where('submission_id', '=', $submissionId)
            ->where('note', '>=', 0)->average('note');
        $submissionUpdated = Submission::where('submission_id', '=', $submissionId)->first();
        $submissionUpdated->global_note = $global_note;
        $submissionUpdated->save();
        return $submissionUpdated;
    }

    public function getSubmissionEvaluationByAdminId($admin, $submissionId)
    {
        return SubmissionEvaluation::where('admin_id', '=', $admin->admin_id)
            ->where('submission_id', '=', $submissionId)->first();
    }

}
