<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $timestamps = false;
    protected $table = 'City';
    protected $primaryKey = 'city_id';
    protected $fillable = ['name', 'country_id'];

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id', 'country_id');
    }
}