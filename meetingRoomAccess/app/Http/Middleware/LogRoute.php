<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (app()->environment('local')) {
            $log = [
                'IP' => request()->ip(),
                'URI' => $request->getUri(),
                'METHOD' => $request->getMethod(),
                'REQUEST_HEADER_AUTH' => $request->header('Authorization'),
                'REQUEST_BODY' => $request->all(),
                'RESPONSE' => $response->getContent()
            ];

            Log::info(json_encode($log));
        }

        return $response;
    }
}