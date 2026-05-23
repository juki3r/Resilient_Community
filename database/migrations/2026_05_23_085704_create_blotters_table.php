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
        Schema::create('blotters', function (Blueprint $table) {
            $table->id();

            // =========================
            // BLOTTER REFERENCE
            // =========================
            $table->string('blotter_number')->unique();

            // =========================
            // INCIDENT INFORMATION
            // =========================
            $table->string('incident_type');
            $table->string('incident_category')->nullable();

            $table->date('incident_date');
            $table->time('incident_time')->nullable();

            $table->string('incident_location');

            $table->text('incident_details');

            // =========================
            // COMPLAINANT
            // =========================
            $table->foreignId('complainant_id')
                ->nullable()
                ->constrained('residents')
                ->nullOnDelete();

            $table->string('complainant_name');
            $table->string('complainant_contact')->nullable();
            $table->text('complainant_address')->nullable();

            // =========================
            // RESPONDENT / ACCUSED
            // =========================
            $table->foreignId('respondent_id')
                ->nullable()
                ->constrained('residents')
                ->nullOnDelete();

            $table->string('respondent_name')->nullable();
            $table->string('respondent_contact')->nullable();
            $table->text('respondent_address')->nullable();

            // =========================
            // WITNESS INFORMATION
            // =========================
            $table->string('witness_name')->nullable();
            $table->string('witness_contact')->nullable();
            $table->text('witness_address')->nullable();

            // =========================
            // CASE HANDLING
            // =========================
            $table->string('reported_by')->nullable();

            $table->string('handled_by')->nullable();

            $table->string('assigned_officer')->nullable();

            // =========================
            // ACTION / RESOLUTION
            // =========================
            $table->text('action_taken')->nullable();

            $table->text('resolution')->nullable();

            $table->date('settlement_date')->nullable();

            // =========================
            // STATUS
            // =========================
            $table->enum('status', [
                'Pending',
                'Ongoing',
                'Resolved',
                'Dismissed',
                'Archived'
            ])->default('Pending');

            // =========================
            // PRIORITY LEVEL
            // =========================
            $table->enum('priority_level', [
                'Low',
                'Medium',
                'High',
                'Critical'
            ])->default('Medium');

            // =========================
            // ATTACHMENTS
            // =========================
            $table->string('attachment')->nullable();

            $table->string('evidence_photo')->nullable();

            // =========================
            // LOCATION DETAILS
            // =========================
            $table->string('barangay')->nullable();

            $table->string('municipality')->nullable();

            $table->string('province')->nullable();

            // =========================
            // ADDITIONAL NOTES
            // =========================
            $table->text('remarks')->nullable();

            // =========================
            // SOFT DELETE
            // =========================
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blotters');
    }
};
