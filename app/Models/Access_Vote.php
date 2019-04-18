<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Access_Vote extends Model
{
    public $timestamps = true;
    protected $table = 'Access_Vote';
    protected $primaryKey = 'access_vote_id';
    protected $fillable = ['access_id', 'vote_id', 'congress_id'];
    protected $dates = ['created_at', 'updated_at'];

    public function access(){
        return $this->hasOne('App\Models\Access',"access_id","access_id");
    }

    public function score(){
        return $this->hasOne("App\Models\Vote_Score", "access_vote_id","access_vote_id");
    }

}