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
        Schema::create('residents', function (Blueprint $table) {
            // ======================
            // IDENTITY
            // ======================
            $table->id();
            $table->string('resident_code')->unique()->nullable();
            $table->string('household_number')->nullable();
            $table->string('family_number')->nullable();

            // ======================
            // PERSONAL INFO
            // ======================
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('alias')->nullable();
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('ethnicity')->nullable();

            // ======================
            // BIRTH DETAILS
            // ======================
            $table->date('birth_date')->nullable();
            $table->integer('age')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('birth_certificate_no')->nullable();

            // ======================
            // ADDRESS
            // ======================
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('city_municipality')->nullable();
            $table->string('barangay')->nullable();
            $table->string('purok_zone')->nullable();
            $table->string('street_address')->nullable();
            $table->text('full_address_text')->nullable();

            // ======================
            // HOUSEHOLD INFO
            // ======================
            $table->boolean('household_head')->default(false);
            $table->string('relationship_to_head')->nullable();
            $table->string('household_role')->nullable(); // head, member, dependent
            $table->integer('number_of_household_members')->nullable();
            $table->string('housing_type')->nullable(); // owned, rented, etc.

            // ======================
            // CONTACT INFO
            // ======================
            $table->string('mobile_number')->nullable();
            $table->string('telephone_number')->nullable();
            $table->string('email')->nullable();

            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            // ======================
            // SOCIO-ECONOMIC
            // ======================
            $table->string('employment_status')->nullable();
            $table->string('occupation')->nullable();
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->string('source_of_income')->nullable();
            $table->text('skills')->nullable(); // JSON string
            $table->string('educational_attainment')->nullable();
            $table->string('school_status')->nullable();

            // ======================
            // HEALTH INFO
            // ======================
            $table->string('blood_type')->nullable();
            $table->boolean('disability_status')->default(false);
            $table->string('disability_type')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->string('vaccination_status')->nullable();

            $table->string('philhealth_number')->nullable();

            // ======================
            // GOVERNMENT IDS
            // ======================
            $table->string('sss_number')->nullable();
            $table->string('gsis_number')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('voters_id_number')->nullable();
            $table->string('pwd_id_number')->nullable();
            $table->string('senior_citizen_id_number')->nullable();

            // ======================
            // RESIDENCY DETAILS
            // ======================
            $table->string('residency_status')->nullable(); // permanent, transient
            $table->date('date_of_residency')->nullable();
            $table->integer('years_of_residency')->nullable();
            $table->text('previous_address')->nullable();

            // ======================
            // BARANGAY PROGRAM FLAGS
            // ======================
            $table->boolean('is_4ps_beneficiary')->default(false);
            $table->boolean('is_indigent')->default(false);
            $table->boolean('is_uct_beneficiary')->default(false);
            $table->boolean('is_voter')->default(false);
            $table->boolean('is_sk_voter')->default(false);
            $table->boolean('is_late_registration')->default(false);
            $table->string('status')->default('active'); // active, migrated, deceased

            // ======================
            // DOCUMENT TRACKING
            // ======================
            $table->string('barangay_clearance_status')->nullable();
            $table->string('cedula_number')->nullable();
            $table->string('police_clearance_status')->nullable();
            $table->string('residency_certificate_status')->nullable();

            // ======================
            // SYSTEM FIELDS
            // ======================
            $table->string('photo_url')->nullable();
            $table->string('signature_url')->nullable();
            $table->text('remarks')->nullable();
            $table->text('tags')->nullable(); // JSON-like flexible search

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();


            // ======================
            // INDEXES (important for performance)
            // ======================
            $table->index(['last_name', 'first_name']);
            $table->index('household_number');
            $table->index('resident_code');
            $table->index('barangay');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
