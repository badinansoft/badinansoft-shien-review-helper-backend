<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\License;

class GenerateLicenseKey extends Action
{
    use InteractsWithQueue, Queueable;

    public function handle(ActionFields $fields, Collection $models): mixed
    {
        foreach ($models as $model) {
            $model->key = License::generateKey();
            $model->save();
        }

        return Action::message('License key(s) generated successfully!');
    }


    public function fields(NovaRequest $request): array
    {
        return [

        ];
    }
}
