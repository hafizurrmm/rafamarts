<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    // Print-friendly HTML view for one or many orders (used by the "Bulk Invoice Print" action)
    public function index(Request $request)
    {
        $ids = array_filter(explode(',', (string) $request->query('ids')));

        $orders = Order::with('items')->whereIn('id', $ids)->get();

        return view('admin.invoices.print', compact('orders'));
    }

    // Opens the PDF in a new tab (browser's built-in PDF viewer) — from there the user
    // can preview it and use the browser's own print/download icons.
    public function view(Order $order)
    {
        $order->load('items');
        $orders = collect([$order]);

        $pdf = Pdf::loadView('admin.invoices.print', ['orders' => $orders, 'forPdf' => true]);

        return $pdf->stream("invoice-{$order->order_number}.pdf");
    }

    // Forces an actual file download instead of opening inline (kept as a separate
    // option in case you want a direct-download link somewhere instead).
    public function download(Order $order)
    {
        $order->load('items');
        $orders = collect([$order]);

        $pdf = Pdf::loadView('admin.invoices.print', ['orders' => $orders, 'forPdf' => true]);

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }

    // "POS Invoice Print" — the compact 80mm receipt-style invoice shown as a
    // separate button on the Orders list/edit page (matches the reference screenshot).
    public function posInvoice(Order $order)
    {
        $order->load('items');
        $orders = collect([$order]);

        $pdf = Pdf::loadView('admin.invoices.pos', ['orders' => $orders, 'forPdf' => true])
            ->setPaper([0, 0, 226.77, 800], 'portrait'); // ~80mm width, auto height

        return $pdf->stream("pos-invoice-{$order->order_number}.pdf");
    }

    // সিলেক্ট করা একাধিক অর্ডারের POS রিসিট এক PDF-এ
    public function posBulk(Request $request)
    {
        $ids = array_filter(explode(',', (string) $request->query('ids')));
        $orders = Order::with('items')->whereIn('id', $ids)->get();
        abort_if($orders->isEmpty(), 404);

        $pdf = Pdf::loadView('admin.invoices.pos', ['orders' => $orders, 'forPdf' => true])
            ->setPaper([0, 0, 226.77, 800], 'portrait');

        return $pdf->stream('pos-invoices.pdf');
    }
}
