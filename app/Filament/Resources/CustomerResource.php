<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * কাস্টমার তালিকা — আলাদা টেবিল নেই; অর্ডারগুলো ফোন নম্বর ধরে গ্রুপ করে প্রতিটি কাস্টমারের
 * মোট অর্ডার, মোট কেনাকাটা ও শেষ অর্ডারের সময় দেখানো হয়। (একই ফোন = একজন কাস্টমার)
 */
class CustomerResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'কাস্টমার';

    protected static ?string $modelLabel = 'কাস্টমার';

    protected static ?string $pluralModelLabel = 'কাস্টমার';

    protected static ?string $navigationGroup = 'Ecommerce';

    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return Order::query()
            ->selectRaw('MAX(id) as id, customer_phone, MAX(customer_name) as customer_name, MAX(customer_address) as customer_address')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw("SUM(CASE WHEN status <> 'cancelled' THEN total_amount ELSE 0 END) as lifetime_value")
            ->selectRaw('MAX(created_at) as last_order_at, MIN(created_at) as first_order_at')
            ->whereNotNull('customer_phone')
            ->groupBy('customer_phone');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('last_order_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('কাস্টমার')
                    ->weight('semibold')
                    ->placeholder('—')
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->where(fn ($q) => $q->where('customer_name', 'like', "%{$search}%")->orWhere('customer_phone', 'like', "%{$search}%"))),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('ফোন')
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer_address')
                    ->label('ঠিকানা')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('অর্ডার')
                    ->alignEnd()
                    ->badge()
                    ->color(fn ($state) => $state > 1 ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('lifetime_value')
                    ->label('মোট কেনাকাটা')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => '৳' . number_format((float) $state, 0))
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_order_at')
                    ->label('শেষ অর্ডার')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('ধরন')
                    ->options(['repeat' => 'রিপিট কাস্টমার (২+ অর্ডার)', 'new' => 'নতুন (১টি অর্ডার)', 'month' => 'এই মাসে প্রথম অর্ডার'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'repeat' => $query->havingRaw('COUNT(*) > 1'),
                        'new' => $query->havingRaw('COUNT(*) = 1'),
                        'month' => $query->havingRaw('MIN(created_at) >= ?', [now()->startOfMonth()]),
                        default => $query,
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('orders')
                    ->label('অর্ডার দেখুন')
                    ->icon('heroicon-o-shopping-cart')
                    ->url(fn (Order $record) => OrderResource::getUrl('index', ['tableSearch' => $record->customer_phone])),
                Tables\Actions\Action::make('call')
                    ->label('কল')
                    ->icon('heroicon-o-phone')
                    ->url(fn (Order $record) => 'tel:' . $record->customer_phone),
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Order $record) => 'https://wa.me/880' . ltrim(substr(preg_replace('/\D+/', '', $record->customer_phone), -10), '0'), shouldOpenInNewTab: true),
            ])
            ->bulkActions([])
            ->paginated([20, 50, 100])
            ->emptyStateHeading('এখনো কোনো কাস্টমার নেই');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
        ];
    }
}
