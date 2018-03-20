<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Admin_Privilege extends Model
{
    protected $table = 'Admin_Privilege';
    protected $primaryKey = 'admin_privilege_id';
    protected $fillable = ['admin_id', 'privilege_id'];

    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;

}