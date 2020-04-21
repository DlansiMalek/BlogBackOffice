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

    protected $submissionServices;

    function __construct(SubmissionServices $submissionServices)
    {
        $this->submissionServices = $submissionServices;
    }

    public function saveAuthor($first_name, $last_name, $rank, $submission_id, $service_id, $etablissement_id)
    {

        $author = new Author();
        $author->first_name = $first_name;
        $author->last_name = $last_name;
        $author->rank = $rank;
        $author->submission_id = $submission_id;
        $author->service_id = $service_id;
        $author->etablissement_id = $etablissement_id;
        $author->save();
        return $author;
    }
    public function editAuthor($id, $rank)
    {

        $author = $this->getAuthorById($id);
        $author->rank = $rank;
        $author->update();
        return $author;
    }
    public function deleteAuthor($author_id)
    {
        $author = $this->getAuthorById($author_id);
        $author->delete();
    }
    public function saveAuthorsBySubmission($authors, $submission_id)
    {
        if (!$submission = $this->submissionServices->getSubmissionById($submission_id)) {
            return response()->json(['response' => 'no submission found'], 400);
        }

        foreach ($authors as $author) {
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

    public function editAuthors($authors, $submission_id)
    {

        $existingAuthors = $this->getAuthorsBySubmissionId($submission_id);
        //test si il exist que l'utilisateur seuelement
        if (sizeof($authors) > 1 )  {
            //première loop pour voir les auteurs qui ont été modifié ou supprimé
            foreach ($existingAuthors as $existingAuthor) {
                $isExist = false;
                foreach ($authors as $author) {
                    if (isset($author['author_id'])) {
                        if ($existingAuthor['author_id'] == $author['author_id']) {
                            $isExist = true;
                            $this->editAuthor(
                                $author['author_id'],
                                $author['rank'],

                            );
                        }
                    } 
                }
                if (!($isExist)) {
                    $this->deleteAuthor($existingAuthor['author_id']);
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
                        $authors[$i]['service_id'],
                        $authors[$i]['etablissement_id'],
                    );
                 }
             }

        } else {
            for ($i = 1; $i < sizeof($existingAuthors); $i++) {
                $this->deleteAuthor($existingAuthors[$i]['author_id']);
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
