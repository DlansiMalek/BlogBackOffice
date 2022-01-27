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

    public function admin()
    {
        return $this->hasOne('App\Models\Admin', 'admin_id', 'admin_id');
    }
    public function category()
    {
        return $this->hasOne('App\Models\Category', 'category_id', 'category_id');
    }
}
