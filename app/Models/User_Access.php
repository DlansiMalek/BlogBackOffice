<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 23/03/2018
 * Time: 00:22
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class User_Access extends Model
{
    public $timestamps = true;
    protected $table = 'User_Access';
    protected $primaryKey = 'user_access_id';
    protected $fillable = ['user_id', 'access_id', 'isPresent'];
    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
}