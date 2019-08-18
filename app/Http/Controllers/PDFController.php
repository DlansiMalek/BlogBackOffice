<?php

namespace App\Http\Controllers;

use App\Services\CongressServices;
use Dompdf\Dompdf;


class PDFController extends Controller
{

    protected $congressServices;

    public function __construct(CongressServices $congressServices)
    {
        $this->congressServices = $congressServices;
    }

    function generateProgramPDF($congress_id)
    {
        $congress = $this->congressServices->getCongressById($congress_id);

        $days_in_french = array("Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche");
        foreach ($congress->accesss as $a) {
            $a->day = $days_in_french[(int)date('N', strtotime($a->start_date)) - 1] . date(' d/m/Y', strtotime($a->start_date));
            $a->time = date('H:i', strtotime($a->start_date));
        }
        $dompdf = new Dompdf(array('enable_remote' => true));
        $dompdf->loadHtml(view('program', ['congress' => $congress, 'accesses' => $congress->accesss->groupby('start_date')->toArray()]));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream();
    }
}