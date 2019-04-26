<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User_Mail extends Model
{
    public $timestamps = true;
    protected $table = 'User_Mail';
    protected $primaryKey = 'user_mail_id';
    protected $fillable = ['user_id','mail_id'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];
}