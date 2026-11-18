<x-filament-widgets::widget>
    <x-filament::section heading="⚡ কুইক অ্যাকশন">
        <div class="flex flex-col gap-2">
            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Resources\ProductResource::getUrl('create') }}"
                icon="heroicon-o-plus"
                color="primary"
                class="justify-start"
            >
                নতুন প্রোডাক্ট যোগ করুন
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Resources\BlacklistResource::getUrl() }}"
                icon="heroicon-o-shield-exclamation"
                color="gray"
                class="justify-start"
            >
                ব্ল্যাকলিস্ট ম্যানেজ করুন
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Pages\StoreSettings::getUrl() }}"
                icon="heroicon-o-cog-6-tooth"
                color="gray"
                class="justify-start"
            >
                স্টোর সেটিংস
            </x-filament::button>
        </div>

        <p class="mt-3 text-xs text-gray-400">
            💬 বাল্ক SMS ও 🎫 ডিসকাউন্ট কুপন ফিচার শীঘ্রই যোগ হবে।
        </p>
    </x-filament::section>
</x-filament-widgets::widget>
