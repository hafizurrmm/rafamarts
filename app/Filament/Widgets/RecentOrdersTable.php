<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Services\SteadfastService;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersTable extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'সাম্প্রতিক অর্ডারসমূহ';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->label('আইডি'),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('কাস্টমার')
                    ->description(fn (Order $record) => $record->customer_address ? \Illuminate\Support\Str::limit($record->customer_address, 25) : null),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('পণ্য ও মূল্য')
                    ->state(fn (Order $record) => $record->items()->sum('quantity') . 'x আইটেম (৳' . number_format($record->total_amount, 0) . ')'),
                Tables\Columns\BadgeColumn::make('courier_name')
                    ->label('কুরিয়ার গেটওয়ে')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : 'পাঠানো হয়নি')
                    ->colors([
                        'gray' => fn ($state) => blank($state),
                        'info' => 'steadfast',
                        'warning' => 'pathao',
                        'success' => 'bahok',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'gray' => 'on_hold',
                        'success' => 'completed',
                        'danger' => ['cancelled', 'returned'],
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record) => route('filament.admin.resources.orders.edit', $record)),

                Tables\Actions\Action::make('send_courier')
                    ->label('')
                    ->icon('heroicon-o-truck')
                    ->visible(fn (Order $record) => blank($record->courier_name))
                    ->action(function (Order $record) {
                        $result = app(SteadfastService::class)->createOrder($record);

                        Notification::make()
                            ->title($result['message'])
                            ->color($result['success'] ? 'success' : 'danger')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_courier_push')
                        ->label('বাল্ক কুরিয়ার পুশ')
                        ->icon('heroicon-o-truck')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $service = app(SteadfastService::class);
                            foreach ($records as $record) {
                                if (blank($record->courier_name)) {
                                    $service->createOrder($record);
                                }
                            }

                            Notification::make()
                                ->title('সিলেক্ট করা অর্ডারগুলো কুরিয়ারে পাঠানো হয়েছে।')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulk_invoice_print')
                        ->label('বাল্ক ইনভয়েস প্রিন্ট')
                        ->icon('heroicon-o-printer')
                        ->action(fn ($records) => redirect()->route('admin.orders.invoices', [
                            'ids' => $records->pluck('id')->implode(','),
                        ])),
                ]),
            ])
            ->paginated(false);
    }
}
