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
    protected $fillable = ['name', 'date', 'admin_id', 'logo','banner', 'username_mail','has_paiement','free'];

    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;

    public function accesss()
    {
        return $this->hasMany('App\Models\Access', "congress_id", "congress_id");
    }

    public function packs()
    {
        return $this->hasMany('App\Models\Pack', "congress_id", "congress_id");
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

    public function form_inputs(){
        return $this->hasMany("App\Models\Form_Input", "congress_id","congress_id");
    }

    public function mails(){
        return $this->hasMany('App\Models\Mail','congress_id','congress_id');
    }

    public function organizations(){
        return $this->belongsToMany("App\Models\Organization",'Congress_Organization',"congress_id","organization_id")->withPivot('montant');
    }

    public function feedback_questions(){
        return $this->hasMany("App\Models\Feedback_Question", "congress_id","congress_id");
    }

}