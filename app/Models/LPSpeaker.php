<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LPSpeaker extends Model
{
    protected $table = 'LP_Speaker';
    protected $primaryKey = 'lp_speaker_id';
    protected $fillable = [
        'congress_id',
        'first_name',
        'last_name',
        'role',
        'profile_img',
        'fb_link',
        'linkedin_link',
        'instagram_link',
        'twitter_link'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;
}
