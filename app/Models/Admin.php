<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    protected $table = 'Admin';
    protected $primaryKey = 'id_Admin';
    protected $fillable = ['email', 'password', 'passwordDecrypt', 'name'];

    protected $hidden = ["password", "passwordDecrypt"];
    public $timestamps = false;


    public function privileges()
    {
        return $this->hasMany('App\Models\Admin_Privilege', 'id_Admin', 'id_Admin');
    }

    public function congresses()
    {
        return $this->belongsToMany('App\Models\Congress', 'Congress_Admin', 'id_Admin', 'id_Congress');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}