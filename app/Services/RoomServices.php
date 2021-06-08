<?php

namespace App\Services;

use App\Models\Room;
use \Firebase\JWT\JWT;
use App\Services\Agora\RtcTokenBuilder;

class RoomServices
{

    public function getRoomsByAdminId($admin_id)
    {
        return Room::where('admin_id', '=', $admin_id)->get();
    }

    public function createToken($email, $name, $isModerator, $userName)
    {
        $key = env('SECRET_KEY_JITSI');
        $payload = array(
            "context" => array(
                "user" => array(
                    "avatar" => "avatar",
                    "name" => $userName, // $isModerator ? "Eventizer Moderator" : "Eventizer Invitee",
                    "email" => $email,
                )
            ),
            "aud" => "meet.eventizer.io",
            "iss" => "meet.eventizer.io",
            "sub" => "meet.eventizer",
            "room" => $name,
            "moderator" => $isModerator
        );
        return JWT::encode($payload, $key);
    }

    public function addRoom($name, $admin_id, $moderator_token, $invitee_token, $moderator_email = null)
    {
        $room = new Room();
        $room->name = $name;
        $room->admin_id = $admin_id;
        $room->moderator_token = $moderator_token;
        $room->invitee_token = $invitee_token;
        if ($moderator_email)
            $room->moderator_email = $moderator_email;
        $room->save();
        return $room;
    }

    public function createTokenAgora($userId, $roomName, $isModerator) {
        return RtcTokenBuilder::buildTokenWithUid(env('AGORA_APPID'), env('AGORA_CERTIFICATE'), $roomName, $userId, RtcTokenBuilder::RolePublisher, Utils::getExpireTime(3600));
    }
}
