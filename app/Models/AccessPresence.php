<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 12/09/2018
 * Time: 14:40
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AccessPresence extends Model
{
    protected $table = 'Access_Presence';
    protected $primaryKey = 'access_presence_id';
    protected $fillable = ['entered_at', 'left_at', 'user_id', 'access_id'];

    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }

    public function access()
    {
        return $this->belongsTo('App\Models\Access', 'access_id', 'access_id');
    }
}