<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = $request->user();

        // Проверяем авторизацию
        if (!$user) {
            return $this->unauthorizedResponse($request);
        }

        // Проверяем роль
        if ($user->role->name === $role || $user->role->name === 'admin') {
            return $next($request);
        }

        return $this->forbiddenResponse($request);
    }

    protected function unauthorizedResponse(Request $request): Response
    {
        if ($request->expectsJson()) {
            return $this->error('Unauthorized', 401);
        }

        return redirect('/login');
    }

    protected function forbiddenResponse(Request $request): Response
    {
        if ($request->expectsJson()) {
            return $this->error('Forbidden - insufficient permissions', 403);
        }

        return redirect('/')->with('error', 'Недостаточно прав доступа');
    }

}
