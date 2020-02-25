<?php

namespace App\Services;

use Illuminate\Support\Str;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Utils
{


    // public static $baseUrl = 'http://localhost/congress-backend-modules/public/api/';

    public static $baseUrl = 'https://api.eventizer.io/api/';

    public static function diffMinutes($enter_time, $endCongress)
    {
        return round(abs(strtotime($enter_time) - strtotime($endCongress)) / 60, 2);
    }

    public static function getFullName($first_name, $last_name)
    {
        return ucfirst($first_name) . " " . strtoupper($last_name);
    }
    public static function getMobileFormatted($mobile){
      if (Str::contains($mobile,['+216','00216']))
        return $mobile;
      
      return '+216'.$mobile;
      
    }
    public static function getSmsMessage($qrCode,$first_name,$last_name,$congress_name,$congress_date,$mobile_committee){
        
        return 
        '        Inscription validée
        '
        
        .'Evénement :'.$congress_name.'
        '
        
        .'Date :'.$congress_date.'
        '
        .'Nom et prénom :'.$first_name.' '.$last_name.'
        '
        .'Code d`accès :'.$qrCode.' Veuillez présenter votre code à l`accueil le jour de l`événement.
        '
 
        .'Comité d`organisation :'.$mobile_committee.' 
        '
        
        .'Hotline technique :'.'numéro eventizer';
        
        
        
    }
    public static function convertDateFrench($date)
    {

        $res = null;
        if ($date) {
            $res = (new Carbon($date))->formatLocalized('%d %B %Y');
            $res = str_replace('January', 'Janvier', $res);
            $res = str_replace('February', 'Février', $res);
            $res = str_replace('March', 'Mars', $res);
            $res = str_replace('April', 'Avril', $res);
            $res = str_replace('May', 'Mai', $res);
            $res = str_replace('June', 'Jui', $res);
            $res = str_replace('July', 'Juiller', $res);
            $res = str_replace('August', 'Aout', $res);
            $res = str_replace('September', 'September', $res);
            $res = str_replace('October', 'Octobre', $res);
            $res = str_replace('November', 'Novembre', $res);
            $res = str_replace('December', 'Décembre', $res);
        }
        return $res;

    }

    public static function getTimeFromDateTime($date)
    {
        return date('H:i', strtotime($date));
    }

    public static function objArraySearch($array, $index, $value)
    {
        foreach ($array as $arrayInf) {
            if ($arrayInf->{$index} == $value) {
                return $arrayInf;
            }
        }
        return null;
    }

    public static function verifyImg(string $extension)
    {
        return $extension == 'jpeg' || $extension == 'png' || $extension == 'jpg' || $extension == 'svg' || $extension == 'gif';
    }

    public static function getAttestationByPrivilegeId($attestations, int $privilegeId)
    {
        for ($i = 0; $i < sizeof($attestations); $i++) {
            if ($attestations[$i]->privilege_id == $privilegeId) {
                return $attestations[$i]->attestation_generator_id;
            }
        }
        return null;
    }

    public static function mapDataByKey($data, string $key)
    {
        return array_map(function ($object) use ($key) {
            return $object[$key];
        }, json_decode($data, true));
    }

    public static function getDefaultMailNotifNewRegister()
    {
        return '<p>Bonjour,</p><p>Vous avez re&ccedil;u une nouvelle inscription dans votre &eacute;v&eacute;nement<strong>&nbsp;{{$congress-&gt;name}}</strong> au nom de :</p><p><strong>Nom &amp; pr&eacute;nom :</strong>&nbsp; {{$participant-&gt;last_name}} {{$participant-&gt;first_name}}</p><p><strong>Email :</strong> {{$participant-&gt;email}}</p><p><strong>T&eacute;l&eacute;phone :</strong>&nbsp; &nbsp;{{$participant-&gt;mobile}}</p><p><strong>Date de l&#39;inscription :</strong> {{$participant-&gt;registration_date}}</p><p><strong>Ateliers :</strong> {{$participant-&gt;accesses}}</p><p><strong>Montant &agrave; payer :</strong> {{$userPayment-&gt;price}} DT</p><p><br></p><p>Vous pouvez acc&eacute;der au back-office &agrave; travers ce lien pour suivre et valider les inscriptions. &nbsp;</p><p>En cas de probl&egrave;me, n&#39;h&eacute;sitez pas &agrave; contacter l&#39;&eacute;quipe Support d&#39;Eventizer &nbsp;disponible 24/7.&nbsp;</p><p><br></p><p>SUPPORT Eventizer<a href="mailto:contact@eventizer.io" rel="noopener noreferrer"></a></p><p><a href="mailto:contact@eventizer.io" rel="noopener noreferrer">contact@eventizer.io</a></p><p>+216 98 613 158&nbsp;</p>';
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

    public static function groupBy($key, $data)
    {
        $result = array();
        foreach ($data as $element) {
            $result[$element[$key]][] = $element;
        }

        return $result;
    }


}
