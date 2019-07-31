<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackAdmin extends Model
{
    protected $table = 'pack_admin';
    protected $primaryKey = 'pack_id';
    protected $fillable = ['name','type','capacity','price','nbr_days','nbr_events'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function modules()
    {
        return $this->belongsToMany('App\Models\Module', 'packadmin_module', 'module_id','pack_id');
    }
}
