<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = 'Author';
    protected $primaryKey = 'author_id';
    protected $fillable = ['first_name','last_name','email','rank','service_id','etablissement_id'];

    protected $hidden = ["password"];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function service() {
        return $this->hasOne('App\Models\Service','service_id','service_id');
    }
    public function etablissment() {
        return $this->hasOne('App\Models\Etablissement','etablissement_id','etablissement_id');
    }
    public function submission() {
        return $this->belongsTo('App\Models\Submission','submission_id','submission_id');
    }
}
