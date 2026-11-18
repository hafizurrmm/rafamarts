<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoices</title>
    <style>
        {{-- Plain English invoice — dompdf doesn't properly shape complex scripts like
             Bengali (no conjunct/matra reordering support), so Bangla text can render
             with wrong-looking glyphs even with a correct font. Keeping this invoice in
             English avoids that entirely and only needs dompdf's built-in fonts. --}}
        body { font-family: Helvetica, Arial, sans-serif; font-size: 13px; color: #111; }
        .invoice { padding: 24px; page-break-after: always; border: 1px solid #ddd; margin-bottom: 20px; }
        .invoice:last-child { page-break-after: auto; }
        h2 { margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        .totals td { border: none; padding: 2px 8px; }
        .no-print { text-align: center; margin: 16px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        @if(!($forPdf ?? false))
            <button onclick="window.print()">Print All</button>
        @endif
    </div>

    @forelse($orders as $order)
        <div class="invoice">
            <h2>{{ \App\Models\Setting::get('site_name', 'ShopBD') }} — Invoice</h2>
            <p>Order Number: <strong>{{ $order->order_number }}</strong> | Date: {{ $order->created_at->format('d M Y') }}</p>

            <p>
                <strong>{{ $order->customer_name }}</strong><br>
                {{ $order->customer_phone }}<br>
                {{ $order->customer_address }}
            </p>

            <table>
                <thead>
                    <tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>Tk {{ number_format($item->price, 0) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Tk {{ number_format($item->line_total, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="totals">
                <tr><td>Sub-Total</td><td>Tk {{ number_format($order->sub_total, 0) }}</td></tr>
                <tr><td>Delivery Charge</td><td>Tk {{ number_format($order->delivery_charge, 0) }}</td></tr>
                @if($order->discount > 0)
                    <tr><td>Discount</td><td>- Tk {{ number_format($order->discount, 0) }}</td></tr>
                @endif
                <tr><td><strong>Total</strong></td><td><strong>Tk {{ number_format($order->total_amount, 0) }}</strong></td></tr>
            </table>
        </div>
    @empty
        <p>No orders found.</p>
    @endforelse
</body>
</html>
