<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public $timestamps = true;
    protected $table = 'Contact';
    protected $primaryKey = 'contact_id';
    protected $fillable = ['user_id','user_viewed','congress_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

   public function congress(){
        return $this->belongsTo('App\Models\Congress','congress_id','congress_id');
    }
}
