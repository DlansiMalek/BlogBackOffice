<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type_Access extends Model
{
    protected $table = 'Type_Access';
    protected $primaryKey = 'type_access_id';
    protected $fillable = ['name'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];


}