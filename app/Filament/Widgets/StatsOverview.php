<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', \App\Models\User::count())
                ->description('Active shops installed')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Total Flows', \App\Models\Flow::count())
                ->description('Customer workflows')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('primary'),
            Stat::make('Templates', \App\Models\Template::count())
                ->description('Available blueprints')
                ->descriptionIcon('heroicon-m-document-duplicate'),
            Stat::make('Total Executions', \App\Models\Execution::count())
                ->description('All time executions')
                ->descriptionIcon('heroicon-m-play')
                ->color('warning'),
        ];
    }
}
