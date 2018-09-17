<?php

namespace App\Services;


use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Utils
{

    // public static $baseUrlRT = 'http://apprtcongress-server/api';
    public static $baseUrlBadge = 'http://congress-file-generator_app_1';

    //public static $baseUrlBadge = 'http://137.74.165.25:8090';
    public static $baseUrlRT = 'http://137.74.165.25:3002/api';

    public static function diffMinutes($enter_time, $endCongress)
    {
        return round(abs(strtotime($enter_time) - strtotime($endCongress)) / 60, 2);
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