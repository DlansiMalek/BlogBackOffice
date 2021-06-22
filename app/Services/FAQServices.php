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

    public function addFAQ($faq ,Request $request)
    {
        if (!$faq)
          $faq = new FAQ();
        
        $faq->question  = $request->input("question");
        $faq->response  = $request->input("response");
        $faq->stand_id  = $request->input('stand_id');
        $faq->save();
        return $faq;
    }

    public function getStandFAQ($stand_id)
    {
        return FAQ::where('stand_id', '=', $stand_id)->get();
    }
}