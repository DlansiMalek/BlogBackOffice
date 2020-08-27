<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemEvaluation extends Model
{
    protected $table = 'Item_Evaluation';
    protected $primaryKey = 'item_evaluation_id';
    protected $fillable = ['label', 'ponderation','congress_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];

    function itemNote() {
        return $this->hasMany('App\Models\ItemNote','item_evaluation_id','item_evaluation_id');
    }
}
