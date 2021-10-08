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
use Illuminate\Support\Facades\Log;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

class NotificationServices
{
    public function __construct()
    {

    }

    public function getKeyByCongressId($congressId,?string $firebaseKey,$userId,$source)
    {
        return UserNotifCongress::where('congress_id', '=', $congressId)
            ->where('user_id','=',$userId)
            ->where('source','=',$source)
            ->when($firebaseKey !=null, function($query) use ($firebaseKey) {
                $query->where('firebase_key_user','=',$firebaseKey);
            })
            ->first();
    }

    public function saveKeyByCongress($congressId, ?string $firebaseKey, $userId=null , $source)
    {
        $userNotifCongress = new UserNotifCongress();
        $userNotifCongress->congress_id = $congressId;
        $userNotifCongress->firebase_key_user = $firebaseKey;
        $userNotifCongress->user_id = $userId;
        $userNotifCongress->source = $source;
        $userNotifCongress->save();
    }

    public function getAllKeysByCongressId($congressId)
    {
        return UserNotifCongress::where('congress_id', '=', $congressId)
            ->get();
    }
    public function getAllKeysByCongressIdAndSource($congressId, $source)
    {
        return UserNotifCongress::where('congress_id', '=', $congressId)
            ->where('source','=',$source)
            ->get();
    }

    public function sendNotification($data, $tokens,$withNotification)
    {

        if(sizeof($tokens)>0) {

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60 * 20);
            $notification = null ;
            $dataBuilder = new PayloadDataBuilder();
            if (gettype($data) == 'array')
            $dataBuilder->addData($data);
            else {
                $dataBuilder->addData(['data_1' => 'value data 1']);
            }
            $option = $optionBuilder->build();
            if ($withNotification)  {
                
                $notificationBuilder = new PayloadNotificationBuilder();
                $notificationBuilder->setBody($data)
                    ->setSound('default');
                $notification = $notificationBuilder->build();
            }
            $data = $dataBuilder->build();
           
             FCM::sendTo($tokens, $option, $notification, $data);

        }
    }

    public function sendNotificationToCongress(string $message, $congress_id)
    {
        $tokens = Utils::mapDataByKey($this->getAllKeysByCongressId($congress_id), 'firebase_key_user');
        $this->sendNotification($message, $tokens,true);
    }

}