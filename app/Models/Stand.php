<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stand extends Model
{
    protected $table = 'Stand';
    protected $primaryKey = 'stand_id';
    protected $fillable = ['url_streaming', 'name'];
    public $timestamps = false;
}
