<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'Currency';
    protected $primaryKey = 'code';
    protected $fillable = ['label'];
    public $incrementing = false;
    public $timestamps = false;
}
