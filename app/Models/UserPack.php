<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPack extends Model
{
    public $timestamps = true;
    protected $table = 'User_Pack';
    protected $primaryKey = 'user_pack_id';
    protected $fillable = ['user_id', 'pack_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];


    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }

    public function pack()
    {
        return $this->belongsTo('App\Models\Pack', 'pack_id', 'pack_id');
    }
}
