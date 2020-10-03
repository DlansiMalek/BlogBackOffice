<?php

/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;

use App\Models\Author;

class AuthorServices
{

    function __construct(){}

    public function saveAuthor($first_name, $last_name, $rank, $submission_id, $service_id, $etablissement_id, $email)
    {

        $author = new Author();
        $author->first_name = $first_name;
        $author->last_name = $last_name;
        $author->email = $email;
        $author->rank = $rank;
        $author->submission_id = $submission_id;
        $author->service_id = $service_id;
        $author->etablissement_id = $etablissement_id;
        $author->save();
        return $author;
    }
    public function editAuthor($existingAuthor, $author,$service,$etablissement)
    {

        $existingAuthor->rank = $author['rank'];
        $existingAuthor->service_id = $author['service_id'] == -1 ? $service : $author['service_id'];
        $existingAuthor->etablissement_id = $author['etablissement_id'] == -1 ? $etablissement : $author['etablissement_id'];
        $existingAuthor->update();
        return $existingAuthor;
    }
    public function deleteAuthor($author)
    {
        $author->delete();
    }
    public function saveAuthorsBySubmission($authors, $submission_id,$etablissements,$services)
    {


        for ($i = 0 ; $i<sizeof($authors) ; $i++){
            $this->saveAuthor(
                $authors[$i]['first_name'],
                $authors[$i]['last_name'],
                $authors[$i]['rank'],
                $submission_id,
                $authors[$i]['service_id'] == -1 ? $services[$i] : $authors[$i]['service_id'] ,
                $authors[$i]['etablissement_id'] == -1 ? $etablissements[$i] : $authors[$i]['etablissement_id'],
                $authors[$i]['email']
            );
        }
    }

    public function editAuthors($existingAuthors,$authors, $submission_id,$services,$etablissements)
    {
        //test si il exist que l'utilisateur seuelement
        if (sizeof($authors) >= 1 )  {
            //première loop pour voir les auteurs qui ont été modifié ou supprimé
            foreach ($existingAuthors as $existingAuthor) {
                $isExist = false;
                for ($i = 0 ; $i< sizeof($authors); $i++ ) {
                    if (isset($authors[$i]['author_id'])) {
                        if ($existingAuthor['author_id'] == $authors[$i]['author_id']) {
                            $isExist = true;
                            $this->editAuthor($existingAuthor, $authors[$i],$services[$i],$etablissements[$i]);
                        }
                    } 
                }
                if (!($isExist)) {
                    $this->deleteAuthor($existingAuthor);
                }
            }
            //2eme loop pour ajouter les nouveaux auteurs 
             for ($i=1;$i<sizeof($authors);$i++){
                 if (!(isset($authors[$i]['author_id']))){
                    $this->saveAuthor(
                        $authors[$i]['first_name'],
                        $authors[$i]['last_name'],
                        $authors[$i]['rank'],
                        $submission_id,
                        $authors[$i]['service_id'] == -1 ? $services[$i] : $authors[$i]['service_id'] ,
                        $authors[$i]['etablissement_id'] == -1 ? $etablissements[$i] : $authors[$i]['etablissement_id'],
                        $authors[$i]['email']);
                 }
             }

        } else {
            for ($i = 1; $i < sizeof($existingAuthors); $i++) {
                $this->deleteAuthor($existingAuthors[$i]);
            }
        }
    }

    public function getAuthorById($author_id)
    {
        return Author::where('author_id', '=', $author_id)->first();
    }

    public function getAuthorsBySubmissionId($submission_id)
    {
        return Author::where('submission_id', '=', $submission_id)->get();
    }
}
