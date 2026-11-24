<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncompleteOrderResource\Pages;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shows only "pending" orders — customers who placed an order
 * but it hasn't been confirmed/processed yet.
 * Reuses OrderResource's form/table definitions, just scopes the query
 * and gives it its own top-level menu entry (as in the reference screenshot).
 */
class IncompleteOrderResource extends OrderResource
{
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationLabel = 'পেন্ডিং অর্ডার';

    protected static ?string $navigationGroup = 'ই-কমার্স';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'incomplete-orders';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'pending');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncompleteOrders::route('/'),
            'edit' => Pages\EditIncompleteOrder::route('/{record}/edit'),
        ];
    }
}
