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
            return "http://appvoting-server:3000";
        }
        return "http://137.74.165.25:3001/";
    }

    public static function getUrlEventizerWeb()
    {
        if (App::environment() == 'test') {
            return "http://localhost:4200";
        }
        if (App::environment() == 'prod') {
            return "https://eventizer.vayetek.com";
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
        return "http://apprtcongress-server:3000/api";
    }

    public static function getUrlPaiement()
    {
        if (App::environment() == 'test') {
            return "http://localhost:8080";
        }
        if (App::environment() == 'prod') {
            return "http://paiement-api:8080";
        }
        return "http://137.74.165.25:3007";
    }

    public static function getUrlBadge()
    {
        if (App::environment() == 'test') {
            return "http://137.74.165.25:8090";
        }
        if (App::environment() == 'prod') {
            return "http://congress-file-generater-app:5000";
        }
        return "http://137.74.165.25:8090";
    }

    public static function getBaseUrl()
    {
        if (App::environment() == 'test') {
            return "http://localhost:8888";
        }
        if (App::environment() == 'prod') {
            return "https://eventizer-api.vayetek.com/api";
        }
        return "http://localhost:8888";
    }

}