<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'image',
        'status',
        'user_id',
        'published_at',
    ];
}
