<?php


namespace App\Services;

use App\Models\CustomSMS;

class CustomSmsServices
{

    public function getSMSList(){
        return CustomSMS::all();
    }
    
    public function getSmsById($smsId){
         return CustomSMS::where('custom_sms_id','=',$smsId)->first();
    }
}