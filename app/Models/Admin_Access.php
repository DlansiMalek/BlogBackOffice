<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin_Access extends Model
{
    protected $table = 'Admin_Access';
    protected $primaryKey = 'admin_access_id';
    protected $fillable = ['admin_id', 'access_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];


}