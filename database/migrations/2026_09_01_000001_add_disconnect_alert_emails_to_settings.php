<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('smtp_settings') && !Schema::hasColumn('smtp_settings', 'disconnect_alert_emails')) {
            Schema::table('smtp_settings', function (Blueprint $table) {
                $table->text('disconnect_alert_emails')->nullable()->after('mail_from_name');
            });
        }

        if (Schema::hasTable('wa_accounts') && !Schema::hasColumn('wa_accounts', 'disconnect_alert_emails')) {
            Schema::table('wa_accounts', function (Blueprint $table) {
                $table->text('disconnect_alert_emails')->nullable()->after('disconnect_email_interval');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('smtp_settings') && Schema::hasColumn('smtp_settings', 'disconnect_alert_emails')) {
            Schema::table('smtp_settings', function (Blueprint $table) {
                $table->dropColumn('disconnect_alert_emails');
            });
        }

        if (Schema::hasTable('wa_accounts') && Schema::hasColumn('wa_accounts', 'disconnect_alert_emails')) {
            Schema::table('wa_accounts', function (Blueprint $table) {
                $table->dropColumn('disconnect_alert_emails');
            });
        }
    }
};
