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

        if ($user->status === 'PENDING' || ($user->waAccount && $user->waAccount->approval_status === 'PENDING')) {
            return back()->withErrors(['email' => '⏳ Pendaftaran Brand & Akun Supervisor Anda masih dalam proses peninjauan & persetujuan (Approval) oleh CEO/Owner.'])->withInput();
        }

        if ($user->status === 'REJECTED' || ($user->waAccount && $user->waAccount->approval_status === 'REJECTED')) {
            return back()->withErrors(['email' => '❌ Pendaftaran Brand / Akun Supervisor Anda ditolak oleh CEO/Owner.'])->withInput();
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
            'brand_name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'brand_phone' => 'nullable|string|max:50',
            'supervisor_phone' => 'nullable|string|max:50',
        ]);

        $brandPhone = $request->brand_phone ? preg_replace('/[^0-9]/', '', $request->brand_phone) : null;
        $supervisorPhone = $request->supervisor_phone ? preg_replace('/[^0-9]/', '', $request->supervisor_phone) : null;

        // 1. Create Pending Brand
        $waAccount = WaAccount::create([
            'name' => $request->brand_name,
            'category' => $request->category ?: 'General Business',
            'phone' => $brandPhone,
            'session_id' => 'session_brand_' . time(),
            'status' => 'DISCONNECTED',
            'approval_status' => 'PENDING',
        ]);

        // 2. Create Pending Supervisor User
        $supervisor = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $supervisorPhone,
            'password' => Hash::make($request->password),
            'role' => 'SUPERVISOR',
            'status' => 'PENDING',
            'wa_account_id' => $waAccount->id,
        ]);

        $waAccount->supervisor_id = $supervisor->id;
        $waAccount->save();

        return redirect('/login')->with('status', '✅ Pendaftaran Brand "' . $request->brand_name . '" & Akun Supervisor Berhasil! Pengajuan telah dikirim ke CEO/Owner untuk persetujuan (Approval).');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
