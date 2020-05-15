<?php

namespace App\Http\Controllers;

use App\Services\CurrencyServices;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected $currencyServices;

    function __construct(CurrencyServices $currencyServices)
    {
        $this->currencyServices = $currencyServices;
    }
    public function getAllCurrencies() {
        return $this->currencyServices->getAllCurrencies();
    }
}
