<?php

namespace App\Http\Controllers;

use App\Models\SubmissionEvaluation;
use App\Services\AdminServices;
use App\Services\AuthorServices;
use App\Services\SubmissionEvaluationService;
use App\Services\SubmissionServices;
use App\Services\ThemeServices;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    private $submissionServices;
    private $autherServices;
    private $themeServices;
    private $adminServices;
    private $submissionEvaluationServices;

    function __construct(
    SubmissionServices $submissionServices, 
    AuthorServices $authorServices,
    ThemeServices $themeServices,
    AdminServices $adminServices,
    SubmissionEvaluationService $submissionEvaluationServices
    )
    {   
      $this->submissionServices=$submissionServices;
      $this->autherServices=$authorServices;
      $this->themeServices=$themeServices;
      $this->adminServices=$adminServices;
      $this->submissionEvaluationServices=$submissionEvaluationServices;
    }

    public function addSubmission(Request $request)
    {   
        
        if (!($request->has('submission.title') && $request->has('submission.type') && $request->has('submission.prez_type') 
            && $request->has('submission.description')  && $request->has('submission.congress_id') && $request->has('submission.theme_id'))
            ){
            return response()->json(['response'=>'bad request'],400);
        }
        
        $submission=$this->submissionServices->addSubmission(
            $request->input('submission.title'),
            $request->input('submission.type'),
            $request->input('submission.prez_type'),
            $request->input('submission.description'),
            $request->input('submission.congress_id'),
            $request->input('submission.theme_id')
            
        );
        $this->saveAuthorsBySubmission($request->input('authors'),$submission);
        $this->affectSubmissionToEvaluators($submission->theme_id,$submission->congress_id,$submission->submission_id);
        return response()->json(['response'=>'Enregistrement avec succes'],200);

    }

    public function saveAuthorsBySubmission($authors,$submission)
    {
        foreach($authors as $author){
            $this->autherServices->saveAuthor(
                $author['first_name'],
                $author['last_name'],
                $author['rank'],
                $submission->submission_id,
                $author['service_id'],
                $author['etablissement_id'],

            );
        }
    }

    public function affectSubmissionToEvaluators($themeId,$congressId,$submissionId){
      
      $theme=$this->themeServices->getThemeByCongressIdAndThemeId($themeId,$congressId) ;  

      $admins=$this->adminServices->getEvaluatorsByCongressId($congressId,11);

        if ($theme){
            foreach($admins as $admin){
                
                $this->submissionEvaluationServices->addSubmissionEvaluation($admin->admin_id,$submissionId);
                
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
