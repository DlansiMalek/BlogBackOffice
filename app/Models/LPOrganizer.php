<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LPOrganizer extends Model
{
    protected $table = 'LP_Organizer';
    protected $primaryKey = 'lp_organizer_id';
    protected $fillable = [
        'full_name',
        'role',
        'profile_img',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;
}
