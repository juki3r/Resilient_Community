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
        Schema::create('mobile_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('full_name');
            $table->string('province');
            $table->string('municipality');
            $table->string('barangay');
            $table->string('purok')->nullable();
            $table->boolean('granted')->default(false);
            $table->string('email')->nullable()->unique();
            $table->string('phone')->unique();
            $table->boolean('phone_verified')->default(false);
            $table->text('fcm_token')->nullable();
            $table->string('password');
            $table->string('otp_code', 10)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('otp_sent_at')->nullable();
            $table->string('role')->default('resident');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_users');
    }
};
