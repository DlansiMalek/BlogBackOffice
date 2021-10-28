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

    function stand() {
        return $this->belongsToMany(Stand::class ,'Stand_Tag' , 'stag_id', 'stand_id');
    }
}
