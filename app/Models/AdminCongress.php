<?php
/**
 * Created by PhpStorm.
 * User: Abbes
 * Date: 25/08/2016
 * Time: 23:16
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminCongress extends Model
{
    protected $table = 'Admin_Congress';
    protected $primaryKey = 'admin_congress_id';
    protected $fillable = ['admin_id', 'congress_id', 'organization_id', 'privilege_id'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $timestamps = true;

    public function congress()
    {
        return $this->belongsTo('App\Models\Congress', 'congress_id', 'congress_id');
    }

    public function admin()
    {
        return $this->belongsTo('App\Models\Admin', 'admin_id', 'admin_id');
    }

    public function privilege()
    {
        return $this->belongsTo('App\Models\Privilege', 'privilege_id', 'privilege_id');
    }
}