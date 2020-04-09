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
    protected $fillable = ['name', 'start_date', 'end_date', 'price', 'congress_type_id', 'description'];

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

    public function theme()
    {
        return $this->belongsToMany('App\Models\Theme','Submission_Theme','congress_id','theme_id');
    }

    public function badges()
    {
        return $this->hasMany('App\Models\Badge', 'congress_id', 'congress_id');
    }

    public function ConfigSubmission(){
        return $this->hasOne('App\Models\ConfigSubmission','congress_id','congress_id');
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
        return $this->belongsToMany('App\Models\Admin', 'Admin_Congress', 'congress_id', 'admin_id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Payment', 'congress_id', 'congress_id');
    }

    public function admin_congresses()
    {
        return $this->hasMany('App\Models\AdminCongress', 'congress_id', 'congress_id');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'User_Congress', 'congress_id', 'user_id');
    }

    public function accesss()
    {
        return $this->hasMany('App\Models\Access', "congress_id", "congress_id")->whereNull('parent_id')->orderBy('start_date');
    }

    public function packs()
    {
        return $this->hasMany('App\Models\Pack', "congress_id", "congress_id");
    }

    public function form_inputs()
    {
        return $this->hasMany("App\Models\FormInput", "congress_id", "congress_id");
    }

    public function location()
    {
        return $this->hasOne('App\Models\Location', 'congress_id', 'congress_id');
    }
}
