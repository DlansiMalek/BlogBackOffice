<?php

/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/10/2017
 * Time: 18:37
 */

namespace App\Services;

use App\Models\Author;
use App\Models\Etablissement;
use App\Models\Service;

class AuthorServices
{

    function __construct()
    {
    }

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

    public function editAuthor($existingAuthor, $author, $service, $etablissement)
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

    public function saveAuthorsBySubmission($authors, $submission_id, $etablissements, $services)
    {


        for ($i = 0; $i < sizeof($authors); $i++) {
            $this->saveAuthor(
                $authors[$i]['first_name'],
                $authors[$i]['last_name'],
                $authors[$i]['rank'],
                $submission_id,
                $authors[$i]['service_id'] == -1 ? $services[$i] : $authors[$i]['service_id'],
                $authors[$i]['etablissement_id'] == -1 ? $etablissements[$i] : $authors[$i]['etablissement_id'],
                $authors[$i]['email']
            );
        }
    }

    public function editAuthors($existingAuthors, $authors, $submission_id, $services, $etablissements)
    {
        //test si il exist que l'utilisateur seuelement
        if (sizeof($authors) >= 1) {
            //première loop pour voir les auteurs qui ont été modifié ou supprimé
            foreach ($existingAuthors as $existingAuthor) {
                $isExist = false;
                for ($i = 0; $i < sizeof($authors); $i++) {
                    if (isset($authors[$i]['author_id'])) {
                        if ($existingAuthor['author_id'] == $authors[$i]['author_id']) {
                            $isExist = true;
                            $this->editAuthor($existingAuthor, $authors[$i], $services[$i], $etablissements[$i]);
                        }
                    }
                }
                if (!($isExist)) {
                    $this->deleteAuthor($existingAuthor);
                }
            }
            //2eme loop pour ajouter les nouveaux auteurs 
            for ($i = 1; $i < sizeof($authors); $i++) {
                if (!(isset($authors[$i]['author_id']))) {
                    $this->saveAuthor(
                        $authors[$i]['first_name'],
                        $authors[$i]['last_name'],
                        $authors[$i]['rank'],
                        $submission_id,
                        $authors[$i]['service_id'] == -1 ? $services[$i] : $authors[$i]['service_id'],
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

    public function addAuthorsExternal($submission, $item)
    {
        $startRank = 2;

        $firstNames = explode(';', isset($item['co_authors_first_names']) ? $item['co_authors_first_names'] : '');
        $lastNames = explode(';', isset($item['co_authors_last_names']) ? $item['co_authors_last_names'] : '');
        $services = explode(';', isset($item['co_authors_service']) ? $item['co_authors_service'] : '');
        $etabs = explode(';', isset($item['co_authors_etablissement']) ? $item['co_authors_etablissement'] : '');
        $emails = explode(';', isset($item['co_authors_emails']) ? $item['co_authors_emails'] : '');

        $nb = sizeof($firstNames);
        if (sizeof($lastNames) === $nb && sizeof($lastNames) === $nb && (sizeof($services) === $nb || sizeof($services) === 0) && (sizeof($etabs) === $nb || sizeof($etabs) === 0) && (sizeof($emails) === $nb || sizeof($emails) === 0)) {


            for ($i = 0; $i < sizeof($firstNames); $i++) {
                $serviceId = $this->getExternalService(isset($services[$i]) ? $services[$i] : null);
                $etablissementId = $this->getExternalEtab(isset($etabs[$i]) ? $etabs[$i] : null);
                $email = isset($email[$i]) ? $email : '';

                $this->saveAuthor($firstNames[$i], $lastNames[$i], ($startRank + $i), $submission->submission_id, $serviceId, $etablissementId, $email);
            }
        }

        return $this->getAuthorsBySubmission($submission->submission_id);
    }

    public function addPrincipalAuthor($submission, $user, $item)
    {
        $serviceId = $this->getExternalService(isset($item['author_service']) ? $item['author_service'] : null);
        $etablissementId = $this->getExternalEtab(isset($item['author_etablissement']) ? $item['author_etablissement'] : null);

        return $this->saveAuthor($user->first_name, $user->last_name, 1, $submission->submission_id, $serviceId, $etablissementId, $user->email);
    }

    public function getAuthorsBySubmission($submissionId)
    {
        return Author::where('submission_id', '=', $submissionId)
            ->get();
    }

    public function deleteAllAuthorsBySubmission($submission_id)
    {
        $authors = $this->getAuthorsBySubmission($submission_id);
        foreach ($authors as $author) {
            $author->delete();
        }
    }

    public function getServiceByLabel($label)
    {
        $name = strtolower($label);
        return Service::whereRaw('lower(label) like (?)', ["%{$name}%"])
            ->first();
    }

    public function getEtablissementByLabel($label)
    {
        $name = strtolower($label);
        return Etablissement::whereRaw('lower(label) like (?)', ["%{$name}%"])
            ->first();
    }

    public function addService($label)
    {
        $service = new Service();
        $service->label = $label;
        $service->save();
        return $service;
    }

    public function addEstablishment($label)
    {
        $etablissement = new Etablissement();
        $etablissement->label = $label;
        $etablissement->save();
        return $etablissement;
    }


    private function getExternalService($authorService)
    {
        $serviceId = null;
        if ($authorService !== null) {
            $service = $this->getServiceByLabel($authorService);
            if (!$service) {
                $service = $this->addService($authorService);
            }
            $serviceId = $service->service_id;
        }
        return $serviceId;
    }

    private function getExternalEtab($authorEtab)
    {
        $etablissementId = null;
        if ($authorEtab !== null) {
            $etablissement = $this->getEtablissementByLabel($authorEtab);
            if (!$etablissement) {
                $etablissement = $this->addEstablishment($authorEtab);
            }
            $etablissementId = $etablissement->etablissement_id;
        }
        return $etablissementId;
    }
}
