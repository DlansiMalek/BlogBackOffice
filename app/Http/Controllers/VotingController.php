<?php

namespace App\Http\Controllers;


use App\Models\Congress;
use App\Models\Feedback_Question_Type;
use App\Models\Feedback_Response;
use App\Services\AdminServices;
use App\Services\FeedbackService;
use App\Services\VotingService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class VotingController extends Controller
{

    protected $votingService;
    protected $adminService;

    function __construct(VotingService $votingService, AdminServices $adminServices)
    {
        $this->votingService = $votingService;
        $this->adminService =$adminServices;
    }


    public function setToken(Request $request){
        if (!$request->has('token')) return response()->json(['error'=>'no token in request'],400);
        if (!$admin = $this->adminService->getConnectedAdmin($request)) return response()->json(['error'=>'Unauthorized'],403);
        $admin->voting_token = $request->get('token');
        $admin->update();
        $personnel = $this->adminService->getListPersonelsByAdmin($admin->admin_id);
        if ($personnel)
            foreach ($personnel as $p){
                $p->voting_token = $request->get('token');
                $p->update();
            }
        return $admin->voting_token;
    }

    public function getToken(Request $request){
        if (!$admin = $this->adminService->getConnectedAdmin($request)) return response()->json(['error'=>'Unauthorized'],403);
        return $admin->voting_token;
    }
}
