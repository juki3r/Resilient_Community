<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvacuationEvent extends Model
{
    protected $fillable = [
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'status',
        'barangay',
        'created_by',
    ];
}
