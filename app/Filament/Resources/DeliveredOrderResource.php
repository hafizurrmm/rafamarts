<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveredOrderResource\Pages;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shows orders that are completed OR whose courier_status indicates delivery.
 */
class DeliveredOrderResource extends OrderResource
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Delivered';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = 50;

    protected static ?string $slug = 'delivered-orders';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query) {
                $query->where('status', 'completed')
                    ->orWhere('courier_status', 'like', '%deliver%');
            });
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveredOrders::route('/'),
            'edit' => Pages\EditDeliveredOrder::route('/{record}/edit'),
        ];
    }
}
