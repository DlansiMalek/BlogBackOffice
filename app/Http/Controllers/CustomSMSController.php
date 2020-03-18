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
        return response(['response'=>'sms deleted successfuly'],200);   
       }
       return response(['response'=>'no sms found'],400);
    }

    public function getSmsById($smsId)
    {
        $sms= $this->customSmsServices->getSmsById($smsId);
        if ($sms)
        return $sms;

        return response(['response'=>'no sms found']);
    }

    public function saveCustomSMS(Request $request)
    {
        return $this->customSmsServices->saveCustomSMS($request);

    }

    public function filterUsersBySmsStatus($smsId,Request $request)
    {

       $status= $request->query('status', '');
       return $this->customSmsServices->filterUsersBySmsStatus($smsId,$status);
       
    }
    
    public function deleteUserSms($smsId,$userId){
     
        $user_sms = $this->customSmsServices->getUserSms($smsId,$userId);
        $user_sms->delete();

        return $user_sms;

    }

    
    public function sendSmsToUsers($smsId)
    {
        $users=array();
        $users=$this->customSmsServices->filterUsersBySmsStatus($smsId,0);
        if (!count($users)>=1)
        return response(['response'=>'There is no users'],400);
        $sms=$this->customSmsServices->getSmsById($smsId);
        
        foreach($users as $user){
        $this->customSmsServices->sendSmsToUsers($user,$sms);
        $user_sms=$user->user_sms[0];
        $user_sms->status=1;
        $user_sms->update();
        }    
        
        return response(['response'=>'Message sent successfully',200]);
    }

}
