<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvacuationResident extends Model
{
    protected $fillable = [
        'evacuation_event_id',
        'evacuation_center_id',
        'resident_name',
        'contact_number',
        'family_members',
        'status',
        'barangay',
    ];
}
