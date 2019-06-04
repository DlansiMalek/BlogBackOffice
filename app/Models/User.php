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
        'price', 'email_sended', 'email_verified', 'verification_code',
        'organization_id', 'pack_id', 'rfid', 'email_attestation_sended', 'path_payement',
        'ref_payment', 'autorisation_num', 'organization_accepted'
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

    //Speaker Access
    function speaker_access()
    {
        return $this->belongsToMany('App\Models\Access', 'Speaker_Access', 'user_id', 'access_id')
            ->withPivot('isPresent');
    }

    //ChairPerson Access
    function chairPerson_access()
    {
        return $this->belongsToMany('App\Models\Access', 'Chair_Person_Access', 'user_id', 'access_id')
            ->withPivot('isPresent');
    }


    function organization()
    {
        return $this->belongsTo('App\Models\Organization', 'organization_id', 'organization_id');
    }

    /*
    function congress()
    {
        return $this->hasOne('App\Models\Congress', 'congress_id', 'congress_id');
    }*/

    function congresses()
    {
        return $this->belongsToMany('App\Models\User', 'User_Congress', 'congress_id', 'user_id');
    }


    function pack()
    {
        return $this->hasOne('App\Models\Pack', 'pack_id', 'pack_id');
    }

    function country()
    {
        return $this->hasOne('App\Models\Country', 'country_id', 'country_id');
    }

    function responses()
    {
        return $this->hasMany("App\Models\Form_Input_Reponse", 'user_id', 'user_id');
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
