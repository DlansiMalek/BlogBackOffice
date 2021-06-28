<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLink extends Model
{
    protected $table = 'Product_Link';
    protected $primaryKey = 'product_link_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['link', 'stand_product_id'];

    public function product()
    {
        return $this->hasOne('App\Models\StandProduct', 'stand_product_id', 'stand_product_id');
    }

}
