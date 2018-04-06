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

class S_Periode extends Model
{
    use SoftDeletes;
    protected $primaryKey = 's_periode_id';
    protected $table = 'S_Periode';
    public $timestamps = true;
    protected $fillable = ['start_date', 'end_date', 'periode_id', 's_groupe_id'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
}
