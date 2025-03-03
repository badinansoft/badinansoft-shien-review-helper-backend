<?php

namespace App\Nova\Metrics;

use App\Models\UsageLog;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\{Value, ValueResult};
use Laravel\Nova\Nova;

class TotalUsageLogs extends Value
{
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, UsageLog::class);
    }

    public function ranges(): array
    {
        return [
            30 => Nova::__('30 Days'),
            60 => Nova::__('60 Days'),
            365 => Nova::__('365 Days'),
            'TODAY' => Nova::__('Today'),
            'MTD' => Nova::__('Month To Date'),
            'QTD' => Nova::__('Quarter To Date'),
            'YTD' => Nova::__('Year To Date'),
        ];
    }

    public function cacheFor(): DateTimeInterface|null
    {
        // return now()->addMinutes(5);

        return null;
    }
}
