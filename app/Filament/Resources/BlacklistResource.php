<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlacklistResource\Pages;
use App\Models\Blacklist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlacklistResource extends Resource
{
    protected static ?string $model = Blacklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'ব্ল্যাকলিস্ট';

    protected static ?string $navigationGroup = 'সেটিংস';

    protected static ?int $navigationSort = 31;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('phone_number')
                ->label('ফোন নম্বর')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('name')
                ->label('নাম (যদি থাকে)'),

            Forms\Components\Textarea::make('reason')
                ->label('কারণ')
                ->placeholder('যেমন: ফেক অর্ডার / ডেলিভারি নেয় না')
                ->columnSpanFull(),

            Forms\Components\TextInput::make('return_count')
                ->label('রিটার্ন হিস্ট্রি (সংখ্যা)')
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('is_active')
                ->label('ব্লকড আছে')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('phone_number')->label('ফোন নম্বর')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('নাম')->searchable()->placeholder('অজ্ঞাত কাস্টমার'),
                Tables\Columns\TextColumn::make('reason')->label('রিটার্ন হিস্ট্রি')->limit(40),
                Tables\Columns\TextColumn::make('return_count')->label('রিটার্ন সংখ্যা')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('ব্লকড')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('যোগ করার তারিখ')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('toggle_block')
                    ->label(fn (Blacklist $record) => $record->is_active ? 'Unblock' : 'Re-block')
                    ->icon(fn (Blacklist $record) => $record->is_active ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn (Blacklist $record) => $record->is_active ? 'success' : 'danger')
                    ->action(fn (Blacklist $record) => $record->update(['is_active' => !$record->is_active])),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlacklists::route('/'),
            'create' => Pages\CreateBlacklist::route('/create'),
            'edit' => Pages\EditBlacklist::route('/{record}/edit'),
        ];
    }
}
