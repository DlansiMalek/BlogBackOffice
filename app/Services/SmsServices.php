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
    
    protected $congressServices;
    public function __construct(CongressServices $congressServices){

       $this->congressServices=$congressServices;
        
    }

    public function authentificationSms($congressId){

        $this->client=new Client([
            'base_uri' =>'https://api.orange.com',
            'headers'=>['Authorization'=>'Basic VFZOYWNPYjVPRmNWSGhQUEU0ZlcyTEczaTU4bm93UnM6UExWSTBQVkpTSVI0REFkUQ==']
        ]);

        $res=$this->client->post('/oauth/v2/token',[
            'form_params'=>[
                'grant_type'=>'client_credentials'
            ]
        ]);
        $config=$this->congressServices->getCongressConfig($congressId);
        $config->token_sms=json_decode($res->getBody(), true)['access_token'];
        $config->update();
        return json_decode($res->getBody(), true);
                 
    }
    
    public function sendSms($congressId, $user){
        
        

       try {
        $response=$this->configSms($congressId,$user);
        return json_decode($response->getBody(),true);

        } catch(Exception $e){
            if($e->getCode()==401){
           $this->authentificationSms($congressId);
            $response=$this->configSms($congressId,$user);
            return json_decode($response->getBody(),true);
            }
            return $e->getMessage();
            
        }
       
                        
        
    }

    public function configSms($congressId, $user){
        $config=$this->congressServices->getCongressConfig($congressId);
        
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
                        'message'=> 'Hello !'
                    ]
                ]
            ]
        ]);
        return $res;
    }
}