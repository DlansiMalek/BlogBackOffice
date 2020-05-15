<?php

namespace App\Services;

use App\Models\Currency;

class CurrencyServices {

    public function getAllCurrencies() {
        return Currency::all();
    }
}