<?php


namespace App\Services;

use App\Models\CommunicationType;

class CommunicationTypeService
{

    public function getAllCommunicationType()
    {
        return CommunicationType::all();
    }

    public function getCommunicationTypeById($communication_type_id)
    {
        return CommunicationType::where('communication_type_id', '=', $communication_type_id)
            ->first();
    }
}
