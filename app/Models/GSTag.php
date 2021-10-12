<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GSTag extends Model
{
    protected $table = 'GSTag';
    protected $primaryKey = 'gstag_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['label', 'congress_id'];


    function tags() {
        return $this->hasMany(STag::class,'stag_id','stag_id');
    }
}