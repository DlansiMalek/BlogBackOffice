<?php

namespace App\Http\Controllers;

use App\Models\SubmissionEvaluation;
use App\Services\AdminServices;
use App\Services\AuthorServices;
use App\Services\ResourcesServices;
use App\Services\SubmissionEvaluationService;
use App\Services\SubmissionServices;
use App\Services\ThemeServices;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
class SubmissionController extends Controller
{
    protected $submissionServices;
    protected $autherServices;
    protected $themeServices;
    protected $adminServices;
    protected $submissionEvaluationServices;
    protected $resourcesServices;

    function __construct(
    SubmissionServices $submissionServices, 
    AuthorServices $authorServices,
    ThemeServices $themeServices,
    AdminServices $adminServices,
    SubmissionEvaluationService $submissionEvaluationServices,
    ResourcesServices $resourcesServices
    )
    {   
      $this->submissionServices=$submissionServices;
      $this->autherServices=$authorServices;
      $this->themeServices=$themeServices;
      $this->adminServices=$adminServices;
      $this->submissionEvaluationServices=$submissionEvaluationServices;
      $this->resourcesServices=$resourcesServices;
    }

    public function addSubmission(Request $request)
    {   
        
        if (!($request->has('submission.title') && $request->has('submission.type') && $request->has('submission.prez_type') 
            && $request->has('submission.description')  && $request->has('submission.congress_id') && $request->has('submission.theme_id')
            && $request->has('authors'))
            ){
            return response()->json(['response'=>'bad request'],400);
            }
            
            try{
            $submission=$this->submissionServices->addSubmission(
                $request->input('submission.title'),
                $request->input('submission.type'),
                $request->input('submission.prez_type'),
                $request->input('submission.description'),
                $request->input('submission.congress_id'),
                $request->input('submission.theme_id')
                
            );
        $this->saveAuthorsBySubmission($request->input('authors'),$submission->submission_id);

        $this->affectSubmissionToEvaluators($submission->theme_id,$submission->congress_id,$submission->submission_id);

        if (sizeof($request->input('resources'))>=1){

        $this->saveResourceSubmission($request->input('resources'),$submission->submission_id);
        }
        
        return response()->json(['response'=>'Enregistrement avec succes'],200);
         }
         catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response'=>$e->getMessage()],400);
        }

    }
    public function getSubmissionById($submission_id){
        if (!$submission=$this->submissionServices->getSubmissionById($submission_id)){
            return response()->json(['response'=>'no submission found'],400);
        }
        return $submission;
    }

    public function saveAuthorsBySubmission($authors,$submission_id)
    {
        if(!$submission=$this->getSubmissionById($submission_id)){
            return response()->json(['response'=>'no submission found'],400);
        }
        foreach($authors as $author){
            $this->autherServices->saveAuthor(
                $author['first_name'],
                $author['last_name'],
                $author['rank'],
                $submission_id,
                $author['service_id'],
                $author['etablissement_id'],

            );
        }
    }

    public function saveResourceSubmission($resources,$submission_id){

        $savedResources=[];
        try{
        foreach($resources as $resource){
           array_push($savedResources ,$this->resourcesServices->saveResource($resource['path'],$resource['size']));
        }
        foreach($savedResources as $savedResource){
            $this->resourcesServices->saveResourceSubmission($savedResource['resource_id'],$submission_id);
        }
        }
        catch(Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response'=>$e->getMessage()],400);

        }
    }

    public function affectSubmissionToEvaluators($themeId,$congressId,$submissionId){
      
      $theme=$this->themeServices->getThemeByCongressIdAndThemeId($themeId,$congressId) ;  

      $admins=$this->adminServices->getEvaluatorsByCongressId($congressId,11);
       
        if ($theme){
            foreach($admins as $admin){
                
                return $this->submissionEvaluationServices->addSubmissionEvaluation($admin['admin_id'],$submissionId);
                
            }
        }
        else {
            $evaluators= $this->adminServices->getEvluatiorsBySubmission();
            $admin_id=$evaluators[0]->admin_id;
            $min=count($evaluators[0]->submission);
            foreach($evaluators as $evaluator){
                if (count($evaluator->submission)<$min)
                {
                    $min=count($evaluator->submission);
                    $admin_id=$evaluator->admin_id;
                }
                return $this->submissionEvaluationServices->addSubmissionEvaluation($admin_id,$submissionId);

            }
        }
    
    }
}
