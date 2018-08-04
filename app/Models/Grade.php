<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    public $timestamps = true;
    protected $table = 'Grade';
    protected $primaryKey = 'grade_id';
    protected $fillable = ['label'];
    protected $dates = ['created_at', 'updated_at'];


}