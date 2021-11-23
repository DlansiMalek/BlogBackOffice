<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMail extends Model
{
    public $timestamps = true;
    protected $table = 'User_Mail';
    protected $primaryKey = 'user_mail_id';
    protected $fillable = ['user_id','mail_id','status'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','user_id');
    }

    public function mail(){
        return $this->belongsTo('App\Models\Mail','mail_id','mail_id');
    }
}

/*
--> status:
0 : creation
1 : success
2 : waiting
-1: error
*/