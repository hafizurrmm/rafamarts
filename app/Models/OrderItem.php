<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'price', 'cost_price', 'quantity', 'line_total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::creating(function (OrderItem $item) {
            // অর্ডারের সময়ের ক্রয়মূল্য সেভ থাকে — পরে প্রোডাক্টের দাম বদলালেও লাভের হিসাব ঠিক থাকে
            if ($item->cost_price === null && $item->product_id) {
                $item->cost_price = Product::whereKey($item->product_id)->value('cost_price');
            }
        });

        static::saving(function (OrderItem $item) {
            // Keep line_total in sync whenever price/quantity change (e.g. admin edits quantity)
            $item->line_total = (float) $item->price * (int) $item->quantity;
        });

        static::saved(function (OrderItem $item) {
            $item->order?->recalculateTotals();
        });

        static::deleted(function (OrderItem $item) {
            $item->order?->recalculateTotals();
        });
    }
}
