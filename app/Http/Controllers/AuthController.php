<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
        }

        if ($user->status === 'PENDING') {
            return back()->withErrors(['email' => '⏳ Akun Anda masih dalam proses peninjauan oleh CEO/Owner. Silakan hubungi CEO untuk persetujuan (Approval).'])->withInput();
        }

        if ($user->status === 'REJECTED') {
            return back()->withErrors(['email' => '❌ Pendaftaran akun Anda ditolak oleh CEO/Owner.'])->withInput();
        }

        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'ADMIN',
            'status' => 'PENDING',
        ]);

        return redirect('/login')->with('status', 'Pendaftaran berhasil! Akun Anda telah dikirim ke CEO/Owner untuk persetujuan (Approval).');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
