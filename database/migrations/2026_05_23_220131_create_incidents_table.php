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

            $table->string('incident_no')->unique();

            $table->string('incident_type');

            $table->string('category')->nullable();

            $table->text('description');

            $table->string('location')->nullable();

            $table->string('reported_by')->nullable();

            $table->string('contact_number')->nullable();

            $table->date('incident_date');

            $table->time('incident_time')->nullable();

            $table->string('status')->default('pending');
            // pending | ongoing | resolved | dismissed

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
