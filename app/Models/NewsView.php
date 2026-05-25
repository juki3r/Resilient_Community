<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsView extends Model
{
    protected $fillable = [
        'news_id',
        'user_id',
        'viewed_at',
    ];
}
