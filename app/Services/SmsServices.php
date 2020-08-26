<?php

namespace App\Services;


use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * @param \Exception $exception
 * @return void
 * @property \GuzzleHttp\Client client
 */
class SmsServices
{


    protected $maxRequest;


    public function __construct()
    {
    }


    public function authentificationSms($config = null)
    {

        $this->client = new Client([
            'base_uri' => 'https://api.orange.com',
            'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . env('SMS_AUTH')]
        ]);

        $res = $this->client->post('/oauth/v2/token', [
            'form_params' => [
                'grant_type' => 'client_credentials'
            ]
        ]);

        if ($config) {
            $config->token_sms = json_decode($res->getBody(), true)['access_token'];
            $config->update();
        }
        return json_decode($res->getBody(), true)['access_token'];

    }

    public function sendSms($congressId, $user, $congress)
    {
        if ($congress->config->is_notif_sms_confirm) {
            while ($this->maxRequest <= 3) {
                try {
                    $response = $this->configSms($user, $congress);
                    return json_decode($response->getBody(), true);
                } catch (Exception $e) {
                    $this->maxRequest++;
                    if ($e->getCode() == 401) {
                        $this->authentificationSms($congress->config);
                        $this->sendSms($congressId, $user, $congress);
                    }
                    Log::info($e->getMessage());
                    return $e->getMessage();
                }
            }
        }
        return true;
    }

    public
    function configSms($user, $congress, $sms = null, $token_sms = null)
    {
        $token = $token_sms ? $token_sms : ($congress ? $congress->config['token_sms'] : '');
        $this->client = new Client([
            'base_uri' => 'https://api.orange.com',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        $res = $this->client->post('/smsmessaging/v1/outbound/tel%3A%2B21653780474/requests', [
            'json' => [
                'outboundSMSMessageRequest' => [
                    'address' => 'tel:' . Utils::getMobileFormatted($user->mobile),
                    'senderName' => $token_sms ? $sms->senderName : 'Eventizer',
                    'senderAddress' => 'tel:+21653780474',
                    'outboundSMSTextMessage' => [
                        'message' => $token_sms ? Utils::customSmsMessage($sms, $user) : Utils::getSmsMessage($user->qr_code, $user->first_name, $user->last_name, $congress->name, $congress->start_date, $congress->config['mobile_committee'], $congress->config['mobile_technical'])
                    ]
                ]
            ]
        ]);
        return $res;
    }
}
