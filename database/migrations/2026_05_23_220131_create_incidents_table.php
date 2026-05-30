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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            $table->string('barangay');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('mobileuser_id')
                ->nullable()
                ->constrained('mobile_users')
                ->nullOnDelete();

            $table->string('incident_no')->unique();

            $table->string('type');

            $table->text('description');

            $table->string('location');

            $table->string('reported_by')->nullable();

            $table->string('contact_number')->nullable();

            $table->timestamp('incident_datetime');

            $table->string('status')->default('pending');
            // pending | ongoing | resolved | dismissed
            $table->boolean('alert_mdrrmo')->default(false);
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->text('action_taken')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
