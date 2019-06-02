<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChairPerson_Access extends Model
{
    public $timestamps = true;
    protected $table = 'ChairPerson_Access';
    protected $primaryKey = 'chairperson_access_id';
    protected $fillable = ['user_id', 'access_id', 'isPresent'];
    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
}
