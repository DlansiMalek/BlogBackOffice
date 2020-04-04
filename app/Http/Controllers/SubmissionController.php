<?php

namespace App\Http\Controllers;

use App\Services\AuthorServices;
use App\Services\SubmissionServices;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    private $submissionServices;
    private $autherServices;
    function __construct(SubmissionServices $submissionServices, AuthorServices $authorServices)
    {   
      $this->submissionServices=$submissionServices;
      $this->autherServices=$authorServices;
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

}
