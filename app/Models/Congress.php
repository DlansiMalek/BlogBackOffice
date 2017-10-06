<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;

class Congress
{
    protected $table = 'Congress';
    protected $primaryKey = 'id_Congress';
    protected $fillable = ['name', 'date'];

    public $timestamps = false;
}