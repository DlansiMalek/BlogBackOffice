<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CongressTheme extends Model
{
    public $timestamps = true;
    protected $table = 'Congress_Theme';
    protected $primaryKey = 'congress_theme_id';
    protected $fillable = ['theme_id', 'congress_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    function theme()
    {
        return $this->belongsTo('App\Models\Theme', 'theme_id', 'theme_id');
    }

    function congress()
    {
        return $this->belongsTo('App\Models\Congress', 'congress_id', 'congress_id');
    }
}
