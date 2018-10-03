<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Labo extends Model
{
    public $timestamps = true;
    protected $table = 'Labo';
    protected $primaryKey = 'labo_id';
    protected $fillable = ['name', 'description'];
    protected $dates = ['created_at', 'updated_at'];


    function users(){
        return $this->hasMany('App\Models\User', 'labo_id', 'labo_id');
    }

}