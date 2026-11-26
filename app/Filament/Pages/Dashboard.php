<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'ড্যাশবোর্ড';

    protected static ?string $title = 'ড্যাশবোর্ড';

    public function getColumns(): int | string | array
    {
        return ['default' => 1, 'md' => 3];
    }
}
