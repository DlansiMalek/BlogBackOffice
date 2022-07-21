<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    public $timestamps = true;
    protected $table = 'Response';
    protected $primaryKey = 'response_id';
    protected $fillable = ['response', 'user_id', 'congress_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

   
}