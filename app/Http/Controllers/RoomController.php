<?php

namespace App\Http\Controllers;

use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\RoomServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected $adminServices;
    protected $roomServices;
    protected $congressServices;
    protected $userServices;

    function __construct(
        AdminServices $adminServices,
        RoomServices $roomServices,
        CongressServices $congressServices,
        UserServices $userServices
    )
    {
        $this->adminServices = $adminServices;
        $this->roomServices = $roomServices;
        $this->congressServices = $congressServices;
        $this->userServices = $userServices;
    }

    public function getAdminRooms()
    {
        $admin = $this->adminServices->retrieveAdminFromToken();
        if (!$admin) {
            return response()->json(['response' => 'no admin found'], 401);
        }
        return $this->roomServices->getRoomsByAdminId($admin->admin_id);
    }

    public function addAdminRooms(Request $request)
    {
        $admin = $this->adminServices->retrieveAdminFromToken();
        if (!$admin) {
            return response()->json(['response' => 'no admin found'], 401);
        }
        if (!($request->has('name'))) {
            return response()->json(['response' => 'bad request'], 400);
        }

        $email = $request->has('moderator_email') ? $request->input('moderator_email') : $admin->email;


        $moderator_token = $this->roomServices->createToken($admin, $email, $request->input('name'), true);
        $invitee_token = $this->roomServices->createToken($admin, $email, $request->input('name'), false);

        $room = $this->roomServices->addRoom(
            $request->input('name'),
            $admin->admin_id,
            $moderator_token,
            $invitee_token,
            $request->has('moderator_email') ? $email : null
        );


        $mailtype = $this->congressServices->getMailType('room');
        $mail = $this->congressServices->getMailOutOfCongress($mailtype->mail_type_id);

        $urlModerator = UrlUtils::getMeetEventizerUrl() . '/' . $room->name . '?jwt=' . $moderator_token;
        $urlInvitee = UrlUtils::getMeetEventizerUrl() . '/' . $room->name . '?jwt=' . $invitee_token;

        return $this->userServices->sendMail(
            $this->congressServices->renderMail(
                $mail->template,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $urlModerator,
                $urlInvitee
            ),
            $admin,
            null,
            $mail->object,
            null,
            null,
            $email
        );
    }
}
