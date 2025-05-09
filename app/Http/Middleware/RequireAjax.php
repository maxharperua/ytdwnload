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
        // Проверяем, является ли запрос AJAX или имеет заголовок Accept: application/json
        if (!$request->ajax() && 
            !$request->wantsJson() && 
            !$request->isJson() && 
            !$request->acceptsJson() && 
            !$request->header('Accept') === 'application/json') {
            abort(403, 'Только AJAX запросы разрешены');
        }

        return $next($request);
    }
} 