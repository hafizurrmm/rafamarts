<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    protected $fillable = [
        'phone_number', 'name', 'reason', 'return_count', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Quick helper used at checkout time to warn/block a phone number.
     */
    public static function isBlocked(string $phone): bool
    {
        return static::where('phone_number', $phone)->where('is_active', true)->exists();
    }
}
