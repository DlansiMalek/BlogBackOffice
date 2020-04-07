<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\ConfigSubmission;
use App\Models\SubmissionEvaluation;
class SubmissionServices 
{



    public function addSubmission($title,$type,$prez_type,$description,$congress_id,$theme_id)
        
    {

        $submission=new Submission();
        $submission->title=$title;
        $submission->type=$type;
        $submission->prez_type=$prez_type;
        $submission->description=$description;
        $submission->congress_id=$congress_id;
        $submission->theme_id=$theme_id;
        $submission->save();
        return $submission;
    }

    public function getSubmissionById($submission_id){
        return Submission::where('submission_id','=',$submission_id)->first();
    }

    public function getConfigSubmission($congress_id)
    {
        return ConfigSubmission::where('congress_id','=',$congress_id)->first();
    }

    
    public function addSubmissionEvaluation($admin_id,$submission_id){

        $submissionEvaluation=new SubmissionEvaluation();
        $submissionEvaluation->submission_id=$submission_id;
        $submissionEvaluation->admin_id=$admin_id;
        $submissionEvaluation->save();
        return $submissionEvaluation;
    }

    public function getSubmissionEvluation($admin_id,$submission_id)
    {
        $condition=['admin_id'=>$admin_id,'submission_id'=>$submission_id];
        return SubmissionEvaluation::where($condition)->first();
    }
}