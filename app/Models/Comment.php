<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Comment extends Model
{
    use Actionable;

    /**
     * @var array
     */
    protected $fillable = [
        'text', 'status', 'used_count'
    ];

    protected function casts(): array
    {
        return [
            'used_count' => 'integer',
            'status' => ContentStatus::class,
        ];
    }

    /**
     * @var array
     */
    protected $attributes = [
        'used_count' => 0,
        'status' => 'active',
    ];
}
