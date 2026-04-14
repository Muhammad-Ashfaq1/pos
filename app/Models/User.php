<?php

namespace App\Models;

use App\Notifications\Auth\QueuedVerifyEmail;
use App\Support\Permissions\PermissionTeamScope;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use MustVerifyEmailTrait;

    public const SUPER_ADMIN = 'super_admin';
    public const TENANT_ADMIN = 'tenant_admin';
    public const MANAGER = 'manager';
    public const CASHIER = 'cashier';
    public const TECHNICIAN = 'technician';
    public const INVENTORY_CLERK = 'inventory_clerk';
    public const CUSTOMER = 'customer';

    protected string $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role',
        'phone',
        'failed_attempts',
        'locked_until',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::SUPER_ADMIN || $this->hasRole(self::SUPER_ADMIN);
    }

    public function isTenantAdmin(): bool
    {
        return $this->role === self::TENANT_ADMIN || $this->hasRole(self::TENANT_ADMIN);
    }

    public function isManager(): bool
    {
        return $this->hasRole(self::MANAGER);
    }

    public function isCashier(): bool
    {
        return $this->hasRole(self::CASHIER);
    }

    public function isTechnician(): bool
    {
        return $this->hasRole(self::TECHNICIAN);
    }

    public function isInventoryClerk(): bool
    {
        return $this->hasRole(self::INVENTORY_CLERK);
    }

    public function isCustomer(): bool
    {
        return $this->hasRole(self::CUSTOMER);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail());
    }

    public function assignPrimaryRole(string $role, ?int $tenantId = null): void
    {
        PermissionTeamScope::for($tenantId ?? 0, function () use ($role): void {
            $this->syncRoles([$role]);
        });

        $this->forceFill(['role' => $role])->saveQuietly();
    }

    public function primaryRoleName(): ?string
    {
        return $this->role ?: $this->getRoleNames()->first();
    }
}
