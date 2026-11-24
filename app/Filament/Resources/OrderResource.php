<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Models\Blacklist;
use App\Models\Order;
use App\Models\Setting;
use App\Services\BahokService;
use App\Services\PathaoService;
use App\Services\RedXService;
use App\Services\SteadfastService;
use App\Support\FraudInfo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'অর্ডার';

    protected static ?string $navigationGroup = 'ই-কমার্স';

    protected static ?int $navigationSort = 2;

    public const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'on_hold' => 'On Hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
    ];

    public const COURIER_STATUS_OPTIONS = [
        'sent' => 'Sent to courier',
        'in_transit' => 'In transit',
        'delivered' => 'Delivered',
        'partial_delivered' => 'Partial delivered',
        'returned' => 'Returned',
        'hold' => 'On hold',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Live-updates the (disabled, display-only) total_amount field in the form
     * as delivery_charge/discount change, using the record's existing sub_total.
     * The actual persisted value is recomputed again in EditOrder::mutateFormDataBeforeSave().
     */
    protected static function recalculateTotalPreview(callable $set, callable $get, ?Order $record): void
    {
        $subTotal = (float) ($record?->sub_total ?? 0);
        $delivery = (float) ($get('delivery_charge') ?? 0);
        $discount = (float) ($get('discount') ?? 0);

        $set('total_amount', max(0, $subTotal + $delivery - $discount));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('fraud_alert')
                ->label('')
                ->content(function (?Order $record) {
                    if (!$record || !Setting::get('fraud_alert_high_return', true)) {
                        return null;
                    }

                    $entry = Blacklist::where('phone_number', $record->customer_phone)->first();

                    if (!$entry || $entry->return_count < 1) {
                        return null;
                    }

                    return "⚠️ সতর্কতা: এই কাস্টমারের ({$entry->phone_number}) পূর্ববর্তী রিটার্ন হিস্ট্রি আছে — {$entry->return_count} বার। কারণ: " . ($entry->reason ?? 'উল্লেখ নেই');
                })
                ->visible(fn (?Order $record) => $record && Blacklist::where('phone_number', $record->customer_phone)->where('return_count', '>=', 1)->exists())
                ->extraAttributes(['class' => 'text-danger-600 font-semibold']),

            // Live own-store delivery-success rate for this customer — computed fresh
            // from Order history every time the edit page opens (cheap, indexed query,
            // see customer_phone index in the performance migration). The deeper
            // network-wide Steadfast check lives on the separate Fraud Checker page
            // (Settings > Fraud Checker) rather than here, since that's an external
            // API call best kept on-demand instead of on every single order page load.
            Forms\Components\Placeholder::make('fraud_history')
                ->label('')
                ->content(function (?Order $record) {
                    if (!$record) {
                        return null;
                    }

                    $orders = Order::where('customer_phone', $record->customer_phone)
                        ->where('id', '!=', $record->id)
                        ->get();

                    $total = $orders->count();

                    if ($total === 0) {
                        return 'ℹ️ এই নম্বর থেকে এটাই প্রথম অর্ডার।';
                    }

                    $completed = $orders->where('status', 'completed')->count();
                    $cancelled = $orders->whereIn('status', ['cancelled', 'returned'])->count();
                    $finished = $completed + $cancelled;
                    $rate = $finished > 0 ? (int) round($completed / $finished * 100) : null;

                    $rateText = $rate !== null ? "{$rate}% সাকসেস রেট" : 'এখনো কোনো অর্ডার সম্পন্ন/ক্যান্সেল হয়নি';

                    return "📊 এই নম্বরের আগের {$total}টা অর্ডার — {$completed}টা ডেলিভারি সম্পন্ন, {$cancelled}টা ক্যান্সেল ({$rateText})। সম্পূর্ণ নেটওয়ার্ক-ওয়াইড ফ্রড চেকের জন্য Settings > Fraud Checker ব্যবহার করুন।";
                })
                ->visible(fn (?Order $record) => $record !== null)
                ->extraAttributes(['class' => 'text-gray-600 text-sm']),

            Forms\Components\Section::make('Customer Info')->schema([
                Forms\Components\TextInput::make('order_number')->disabled(),
                Forms\Components\TextInput::make('customer_name')->required(),
                Forms\Components\TextInput::make('customer_phone')->required(),
                Forms\Components\Textarea::make('customer_address')->required()->columnSpanFull(),
                Forms\Components\Textarea::make('customer_note')->columnSpanFull(),
                Forms\Components\TextInput::make('customer_ip')->label('Customer IP')->disabled()->dehydrated(false),
                Forms\Components\TextInput::make('ip_location')->label('IP লোকেশন')->disabled()->dehydrated(false)
                    ->helperText(fn (?Order $record) => $record?->ip_country_code && $record->ip_country_code !== 'BD' ? '⚠ বাংলাদেশের বাইরের IP — সতর্ক থাকুন' : null),
            ])->columns(2),

            Forms\Components\Section::make('Order Status')->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'returned' => 'Returned',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('sub_total')
                    ->numeric()->prefix('৳')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('অর্ডার আইটেম থেকে অটো-ক্যালকুলেট হয়'),

                Forms\Components\TextInput::make('delivery_charge')
                    ->numeric()->prefix('৳')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get, ?Order $record) =>
                        static::recalculateTotalPreview($set, $get, $record)
                    ),

                Forms\Components\TextInput::make('discount')
                    ->label('ডিসকাউন্ট')
                    ->numeric()->prefix('৳')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get, ?Order $record) =>
                        static::recalculateTotalPreview($set, $get, $record)
                    ),

                Forms\Components\TextInput::make('total_amount')
                    ->numeric()->prefix('৳')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Sub-Total + Delivery − Discount'),
            ])->columns(3),

            // bKash/Nagad/Rocket ম্যানুয়াল পেমেন্ট — শুধু মোবাইল পেমেন্টের অর্ডারে দেখায়
            Forms\Components\Section::make('Payment Info')
                ->schema([
                    Forms\Components\Placeholder::make('payment_method_label')
                        ->label('পেমেন্ট মেথড')
                        ->content(fn (?Order $record) => $record?->paymentLabel() ?? '—'),
                    Forms\Components\TextInput::make('payment_trx_id')->label('TrxID')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('payment_sender_number')->label('প্রেরকের নম্বর')->disabled()->dehydrated(false),
                    Forms\Components\Toggle::make('payment_verified')
                        ->label('পেমেন্ট যাচাই হয়েছে')
                        ->inline(false)
                        ->helperText('bKash/Nagad/Rocket অ্যাপে TrxID ও টাকার পরিমাণ মিলিয়ে অন করুন। যাচাইয়ের আগে কুরিয়ারে পাঠাবেন না।'),
                ])
                ->columns(4)
                ->visible(fn (?Order $record) => $record?->isMobilePayment() ?? false),

            Forms\Components\Section::make('Courier Info')
                ->schema([
                    Forms\Components\Select::make('courier_name')
                        ->label('কুরিয়ার সিলেক্ট করুন')
                        ->options([
                            'steadfast' => 'Steadfast',
                            'pathao' => 'Pathao',
                            'redx' => 'RedX',
                            'bahok' => 'Bahok',
                        ])
                        ->placeholder('কোনোটি নয়')
                        ->live(),

                    Forms\Components\TextInput::make('consignment_id')->label('Consignment ID')->disabled(),
                    Forms\Components\TextInput::make('tracking_code')->label('Tracking Code')->disabled(),
                    Forms\Components\TextInput::make('courier_status')->label('Courier Status')->disabled(),
                ])
                ->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        // আইটেম/প্রোডাক্ট ছবি eager-load (N+1 এড়াতে) + এই নম্বরের আগের অর্ডার সংখ্যা ("New Customer" ব্যাজের জন্য)
        ->modifyQueryUsing(fn ($query) => $query
            ->with('items.product')
            ->addSelect([
                'orders.*',
                'prior_orders' => \App\Models\Order::query()
                    ->from('orders as o2')
                    ->selectRaw('count(*)')
                    ->whereColumn('o2.customer_phone', 'orders.customer_phone')
                    ->whereColumn('o2.id', '<', 'orders.id'),
            ])
        )
        ->columns([
            Tables\Columns\ViewColumn::make('order_info')
                ->label('Order')
                ->view('filament.tables.columns.order-info')
                ->searchable(['order_number'])
                ->sortable(['created_at']),
            Tables\Columns\ViewColumn::make('customer_info')
                ->label('Customer')
                ->view('filament.tables.columns.customer-info')
                ->searchable(['customer_name', 'customer_phone', 'customer_address', 'customer_ip', 'payment_trx_id']),
            Tables\Columns\TextColumn::make('courier_rate')
                ->label('Courier Rate')
                ->state(function (Order $record) {
                    $rate = FraudInfo::local($record->customer_phone)['success_rate'];

                    return $rate === null ? 'New' : $rate . '%';
                })
                ->badge()
                ->color(function (Order $record) {
                    $rate = FraudInfo::local($record->customer_phone)['success_rate'];

                    return $rate === null ? 'gray' : ($rate >= 80 ? 'success' : ($rate >= 60 ? 'warning' : 'danger'));
                })
                ->description('Details')
                ->tooltip('ফ্রড চেক দেখতে ক্লিক করুন')
                ->action(
                    Tables\Actions\Action::make('fraud_details')
                        ->modalHeading(fn (Order $record) => 'কাস্টমার যাচাই — ' . $record->customer_phone)
                        ->modalContent(function (Order $record) {
                            [$steadfastData, $steadfastError] = FraudInfo::steadfast($record->customer_phone);

                            return view('filament.modals.fraud-details', [
                                'localStats' => FraudInfo::local($record->customer_phone),
                                'blacklistEntry' => FraudInfo::blacklist($record->customer_phone),
                                'steadfastData' => $steadfastData,
                                'steadfastError' => $steadfastError,
                            ]);
                        })
                        ->modalWidth('4xl')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('বন্ধ করুন')
                ),
            Tables\Columns\TextColumn::make('total_amount')->money('BDT')->sortable(),
            Tables\Columns\TextColumn::make('payment_method')
                ->label('Payment')
                ->badge()
                ->formatStateUsing(fn ($state, Order $record) => $record->isMobilePayment()
                    ? $record->paymentLabel() . ($record->payment_verified ? ' ✓' : ' ⏳')
                    : 'COD')
                ->color(fn ($state, Order $record) => ! $record->isMobilePayment()
                    ? 'gray'
                    : ($record->payment_verified ? 'success' : 'warning'))
                ->description(fn (Order $record) => $record->isMobilePayment() ? $record->payment_trx_id : null),
            // টেবিল থেকেই সরাসরি স্ট্যাটাস বদলানো যায় — সিলেক্ট করলেই সেভ হয়ে যায়
            Tables\Columns\SelectColumn::make('status')
                ->label('Status')
                ->options([
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'on_hold' => 'On Hold',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'returned' => 'Returned',
                ])
                ->selectablePlaceholder(false)
                ->afterStateUpdated(fn () => Notification::make()->title('স্ট্যাটাস আপডেট হয়েছে')->success()->send()),
            Tables\Columns\BadgeColumn::make('courier_name')
                ->label('Courier')
                ->colors([
                    'gray' => fn ($state) => blank($state),
                    'info' => 'steadfast',
                    'warning' => 'pathao',
                    'success' => 'bahok',
                    'danger' => 'redx',
                ])
                ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : '—'),
            Tables\Columns\TextColumn::make('courier_status')
                ->label('Courier Status')
                ->badge()
                ->placeholder('—'),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'on_hold' => 'On Hold',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'returned' => 'Returned',
                ]),
            Tables\Filters\SelectFilter::make('courier_name')
                ->label('Courier')
                ->options([
                    'steadfast' => 'Steadfast',
                    'pathao' => 'Pathao',
                    'redx' => 'RedX',
                    'bahok' => 'Bahok',
                ]),
        ])
        ->actions([
            Tables\Actions\Action::make('verify_payment')
                ->label('পেমেন্ট যাচাই হয়েছে')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (Order $record) => $record->isMobilePayment() && ! $record->payment_verified)
                ->requiresConfirmation()
                ->modalHeading('পেমেন্ট যাচাই করুন')
                ->modalDescription(fn (Order $record) => "{$record->paymentLabel()} — TrxID: {$record->payment_trx_id}, প্রেরক: {$record->payment_sender_number}, পরিমাণ: ৳" . number_format((float) $record->total_amount, 0) . '। অ্যাপে মিলিয়ে দেখেছেন?')
                ->action(function (Order $record) {
                    $record->update(['payment_verified' => true]);

                    Notification::make()->title('পেমেন্ট যাচাই হয়েছে')->success()->send();
                }),

            Tables\Actions\Action::make('block_customer')
                ->icon('heroicon-o-no-symbol')
                ->iconButton()
                ->color('warning')
                ->tooltip(fn (Order $record) => Blacklist::isBlocked((string) $record->customer_phone) ? 'ব্লক করা আছে — আনব্লক করুন' : 'কাস্টমার ব্লক করুন')
                ->requiresConfirmation()
                ->modalHeading(fn (Order $record) => Blacklist::isBlocked((string) $record->customer_phone) ? 'কাস্টমার আনব্লক করবেন?' : 'কাস্টমার ব্লক করবেন?')
                ->modalDescription(fn (Order $record) => $record->customer_phone . ' — ব্লক করলে এই নম্বর ব্ল্যাকলিস্টে যাবে।')
                ->action(function (Order $record) {
                    $phone = (string) $record->customer_phone;
                    $entry = Blacklist::where('phone_number', $phone)->first();

                    if ($entry && $entry->is_active) {
                        $entry->update(['is_active' => false]);
                        Notification::make()->title('আনব্লক করা হয়েছে')->success()->send();

                        return;
                    }

                    Blacklist::updateOrCreate(
                        ['phone_number' => $phone],
                        ['name' => $record->customer_name, 'reason' => 'অর্ডার লিস্ট থেকে ব্লক ('. $record->order_number .')', 'is_active' => true]
                    );
                    Notification::make()->title('কাস্টমার ব্লক করা হয়েছে')->warning()->send();
                }),

            Tables\Actions\EditAction::make()->label('')->iconButton()->tooltip('Edit'),
            Tables\Actions\DeleteAction::make()->label('')->iconButton()->tooltip('Delete'),
        ])
        ->bulkActions([
            // অর্ডার সিলেক্ট করলে উপরে এই অ্যাকশনগুলো আসে
            Tables\Actions\BulkAction::make('bulk_status')
                ->label('অর্ডার স্ট্যাটাস আপডেট')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('status')
                        ->label('নতুন স্ট্যাটাস')
                        ->options(self::STATUS_OPTIONS)
                        ->required(),
                ])
                ->action(function (\Illuminate\Support\Collection $records, array $data) {
                    // একে একে save() — যাতে অর্ডার মডেলের observer/ইভেন্ট (নোটিফিকেশন ইত্যাদি) চলে
                    $records->each(fn (Order $o) => $o->update(['status' => $data['status']]));

                    Notification::make()->title($records->count() . 'টি অর্ডারের স্ট্যাটাস আপডেট হয়েছে')->success()->send();
                })
                ->deselectRecordsAfterCompletion(),

            Tables\Actions\BulkAction::make('bulk_courier_status')
                ->label('কুরিয়ার স্ট্যাটাস আপডেট')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('courier_status')
                        ->label('কুরিয়ার স্ট্যাটাস')
                        ->options(self::COURIER_STATUS_OPTIONS)
                        ->required(),
                ])
                ->action(function (\Illuminate\Support\Collection $records, array $data) {
                    $records->each(fn (Order $o) => $o->update(['courier_status' => $data['courier_status']]));

                    Notification::make()->title($records->count() . 'টি অর্ডারের কুরিয়ার স্ট্যাটাস আপডেট হয়েছে')->success()->send();
                })
                ->deselectRecordsAfterCompletion(),

            Tables\Actions\BulkAction::make('bulk_send_courier')
                ->label('কুরিয়ারে পাঠান')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->modalHeading('কোন কুরিয়ারে পাঠাবেন?')
                ->modalSubmitActionLabel('পাঠান')
                ->form([
                    Forms\Components\Select::make('courier')
                        ->label('কুরিয়ার সিলেক্ট করুন')
                        ->options([
                            'steadfast' => 'Steadfast',
                            'pathao' => 'Pathao',
                            'redx' => 'RedX',
                            'bahok' => 'Bahok',
                        ])
                        ->default(fn () => collect(['steadfast', 'pathao', 'redx', 'bahok'])->first(fn ($c) => (bool) Setting::get($c . '_enabled', false)))
                        ->required(),
                ])
                ->action(fn (\Illuminate\Support\Collection $records, array $data) => self::bulkSendToCourier($records, $data['courier']))
                ->deselectRecordsAfterCompletion(),

            Tables\Actions\BulkAction::make('bulk_check_status')
                ->label('কুরিয়ার স্ট্যাটাস চেক')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (\Illuminate\Support\Collection $records) {
                    $done = 0;
                    foreach ($records->filter(fn (Order $o) => filled($o->courier_name)) as $o) {
                        $service = match ($o->courier_name) {
                            'pathao' => app(PathaoService::class),
                            'redx' => app(RedXService::class),
                            'bahok' => app(BahokService::class),
                            default => app(SteadfastService::class),
                        };
                        $service->checkStatus($o);
                        $done++;
                    }

                    Notification::make()->title($done . 'টি অর্ডারের কুরিয়ার স্ট্যাটাস আপডেট করা হয়েছে')->success()->send();
                })
                ->deselectRecordsAfterCompletion(),

            Tables\Actions\BulkAction::make('bulk_invoice')
                ->label('Invoice Print')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->action(fn (\Illuminate\Support\Collection $records) => redirect()->route('admin.orders.invoices', ['ids' => $records->pluck('id')->implode(',')]))
                ->deselectRecordsAfterCompletion(),

            Tables\Actions\BulkAction::make('bulk_pos_invoice')
                ->label('POS Invoice Print')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action(fn (\Illuminate\Support\Collection $records) => redirect()->route('admin.orders.invoices.pos', ['ids' => $records->pluck('id')->implode(',')]))
                ->deselectRecordsAfterCompletion(),

            Tables\Actions\BulkAction::make('bulk_export')
                ->label('CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (\Illuminate\Support\Collection $records) {
                    return response()->streamDownload(function () use ($records) {
                        $out = fopen('php://output', 'w');
                        fwrite($out, "\xEF\xBB\xBF"); // Excel এ বাংলা ঠিকমতো দেখাতে
                        fputcsv($out, ['Order', 'Date', 'Customer', 'Phone', 'Address', 'Total', 'Payment', 'Status', 'Courier', 'Courier Status', 'Consignment']);
                        foreach ($records as $o) {
                            fputcsv($out, [
                                $o->order_number, $o->created_at?->format('Y-m-d H:i'), $o->customer_name, $o->customer_phone,
                                $o->customer_address, $o->total_amount, $o->paymentLabel(), $o->status,
                                $o->courier_name, $o->courier_status, $o->consignment_id,
                            ]);
                        }
                        fclose($out);
                    }, 'orders-' . now()->format('Ymd-His') . '.csv');
                }),

            Tables\Actions\DeleteBulkAction::make()->label('')->iconButton()->tooltip('Delete selected'),
        ])
        ->checkIfRecordIsSelectableUsing(fn () => true);
    }

    /** সিলেক্ট করা অর্ডারগুলো কুরিয়ারে পাঠায়; আগেই পাঠানো থাকলে বাদ দেয় */
    protected static function bulkSendToCourier(\Illuminate\Support\Collection $records, string $courier): void
    {
        $service = match ($courier) {
            'pathao' => app(PathaoService::class),
            'redx' => app(RedXService::class),
            'bahok' => app(BahokService::class),
            default => app(SteadfastService::class),
        };

        $ok = 0;
        $skipped = 0;
        $failed = [];

        foreach ($records as $order) {
            if (filled($order->consignment_id) || filled($order->courier_order_id)) {
                $skipped++;
                continue;
            }

            $result = $service->createOrder($order);
            if ($result['success']) {
                $ok++;
            } else {
                $failed[] = $order->order_number . ': ' . ($result['message'] ?? 'ব্যর্থ');
            }
        }

        $n = Notification::make()
            ->title(ucfirst($courier) . " এ {$ok}টি পাঠানো হয়েছে" . ($skipped ? ", {$skipped}টি আগেই পাঠানো ছিল" : '') . ($failed ? ', ' . count($failed) . 'টি ব্যর্থ' : ''))
            ->color($failed ? ($ok ? 'warning' : 'danger') : 'success');

        if ($failed) {
            $n->body(implode("\n", array_slice($failed, 0, 5)));
        }

        $n->send();
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
