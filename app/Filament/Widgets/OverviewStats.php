<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Scan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $active = session('active_event_id');

        $eventsCount = Event::query()
            ->where('is_published', true)
            ->count();

        $regsQuery = Registration::query();
        if ($active) {
            $regsQuery->where('event_id', $active);
        }
        $totalRegs = (clone $regsQuery)->count();
        $approved  = (clone $regsQuery)->where('status', Registration::ST_APPROVED)->count();
        $pending   = (clone $regsQuery)->where('status', Registration::ST_PENDING)->count();

        $scansQuery = Scan::query()->when($active, function ($q, $id) {
            $q->whereHas('registration', fn ($r) => $r->where('event_id', $id));
        });
        $scansToday = (clone $scansQuery)->whereDate('created_at', today())->count();

        return [
            Stat::make('Published Events', number_format($eventsCount))
                ->icon('heroicon-o-calendar')
                ->description('Events ready for public')
                ->color('info'),
            Stat::make('Registrations', number_format($totalRegs))
                ->icon('heroicon-o-ticket')
                ->description($active ? 'For active event' : 'All events')
                ->color('primary'),
            Stat::make('Approved', number_format($approved))
                ->icon('heroicon-o-check-badge')
                ->description('Approved registrations')
                ->color('success'),
            Stat::make('Pending', number_format($pending))
                ->icon('heroicon-o-clock')
                ->description('Awaiting review')
                ->color('warning'),
            Stat::make('Scans Today', number_format($scansToday))
                ->icon('heroicon-o-qr-code')
                ->description('Check-ins today')
                ->color('secondary'),
        ];
    }
}
