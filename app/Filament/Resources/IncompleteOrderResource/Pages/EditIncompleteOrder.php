<?php

namespace App\Filament\Resources\IncompleteOrderResource\Pages;

use App\Filament\Resources\IncompleteOrderResource;
use App\Filament\Resources\OrderResource\Pages\EditOrder;

/**
 * Reuses all of EditOrder's logic (discount/total recalculation, courier send
 * action) — just points at the IncompleteOrderResource instead.
 */
class EditIncompleteOrder extends EditOrder
{
    protected static string $resource = IncompleteOrderResource::class;
}
