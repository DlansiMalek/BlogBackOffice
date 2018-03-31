<?php

namespace App\Http\Middleware;

use App\Models\Admin_Privilege;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class Super_Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {


        Try {
            if (count(Admin_Privilege::where(function ($query) {
                    $query->where('admin_id', '=', JWTAuth::parseToken()->toUser()->admin_id)->where('privilege_id', '=', '1');
                })->get()) > 0
            )
                return $next($request);
            else
                return response()->json(['error' => 'Permission denied'], 403);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token Expired'], 403);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token Invalid'], 403);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
    }
}
