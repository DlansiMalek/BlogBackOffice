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
 * @property mixed passwordDecrypt
 */
class Admin extends Authenticatable implements JWTSubject
{
    protected $table = 'Admin';
    protected $primaryKey = 'admin_id';
    protected $fillable = ['email', 'mobile', 'name', "passwordDecrypt","privilege_id", 'voting_token'];

    protected $hidden = ["passwordDecrypt", "password"];
    protected $dates = ['created_at', 'updated_at','deleted_at'];
    public $timestamps = true;

    public function congresses()
    {
        return $this->belongsToMany('App\Models\Congress', 'Admin_Congress', 'admin_id','congress_id');
    }

    public function admin_congresses()
    {
        return $this->hasMany('App\Models\AdminCongress', 'admin_id', 'admin_id');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment','admin_id','admin_id');
    }
    public function AdminPayments(){
        return $this->hasMany('App\Models\PaymentAdmin','admin_id','admin_id');
    }
    public function AdminHistories(){
        return $this->hasMany('App\Models\HistoryPack','admin_id','admin_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

}