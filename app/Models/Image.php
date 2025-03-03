<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Actionable;

class Image extends Model
{
    use Actionable;

    /**
     * @var array
     */
    protected $fillable = [
        'path', 'status', 'used_count'
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

    public function getImageDataUrl(): ?string
    {
        $path = $this->path;
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        $data = Storage::disk('public')->get($path);
        $base64 = base64_encode($data);
        $mimType = Storage::disk('public')->mimeType($path);

        return "data:$mimType;base64,$base64";
    }

    public function image(): Attribute
    {
        return new Attribute(
            get: fn() => Storage::disk('public')->url($this->path),
        );
    }

    public function imageName(): Attribute
    {
        return new Attribute(
            get: fn() => pathinfo($this->path, PATHINFO_FILENAME),
        );
    }
}
