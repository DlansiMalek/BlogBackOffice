<?php

namespace App\Services;

use App\Models\Etablissement;

class EstablishmentServices {

    public function addEstablishment($label)
    {
        $etablissement = new Etablissement();
        $etablissement->label = $label;
        $etablissement->save();
        return $etablissement;
    }
    public function getEstablishmentByLabel($label) {
        return Etablissement::where('label','=',$label)->first();
    }
    public function addMultipleEstablishmentsFromAuthors($authors) {
        $etablissementsIds = [];
        foreach ($authors as $author) {
                if ($author['etablissement_id'] == '-1') {
                    if (!$etablissement = $this->getEstablishmentByLabel($author['customEstablishment'])) {
                            $etablissement = $this->addEstablishment($author['customEstablishment']) ;
                    }
                    array_push($etablissementsIds, $etablissement->etablissement_id);
                } else {
                    array_push($etablissementsIds, -1);
                }
        }
        return $etablissementsIds;
    }

    public function getEtablissementsByCongressId($congressId)
    {
        $etablissements = Etablissement::where('congress_id', '=', $congressId)
        ->orWhere('congress_id', '=', null)
        ->get();
        return $etablissements;
    }

    public function addExternalEstablishment($request ,$congressId)
    {
        $etablissement = new Etablissement();
        $etablissement->label = $request->label;
        $etablissement->congress_id = $congressId;
        $etablissement->save();
        return $etablissement;
    }

}