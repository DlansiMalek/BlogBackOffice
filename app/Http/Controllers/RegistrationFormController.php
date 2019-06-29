<?php

namespace App\Http\Controllers;


use App\Services\RegistrationFormServices;
use Illuminate\Http\Request;

class RegistrationFormController extends Controller
{

    private $registrationFormServices;

    function __construct(RegistrationFormServices $registrationFormServices)
    {
        $this->registrationFormServices = $registrationFormServices;
    }

    public function getForm($congressId)
    {
        return $this->registrationFormServices->getForm($congressId);
    }

    public function getInputTypes()
    {
        return $this->registrationFormServices->getInputTypes();
    }

    public function setForm(Request $request, $congress_id)
    {
        $this->registrationFormServices->setForm($request, $congress_id);
        return $this->registrationFormServices->getForm($congress_id);
    }

}