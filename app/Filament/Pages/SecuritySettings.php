<?php

namespace App\Filament\Pages;

/** Store Settings এর "সিকিউরিটি ও ফ্রড" অংশ — আলাদা মেনু পেজ (একই সেটিংস স্টোরেজ, একই সেভ লজিক) */
class SecuritySettings extends StoreSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'সিকিউরিটি ও ফ্রড';

    protected static ?string $title = 'সিকিউরিটি ও ফ্রড';

    protected static ?int $navigationSort = 20;

    protected static function visibleTabs(): array
    {
        return ['security'];
    }
}
