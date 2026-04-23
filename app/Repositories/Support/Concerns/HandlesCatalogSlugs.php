<?php

namespace App\Repositories\Support\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HandlesCatalogSlugs
{
    protected function makeUniqueSlug(
        string $modelClass,
        string $value,
        int|string|null $ignoreId = null,
        string $fallbackPrefix = 'item'
    ): string {
        /** @var Model $model */
        $model = new $modelClass();
        $baseSlug = Str::slug($value);
        $baseSlug = $baseSlug !== '' ? $baseSlug : $fallbackPrefix;
        $slug = $baseSlug;
        $suffix = 2;

        while (
            $modelClass::query()
                ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
                ->where($model->qualifyColumn('slug'), $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
