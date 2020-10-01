<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stand extends Model
{
    public $timestamps = true;
    protected $table = 'Stands';
    protected $primaryKey = 'stand_id';
    protected $fillable = ['name','organization_id','congress_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

   public function congress(){
        return $this->belongsTo('App\Models\Congress','congress_id','congress_id');
    }
}
