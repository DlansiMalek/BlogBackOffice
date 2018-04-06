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

class SGroupe_Service extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'sgroupe_service_id';
    protected $table = 'SGroupe_Service';
    public $timestamps = true;
    protected $fillable = ['s_groupe_id', 'service_id'];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
}
