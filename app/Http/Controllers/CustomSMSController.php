<?php

namespace App\Http\Controllers;

use App\Services\SmsServices;
use App\Services\AdminServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomSMSController extends Controller
{

    protected $smsService;

       

    function __construct(AdminServices $adminServices, SmsServices $smsService)
    {

        $this->adminServices=$adminServices;
         $this->smsService = $smsService;
    }

    public function getListSMS()
    {
        
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $sms = $this->smsService->getSMSList($admin->admin_id);

        return response()->json($sms, 200);
    }
 
    public function deleteSMS($smsId)
    {
        if (!$sms = $this->smsService->getCustomSmsById($smsId))
            return response(['response' => 'no sms found'], 400);

        $sms->delete();
        return response(['response' => 'sms deleted successfuly'], 200);
    }

    public function getSmsById($smsId)
    {
        if (!$sms = $this->smsService->getCustomSmsById($smsId))
            return response(['response' => 'no sms found']);
        return $sms;
    }

    public function saveCustomSMS(Request $request)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json(['error' => 'admin_not_found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
            'userIds' => 'required',
        ]);
        if ($validator->fails())
            return $validator->errors();

        return $this->smsService->saveCustomSMS($request,$admin->admin_id);
    }

    public function filterUsersBySmsStatus($smsId, Request $request)
    {

        $status = $request->query('status', '');

        if (!$sms = $this->smsService->getCustomSmsById($smsId)) {
            return response()->json(['error' => 'sms not found'], 404);
        }
        return response()->json($this->smsService->filterUsersByCustomSmsStatus($smsId, $status));
    }


    public function deleteUserSms($smsId, $userId)
    {

        if (!$user_sms = $this->smsService->getUserCustomSms($smsId, $userId))
            return response(['No user_sms found', 404]);

        $user_sms->delete();
        return $user_sms;

    }


    public function sendSmsToUsers($smsId)
    {

        if (!$sms = $this->smsService->getCustomSmsById($smsId))
            return response(['response' => 'There is no sms'], 400);

        $users = $this->smsService->filterUsersByCustomSmsStatus($smsId, 0);
        if (!sizeof($users) >= 1)
            return response(['response' => 'There is no users'], 400);

        foreach ($users as $user) {
            if (sizeof($user->user_sms) > 0)
                return $this->smsService->sendSmsToUsers($user, $sms);
        }

        return response(['response' => 'Message sent successfully', 200]);
    }

}
