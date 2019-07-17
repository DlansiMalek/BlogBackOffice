<?php


namespace App\Services;


use App\Models\Mail;
use App\Models\MailType;

class MailServices
{

    public function getAllMailTypes()
    {
        return MailType::all();
    }

    public function getMailTypeById($mailTypeId)
    {
        return MailType::find($mailTypeId);
    }

    public function getMailByTypeAndCongress($mailTypeId, $congressId)
    {
        return Mail::where('mail_type_id', '=', $mailTypeId)
            ->where('congress_id', '=', $congressId)
            ->first();
    }
}