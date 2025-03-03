<?php

namespace App\Nova;

use App\Enums\LicenseStatus;
use App\Models\License;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{DateTime, HasMany, ID, Number, Select, Text, Textarea};
use Laravel\Nova\Panel;

class LicenseResource extends Resource
{
    public static string $model = License::class;

    /**
     * @var string
     */
    public static $title = 'name';

    /**
     * @var array
     */
    public static $search = [
        'id', 'name', 'key', 'device_id', 'notes',
    ];


    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required', 'string', 'max:255'),

            Text::make(__('License Key'), 'key')
                ->sortable()
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->rules('required', 'string', 'max:255'),

            Select::make(__('Status'), 'status')
                ->options(LicenseStatus::options())
                ->sortable()
                ->displayUsingLabels()
                ->rules('required', 'in:' . implode(',', LicenseStatus::values())),

            Text::make(__('Device ID'), 'device_id')
                ->nullable()
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->sortable(),

            DateTime::make(__('Expires At'), 'expires_at')
                ->nullable()
                ->sortable(),

            Number::make(__('Tokens Used'), 'tokens_used')
                ->sortable()
                ->readonly(),

            new Panel('Additional Information', [
                Textarea::make(__('Notes'), 'notes')
                    ->nullable()
                    ->alwaysShow(),
            ]),

            HasMany::make(__('Usage Logs'), 'usageLogs', UsageLogResource::class),
        ];
    }


    public function cards(Request $request): array
    {
        return [];
    }


    public function filters(Request $request): array
    {
        return [];
    }


    public function lenses(Request $request): array
    {
        return [];
    }

    public function actions(Request $request): array
    {
        return [
            new Actions\GenerateLicenseKey,
        ];
    }

    public static function label(): string
    {
        return __('Licenses');
    }

    public static function singularLabel(): string
    {
        return __('License');
    }
}
