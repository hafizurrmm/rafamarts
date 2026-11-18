<x-filament-widgets::widget>
    <x-filament::section heading="Today Report">
        <div class="divide-y divide-gray-100 dark:divide-white/10 text-sm">
            <div class="flex justify-between py-2.5 px-3 bg-gray-50 dark:bg-white/5 rounded">
                <span class="font-semibold">Today Order</span>
                <span class="font-bold">{{ $total }}</span>
            </div>
            @foreach($rows as $label => $count)
                <div class="flex justify-between py-2.5 px-3">
                    <span>{{ $label }}</span>
                    <span class="{{ $count > 0 ? 'font-semibold' : 'text-gray-400' }}">{{ $count }}</span>
                </div>
            @endforeach
            <div class="flex justify-between py-2.5 px-3 bg-gray-50 dark:bg-white/5 rounded mt-1">
                <span class="font-semibold">আজকের বিক্রির পরিমাণ</span>
                <span class="font-bold">৳{{ number_format($amount, 0) }}</span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
