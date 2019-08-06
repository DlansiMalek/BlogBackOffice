<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PackModule extends Model
{
    protected $table = 'packadmin_module';
    protected $primaryKey = 'id';
    protected $fillable = ['pack_id', 'module_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];
}
