<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('HTTP Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'data' => json_encode($request->all(), JSON_UNESCAPED_UNICODE), // 保留中文
            'headers' => json_encode($request->headers->all(), JSON_UNESCAPED_UNICODE), // 保留中文
        ]);
        // 執行下一個請求
        $response = $next($request);

        // 記錄返回數據
        Log::info('HTTP Response', [
            'status' => $response->status(),
            'headers' => json_encode($response->headers->all(), JSON_UNESCAPED_UNICODE), // 保留中文
            'body' => json_encode(json_decode($response->getContent(), true), JSON_UNESCAPED_UNICODE), // 保留中文
        ]);


        return $response;
    }
}
