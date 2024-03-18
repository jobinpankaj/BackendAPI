<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\SiteLanguage;
use Route;
use Auth;

class SetAppLanguage
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
        $language_id = (isset($headers['Projectlanguageid']) && !empty($headers['Projectlanguageid'])) ? $headers["Projectlanguageid"] : "1";
        $siteData = SiteLanguage::find($language_id);

        $locale = ($siteData) ? $siteData->short_code : 'en';
        App::setLocale($locale);
        return $next($request);
    }
}
