<?php

namespace App\Models;

 
use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    public $timestamps = true;
    protected $table = 'FAQ';
    protected $primaryKey = 'FAQ_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['stand_id', 'question' ,'response'];

    function stand() {
        return $this->belongsTo(Stand::class,'stand_id','stand_id');
    }
}
