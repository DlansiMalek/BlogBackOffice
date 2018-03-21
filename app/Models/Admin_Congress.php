<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin_Congress extends Model
{
    protected $table = 'Admin_Congress';
    protected $primaryKey = 'admin_congress_id';
    protected $fillable = ['admin_id', 'congress_id'];

    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;


}