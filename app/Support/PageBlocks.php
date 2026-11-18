<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Illuminate\Support\Collection;

/**
 * Page Builder এর সব কম্পোনেন্ট (ব্লক) এক জায়গায়:
 *  - blocks()  → অ্যাডমিনে ফর্মের ঘরগুলো (Filament)
 *  - বাকি হেল্পারগুলো → resources/views/pages/blocks/*.blade.php থেকে ডাকা হয়
 *
 * নতুন ব্লক যোগ করতে: এখানে একটা Block লিখে blocks() এর তালিকায় দিন,
 * তারপর resources/views/pages/blocks/{block-নাম}.blade.php বানান।
 */
class PageBlocks
{
    public static function blocks(): array
    {
        return [
            self::hero(),
            self::countdown(),
            self::text(),
            self::image(),
            self::gallery(),
            self::productGrid(),
            self::categories(),
            self::features(),
            self::imageText(),
            self::video(),
            self::testimonials(),
            self::faq(),
            self::cta(),
            self::contact(),
            self::spacer(),
            self::customHtml(),
        ];
    }

    // ------------------------------------------------------------------ ব্লক ফর্ম

    protected static function hero(): Block
    {
        return Block::make('hero')->label('হিরো ব্যানার')->icon('heroicon-o-photo')->columns(2)->schema([
            FileUpload::make('image')->label('ব্যাকগ্রাউন্ড ছবি')->image()->directory('pages')->columnSpanFull()
                ->helperText('ছবি না দিলে থিমের প্রাইমারি রং ব্যাকগ্রাউন্ড হবে।'),
            TextInput::make('heading')->label('বড় লেখা (হেডিং)')->maxLength(150)->columnSpanFull(),
            Textarea::make('subheading')->label('ছোট লেখা')->rows(2)->maxLength(300)->columnSpanFull(),
            ...self::buttonFields(),
            self::alignSelect(),
            Select::make('height')->label('উচ্চতা')->native(false)->default('md')
                ->options(['sm' => 'ছোট', 'md' => 'মাঝারি', 'lg' => 'বড়']),
            Select::make('overlay')->label('ছবির উপর অন্ধকার ভাব (লেখা পড়ার সুবিধার জন্য)')->native(false)->default('40')
                ->options(['0' => 'নেই', '20' => 'হালকা', '40' => 'মাঝারি', '60' => 'বেশি']),
        ]);
    }

    protected static function countdown(): Block
    {
        return Block::make('countdown')->label('কাউন্টডাউন টাইমার')->icon('heroicon-o-clock')->columns(2)->schema([
            TextInput::make('heading')->label('হেডিং')->default('অফার শেষ হতে বাকি')->columnSpanFull(),
            TextInput::make('subheading')->label('ছোট লেখা')->placeholder('যেমন: সব পণ্যে ২০% ছাড়')->columnSpanFull(),
            DateTimePicker::make('end_at')->label('শেষ হবে')->required()->seconds(false)->native(false)
                ->helperText('বাংলাদেশ সময় (BST) ধরা হবে। সময় শেষ হলে নিচের "শেষ হওয়ার পর লেখা" দেখাবে।'),
            TextInput::make('expired_text')->label('শেষ হওয়ার পর লেখা')->default('অফারটি শেষ হয়ে গেছে।'),
            ...self::buttonFields(),
            Select::make('style')->label('রঙ')->native(false)->default('dark')
                ->options(['dark' => 'গাঢ় (সেকেন্ডারি কালার)', 'brand' => 'প্রাইমারি কালার', 'light' => 'সাদা']),
        ]);
    }

    protected static function text(): Block
    {
        return Block::make('text')->label('লেখা (রিচ টেক্সট)')->icon('heroicon-o-document-text')->schema([
            RichEditor::make('content')->label('লেখা')->required()
                ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList', 'blockquote', 'link', 'undo', 'redo']),
            self::alignSelect('left'),
        ]);
    }

    protected static function image(): Block
    {
        return Block::make('image')->label('ছবি')->icon('heroicon-o-photo')->columns(2)->schema([
            FileUpload::make('image')->label('ছবি')->image()->required()->directory('pages')->columnSpanFull(),
            TextInput::make('alt')->label('ছবির বর্ণনা (SEO)'),
            TextInput::make('link_url')->label('ক্লিক করলে যে লিংকে যাবে (ঐচ্ছিক)'),
            Select::make('width')->label('প্রস্থ')->native(false)->default('full')
                ->options(['full' => 'পুরো প্রস্থ', 'wide' => 'বড়', 'narrow' => 'ছোট (মাঝখানে)']),
        ]);
    }

    protected static function gallery(): Block
    {
        return Block::make('gallery')->label('ছবির গ্যালারি')->icon('heroicon-o-squares-2x2')->columns(2)->schema([
            TextInput::make('heading')->label('হেডিং (ঐচ্ছিক)')->columnSpanFull(),
            FileUpload::make('images')->label('ছবিগুলো')->image()->multiple()->reorderable()->required()
                ->directory('pages')->columnSpanFull(),
            Select::make('columns')->label('ডেস্কটপে কলাম')->native(false)->default('3')
                ->options(['2' => '২', '3' => '৩', '4' => '৪']),
        ]);
    }

    protected static function productGrid(): Block
    {
        return Block::make('product_grid')->label('প্রোডাক্ট গ্রিড')->icon('heroicon-o-shopping-bag')->columns(2)->schema([
            TextInput::make('heading')->label('হেডিং')->placeholder('যেমন: নতুন পণ্য')->columnSpanFull(),
            Select::make('source')->label('কোন পণ্য দেখাবে')->native(false)->live()->default('latest')
                ->options([
                    'latest' => 'সর্বশেষ পণ্য',
                    'flash_sale' => 'Flash Sale এর পণ্য',
                    'category' => 'একটা ক্যাটাগরির পণ্য',
                    'selected' => 'আমি নিজে বেছে নেব',
                ]),
            Select::make('category_id')->label('ক্যাটাগরি')->native(false)->searchable()
                ->options(fn () => Category::orderBy('name')->pluck('name', 'id')->all())
                ->visible(fn (Get $get) => $get('source') === 'category')
                ->required(fn (Get $get) => $get('source') === 'category'),
            Select::make('products')->label('পণ্য বাছুন')->multiple()->searchable()->columnSpanFull()
                ->getSearchResultsUsing(fn (string $search) => Product::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id')->all())
                ->getOptionLabelsUsing(fn (array $values) => Product::whereIn('id', $values)->pluck('name', 'id')->all())
                ->visible(fn (Get $get) => $get('source') === 'selected')
                ->required(fn (Get $get) => $get('source') === 'selected'),
            TextInput::make('limit')->label('সর্বোচ্চ কয়টা পণ্য')->numeric()->default(8)->minValue(1)->maxValue(24),
            Select::make('columns')->label('ডেস্কটপে কলাম')->native(false)->default('4')
                ->options(['2' => '২', '3' => '৩', '4' => '৪', '5' => '৫']),
        ]);
    }

    protected static function categories(): Block
    {
        return Block::make('categories')->label('ক্যাটাগরি গ্রিড')->icon('heroicon-o-tag')->columns(2)->schema([
            TextInput::make('heading')->label('হেডিং')->default('ক্যাটাগরি')->columnSpanFull(),
            TextInput::make('limit')->label('সর্বোচ্চ কয়টা')->numeric()->default(8)->minValue(1)->maxValue(24),
        ]);
    }

    protected static function features(): Block
    {
        return Block::make('features')->label('সুবিধা / ট্রাস্ট ব্যাজ')->icon('heroicon-o-check-badge')->schema([
            TextInput::make('heading')->label('হেডিং (ঐচ্ছিক)'),
            Repeater::make('items')->label('সুবিধাগুলো')->collapsible()->reorderable()
                ->itemLabel(fn (array $state) => $state['title'] ?? null)
                ->default([
                    ['icon' => '🚚', 'title' => 'সারা দেশে ডেলিভারি', 'text' => ''],
                    ['icon' => '💵', 'title' => 'ক্যাশ অন ডেলিভারি', 'text' => ''],
                    ['icon' => '📞', 'title' => 'যেকোনো সময় যোগাযোগ', 'text' => ''],
                    ['icon' => '🔒', 'title' => 'নিরাপদ অর্ডার', 'text' => ''],
                ])
                ->columns(3)
                ->schema([
                    TextInput::make('icon')->label('আইকন (ইমোজি)')->maxLength(8)->default('✅'),
                    TextInput::make('title')->label('শিরোনাম')->required(),
                    TextInput::make('text')->label('ছোট বর্ণনা'),
                ]),
        ]);
    }

    protected static function imageText(): Block
    {
        return Block::make('image_text')->label('ছবি + লেখা (পাশাপাশি)')->icon('heroicon-o-view-columns')->columns(2)->schema([
            FileUpload::make('image')->label('ছবি')->image()->required()->directory('pages')->columnSpanFull(),
            TextInput::make('heading')->label('হেডিং')->columnSpanFull(),
            RichEditor::make('text')->label('লেখা')->columnSpanFull()
                ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link']),
            ...self::buttonFields(),
            Select::make('image_position')->label('ছবি কোন পাশে')->native(false)->default('left')
                ->options(['left' => 'বামে', 'right' => 'ডানে']),
        ]);
    }

    protected static function video(): Block
    {
        return Block::make('video')->label('ভিডিও (YouTube)')->icon('heroicon-o-play-circle')->schema([
            TextInput::make('heading')->label('হেডিং (ঐচ্ছিক)'),
            TextInput::make('youtube_url')->label('YouTube লিংক')->required()->url()
                ->placeholder('https://www.youtube.com/watch?v=...'),
        ]);
    }

    protected static function testimonials(): Block
    {
        return Block::make('testimonials')->label('কাস্টমার রিভিউ')->icon('heroicon-o-chat-bubble-left-right')->schema([
            TextInput::make('heading')->label('হেডিং')->default('আমাদের কাস্টমাররা কী বলছেন'),
            Repeater::make('items')->label('রিভিউ')->collapsible()->reorderable()
                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                ->helperText('শুধু আসল কাস্টমারের রিভিউ দিন (অনুমতি নিয়ে)। বানানো রিভিউ Facebook/Google অ্যাড পলিসি ভঙ্গ করে এবং কাস্টমারের আস্থা নষ্ট করে।')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->label('নাম')->required(),
                    TextInput::make('place')->label('এলাকা / পরিচয়'),
                    Textarea::make('text')->label('রিভিউ')->rows(3)->required()->columnSpanFull(),
                    Select::make('rating')->label('রেটিং')->native(false)->default('5')
                        ->options(['5' => '★★★★★', '4' => '★★★★☆', '3' => '★★★☆☆']),
                    FileUpload::make('photo')->label('ছবি (ঐচ্ছিক)')->image()->avatar()->directory('pages'),
                ]),
        ]);
    }

    protected static function faq(): Block
    {
        return Block::make('faq')->label('FAQ (প্রশ্ন-উত্তর)')->icon('heroicon-o-question-mark-circle')->schema([
            TextInput::make('heading')->label('হেডিং')->default('সাধারণ জিজ্ঞাসা'),
            Repeater::make('items')->label('প্রশ্ন ও উত্তর')->collapsible()->reorderable()
                ->itemLabel(fn (array $state) => $state['question'] ?? null)
                ->schema([
                    TextInput::make('question')->label('প্রশ্ন')->required(),
                    Textarea::make('answer')->label('উত্তর')->rows(3)->required(),
                ]),
        ]);
    }

    protected static function cta(): Block
    {
        return Block::make('cta')->label('বাটন সহ CTA সেকশন')->icon('heroicon-o-cursor-arrow-rays')->columns(2)->schema([
            TextInput::make('heading')->label('হেডিং')->required()->columnSpanFull(),
            Textarea::make('text')->label('ছোট লেখা')->rows(2)->columnSpanFull(),
            ...self::buttonFields(),
            Select::make('style')->label('রঙ')->native(false)->default('brand')
                ->options(['brand' => 'প্রাইমারি কালার', 'dark' => 'গাঢ় (সেকেন্ডারি কালার)', 'light' => 'সাদা']),
        ]);
    }

    protected static function contact(): Block
    {
        return Block::make('contact')->label('কন্টাক্ট ইনফো')->icon('heroicon-o-phone')->columns(2)->schema([
            TextInput::make('heading')->label('হেডিং')->default('যোগাযোগ করুন')->columnSpanFull(),
            Textarea::make('address')->label('ঠিকানা')->rows(2),
            TextInput::make('email')->label('ইমেইল')->email(),
            TextInput::make('hours')->label('খোলার সময়')->placeholder('যেমন: প্রতিদিন সকাল ১০টা - রাত ১০টা'),
            TextInput::make('map_embed_url')->label('Google Map এমবেড লিংক (ঐচ্ছিক)')
                ->placeholder('https://www.google.com/maps/embed?pb=...')
                ->helperText('Google Maps > Share > Embed a map থেকে iframe এর src="..." অংশটুকু বসান।'),
            Toggle::make('show_call')->label('কল বাটন (Store Settings এর হটলাইন নম্বর)')->default(true),
            Toggle::make('show_whatsapp')->label('WhatsApp বাটন (Store Settings থেকে)')->default(true),
            Toggle::make('show_facebook')->label('Facebook পেজ লিংক (Store Settings থেকে)')->default(true),
        ]);
    }

    protected static function spacer(): Block
    {
        return Block::make('spacer')->label('ফাঁকা জায়গা / লাইন')->icon('heroicon-o-minus')->columns(2)->schema([
            Select::make('size')->label('ফাঁকা জায়গার পরিমাণ')->native(false)->default('md')
                ->options(['sm' => 'ছোট', 'md' => 'মাঝারি', 'lg' => 'বড়']),
            Toggle::make('line')->label('মাঝখানে একটা লাইন দেখান'),
        ]);
    }

    protected static function customHtml(): Block
    {
        return Block::make('custom_html')->label('কাস্টম HTML / এমবেড (অ্যাডভান্সড)')->icon('heroicon-o-code-bracket')->schema([
            Textarea::make('html')->label('HTML কোড')->rows(8)->required()
                ->helperText('Facebook পোস্ট এমবেড, ম্যাপ, বা নিজের HTML বসাতে পারেন। শুধু বিশ্বস্ত কোড বসাবেন, ভুল কোড পেজ ভেঙে দিতে পারে।'),
        ]);
    }

    // ------------------------------------------------------------------ ফর্মের ছোট অংশ

    protected static function buttonFields(): array
    {
        return [
            TextInput::make('button_text')->label('বাটনের লেখা')->maxLength(60),
            TextInput::make('button_url')->label('বাটনের লিংক')
                ->placeholder('/product/... বা /page-name বা https://... বা tel:01XXXXXXXXX')
                ->helperText('লেখা ও লিংক দুটোই দিলে তবেই বাটন দেখাবে।'),
        ];
    }

    protected static function alignSelect(string $default = 'center'): Select
    {
        return Select::make('align')->label('লেখার অবস্থান')->native(false)->default($default)
            ->options(['left' => 'বামে', 'center' => 'মাঝে', 'right' => 'ডানে']);
    }

    // ------------------------------------------------------------------ ব্লেড টেমপ্লেটের হেল্পার

    /** আপলোড করা ছবির পাবলিক URL (ছবি না থাকলে null) */
    public static function img(?string $path): ?string
    {
        return $path ? asset('storage/' . ltrim($path, '/')) : null;
    }

    /** বাটনের লিংক ফাঁকা বা javascript: হলে null — তাহলে বাটন দেখানো হয় না */
    public static function safeUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || preg_match('/^\s*(javascript|data|vbscript):/i', $url)) {
            return null;
        }

        return $url;
    }

    /** Product Grid ব্লকের পণ্যগুলো */
    public static function products(array $data): Collection
    {
        $limit = max(1, min(24, (int) ($data['limit'] ?? 8)));
        $query = Product::where('is_active', true);

        switch ($data['source'] ?? 'latest') {
            case 'flash_sale':
                return $query->where('is_flash_sale', true)->latest()->take($limit)->get();

            case 'category':
                return $query->where('category_id', $data['category_id'] ?? 0)->latest()->take($limit)->get();

            case 'selected':
                $ids = array_values((array) ($data['products'] ?? []));

                return $query->whereIn('id', $ids)->get()
                    ->sortBy(fn ($product) => array_search($product->id, $ids))
                    ->take($limit)
                    ->values();

            default:
                return $query->latest()->take($limit)->get();
        }
    }

    /** Categories ব্লকের ক্যাটাগরিগুলো */
    public static function categoryList(array $data): Collection
    {
        $limit = max(1, min(24, (int) ($data['limit'] ?? 8)));

        return Category::where('is_active', true)->orderBy('sort_order')->take($limit)->get();
    }

    /** YouTube লিংক থেকে এমবেড URL (privacy-enhanced youtube-nocookie ডোমেইন); না মিললে null */
    public static function youtubeEmbed(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1];
        }

        return null;
    }

    /** 01XXXXXXXXX → https://wa.me/8801XXXXXXXXX (layouts/app এর মতোই নিয়ম) */
    public static function whatsappLink(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $number);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '01') && strlen($digits) === 11) {
            $digits = '88' . $digits;
        } elseif (str_starts_with($digits, '1') && strlen($digits) === 10) {
            $digits = '880' . $digits;
        }

        return 'https://wa.me/' . $digits;
    }

    public static function setting(string $key): ?string
    {
        $value = Setting::get($key);

        return $value ? (string) $value : null;
    }
}
