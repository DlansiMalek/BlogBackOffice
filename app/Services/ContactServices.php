<?php

namespace App\Services;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class ContactServices{


    public function addContact($user_id,$user_view_id,$congressId=null) {
        $contact = new Contact();
        $contact->user_id = $user_id;
        $contact->user_viewed = $user_view_id;
        $contact->congress_id = $congressId;
        $contact->save();
        return $contact;

    }

    public function getContactByUserViewedId($user_view_id,$user_id,$congress_id=null){
        return Contact::where('user_viewed','=',$user_view_id)->where('user_id','=',$user_id)
        ->when($congress_id && $congress_id!=='null' ,function($query) use($congress_id) {
            return $query->where('congress_id','=',$congress_id);
        })
        ->first();
    }
    
    public function getAllContacts($offset,$perPage,$search,$congressId,$user_id) {
        return  Contact::where('Contact.user_id','=',$user_id) 
        ->when($congressId!=='null' && $congressId ,function($query) use($congressId) {
            return $query->where('congress_id',$congressId);
        })
        ->with(['congress' => function($query) {
            $query->select(['congress_id','name']);
        }])
        ->join('User','User.user_id','=','Contact.user_viewed') //jointure interne pour le filtrage
        ->where(DB::raw('CONCAT(first_name," ",last_name)'),'LIKE', '%' . $search . '%')
        ->select(['contact_id','user_viewed','Contact.user_id','first_name','last_name','congress_id'])
        ->offset($offset)->limit($perPage)
        ->get();
       
    }
}
