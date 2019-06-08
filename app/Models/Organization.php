<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    public $timestamps = true;
    protected $table = 'Organization';
    protected $primaryKey = 'organization_id';
    protected $fillable = ['name', 'description','email','mobile','admin_id'];
    protected $dates = ['created_at', 'updated_at'];


    function users()
    {
        return $this->hasMany('App\Models\User', 'organization_id', 'organization_id');
    }

}