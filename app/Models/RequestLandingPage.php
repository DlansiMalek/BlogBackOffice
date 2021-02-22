<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLandingPage extends Model
{
    protected $table = 'request_landing_pages';
    protected $primaryKey = 'request_landing_page_id';
    protected $fillable = ['dns', 'status', 'congress_id','admin_id'];

    protected $dates = ['created_at', 'updated_at'];
    public $timestamps = true;
    public function congress()
    {
        return $this->belongsTo(Congress::class, 'congress_id', 'congress_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }
}
