<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FMenu extends Model
{
    protected $table = 'FMenu';
    protected $primaryKey = 'FMenu_id';
    protected $fillable = [
        'key',
        'fr_label',
        'en_label',
        'is_visible',
        'congress_id',
        'rank',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    function confingLP()
    {
        return $this->belongsTo(confingLP::class, 'congress_id', 'congress_id');
    }
}
