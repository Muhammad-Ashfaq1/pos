<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Images\UploadImageRequest;
use App\Models\Image;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService
    ) {
    }

    public function upload(UploadImageRequest $request): JsonResponse
    {
        $owner = $this->resolveImageableOwner(
            $request->imageableClass(),
            (int) $request->integer('imageable_id')
        );

        $this->authorizeOwnerUpdate($owner);

        $images = [];
        $startingSortOrder = (int) ($owner->images()->max('sort_order') ?? 0) + 1;

        foreach ($request->file('images', []) as $index => $file) {
            $images[] = $this->imageService->transform(
                $this->imageService->storeForModel(
                    model: $owner,
                    file: $file,
                    sortOrder: $startingSortOrder + $index,
                    isPrimary: ! $owner->images()->exists() && $index === 0,
                    user: $request->user(),
                )
            );
        }

        return response()->json([
            'message' => 'Images uploaded successfully.',
            'data' => $images,
        ]);
    }

    public function destroy(Image $image, Request $request): JsonResponse
    {
        $owner = $image->imageable;
        abort_unless($owner instanceof Model, 404);

        $this->authorizeOwnerUpdate($owner);
        $wasPrimary = $image->is_primary;

        $this->imageService->delete($image);
        $owner = $owner->fresh('images');

        if ($wasPrimary && $owner?->images?->isNotEmpty()) {
            $this->imageService->setPrimaryImage($owner, $owner->images->first());
        }

        return response()->json([
            'message' => 'Image removed successfully.',
        ]);
    }

    public function setPrimary(Image $image, Request $request): JsonResponse
    {
        $owner = $image->imageable;
        abort_unless($owner instanceof Model, 404);

        $this->authorizeOwnerUpdate($owner);
        $this->imageService->setPrimaryImage($owner, $image);

        return response()->json([
            'message' => 'Primary image updated successfully.',
            'data' => $this->imageService->transform($image->fresh()),
        ]);
    }

    private function resolveImageableOwner(string $class, int $id): Model
    {
        abort_unless($class === Product::class, 404);

        return $class::query()->findOrFail($id);
    }

    private function authorizeOwnerUpdate(Model $owner): void
    {
        if ($owner instanceof Product) {
            $this->authorize('update', $owner);

            return;
        }

        abort(404);
    }
}
