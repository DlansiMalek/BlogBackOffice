<?php

namespace App\Services;


use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Exception;

/**
 * @property \GuzzleHttp\Client client
 * @param  \Exception  $exception
 * @return void
 */

class SmsServices{
    
    
    protected $maxRequest;
   

    public function __construct(){ }

   

    public function authentificationSms($congressId,$config){

        $this->client=new Client([
            'base_uri' =>'https://api.orange.com',
            'headers'=>['Authorization'=>'Basic '.env('SMS_AUTH')]
        ]);

        $res=$this->client->post('/oauth/v2/token',[
            'form_params'=>[
                'grant_type'=>'client_credentials'
            ]
        ]);
        $config->token_sms=json_decode($res->getBody(), true)['access_token'];
        $config->update();
        return json_decode($res->getBody(), true);
                 
    }
    
    public function sendSms($congressId, $user,$congress){
        
       while($this->maxRequest<=3){
       try {
        $response=$this->configSms($congressId,$user,$congress);
        return json_decode($response->getBody(),true);

        } catch(Exception $e){
            $this->maxRequest++;
            if($e->getCode()==401){
            $this->authentificationSms($congressId,$congress->config);
            $this->sendSms($congressId,$user,$congress);
            }
            return $e->getMessage();
            
        }
    }
    return response()->json(['Response'=>'Some serveur error please try again later']);
        
    }

    public function configSms($congressId, $user,$congress){
        
        $this->client=new Client([
            'base_uri' =>'https://api.orange.com',
            'headers'=>[
                'Content-Type'=>'application/json',
                'Authorization'=>'Bearer '.$congress->config['token_sms']
            ]
        ]);
        $res=$this->client->post('/smsmessaging/v1/outbound/tel%3A%2B21653780474/requests',[
            'json'=>[
                'outboundSMSMessageRequest'=>[
                    'address'=>'tel:'.Utils::getMobileFormatted($user->mobile),
                    'senderAddress'=>'tel:+21653780474',
                    'outboundSMSTextMessage'=>[
                        'message'=> Utils::getSmsMessage($user->qr_code,$user->first_name,$user->last_name,$congress->name,$congress->start_date)
                    ]
                ]
            ]
        ]);
        return $res;
    }
}