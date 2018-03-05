<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User_Tmp extends Authenticatable
{
    use Notifiable;
    protected $table = 'User_Tmp';
    protected $primaryKey = 'id_User';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'laboratoire', 'pack', 'Mode_exercice', 'city'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'validation_code'
    ];

    function city()
    {
        return $this->hasOne('App\Models\City', 'id_City', 'city_id');
    }

    function congresses()
    {
        return $this->belongsToMany("App\Models\Congress", "Congress_User", "id_User", "id_Congress");
    }
}
