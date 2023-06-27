<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
    //  * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // This check is for fetching data from api using api_token
        if ($request->header('Authorization')) {
            $api_token = $request->header('Authorization');

            $adminData = AdminUser::where('api_token', $api_token)->first();

            if ($adminData) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'You are not authorized to access this admin route.'], 401);
    }
}
