<?php

namespace App\Filament\Widgets;

use App\Support\SalesStats;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonthKpis extends BaseWidget
{
    protected static ?int $sort = -9;

    protected function getColumns(): int
    {
        return 4;
    }

    private function trend(?float $pct, string $suffix = 'গত মাসের একই সময়ের তুলনায়'): array
    {
        if ($pct === null) {
            return ['তুলনার ডেটা নেই', 'heroicon-m-minus', 'gray'];
        }

        return [
            abs($pct) . '% ' . ($pct >= 0 ? 'বেশি' : 'কম') . ' — ' . $suffix,
            $pct >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down',
            $pct >= 0 ? 'success' : 'danger',
        ];
    }

    protected function getStats(): array
    {
        $m = SalesStats::month();
        $p = SalesStats::lastMonthSamePeriod();
        $aov = $m['orders'] ? $m['revenue'] / $m['orders'] : 0;
        $paov = $p['orders'] ? $p['revenue'] / $p['orders'] : 0;

        [$d1, $i1, $c1] = $this->trend(SalesStats::pct($m['revenue'], $p['revenue']));
        [$d2, $i2, $c2] = $this->trend(SalesStats::pct((float) $m['orders'], (float) $p['orders']));
        [$d3, $i3, $c3] = $this->trend(SalesStats::pct($aov, $paov));

        $pf = SalesStats::profit(now()->startOfMonth(), now()->endOfMonth());
        $pfDesc = $pf['items'] && $pf['missing'] === $pf['items']
            ? 'প্রোডাক্টে ক্রয়মূল্য দিন, তবে লাভ হিসাব হবে'
            : 'মোট লাভ ' . SalesStats::taka($pf['gross']) . ' − খরচ ' . SalesStats::taka($pf['expenses']);

        return [
            Stat::make('এই মাসের বিক্রি', SalesStats::taka($m['revenue']))->description($d1)->descriptionIcon($i1)->color($c1),
            Stat::make('এই মাসের অর্ডার', $m['orders'])->description($d2)->descriptionIcon($i2)->color($c2),
            Stat::make('গড় অর্ডার মূল্য', SalesStats::taka($aov))->description($d3)->descriptionIcon($i3)->color($c3),
            Stat::make('এই মাসের নেট লাভ', SalesStats::taka($pf['net']))
                ->description($pfDesc)
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($pf['net'] >= 0 ? 'success' : 'danger'),
        ];
    }
}
