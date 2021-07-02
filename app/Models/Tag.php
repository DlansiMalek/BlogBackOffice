<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'Tag';
    protected $primaryKey = 'tag_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['label', 'congress_id'];

    function congresses()
    {
        return $this->belongsToMany('App\Models\StandProduct', 'Product_Tag', 'tag_id', 'stand_product_id');
    }
}
