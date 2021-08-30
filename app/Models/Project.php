<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'Project';
    protected $primaryKey = 'project_id';
    protected $fillable = ['nom', 'date', 'lien', 'admin_id','category_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];
}
