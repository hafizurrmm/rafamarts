<?php

namespace App\Filament\Resources\DeliveredOrderResource\Pages;

use App\Filament\Resources\DeliveredOrderResource;
use App\Filament\Resources\OrderResource\Pages\EditOrder;

class EditDeliveredOrder extends EditOrder
{
    protected static string $resource = DeliveredOrderResource::class;
}
