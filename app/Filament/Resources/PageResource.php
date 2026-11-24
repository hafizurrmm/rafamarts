<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use App\Support\PageBlocks;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'পেজ';

    protected static ?string $navigationGroup = 'ওয়েবসাইট';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('পেজের তথ্য')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('পেজের নাম')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->label('URL (লিংকের অংশ)')
                        ->required()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                        ->rules([Rule::notIn(Page::RESERVED_SLUGS)])
                        ->prefix('/')
                        ->helperText('ইংরেজি ছোট হাতের অক্ষর, সংখ্যা ও ড্যাশ (-)। যেমন: about-us, privacy-policy, eid-offer'),

                    Forms\Components\Toggle::make('is_published')
                        ->label('পাবলিশ করুন')
                        ->helperText('বন্ধ থাকলে (ড্রাফট) শুধু আপনি অ্যাডমিন হিসেবে লগইন থাকলে প্রিভিউ দেখতে পাবেন।'),

                    Forms\Components\Toggle::make('show_title')
                        ->label('পেজের উপরে নাম (টাইটেল) দেখান')
                        ->default(true)
                        ->helperText('অফার/ল্যান্ডিং পেজে হিরো ব্যানারই যথেষ্ট হলে এটা বন্ধ করুন।'),
                ])
                ->columns(2),

            Forms\Components\Section::make('পেজের কনটেন্ট (কম্পোনেন্ট যোগ করে সাজান)')
                ->schema([
                    Forms\Components\Builder::make('content')
                        ->hiddenLabel()
                        ->blocks(PageBlocks::blocks())
                        ->collapsible()
                        ->cloneable()
                        ->blockNumbers(false)
                        ->blockPickerColumns(2)
                        ->addActionLabel('+ কম্পোনেন্ট যোগ করুন'),
                ]),

            Forms\Components\Section::make('SEO ও শেয়ার প্রিভিউ')
                ->description('ফাঁকা রাখলে পেজের নামই টাইটেল হিসেবে ব্যবহার হবে।')
                ->schema([
                    Forms\Components\TextInput::make('meta_title')->label('SEO টাইটেল')->maxLength(70),
                    Forms\Components\Textarea::make('meta_description')->label('SEO বর্ণনা')->rows(2)->maxLength(320),
                    Forms\Components\FileUpload::make('og_image')
                        ->label('শেয়ার ছবি (Facebook/WhatsApp এ লিংক শেয়ার করলে যে ছবি দেখাবে)')
                        ->image()->directory('pages'),
                ])
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('পেজ')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Page $record) => '/' . $record->slug),
                Tables\Columns\IconColumn::make('is_published')->label('পাবলিশড')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('আপডেট')->since()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view_page')
                    ->label('দেখুন')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Page $record) => url('/' . $record->slug))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->label('কপি')
                    ->beforeReplicaSaved(function (Page $replica) {
                        $replica->title .= ' (কপি)';
                        $replica->slug .= '-copy-' . Str::lower(Str::random(3));
                        $replica->is_published = false;
                    }),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
