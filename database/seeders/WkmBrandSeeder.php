<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\WaAccount;
use Illuminate\Support\Facades\Hash;

class WkmBrandSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Find or verify Siswandi
        $siswandi = User::where('email', 'siswandi@difitech.co.id')->first();
        if (!$siswandi) {
            $siswandi = User::firstOrCreate(
                ['email' => 'siswandi@difitech.co.id'],
                [
                    'name' => 'Siswandi',
                    'role' => 'SUPERVISOR',
                    'status' => 'APPROVED',
                    'password' => Hash::make('password123'),
                ]
            );
        }

        // 2. Find or create Brand WKM
        $wkm = WaAccount::where('name', 'WKM')->orWhere('name', 'like', '%WKM%')->first();
        if (!$wkm) {
            $wkm = WaAccount::create([
                'name' => 'WKM',
                'category' => 'General Business',
                'phone' => '62895638871717',
                'session_id' => 'session_brand_' . time(),
                'status' => 'DISCONNECTED',
                'approval_status' => 'APPROVED',
                'supervisor_id' => $siswandi->id,
            ]);
        } else {
            $wkm->name = 'WKM';
            $wkm->phone = '62895638871717';
            $wkm->approval_status = 'APPROVED';
            $wkm->supervisor_id = $siswandi->id;
            $wkm->save();
        }

        // Ensure default sales pipeline stages
        $wkm->ensureDefaultStages();

        // 3. Attach Siswandi to Brand WKM
        if ($siswandi) {
            $siswandi->supervisedBrands()->syncWithoutDetaching([$wkm->id]);
        }

        // 4. Create or Update Admin CS: Bu Amel
        $amel = User::where('email', 'amel@difitech.co.id')
            ->orWhere('email', 'buamel@difitech.co.id')
            ->orWhere('phone', '62895638871717')
            ->first();

        if (!$amel) {
            $amel = User::create([
                'name' => 'Bu Amel',
                'email' => 'amel@difitech.co.id',
                'phone' => '62895638871717',
                'wa_phone' => '62895638871717',
                'role' => 'SALES_ADMIN',
                'status' => 'APPROVED',
                'wa_account_id' => $wkm->id,
                'wa_status' => 'DISCONNECTED',
                'password' => Hash::make('password123'),
            ]);
            $amel->session_id = 'session_user_' . $amel->id;
            $amel->save();
        } else {
            $amel->name = 'Bu Amel';
            $amel->role = 'SALES_ADMIN';
            $amel->status = 'APPROVED';
            $amel->wa_account_id = $wkm->id;
            $amel->phone = '62895638871717';
            $amel->wa_phone = '62895638871717';
            if (!$amel->session_id) {
                $amel->session_id = 'session_user_' . $amel->id;
            }
            $amel->save();
        }

        echo "✅ Brand WKM & Admin CS Bu Amel berhasil ditanam!" . PHP_EOL;
        echo "   Brand: WKM (ID: {$wkm->id}) - No WA: +{$wkm->phone}" . PHP_EOL;
        echo "   Supervisor: {$siswandi->name} ({$siswandi->email})" . PHP_EOL;
        echo "   Admin CS: {$amel->name} ({$amel->email}) - No WA: +{$amel->phone}" . PHP_EOL;
    }
}
