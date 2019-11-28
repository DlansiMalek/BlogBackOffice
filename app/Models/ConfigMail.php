<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigMail extends Model
{
    protected $table = 'Config_Mail';
    protected $primaryKey = 'config_mail_id';
    protected $fillable = ['username', 'password', 'mail_name', 'mail_address', 'driver', 'host', 'port','encryption'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;
}