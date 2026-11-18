<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <form wire:submit.prevent="check" class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium mb-1">কাস্টমারের মোবাইল নম্বর</label>
                <input type="text" wire:model="phone" placeholder="01XXXXXXXXX"
                       class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2
                              bg-white dark:bg-gray-900 text-gray-900 dark:text-white
                              placeholder-gray-400 dark:placeholder-gray-500
                              focus:outline-none focus:ring-2 focus:ring-primary-500">
                @error('phone')
                    <p class="text-danger-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-6 py-2 rounded whitespace-nowrap">
                চেক করুন
            </button>
        </form>
    </div>

    @if($checked)
        <div class="grid md:grid-cols-2 gap-4 mt-4">

            {{-- Own store history --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h3 class="font-bold mb-3">এই দোকানে এই নম্বরের অর্ডার হিস্ট্রি</h3>

                @if($localStats['total'] === 0)
                    <p class="text-gray-500 text-sm">এই নম্বর থেকে আপনার দোকানে আগে কোনো অর্ডার আসেনি — নতুন কাস্টমার।</p>
                @else
                    <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                            <div class="text-gray-500">মোট অর্ডার</div>
                            <div class="text-xl font-bold">{{ $localStats['total'] }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                            <div class="text-gray-500">সাকসেস রেট</div>
                            <div class="text-xl font-bold {{ $localStats['success_rate'] !== null && $localStats['success_rate'] < 70 ? 'text-danger-600' : 'text-success-600' }}">
                                {{ $localStats['success_rate'] !== null ? $localStats['success_rate'] . '%' : '—' }}
                            </div>
                        </div>
                        <div class="bg-success-50 dark:bg-gray-900 rounded p-3">
                            <div class="text-gray-500">ডেলিভারি সম্পন্ন</div>
                            <div class="text-lg font-semibold text-success-600">{{ $localStats['completed'] }}</div>
                        </div>
                        <div class="bg-danger-50 dark:bg-gray-900 rounded p-3">
                            <div class="text-gray-500">ক্যান্সেল/রিটার্ন</div>
                            <div class="text-lg font-semibold text-danger-600">{{ $localStats['cancelled'] }}</div>
                        </div>
                    </div>
                    @if($localStats['pending'] > 0)
                        <p class="text-xs text-gray-500">+ {{ $localStats['pending'] }}টা অর্ডার এখনো পেন্ডিং/প্রসেসিং এ আছে, হিসাবে ধরা হয়নি।</p>
                    @endif
                @endif

                @if($blacklistEntry)
                    <div class="mt-3 bg-danger-50 text-danger-700 rounded p-3 text-sm font-semibold">
                        ⚠️ এই নম্বর ব্ল্যাকলিস্টে আছে{{ $blacklistEntry->is_active ? '' : ' (বর্তমানে আনব্লকড)' }}
                        @if($blacklistEntry->reason)
                            — কারণ: {{ $blacklistEntry->reason }}
                        @endif
                    </div>
                @endif
            </div>

            {{-- Steadfast network-wide fraud score --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h3 class="font-bold mb-3">Steadfast নেটওয়ার্ক-ওয়াইড ফ্রড চেক</h3>
                <p class="text-xs text-gray-500 mb-3">শুধু আপনার দোকান না, বাংলাদেশের সব মার্চেন্টের ডেলিভারি হিস্ট্রি মিলিয়ে এই স্কোর — নতুন কাস্টমার যাচাইয়ের জন্য সবচেয়ে কার্যকর।</p>

                @if($steadfastData)
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                            <div class="text-gray-500">ডেলিভারি রেশিও</div>
                            <div class="text-xl font-bold {{ ($steadfastData['delivery_ratio'] ?? null) !== null && $steadfastData['delivery_ratio'] < 70 ? 'text-danger-600' : 'text-success-600' }}">
                                {{ $steadfastData['delivery_ratio'] !== null ? $steadfastData['delivery_ratio'] . '%' : '—' }}
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                            <div class="text-gray-500">ক্যান্সেলেশন রেশিও</div>
                            <div class="text-xl font-bold {{ ($steadfastData['cancellation_ratio'] ?? null) !== null && $steadfastData['cancellation_ratio'] > 30 ? 'text-danger-600' : 'text-success-600' }}">
                                {{ $steadfastData['cancellation_ratio'] !== null ? $steadfastData['cancellation_ratio'] . '%' : '—' }}
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                            <div class="text-gray-500">অর্ডারের পরিমাণ</div>
                            <div class="text-lg font-semibold">{{ ucfirst($steadfastData['volume_band'] ?? '—') }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                            <div class="text-gray-500">রিপোর্ট সংখ্যা</div>
                            <div class="text-lg font-semibold">{{ $steadfastData['total_reports'] ?? 0 }}</div>
                        </div>
                    </div>

                    @if(!empty($steadfastData['fraud_categories']))
                        <div class="mt-3 bg-danger-50 text-danger-700 rounded p-3 text-sm">
                            <strong>ফ্রড ক্যাটাগরি:</strong> {{ implode(', ', $steadfastData['fraud_categories']) }}
                        </div>
                    @endif
                @elseif($steadfastError)
                    <p class="text-sm text-gray-500">{{ $steadfastError }}</p>
                @endif
            </div>
        </div>

        <div class="mt-3 text-xs text-gray-400">
            এই রিপোর্ট শুধু একটা সিগনাল — চূড়ান্ত সিদ্ধান্ত না। সন্দেহজনক মনে হলে কাস্টমারকে কল করে নিশ্চিত হয়ে নিন।
        </div>
    @endif
</x-filament-panels::page>
