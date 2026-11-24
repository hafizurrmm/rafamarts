<?php

namespace App\Filament\Pages;

/** Store Settings এর "টেলিগ্রাম নোটিফিকেশন" অংশ — আলাদা মেনু পেজ (একই সেটিংস স্টোরেজ, একই সেভ লজিক) */
class TelegramSettings extends StoreSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationLabel = 'টেলিগ্রাম নোটিফিকেশন';

    protected static ?string $title = 'টেলিগ্রাম নোটিফিকেশন';

    protected static ?int $navigationSort = 19;

    protected static function visibleTabs(): array
    {
        return ['telegram'];
    }
}
