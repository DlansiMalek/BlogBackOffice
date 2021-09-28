<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\SubmissionEvaluation;
/**
 * @property mixed responsible
 * @property mixed admin_id
 * @property mixed email
 * @property mixed name
 * @property mixed mobile
 * @property mixed passwordDecrypt
 */
class Admin extends Authenticatable implements JWTSubject
{
    protected $table = 'Admin';
    protected $primaryKey = 'admin_id';
    protected $fillable = ['email', 'mobile', 'name', "privilege_id", 'voting_token', 'passwordDecrypt','status','valid_date'];

    protected $hidden = ["password"];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function congresses()
    {
        return $this->belongsToMany('App\Models\Congress', 'Admin_Congress', 'admin_id', 'congress_id');
    }

    public function admin_congresses()
    {
        return $this->hasMany('App\Models\AdminCongress', 'admin_id', 'admin_id');
    }

    public function submissionEvaluation(){

        return $this->hasMany('App\Models\SubmissionEvaluation','admin_id','admin_id');
    }

    function themeAdmin()
    {

        return $this->hasMany('App\Models\ThemeAdmin','admin_id','admin_id');
    }
    function theme()
    {
        return $this->belongsToMany('App\Models\Theme','Theme_Admin','admin_id','theme_id');
    }

    public function submission()
    {
        return $this->belongsToMany('App\Models\Submission', 'Submission_Evaluation', 'admin_id', 'submission_id');
    }
    public function evaluations()
    {
        return $this->belongsToMany('App\Models\User',  'Evaluation_Inscription', 'admin_id', 'user_id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Payment', 'admin_id', 'admin_id');
    }

    public function adminPayment()
    {
        return $this->hasMany('App\Models\PaymentAdmin', 'admin_id', 'admin_id');
    }

    public function offres()
    {
        return $this->hasMany('App\Models\Offre', 'admin_id', 'admin_id');
    }
    public function projects()
    {
        return $this->hasMany('App\Models\Project', 'admin_id', 'admin_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

}
