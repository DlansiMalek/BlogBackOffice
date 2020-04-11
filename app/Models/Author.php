<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = 'Author';
    protected $primaryKey = 'author_id';
    protected $fillable = ['first_name','last_name','rank'];

    protected $hidden = ["password"];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;



    public function submission() {
        return $this->hasMany('App\Models\Submission','author_id');
    }
}
