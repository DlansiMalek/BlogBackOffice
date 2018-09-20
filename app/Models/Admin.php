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

/**
 * @property mixed responsible
 * @property mixed admin_id
 * @property mixed email
 * @property mixed name
 * @property mixed mobile
 */
class Admin extends Authenticatable implements JWTSubject
{
    protected $table = 'Admin';
    protected $primaryKey = 'admin_id';
    protected $fillable = ['email', 'mobile', 'name', 'responsible'];

    protected $hidden = ["password", "passwordDecrypt"];
    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;


    public function privileges()
    {
        return $this->hasMany('App\Models\Admin_Privilege', 'admin_id', 'admin_id');
    }

    public function congresses()
    {
        return $this->hasMany('App\Models\Congress', 'admin_id', 'admin_id');
    }

    public function congress_allowed()
    {
        return $this->hasMany('App\Models\Congress', 'admin_id', 'admin_id');
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