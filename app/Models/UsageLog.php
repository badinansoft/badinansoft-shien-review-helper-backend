<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Nova\Actions\Actionable;

class UsageLog extends Model
{
    use Actionable;

    /**
     * @var array
     */
    protected $fillable = [
        'license_id', 'url', 'timestamp',
        'reviews_filled', 'images_attached', 'user_agent', 'ip_address'
    ];


    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
            'reviews_filled' => 'integer',
            'images_attached' => 'integer',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
