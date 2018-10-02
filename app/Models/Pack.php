<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    public $timestamps = true;
    protected $table = 'Pack';
    protected $primaryKey = 'pack_id';
    protected $fillable = ['label', 'description','price','congress_id'];
    protected $dates = ['created_at', 'updated_at'];


}