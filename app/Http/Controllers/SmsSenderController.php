<?php

namespace App\Http\Controllers;

use App\Services\SmsServices;
use Illuminate\Http\Request;

class SmsSenderController extends Controller

{
    protected $smsServices;

    function __construct(SmsServices $smsServices){
        $this->smsServices=$smsServices;
    }

    public function  authentifcationSms($congressId){
        $auth=$this->smsServices->authentificationSms($congressId);
        return $auth;
    }
    public function sendSms($congressId,Request $request){
        $message=$this->smsServices->sendSms($congressId,$request);
        return $message;
        

   
     
    }
    
}
