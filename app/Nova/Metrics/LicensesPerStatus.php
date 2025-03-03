<?php

namespace App\Nova\Metrics;

use App\Models\License;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class LicensesPerStatus extends Partition
{
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->count($request, License::class, 'status')
            ->label(function ($value) {
                return ucfirst($value);
            });
    }


    public function cacheFor(): DateTimeInterface|null
    {
        // return now()->addMinutes(5);

        return null;
    }


    public function uriKey(): string
    {
        return 'licenses-per-status';
    }
}
