<?php

namespace App\Services;

use App\Models\SubmissionEvaluation;

class SubmissionEvaluationService{


    public function addSubmissionEvaluation($admin_id,$submission_id){

        $submissionEvaluation=new SubmissionEvaluation();
        $submissionEvaluation->submission_id=$submission_id;
        $submissionEvaluation->admin_id=$admin_id;
        $submissionEvaluation->save();
        return $submissionEvaluation;
    }
}