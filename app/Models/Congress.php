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
    protected $fillable = ['name', 'start_date', 'end_date', 'price'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;


    public function config()
    {
        return $this->hasOne('App\Models\ConfigCongress', 'congress_id', 'congress_id');
    }

    public function mail_config()
    {
        return $this->hasOne('App\Models\Mail_Config', 'congress_id', 'congress_id');
    }

    public function badges()
    {
        return $this->hasMany('App\Models\Badge', 'congress_id', 'congress_id');
    }

    public function mails()
    {
        return $this->hasMany('App\Models\Mail', 'congress_id', 'congress_id');
    }

    public function attestation()
    {
        return $this->hasOne('App\Models\Attestation', 'congress_id', 'congress_id');
    }

    public function admins()
    {
        return $this->belongsToMany('App\Models\Admin', 'Admin_Congress','congress_id', 'admin_id');
    }

    public function payments(){
        return $this->hasMany('App\Models\Payment','congress_id','congress_id');
    }


    public function admin_congresses(){
        return $this->hasMany('App\Models\AdminCongress','congress_id','congress_id');
    }


    public function users()
    {
        return $this->belongsToMany('App\Models\Congress', 'UserCongress', 'user_id', 'congress_id');
    }

    public function accesses()
    {
        return $this->hasMany('App\Models\Access', "congress_id", "congress_id");
    }


    public function packs()
    {
        return $this->hasMany('App\Models\Pack', "congress_id", "congress_id");
    }

    public function form_inputs()
    {
        return $this->hasMany("App\Models\FormInput", "congress_id", "congress_id");
    }


//
//    public function organizations()
//    {
//        return $this->belongsToMany("App\Models\Organization", 'Congress_Organization', "congress_id", "organization_id")->withPivot('montant');
//    }
//
//    public function feedback_questions()
//    {
//        return $this->hasMany("App\Models\Feedback_Question", "congress_id", "congress_id");
//    }

}