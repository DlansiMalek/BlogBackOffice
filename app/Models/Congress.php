<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Congress extends Model
{
    protected $table = 'Congress';
    protected $primaryKey = 'congress_id';
    protected $fillable = ['name', 'date', 'object_mail_inscription', 'admin_id', 'object_mail_attestation',
        'logo', 'username_mail'];

    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;


    public function accesss()
    {
        return $this->hasMany('App\Models\Access', "congress_id", "congress_id");
    }


    public function users()
    {
        return $this->hasMany('App\Models\User', 'congress_id', 'congress_id');
    }

    public function attestation()
    {
        return $this->hasOne('App\Models\Attestation', 'congress_id', 'congress_id');
    }

    public function badges()
    {
        return $this->hasMany('App\Models\Badge', 'congress_id', 'congress_id');
    }
}