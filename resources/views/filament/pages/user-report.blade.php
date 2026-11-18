<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left border-b">
                <tr>
                    <th class="py-2">Customer</th>
                    <th class="py-2">Phone</th>
                    <th class="py-2">Total Orders</th>
                    <th class="py-2">Total Spent</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b">
                        <td class="py-2">{{ $row->customer_name }}</td>
                        <td class="py-2">{{ $row->customer_phone }}</td>
                        <td class="py-2">{{ $row->total_orders }}</td>
                        <td class="py-2">৳ {{ number_format($row->total_spent, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-500">এখনো কোনো অর্ডার নেই।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
