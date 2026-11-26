<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';

    protected static bool $isDiscovered = false; // ড্যাশবোর্ড থেকে সরানো

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;
}
