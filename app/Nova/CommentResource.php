<?php

namespace App\Nova;

use App\Enums\ContentStatus;
use App\Models\Comment;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CommentResource extends Resource
{

    public static string $model = Comment::class;

    /**
     * @var string
     */
    public static $title = 'text';

    /**
     * @var array
     */
    public static $search = [
        'id', 'text',
    ];

    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            Text::make(__('Text'), 'text')
                ->rules('required', 'string'),

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

    public function actions(NovaRequest $request): array
    {
        return [
            (new Actions\ImportComments)
                ->standalone()
                ->confirmButtonText('Import Comments')
                ->confirmText('Are you sure you want to import these comments?')
                ->showOnTableRow(false)
        ];
    }

    public static function label(): string
    {
        return __('Comments');
    }

    public static function singularLabel(): string
    {
        return __('Comment');
    }
}
