<?php

namespace App\Http\Controllers;

use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\RoomServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    protected $adminServices;
    protected $roomServices;
    protected $congressServices;
    protected $userServices;
    protected $mailServices;

    function __construct(
        AdminServices $adminServices,
        RoomServices $roomServices,
        CongressServices $congressServices,
        UserServices $userServices,
        MailServices $mailServices
    )
    {
        $this->adminServices = $adminServices;
        $this->roomServices = $roomServices;
        $this->congressServices = $congressServices;
        $this->userServices = $userServices;
        $this->mailServices = $mailServices;
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

        $name = Utils::getUCWords($request->input('name'));

        $moderator_token = $this->roomServices->createToken($email, $name, true, "Eventizer Moderator");
        $invitee_token = $this->roomServices->createToken($email, $name, false, "Eventizer Invitee");

        $room = $this->roomServices->addRoom(
            $name,
            $admin->admin_id,
            $moderator_token,
            $invitee_token,
            $request->has('moderator_email') ? $email : null
        );

        $mailtype = $this->congressServices->getMailType('room');
        $mail = $this->congressServices->getMailOutOfCongress($mailtype->mail_type_id);

        $urlModerator = UrlUtils::getMeetEventizerUrl() . '/' . $room->name . '?jwt=' . $moderator_token;
        $urlInvitee = UrlUtils::getMeetEventizerUrl() . '/' . $room->name . '?jwt=' . $invitee_token;

        $this->mailServices->sendMail(
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
                $urlInvitee,
                $room
            ),
            $admin,
            null,
            'Invitation: ' . $room->name,
            null,
            null,
            $email
        );

        return response()->json(['message' => 'added room success']);
    }
}
