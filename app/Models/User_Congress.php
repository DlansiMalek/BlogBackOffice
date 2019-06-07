<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Congress extends Model
{
    public $timestamps = true;
    protected $table = 'User_Congress';
    protected $primaryKey = 'user_congress_id';
    protected $fillable = ['user_id', 'congress_id'];
    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
    public function privilege()
    {
        return $this->hasOne('App\Models\Privilege', 'privilege_id', 'privilege_id');
    }
}
