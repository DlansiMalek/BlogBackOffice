<?php
/**
 * Created by IntelliJ IDEA.
 * User: S4M37
 * Date: 07/07/2017
 * Time: 13:47
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class City extends Model
{

    protected $table = 'cities';
    protected $primaryKey = 'city_id';
    protected $fillable = [
        'name', 'country_id'
    ];

    function country()
    {
        return $this->hasOne('App\Models\Country', 'country_id', 'country_id');
    }
}