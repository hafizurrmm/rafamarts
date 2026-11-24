<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/** কুরিয়ার API কনফিগ: Steadfast, Pathao, RedX, Carrybee — প্রতিটির নিজস্ব Save / Clear */
class CourierApi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'কুরিয়ার API';

    protected static ?string $title = 'কুরিয়ার API';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 15;

    protected static string $view = 'filament.pages.courier-api';

    public array $data = [];

    /** courier => [badge, color, key prefix, [field key => [label, type, placeholder]], required keys] */
    public static function couriers(): array
    {
        return [
            'steadfast' => [
                'name' => 'Steadfast Courier', 'badge' => 'SF', 'color' => '#16a34a',
                'fields' => [
                    'steadfast_api_key' => ['API Key', 'secret', 'API Key', 'half'],
                    'steadfast_secret_key' => ['Secret Key', 'secret', 'Secret Key', 'half'],
                ],
                'enabled' => 'steadfast_enabled',
            ],
            'pathao' => [
                'name' => 'Pathao Courier', 'badge' => 'PT', 'color' => '#e11d48',
                'fields' => [
                    'pathao_client_id' => ['Client ID', 'text', 'Client ID', 'half'],
                    'pathao_client_secret' => ['Client Secret', 'secret', 'Client Secret', 'half'],
                    'pathao_username' => ['Username (Email)', 'text', 'name@example.com', 'half'],
                    'pathao_password' => ['Password', 'secret', 'Password', 'half'],
                    'pathao_store_id' => ['Store ID', 'text', 'Store ID', 'full'],
                ],
                'enabled' => 'pathao_enabled',
            ],
            'redx' => [
                'name' => 'RedX', 'badge' => 'RX', 'color' => '#dc2626',
                'fields' => [
                    'redx_access_token' => ['API Access Token', 'secret', 'API Access Token', 'full'],
                ],
                'enabled' => 'redx_enabled',
            ],
            'carrybee' => [
                'name' => 'Carrybee', 'badge' => 'CB', 'color' => '#f59e0b',
                'fields' => [
                    'carrybee_client_id' => ['Client ID', 'text', 'Client ID', 'half'],
                    'carrybee_client_secret' => ['Client Secret', 'secret', 'Client Secret', 'half'],
                    'carrybee_client_context' => ['Client Context', 'text', 'Client Context', 'half'],
                    'carrybee_store_id' => ['Store ID', 'text', 'Store ID', 'half'],
                ],
                'enabled' => 'carrybee_enabled',
            ],
        ];
    }

    public function mount(): void
    {
        $this->fillData();
    }

    protected function fillData(): void
    {
        foreach (self::couriers() as $c) {
            foreach (array_keys($c['fields']) as $key) {
                $this->data[$key] = (string) Setting::get($key, '');
            }
            $this->data[$c['enabled']] = (bool) Setting::get($c['enabled'], false);
        }
    }

    public function save(string $courier): void
    {
        $c = self::couriers()[$courier] ?? null;
        abort_unless($c, 404);

        foreach (array_keys($c['fields']) as $key) {
            Setting::set($key, trim((string) ($this->data[$key] ?? '')));
        }
        Setting::set($c['enabled'], (bool) ($this->data[$c['enabled']] ?? false));

        Notification::make()->title($c['name'] . ' সেভ হয়েছে')->success()->send();
    }

    public function clear(string $courier): void
    {
        $c = self::couriers()[$courier] ?? null;
        abort_unless($c, 404);

        foreach (array_keys($c['fields']) as $key) {
            Setting::set($key, '');
        }
        Setting::set($c['enabled'], false);
        $this->fillData();

        Notification::make()->title($c['name'] . ' এর তথ্য মুছে ফেলা হয়েছে')->warning()->send();
    }

    /** কার্ডের ব্যাজ: সেট করা হয়নি / সেট করা আছে / চালু */
    public function statusFor(string $courier): array
    {
        $c = self::couriers()[$courier];
        $filled = collect(array_keys($c['fields']))->every(fn ($k) => filled(Setting::get($k)));

        if ($filled && Setting::get($c['enabled'])) {
            return ['চালু আছে', '#047857', '#d1fae5'];
        }

        return $filled ? ['সেট করা আছে', '#b45309', '#fef3c7'] : ['সেট করা হয়নি', '#6b7280', '#f3f4f6'];
    }
}
