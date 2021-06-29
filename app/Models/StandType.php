<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandType extends Model
{
    public $timestamps = true;
    protected $table = 'Stand_Type';
    protected $primaryKey = 'stand_type_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['name','preview_img', 'is_fixed' ,'is_publicity'];
}
