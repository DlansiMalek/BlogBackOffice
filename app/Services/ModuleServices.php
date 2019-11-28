<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleServices
{

    public function getModuleById($moduleId)
    {
        return Module::where('module_id', '=', $moduleId)
            ->first();
    }

    public function getAllModules (){ return Module::all();}


}