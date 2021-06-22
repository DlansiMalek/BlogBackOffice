<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StandServices;
use App\Services\FAQServices;
use Illuminate\Support\Facades\Log;

class FAQController extends Controller
{
    protected $standServices;
    protected $faqServices;

    function __construct(StandServices $standServices,FAQServices $faqServices)
    {
        $this->standServices = $standServices;
        $this->faqServices   = $faqServices;
    }

    function addFAQ(Request $request)
    {
        if (!$request->has(['question'])) {
            return response()->json(["message" => "invalid request", "required inputs" => ['question']], 404);
        }
        $stand = null;
        if ($request->has('stand_id')) {
            if (!$stand = $this->standServices->getStandById($request->input('stand_id'))) {
                return response()->json(["message" => "stand not found"], 404);
            }
        }
        $faq = null;
        if ($request->has('FAQ_id')) {
            $faq = $this->faqServices->getFAQById($request->input('FAQ_id'));
        }
        $faq = $this->faqServices->addFAQ($faq , $request);

        return response()->json($this->faqServices->getFAQById($faq->FAQ_id));
    }

    public function deleteFAQ($FAQ_id)
    {
        if (!$faq = $this->faqServices->getFAQById($FAQ_id)) {
            return response()->json('no faq found', 404);
        }    
        $this->FAQServices->deleteFAQ($faq);
        return response()->json(['response' => 'faq deleted'], 200);
    }
    public function getFAQById($FAQ_id)
    {
        return $this->faqServices->getFAQById($FAQ_id);
    }

    public function getStandFAQs($congress_id, $stand_id)
    {
        if (!$Stand = $this->standServices->getStandById($stand_id)) {
            return response()->json(['response' => 'Stand not found', 404]);
        }
        $standFaqs = $this->faqServices->getStandFAQ($stand_id);
        return response()->json($standFaqs, 200);
    }
}
