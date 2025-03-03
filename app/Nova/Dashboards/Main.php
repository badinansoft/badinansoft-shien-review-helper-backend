<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\LicensesPerStatus;
use App\Nova\Metrics\TotalUsageLogs;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{

    public function cards(): array
    {
        return [
            new LicensesPerStatus(),
            new TotalUsageLogs(),
        ];
    }
}
