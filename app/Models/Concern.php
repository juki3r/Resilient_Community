<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concern extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'location',
        'description',
        'status',
        'progress',
        'admin_reply',
        'admin_read',
        'barangay',
    ];

    public function user()
    {
        return $this->belongsTo(MobileUser::class, 'user_id');
    }

    protected $casts = [
        'admin_read' => 'boolean',
    ];
}
