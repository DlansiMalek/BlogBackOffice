<?php


namespace App\Services;

use App\Models\CustomSMS;
use App\Models\UserSms;
use Illuminate\Http\Request;

class CustomSmsServices
{

    public function getSMSList(){
        return CustomSMS::all();
    }
    
    public function getSmsById($smsId){
         return CustomSMS::where('custom_sms_id','=',$smsId)->first();
    }
    public function saveCustomSMS(Request $request){
        
         if ( $request->has('title') && $request->has('content')&& $request->has('userIds')){
        
        if (!$sms=$this->getSmsById($request->input('customSmsId'))){
            $sms=new CustomSMS();
            $sms->custom_sms_id=$request->input('customSmsId');
            $sms->title=$request->input('title');
            $sms->content=$request->input('content');
            $sms->save();
           
            foreach($request->input('userIds') as $userId){
                $user_sms=new UserSms();
                $user_sms->custom_sms_id=$request->input('customSmsId');
                $user_sms->user_id=$userId;
                $user_sms->save();
            }

            
            return $sms;
        }
        else {
            $sms->title=$request->input('title');
            $sms->content=$request->input('content');
            $sms->update();
            foreach($request->input('userIds') as $userId){
                if ($user_sms= UserSms::where('user_id','=',$userId)->first())
                {
                    $user_sms->user_id=$userId;
                    $user_sms->update(); 
                }
                else {
                    $user_sms=new UserSms();
                    $user_sms->custom_sms_id=$sms->custom_sms_id;
                    $user_sms->user_id=$userId;
                    $user_sms->save();
                }
            }
           
            return $sms; 
        }
    }
    return response(['response'=>'Bad request']);
        
    }
}