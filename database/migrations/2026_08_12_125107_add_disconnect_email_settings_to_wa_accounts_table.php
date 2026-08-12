<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_accounts', function (Blueprint $table) {
            $table->boolean('disconnect_email_enabled')->default(true)->after('status');
            $table->integer('disconnect_email_interval')->default(10)->after('disconnect_email_enabled'); // Default 10 seconds for testing mode (1800 for 30m prod)
            $table->timestamp('last_disconnect_email_sent_at')->nullable()->after('disconnect_email_interval');
        });
    }

    public function down(): void
    {
        Schema::table('wa_accounts', function (Blueprint $table) {
            $table->dropColumn(['disconnect_email_enabled', 'disconnect_email_interval', 'last_disconnect_email_sent_at']);
        });
    }
};
