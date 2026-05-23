<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvacuationCenter extends Model
{
    protected $fillable = [
        'name',
        'location',
        'capacity',
        'contact_person',
        'contact_number',
        'barangay',
    ];
}
