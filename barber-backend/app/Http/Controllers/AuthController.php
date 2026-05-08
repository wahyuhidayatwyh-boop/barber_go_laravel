<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        // This controller handles authentication routes which are public
    }
    
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->intended($this->redirectTo());
        }
        
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Login successful',
                    'redirect' => $this->redirectTo()
                ]);
            }

            return redirect()->intended($this->redirectTo());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.',
            ], 401);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->intended($this->redirectTo());
        }
        
        return view('login');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // Default role for new users
        ]);

        Auth::login($user);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Registration successful',
                'redirect' => $this->redirectTo()
            ]);
        }

        return redirect()->intended($this->redirectTo());
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Determine the redirect path after login
     */
    protected function redirectTo()
    {
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return '/admin/dashboard';
            }
            
            // Logout user if they are not admin
            Auth::logout();
            session()->flash('error', 'Akses ditolak. Website ini khusus untuk Admin.');
        }

        return '/login';
    }
}
