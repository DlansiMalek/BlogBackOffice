<?php

namespace App\Http\Controllers;

use App\Services\CongressServices;
use Dompdf\Dompdf;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use PDF;

class PDFController extends Controller
{

    protected $congressServices;

    public function __construct(CongressServices $congressServices)
    {
        $this->congressServices = $congressServices;
    }

    function generateProgramPDF($congress_id)
    {
        $file = new Filesystem();
        $congress = $this->congressServices->getCongressById($congress_id);

        $days_in_french = array("Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche");
        foreach ($congress->accesss as $a) {
            $a->day = $days_in_french[(int)date('N', strtotime($a->start_date)) - 1] . date(' d/m/Y', strtotime($a->start_date));
            $a->time = date('H:i', strtotime($a->start_date));
        }

        $data = [
            'congress' => $congress,
            'accesses' => $congress->accesss->groupby('start_date')->toArray()
        ];

        $pdf = PDF::loadView('program', $data);
        $pdf->save(public_path() . "/program.pdf");
        if ($file->exists(public_path() . "/program.pdf")) {
            return response()->download(public_path() . "/program.pdf")
                ->deleteFileAfterSend(true);
        } else {
            return response()->json(["error" => "dossier vide"]);
        }
    }
}