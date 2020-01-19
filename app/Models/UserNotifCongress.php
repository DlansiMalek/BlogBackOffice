<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserNotifCongress extends Model
{
    use SoftDeletes;

    public $timestamps = true;
    protected $table = 'User_Notif_Congress';
    protected $primaryKey = 'user_notif_congress_id';
    protected $fillable = ['congress_id', 'user_id', 'firebase_key_user'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

}