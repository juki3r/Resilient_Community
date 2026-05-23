<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvacuationCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capacity',

        'event_type',
        'description',

        'start_date',
        'start_time',

        'end_date',
        'end_time',

        'status',

        'barangay',
        'created_by',
    ];

    // =========================
    // OPTIONAL SCOPES (USEFUL)
    // =========================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function scopeBarangay($query, $barangay)
    {
        return $query->where('barangay', $barangay);
    }
}
