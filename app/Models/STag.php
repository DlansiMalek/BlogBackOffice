<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class STag extends Model
{
    protected $table = 'STag';
    protected $primaryKey = 'stag_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['label', 'congress_id'];

    function congresses()
    {
        return $this->belongsTo(Congress::class);
    }
}
