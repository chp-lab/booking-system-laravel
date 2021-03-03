<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IpScreen
{
    public $blockIp = [];
        
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (in_array($request->ip(), $this->blockIp)) {
            $log = [
                'IP' => request()->ip(). " (BLOCKED)",
                'URI' => $request->getUri(),
                'METHOD' => $request->getMethod(),
                'REQUEST_BODY' => $request->all(),
                'RESPONSE' => ['Status' => 'fail', 'Message' => 'you dont have permission to access this api']
            ];
            Log::info(json_encode($log));
            return response()->json(['Status' => 'fail', 'Message' => 'you dont have permission to access this api'], 403);
        }
    
        return $next($request);
    }
}
