<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'Company';
    protected $primaryKey = 'company_id';
    protected $fillable = [ 'name', 'description', 'congress_id' ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;
}
