<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCache extends Model
{
    protected $table = 'news_cache';

    protected $fillable = [
        'country_id', 'title', 'description', 'url', 'image_url', 'source', 'published_at',
        'sentiment', 'positive_score', 'negative_score', 'query', 'language',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'positive_score' => 'integer',
        'negative_score' => 'integer',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function analysis()
    {
        return $this->hasOne(NewsSentiment::class, 'news_cache_id');
    }
}
