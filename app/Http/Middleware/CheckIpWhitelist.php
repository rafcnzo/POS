<?php
namespace App\Http\Middleware;

use App\Models\AllowedIp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckIpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->getClientIp(); // Laravel handles trust proxies if configured

        $allowed = cache()->remember('allowed_ips', 60, function () {
            return AllowedIp::pluck('ip')->toArray();
        });

        if (! in_array($ip, $allowed)) {
            Log::warning('Access denied for IP: ' . $ip . ' url: ' . $request->fullUrl());
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
