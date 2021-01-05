<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceSubmission extends Model
{
    public $timestamps = true;
    protected $table = 'Resource_Submission';
    protected $primaryKey = 'resource_submission_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['resource_id', 'submission_id'];

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id', 'resource_id');
    }

}
