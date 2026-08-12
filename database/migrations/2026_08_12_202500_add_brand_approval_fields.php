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
        Schema::table('wa_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('wa_accounts', 'category')) {
                $table->string('category')->nullable()->after('name');
            }
            if (!Schema::hasColumn('wa_accounts', 'approval_status')) {
                $table->string('approval_status')->default('APPROVED')->after('status');
            }
            if (!Schema::hasColumn('wa_accounts', 'supervisor_id')) {
                $table->unsignedBigInteger('supervisor_id')->nullable()->after('approval_status');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_accounts', function (Blueprint $table) {
            $table->dropColumn(['category', 'approval_status', 'supervisor_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
