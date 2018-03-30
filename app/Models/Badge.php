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
    protected $fillable = ['img_name', 'qr_code_choice', 'text_choice'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];


}