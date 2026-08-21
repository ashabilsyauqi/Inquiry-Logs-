<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'session_id')) {
                $table->string('session_id')->nullable()->unique()->after('wa_account_id');
            }
            if (!Schema::hasColumn('users', 'wa_status')) {
                $table->string('wa_status')->default('DISCONNECTED')->after('session_id');
            }
            if (!Schema::hasColumn('users', 'wa_phone')) {
                $table->string('wa_phone')->nullable()->after('wa_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['session_id', 'wa_status', 'wa_phone']);
        });
    }
};
