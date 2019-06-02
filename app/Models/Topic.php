<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    public $timestamps = true;
    protected $table = 'Topic';
    protected $primaryKey = 'topic_id';
    protected $dates = ['created_at', 'updated_at'];

    public function accesses()
    {
        return $this->hasMany('App\Models\Access', 'topic_id', 'topic_id');
    }
}

