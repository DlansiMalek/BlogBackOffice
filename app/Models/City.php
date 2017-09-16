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
    protected $table = 'City';
    protected $primaryKey = 'id_City';
    protected $fillable = ['Name', 'CountryCode'];

    public $timestamps = false;

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'CountryCode', 'Code');
    }
}