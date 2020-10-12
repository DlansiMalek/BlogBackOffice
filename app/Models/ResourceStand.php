<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceStand extends Model
{
    public $timestamps = true;
    protected $table = 'Resource_Stand';
    protected $primaryKey = 'resource_stand_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['doc_name','stand_id', 'resource_id'];

    function resource() {
        return $this->belongsTo(Resource::class,'resource_id','resource_id');
    }
}