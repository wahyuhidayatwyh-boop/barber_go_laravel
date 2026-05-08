<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();
        
        // Check if user has admin privileges
        if (($user->role ?? null) === 'admin' || (method_exists($user, 'isAdmin') && $user->isAdmin())) {
            return $next($request);
        }
        
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Admin access required'], 403);
        }
        
        return redirect()->route('login')->with('error', 'Akses ditolak. Anda bukan admin.');
    }
}
