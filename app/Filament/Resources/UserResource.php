<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'ইউজার';

    protected static ?string $navigationGroup = 'Ecommerce';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('role')
                ->options(['admin' => 'Admin', 'customer' => 'Customer'])
                ->default('customer')
                ->required(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context) => $context === 'create')
                ->helperText('পাসওয়ার্ড বদলাতে না চাইলে খালি রাখুন।'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
            Tables\Columns\BadgeColumn::make('role')
                ->colors(['primary' => 'admin', 'gray' => 'customer']),
            Tables\Columns\TextColumn::make('orders_count')->counts('orders')->label('Orders'),
            Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('role')
                ->options(['admin' => 'Admin', 'customer' => 'Customer']),
        ])
        ->actions([
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
