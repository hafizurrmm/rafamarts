<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationLabel = 'মেনু';

    protected static ?string $navigationGroup = 'ওয়েবসাইট';

    protected static ?int $navigationSort = 21;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('name')->label('মেনুর নাম')->required()->maxLength(100)
                        ->placeholder('যেমন: মেইন মেনু'),
                    Forms\Components\Select::make('location')->label('কোথায় দেখাবে')->required()->native(false)
                        ->options(Menu::LOCATIONS)
                        ->unique(ignoreRecord: true)
                        ->helperText('প্রতিটা জায়গার জন্য একটাই মেনু। হেডার মেনু না বানালে আগের মতো ক্যাটাগরি মেনুই দেখাবে।'),
                    Forms\Components\Toggle::make('is_active')->label('চালু')->default(true),
                ])
                ->columns(3),

            Forms\Components\Section::make('মেনু আইটেম')
                ->description('ক্রম বদলাতে আইটেম ধরে টেনে আনুন। "সাবমেনু" যোগ করলে ডেস্কটপ হেডারে ড্রপডাউন হবে।')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->hiddenLabel()
                        ->collapsible()
                        ->reorderable()
                        ->cloneable()
                        ->itemLabel(fn (array $state) => $state['label'] ?? null)
                        ->addActionLabel('+ মেনু আইটেম যোগ করুন')
                        ->columns(2)
                        ->schema([
                            ...static::itemFields(),
                            Forms\Components\Repeater::make('children')
                                ->label('সাবমেনু (ঐচ্ছিক)')
                                ->collapsible()
                                ->collapsed()
                                ->reorderable()
                                ->itemLabel(fn (array $state) => $state['label'] ?? null)
                                ->addActionLabel('+ সাবমেনু যোগ করুন')
                                ->columns(2)
                                ->columnSpanFull()
                                ->schema(static::itemFields()),
                        ]),
                ]),
        ]);
    }

    /** আইটেম ও সাবমেনু — দুই জায়গায় একই ঘর */
    protected static function itemFields(): array
    {
        return [
            Forms\Components\TextInput::make('label')->label('মেনুতে যে নাম দেখাবে')->required()->maxLength(60),

            Forms\Components\Select::make('type')->label('কোথায় যাবে')->required()->native(false)->live()
                ->default('page')
                ->options([
                    'home' => 'হোম পেজ',
                    'page' => 'একটা পেজ (Pages থেকে)',
                    'category' => 'একটা ক্যাটাগরি',
                    'custom' => 'কাস্টম লিংক',
                ]),

            Forms\Components\Select::make('page_id')->label('পেজ')->native(false)->searchable()
                ->options(fn () => Page::orderBy('title')->pluck('title', 'id')->all())
                ->visible(fn (Forms\Get $get) => $get('type') === 'page')
                ->required(fn (Forms\Get $get) => $get('type') === 'page')
                ->helperText('অপ্রকাশিত (ড্রাফট) পেজ মেনুতে দেখাবে না।'),

            Forms\Components\Select::make('category_id')->label('ক্যাটাগরি')->native(false)->searchable()
                ->options(fn () => Category::orderBy('name')->pluck('name', 'id')->all())
                ->visible(fn (Forms\Get $get) => $get('type') === 'category')
                ->required(fn (Forms\Get $get) => $get('type') === 'category'),

            Forms\Components\TextInput::make('url')->label('লিংক')
                ->placeholder('https://... বা /something বা tel:01XXXXXXXXX বা # (শুধু ড্রপডাউনের নামের জন্য)')
                ->regex('~^(https?://|/|tel:|mailto:|#)~i')
                ->validationMessages(['regex' => 'লিংক http://, https://, /, tel:, mailto: বা # দিয়ে শুরু হতে হবে।'])
                ->visible(fn (Forms\Get $get) => $get('type') === 'custom')
                ->required(fn (Forms\Get $get) => $get('type') === 'custom'),

            Forms\Components\Toggle::make('new_tab')->label('নতুন ট্যাবে খুলবে')->inline(false),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('নাম')->searchable(),
                Tables\Columns\TextColumn::make('location')->label('কোথায়')->badge()
                    ->formatStateUsing(fn (string $state) => Menu::LOCATIONS[$state] ?? $state),
                Tables\Columns\TextColumn::make('items_count')->label('আইটেম')
                    ->state(fn (Menu $record) => count($record->items ?? [])),
                Tables\Columns\IconColumn::make('is_active')->label('চালু')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
