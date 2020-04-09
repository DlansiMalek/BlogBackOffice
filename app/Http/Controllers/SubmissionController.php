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
    protected $authorServices;


    function __construct(
        SubmissionServices $submissionServices,
        AuthorServices $authorServices
        )
    {
        $this->submissionServices=$submissionServices;
        $this->authorServices=$authorServices;
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
        $this->authorServices->saveAuthorsBySubmission($request->input('authors'),$submission->submission_id);

        $this->submissionServices->affectSubmissionToEvaluators(
        $submission->theme_id,
        $submission->congress_id,
        $submission->submission_id
        );

        $this->submissionServices->saveResourceSubmission($request->input('resourceIds'),$submission->submission_id);
        
        return response()->json(['response'=>'Enregistrement avec succes'],200);
         }

         catch (Exception $e) {

            Log::info($e->getMessage());
            return response()->json(['response'=>$e->getMessage()],400);
        }

    }
}
