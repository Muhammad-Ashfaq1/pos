<?php

namespace App\Enums;

enum PlanDuration: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case SemiAnnual = 'semi_annual';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::SemiAnnual => 'Semi-Annual',
            self::Yearly => 'Yearly',
        };
    }

    public function days(): int
    {
        return match ($this) {
            self::Weekly => 7,
            self::Monthly => 30,
            self::Quarterly => 90,
            self::SemiAnnual => 180,
            self::Yearly => 365,
        };
    }

    public function billingCycle(): string
    {
        return match ($this) {
            self::Weekly => 'weekly',
            self::Yearly => 'yearly',
            default => 'monthly',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function tryFromDays(int $days): self
    {
        foreach (self::cases() as $case) {
            if ($case->days() === $days) {
                return $case;
            }
        }

        return self::Monthly;
    }

    public static function options(): array
    {
        return array_map(
            fn (self $duration) => [
                'value' => $duration->value,
                'label' => $duration->label(),
                'days' => $duration->days(),
            ],
            self::cases()
        );
    }
}
