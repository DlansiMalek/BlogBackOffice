<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'users';
    protected $primaryKey = 'user_id';

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
        return $this->hasOne('App\Models\City', 'city_id', 'city_id');
    }
}
