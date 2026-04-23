<?php

namespace App\Models;

use App\Helpers\FileUploadManager;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'disk',
        'path',
        'file_name',
        'original_name',
        'extension',
        'mime_type',
        'size',
        'collection',
        'sort_order',
        'is_primary',
        'uploaded_by',
    ];

    protected $appends = [
        'url',
    ];

    protected function casts(): array
    {
        return [
            'imageable_id' => 'integer',
            'size' => 'integer',
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
            'uploaded_by' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (Image $image): void {
            DB::afterCommit(function () use ($image): void {
                FileUploadManager::deleteFile($image->path, $image->disk ?: 'public');
            });
        });
    }

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        $disk = $this->disk ?: 'public';

        if (! Storage::disk($disk)->exists($this->path)) {
            return null;
        }

        return Storage::disk($disk)->url($this->path);
    }
}
