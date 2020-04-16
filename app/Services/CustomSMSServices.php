<?php


namespace App\Services;

use App\Models\CustomSMS;
use App\Models\User;
use App\Models\UserSms;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Exception;
use App\Services\SmsServices;
use Illuminate\Support\Facades\Log;

/**
 * @param \Exception $exception
 * @return void
 * @property \GuzzleHttp\Client client
 */
class CustomSmsServices
{
    protected $smsServices;

    function __construct(SmsServices $smsServices)
    {
        $this->smsServices = $smsServices;
    }

    public function getSMSList()
    {
        return CustomSMS::all();
    }

    public function getSmsById($smsId)
    {
        return CustomSMS::where('custom_sms_id', '=', $smsId)->first();
    }

    public function getAllUserSms()
    {

        return UserSms::all();

    }

    public function getUserSms($smsId, $userId)
    {
        $conditionsToMatch = ['custom_sms_id' => $smsId, 'user_id' => $userId];

        return UserSms::where($conditionsToMatch)->first();
    }

    public function filterUsersBySmsStatus($smsId, $status)
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

    public function sendSmsToUsers($user, $sms)
    {
        $token_sms = $this->smsServices->authentificationSms();
        try {
            $response = $this->smsServices->configSms($user, null, $sms, $token_sms);
            $user_sms = $user->user_sms[0];
            $user_sms->status = 1;
            $user_sms->update();
            return json_decode($response->getBody(), true);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return $e->getMessage();
        }
    }

    public function saveCustomSMS(Request $request)
    {

        if (!$sms = $this->getSmsById($request->input('customSmsId'))) {
            $sms = new CustomSMS();
            $sms->title = $request->input('title');
            $sms->content = $request->input('content');
            $sms->save();

            foreach ($request->input('userIds') as $userId) {
                $this->saveUserSms($sms->custom_sms_id, $userId);
            }

            return $sms;
        } else {
            $sms->title = $request->input('title');
            $sms->content = $request->input('content');
            $sms->update();

            //créer un nouveau user_sms pour chaque nouveau userId
            $users_sms = $this->getAllUserSms();
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
}
