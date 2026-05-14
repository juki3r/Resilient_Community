<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'type',
        'priority',
        'status',
        'is_pinned',
        'publish_at',
        'published_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'publish_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    // 👤 relationship: who posted it
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
