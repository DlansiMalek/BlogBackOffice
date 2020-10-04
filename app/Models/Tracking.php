<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    public $timestamps = true;
    protected $table = 'Tracking';
    protected $primaryKey = 'tracking_id';
    protected $fillable = ['date', 'type', 'comment', 'action_id', 'access_id', 'stand_id', 'user_id', 'congress_id'];


    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
