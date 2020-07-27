<?php

namespace App\Services;

use App\Models\Currency;
use GuzzleHttp\Client;

class CurrencyServices
{

    public function getAllCurrencies()
    {
        return Currency::all();
    }

    public function getConvertCurrency($convertFrom, $convertTo)
    {
        $client = new Client(['verify'=> 'C:/xampp/apache/bin/mycert.pem']);
        $res = $client->request('GET',
            UrlUtils::getBaseCurrencyRates() . "/convert?q=" . $convertFrom . '_' . $convertTo . "&compact=ultra&apiKey=" . env('API_CURRENCY_KEY', ''));

        return $res->getBody();
    }
}
