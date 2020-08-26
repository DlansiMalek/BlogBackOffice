<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Etablissement extends Model
{
    protected $table = 'Etablissement';
    protected $primaryKey = 'etablissement_id';
    protected $fillable = ['label'];
    public $timestamps = false;

}