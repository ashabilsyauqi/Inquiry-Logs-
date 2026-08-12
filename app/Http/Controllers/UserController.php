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

        $pendingBrands = WaAccount::with('supervisor')
            ->where('approval_status', 'PENDING')
            ->latest()
            ->get();

        $allApprovedBrands = WaAccount::with('supervisor')
            ->where('approval_status', 'APPROVED')
            ->latest()
            ->get();

        return response()->json([
            'pendingBrands' => $pendingBrands,
            'approvedBrands' => $allApprovedBrands,
        ]);
    }

    public function approveBrand(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->isCeo()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $brand = WaAccount::findOrFail($id);
        $brand->approval_status = 'APPROVED';
        $brand->save();

        $brand->ensureDefaultStages();

        if ($brand->supervisor) {
            $brand->supervisor->status = 'APPROVED';
            $brand->supervisor->save();
        }

        return response()->json(['status' => 'success', 'message' => '✅ Brand "' . $brand->name . '" & Akun Supervisor berhasil disetujui (APPROVED)!']);
    }

    public function rejectBrand($id)
    {
        if (!Auth::user() || !Auth::user()->isCeo()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $brand = WaAccount::findOrFail($id);
        $brand->approval_status = 'REJECTED';
        $brand->save();

        if ($brand->supervisor) {
            $brand->supervisor->status = 'REJECTED';
            $brand->supervisor->save();
        }

        return response()->json(['status' => 'success', 'message' => '❌ Pengajuan Brand "' . $brand->name . '" ditolak.']);
    }
}
