<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessChair extends Model
{
    public $timestamps = true;
    protected $table = 'Access_Chair';
    protected $primaryKey = 'access_chair_id';
    protected $fillable = ['user_id', 'access_id'];
    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
}
