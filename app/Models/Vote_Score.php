<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote_Score extends Model
{
    public $timestamps = true;
    protected $table = 'Vote_Score';
    protected $primaryKey = 'vote_score_id';
    protected $fillable = ['access_vote_id', 'user_id', 'score', 'num_user_vote'];
    protected $dates = ['created_at', 'updated_at'];
}