<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $table = 'Badge';
    protected $primaryKey = 'badge_id';
    protected $fillable = ['badge_id_generator', 'congress_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];


}