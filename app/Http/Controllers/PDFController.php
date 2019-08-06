<?php

namespace App\Http\Controllers;

use App\Models\Congress;
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
        $congress  = $this->congressServices->getCongressById($congress_id);

        return view('program', ['congress' => $congress]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('program', ['congress' => $congress]));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream();
    }
}