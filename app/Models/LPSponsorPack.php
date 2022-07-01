<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LPSponsorPack extends Model
{
    protected $table = 'LP_Sponsor_Pack';
    protected $primaryKey = 'lp_sponsor_pack_id';
    protected $fillable = [
        'congress_id',
        'description',
        'description_en',
        'description_ar'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;
}
