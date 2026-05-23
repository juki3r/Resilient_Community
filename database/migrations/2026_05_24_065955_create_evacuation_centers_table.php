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

            // EVACUATION STATUS
            $table->string('event_type'); // flood, fire, earthquake, etc
            $table->text('description')->nullable();

            // TIMELINE
            $table->date('start_date');
            $table->time('start_time');

            $table->date('end_date')->nullable();
            $table->time('end_time')->nullable();

            // STATUS
            $table->string('status')->default('active');
            // active | ended

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
