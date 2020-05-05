<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeParams extends Model
{
    protected $table = 'Badge_Params';
    protected $primaryKey = 'badge_param_id';
    protected $fillable = ['key', 'badge_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];

}
