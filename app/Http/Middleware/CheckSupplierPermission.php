<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\SiteLanguage;
use Route;
use Auth;
use Exception;

class CheckSupplierPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $headers = getallheaders();
        $permission = $headers['permission'] ? $headers['permission'] : ""; 
        try{
            // $userRole = auth()->user()->roles()->first();
            if(auth()->user()->hasPermissionTo($permission,"supplier"))
            {
                return $next($request);
            }
            else {
                return sendError('Access Denied', ['error' => "You don't have permission to access this pagee."], 403);
            }
        }
        catch(Exception $e) {
            // return sendError('Access Denied', ['error' => $e->message], 403);
            return sendError('Access Denied', ['error' => "You don't have permission to access this page."], 403);
        }
    }
}
