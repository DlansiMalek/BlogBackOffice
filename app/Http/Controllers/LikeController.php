<?php


namespace App\Http\Controllers;


use App\Services\LikeServices;
use App\Services\UserServices;

class LikeController extends Controller
{
    protected $likeServices;
    protected $userServices;

    public function __construct(LikeServices $likeServices,UserServices $userServices)
    {
        $this->likeServices = $likeServices;
        $this->userServices = $userServices;
    }

    public function like($user_id, $access_id)
    {
        if ($like = $this->likeServices->get($user_id, $access_id)) {
            $this->likeServices->unlike($like);
            return response()->json($this->userServices->getUserById($user_id), 200);
        } else {
            $this->likeServices->like($user_id, $access_id);
            return response()->json($this->userServices->getUserById($user_id), 200);
        }
    }

}