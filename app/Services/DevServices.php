<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;

class DevServices {
    public function clearCache() {
        return Artisan::call('cache:clear');
    }
}