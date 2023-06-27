<?php

namespace App\Http\Middleware;

use App\Models\CompanyUser;
use Closure;
use Illuminate\Http\Request;

class CompanyMiddleware
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

            $companyUserData = CompanyUser::where('api_token', $api_token)->first();

            // return response()->json($companyUserData);

            if ($companyUserData) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'You are not authorized to access this company route.'], 401);
    }
}
