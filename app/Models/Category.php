<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'Category';
    protected $primaryKey = 'category_id';
    protected $fillable = ['label'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];

    public function projects()
    {
        return $this->hasMany('App\Models\Project', 'category_id', 'category_id');
    }
}
