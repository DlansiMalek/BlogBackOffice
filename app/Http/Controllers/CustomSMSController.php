<?php

namespace App\Http\Controllers;

use App\Services\CustomSmsServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
     if ( !$sms=$this->customSmsServices->getSmsById($smsId))
        return response(['response'=>'no sms found'],400);

        $sms->delete();
        return response(['response'=>'sms deleted successfuly'],200);   
    }

    public function getSmsById($smsId)
    {
        if (!$sms=$this->customSmsServices->getSmsById($smsId))
        return response(['response'=>'no sms found']);
        return $sms;
    }

    public function saveCustomSMS(Request $request)
    {   
        $validator = Validator::make($request->all(),[
            'title'=>'required',
            'content'=>'required',
            'userIds'=>'required',
        ]);
        if ($validator->fails())
        return $validator->errors();

        return $this->customSmsServices->saveCustomSMS($request);
       

    }

    public function filterUsersBySmsStatus($smsId,Request $request)
    {
       
        $status= $request->query('status', '');
         if (!$users= $this->customSmsServices->filterUsersBySmsStatus($smsId,$status))
        return response(['No users found',404]);

        return $users;
       
    }
  

    public function deleteUserSms($smsId,$userId){

        if (! $user_sms = $this->customSmsServices->getUserSms($smsId,$userId))
        return response(['No user_sms found',404]);
    
        $user_sms->delete();
        return $user_sms;   

    }

    
    public function sendSmsToUsers($smsId)
    {
        $users=array();
        $users=$this->customSmsServices->filterUsersBySmsStatus($smsId,0);
        if (!count($users)>=1)
        return response(['response'=>'There is no users'],400);

        if (!$sms=$this->customSmsServices->getSmsById($smsId))
        return response(['response'=>'There is no sms'],400);

     
        foreach($users as $user){
        $this->customSmsServices->sendSmsToUsers($user,$sms);
        $user_sms=$user->user_sms[0];
        $user_sms->status=1;
        $user_sms->update();
        }    
        
        return response(['response'=>'Message sent successfully',200]);
    }

}
