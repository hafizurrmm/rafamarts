<?php

namespace App\Filament\Resources\IncompleteOrderResource\Pages;

use App\Filament\Resources\IncompleteOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListIncompleteOrders extends ListRecords
{
    protected static string $resource = IncompleteOrderResource::class;
}
