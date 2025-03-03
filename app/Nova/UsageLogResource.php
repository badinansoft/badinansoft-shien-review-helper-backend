<?php

namespace App\Nova;

use App\Models\UsageLog;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{BelongsTo, DateTime, ID, Number, Text};

class UsageLogResource extends Resource
{

    public static string $model = UsageLog::class;

    /**
     * @var string
     */
    public static $title = 'id';

    /**
     * @var array
     */
    public static $search = [
        'id', 'url', 'ip_address',
    ];


    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            BelongsTo::make(__('License'), 'license', LicenseResource::class),

            Text::make(__('URL'), 'url')
                ->hideFromIndex()
                ->sortable(),

            DateTime::make(__('Timestamp'), 'timestamp')
                ->sortable(),

            Number::make(__('Reviews Filled'), 'reviews_filled')
                ->sortable(),

            Number::make(__('Images Attached'), 'images_attached')
                ->sortable(),

            Text::make(__('IP Address'), 'ip_address')
                ->sortable(),

            Text::make(__('User Agent'), 'user_agent'),
        ];
    }

    public static function label(): string
    {
        return __('Usage Logs');
    }

    public static function singularLabel(): string
    {
        return __('Usage Log');
    }

    public static function authorizedToCreate(Request $request): false
    {
        return false;
    }
}
