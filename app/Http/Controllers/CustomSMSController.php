<?php

namespace App\Http\Controllers;

use App\Services\CustomSmsServices;
use Illuminate\Http\Request;

class CustomSMSController extends Controller
{

    protected $customSmsServices;
     
    function __construct(CustomSmsServices $customSmsServices)
    {
        $this->customSmsServices=$customSmsServices;
       
    }

    public function getListSMS()
    {
        return $this->customSmsServices->getSMSList();
    }

    public function deleteSMS($smsId)
    {
       $sms= $this->customSmsServices->getSmsById($smsId);
       if ($sms){
        $sms->delete();
        return response(['response'=>'sms deleted successfuly']);   
       }
       return response(['response'=>'no sms found']);
       
      
   
    }


}
