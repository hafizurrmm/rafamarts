<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'প্রোডাক্ট';

    protected static ?string $navigationGroup = 'Ecommerce';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Product Info')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state) . '-' . Str::random(5))),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(Category::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\RichEditor::make('description')
                    ->label('বিস্তারিত বর্ণনা')
                    ->helperText('প্রোডাক্ট পেজের নিচে "বর্ণনা" ট্যাবে দেখাবে (হেডিং, বোল্ড, লিস্ট, ছবি ব্যবহার করা যাবে)।')
                    ->toolbarButtons(['h2', 'h3', 'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'blockquote', 'link', 'attachFiles', 'undo', 'redo'])
                    ->fileAttachmentsDirectory('product-descriptions')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('products')
                    ->imageEditor(),
            ])->columns(2),

            Forms\Components\Section::make('Pricing & Stock')->schema([
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('৳')
                    ->required(),

                Forms\Components\TextInput::make('compare_price')
                    ->numeric()
                    ->prefix('৳')
                    ->helperText('Original price shown with strikethrough, for discount badge.'),

                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\TextInput::make('sku')
                    ->label('SKU'),

                Forms\Components\Toggle::make('is_flash_sale')
                    ->label('Show in Flash Sale'),

                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // "category.name" কলামটা দেখাতে প্রতিটা রো-র জন্য আলাদা করে category কোয়েরি
            // চালানো এড়াতে (N+1 প্রবলেম) — এক কোয়েরিতেই সব ক্যাটাগরি এক সাথে লোড হয়।
            ->modifyQueryUsing(fn ($query) => $query->with('category'))
            ->columns([
            Tables\Columns\ImageColumn::make('image'),
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category.name')->label('Category')->sortable(),
            Tables\Columns\TextColumn::make('price')->money('BDT')->sortable(),
            Tables\Columns\TextColumn::make('compare_price')->money('BDT')->toggleable(),
            Tables\Columns\TextColumn::make('stock')->sortable(),
            Tables\Columns\IconColumn::make('is_flash_sale')->boolean()->label('Flash'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('category_id')
                ->label('Category')
                ->options(Category::pluck('name', 'id')),
            Tables\Filters\TernaryFilter::make('is_flash_sale'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
