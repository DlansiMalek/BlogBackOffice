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
    protected $fillable = ['date', 'type', 'comment', 'action_id', 'access_id', 'stand_id', 'user_id', 'congress_id', 'user_call_id'];


    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function user_call()
    {
        return $this->belongsTo(User::class, 'user_call_id', 'user_id');
    }

    public function access()
    {
        return $this->belongsTo(Access::class, 'access_id', 'access_id');
    }

    public function stand()
    {
        return $this->belongsTo(Stand::class, 'stand_id', 'stand_id');
    }

    public function action()
    {
        return $this->belongsTo(Action::class, 'action_id', 'action_id');
    }
}
