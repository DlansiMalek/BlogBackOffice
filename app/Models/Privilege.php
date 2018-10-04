<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Privilege extends Model
{
    protected $table = 'Privilege';
    protected $primaryKey = 'privilege_id';
    protected $fillable = ['name'];

    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;

    public function badges()
    {
        return $this->hasMany('App\Models\Badge', 'privilege_id', 'privilege_id');
    }

}