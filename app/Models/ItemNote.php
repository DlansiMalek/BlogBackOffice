<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemNote extends Model
{
    protected $table = 'Item_Note';
    protected $primaryKey = 'item_note_id';
    protected $fillable = ['note', 'comment','item_evaluation_id','evaluation_inscription_id'];


    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at','deleted_at'];

    public function evaluationInscription() {
        return   $this->belongsTo('App\Models\Evaluation_Inscription','evaluation_inscription_id','evaluation_inscription_id');
    }

    public function itemEvaluation(){
        return $this->hasOne('App\Models\ItemEvaluation','item_evaluation_id','item_evaluation_id');
    }
}
