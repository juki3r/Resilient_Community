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
        Schema::create('evacuation_centers', function (Blueprint $table) {
            $table->id();
            // CENTER INFO
            $table->string('name');
            $table->string('location');

            $table->integer('capacity')->nullable();
            $table->integer('current_occupancy')->nullable();

            // EVACUATION STATUS
            $table->string('event_type'); // flood, fire, earthquake, etc

            // STATUS
            $table->string('status')->default('Standby');
            // active | ended
            $table->json('facilities')->nullable();

            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();

            // BARANGAY SCOPE
            $table->string('barangay');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evacuation_centers');
    }
};
