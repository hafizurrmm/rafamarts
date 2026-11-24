<?php

namespace App\Filament\Pages;

use App\Models\Blacklist;
use App\Models\Order;
use App\Services\SteadfastService;
use Filament\Pages\Page;

/**
 * Lookup a phone number and see its delivery-success / return history BEFORE
 * confirming an order — the single most effective fraud tool for a COD store.
 *
 * Shows two signals side by side:
 *  1. Your own order history for this number (always available, free, instant).
 *  2. Steadfast's network-wide Fraud Check score (delivery/cancellation ratio
 *     across EVERY merchant, not just yours) — this is what catches a fraud
 *     customer who has never ordered from you before, which #1 can't.
 */
class FraudChecker extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'Fraud Checker';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 30;

    protected static string $view = 'filament.pages.fraud-checker';

    public ?string $phone = null;

    public bool $checked = false;

    public ?array $localStats = null;

    public ?Blacklist $blacklistEntry = null;

    public ?array $steadfastData = null;

    public ?string $steadfastError = null;

    public function check(): void
    {
        $this->validate(['phone' => 'required|string|min:6|max:20']);

        $phone = trim($this->phone);
        $this->checked = true;

        // --- 1) Your own store's order history for this number ---
        $orders = Order::where('customer_phone', $phone)->get();
        $total = $orders->count();
        $completed = $orders->where('status', 'completed')->count();
        $cancelled = $orders->where('status', 'cancelled')->count();
        $pending = $total - $completed - $cancelled;
        $finished = $completed + $cancelled; // only finished orders count toward a rate

        $this->localStats = [
            'total' => $total,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'pending' => $pending,
            'success_rate' => $finished > 0 ? (int) round($completed / $finished * 100) : null,
        ];

        $this->blacklistEntry = Blacklist::where('phone_number', $phone)->first();

        // --- 2) Steadfast's network-wide Fraud Check (optional — needs API keys) ---
        $this->steadfastData = null;
        $this->steadfastError = null;

        $service = app(SteadfastService::class);

        if ($service->isConfigured()) {
            $result = $service->checkFraud($phone);

            if ($result['success']) {
                $this->steadfastData = $result['data'];
            } else {
                $this->steadfastError = $result['message'];
            }
        } else {
            $this->steadfastError = 'Steadfast চালু/কনফিগার করা নেই — শুধু নিজের স্টোরের হিস্ট্রি দেখানো হচ্ছে। Settings > কুরিয়ার API তে গিয়ে Steadfast চালু করলে নেটওয়ার্ক-ওয়াইড ফ্রড স্কোরও দেখা যাবে।';
        }
    }
}
