<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left border-b">
                <tr>
                    <th class="py-2">Courier</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Total Orders</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b">
                        <td class="py-2">{{ ucfirst($row->courier_name) }}</td>
                        <td class="py-2">{{ $row->courier_status ?? '—' }}</td>
                        <td class="py-2">{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-500">এখনো কোনো অর্ডার কুরিয়ারে পাঠানো হয়নি।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
