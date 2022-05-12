<?php

namespace App\Http\Controllers;

use App\Services\CongressServices;
use App\Services\MeetingServices;
use Dompdf\Dompdf;
use Illuminate\Filesystem\Filesystem;
use PDF;
use Illuminate\Http\Request;
use App\Services\UrlUtils;
use Illuminate\Support\Facades\Storage;
use ZanySoft\Zip\Zip;
use File;    


class PDFController extends Controller
{

    protected $congressServices;
    protected $meetingServices;

    public function __construct(CongressServices $congressServices, MeetingServices $meetingServices )
    {
        $this->congressServices = $congressServices;
        $this->meetingServices = $meetingServices;
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

    function generateMeetingPlanningPDF($meeting_id)
    {
        $file = new Filesystem();
        $meetingPlanning = $this->meetingServices->getMeetingPlanning($meeting_id);
        $congressId = $meetingPlanning->congress_id;
        $congress = $this->congressServices->getCongressById($congressId);

        $data = [
            'congress' => $congress,
            'meetings' => $meetingPlanning->meetings
        ];
        $pdf = PDF::loadView('meetingProgram', $data);
        $pdf->save(public_path() . "/meetingProgram.pdf");
        if ($file->exists(public_path() . "/meetingProgram.pdf")) {
            return response()->download(public_path() . "/meetingProgram.pdf")
            ->deleteFileAfterSend(true);
        } else {
            return response()->json(["error" => "dossier vide"]);
        }
    }

    function generateBadgePDF($congress_id, Request $request)
    {
       $client = new \GuzzleHttp\Client();
        $res = $client->request('POST',
            UrlUtils::getUrlBadge() . '/badge/generateParticipantsPro', [
                'json' => [
                    'participants' => $request['participants'],
                    'badgeIdGenerator' => $request['badgeIdGenerator']
                ]
            ]); 
         Storage::disk('public_uploads')->put( 'badges.zip', $res->getBody());
         $zip = Zip::open(public_path() . '/badges/badges.zip');
         $zip->extract(public_path() . '/uncompressed', '/media/generate_participant'); 
      $path = public_path('uncompressed');
      $filesInFolder = File::allFiles($path);
      foreach($filesInFolder as $key => $path){
        $files = pathinfo($path);
        $allMedia[] = $files['basename'];
      }
      if(sizeof($allMedia) > 7){
        $cataloguesNumber = 0 ;
        do {
            $take8 = array_slice($allMedia, 0, 8);
            $i = 0 ;
            foreach ($take8 as $badge) {
               rename(public_path('uncompressed/media/generate_participant/'. $badge), public_path('uncompressed/media/generate_participant/'.$i.'.png'));
             $i++;
            }
            $data = [];
            $pdf = PDF::loadView('badge-catalogue', $data, [
                'format' => 'A4-L',
                'display_mode'     => 'fullpage'
              ]);
            $pdf->save(public_path() . "/program.$cataloguesNumber.pdf");
            $k++;
            for ($j = 0; $j <= 8; $j++) {
                if(File::exists(public_path('uncompressed/media/generate_participant/'.$j.'.png'))){
                    File::delete(public_path('uncompressed/media/generate_participant/'.$j.'.png'));
                    }
            }
            $allMedia  = array_splice($allMedia, 8);
        } while (sizeof($allMedia) > 0);
      }
      File::delete(public_path('badges/badges.zip'));
      File::deleteDirectory(public_path('badges'));
      File::deleteDirectory(public_path('uncompressed'));
 
      $zip = Zip::create('file.zip');
      for ($y = 0; $y <= 8; $y++) {
       $zip->add(public_path("/program.$y.pdf"));
            }
            $pathZip ="file.zip";

                return response()->download($pathZip)
                    ->deleteFileAfterSend(true);
    }
     
}