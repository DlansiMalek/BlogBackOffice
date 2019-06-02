<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    public $timestamps = true;
    protected $table = 'Resource';
    protected $primaryKey = 'resource_id';
    protected $dates = ['created_at', 'updated_at'];

    public function access()
    {
        return $this->belongsTo('App\Models\Access', 'resource_id', 'resource_id');
    }
}
