<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPage extends Model
{
    protected $fillable = [
        'product_id', 'title', 'slug', 'is_published', 'headline', 'subheadline', 'hero_image',
        'gallery', 'video_url', 'features', 'body', 'button_text', 'form_title', 'countdown_ends_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'gallery' => 'array',
        'features' => 'array',
        'countdown_ends_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        return url('/lp/' . $this->slug);
    }
}
