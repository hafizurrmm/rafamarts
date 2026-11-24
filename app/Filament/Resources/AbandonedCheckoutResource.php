<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbandonedCheckoutResource\Pages;
use App\Models\AbandonedCheckout;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * চেকআউটে ফোন নম্বর দিয়ে অর্ডার না করে চলে যাওয়া কাস্টমারদের তালিকা (Abandoned Cart)।
 * শুধু দেখা, ফোন/WhatsApp করা, "যোগাযোগ হয়েছে" চিহ্ন দেওয়া ও মুছে ফেলা যায়।
 */
class AbandonedCheckoutResource extends Resource
{
    protected static ?string $model = AbandonedCheckout::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'অসম্পূর্ণ কার্ট';

    protected static ?string $modelLabel = 'অসম্পূর্ণ চেকআউট';

    protected static ?string $pluralModelLabel = 'অসম্পূর্ণ চেকআউট';

    protected static ?string $navigationGroup = 'Ecommerce';

    protected static ?int $navigationSort = 7;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereNull('recovered_at')->whereNull('contacted_at')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('last_seen_at'))
            ->columns([
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('সর্বশেষ')
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('নাম')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('ফোন')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('items')
                    ->label('প্রোডাক্ট')
                    ->state(fn (AbandonedCheckout $record) => collect($record->cart)
                        ->map(fn ($i) => $i['name'] . ' × ' . $i['quantity'])
                        ->all())
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('মোট')
                    ->formatStateUsing(fn ($state) => '৳' . number_format((float) $state, 0))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('অবস্থা')
                    ->badge()
                    ->state(fn (AbandonedCheckout $record) => $record->statusLabel())
                    ->color(fn (string $state) => match ($state) {
                        'অর্ডার হয়েছে' => 'success',
                        'যোগাযোগ করা হয়েছে' => 'warning',
                        default => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->label('অবস্থা')
                    ->options([
                        'new' => 'নতুন (যোগাযোগ বাকি)',
                        'contacted' => 'যোগাযোগ করা হয়েছে',
                        'recovered' => 'অর্ডার হয়েছে',
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'new' => $query->whereNull('recovered_at')->whereNull('contacted_at'),
                        'contacted' => $query->whereNull('recovered_at')->whereNotNull('contacted_at'),
                        'recovered' => $query->whereNotNull('recovered_at'),
                        default => $query,
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('call')
                    ->label('কল')
                    ->icon('heroicon-o-phone')
                    ->url(fn (AbandonedCheckout $record) => 'tel:' . $record->customer_phone),

                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (AbandonedCheckout $record) => $record->whatsappUrl(), shouldOpenInNewTab: true),

                Tables\Actions\Action::make('contacted')
                    ->label('যোগাযোগ হয়েছে')
                    ->icon('heroicon-o-check')
                    ->visible(fn (AbandonedCheckout $record) => $record->recovered_at === null && $record->contacted_at === null)
                    ->action(function (AbandonedCheckout $record) {
                        $record->update(['contacted_at' => now()]);
                        Notification::make()->title('চিহ্নিত করা হয়েছে')->success()->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('এখনো কোনো অসম্পূর্ণ চেকআউট নেই')
            ->emptyStateDescription('কাস্টমার চেকআউটে ফোন নম্বর দিয়ে অর্ডার না করে চলে গেলে এখানে দেখা যাবে।')
            ->poll('60s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbandonedCheckouts::route('/'),
        ];
    }
}
