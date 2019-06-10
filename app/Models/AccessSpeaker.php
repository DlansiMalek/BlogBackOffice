<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessSpeaker extends Model
{
    public $timestamps = true;
    protected $table = 'Access_Speaker';
    protected $primaryKey = 'speaker_access_id';
    protected $fillable = ['user_id', 'access_id'];
    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
}
