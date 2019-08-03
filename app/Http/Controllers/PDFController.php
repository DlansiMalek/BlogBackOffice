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
//
//        $accesses = $congress->accesss->groupby('day')->toArray();
//        $res = [];
//        foreach (array_keys($accesses) as $key){
//            $tempItem = (object) $accesses[$key];
//            $times = [];
//            foreach ($tempItem as $i){
//                if (!array_has($times, $i['time'])){
//                    array_push($times, $i['time']);
//                }
//                $times->sort()
//            }
//            $res[$key] = $tempItem;
//        }

//        return view('program', ['congress' => $congress, 'accesses' => $congress->accesss->groupby('start_date')->toArray()]);
        $dompdf = new Dompdf(array('enable_remote' => true));
        $dompdf->loadHtml(view('program', ['congress' => $congress, 'accesses' => $congress->accesss->groupby('start_date')->toArray()]));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream();
    }
}