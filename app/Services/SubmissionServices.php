<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\ConfigSubmission;
use App\Models\SubmissionEvaluation;
use App\Models\ResourceSubmission;

class SubmissionServices
{
    protected $resourcesServices;
    protected $adminServices;

    function __construct(
        AdminServices $adminServices,
        ResourcesServices $resourcesServices)
    {
        $this->resourcesServices = $resourcesServices;
        $this->adminServices = $adminServices;
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
        return Submission::with([
            'Authors'=>function ($query) use ($submission_id){
                $query->where('submission_id','=',$submission_id);
            },
            'Resources'=>function ($query) use ($submission_id){
                $query->where('submission_id','=',$submission_id);
            }
            ])
            ->where('submission_id', '=', $submission_id)
            ->first();
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

}
