<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;

use App\Models\Author;

class AuthorServices{

    protected $submissionServices;

    function __construct(SubmissionServices $submissionServices)
    {
        $this->submissionServices=$submissionServices;
    }

    public function saveAuthor($first_name,$last_name,$rank,$submission_id,$service_id,$etablissement_id){

        $author=new Author();
        $author->first_name=$first_name;
        $author->last_name=$last_name;
        $author->rank=$rank;
        $author->submission_id=$submission_id;
        $author->service_id=$service_id;
        $author->etablissement_id=$etablissement_id;
        $author->save();
        return $author;
    }

    public function saveAuthorsBySubmission($authors,$submission_id)
    {
        if(!$submission=$this->submissionServices->getSubmissionById($submission_id)){
            return response()->json(['response'=>'no submission found'],400);
        }
        foreach($authors as $author){
            $this->saveAuthor(
                $author['first_name'],
                $author['last_name'],
                $author['rank'],
                $submission_id,
                $author['service_id'],
                $author['etablissement_id'],

            );
        }
    }

}