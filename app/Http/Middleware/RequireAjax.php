<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAjax
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем наличие заголовка X-Requested-With
        if (!$request->header('X-Requested-With') || $request->header('X-Requested-With') !== 'XMLHttpRequest') {
            abort(403, 'Только AJAX запросы разрешены');
        }

        return $next($request);
    }
} 