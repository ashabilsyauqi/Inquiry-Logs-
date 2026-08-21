<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_lead_comparisons', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->json('real_stage_counts')->nullable();
            $table->json('ai_stage_counts')->nullable();
            $table->json('differences')->nullable(); // optional diff details
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_lead_comparisons');
    }
};
