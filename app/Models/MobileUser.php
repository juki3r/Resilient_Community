<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class MobileUser extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $table = 'mobile_users';

    protected $fillable = [
        'user_id',
        'full_name',
        'province',
        'municipality',
        'barangay',
        'purok',
        'granted',
        'email',
        'phone',
        'phone_verified',
        'fcm_token',
        'password',
        'otp_code',
        'otp_expires_at',
        'otp_sent_at',
        'role',
        'barangay',
        'is_logged_in'
    ];

    protected $hidden = [
        'password',
        'otp_code',
        'remember_token',
    ];

    protected $casts = [
        'granted' => 'boolean',
        'phone_verified' => 'boolean',
        'otp_expires_at' => 'datetime',
        'otp_sent_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getFullNameAttribute()
    {
        return trim(
            $this->first_name . ' ' .
                $this->middle_name . ' ' .
                $this->last_name
        );
    }
}
