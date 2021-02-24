<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessGame extends Model
{
    public $timestamps = true;
    protected $table = 'Access_Game';
    protected $primaryKey = 'access_game_id';
    protected $fillable = ['score', 'user_id', 'access_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    function access()
    {
        return $this->belongsTo('App\Models\Access', 'access_id', 'access_id');
    }

    function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
}
