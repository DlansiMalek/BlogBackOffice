<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivilegeConfig extends Model
{
    protected $table = 'Privilege_Config';
    protected $primaryKey = 'privilege_config_id';
    protected $fillable = ['privilege_id', 'congress_id', 'status'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function privilege () {
        return $this->belongsTo('App\Models\Privilege', 'privilege_id', 'privilege_id');
    }

    public function privilege_config_children()
    {
        return $this->hasMany(PrivilegeConfigChildren::class, 'privilege_config_id', 'privilege_config_id');
    }
}
