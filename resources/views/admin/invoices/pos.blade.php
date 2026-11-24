<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Invoice</title>
    <style>
        {{-- Narrow 80mm thermal-receipt layout — the compact "POS Invoice" style shown
             on the orders dashboard, separate from the full A4 "Invoice Print". Kept in
             English for the same dompdf script-shaping reason as the main invoice
             template (see admin.invoices.print). --}}
        @page { margin: 6px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #111; width: 72mm; margin: 0 auto; }
        .receipt { padding: 6px 2px; }
        .center { text-align: center; }
        h2 { margin: 0 0 2px; font-size: 14px; }
        .muted { color: #555; font-size: 10px; }
        hr { border: none; border-top: 1px dashed #999; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { padding: 2px 0; text-align: left; vertical-align: top; }
        th:last-child, td:last-child { text-align: right; }
        .totals td { padding: 1px 0; }
        .totals .grand td { font-weight: bold; border-top: 1px dashed #999; padding-top: 3px; }
        .no-print { text-align: center; margin: 12px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        @if(!($forPdf ?? false))
            <button onclick="window.print()">Print</button>
        @endif
    </div>

    @foreach($orders as $order)
        <div class="receipt">
            <div class="center">
                <h2>{{ \App\Models\Setting::get('site_name', config('shop.name')) }}</h2>
                @if(\App\Models\Setting::get('hotline_number'))
                    <div class="muted">Hotline: {{ \App\Models\Setting::get('hotline_number') }}</div>
                @endif
            </div>
            <hr>
            <div>Order: <strong>{{ $order->order_number }}</strong></div>
            <div class="muted">{{ $order->created_at->format('d M Y, h:i A') }}</div>
            <div class="muted">{{ $order->customer_name }} — {{ $order->customer_phone }}</div>
            <hr>
            <table>
                <thead>
                    <tr><th>Item</th><th>Qty</th><th>Total</th></tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->line_total, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <hr>
            <table class="totals">
                <tr><td>Sub-Total</td><td style="text-align:right">Tk {{ number_format($order->sub_total, 0) }}</td></tr>
                <tr><td>Delivery</td><td style="text-align:right">Tk {{ number_format($order->delivery_charge, 0) }}</td></tr>
                @if($order->discount > 0)
                    <tr><td>Discount</td><td style="text-align:right">- Tk {{ number_format($order->discount, 0) }}</td></tr>
                @endif
                <tr class="grand"><td>Total</td><td style="text-align:right">Tk {{ number_format($order->total_amount, 0) }}</td></tr>
            </table>
            <hr>
            <p class="center muted">Thank you! Please order again.</p>
        </div>
    @endforeach
</body>
</html>
