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
        $brandName = $brand->name;

        if ($brand->supervisor) {
            $brand->supervisor->delete();
        }

        $brand->delete();

        return response()->json(['status' => 'success', 'message' => '🗑️ Pengajuan Brand "' . $brandName . '" & Akun Supervisor telah ditolak dan dihapus dari sistem.']);
    }

    /**
     * Fetch CS Team Members under the Supervisor's Brand
     */
    public function getCsTeam()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $brandId = $user->isCeo() ? request('account_id') : $user->wa_account_id;
        if (!$brandId && !$user->isCeo()) {
            return response()->json(['csTeam' => []]);
        }

        $query = User::where('role', 'SALES_ADMIN');
        if ($brandId && $brandId !== 'all') {
            $query->where('wa_account_id', $brandId);
        }

        $csTeam = $query->with('waAccount')->latest()->get();

        return response()->json([
            'status' => 'success',
            'csTeam' => $csTeam,
        ]);
    }

    /**
     * Supervisor recruits/creates a new Admin CS Team Member under their Brand
     */
    public function storeCsMember(Request $request)
    {
        $supervisor = Auth::user();
        if (!$supervisor) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $brandId = $supervisor->isCeo() ? ($request->input('wa_account_id') ?: $supervisor->wa_account_id) : $supervisor->wa_account_id;
        if ((!$brandId || $brandId === 'all') && $supervisor->isCeo()) {
            $firstBrand = WaAccount::where('approval_status', 'APPROVED')->first() ?: WaAccount::first();
            $brandId = $firstBrand ? $firstBrand->id : null;
        }

        if (!$brandId) {
            return response()->json(['error' => 'Brand ID tidak terdeteksi. Silakan pilih brand terlebih dahulu.'], 422);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ], [
            'name.required' => 'Nama Admin CS wajib diisi.',
            'email.required' => 'Email login CS wajib diisi.',
            'email.unique' => 'Email sudah terdaftar di sistem. Gunakan email lain.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $csUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => 'SALES_ADMIN',
            'status' => 'APPROVED', // CS registered by Supervisor are AUTO-APPROVED
            'wa_account_id' => $brandId,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '🎉 Admin CS baru "' . $csUser->name . '" berhasil direkrut & aktif!',
            'csUser' => $csUser
        ]);
    }

    /**
     * Remove an Admin CS Team Member
     */
    public function destroyCsMember($id)
    {
        $supervisor = Auth::user();
        if (!$supervisor) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $csUser = User::find($id);
        if (!$csUser) {
            return response()->json([
                'status' => 'success',
                'message' => 'Admin CS sudah terhapus dari sistem.'
            ]);
        }

        // Security check: Non-CEO can only delete CS under their own brand
        if (!$supervisor->isCeo() && $csUser->wa_account_id != $supervisor->wa_account_id) {
            return response()->json(['error' => 'Akses ditolak: Hanya Supervisor Brand ini yang dapat menghapus CS.'], 403);
        }

        $csName = $csUser->name;
        $csUser->delete();

        return response()->json([
            'status' => 'success',
            'message' => '🗑️ Admin CS "' . $csName . '" berhasil dihapus.'
        ]);
    }

}
