<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'image', 'is_active', 'sort_order',
    ];

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Keep HomeController's cached category list fresh the moment an admin saves/deletes one.
        static::saved(function () {
            Cache::forget('home:categories');
            Menu::flush(); // মেনুতে ক্যাটাগরির লিংক থাকলে সেটাও নতুন করে তৈরি হবে
        });
        static::deleted(function () {
            Cache::forget('home:categories');
            Menu::flush();
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
