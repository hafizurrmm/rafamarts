<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StoreSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'স্টোর সেটিংস (Store Settings)';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // Store Branding
            'site_name' => Setting::get('site_name', 'ShopBD'),
            'site_logo' => Setting::get('site_logo'),
            'site_favicon' => Setting::get('site_favicon'),
            'banner_type' => Setting::get('banner_type', 'static'),
            'banner_image' => Setting::get('banner_image'),
            'banner_images' => Setting::getArray('banner_images'),
            'hotline_number' => Setting::get('hotline_number'),
            'whatsapp_number' => Setting::get('whatsapp_number'),
            'facebook_url' => Setting::get('facebook_url'),
            'site_description' => Setting::get('site_description'),
            'product_return_policy' => Setting::get('product_return_policy'),

            // Facebook Pixel & Conversions API (CAPI)
            'fb_enabled' => (bool) Setting::get('fb_enabled', false),
            'fb_pixel_id' => Setting::get('fb_pixel_id'),
            'fb_access_token' => Setting::get('fb_access_token'),
            'fb_test_event_code' => Setting::get('fb_test_event_code'),

            // Courier & Shipping
            'steadfast_enabled' => (bool) Setting::get('steadfast_enabled', false),
            'steadfast_api_key' => Setting::get('steadfast_api_key'),
            'steadfast_secret_key' => Setting::get('steadfast_secret_key'),

            'pathao_enabled' => (bool) Setting::get('pathao_enabled', false),
            'pathao_environment' => Setting::get('pathao_environment', 'sandbox'),
            'pathao_client_id' => Setting::get('pathao_client_id'),
            'pathao_client_secret' => Setting::get('pathao_client_secret'),
            'pathao_username' => Setting::get('pathao_username'),
            'pathao_password' => Setting::get('pathao_password'),
            'pathao_store_id' => Setting::get('pathao_store_id'),
            'pathao_default_city_id' => Setting::get('pathao_default_city_id'),
            'pathao_default_zone_id' => Setting::get('pathao_default_zone_id'),
            'pathao_default_area_id' => Setting::get('pathao_default_area_id'),

            'bahok_enabled' => (bool) Setting::get('bahok_enabled', false),
            'bahok_api_token' => Setting::get('bahok_api_token'),

            // Payment Gateway (COD only for now — placeholders for future SSLCommerz/Stripe)
            'cod_enabled' => (bool) Setting::get('cod_enabled', true),
            'sslcommerz_enabled' => (bool) Setting::get('sslcommerz_enabled', false),
            'sslcommerz_store_id' => Setting::get('sslcommerz_store_id'),
            'sslcommerz_store_password' => Setting::get('sslcommerz_store_password'),

            // Manual mobile banking (bKash / Nagad / Rocket)
            'bkash_enabled' => (bool) Setting::get('bkash_enabled', false),
            'bkash_number' => Setting::get('bkash_number'),
            'bkash_type' => Setting::get('bkash_type', 'send_money'),
            'nagad_enabled' => (bool) Setting::get('nagad_enabled', false),
            'nagad_number' => Setting::get('nagad_number'),
            'nagad_type' => Setting::get('nagad_type', 'send_money'),
            'rocket_enabled' => (bool) Setting::get('rocket_enabled', false),
            'rocket_number' => Setting::get('rocket_number'),
            'rocket_type' => Setting::get('rocket_type', 'send_money'),

            // AI & SMS
            'sms_enabled' => (bool) Setting::get('sms_enabled', false),
            'sms_api_key' => Setting::get('sms_api_key'),
            'sms_sender_id' => Setting::get('sms_sender_id'),
            'ai_call_enabled' => (bool) Setting::get('ai_call_enabled', false),

            // Telegram order notification
            'telegram_enabled' => (bool) Setting::get('telegram_enabled', false),
            'telegram_bot_token' => Setting::get('telegram_bot_token'),
            'telegram_chat_id' => Setting::get('telegram_chat_id'),

            // Security & Fraud
            'fraud_alert_high_return' => (bool) Setting::get('fraud_alert_high_return', true),
            'fraud_auto_block_repeat_orders' => (bool) Setting::get('fraud_auto_block_repeat_orders', true),

            // Theme & Typography
            'primary_color' => Setting::get('primary_color', '#16a34a'),
            'secondary_color' => Setting::get('secondary_color', '#0f172a'),
            'body_bg_color' => Setting::get('body_bg_color', '#f9fafb'),
            'product_image_height' => Setting::get('product_image_height', '18rem'),
            'product_title_font_size' => Setting::get('product_title_font_size', '0.875rem'),
            'product_price_font_size' => Setting::get('product_price_font_size', '1rem'),
            'product_button_font_size' => Setting::get('product_button_font_size', '0.875rem'),
            'product_compare_price_font_size' => Setting::get('product_compare_price_font_size', '0.75rem'),
            'section_heading_font_size' => Setting::get('section_heading_font_size', '1.5rem'),

            // Grid layout
            'flash_sale_columns' => Setting::get('flash_sale_columns', 5),
            'all_products_columns' => Setting::get('all_products_columns', 5),
            'all_products_rows' => Setting::get('all_products_rows', 3),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Settings')
                ->tabs([

                    Forms\Components\Tabs\Tab::make('স্টোর ব্র্যান্ডিং')
                        ->icon('heroicon-o-building-storefront')
                        ->schema([
                            Forms\Components\TextInput::make('site_name')->label('Site Name')->required(),
                            Forms\Components\FileUpload::make('site_logo')->label('Site Logo')->image()->directory('settings')->imageEditor(),
                            Forms\Components\FileUpload::make('site_favicon')
                                ->label('Favicon (ব্রাউজার ট্যাব আইকন)')
                                ->image()
                                ->directory('settings')
                                ->helperText('বর্গাকার ছবি ভালো হয় (যেমন 512x512px)। খালি রাখলে Site Logo, সেটাও না থাকলে ডিফল্ট আইকন দেখাবে।'),

                            Forms\Components\Radio::make('banner_type')
                                ->label('হোমপেজ ব্যানার')
                                ->options([
                                    'static' => 'স্ট্যাটিক (একটাই ছবি)',
                                    'slider' => 'স্লাইড (একাধিক ছবি ঘুরে ঘুরে দেখাবে)',
                                ])
                                ->default('static')
                                ->live()
                                ->columnSpanFull(),

                            Forms\Components\FileUpload::make('banner_image')
                                ->label('Banner Image')
                                ->image()->directory('settings')->imageEditor()
                                ->visible(fn (callable $get) => $get('banner_type') === 'static'),

                            Forms\Components\FileUpload::make('banner_images')
                                ->label('Banner Images (একাধিক)')
                                ->image()->directory('settings')->multiple()->reorderable()
                                ->visible(fn (callable $get) => $get('banner_type') === 'slider')
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('hotline_number')->label('Hotline Number'),
                            Forms\Components\TextInput::make('whatsapp_number')->label('WhatsApp Number')->placeholder('8801XXXXXXXXX')->helperText('দেশের কোডসহ দিন (880...)। 01XXXXXXXXX দিলেও সাইট নিজে থেকে ঠিক করে নেবে।'),
                            Forms\Components\TextInput::make('facebook_url')->label('Facebook Page URL')->url(),
                            Forms\Components\Textarea::make('site_description')
                                ->label('সাইট বর্ণনা (SEO / Facebook লিংক প্রিভিউ)')
                                ->helperText('গুগল সার্চ এবং ফেসবুকে লিংক শেয়ার করলে এই লেখাটা দেখাবে। ১৫০-১৬০ অক্ষরের মধ্যে রাখুন।')
                                ->rows(3)
                                ->columnSpanFull(),
                            Forms\Components\RichEditor::make('product_return_policy')
                                ->label('রিটার্ন পলিসি (প্রোডাক্ট পেজের "রিটার্ন পলিসি" ট্যাব)')
                                ->helperText('খালি রাখলে একটা ডিফল্ট লেখা দেখাবে। সব প্রোডাক্টে একই লেখা যাবে।')
                                ->toolbarButtons(['h2', 'h3', 'bold', 'italic', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('কুরিয়ার ও শিপিং')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            Forms\Components\Section::make('Steadfast Courier API')->schema([
                                Forms\Components\Toggle::make('steadfast_enabled')->label('Status (ON/OFF)')->inline(false),
                                Forms\Components\TextInput::make('steadfast_api_key')->label('API Key'),
                                Forms\Components\TextInput::make('steadfast_secret_key')->label('Secret Key')->password()->revealable(),
                            ])->columns(3),

                            Forms\Components\Section::make('Pathao Courier API')->schema([
                                Forms\Components\Toggle::make('pathao_enabled')->label('Status (ON/OFF)')->inline(false),
                                Forms\Components\Select::make('pathao_environment')
                                    ->options(['sandbox' => 'Sandbox', 'live' => 'Live'])->default('sandbox'),
                                Forms\Components\TextInput::make('pathao_client_id')->label('Client ID'),
                                Forms\Components\TextInput::make('pathao_client_secret')->label('Client Secret')->password()->revealable(),
                                Forms\Components\TextInput::make('pathao_username')->label('Username (Email)'),
                                Forms\Components\TextInput::make('pathao_password')->label('Password')->password()->revealable(),
                                Forms\Components\TextInput::make('pathao_store_id')->label('Store ID'),
                                Forms\Components\TextInput::make('pathao_default_city_id')->label('Default City ID')->numeric(),
                                Forms\Components\TextInput::make('pathao_default_zone_id')->label('Default Zone ID')->numeric(),
                                Forms\Components\TextInput::make('pathao_default_area_id')->label('Default Area ID')->numeric(),
                            ])->columns(3),

                            Forms\Components\Section::make('Bahok Courier API')
                                ->description('Bahok এর নির্দিষ্ট এন্ডপয়েন্ট/ফিল্ড নাম আপনার merchant panel এর ডকুমেন্টেশন দেখে যাচাই করে নিন।')
                                ->schema([
                                    Forms\Components\Toggle::make('bahok_enabled')->label('Status (ON/OFF)')->inline(false),
                                    Forms\Components\TextInput::make('bahok_api_token')->label('API Token')->password()->revealable(),
                                ])->columns(2),
                        ]),

                    Forms\Components\Tabs\Tab::make('পেমেন্ট গেটওয়ে')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Forms\Components\Toggle::make('cod_enabled')->label('Cash on Delivery (COD)')->default(true)->columnSpanFull(),

                            Forms\Components\Section::make('মোবাইল ব্যাংকিং (ম্যানুয়াল — bKash / Nagad / Rocket)')
                                ->description('চেকআউটে কাস্টমার আপনার নম্বরে টাকা পাঠিয়ে TrxID ও প্রেরকের নম্বর দেবে। অর্ডার পেজে TrxID মিলিয়ে "পেমেন্ট যাচাই হয়েছে" টগল অন করবেন। নম্বর খালি থাকলে ওই মেথড চেকআউটে দেখাবে না।')
                                ->schema([
                                    Forms\Components\Toggle::make('bkash_enabled')->label('bKash চালু করুন')->columnSpanFull(),
                                    Forms\Components\TextInput::make('bkash_number')->label('bKash নম্বর')->placeholder('01XXXXXXXXX'),
                                    Forms\Components\Select::make('bkash_type')->label('নম্বরের ধরন')
                                        ->options(['send_money' => 'Send Money (পার্সোনাল)', 'payment' => 'Payment (মার্চেন্ট)'])
                                        ->default('send_money')->native(false),

                                    Forms\Components\Toggle::make('nagad_enabled')->label('Nagad চালু করুন')->columnSpanFull(),
                                    Forms\Components\TextInput::make('nagad_number')->label('Nagad নম্বর')->placeholder('01XXXXXXXXX'),
                                    Forms\Components\Select::make('nagad_type')->label('নম্বরের ধরন')
                                        ->options(['send_money' => 'Send Money (পার্সোনাল)', 'payment' => 'Payment (মার্চেন্ট)'])
                                        ->default('send_money')->native(false),

                                    Forms\Components\Toggle::make('rocket_enabled')->label('Rocket চালু করুন')->columnSpanFull(),
                                    Forms\Components\TextInput::make('rocket_number')->label('Rocket নম্বর')->placeholder('01XXXXXXXXX-X'),
                                    Forms\Components\Select::make('rocket_type')->label('নম্বরের ধরন')
                                        ->options(['send_money' => 'Send Money (পার্সোনাল)', 'payment' => 'Payment (মার্চেন্ট)'])
                                        ->default('send_money')->native(false),
                                ])->columns(2)->columnSpanFull(),

                            Forms\Components\Section::make('SSLCommerz (অটো গেটওয়ে — এখনো চালু হয়নি)')
                                ->schema([
                                    Forms\Components\Toggle::make('sslcommerz_enabled')->label('SSLCommerz'),
                                    Forms\Components\TextInput::make('sslcommerz_store_id')->label('Store ID'),
                                    Forms\Components\TextInput::make('sslcommerz_store_password')->label('Store Password')->password()->revealable(),
                                ])->columns(3)->columnSpanFull()->collapsed(),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('এআই ও এসএমএস')
                        ->icon('heroicon-o-cpu-chip')
                        ->schema([
                            Forms\Components\Toggle::make('sms_enabled')->label('SMS Campaign চালু করুন'),
                            Forms\Components\TextInput::make('sms_api_key')->label('SMS Gateway API Key')->password()->revealable(),
                            Forms\Components\TextInput::make('sms_sender_id')->label('Sender ID'),
                            Forms\Components\Toggle::make('ai_call_enabled')->label('AI Call চালু করুন'),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('ফেসবুক পিক্সেল ও CAPI')
                        ->icon('heroicon-o-megaphone')
                        ->schema([
                            Forms\Components\Section::make('Facebook Pixel + Conversions API')
                                ->description(
                                    'Pixel (ব্রাউজার থেকে) আর Conversions API (সার্ভার থেকে) — দুটো একসাথে চালু থাকলে Facebook '
                                    . 'iOS14+/Ad-blocker এর কারণে হারানো ডেটাও ফিরে পায়, ফলে Event Match Quality বাড়ে, '
                                    . 'বিজ্ঞাপনের অপ্টিমাইজেশন ভালো হয় এবং Cost Per Result কমে।'
                                )
                                ->schema([
                                    Forms\Components\Toggle::make('fb_enabled')
                                        ->label('Facebook Pixel + Conversions API চালু করুন')
                                        ->live()
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('fb_pixel_id')
                                        ->label('Pixel ID')
                                        ->helperText('Meta Events Manager > Data Sources > আপনার Pixel থেকে সংখ্যাটি কপি করুন।')
                                        ->visible(fn (callable $get) => $get('fb_enabled')),

                                    Forms\Components\TextInput::make('fb_access_token')
                                        ->label('Conversions API Access Token')
                                        ->password()->revealable()
                                        ->helperText('Events Manager > Settings > Conversions API থেকে "Generate access token" করুন (সিস্টেম ইউজার দিয়ে বানালে মেয়াদ শেষ হবে না)।')
                                        ->visible(fn (callable $get) => $get('fb_enabled')),

                                    Forms\Components\TextInput::make('fb_test_event_code')
                                        ->label('Test Event Code (ঐচ্ছিক)')
                                        ->helperText('Events Manager > Test Events ট্যাবে কোডটি পাবেন। লাইভে যাওয়ার আগে ইভেন্ট ঠিকমতো আসছে কিনা যাচাই করতে ব্যবহার করুন, তারপর খালি করে দিন।')
                                        ->visible(fn (callable $get) => $get('fb_enabled')),
                                ])->columns(2),
                        ]),

                    Forms\Components\Tabs\Tab::make('টেলিগ্রাম নোটিফিকেশন')
                        ->icon('heroicon-o-paper-airplane')
                        ->schema([
                            Forms\Components\Placeholder::make('telegram_info')
                                ->label('')
                                ->content('নতুন অর্ডার এলে আপনার মোবাইলের Telegram এ পপআপ নোটিফিকেশন আসবে। @BotFather এ /newbot দিয়ে বট বানিয়ে Token নিন, বটকে একবার /start দিন, তারপর https://api.telegram.org/bot<TOKEN>/getUpdates খুলে chat id নিন।')
                                ->columnSpanFull(),

                            Forms\Components\Toggle::make('telegram_enabled')
                                ->label('Telegram নোটিফিকেশন চালু করুন')
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('telegram_bot_token')
                                ->label('Bot Token')
                                ->password()->revealable(),

                            Forms\Components\TextInput::make('telegram_chat_id')
                                ->label('Chat ID')
                                ->helperText('গ্রুপ হলে আইডি -100... দিয়ে শুরু হয়।'),

                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('telegram_test')
                                    ->label('টেস্ট মেসেজ পাঠান')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->color('gray')
                                    ->action(function () {
                                        [$ok, $msg] = \App\Services\TelegramService::sendTest(
                                            $this->data['telegram_bot_token'] ?? null,
                                            $this->data['telegram_chat_id'] ?? null,
                                        );

                                        Notification::make()->title($msg)->{$ok ? 'success' : 'danger'}()->send();
                                    }),
                            ])->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('সিকিউরিটি ও ফ্রড')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Forms\Components\Placeholder::make('info')
                                ->label('')
                                ->content('কাস্টমার ব্ল্যাকলিস্ট হলো এমন একটি ডেটাবেজ, যার মাধ্যমে ফেক বা রিটার্ন করা কাস্টমারদের ট্র্যাক করা যায়। ব্ল্যাকলিস্ট তালিকা ম্যানেজ করতে Settings গ্রুপের "Blacklist" মেনুতে যান।'),

                            Forms\Components\Toggle::make('fraud_alert_high_return')
                                ->label('কোনো কাস্টমারের পূর্ববর্তী কুরিয়ার রিটার্ন রেট ৩০% এর বেশি হলে অর্ডার পেজে এলার্ট দেখাবে'),

                            Forms\Components\Toggle::make('fraud_auto_block_repeat_orders')
                                ->label('একই ফোন নম্বর থেকে ১ ঘণ্টায় ৩টির বেশি অর্ডার আসলে অটো-ব্লক করবে'),
                        ]),

                    Forms\Components\Tabs\Tab::make('থিম ও টাইপোগ্রাফি')
                        ->icon('heroicon-o-swatch')
                        ->schema([
                            Forms\Components\Section::make('ওয়েবসাইট কালার')->schema([
                                Forms\Components\ColorPicker::make('primary_color')
                                    ->label('প্রাইমারি কালার (বাটন, দাম, লিংক)'),
                                Forms\Components\ColorPicker::make('secondary_color')
                                    ->label('সেকেন্ডারি কালার (হেডার/ফুটার ব্যাকগ্রাউন্ড)'),
                                Forms\Components\ColorPicker::make('body_bg_color')
                                    ->label('বডি ব্যাকগ্রাউন্ড কালার (পুরো সাইটের পেছনের রঙ)')
                                    ->helperText('ডিফল্ট হালকা ধূসর (#f9fafb)। সাদা চাইলে #ffffff দিন।'),
                            ])->columns(2),

                            Forms\Components\Section::make('প্রোডাক্ট কন্টেন্ট সাইজ (Typography)')
                                ->description('প্রোডাক্ট কার্ডের ছবির উচ্চতা এবং লেখার সাইজ এখান থেকে নিয়ন্ত্রণ করুন। CSS ইউনিট সহ লিখুন, যেমন: 18rem, 0.875rem, 1rem')
                                ->schema([
                                    Forms\Components\TextInput::make('product_image_height')
                                        ->label('প্রোডাক্ট ছবির উচ্চতা')
                                        ->placeholder('18rem'),
                                    Forms\Components\TextInput::make('product_title_font_size')
                                        ->label('প্রোডাক্টের নামের ফন্ট সাইজ')
                                        ->placeholder('0.875rem'),
                                    Forms\Components\TextInput::make('product_price_font_size')
                                        ->label('দামের ফন্ট সাইজ')
                                        ->placeholder('1rem'),
                                    Forms\Components\TextInput::make('product_compare_price_font_size')
                                        ->label('পুরনো দাম (কাটা দাম) এর ফন্ট সাইজ')
                                        ->placeholder('0.75rem'),
                                    Forms\Components\TextInput::make('product_button_font_size')
                                        ->label('"অর্ডার করুন" বাটনের ফন্ট সাইজ')
                                        ->placeholder('0.875rem'),
                                    Forms\Components\TextInput::make('section_heading_font_size')
                                        ->label('সেকশন হেডিং ফন্ট সাইজ (Flash Sale, All Products)')
                                        ->placeholder('1.5rem'),
                                ])->columns(3),

                            Forms\Components\Section::make('গ্রিড লেআউট (কলাম ও রো)')
                                ->description('ডেস্কটপে কয়টা কলাম দেখাবে সেটা এখান থেকে ঠিক করুন। মোবাইলে স্বয়ংক্রিয়ভাবে কম কলামে দেখাবে।')
                                ->schema([
                                    Forms\Components\TextInput::make('flash_sale_columns')
                                        ->label('Flash Sale — ডেস্কটপে কলাম সংখ্যা')
                                        ->numeric()->minValue(2)->maxValue(8)->default(5),

                                    Forms\Components\TextInput::make('all_products_columns')
                                        ->label('All Products — ডেস্কটপে কলাম সংখ্যা')
                                        ->numeric()->minValue(2)->maxValue(8)->default(5),

                                    Forms\Components\TextInput::make('all_products_rows')
                                        ->label('All Products — প্রতি পেজে রো সংখ্যা')
                                        ->numeric()->minValue(1)->maxValue(10)->default(3)
                                        ->helperText('কলাম × রো = প্রতি পেজে মোট কতগুলো প্রোডাক্ট দেখাবে (পেজিনেশন)'),
                                ])->columns(3),
                        ]),
                ])
                ->columnSpanFull(),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany($state);

        Notification::make()->title('সেটিংস সেভ হয়েছে')->success()->send();
    }
}
