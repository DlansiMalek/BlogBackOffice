<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    public $timestamps = true;
    protected $table = 'Like';
    protected $primaryKey = 'like_id';
    protected $fillable = ['user_id','access_id'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];

}