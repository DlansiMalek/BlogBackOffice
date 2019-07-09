<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'Country';
    protected $primaryKey = 'country_id';
    protected $fillable = ['name','code','label','nationality','nationality_arabe'];
    public $timestamps = false;

    public function cities(){
        return $this->hasMany('App\Models\City','city_id','city_id');
    }
}