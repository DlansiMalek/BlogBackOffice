<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
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

    function addFAQ($congress_id, $standId, Request $request)
    {
        if (!$stand = $this->standServices->getStandById($standId)) {
            return response()->json(["message" => "stand not found"], 404);
        }
        $faqs = $request->all();
        foreach ($faqs as $faq) {
            if (array_key_exists('faq_id', $faq)) {
                $faquestion = $this->faqServices->getFAQById($faq['faq_id']);
            } else {
                $faquestion = new FAQ();
            }
            $faquestions = $this->faqServices->addFAQ($faquestion, $faq);
        }

        return response()->json($this->faqServices->getStandFAQ($standId), 200);
    }

    public function deleteFAQ($congress_id,$stand_id,$faq_id)
    {
        if (!$faq = $this->faqServices->getFAQById($faq_id)) {
            return response()->json('no faq found', 404);
        }    
        $this->faqServices->deleteFAQ($faq);
        return response()->json(['response' => 'faq deleted'], 200);
    }
    public function getFAQById($faq_id)
    {
        return $this->faqServices->getFAQById($faq_id);
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
