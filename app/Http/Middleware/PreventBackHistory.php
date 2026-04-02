<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PreventBackHistory
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // BinaryFileResponse (PDF表示など) の場合は、headersプロパティを介してセットする
        if ($response instanceof BinaryFileResponse) {
            $response->headers->set('Cache-Control', 'nocache, no-store, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
        } else {
            // 通常のレスポンスの場合
            $response->header('Cache-Control', 'nocache, no-store, max-age=0, must-revalidate');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
        }

        return $response;
    }
}