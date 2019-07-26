<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'module';
    protected $primaryKey = 'module_id';
    protected $fillable = ['type','description'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function Adminpacks()
    {
        return $this->belongsToMany('App\Models\PackAdmin', 'packadmin_module', 'pack_id','module_id');
    }
    
}
