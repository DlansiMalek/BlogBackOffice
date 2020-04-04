<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'Service';
    protected $primaryKey = 'service_id';
    public $incrementing = false;
    protected $fillable = ['label'];
    public $timestamps = false;

}