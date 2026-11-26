<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'image', 'gallery', 'video_url',
        'price', 'compare_price', 'cost_price', 'stock', 'sku',
        'is_flash_sale', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'gallery' => 'array',
        'is_flash_sale' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(5);
            }
        });

        // Keep HomeController's cached Flash Sale list fresh the moment an admin
        // saves/deletes a product (toggles is_flash_sale, changes price/stock, etc).
        static::saved(fn () => Cache::forget('home:flash_sale'));
        static::deleted(fn () => Cache::forget('home:flash_sale'));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // % discount badge, e.g. -22%
    public function getDiscountPercentAttribute(): ?int
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/placeholder-product.png');
    }

    /** প্রোডাক্ট পেজের সব ছবির URL: মূল ছবি + গ্যালারি (ডুপ্লিকেট বাদ) */
    public function getAllImageUrlsAttribute(): array
    {
        $paths = array_values(array_unique(array_filter(array_merge(
            [$this->image],
            (array) ($this->gallery ?? [])
        ))));

        return $paths
            ? array_map(fn ($p) => asset('storage/' . $p), $paths)
            : [asset('images/placeholder-product.png')];
    }
}
