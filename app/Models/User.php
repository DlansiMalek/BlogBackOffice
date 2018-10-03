<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    public $timestamps = true;
    protected $table = 'User';
    protected $primaryKey = 'user_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'gender', 'mobile', 'city_id', 'qr_code', 'isPresent', 'payement_type_id',
        'price', 'email_sended', 'email_verified', 'verification_code', 'congress_id', 'lieu_ex_id', 'grade_id',
        'organization_id', 'pack_id', 'privilege_id', 'rfid'
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

    function accesss()
    {
        return $this->belongsToMany('App\Models\Access', 'User_Access', 'user_id', 'access_id')
            ->withPivot('isPresent');
    }

    function grade()
    {
        return $this->belongsTo('App\Models\Grade', 'grade_id', 'grade_id');
    }

    function organization()
    {
        return $this->belongsTo('App\Models\Organization', 'organization_id', 'organization_id');
    }

    function congress()
    {
        return $this->hasOne('App\Models\Congress', 'congress_id', 'congress_id');
    }

    function privilege()
    {
        return $this->hasOne('App\Models\Privilege', 'privilege_id', 'privilege_id');
    }

}
