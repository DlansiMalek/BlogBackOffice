<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Privilege extends Model
{
    protected $table = 'Privilege';
    protected $primaryKey = 'privilege_id';
    protected $fillable = ['name','internal','priv_reference','congress_id'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public $timestamps = true;

    public function badges()
    {
        return $this->hasMany('App\Models\Badge', 'privilege_id', 'privilege_id');
    }

    public function privilegeConfig () {
        return $this->hasMany('App\Models\PrivilegeConfig', 'privilege_id', 'privilege_id');
    }
    public function privilege () {
        return $this->hasOne('App\Models\Privilege', 'privilege_id', 'priv_reference');
    }
}
