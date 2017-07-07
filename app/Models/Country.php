<?php
/**
 * Created by IntelliJ IDEA.
 * User: S4M37
 * Date: 07/07/2017
 * Time: 13:46
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Country extends Model
{

    protected $table = 'countries';
    protected $primaryKey = 'country_id';
    protected $fillable = [
        'country_id', 'name', 'code'
    ];

    function cities()
    {
        return $this->hasMany('cities', 'country_id');
    }
    
}