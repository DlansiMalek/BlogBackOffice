<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSms extends Model
{
    public $timestamps = true;
    protected $table = 'User_Sms';
    protected $primaryKey = 'user_sms_id';
    protected $fillable = ['user_id', 'custom_sms_id', 'status'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];


    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
    
}
