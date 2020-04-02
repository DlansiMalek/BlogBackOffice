<?php

namespace App\Http\Controllers;

use App\Services\SubmissionServices;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    private $submissionServices;

    function __construct(SubmissionServices $submissionServices)
    {   
      $this->submissionServices=$submissionServices;
    }

    public function addSubmission(Request $request)
    {
        if (!($request->has('title') && $request->has('type') && $request->has('prez_type') 
            && $request->has('description') && $request->has('author_id') && $request->has('congress_id') && $request->has('theme_id'))){
            return response()->json(['response'=>'bad request'],400);
        }
        return $this->submissionServices->addSubmission(
            $request->input('title'),
            $request->input('type'),
            $request->input('prez_type'),
            $request->input('description'),
            $request->input('author_id'),
            $request->input('congress_id'),
            $request->input('theme_id')
            
        );
    }

}
