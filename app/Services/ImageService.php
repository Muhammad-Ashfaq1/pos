<?php

namespace App\Services;

use App\Helpers\FileUploadManager;
use App\Models\Image;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImageService
{
    public function syncForModel(
        Model $model,
        array $uploadedImages = [],
        array $removedImageIds = [],
        ?string $primaryImageRef = null,
        ?Authenticatable $user = null,
        string $collection = 'gallery',
        string $disk = 'public',
    ): Collection {
        $createdFiles = [];

        try {
            return DB::transaction(function () use (
                $model,
                $uploadedImages,
                $removedImageIds,
                $primaryImageRef,
                $user,
                $collection,
                $disk,
                &$createdFiles
            ): Collection {
                $images = $model->images()->get()->keyBy('id');

                if ($removedImageIds !== []) {
                    $images
                        ->only($removedImageIds)
                        ->each(function (Image $image): void {
                            $image->delete();
                        });
                }

                $model->unsetRelation('images');
                $remaining = $model->images()->get();
                $nextSortOrder = (int) ($remaining->max('sort_order') ?? 0) + 1;

                foreach ($uploadedImages as $index => $file) {
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }

                    $image = $this->storeForModel(
                        model: $model,
                        file: $file,
                        collection: $collection,
                        sortOrder: $nextSortOrder + $index,
                        isPrimary: false,
                        user: $user,
                        disk: $disk,
                    );

                    $createdFiles[] = [
                        'path' => $image->path,
                        'disk' => $image->disk,
                    ];
                }

                $model->unsetRelation('images');
                $model->load('images');
                $allImages = $model->images;
                $primaryImage = $this->resolvePrimaryImageReference($allImages, $primaryImageRef, $remaining->count());

                if (! $primaryImage && $allImages->isNotEmpty()) {
                    $primaryImage = $allImages
                        ->firstWhere('is_primary', true)
                        ?: $allImages->sortBy(['sort_order', 'id'])->first();
                }

                $this->setPrimaryImage($model, $primaryImage);

                return $model->fresh('images')->images;
            });
        } catch (Throwable $exception) {
            foreach ($createdFiles as $file) {
                FileUploadManager::deleteFile($file['path'] ?? null, $file['disk'] ?? 'public');
            }

            throw $exception;
        }
    }

    public function storeForModel(
        Model $model,
        UploadedFile $file,
        string $collection = 'gallery',
        ?int $sortOrder = null,
        bool $isPrimary = false,
        ?Authenticatable $user = null,
        string $disk = 'public',
    ): Image {
        $upload = FileUploadManager::uploadFile($file, $this->buildPathPrefix($model), $disk);

        try {
            return $model->images()->create([
                'disk' => $disk,
                'path' => $upload['path'],
                'file_name' => $upload['doc_name'] ?? null,
                'original_name' => $upload['original_doc_name'] ?? $file->getClientOriginalName(),
                'extension' => $upload['doc_type'] ?? $file->getClientOriginalExtension(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'collection' => $collection,
                'sort_order' => $sortOrder ?? 1,
                'is_primary' => $isPrimary,
                'uploaded_by' => $user?->getAuthIdentifier(),
            ]);
        } catch (Throwable $exception) {
            FileUploadManager::deleteFile($upload['path'] ?? null, $disk);

            throw $exception;
        }
    }

    public function setPrimaryImage(Model $model, ?Image $primaryImage): void
    {
        $model->images()->update(['is_primary' => false]);

        if ($primaryImage) {
            $primaryImage->forceFill(['is_primary' => true])->save();
        }
    }

    public function delete(Image $image): void
    {
        $image->delete();
    }

    public function transform(Image $image): array
    {
        return [
            'id' => $image->id,
            'url' => $image->url,
            'path' => $image->path,
            'disk' => $image->disk,
            'file_name' => $image->file_name,
            'original_name' => $image->original_name,
            'mime_type' => $image->mime_type,
            'extension' => $image->extension,
            'size' => $image->size,
            'size_label' => $this->formatBytes($image->size),
            'sort_order' => $image->sort_order,
            'is_primary' => $image->is_primary,
            'collection' => $image->collection,
        ];
    }

    public function transformMany(iterable $images): array
    {
        $items = [];

        foreach ($images as $image) {
            if ($image instanceof Image) {
                $items[] = $this->transform($image);
            }
        }

        return $items;
    }

    private function resolvePrimaryImageReference(Collection $images, ?string $primaryImageRef, int $existingCountBeforeUploads): ?Image
    {
        if (! $primaryImageRef) {
            return null;
        }

        if (str_starts_with($primaryImageRef, 'existing:')) {
            $id = (int) substr($primaryImageRef, 9);

            return $images->firstWhere('id', $id);
        }

        if (str_starts_with($primaryImageRef, 'new:')) {
            $newIndex = (int) substr($primaryImageRef, 4);

            return $images->values()->get($existingCountBeforeUploads + $newIndex);
        }

        return null;
    }

    private function buildPathPrefix(Model $model): string
    {
        $tenantId = data_get($model, 'tenant_id');
        $directory = method_exists($model, 'getTable') ? $model->getTable() : 'models';

        return sprintf('tenants/%s/%s/%s/images/', $tenantId, $directory, $model->getKey());
    }

    private function formatBytes(?int $bytes): string
    {
        $bytes = (int) $bytes;

        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / (1024 * 1024), 2).' MB';
    }
}
