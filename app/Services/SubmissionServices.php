<?php

namespace App\Services;

use App\Models\Submission;

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

}