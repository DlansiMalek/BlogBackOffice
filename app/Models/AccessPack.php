<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AccessPack extends Model
{
    protected $table = 'Access_Pack';
    protected $primaryKey = 'access_pack_id';
    protected $fillable = ['access_pack_id', 'access_id', 'pack_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];

}