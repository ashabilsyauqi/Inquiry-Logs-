<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('ADMIN')->after('email');
            $table->string('status')->default('PENDING')->after('role');
            $table->unsignedBigInteger('wa_account_id')->nullable()->after('status');
            $table->foreign('wa_account_id')->references('id')->on('wa_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['wa_account_id']);
            $table->dropColumn(['role', 'status', 'wa_account_id']);
        });
    }
};
