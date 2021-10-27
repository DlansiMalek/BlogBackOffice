<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigLP extends Model
{
    protected $table = 'Config_LP';
    protected $primaryKey = 'config_lp_id';
    protected $fillable = [
        'congress_id',
        'header_logo_event',
        'is_inscription',
        'register_link',
        'home_banner_event',
        'home_start_date',
        'home_end_date',
        'home_title',
        'home_description',
        'prp_banner_event',
        'prp_title',
        'prp_description',
        'speaker_title',
        'speaker_description',
        'sponsor_title',
        'sponsor_description',
        'prg_title',
        'prg_description',
        'contact_title',
        'contact_description',
        'event_link_fb',
        'event_link_instagram',
        'event_link_linkedin',
        'event_link_twitter',
        'theme_color',
        'theme_mode',
        'name_partenaire',
        'link_partenaire',
        'show_date',
        'background_color',
        'opacity_color'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    function FMenu() {
        return $this->hasMany(FMenu::class,'congress_id','congress_id');
    }
}
