<?php

namespace App\Services;

 
use App\Models\FAQ;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Illuminate\Support\Facades\Log;

class FAQServices
{
    public function getAll()
    {
        return FAQ::all();
    }

    public function getFAQById($FAQ_id)
    {
        return FAQ::find($FAQ_id);
    }

    public function deleteFAQ($FAQ)
    {
        return $FAQ->delete();
    }

    public function addFAQ($faquestion, $faq)
    {
        if (!$faquestion) {
            $faquestion = new FAQ();
        }
        $faquestion->question  = strval($faq['question']);
        $faquestion->response  = strval($faq['response']);
        $faquestion->stand_id  = (int)$faq['stand_id'];
        $faquestion->save();
        return true;
    }

    public function getStandFAQ($stand_id)
    {
        return FAQ::where('stand_id', '=', $stand_id)->get();
    }
}