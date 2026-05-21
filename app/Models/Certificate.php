<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'age',
        'gender',
        'address',
        'document_type',
        'purpose',
        'company_name',
        'business_nature',
    ];

    /**
     * 🔗 Relationship: Request belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
