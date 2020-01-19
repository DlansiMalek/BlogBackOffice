<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:42
 */

namespace App\Services;

use App\Models\UserNotifCongress;
use Illuminate\Http\Request;

class NotificationServices
{
    public function __construct()
    {

    }

    public function getKeyByCongressId($congressId, ?string $firebaseKey)
    {
        return UserNotifCongress::where('congress_id', '=', $congressId)
            ->where('firebase_key_user', '=', $firebaseKey)
            ->first();
    }

    public function saveKeyByCongress($congressId, ?string $firebaseKey)
    {
        $userNotifCongress = new UserNotifCongress();
        $userNotifCongress->congress_id = $congressId;
        $userNotifCongress->firebase_key_user = $firebaseKey;
        $userNotifCongress->save();
    }

}