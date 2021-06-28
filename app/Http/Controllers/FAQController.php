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

    function addFAQ(Request $request)
    {
        $faqs = $request->all();
        foreach ($faqs as $faq) {
            $stand_id = (int)$faq['stand_id'];
            if (!$stand = $this->standServices->getStandById($stand_id)) {
                return response()->json(["message" => "stand not found"], 404);
            }
            if (array_key_exists('FAQ_id', $faq)) {
                $faquestion = $this->faqServices->getFAQById($faq['FAQ_id']);
            } else {
                $faquestion = new FAQ();
            }
            $faquestions = $this->faqServices->addFAQ($faquestion, $faq);
        }

        return response()->json($this->faqServices->getStandFAQ($stand_id));
    }

    public function deleteFAQ($congress_id,$stand_id,$FAQ_id)
    {
        if (!$faq = $this->faqServices->getFAQById($FAQ_id)) {
            return response()->json('no faq found', 404);
        }    
        $this->faqServices->deleteFAQ($faq);
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
