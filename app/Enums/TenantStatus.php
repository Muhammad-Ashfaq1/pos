<?php

namespace App\Enums;

enum TenantStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
