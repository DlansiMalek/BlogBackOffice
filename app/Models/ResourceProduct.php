<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceProduct extends Model
{
    public $timestamps = true;
    protected $table = 'Resource_Product';
    protected $primaryKey = 'resource_product_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['stand_id', 'resource_id', 'file_name'];

    function resource() {
        return $this->belongsTo(Resource::class,'resource_id','resource_id');
    }
}
