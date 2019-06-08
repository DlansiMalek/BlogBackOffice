<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'User';
    protected $primaryKey = 'user_id';
    protected $fillable = ['first_name', 'last_name', 'gender', 'mobile', 'qr_code', 'email_verified', 'verification_code', 'rfid'];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];

    function accesses()
    {
        return $this->belongsToMany('App\Models\Access', 'User_Access', 'user_id', 'access_id')
            ->withPivot('isPresent');
    }

    //Speaker Access
    function speaker_access()
    {
        return $this->belongsToMany('App\Models\Access', 'Speaker_Access', 'user_id', 'access_id');
    }

    //ChairPerson Access
    function chairPerson_access()
    {
        return $this->belongsToMany('App\Models\Access', 'Chair_Access', 'user_id', 'access_id');
    }

    public function payments(){
        return $this->hasMany('App\Models\Payment','user_id','user_id');
    }

    function organization()
    {
        return $this->belongsToMany('App\Models\Organization', 'User_Congress', 'user_id','organization_id');
    }

    function congresses()
    {
        return $this->belongsToMany('App\Models\Congress', 'User_Congress', 'user_id', 'congress_id');
    }

    function country()
    {
        return $this->hasOne('App\Models\Country', 'country_id', 'country_id');
    }

    function responses()
    {
        return $this->hasMany("App\Models\Form_Input_Response", 'user_id', 'user_id');
    }

    function attestation_requests()
    {
        return $this->hasMany('App\Models\Attestation_Request', 'user_id', 'user_id');
    }

    function feedback_responses()
    {
        return $this->hasMany('App\Models\Feedback_Response', 'user_id', 'user_id');
    }

}
