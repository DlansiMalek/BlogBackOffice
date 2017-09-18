<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Congress extends Authenticatable
{
    protected $table = 'Congress';
    protected $primaryKey = 'id_Congress';
    protected $fillable = ['name', 'date', 'login', 'password', 'passwordDecrypte'];

    public $timestamps = false;

    public $incrementing = false;
    protected $hidden = ['password', 'passwordDecrypte'];
}