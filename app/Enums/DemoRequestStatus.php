<?php

namespace App\Enums;

enum DemoRequestStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Scheduled = 'scheduled';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => __('admin.demo_requests.status.new'),
            self::Contacted => __('admin.demo_requests.status.contacted'),
            self::Scheduled => __('admin.demo_requests.status.scheduled'),
            self::Closed => __('admin.demo_requests.status.closed'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Contacted => 'info',
            self::Scheduled => 'primary',
            self::Closed => 'success',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
