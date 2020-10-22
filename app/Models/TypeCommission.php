<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeCommission extends Model
{
    protected $table = 'Type_Commission';
    protected $primaryKey = 'type_commission_id';
    protected $fillable = ['label'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];
}
