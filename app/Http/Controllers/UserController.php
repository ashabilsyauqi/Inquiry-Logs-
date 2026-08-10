<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WaAccount;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        if (!Auth::user() || !Auth::user()->isCeo()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $users = User::with('waAccount')->where('id', '!=', Auth::id())->latest()->get();
        $waAccounts = WaAccount::all();

        return response()->json([
            'users' => $users,
            'waAccounts' => $waAccounts,
        ]);
    }

    public function approve(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->isCeo()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        $waAccountId = $request->input('wa_account_id');

        $user->status = 'APPROVED';
        $user->wa_account_id = $waAccountId ?: null;
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Akun berhasil disetujui!']);
    }

    public function reject($id)
    {
        if (!Auth::user() || !Auth::user()->isCeo()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        $user->status = 'REJECTED';
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Akun ditolak.']);
    }
}
