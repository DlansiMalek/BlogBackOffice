<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Speaker_Access extends Model
{
    public $timestamps = true;
    protected $table = 'Speaker_Access';
    protected $primaryKey = 'speaker_access_id';
    protected $fillable = ['user_id', 'access_id', 'isPresent'];
    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
}
