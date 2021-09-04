<?php

namespace App\Services;

use App\Models\ContactUs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContactUsServices
{

    public function addContactUs($request)
    {
        $contact = new ContactUs();
        $contact->user_name = $request->input('user_name');
        $contact->email     = $request->input('email');
        $contact->mobile   = $request->input("mobile");
        $contact->subject   = $request->input("subject");
        $contact->message   = $request->input("message");
        $contact->save();
        return $contact;
    }
}
