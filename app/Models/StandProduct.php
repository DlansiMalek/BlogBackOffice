<?php

namespace App\Models;

 
use Illuminate\Database\Eloquent\Model;

class StandProduct extends Model
{
    public $timestamps = true;
    protected $table = 'Stand_Product';
    protected $primaryKey = 'stand_product_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['name','stand_id', 'main_img' ,'brochure_file'];

    function stand() {
        return $this->belongsTo(Stand::class,'stand_id','stand_id');
    }
	function docs()
    {
        return $this->belongsToMany(Resource::class,'Resource_Product','stand_product_id','resource_id');
    }
    
	function files()
    {
        return $this->belongsToMany(Resource::class,'Product_File','stand_product_id','resource_id');
    }

    function product_tags()
    {
        return $this->belongsToMany(Tag::class,'Product_Tag','stand_product_id','tag_id');
    }
}
