<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Services\BahokService;
use App\Services\PathaoService;
use App\Services\SteadfastService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_to_selected_courier')
                ->label('নির্বাচিত কুরিয়ারে পাঠান')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Order $record) => filled($record->courier_name) && blank($record->courier_order_id) && blank($record->consignment_id))
                ->action(function (Order $record) {
                    $service = match ($record->courier_name) {
                        'steadfast' => app(SteadfastService::class),
                        'pathao' => app(PathaoService::class),
                        'bahok' => app(BahokService::class),
                        default => null,
                    };

                    if (!$service) {
                        Notification::make()->title('আগে উপরে কুরিয়ার সিলেক্ট করে সেভ করুন।')->danger()->send();

                        return;
                    }

                    $result = $service->createOrder($record);

                    Notification::make()
                        ->title($result['message'])
                        ->color($result['success'] ? 'success' : 'danger')
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }

    /**
     * total_amount is disabled/not dehydrated in the form (it's just a live preview),
     * so recompute the real value here from the record's current sub_total
     * (kept in sync separately by OrderItem model events) + the submitted
     * delivery_charge/discount, and persist it.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $subTotal = (float) $this->record->sub_total;
        $delivery = (float) ($data['delivery_charge'] ?? 0);
        $discount = (float) ($data['discount'] ?? 0);

        $data['total_amount'] = max(0, $subTotal + $delivery - $discount);

        return $data;
    }
}
