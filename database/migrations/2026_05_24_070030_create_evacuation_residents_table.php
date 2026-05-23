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
        Schema::create('evacuation_residents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evacuation_event_id');
            $table->unsignedBigInteger('evacuation_center_id');

            $table->string('resident_name');
            $table->string('contact_number')->nullable();
            $table->integer('family_members')->nullable();

            $table->enum('status', ['inside', 'left', 'transferred'])->default('inside');

            $table->string('barangay');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evacuation_residents');
    }
};
