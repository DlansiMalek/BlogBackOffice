<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMailAdmin extends Model
{
    public $timestamps = true;
    protected $table = 'User_Mail_Admin';
    protected $primaryKey = 'user_mail_admin_id';
    protected $fillable = ['user_id','mail_admin_id','status'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','user_id');
    }

    public function mailAdmin(){
        return $this->belongsTo('App\Models\MailAdmin','mail_admin_id','mail_admin_id');
    }
}