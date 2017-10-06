<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Model;

class Admin extends Authenticatable
{
    protected $table = 'Admin';
    protected $primaryKey = 'id_Admin';
    protected $fillable = ['email', 'password', 'passwordDecrypt', 'name'];

    protected $hidden = ["password", "passwordDecrypt"];
    public $timestamps = false;

}