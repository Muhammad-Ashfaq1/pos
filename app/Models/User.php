<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // 🔹 Role Constants
    public const SUPER_ADMIN = 'super_admin';
    public const TENANT_ADMIN = 'tenant_admin';
    public const MANAGER = 'manager';
    public const CASHIER = 'cashier';
    public const TECHNICIAN = 'technician';
    public const INVENTORY_CLERK = 'inventory_clerk';
    public const CUSTOMER = 'customer';

    // 🔹 Helper Functions

    public function isSuperAdmin()
    {
        return $this->hasRole(self::SUPER_ADMIN);
    }

    public function isTenantAdmin()
    {
        return $this->hasRole(self::TENANT_ADMIN);
    }

    public function isManager()
    {
        return $this->hasRole(self::MANAGER);
    }

    public function isCashier()
    {
        return $this->hasRole(self::CASHIER);
    }

    public function isTechnician()
    {
        return $this->hasRole(self::TECHNICIAN);
    }

    public function isInventoryClerk()
    {
        return $this->hasRole(self::INVENTORY_CLERK);
    }

    public function isCustomer()
    {
        return $this->hasRole(self::CUSTOMER);
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
