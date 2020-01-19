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
use Illuminate\Http\Request;


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
        $firebaseKey = $request->input('token');

        $userKey = $this->notificationService->getKeyByCongressId($congressId, $firebaseKey);

        if ($deleteParam || $userKey) {
            if ($deleteParam && $userKey) {
                $userKey->delete();
                return response(['message' => 'user deleted'], 200);
            }
            return response(['message' => 'user exist'], 200);
        }

        $this->notificationService->saveKeyByCongress($congressId, $firebaseKey);

        return response()->json(['message' => 'save with success']);
    }

}
