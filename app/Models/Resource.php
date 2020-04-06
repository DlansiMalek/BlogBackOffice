<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    public $timestamps = true;
    protected $table = 'Resource';
    protected $primaryKey = 'resource_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['path', 'size'];

 
}
