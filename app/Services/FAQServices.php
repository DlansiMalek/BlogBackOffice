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

    public function getFAQById($faq_id)
    {
        return FAQ::find($faq_id);
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
        $faquestion->question  =  $faq['question'];
        $faquestion->response  =  $faq['response'];
        $faquestion->stand_id  =  $faq['stand_id'];
        $faquestion->save();
        return $faquestion;
    }

    public function getStandFAQ($stand_id)
    {
        return FAQ::where('stand_id', '=', $stand_id)->get();
    }
}