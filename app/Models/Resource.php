<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    public $timestamps = true;
    protected $table = 'Resource';
    protected $primaryKey = 'resource_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['access_id', 'path', 'size'];

    public function access()
    {

        return $this->belongsTo('App\Models\Access', 'access_id', 'access_id');
    }

    public function resource_stand()
    {

        return $this->hasMany(ResourceStand::class, 'resource_id', 'resource_id');
    }

    public function resource_product()
    {

        return $this->hasMany(ResourceProduct::class, 'resource_id', 'resource_id');
    }

    public function product_file()
    {
        return $this->hasMany(ProductFile::class, 'resource_id', 'resource_id');
    }
}
