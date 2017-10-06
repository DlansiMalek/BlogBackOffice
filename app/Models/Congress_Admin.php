<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Congress_Admin extends Model
{
    protected $table = 'Congress_Admin';
    protected $primaryKey = 'id_Congress_Admin';
    protected $fillable = ['id_Admin', 'id_Congress'];

    public $timestamps = false;

}