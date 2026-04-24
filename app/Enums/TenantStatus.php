<?php

namespace App\Enums;

enum TenantStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Inactive = 'inactive';

    public function allowsLogin(): bool
    {
        return $this === self::Approved;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Suspended, self::Inactive => 'secondary',
        };
    }

    public function loginBlockedMessage(): string
    {
        return match ($this) {
            self::Pending => 'Your shop is still waiting for super admin approval.',
            self::Rejected => 'Your shop registration was rejected. Please contact support for next steps.',
            self::Suspended => 'Your shop has been suspended. Please contact support.',
            self::Inactive => 'Your shop is inactive. Please contact support.',
            self::Approved => 'Your shop is active.',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
