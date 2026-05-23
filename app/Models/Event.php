<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'incident_id',
        'barangay',
        'created_by',
    ];
}
