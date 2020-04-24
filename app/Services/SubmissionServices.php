<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\SubmissionEvaluation;
use App\Models\ResourceSubmission;

class SubmissionServices
{


    function __construct() {}

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

    public function editSubmission($submission,$title, $type, $prez_type, $description, $theme_id)
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
            'resources' => function ($query) use ($submission_id) {
                $query->where('submission_id', '=', $submission_id);
            },
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
                        $this->addResourceSubmission($resourceId,$submission_id);
                    }
                }
            }
        } else {
            foreach ($resourceIds as $resourceId) {

                $this->addResourceSubmission($resourceId,$submission_id);
            }
        }
    }

    function addResourceSubmission($resourceId,$submissionId){

        $resourceSubmission = new ResourceSubmission();
        $resourceSubmission->resource_id = $resourceId;
        $resourceSubmission->Submission_id = $submissionId;
        $resourceSubmission->save();

        return $resourceSubmission ;
}

    public function affectSubmissionToEvaluators($configSubmission,$submission_id, $admins)
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
}
