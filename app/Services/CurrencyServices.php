<?php

namespace App\Services;

use App\Models\Currency;

class CurrencyServices
{

    public function getAllCurrencies()
    {
        return Currency::all();
    }

    public function getConvertCurrency($convertFrom, $convertTo)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET',
            UrlUtils::getBaseCurrencyRates() . "/convert?q=" . $convertFrom . '_' . $convertTo . "&compact=ultra&apiKey=" . env('API_CURRENCY_KEY', ''));

        return $res->getBody();
    }
}
