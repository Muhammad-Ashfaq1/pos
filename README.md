# POS

Multi-tenant Point of Sale + auto-service shop platform built on Laravel 13. Each tenant (shop) gets isolated catalog, customers, staff, roles, and a POS panel; a super-admin tier oversees tenant onboarding and lifecycle.

For an architectural overview, request flow, RBAC matrix, module breakdown, and full schema reference, see [docs/README.md](docs/README.md).

## Stack

- PHP 8.3+, Laravel 13
- MySQL/MariaDB (default) — works with Postgres
- `spatie/laravel-permission` ^7.3 (teams enabled, scoped per tenant)
- `stancl/tenancy` ^3.10 (single-database tenancy via `tenant_id` column scoping)
- Blade + Tailwind 4 + Vite 7 (Vuexy admin template)
- Node 20+ for the asset pipeline

## Prerequisites

- PHP 8.3 with extensions: `mbstring`, `pdo_mysql` (or `pdo_pgsql`), `bcmath`, `intl`, `gd`, `xml`, `curl`, `zip`
- Composer 2.x
- Node.js 20+ and npm
- MySQL 8 / MariaDB 10.6+ (or Postgres 14+)

## Local setup

### 1. Clone and install dependencies

```bash
git clone <repo-url> pos
cd pos
composer install
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set the database connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos
DB_USERNAME=root
DB_PASSWORD=
```

Create the database:

```bash
mysql -uroot -e "CREATE DATABASE pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

The remaining variables in [.env.example](.env.example) cover seeded super admin / demo shop credentials and mailer settings — defaults work for local development. Mail driver is `log` by default, so verification and notification emails are written to `storage/logs/laravel.log` instead of being sent.

### 3. Migrate and seed

```bash
php artisan migrate --seed
```

This runs every migration in order and then [`DatabaseSeeder`](database/seeders/DatabaseSeeder.php), which creates roles, permissions, the super admin, a demo approved shop with employees, and a demo catalog.

### 4. Run the dev stack

A single composer script starts the PHP server, queue worker, log tailer, and Vite dev server in parallel:

```bash
composer run dev
```

Open <http://localhost:8000>.

If you prefer manual control, run each piece in its own terminal:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
php artisan pail
npm run dev
```

### 5. Storage symlink (for image uploads)

```bash
php artisan storage:link
```

## Default credentials

Created by the seeders:

| Role | Email | Password |
|------|-------|----------|
| Super admin | `superadmin@pos.com` | `password` |
| Demo tenant admin | `owner@rapidlube.test` | `password123` |
| Demo employees | seeded per shop | `password123` |

Super admin lands on `/admin/dashboard`; tenant admin lands on `/tenant/dashboard`; employees land on `/employee/dashboard`. The full sign-up → email-verify → super-admin-approval flow is documented in [docs/auth-and-onboarding.md](docs/auth-and-onboarding.md).

## Common commands

```bash
# Run the full dev stack (PHP, queue, logs, Vite)
composer run dev

# Reset DB and re-seed (DESTRUCTIVE — drops all data)
php artisan migrate:fresh --seed

# Re-seed the permissions table after editing PermissionSeeder
php artisan permissions:sync

# Tests
php artisan test

# Code style (Laravel Pint)
./vendor/bin/pint

# Build production frontend
npm run build
```

## Where things live

```
app/Http/Controllers/Auth/         signup, login, verify, password reset
app/Http/Controllers/Admin/        super-admin tenant management
app/Http/Controllers/Tenant/       per-shop catalog, customers, settings, roles
app/Http/Controllers/Employee/     employee POS panel
app/Models/                        Eloquent models (BelongsToTenant trait scopes by tenant_id)
app/Repositories/                  one repo per resource, interfaces bound in AppServiceProvider
app/Actions/                       multi-step business operations (signup, status transitions)
routes/                            web.php, auth.php, admin.php, tenant.php, employee.php
database/migrations/               chronological schema
database/seeders/                  DatabaseSeeder runs the rest in order
resources/views/                   Blade templates by audience
config/permission.php              Spatie teams config (tenant-scoped roles)
config/tenancy.php                 stancl/tenancy config (single-database mode)
```

## Documentation

Project documentation lives in [docs/](docs/):

- [docs/README.md](docs/README.md) — index and reading order
- [docs/architecture.md](docs/architecture.md) — tech stack, request lifecycle, multi-tenancy mechanism
- [docs/auth-and-onboarding.md](docs/auth-and-onboarding.md) — full signup, approval, login, password reset, impersonation
- [docs/rbac.md](docs/rbac.md) — roles, permissions, team scoping, role × permission matrix
- [docs/modules.md](docs/modules.md) — every module: catalog, CRM, discounts, images, settings, POS panel
- [docs/database.md](docs/database.md) — migrations and full schema reference

## Troubleshooting

- **`SQLSTATE[HY000] [2002] Connection refused`** — MySQL isn't running, or the host/port in `.env` is wrong. Check `mysql.server start` (macOS) or `service mysql status` (Linux).
- **Permission denied on `storage/` or `bootstrap/cache/`** — `chmod -R ug+rw storage bootstrap/cache`.
- **`Vite manifest not found`** — run `npm run dev` (development) or `npm run build` (production).
- **Login blocked with "shop is still waiting for super admin approval"** — log in as super admin, go to `/admin/shops`, and approve the tenant.
- **Permission middleware rejects a route after adding a new permission** — run `php artisan permissions:sync`, then re-attach permissions to roles via the tenant's roles & permissions UI.

## License

MIT.
