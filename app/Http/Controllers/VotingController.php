<?php
/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 15/04/2019
 * Time: 17:36
 */

namespace App\Http\Controllers;


use App\Services\VotingServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VotingController
{

    protected $votingServices;


    function __construct(VotingServices $votingServices)
    {
        $this->votingServices = $votingServices;
    }


    public function getListPolls(Request $request)
    {
        $token = $request->query("token");
        Log::info($token);
        $userResponse = $this->votingServices->signinUser($token);


        return $this->votingServices->getListPolls($userResponse['token']);
    }


}