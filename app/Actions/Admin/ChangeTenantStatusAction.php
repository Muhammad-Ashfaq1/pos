<?php

namespace App\Actions\Admin;

use App\Enums\TenantStatus;
use App\Exceptions\InvalidTenantStatusTransitionException;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\TenantStatusChangedNotification;
use Illuminate\Support\Facades\DB;

class ChangeTenantStatusAction
{
    private const TRANSITIONS = [
        'approve' => [
            'status' => TenantStatus::Approved,
            'is_active' => true,
            'message' => 'Shop approved successfully',
            'status_text' => 'Approved',
            'badge_class' => 'bg-success',
        ],
        'reject' => [
            'status' => TenantStatus::Rejected,
            'is_active' => false,
            'message' => 'Shop rejected successfully',
            'status_text' => 'Rejected',
            'badge_class' => 'bg-danger',
        ],
        'suspend' => [
            'status' => TenantStatus::Suspended,
            'is_active' => false,
            'message' => 'Shop suspended successfully',
            'status_text' => 'Suspended',
            'badge_class' => 'bg-secondary',
        ],
        'reactivate' => [
            'status' => TenantStatus::Approved,
            'is_active' => true,
            'message' => 'Shop reactivated successfully',
            'status_text' => 'Approved',
            'badge_class' => 'bg-success',
        ],
    ];

    public function execute(Tenant $tenant, string $action, ?string $reason = null): array
    {
        $transition = self::TRANSITIONS[$action] ?? null;

        if (! $transition) {
            throw InvalidTenantStatusTransitionException::unsupportedAction($action);
        }

        DB::transaction(function () use ($tenant, $transition, $reason): void {
            $tenant->forceFill([
                'status' => $transition['status']->value,
                'approved_at' => $transition['status'] === TenantStatus::Approved ? now() : $tenant->approved_at,
                'rejected_reason' => $transition['status'] === TenantStatus::Rejected ? $reason : null,
                'approved_by' => auth()->id(),
                'onboarding_status' => $transition['status'] === TenantStatus::Approved ? 'in_progress' : $tenant->onboarding_status,
            ])->save();

            User::query()
                ->where('tenant_id', $tenant->id)
                ->where('role', User::TENANT_ADMIN)
                ->update(['is_active' => $transition['is_active']]);
        });

        if ($tenant->adminUser) {
            $tenant->adminUser->notify(new TenantStatusChangedNotification(
                status: $transition['status']->value,
                shopName: $tenant->shop_name,
                reason: $reason
            ));
        }

        return [
            'success' => true,
            'message' => $transition['message'],
            'status_text' => $transition['status_text'],
            'badge_class' => $transition['badge_class'],
        ];
    }
}
