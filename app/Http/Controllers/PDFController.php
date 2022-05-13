<?php

namespace App\Http\Controllers;

use App\Services\CongressServices;
use App\Services\MeetingServices;
use App\Services\SharedServices;
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
    protected $sharedServices;

    public function __construct(CongressServices $congressServices, MeetingServices $meetingServices,
        SharedServices $sharedServices)
    {
        $this->congressServices = $congressServices;
        $this->meetingServices = $meetingServices;
        $this->sharedServices = $sharedServices;
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
        ini_set('max_execution_time', 1500); //9 minutes

        // create pdf folder
        $folderPath = storage_path('pdf');
        File::deleteDirectory($folderPath);
        mkdir($folderPath);

        // save badges
        $this->sharedServices->saveCatalogBadges($request);

        // extract zip
        $uncompressedDir = public_path('uncompressed');
        $pathUncompressed = '/media/generate_participant';
        $zipPath = storage_path('app/badges.zip');
        $zip = Zip::open($zipPath);
        $zip->extract($uncompressedDir, $pathUncompressed);

        $filesInFolder = File::allFiles($uncompressedDir);        
        foreach ($filesInFolder as $key => $path) {
            $files = pathinfo($path);
            $allMedia[] = $files['basename'];
        }
        $pathFiles = public_path('uncompressed/media/generate_participant/');
        for ($re = 0; $re <= sizeof($allMedia) - 1; $re++) {
            rename($pathFiles . $allMedia[$re], $pathFiles . strstr($allMedia[$re], '_'));
            $allMedia[$re] = strstr($allMedia[$re], '_');
        }
        natsort($allMedia);
        if (sizeof($allMedia) > 7) {
            $cataloguesNumber = 0;
            do {
                $take8 = array_slice($allMedia, 0, 8);
                $i = 0;
                foreach ($take8 as $badge) {
                    rename($pathFiles . $badge, $pathFiles . $i . '.png');
                    $i++;
                }
                $pdf = PDF::loadView('badge-catalogue', [], [
                    'format' => 'A4-L',
                    'display_mode'     => 'fullpage'
                ]);
                $pdf->save(storage_path() . "/pdf/catalogue.$cataloguesNumber.pdf");
                $cataloguesNumber++;
                $toremove =  8 - sizeof($allMedia);
                for ($j = 0; $j <= $toremove; $j++) {
                    $pathImg = $pathFiles . $j . '.png';
                    if (File::exists($pathImg)) {
                        File::delete($pathImg);
                    }
                }
                $allMedia  = array_splice($allMedia, 8);
            } while (sizeof($allMedia) > 0);
        }

        // build zip file
        $resultFilePath = storage_path('file.zip');
        $zip = Zip::create($resultFilePath);
        for ($y = 0; $y <= $cataloguesNumber; $y++) {
            $zip->add($folderPath . "/catalogue.$y.pdf");
        }
        $zip->close();

        // delete temp directories
        File::delete($zipPath);
        File::deleteDirectory(public_path('uncompressed'));
        File::deleteDirectory(storage_path('pdf'));

        if ($resultFilePath) {
            return response()->download($resultFilePath)->deleteFileAfterSend(true);
        }
    }
     
}