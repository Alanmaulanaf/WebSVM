<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $cred = $r->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
            'ingat'    => ['nullable','boolean'],
        ]);

        $remember = (bool)($cred['ingat'] ?? false);
        $attempt = Auth::attempt([
            'email' => strtolower($cred['email']),
            'password' => $cred['password']
        ], $remember);

        if (!$attempt) {
            return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->withInput();
        }

        $r->session()->regenerate();
        return redirect()->route('dashboard')->with('ok','Berhasil masuk.');
    }

    // ===== Logout =====
    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login')->with('ok','Berhasil keluar.');
    }
}
