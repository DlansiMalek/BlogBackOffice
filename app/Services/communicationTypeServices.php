<?php


namespace App\Services;

use App\Models\communicationType;

class CommunicationTypeService {

    public function getAllCommunicationType() {
        return communicationType::all();
    }
    
    public function getCommunicationTypeById($communication_type_id) {
        return communicationType::where('communication_type_id','=',$communication_type_id)
        ->first();
    }
}