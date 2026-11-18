<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    // এই স্লাগগুলো সাইটের নিজস্ব URL, তাই কোনো পেজে ব্যবহার করা যাবে না
    public const RESERVED_SLUGS = ['admin', 'cart', 'checkout', 'product', 'login', 'livewire', 'storage', 'up'];

    protected $fillable = [
        'title', 'slug', 'content', 'show_title', 'is_published',
        'meta_title', 'meta_description', 'og_image',
    ];

    protected $casts = [
        'content' => 'array',
        'show_title' => 'boolean',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        // পেজের স্লাগ/স্ট্যাটাস বদলালে মেনুর লিংকও সাথে সাথে ঠিক হওয়া দরকার
        static::saved(fn () => Menu::flush());
        static::deleted(fn () => Menu::flush());
    }
}
