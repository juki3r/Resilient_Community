<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $fillable = [
        'barangay',
        'incident_no',
        'type',
        'description',
        'location',
        'reported_by',
        'contact_number',
        'incident_datetime',
        'status',
        'action_taken',
        'alert_mdrrmo',
        'municipality',
        'province'
    ];
}
