<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Order Items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('product_name')->disabled()->dehydrated(),
            Forms\Components\TextInput::make('price')->numeric()->prefix('৳')->disabled()->dehydrated(),
            Forms\Components\TextInput::make('quantity')
                ->numeric()
                ->minValue(1)
                ->required(),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                Tables\Columns\TextColumn::make('product_name')->label('পণ্য'),
                Tables\Columns\TextColumn::make('price')->label('দাম')->money('BDT'),
                Tables\Columns\TextColumn::make('quantity')->label('পরিমাণ'),
                Tables\Columns\TextColumn::make('line_total')->label('মোট')->money('BDT'),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('decrease')
                    ->label('')
                    ->icon('heroicon-o-minus-circle')
                    ->color('gray')
                    ->action(function ($record) {
                        if ($record->quantity > 1) {
                            $record->update(['quantity' => $record->quantity - 1]);
                        }
                    }),

                Tables\Actions\Action::make('increase')
                    ->label('')
                    ->icon('heroicon-o-plus-circle')
                    ->color('gray')
                    ->action(fn ($record) => $record->update(['quantity' => $record->quantity + 1])),

                Tables\Actions\EditAction::make()
                    ->label('পরিমাণ পরিবর্তন'),

                Tables\Actions\DeleteAction::make()
                    ->label('মুছুন'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
