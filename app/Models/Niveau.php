<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 29/03/2018
 * Time: 11:00
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Niveau extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'niveau_id';
    protected $table = 'Niveau';
    public $timestamps = true;
    protected $fillable = ['label'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
}
