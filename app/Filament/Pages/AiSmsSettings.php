<?php

namespace App\Filament\Pages;

/** Store Settings এর "এআই ও এসএমএস" অংশ — আলাদা মেনু পেজ (একই সেটিংস স্টোরেজ, একই সেভ লজিক) */
class AiSmsSettings extends StoreSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'এআই ও এসএমএস';

    protected static ?string $title = 'এআই ও এসএমএস';

    protected static ?int $navigationSort = 17;

    protected static function visibleTabs(): array
    {
        return ['ai_sms'];
    }
}
