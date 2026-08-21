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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('ai_suggested_stage')->nullable()->after('stage');
            $table->string('ai_suggested_keyword')->nullable()->after('ai_suggested_stage');
            $table->text('ai_suggestion_reason')->nullable()->after('ai_suggested_keyword');
            $table->timestamp('ai_suggested_at')->nullable()->after('ai_suggestion_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'ai_suggested_stage',
                'ai_suggested_keyword',
                'ai_suggestion_reason',
                'ai_suggested_at',
            ]);
        });
    }
};
