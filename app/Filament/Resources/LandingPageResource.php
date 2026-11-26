<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LandingPageResource\Pages;
use App\Models\LandingPage;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LandingPageResource extends Resource
{
    protected static ?string $model = LandingPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationLabel = 'ল্যান্ডিং পেজ';

    protected static ?string $modelLabel = 'ল্যান্ডিং পেজ';

    protected static ?string $navigationGroup = 'ওয়েবসাইট';

    protected static ?int $navigationSort = 22;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('পেজ ও প্রোডাক্ট')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('পেজের নাম (শুধু আপনার জন্য)')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->label('লিংকের অংশ')
                        ->required()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                        ->prefix('/lp/')
                        ->helperText('ইংরেজি ছোট হাতের অক্ষর, সংখ্যা ও ড্যাশ (-)।'),
                    Forms\Components\Select::make('product_id')
                        ->label('কোন প্রোডাক্ট বিক্রি হবে')
                        ->options(fn () => Product::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->helperText('প্রোডাক্টের দাম, কমপেয়ার প্রাইস ও নাম এখান থেকেই আসবে।'),
                    Forms\Components\Toggle::make('is_published')
                        ->label('পাবলিশ করুন')
                        ->helperText('বন্ধ থাকলে (ড্রাফট) শুধু আপনি অ্যাডমিন হিসেবে দেখতে পারবেন।')
                        ->inline(false),
                ]),

            Forms\Components\Section::make('উপরের অংশ (Hero)')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('headline')->label('বড় হেডিং')->maxLength(200)->columnSpanFull(),
                    Forms\Components\Textarea::make('subheadline')->label('ছোট বর্ণনা')->rows(2)->maxLength(500)->columnSpanFull(),
                    Forms\Components\FileUpload::make('hero_image')->label('মূল ছবি')->image()->disk('public')->directory('landing')->imageEditor(),
                    Forms\Components\TextInput::make('button_text')->label('বাটনের লেখা')->placeholder('অর্ডার করুন')->maxLength(40),
                    Forms\Components\DateTimePicker::make('countdown_ends_at')->label('অফার শেষ হওয়ার সময় (কাউন্টডাউন)')->helperText('খালি রাখলে কাউন্টডাউন দেখাবে না।')->seconds(false),
                    Forms\Components\TextInput::make('video_url')->label('YouTube ভিডিও লিংক (ঐচ্ছিক)')->url()->maxLength(300),
                ]),

            Forms\Components\Section::make('বিস্তারিত')
                ->schema([
                    Forms\Components\Repeater::make('features')
                        ->label('বিশেষত্ব / সুবিধা')
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('টাইটেল')->required()->maxLength(80),
                            Forms\Components\TextInput::make('text')->label('ছোট লেখা')->maxLength(200),
                        ])
                        ->columns(2)
                        ->maxItems(8)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state) => $state['title'] ?? null),
                    Forms\Components\FileUpload::make('gallery')
                        ->label('গ্যালারির ছবি')
                        ->image()->multiple()->reorderable()->maxFiles(10)
                        ->disk('public')->directory('landing'),
                    Forms\Components\RichEditor::make('body')
                        ->label('বিস্তারিত লেখা')
                        ->toolbarButtons(['h2', 'h3', 'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'blockquote', 'link', 'attachFiles', 'undo', 'redo'])
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('landing'),
                ]),

            Forms\Components\Section::make('অর্ডার ফর্ম')
                ->schema([
                    Forms\Components\TextInput::make('form_title')->label('ফর্মের শিরোনাম')->placeholder('অর্ডার করতে নিচের ফর্মটি পূরণ করুন')->maxLength(120),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('hero_image')->label('')->disk('public')->square()->size(48),
                Tables\Columns\TextColumn::make('title')->label('পেজ')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('product.name')->label('প্রোডাক্ট')->placeholder('—'),
                Tables\Columns\TextColumn::make('slug')->label('লিংক')->formatStateUsing(fn ($state) => '/lp/' . $state)->copyable()->copyableState(fn (LandingPage $r) => $r->url()),
                Tables\Columns\IconColumn::make('is_published')->label('পাবলিশ')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('আপডেট')->since(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\Action::make('open')->label('')->tooltip('পেজ দেখুন')->icon('heroicon-o-arrow-top-right-on-square')->iconButton()
                    ->url(fn (LandingPage $r) => $r->url(), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make()->label('')->tooltip('এডিট')->iconButton(),
                Tables\Actions\DeleteAction::make()->label('')->tooltip('ডিলিট')->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLandingPages::route('/'),
            'create' => Pages\CreateLandingPage::route('/create'),
            'edit' => Pages\EditLandingPage::route('/{record}/edit'),
        ];
    }
}
