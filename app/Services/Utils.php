<?php

namespace App\Metiers;


use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Utils
{

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
        if (!$qrcodeName) {
            $qrcodeName = "qrcode.png";
        }
        QrCode::format("png")
            ->size(200)
            ->generate($QRcode, public_path() . '/QRcode/' . $qrcodeName);
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