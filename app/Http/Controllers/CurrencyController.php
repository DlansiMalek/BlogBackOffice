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

    public function getAllCurrencies()
    {
        return $this->currencyServices->getAllCurrencies();
    }

    public function getConvertCurrency(Request $request)
    {
        $convertFrom = $request->query('convertFrom');
        $convertTo = $request->query('convertTo');
        return $this->currencyServices->getConvertCurrency($convertFrom, $convertTo);
    }
}
