<?php

namespace App\Models;

use App\Enums\LicenseStatus;
use App\Observers\LicenseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Nova\Actions\Actionable;

#[ObservedBy(LicenseObserver::class)]
class License extends Model
{
    use Actionable;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'key', 'status', 'device_id',
        'expires_at', 'tokens_used', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'tokens_used' => 'integer',
            'status' => LicenseStatus::class,
        ];
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class);
    }


    public static function generateKey(): string
    {

        // Generate a more secure key with 5 segments of 8 characters each
        $segments = [];
        for ($i = 0; $i < 5; $i++) {
            // Use random_bytes for cryptographically secure randomness
            $bytes = random_bytes(4);
            // Convert to hexadecimal and uppercase
            $segments[] = strtoupper(bin2hex($bytes));
        }

        // Join segments with hyphens
        $key = implode('-', $segments);

        // Check if key already exists
        while (self::where('key', $key)->exists()) {
            // Regenerate if there's a collision
            $segments = [];
            for ($i = 0; $i < 5; $i++) {
                $bytes = random_bytes(4);
                $segments[] = strtoupper(bin2hex($bytes));
            }
            $key = implode('-', $segments);
        }

        return $key;
    }
}
