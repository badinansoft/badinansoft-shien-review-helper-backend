<?php

namespace App\Nova;

use App\Enums\ContentStatus;
use App\Models\Image;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image as NovaImage;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;

class ImageResource extends Resource
{
    public static string $model = Image::class;

    /**
     * @var string
     */
    public static $title = 'name';

    /**
     * @var array
     */
    public static $search = [
        'id',  'name',
    ];

    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            NovaImage::make(__('Image'), 'path')
                ->disk('public')
                ->path('review-images')
                ->prunable()
                ->creationRules('required', 'image', 'max:2048')
                ->updateRules('nullable', 'image', 'max:2048'),

            Select::make(__('Status'), 'status')
                ->options(ContentStatus::options())
                ->sortable()
                ->displayUsingLabels()
                ->rules('required', 'in:' . implode(',', ContentStatus::values())),

            Number::make(__('Used Count'), 'used_count')
                ->sortable()
                ->readonly(),
        ];
    }

    public static function label(): string
    {
        return __('Images');
    }

    public static function singularLabel(): string
    {
        return __('Image');
    }

}
