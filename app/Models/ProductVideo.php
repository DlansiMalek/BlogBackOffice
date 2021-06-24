<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVideo extends Model
{
    protected $table = 'Product_Video';
    protected $primaryKey = 'product_video_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['resource_id', 'stand_product_id'];

    function resource() 
    {
        return $this->belongsTo(Resource::class,'resource_id','resource_id');
    }
}
