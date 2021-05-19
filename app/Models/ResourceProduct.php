<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceProduct extends Model
{
    public $timestamps = true;
    protected $table = 'resource_product';
    protected $primaryKey = 'resource_product_id ';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['file_name','stand_id', 'resource_id'];

    function resource() {
        return $this->belongsTo(Resource::class,'resource_id','resource_id');
    }
}
