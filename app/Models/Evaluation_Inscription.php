<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation_Inscription extends Model
{
    protected $table = 'Evaluation_Inscription';
    protected $primaryKey = 'evaluation_inscription_id';
    protected $fillable = ['commentaire','note','admin_id','user_id','congress_id'];
    public $timestamps = false;


 function admin() {
     return $this->hasMany('App\Models\Admin','admin_id','admin_id');
 }

}
