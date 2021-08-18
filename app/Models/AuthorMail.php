<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorMail extends Model
{
    public $timestamps = true;
    protected $table = 'Author_Mail';
    protected $primaryKey = 'author_mail_id';
    protected $fillable = ['author_id','mail_id','status'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];

    public function author(){
        return $this->belongsTo('App\Models\Author','author_id','author_id');
    }

    public function mail(){
        return $this->belongsTo('App\Models\Mail','mail_id','mail_id');
    }
}
