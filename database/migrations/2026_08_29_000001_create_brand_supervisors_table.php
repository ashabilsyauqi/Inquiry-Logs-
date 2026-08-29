<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brand_supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wa_account_id')->constrained('wa_accounts')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'wa_account_id']);
        });

        // Migrate existing supervisor relationships from users and wa_accounts
        try {
            // 1. From users table where role is SUPERVISOR
            $supervisors = DB::table('users')->where('role', 'SUPERVISOR')->whereNotNull('wa_account_id')->get();
            foreach ($supervisors as $sup) {
                DB::table('brand_supervisors')->insertOrIgnore([
                    'user_id' => $sup->id,
                    'wa_account_id' => $sup->wa_account_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. From wa_accounts table where supervisor_id is set
            $accounts = DB::table('wa_accounts')->whereNotNull('supervisor_id')->get();
            foreach ($accounts as $acc) {
                DB::table('brand_supervisors')->insertOrIgnore([
                    'user_id' => $acc->supervisor_id,
                    'wa_account_id' => $acc->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Log or ignore if tables are fresh
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_supervisors');
    }
};
