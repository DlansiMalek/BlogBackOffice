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

class Periode extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'periode_id';
    protected $table = 'Periode';
    public $timestamps = true;
    protected $fillable = ['session_stage_id', 'start_date', 'end_date', 'end_middle_date', 'start_middle_date'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
}
