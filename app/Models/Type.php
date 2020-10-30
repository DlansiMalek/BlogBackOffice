<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'Type';
    protected $primaryKey = 'type_id';
    protected $fillable = ['name'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];

}
