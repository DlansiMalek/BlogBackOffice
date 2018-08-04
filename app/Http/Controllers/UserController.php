<?php

namespace App\Http\Controllers;


use App\Services\AdminServices;
use App\Services\CongressServices;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;

class UserController extends Controller
{

    protected $userServices;
    protected $congressServices;
    protected $adminServices;

    function __construct(UserServices $userServices, CongressServices $congressServices,
                         AdminServices $adminServices)
    {
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
    }

    public function index()
    {
        return $this->userServices->getAllUsers();
    }

    public function register(Request $request)
    {
        if (!$request->has(['first_name', 'last_name', 'mobile', 'email', 'congressId'])
        ) {
            return response()->json(['response' => 'invalid request',
                'content' => ['first_name', 'last_name', 'mobile', 'email', 'congressId']], 400);
        }
        $createdUser = $this->userServices->registerUser($request);
        if (!$createdUser) {
            return response()->json(['response' => 'user exist'], 400);
        }

        //Utils::generateQRcode($createdUser->qr_code);
        //$this->userServices->impressionBadge($createdUser);
        //$congress = $this->congressServices->getCongressById($request->input("congressId"));
        //$this->userServices->sendMail($createdUser, $congress);


        return response()->json($createdUser, 201);
    }

    public function getUserById($user_id)
    {
        $user = $this->userServices->getParticipatorById($user_id);
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
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        return response()->json($this->userServices->updateUser($request, $user), 202);
    }

    public function delete($user_id)
    {
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        $user->delete();
        return response()->json(['response' => 'user deleted'], 202);
    }

    public function validateUser($user_id, $validation_code)
    {
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        if ($validation_code === $user->verification_code) {
            $user->email_verified = 1;
            $user->update();
            return response()->json(['response' => 'user verified'], 202);
        }
        return response()->json(['response' => 'invalid verifiaction code'], 400);
    }

    public function resendConfirmationMail($user_id)
    {
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }


        $this->userServices->sendConfirmationMail($user);
        return response()->json(['response' => 'email send to user' . $user->email], 202);
    }


    public function sendingMailWithAttachement($userId)
    {
        if (!$user = $this->userServices->getParticipatorById($userId)) {
            return response()->json(["error" => "User not found"], 404);
        }

        $this->userServices->impressionBadge($user);

        $this->userServices->sendMail($user);

        return response()->json(["message" => "email sending success"], 200);
    }

    public function getUsersByCongress($congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        $users = $this->userServices->getUsersByCongress($congressId);

        return response()->json($users);
    }

    public function addUserToCongress(Request $request, $congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $user = $this->userServices->addParticipant($request, $congressId);

        $this->userServices->affectAccess($user->user_id, $request->input("accessIds"));


        return response()->json(['add success'], 200);
        /*$file = new Filesystem();

        Utils::generateQRcode($user->qr_code, "qrcode.png");


        if ($file->exists(public_path() . "/qrcode.png")) {
            return response()->download(public_path() . "/qrcode.png")
                ->deleteFileAfterSend(true);
        } else {
            return response()->json(["error" => "dossier vide"]);
        }*/
    }

    public function registerUserToCongress(Request $request, $congressId)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }

        if ($user = $this->userServices->getUserByEmail($congressId, $request->input('email'))) {
            return response()->json(['error' => 'user exist'], 400);
        }
        $request->merge(["congressId" => $congressId]);
        $user = $this->userServices->registerUser($request);

        $this->userServices->affectAccess($user->user_id, $request->input("accessIds"));

        if (!$user) {
            return response()->json(['response' => 'user exist'], 400);
        }

        return response()->json($user, 201);

    }

    public function getUsersByAccess($accessId)
    {
        $users = $this->userServices->getUsersByAccess($accessId);

        return response()->json($users);
    }

    public function getPresencesByAccess($accessId)
    {
        $users = $this->userServices->getPresencesByAccess($accessId);

        return response()->json($users);
    }

    public function getPresencesByCongress($congressId)
    {
        $users = $this->userServices->getAllPresencesByCongress($congressId);

        return response()->json($users);
    }

    public function getQrCodeUser($userId)
    {
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(["error" => "user not found"], 404);
        }
        $file = new Filesystem();

        Utils::generateQRcode($user->qr_code, "qrcode.png");

        if ($file->exists(public_path() . "/qrcode.png")) {
            return response()->download(public_path() . "/qrcode.png")
                ->deleteFileAfterSend(true);
        } else {
            return response()->json(["error" => "dossier vide"]);
        }
    }

    public function getAllPayementTypes()
    {
        return response()->json($this->userServices->getAllPayementTypes());
    }
}
