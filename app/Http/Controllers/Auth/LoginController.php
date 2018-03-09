<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Metiers\AdminServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    protected $adminServices;

    public function __construct(AdminServices $adminServices)
    {
        $this->adminServices = $adminServices;
    }

    public function loginAdmin(Request $request)
    {
        $credentials = request(['email', 'password']);
        $admin = $this->adminServices->getAdminByLogin($request->input("email"));


        // verify the credentials and create a token for the user
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'invalid credentials'], 401);
        }

        return response()->json(['admin' => $admin, 'token' => $token], 200);
    }
}
