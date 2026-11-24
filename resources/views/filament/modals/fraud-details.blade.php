<div class="grid md:grid-cols-2 gap-4 text-sm">
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="font-bold mb-3">এই দোকানে এই নম্বরের অর্ডার হিস্ট্রি</h3>

        @if($localStats['total'] === 0)
            <p class="text-gray-500">এই নম্বর থেকে আগে কোনো অর্ডার আসেনি — নতুন কাস্টমার।</p>
        @else
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                    <div class="text-gray-500">মোট অর্ডার</div>
                    <div class="text-xl font-bold">{{ $localStats['total'] }}</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                    <div class="text-gray-500">সাকসেস রেট</div>
                    <div class="text-xl font-bold" style="color: {{ $localStats['success_rate'] !== null && $localStats['success_rate'] < 70 ? '#dc2626' : '#059669' }}">
                        {{ $localStats['success_rate'] !== null ? $localStats['success_rate'] . '%' : '—' }}
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                    <div class="text-gray-500">ডেলিভারি সম্পন্ন</div>
                    <div class="text-lg font-semibold" style="color:#059669">{{ $localStats['completed'] }}</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                    <div class="text-gray-500">ক্যান্সেল/রিটার্ন</div>
                    <div class="text-lg font-semibold" style="color:#dc2626">{{ $localStats['cancelled'] }}</div>
                </div>
            </div>
            @if($localStats['pending'] > 0)
                <p class="text-xs text-gray-500">+ {{ $localStats['pending'] }}টা অর্ডার এখনো পেন্ডিং/প্রসেসিং এ আছে, হিসাবে ধরা হয়নি।</p>
            @endif
        @endif

        @if($blacklistEntry)
            <div class="mt-3 rounded p-3 font-semibold" style="background:#fee2e2;color:#b91c1c">
                ⚠️ এই নম্বর ব্ল্যাকলিস্টে আছে{{ $blacklistEntry->is_active ? '' : ' (বর্তমানে আনব্লকড)' }}
                @if($blacklistEntry->reason) — কারণ: {{ $blacklistEntry->reason }} @endif
            </div>
        @endif
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="font-bold mb-1">Steadfast নেটওয়ার্ক-ওয়াইড ফ্রড চেক</h3>
        <p class="text-xs text-gray-500 mb-3">সব মার্চেন্টের ডেলিভারি হিস্ট্রি মিলিয়ে স্কোর — নতুন কাস্টমার যাচাইয়ের জন্য কার্যকর।</p>

        @if($steadfastData)
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                    <div class="text-gray-500">ডেলিভারি রেশিও</div>
                    <div class="text-xl font-bold" style="color: {{ ($steadfastData['delivery_ratio'] ?? null) !== null && $steadfastData['delivery_ratio'] < 70 ? '#dc2626' : '#059669' }}">
                        {{ ($steadfastData['delivery_ratio'] ?? null) !== null ? $steadfastData['delivery_ratio'] . '%' : '—' }}
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                    <div class="text-gray-500">ক্যান্সেলেশন রেশিও</div>
                    <div class="text-xl font-bold" style="color: {{ ($steadfastData['cancellation_ratio'] ?? null) !== null && $steadfastData['cancellation_ratio'] > 30 ? '#dc2626' : '#059669' }}">
                        {{ ($steadfastData['cancellation_ratio'] ?? null) !== null ? $steadfastData['cancellation_ratio'] . '%' : '—' }}
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
                <div class="mt-3 rounded p-3" style="background:#fee2e2;color:#b91c1c">
                    <strong>ফ্রড ক্যাটাগরি:</strong> {{ implode(', ', $steadfastData['fraud_categories']) }}
                </div>
            @endif
        @elseif($steadfastError)
            <p class="text-gray-500">{{ $steadfastError }}</p>
        @endif
    </div>
</div>

<p class="mt-3 text-xs text-gray-400">এই রিপোর্ট শুধু একটা সিগনাল — চূড়ান্ত সিদ্ধান্ত না। সন্দেহজনক মনে হলে কাস্টমারকে কল করে নিশ্চিত হয়ে নিন।</p>
