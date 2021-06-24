<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFile extends Model
{
    protected $table = 'Product_File';
    protected $primaryKey = 'product_file_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['resource_id', 'stand_product_id'];

    function resource() 
    {
        return $this->belongsTo(Resource::class,'resource_id','resource_id');
    }
}
