<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessType extends Model
{
    public $timestamps = true;
    protected $table = 'Access_Type';
    protected $primaryKey = 'access_type_id';
    protected $fillable = ['label'];

    protected $dates = ['created_at', 'updated_at','deleted_at'];
}
