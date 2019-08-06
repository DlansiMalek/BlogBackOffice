<?php


namespace App\Http\Controllers;


use App\Services\LikeServices;

class LikeController extends Controller
{
    protected $likeServices;

    public function __construct(LikeServices $likeServices)
    {
        $this->likeServices = $likeServices;
    }

    public function like($user_id, $access_id)
    {
        if ($like = $this->likeServices->get($user_id, $access_id)){
            $this->likeServices->unlike($like);
            return response()->json(['response' => 'unliked'], 200);
        }
        else {
            $this->likeServices->like($user_id, $access_id);
            return response()->json(['resp  onse' => 'liked'], 200);
        }
    }

}