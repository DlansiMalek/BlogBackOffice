<?php


namespace App\Services;

use App\Models\CustomSMS;
use App\Models\User;
use App\Models\UserSms;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * @param \Exception $exception
 * @return void
 * @property \GuzzleHttp\Client client
 */
class CustomSmsServices
{

    public function getSMSList(){
        return CustomSMS::all();
    }
    
    public function getSmsById($smsId){
         return CustomSMS::where('custom_sms_id','=',$smsId)->first();
    }
    
    public function getUserSms($smsId,$userId)
    {
        $conditionsToMatch=['custom_sms_id'=>$smsId,'user_id'=>$userId];
        
        return UserSms::where($conditionsToMatch)->first();
    }
    public function filterUsersBySmsStatus($smsId,$status){


       $users=User::whereHas('custom_sms',function($query) use ($smsId,$status){

        if ($status=="1" || $status=="0"){
           $query->where('Custom_SMS.custom_sms_id','=',$smsId);
           $query->where('User_Sms.status','=',$status);   
        } 
        else {
            $query->where('Custom_SMS.custom_sms_id','=',$smsId); 
        }

       })
       ->with(['user_sms'=>function($query) use ($smsId){
           $query->where('User_Sms.custom_sms_id','=',$smsId);
       }])
       ->get();
       return $users;
    }


    public function authentificationSms()
    {

        $this->client = new Client([
            'base_uri' => 'https://api.orange.com',
            'headers' => ['Authorization' => 'Basic ' . env('SMS_AUTH')]
        ]);

        $res = $this->client->post('/oauth/v2/token', [
            'form_params' => [
                'grant_type' => 'client_credentials'
            ]
        ]);
        return  json_decode($res->getBody(), true)['access_token'];


    }

    public function sendSmsToUsers($user,$sms)
    {
      
        $token_sms= $this->authentificationSms();
         
                try {
                   $response = $this->configSms($sms,$user,$token_sms);  
                   return json_decode($response->getBody(), true);
                } catch (Exception $e) {
                  
                    Log::info($e->getMessage());
                    return $e->getMessage();
                }
    }
        
     
    
 public  function configSms($sms,$user,$token_sms)
    {

        $this->client = new Client([
            'base_uri' => 'https://api.orange.com',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token_sms
            ]
        ]);
        $res = $this->client->post('/smsmessaging/v1/outbound/tel%3A%2B21653780474/requests', [
            'json' => [
                'outboundSMSMessageRequest' => [
                    'address' => 'tel:' . Utils::getMobileFormatted($user->mobile),
                    'senderAddress' => 'tel:+21653780474',
                    'outboundSMSTextMessage' => [
                        'message' => Utils::customSmsMessage($sms,$user)
                    ]
                ]
            ]
        ]);
        return $res;
    }
    public function saveCustomSMS(Request $request){

        if ( $request->has('title') && $request->has('content') && $request->has('userIds')){

        if (!$sms=$this->getSmsById($request->input('customSmsId'))){
            $sms=new CustomSMS();
            $sms->title=$request->input('title');
            $sms->content=$request->input('content');
            $sms->save();
           
            foreach($request->input('userIds') as $userId){
                $user_sms=new UserSms();
                $user_sms->custom_sms_id=$sms->custom_sms_id;
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