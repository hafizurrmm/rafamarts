<?php

namespace App\Filament\Pages;

/** Store Settings এর "পেমেন্ট গেটওয়ে" অংশ — আলাদা মেনু পেজ (একই সেটিংস স্টোরেজ, একই সেভ লজিক) */
class PaymentSettings extends StoreSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'পেমেন্ট গেটওয়ে';

    protected static ?string $title = 'পেমেন্ট গেটওয়ে';

    protected static ?int $navigationSort = 16;

    protected static function visibleTabs(): array
    {
        return ['payment'];
    }
}
