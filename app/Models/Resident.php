<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $table = 'residents';

    // ======================
    // MASS ASSIGNMENT
    // ======================
    protected $fillable = [

        // Identity
        'resident_code',
        'household_number',
        'family_number',

        // Personal Info
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'alias',
        'gender',
        'civil_status',
        'nationality',
        'religion',
        'ethnicity',

        // Birth Details
        'birth_date',
        'age',
        'place_of_birth',
        'birth_certificate_no',

        // Address
        'region',
        'province',
        'city_municipality',
        'barangay',
        'purok_zone',
        'street_address',
        'full_address_text',

        // Household
        'household_head',
        'relationship_to_head',
        'household_role',
        'number_of_household_members',
        'housing_type',

        // Contact
        'mobile_number',
        'telephone_number',
        'email',
        'emergency_contact_name',
        'emergency_contact_number',
        'emergency_contact_relationship',

        // Socio-economic
        'employment_status',
        'occupation',
        'monthly_income',
        'source_of_income',
        'skills',
        'educational_attainment',
        'school_status',

        // Health
        'blood_type',
        'disability_status',
        'disability_type',
        'medical_conditions',
        'vaccination_status',
        'philhealth_number',

        // Government IDs
        'sss_number',
        'gsis_number',
        'tin_number',
        'voters_id_number',
        'pwd_id_number',
        'senior_citizen_id_number',

        // Residency
        'residency_status',
        'date_of_residency',
        'years_of_residency',
        'previous_address',

        // Barangay Programs
        'is_4ps_beneficiary',
        'is_indigent',
        'is_uct_beneficiary',
        'is_voter',
        'is_sk_voter',
        'is_late_registration',
        'status',

        // Documents
        'barangay_clearance_status',
        'cedula_number',
        'police_clearance_status',
        'residency_certificate_status',

        // System
        'photo_url',
        'signature_url',
        'remarks',
        'tags',
        'created_by',
        'updated_by',
    ];

    // ======================
    // CASTING
    // ======================
    protected $casts = [
        'birth_date' => 'date',
        'date_of_residency' => 'date',

        'household_head' => 'boolean',
        'disability_status' => 'boolean',

        'is_4ps_beneficiary' => 'boolean',
        'is_indigent' => 'boolean',
        'is_uct_beneficiary' => 'boolean',
        'is_voter' => 'boolean',
        'is_sk_voter' => 'boolean',
        'is_late_registration' => 'boolean',

        'monthly_income' => 'decimal:2',
    ];

    // ======================
    // ACCESSORS
    // ======================

    // Auto compute age (better than storing it)
    public function getComputedAgeAttribute()
    {
        if (!$this->birth_date) return null;

        return Carbon::parse($this->birth_date)->age;
    }

    // Full name helper
    public function getFullNameAttribute()
    {
        return trim(
            "{$this->first_name} " .
                ($this->middle_name ? $this->middle_name . ' ' : '') .
                "{$this->last_name} " .
                ($this->suffix ?? '')
        );
    }

    // ======================
    // SCOPES
    // ======================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVoters($query)
    {
        return $query->where('is_voter', true);
    }

    public function scopeHousehold($query, $householdNumber)
    {
        return $query->where('household_number', $householdNumber);
    }
}
