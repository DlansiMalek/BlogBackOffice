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
    protected $primaryKey = 'id_Admin_Privilege';
    protected $fillable = ['id_Admin', 'id_Privilege'];

    public $timestamps = false;

}