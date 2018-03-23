<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'User';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'gender', 'mobile', 'city_id', 'qr_code', 'isPresent'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;


    function accesss()
    {
        return $this->hasMany('App\Models\User_Access', 'user_id', 'user_id');
    }

}
