<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stand extends Model
{

    public $timestamps = true;
    protected $table = 'Stand';
    protected $primaryKey = 'stand_id';
    protected $fillable = ['name', 'congress_id', 'organization_id', 'url_streaming'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

}
