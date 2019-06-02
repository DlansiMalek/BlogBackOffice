<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Access_Type extends Model
{
    public $timestamps = true;
    protected $table = 'Access_Type';
    protected $primaryKey = 'access_type_id';
   // protected $fillable = [];
    protected $dates = ['created_at', 'updated_at'];

    public function access(){
        return $this->hasMany('App\Models\Access', 'access_type_id', 'access_type_id');    }
}
