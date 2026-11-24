<?php

namespace App\Filament\Pages;

/** Store Settings এর "ফেসবুক পিক্সেল ও CAPI" অংশ — আলাদা মেনু পেজ (একই সেটিংস স্টোরেজ, একই সেভ লজিক) */
class FacebookPixelSettings extends StoreSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'ফেসবুক পিক্সেল ও CAPI';

    protected static ?string $title = 'ফেসবুক পিক্সেল ও CAPI';

    protected static ?int $navigationSort = 18;

    protected static function visibleTabs(): array
    {
        return ['facebook'];
    }
}
