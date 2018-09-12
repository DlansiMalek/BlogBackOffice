<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 12/09/2018
 * Time: 14:40
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Access_Presence extends Model
{
    protected $table = 'Access_Presence';
    protected $primaryKey = 'access_presence_id';
    protected $fillable = ['enter_time', 'leave_time', 'user_id', 'access_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];
}