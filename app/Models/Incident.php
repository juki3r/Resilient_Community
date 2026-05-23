<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $fillable = [
        'barangay',
        'incident_no',
        'incident_type',
        'category',
        'description',
        'location',
        'reported_by',
        'contact_number',
        'incident_date',
        'incident_time',
        'status',
        'action_taken',
    ];
}
