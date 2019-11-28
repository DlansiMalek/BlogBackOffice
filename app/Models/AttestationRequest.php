<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 12/09/2018
 * Time: 21:12
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AttestationRequest extends Model
{
    public $timestamps = true;
    protected $table = 'Attestation_Request';
    protected $primaryKey = 'attestation_request_id';
    protected $fillable = ['done','access_id','user_id','congress_id'];
    protected $dates = ['created_at', 'updated_at','deleted_at'];

    public function access(){
        return $this->hasOne("App\Models\Access",'access_id','access_id');
    }

    public function congress(){
        return $this->hasOne("App\Models\Congress",'congress_id','congress_id');
    }

    public function user(){
        return $this->hasOne("App\Models\User",'user_id','user_id');
    }
}