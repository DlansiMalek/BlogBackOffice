<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeOffre extends Model
{
    protected $table = 'Type_Offre';
    protected $primaryKey = 'type_offre_id';
    protected $fillable = ['name'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];
}
