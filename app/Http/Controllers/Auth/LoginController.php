<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminServices;
use App\Services\OffreServices;
use App\Services\PrivilegeServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Contracts\Providers\Auth;

class LoginController extends Controller
{

    protected $adminServices;
    protected $userServices;
    protected $privilegeServices;
    protected $offreServices;

    public function __construct(AdminServices $adminServices,
        PrivilegeServices $privilegeServices,
        UserServices $userServices,
        OffreServices $offreServices) {
        $this->adminServices = $adminServices;
        $this->privilegeServices = $privilegeServices;
        $this->userServices = $userServices;
        $this->offreServices = $offreServices;
    }

    /**
     * @SWG\Post(
     *   path="/auth/login/admin",
     *   summary="Login Admin",
     *   operationId="loginAdmin",
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="Email Admin.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="query",
     *     description="Password Admin.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function loginAdmin(Request $request)
    {

        $credentials = request(['email', 'password']);

        $admin = $this->adminServices->getAdminByLogin($request->input("email"));

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'invalid credentials'], 401);
        }

        return response()->json(['admin' => $admin, 'token' => $token], 200);
    }

    public function loginUser(Request $request)
    {
        $credentials = request(['email', 'password']);

        $user = $this->userServices->getUserByEmail($request->input("email"));

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'invalid credentials'], 401);
        }

        // TODO Je le déscative pour l'instant (à valider la tache d'envoi de mail)
        /*if ($user->email_verified == 0) {
        return response()->json(['error' => 'email not verified'], 405);
        }*/

        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    public function forgetPassword(Request $request)
    {
        $admin = $this->adminServices->getAdminByLogin($request['email']);

        if (!$admin) {
            return response()->json(['error' => 'invalid email'], 501);
        }

        $password = $this->adminServices->generateNewPassword($admin);
        // send email
        // ??
        $email = $admin->email;
        Mail::send('forgetPasswordMail', ['user_name' => $admin->name, 'last_name' => $admin->last_name,
            'password' => $password], function ($message) use ($email) {
            $message->to($email)->subject('Change your password');
        });

        return response()->json('check your email', 200);
    }

    /**
     * @SWG\Post(
     *   path="/mobile/login",
     *   summary="Login",
     *   tags={"Mobile"},
     *   operationId="loginAdminMobile",
     *   @SWG\Parameter(
     *     name="QrCode",
     *     in="query",
     *     description="QR code",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function loginAdminMobile(Request $request)
    {
        $admin = $this->adminServices->getAdminByQrCode($request->input("QrCode"));
        $credentials = array(
            "email" => $admin->email,
            "password" => $admin->passwordDecrypt,
        );

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'invalid credentials'], 401);
        }

        return response()->json(['admin' => $admin, 'token' => $token], 200);
    }
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */

    public function redirectToGoogleProvider(Request $request)
    {

        $id = $request->id;
        Session::put('id', $id);

        return Socialite::driver('google')->with(["prompt" => "select_account"])->redirect();

    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */

    public function handleGoogleProviderCallback()
    {
        $id = Session::get('id', url('/'));
        Session::forget('id');
        try {
            $user = Socialite::with('google')->user();
        } catch (\Exception $e) {
            return redirect('/api/login/google/');
        }
        $existingUser = User::where('email', $user->email)->first();
        if (!$existingUser && $id !== null) {
            $compte = "non";
            return redirect()->to(UrlUtils::getBaseUrlFrontOffice() . '/landingpage/' . $id . '/login?&compte=' . $compte);

        }
        if (!$existingUser) {
            $existingUser = $this->userServices->saveUserWithFbOrGoogle($user);
        }
        $token = auth()->login($existingUser, true);
        if ($id !== null) {
            return redirect()->to(UrlUtils::getBaseUrlFrontOffice() . '/landingpage/' . $id . '/login?&token=' . $token . '&user=' . $existingUser->email . '&id=' . $id);

        } else {
            return redirect()->to(UrlUtils::getBaseUrlFrontOffice() . '/login?&token=' . $token . '&user=' . $existingUser->email);
        }
    }
    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToFacebookProvider(Request $request)
    {
        $id = $request->id;
        Session::put('id', $id);
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from facebook.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleFacebookProviderCallback()
    {
        $id = Session::get('id', url('/'));
        Session::forget('id');
        try {
            $user = Socialite::with('facebook')->user();
        } catch (\Exception $e) {
            return redirect('/api/login/facebook');
        }
        $existingUser = User::where('email', $user->email)->first();
        if (!$existingUser && $id !== null) {
            $compte = "non";
            return redirect()->to(UrlUtils::getBaseUrlFrontOffice() . '/landingpage/' . $id . '/login?&compte=' . $compte);

        }
        if (!$existingUser) {
            $existingUser = $this->userServices->saveUserWithFbOrGoogle($user);
        }
        $token = auth()->login($existingUser, true);

        if ($id !== null) {
            return redirect()->to(UrlUtils::getBaseUrlFrontOffice() . '/landingpage/' . $id . '/login?&token=' . $token . '&user=' . $existingUser->email . '&id=' . $id);

        } else {
            return redirect()->to(UrlUtils::getBaseUrlFrontOffice() . '/login?&token=' . $token . '&user=' . $existingUser->email);
        }
    }
}
