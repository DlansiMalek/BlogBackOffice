<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{

    protected $table = 'Location';
    protected $primaryKey = 'location_id';
    protected $fillable = ['lng', 'lat', 'address', 'congress_id'];

    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;

    public function congress()
    {
        return $this->hasOne('App\Models\Congress', 'congress_id', 'congress_id');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City', 'city_id', 'city_id');
    }
}
