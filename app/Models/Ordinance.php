<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ordinance extends Model
{
    protected $fillable = [
        'barangay',
        'ordinance_number',
        'title',
        'description',
        'category',
        'status',
        'effectivity_date',
        'approved_date',
        'penalties',
    ];
}
