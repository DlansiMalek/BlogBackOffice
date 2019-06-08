<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    public $timestamps = true;
    protected $table = 'Topic';
    protected $primaryKey = 'topic_id';
    protected $fillable = ['name'];
    protected $dates = ['created_at', 'updated_at'];
}

