<?php


namespace App\Services;


use App\Models\Like;

class LikeServices
{
    public function like($user_id, $access_id){
        $like = new Like();
        $like->user_id = $user_id;
        $like->access_id = $access_id;
        $like->save();
        return $like;
    }

    public function unlike($like)
    {
        $like->delete();
    }

    public function get($user_id, $access_id)
    {
        return Like::where('user_id','=',$user_id)
            ->where('access_id','=',$access_id)
            ->first();
    }

}