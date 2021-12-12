<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNetwork extends Model
{
    protected $table = 'User_Network';
    protected $primaryKey = 'user_network_id';
    protected $fillable = ['user_id', 'fav_id'];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    function fav()
    {
        return $this->hasOne('App\Models\User', 'user_id', 'fav_id');
    }
}
