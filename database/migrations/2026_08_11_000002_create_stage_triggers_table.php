<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_account_id')->nullable()->constrained('wa_accounts')->onDelete('cascade');
            $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages')->onDelete('cascade');
            $table->string('keyword');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_triggers');
    }
};
