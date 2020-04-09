<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\ConfigSubmission;
use App\Models\SubmissionEvaluation;
class SubmissionServices 
{
    protected $resourcesServices;
    protected $adminServices;

    function __construct(

        AdminServices $adminServices,
        ResourcesServices $resourcesServices)
    {
    
        $this->resourcesServices=$resourcesServices;
        $this->adminServices=$adminServices;
    
    }
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

    public function saveResourceSubmission($resourceIds,$submission_id)
    {

        if(!$submission=$this->getSubmissionById($submission_id)){
            return response()->json(['response'=>'no submission found'],400);
        }

       foreach($resourceIds as $resourceId)
        {

            $this->resourcesServices->saveResourceSubmission($resourceId,$submission_id);
        
        }
    }

    public function affectSubmissionToEvaluators($theme_id,$congress_id,$submission_id)
    {
            if(!$submission=$this->getSubmissionById($submission_id)){
                return response()->json(['response'=>'no submission found'],400);
            }

            $configSubmission=$this->getConfigSubmission($congress_id);

            $admins= $this->adminServices->getEvaluatorsByTheme($theme_id,$congress_id,11);

            if (!sizeof($admins)>0)
            {
                $admins=$this->adminServices->getEvaluatorsByCongress($congress_id,11);
            }

            $loopLength=sizeof($admins)>$configSubmission['num_evaluators'] ? $configSubmission['num_evaluators'] : sizeof($admins);

            for ($i=0;$i<$loopLength;$i++)
            {          

                $this->addSubmissionEvaluation($admins[$i]->admin_id,$submission_id);
                    
            }
            
    
    }
    
    public function addSubmissionEvaluation($admin_id,$submission_id){

        $submissionEvaluation=new SubmissionEvaluation();
        $submissionEvaluation->submission_id=$submission_id;
        $submissionEvaluation->admin_id=$admin_id;
        $submissionEvaluation->save();
        return $submissionEvaluation;
    }

}