<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'City';
    protected $primaryKey = 'city_id';
    protected $fillable = ['name', 'country_code', 'name_ar'];

    // protected $dates = ['created_at', 'updated_at'];
    public $timestamps = false;

    public function locations(){
        return $this->hasMany('App\Models\Location','location_id','location_id');
    }
    public function country(){
        return $this->belongsTo('App\Models\Country','country_code','code');
    }
}
