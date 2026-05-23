<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blotter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        // =========================
        // BLOTTER REFERENCE
        // =========================
        'blotter_number',

        // =========================
        // INCIDENT INFORMATION
        // =========================
        'incident_type',
        'incident_category',
        'incident_date',
        'incident_time',
        'incident_location',
        'incident_details',

        // =========================
        // COMPLAINANT
        // =========================
        'complainant_id',
        'complainant_name',
        'complainant_contact',
        'complainant_address',

        // =========================
        // RESPONDENT
        // =========================
        'respondent_id',
        'respondent_name',
        'respondent_contact',
        'respondent_address',

        // =========================
        // WITNESS
        // =========================
        'witness_name',
        'witness_contact',
        'witness_address',

        // =========================
        // CASE HANDLING
        // =========================
        'reported_by',
        'handled_by',
        'assigned_officer',

        // =========================
        // ACTION / RESOLUTION
        // =========================
        'action_taken',
        'resolution',
        'settlement_date',

        // =========================
        // STATUS
        // =========================
        'status',
        'priority_level',

        // =========================
        // ATTACHMENTS
        // =========================
        'attachment',
        'evidence_photo',

        // =========================
        // LOCATION
        // =========================
        'barangay',
        'municipality',
        'province',

        // =========================
        // NOTES
        // =========================
        'remarks',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'incident_time' => 'datetime:H:i',
        'settlement_date' => 'date',
    ];

    // =========================
    // RELATIONSHIPS
    // =========================

    public function complainant()
    {
        return $this->belongsTo(Resident::class, 'complainant_id');
    }

    public function respondent()
    {
        return $this->belongsTo(Resident::class, 'respondent_id');
    }
}
