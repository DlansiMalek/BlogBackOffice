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


}