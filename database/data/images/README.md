# Seed data images

Static images used by the database seeders. These are committed to the repo so a
fresh `php artisan migrate:fresh --seed` reproduces a catalog complete with imagery,
without any network access at seed time.

## Layout

```
database/data/images/
└── products/            one image per demo product (filename referenced by TenantCatalogSeeder)
```

## How they are used

[`TenantCatalogSeeder`](../../seeders/TenantCatalogSeeder.php) maps each demo product to a
file in `products/` via the `image` key in its `PRODUCTS_BY_CATEGORY` definition. For every
tenant, `seedProductImage()`:

1. Reads the local file from `database/data/images/products/<file>`.
2. Pushes it onto the `public` storage disk under
   `tenants/{tenantId}/products/{productId}/images/` (via `FileUploadManager`).
3. Records an `images` row (polymorphic to the product, `is_primary = true`) holding the
   stored path, disk, mime, size, etc.

The step is idempotent — a product that already has an image is skipped — so re-running the
seeder will not duplicate files or rows.

To serve the stored files over HTTP, run `php artisan storage:link` once.

## Provenance

The product images are **generated locally** by [`generate.php`](generate.php) using PHP GD —
each is a clean studio-style illustration of the actual product category (oil bottle, filter,
brake disc, battery, tyre) with the brand/name rendered on it. This keeps them deterministic,
always category-correct, and free of any licensing or network dependency.

Regenerate (e.g. after tweaking colours, labels, or adding a product) with:

```bash
php database/data/images/generate.php
```

They are representative demo graphics, not official product photos. To use your own real
photos instead, just drop JPGs into `products/` using the same filenames — the seeder picks
up whatever is on disk.
