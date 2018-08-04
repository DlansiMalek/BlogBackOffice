<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lieu_Ex extends Model
{
    public $timestamps = true;
    protected $table = 'Lieu_Ex';
    protected $primaryKey = 'lieu_ex_id';
    protected $fillable = ['label'];
    protected $dates = ['created_at', 'updated_at'];


}