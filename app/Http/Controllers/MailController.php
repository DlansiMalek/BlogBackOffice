<?php

namespace App\Http\Controllers;


use App\Models\AttestationRequest;
use App\Models\User;
use App\Models\UserCongress;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\MailServices;
use App\Services\OrganizationServices;
use App\Services\PackServices;
use App\Services\SharedServices;
use App\Services\UserServices;
use App\Services\Utils;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailController extends Controller
{

    protected $mailService;

    function __construct(MailServices $mailService)
    {
        $this->mailService = $mailService;
    }


    public function getAllMailTypes()
    {
        return $this->mailService->getAllMailTypes();
    }

    public function getMailTypeById($mailTypeId)
    {
        return $this->mailService->getMailTypeById($mailTypeId);
    }

    public function getByMailTypeAndCongress($mailTypeId, $congressId)
    {
        return $this->mailService->getMailByTypeAndCongress($mailTypeId, $congressId);
    }
}
