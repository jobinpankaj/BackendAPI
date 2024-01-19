<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\SiteLanguage;
use Route;
use Auth;
use Exception;

class CheckMyPermission
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
        $permission_revised = auth()->user()->permission_revised;
       
        if($permission_revised == "1" && in_array(auth()->user()->user_type_id,["2","3"])){
            \DB::table("oauth_access_tokens")->where("user_id",auth()->user()->id)->update(["revoked"=>1]);
            return sendError('Access Denied', ['error' => "Your permission has been changed. Please login again."], 401);
        }
        $headers = getallheaders();
        //dd($headers);
        $permission = $headers['permission'] ? $headers['permission'] : "";
        // dd($permission);
        try{
            if($permission === "superadmin-login" && auth()->user()->user_type_id == "1"){
                return $next($request);
            }
            if(auth()->user()->hasPermissionTo($permission,'api'))
            {
                return $next($request);
            }
            else {
                return sendError('Access Denied', ['error' => "You don't have permission to access this page."], 403);
            }
        }
        catch(Exception $e) {
            // return sendError('Access Denied', ['error' => $e], 403);
            return sendError('Access Denied', ['error' => "You don't have permission to access this page."], 403);
        }
    }
}
