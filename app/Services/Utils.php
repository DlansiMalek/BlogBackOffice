<?php

namespace App\Services;


use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Utils
{

    const baseUrlWEB = "https://congress.vayetek.com";
    public static $baseUrlRT = 'http://apprtcongress-server:3000/api';
    public static $baseUrlPaiement = 'http://localhost:8080';
    // public static $baseUrlPaiement = 'http://localhost:8080';
//    public static $baseUrlBadge = 'http://congress-file-generater-app:5000';
    //public static $baseUrlBadge = 'http://congress-file-generater.vayetek.com';

    public static $baseUrlBadge = 'http://137.74.165.25:8090';

    //public static $baseUrlRT = 'http://137.74.165.25:3002/api';

    public static function diffMinutes($enter_time, $endCongress)
    {
        return round(abs(strtotime($enter_time) - strtotime($endCongress)) / 60, 2);
    }

    public static function getFullName($first_name, $last_name)
    {
        return ucfirst($first_name) . " " . strtoupper($last_name);
    }

    public static function convertDateFrench($date)
    {
        return (new Carbon($date))->formatLocalized('%d %B %Y');
    }

    public static function getTimeFromDateTime($date)
    {
        return date('H:i', strtotime($date));
    }

    function base64_to_jpeg($base64_string, $output_file)
    {
        $ifp = fopen($output_file, "wb");
        // $data = explode(',', $base64_string);
        fwrite($ifp, base64_decode($base64_string));
        fclose($ifp);

        return $output_file;
    }

    public static function generateQRcode($QRcode, $qrcodeName = null)
    {

        QrCode::format("png")
            ->size(200)
            ->generate($QRcode, public_path() . "/" . $qrcodeName);
    }

    public static function generateCode($id, $length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString . $id;
    }

}
