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
    protected $fillable = ['name', 'description', 'email', 'mobile', 'admin_id','resource_id','is_sponsor','logo_position'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];


    function users()
    {
        return $this->hasMany('App\Models\User', 'organization_id', 'organization_id');
    }

    function congressOrganization()
    {
        return $this->hasMany(CongressOrganization::class, 'organization_id', 'organization_id');
    }

    function admin()
    {
        return $this->hasOne(Admin::class, 'admin_id', 'admin_id');
    }

    function stands()
    {
        return $this->hasMany(Stand::class, 'organization_id', 'organization_id');
    }

    function resource() {
        return $this->hasOne(Resource::class,'resource_id','resource_id');
    }
}
