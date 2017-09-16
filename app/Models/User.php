<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'User';
    protected $primaryKey = 'id_User';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gender', 'first_name', 'last_name', 'profession', 'domain', 'establishment', 'city_id', 'valide',
        'address', 'postal', 'tel', 'mobile', 'fax', 'email', 'cin', 'validation_code'
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
}
