<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    use HasFactory;

    protected $fillable = [
        'barangay',
        'full_name',
        'gender',
        'position',
        'committee',
        'address',
        'contact_number',
        'email',
        'term_start',
        'term_end',
        'status',
        'photo',
        'remarks'
    ];
}
