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
        $contact->subject   = $request->input("subject");
        $contact->message   = $request->input("message");
        $contact->save();
        return $contact;
    }
    public function renderMail($template, $admin = null, $user = null, $activationLink = null, $linkBackOffice = null, $paymentLink = null, $contact)
    {
        $template = str_replace('{{$admin-&gt;email}}', '{{$admin->email}}', $template);
        $template = str_replace('{{$admin-&gt;passwordDecrypt}}', '{{$admin->passwordDecrypt}}', $template);
        $template = str_replace('{{$admin-&gt;name}}', '{{$admin->name}}', $template);
        $template = str_replace('{{$user-&gt;first_name}}', '{{$user->first_name}}', $template);
        $template = str_replace('{{$user-&gt;last_name}}', '{{$user->last_name}}', $template);
        $template = str_replace('{{$user-&gt;gender}}', '{{$user->gender}}', $template);

        $template = str_replace('{{$contact-&gt;message}}', '{{$contact->message}}', $template);
        $template = str_replace('{{$contact-&gt;subject}}', '{{$contact->subject}}', $template);
        $template = str_replace('{{$contact-&gt;email}}', '{{$contact->email}}', $template);
        $template = str_replace('{{$contact-&gt;user_name}}', '{{$contact->user_name}}', $template);

        return view(['template' => '<html>' . $template . '</html>'], ['admin' => $admin, 'user' => $user, 'linkBackOffice' => $linkBackOffice, 'activationLink' => $activationLink, 'paymentLink' => $paymentLink,'contact' => $contact]);
    }
}
