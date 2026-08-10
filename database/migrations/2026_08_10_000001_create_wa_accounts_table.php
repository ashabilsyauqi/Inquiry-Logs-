<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('session_id')->unique();
            $table->string('status')->default('DISCONNECTED');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_accounts');
    }
};
