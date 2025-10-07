<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\StatsOverviewResource\Widgets\AdminChart;
use App\Models\Staff;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;


class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    use HasWidgetShield;
    protected static ?string $maxHeight = '100px';
    protected static ?int $sort = 1;
    public function getColumns(): int
    {
        return 2;
    }


    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        return [


            Stat::make('Active Staffs', Staff::query()
                ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                ->where('is_approved', 1)
                ->count())
                ->description('Active Medical Staffs')
                ->descriptionIcon('fas-wallet')
                ->color('info')
                ,

            Stat::make('Pending Approval', Staff::query()
                ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                ->where('is_approved', 0)
                ->count(), )
                ->description('Pending Approval')
                ->descriptionIcon('fas-clock')
                ->color('primary')
                ,

            Stat::make('Rejected Staffs', Staff::query()
                ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                ->where('is_approved', 'defaulted')
                ->count(), )
                ->description('Rejected Staffs')
                ->descriptionIcon('fas-sync')
                ->color('danger')
                ,


            Stat::make('Victims', Staff::query()
                ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                ->where('is_approve', 0)
                ->count())
                ->description('Victims')
                ->descriptionIcon('fas-wallet')
                ->color('success')











        ];
    }
}
