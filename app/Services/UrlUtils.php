<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 08/05/2019
 * Time: 11:53
 */

namespace App\Services;


use Illuminate\Support\Facades\App;

class UrlUtils
{

    public static function getVayeVotingUrl()
    {
        if (App::environment() == 'test') {
            return "http://localhost:3000";
        }
        if (App::environment() == 'prod') {
            return "https://ws.vayetek.com";
        }
        if (App::environment() == 'dev') {
            return "https://ws.vayetek.com";
        }
        return "https://ws.vayetek.com";
        //return "http://localhost:3000";
    }

    public static function getMeetEventizerUrl()
    {
        return "https://meet.eventizer.io";
    }

    public static function getUrlEventizerWeb()
    {
        if (App::environment() == 'test') {
            return "http://localhost:4200";
        }
        if (App::environment() == 'prod') {
            return "https://organizer.eventizer.io";
        }
        if (App::environment() == 'dev') {
            return "https://dev.organizer.eventizer.io";
        }
        return "http://localhost:4200";
    }

    public static function getUrlRT()
    {
        if (App::environment() == 'test') {
            return "http://137.74.165.25:3002/api";
        }
        if (App::environment() == 'prod') {
            return "http://apprtcongress-server:3000/api";
        }
        if (App::environment() == 'dev') {
            return "http://apprtcongress-server:3000/api";
        }
        return "http://137.74.165.25:3002/api";
    }

    public static function getUrlPaiement()
    {
        if (App::environment() == 'test') {
            return "http://localhost:8080";
        }
        if (App::environment() == 'prod') {
            return "https://paiement-api.vayetek.com";
        }
        if (App::environment() == 'dev') {
            return "https://paiement-api.vayetek.com";
        }
        return "https://paiement-api.vayetek.com";
    }

    public static function getUrlBadge()
    {
        if (App::environment() == 'test') {
            return "https://congress-file-generater.vayetek.com";
        }
        if (App::environment() == 'prod') {
            return "https://congress-file-generater.vayetek.com";
        }
        if (App::environment() == 'dev') {
            return "https://congress-file-generater.vayetek.com";
        }
        return "https://congress-file-generater.vayetek.com";
    }

    public static function getBaseUrl()
    {
        if (App::environment() == 'test') {
            return "http://localhost:8888/api";
        }
        if (App::environment() == 'prod') {
            return "https://api.eventizer.io/api";
        }
        if (App::environment() == 'dev') {
            return "https://dev.api.eventizer.io/api";
        }
        return "http://localhost:8888/api";
    }

    public static function getBaseImgUrl()
    {
        if (App::environment() == 'test') {
            return "http://localhost:8888";
        }
        if (App::environment() == 'prod') {
            return "http://eventizer-api-master-web";
        }
        if (App::environment() == 'dev') {
            return "http://eventizer-api-develop-web";
        }

        return "http://localhost:8888";
    }

    public static function getBaseUrlFrontOffice()
    {
        if (App::environment() == 'test') {
            return "http://localhost:4200/#";
        }
        if (App::environment() == 'prod') {
            return 'https://app.eventizer.io/#';
        }
        if (App::environment() == 'dev') {
            return 'https://dev.app.eventizer.io/#';
        }

        return "http://localhost:4200/#";
    }

    public static function getBaseCurrencyRates()
    {
        return "https://free.currconv.com/api/v7";
    }

    public static function getBaseUrlDiscoveryRecording()
    {
        if (App::environment() == 'prod') {
            return 'https://discovery.recordings.meet.eventizer.io';
        }
        if (App::environment() == 'dev') {
            return 'https://dev.discovery.recordings.meet.eventizer.io';
        }

        return "https://discovery.recordings.meet.eventizer.io";
    }


    public static function getUrlSendPulse()
    {
        return "https://api.sendpulse.com";
    }

    public static function getUrlSendInBlue()
    {
        return "https://api.sendinblue.com/v3/smtp/email";
    }

    public static function getElasticBaseUrl()
    {
        return "https://" . env('ELASTIC_USER') . ":" . env('ELASTIC_PASSWORD') . '@elastic.tracking.master.vayetek.com';
    }

    public static function getFilesUrl()
    {
        if (App::environment() == 'test') {
            return "https://eventizer-dev.s3.eu-west-3.amazonaws.com/";
        }
        if (App::environment() == 'prod') {
            return "https://eventizer-prod.s3.eu-west-3.amazonaws.com/";
        }
        if (App::environment() == 'dev') {
            return "https://eventizer-dev.s3.eu-west-3.amazonaws.com/";
        }

        return "https://".env('AWS_BUCKET').".s3.".env('AWS_REGION').".amazonaws.com//";
    }

    public static function getPaymentLinkFrontoffice($url_frontoffice, $token, $congressId)
    {
        return $url_frontoffice . "user-profile/payment/upload-payement?token=" . $token . "&congressId=" . $congressId;
    }

    public static function getPaymentLinkBackoffice($url_backoffice, $user_id, $token, $congressId)
    {
        return $url_backoffice . "/#/auth/user/" . $user_id . "/upload-payement?token=" . $token . "&congressId=" . $congressId;
    }

    public static function getBaseUrlPWA()
    {
        if (App::environment() == 'test') {
            return "http://localhost:4200/#";
        }
        if (App::environment() == 'prod') {
            return 'https://participant.eventizer.io/#';
        }
        if (App::environment() == 'dev') {
            return 'https://dev.participant.eventizer.io/#';
        }

        return "http://localhost:4200/#";
    }
}
