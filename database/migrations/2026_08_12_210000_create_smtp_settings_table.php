<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mail_mailer')->default('smtp');
            $table->string('mail_host')->nullable();
            $table->integer('mail_port')->default(587);
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_encryption')->nullable()->default('tls');
            $table->string('mail_from_address')->default('no-reply@difitech.id');
            $table->string('mail_from_name')->default('Difitech CRM Alert');
            $table->timestamps();
        });

        // Insert default initial row
        DB::table('smtp_settings')->insert([
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_username' => null,
            'mail_password' => null,
            'mail_encryption' => 'tls',
            'mail_from_address' => 'no-reply@difitech.id',
            'mail_from_name' => 'Difitech CRM Alert',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};
