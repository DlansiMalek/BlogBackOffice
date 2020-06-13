<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:36
 */

namespace App\Http\Controllers;

use App\Services\CongressServices;
use App\Services\NotificationServices;
use App\Services\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class NotificationController extends Controller
{

    protected $notificationService;
    protected $congressServices;

    function __construct(NotificationServices $notificationService,
                         CongressServices $congressServices)
    {
        $this->notificationService = $notificationService;
        $this->congressServices = $congressServices;
    }

    public function sendFirebaseKey($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getById($congressId)) {
            return response()->json(['response' => 'congress not found'], 404);
        }

        $deleteParam = $request->has('deleted') && $request->query('deleted') === 'true';
        $source = $request->input('source');
        $firebaseKey =  $source == 'frontOffice' ? null : $request->input('token') ;
        $user_id =  $request->input('userId');   
        //mon but est de comparer entre le token que j'ai deja avec celui dans request
        // j'ai pas idée sur l'utilisation de l'autre cas c'est pour cela que j'ai procédé ainsi 
          $userKey = $this->notificationService->getKeyByCongressId($congressId, $firebaseKey,$user_id,$source);
        
        if ($deleteParam || $userKey) {
            if ($deleteParam && $userKey) {
                $userKey->delete();
                return response(['message' => 'user deleted'], 200);
            }
            //si le token qui exist deja est different que celui envoyé dans la requête
            // on supprime puis on ajoute le nouveau
            if ($userKey->firebase_key_user != $firebaseKey) {
                $userKey->delete();
            }
            else {
                return response(['message' => 'user exist'], 200);
            }
        }
        $firebaseKey =  $request->input('token') ;
        $this->notificationService->saveKeyByCongress($congressId, $firebaseKey,$user_id,$source);

        return response()->json(['message' => 'save with success']);
    }

    public function sendNotificationToCongress($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getById($congressId)) {
            return response()->json(['response' => 'congress not found'], 404);
        }
        $message = $request->input("message");

        $usersToken = $this->notificationService->getAllKeysByCongressId($congressId);

        $tokens = Utils::mapDataByKey($usersToken, 'firebase_key_user');

        $this->notificationService->sendNotification($message, $tokens , true);

        return response()->json(['message' => 'success send']);
    }

}
