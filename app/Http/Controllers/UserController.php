<?php

namespace App\Http\Controllers;

use App\Metiers\Utils;
use App\Services\UserServices;
use Illuminate\Http\Request;

class UserController extends Controller
{

    protected $userServices;

    function __construct(UserServices $userServices)
    {
        $this->userServices = $userServices;
    }

    public function index()
    {
        return $this->userServices->getAllUsers();
    }

    public function register(Request $request)
    {
        if (!$request->has(['gender', 'first_name', 'last_name',
            'profession', 'domain', 'establishment', 'city_id',
            'address', 'postal', 'tel', 'mobile', 'fax', 'email', 'cin',])
        ) {
            return response()->json(['response' => 'invalid request',
                'content' => ['gender', 'first_name', 'last_name',
                    'profession', 'domain', 'establishment', 'city_id',
                    'address', 'postal', 'tel', 'mobile', 'fax', 'email', 'cin',]], 400);
        }
        $createdUser = $this->userServices->registerUser($request);
        Utils::generateQRcode($createdUser->qr_code);
        $this->userServices->impressionBadge($createdUser);
        $this->userServices->sendMail($createdUser);

        if (!$createdUser) {
            return response()->json(['response' => 'user exist'], 400);
        }
        return response()->json($createdUser, 201);
    }

    public function getUserById($user_id)
    {
        $user = $this->userServices->getUserById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        return response()->json($user, 200);
    }

    public function update(Request $request, $user_id)
    {
        if (!$request->has(['first_name', 'last_name'])) {
            return response()->json(['response' => 'invalid request',
                'content' => ['gender', 'first_name', 'last_name',
                    'profession', 'domain', 'establishment', 'city_id',
                    'address', 'postal', 'tel', 'mobile', 'fax',]], 400);
        }
        $user = $this->userServices->getUserById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        return response()->json($this->userServices->updateUser($request, $user), 202);
    }

    public function delete($user_id)
    {
        $user = $this->userServices->getUserById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        $user->delete();
        return response()->json(['response' => 'user deleted'], 202);
    }

    public function validateUser($user_id, $validation_code)
    {
        $user = $this->userServices->getUserById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        if ($validation_code === $user->validation_code) {
            $user->valide = true;
            $user->update();
            return response()->json(['response' => 'user validated'], 202);
        }
        return response()->json(['response' => 'invalid validation code'], 400);
    }

    public function resendConfirmationMail($user_id)
    {
        $user = $this->userServices->getUserById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }


        $this->userServices->sendConfirmationMail($user);
        return response()->json(['response' => 'email send to user' . $user->email], 202);
    }


    public function sendingMailWithAttachement($userId)
    {
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(["error" => "User not found"], 404);
        }

        $this->userServices->impressionBadge($user);

        $this->userServices->sendMail($user);


        return response()->json(["message" => "email sending success"], 200);
    }

}
