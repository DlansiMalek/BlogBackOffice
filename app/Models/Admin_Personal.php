<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Admin_Personal extends Model
{
    protected $table = 'Admin_Personal';
    protected $primaryKey = 'id_Admin_Personal';
    protected $fillable = ['id_Admin', 'id_Organisateur'];

    public $timestamps = false;

}