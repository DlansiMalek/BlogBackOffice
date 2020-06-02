<?php

namespace App\Http\Controllers;

use App\Services\CustomSmsServices;
use App\Services\AdminServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomSMSController extends Controller
{

    protected $customSmsServices;

    function __construct(CustomSmsServices $customSmsServices,AdminServices $adminServices)
    {
        $this->customSmsServices = $customSmsServices;
        $this->adminServices=$adminServices;
    }

    public function getListSMS()
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $sms = $this->customSmsServices->getSMSList($admin->admin_id);

        return response()->json($sms, 200);
    }

    public function deleteSMS($smsId)
    {
        if (!$sms = $this->customSmsServices->getSmsById($smsId))
            return response(['response' => 'no sms found'], 400);

        $sms->delete();
        return response(['response' => 'sms deleted successfuly'], 200);
    }

    public function getSmsById($smsId)
    {
        if (!$sms = $this->customSmsServices->getSmsById($smsId))
            return response(['response' => 'no sms found']);
        return $sms;
    }

    public function saveCustomSMS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
            'userIds' => 'required',
        ]);
        if ($validator->fails())
            return $validator->errors();

        return $this->customSmsServices->saveCustomSMS($request);
    }

    public function filterUsersBySmsStatus($smsId, Request $request)
    {

        $status = $request->query('status', '');

        if (!$sms = $this->customSmsServices->getSmsById($smsId)) {
            return response()->json(['error' => 'sms not found'], 404);
        }
        return response()->json($this->customSmsServices->filterUsersBySmsStatus($smsId, $status));
    }


    public function deleteUserSms($smsId, $userId)
    {

        if (!$user_sms = $this->customSmsServices->getUserSms($smsId, $userId))
            return response(['No user_sms found', 404]);

        $user_sms->delete();
        return $user_sms;

    }


    public function sendSmsToUsers($smsId)
    {

        if (!$sms = $this->customSmsServices->getSmsById($smsId))
            return response(['response' => 'There is no sms'], 400);

        $users = $this->customSmsServices->filterUsersBySmsStatus($smsId, 0);
        if (!sizeof($users) >= 1)
            return response(['response' => 'There is no users'], 400);

        foreach ($users as $user) {
            if (sizeof($user->user_sms) > 0)
                $this->customSmsServices->sendSmsToUsers($user, $sms);
        }

        return response(['response' => 'Message sent successfully', 200]);
    }

}
