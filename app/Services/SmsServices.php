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
    protected $utils;

    public function __construct(Utils $utils){
        $this->utils=$utils;
     }

   

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
    
    public function sendSms($congressId, $user,$config){
        
       while($this->maxRequest<=3){
       try {
        $response=$this->configSms($congressId,$user,$config);
        return json_decode($response->getBody(),true);

        } catch(Exception $e){
            $this->maxRequest++;
            if($e->getCode()==401){
            $this->authentificationSms($congressId,$config);
            $this->sendSms($congressId,$user,$config);
            }
            return $e->getMessage();
            
        }
    }
    return response()->json(['Response'=>'Some serveur error please try again later']);
                        
        
    }

    public function configSms($congressId, $user,$config){
        
        $this->client=new Client([
            'base_uri' =>'https://api.orange.com',
            'headers'=>[
                'Content-Type'=>'application/json',
                'Authorization'=>'Bearer '.$config->token_sms
            ]
        ]);
        $res=$this->client->post('/smsmessaging/v1/outbound/tel%3A%2B21653780474/requests',[
            'json'=>[
                'outboundSMSMessageRequest'=>[
                    'address'=>'tel:+216'.$user->mobile,
                    'senderAddress'=>'tel:+21653780474',
                    'outboundSMSTextMessage'=>[
                        'message'=> $this->utils->getSmsMessage($user->qr_code)
                    ]
                ]
            ]
        ]);
        return $res;
    }
}