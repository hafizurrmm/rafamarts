<?php

namespace App\Http\Controllers;

use App\Models\AbandonedCheckout;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(Request $request)
    {
        abort_unless(\App\Support\Ui::on('track'), 404);
        return view('orders.track', ['order' => null, 'searched' => false, 'steps' => []]);
    }

    public function lookup(Request $request)
    {
        abort_unless(\App\Support\Ui::on('track'), 404);
        $data = $request->validate([
            'order_id' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:25'],
        ]);

        $phone = AbandonedCheckout::normalizePhone($data['phone']);
        $key = strtoupper(trim(strtr($data['order_id'], ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9'])));
        $key = ltrim($key, '#');

        $order = null;

        if ($phone) {
            $query = Order::query();
            if (ctype_digit($key)) {
                $query->where('id', (int) $key);
            } else {
                $query->where('order_number', str_starts_with($key, 'ORD-') ? $key : 'ORD-' . $key);
            }

            $candidate = $query->first();

            if ($candidate && AbandonedCheckout::normalizePhone($candidate->customer_phone) === $phone) {
                $order = $candidate->load('items');
            }
        }

        return view('orders.track', [
            'order' => $order,
            'searched' => true,
            'steps' => $order ? $this->steps($order) : [],
        ])->with('old', $data);
    }

    /** টাইমলাইনের ধাপ: [label, done] */
    private function steps(Order $order): array
    {
        $rank = match ($order->status) {
            'pending' => 1,
            'on_hold' => 1,
            'processing' => 2,
            'completed' => 4,
            default => 0,
        };

        if ($rank === 2 && ($order->courier_name || $order->consignment_id || $order->tracking_code)) {
            $rank = 3;
        }

        return [
            ['label' => 'অর্ডার গৃহীত', 'done' => $rank >= 1],
            ['label' => 'প্রসেসিং', 'done' => $rank >= 2],
            ['label' => 'কুরিয়ারে হস্তান্তর', 'done' => $rank >= 3],
            ['label' => 'ডেলিভারি সম্পন্ন', 'done' => $rank >= 4],
        ];
    }
}
