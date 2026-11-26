<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** খরচের খাতা — বিজ্ঞাপন, প্যাকিং ইত্যাদি। ড্যাশবোর্ডের নেট লাভ এখান থেকে হিসাব হয়। */
class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'ই-কমার্স';

    protected static ?string $navigationLabel = 'খরচ';

    protected static ?string $modelLabel = 'খরচ';

    protected static ?string $pluralModelLabel = 'খরচ';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('spent_on')->label('তারিখ')->default(now())->required(),
            Forms\Components\Select::make('type')->label('ধরন')->options(Expense::TYPES)->default('ad')->required()->native(false),
            Forms\Components\TextInput::make('title')->label('বিবরণ')->required()->maxLength(150)->placeholder('যেমন: Facebook Ads — নভেম্বর'),
            Forms\Components\TextInput::make('amount')->label('টাকা')->numeric()->prefix('৳')->required()->minValue(0),
            Forms\Components\Textarea::make('note')->label('নোট')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('spent_on', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('spent_on')->label('তারিখ')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('title')->label('বিবরণ')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('ধরন')->badge()->formatStateUsing(fn ($s) => Expense::TYPES[$s] ?? $s),
                Tables\Columns\TextColumn::make('amount')->label('টাকা')->money('BDT')->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('মোট')->money('BDT')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label('ধরন')->options(Expense::TYPES),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageExpenses::route('/')];
    }
}
