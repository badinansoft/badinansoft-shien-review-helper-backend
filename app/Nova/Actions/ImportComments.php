<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Comment;
use App\Enums\ContentStatus;

class ImportComments extends Action
{
    use InteractsWithQueue, Queueable;


    public function handle(ActionFields $fields, Collection $models): ActionResponse|Action
    {
        try {
            // Parse the JSON input
            $commentsJson = $fields->comments_json;
            $comments = json_decode($commentsJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return Action::danger('Invalid JSON format. Please check your input.');
            }

            if (!is_array($comments)) {
                return Action::danger('The JSON must be an array of strings.');
            }

            $createdCount = 0;

            // Create each comment
            foreach ($comments as $text) {
                if (is_string($text) && !empty($text)) {
                    Comment::create([
                        'text' => $text,
                        'status' => ContentStatus::ACTIVE,
                    ]);
                    $createdCount++;
                }
            }

            return Action::message("Successfully created {$createdCount} comments!");

        } catch (\Exception $e) {
            return Action::danger('Error importing comments: ' . $e->getMessage());
        }
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Textarea::make('Comments JSON', 'comments_json')
                ->help('Paste a JSON array of comment strings, e.g., ["Comment 1", "Comment 2", ...]')
                ->rows(10)
                ->rules('required')
        ];
    }
}
