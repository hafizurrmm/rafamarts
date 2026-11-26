<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public const TYPES = [
        'ad' => 'বিজ্ঞাপন (Ads)',
        'delivery' => 'ডেলিভারি/কুরিয়ার',
        'packing' => 'প্যাকিং',
        'salary' => 'বেতন',
        'rent' => 'ভাড়া/বিল',
        'other' => 'অন্যান্য',
    ];

    protected $fillable = ['spent_on', 'title', 'type', 'amount', 'note'];

    protected $casts = ['spent_on' => 'date', 'amount' => 'decimal:2'];
}
