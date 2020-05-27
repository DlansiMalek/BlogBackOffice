<?php

namespace App\Services;


use GuzzleHttp\Client;
use App\Models\CustomSMS;
use Illuminate\Http\Request;
use Exception;
use App\Models\User;
use App\Models\UserSms;
use Illuminate\Support\Facades\Log;

/**
 * @param \Exception $exception
 * @return void
 * @property \GuzzleHttp\Client client
 */
class SmsServices
{


    protected $maxRequest = 0;


    public function __construct()
    {
    }
    public function getCustomSmsList()
    {
        return CustomSMS::all();
    }

    public function getCustomSmsById($smsId)
    {
        return CustomSMS::where('custom_sms_id', '=', $smsId)->first();
    }

    public function getAllUserCustomSms()
    {

        return UserSms::all();

    }

    public function getUserCustomSms($smsId, $userId)
    {
        $conditionsToMatch = ['custom_sms_id' => $smsId, 'user_id' => $userId];

        return UserSms::where($conditionsToMatch)->first();
    }

    public function filterUsersByCustomSmsStatus($smsId, $status)
    {

        $users = User::whereHas('custom_sms', function ($query) use ($smsId, $status) {

            if ($status == "1" || $status == "0") {
                $query->where('Custom_SMS.custom_sms_id', '=', $smsId);
                $query->where('User_Sms.status', '=', $status);
            } else {
                $query->where('Custom_SMS.custom_sms_id', '=', $smsId);
            }
        })
            ->with(['user_sms' => function ($query) use ($smsId) {
                $query->where('User_Sms.custom_sms_id', '=', $smsId);
            }])
            ->get();
        return $users;
    } 
    
    public function sendSmsToUsers($user,$sms=null,$congressId=null, $congress=null)
    {
        if ( ($congress && $congress->config->is_notif_sms_confirm) || $sms) {
            while ($this->maxRequest <= 3) {
                try {
                    if ($sms) {
                    $token_sms = $this->authentificationSms();
                    $response = $this->configSms($user, null, $sms, $token_sms);     
                    $this->updateUserSms($user);
                    }
                    else {
                    $response = $this->configSms($user, $congress); 
                    }
                    return json_decode($response->getBody(), true);
                } catch (Exception $e) {
                    $this->maxRequest++;
                    if ($e->getCode() == 401) {
                        if ($congress) {
                        $this->authentificationSms($congress->config);
                        }
                        return $this->sendSmsToUsers($congressId, $user, $congress);
                    }
                    Log::info($e->getMessage());
                    return $e->getMessage();
                }
            }
        }
        return true;
    }

    private function updateUserSms($user) {
        $user_sms = $user->user_sms[0];
        $user_sms->status = 1;
        $user_sms->update();
    }

    public function saveCustomSMS(Request $request)
    {

        if (!$sms = $this->getCustomSmsById($request->input('customSmsId'))) {
            $sms = new CustomSMS();
            $sms->title = $request->input('title');
            $sms->senderName = $request->input('senderName');
            $sms->content = $request->input('content');
            $sms->save();

            foreach ($request->input('userIds') as $userId) {
                $this->saveUserSms($sms->custom_sms_id, $userId);
            }

            return $sms;
        } else {
            $sms->title = $request->input('title');
            $sms->senderName = $request->input('senderName');
            $sms->content = $request->input('content');
            $sms->update();

            //créer un nouveau user_sms pour chaque nouveau userId
            $users_sms = $this->getAllUserCustomSms();
            foreach ($request->input('userIds') as $userId) {
                $isUserIdExisting = false;
                foreach ($users_sms as $user_sms) {
                    if ($user_sms->user_id == $userId) {
                        $isUserIdExisting = true;
                        break;
                    }
                }
                if (!$isUserIdExisting) {
                    $this->saveUserSms($sms->custom_sms_id, $userId);
                }
            }

            return $sms;
        }
    }

    public function saveUserSms($smsId, $userId)
    {
        $user_sms = new UserSms();
        $user_sms->custom_sms_id = $smsId;
        $user_sms->user_id = $userId;
        $user_sms->save();
        return $user_sms;
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
        $body =  [
            'json' => [
                'outboundSMSMessageRequest' => [
                    'address' => 'tel:' . Utils::getMobileFormatted($user->mobile),
                    'senderAddress' => 'tel:+21653780474',
                    'outboundSMSTextMessage' => [
                        'message' =>  $token_sms ? Utils::customSmsMessage($sms, $user) : Utils::getSmsMessage($user->qr_code, $user->first_name, $user->last_name, $congress->name, $congress->start_date, $congress->config['mobile_committee'], $congress->config['mobile_technical'])
                    ]
                ]
            ]
        ] ;
        if ($sms && $sms->senderName == 'Ecozone') {
        $body['json']['outboundSMSMessageRequest']['senderName'] = $sms->senderName;
        }
        $res = $this->client->post('/smsmessaging/v1/outbound/tel%3A%2B21653780474/requests', $body);
        return $res;
    }
}
