<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;


    //  Role Constants
    public const SUPER_ADMIN = 'super_admin';
    public const TENANT_ADMIN = 'tenant_admin';
    public const MANAGER = 'manager';
    public const CASHIER = 'cashier';
    public const TECHNICIAN = 'technician';
    public const INVENTORY_CLERK = 'inventory_clerk';
    public const CUSTOMER = 'customer';
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
               'locked_until' => 'datetime'
        ];
    }


    //  Helper Functions

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

}
