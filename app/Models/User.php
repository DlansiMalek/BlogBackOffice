<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'User';
    protected $primaryKey = 'user_id';
    protected $fillable = ['first_name', 'last_name', 'gender', 'mobile', 'qr_code','code', 'email_verified', 'verification_code', 'rfid', 'profile_pic', 'country_id'];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    function user_mails()
    {
        return $this->hasMany(UserMail::class, "user_id", "user_id");
    }

    function accesses()
    {
        return $this->belongsToMany('App\Models\Access', 'User_Access', 'user_id', 'access_id')
            ->withPivot('isPresent');
    }

    //Speaker Access
    function speaker_access()
    {
        return $this->belongsToMany('App\Models\Access', 'Access_Speaker', 'user_id', 'access_id');
    }

    //ChairPerson Access
    function chair_access()
    {
        return $this->belongsToMany('App\Models\Access', 'Access_Chair', 'user_id', 'access_id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Payment', 'user_id', 'user_id');
    }

    function organization()
    {
        return $this->belongsToMany('App\Models\Organization', 'User_Congress', 'user_id', 'organization_id');
    }

    function congresses()
    {
        return $this->belongsToMany('App\Models\Congress', 'User_Congress', 'user_id', 'congress_id');
    }

    function user_congresses()
    {
        return $this->hasMany('App\Models\UserCongress', 'user_id', 'user_id');
    }

    function country()
    {
        return $this->hasOne('App\Models\Country', 'alpha3code', 'country_id');
    }

    function responses()
    {
        return $this->hasMany('App\Models\FormInputResponse', 'user_id', 'user_id');
    }

    function attestation_requests()
    {
        return $this->hasMany('App\Models\AttestationRequest', 'user_id', 'user_id');
    }

    function feedback_responses()
    {
        return $this->hasMany('App\Models\FeedbackResponse', 'user_id', 'user_id');
    }

    function likes()
    {
        return $this->hasMany('App\Models\Like', 'user_id', 'user_id');
    }

}
