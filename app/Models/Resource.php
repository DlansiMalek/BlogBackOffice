<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    public $timestamps = true;
    protected $table = 'Resource';
    protected $primaryKey = 'resource_id';
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = ['access_id', 'path'];

    public function access()
    {
        return $this->belongsTo('App\Models\Access', 'access_id', 'access_id');
    }
}
