<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'Country';
    protected $primaryKey = 'alpha3code';
    public $incrementing = false;
    protected $fillable = ['code', 'name', 'phone_code'];
    public $timestamps = false;

    public function cities()
    {
        return $this->hasMany('App\Models\City', 'city_id', 'city_id');
    }
}
