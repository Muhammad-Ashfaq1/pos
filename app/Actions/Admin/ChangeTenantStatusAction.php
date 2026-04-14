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
            'message' => 'Shop approved successfully.',
        ],
        'reject' => [
            'status' => TenantStatus::Rejected,
            'is_active' => false,
            'message' => 'Shop rejected successfully.',
        ],
        'suspend' => [
            'status' => TenantStatus::Suspended,
            'is_active' => false,
            'message' => 'Shop suspended successfully.',
        ],
        'reactivate' => [
            'status' => TenantStatus::Approved,
            'is_active' => true,
            'message' => 'Shop reactivated successfully.',
        ],
    ];

    public function execute(Tenant $tenant, string $action, ?string $reason = null): array
    {
        $transition = self::TRANSITIONS[$action] ?? null;

        if (! $transition) {
            throw InvalidTenantStatusTransitionException::unsupportedAction($action);
        }

        DB::transaction(function () use ($tenant, $transition, $reason): void {
            $status = $transition['status'];

            $tenant->forceFill([
                'status' => $status->value,
                'approved_at' => $status === TenantStatus::Approved ? now() : $tenant->approved_at,
                'approved_by' => auth()->id(),
                'rejected_at' => $status === TenantStatus::Rejected ? now() : null,
                'suspended_at' => $status === TenantStatus::Suspended ? now() : null,
                'rejected_reason' => $status === TenantStatus::Rejected ? $reason : null,
                'onboarding_status' => $status === TenantStatus::Approved ? 'in_progress' : $tenant->onboarding_status,
            ])->save();

            User::query()
                ->where('tenant_id', $tenant->id)
                ->where('role', User::TENANT_ADMIN)
                ->update(['is_active' => $transition['is_active']]);
        });

        $tenant->loadMissing('adminUser');

        if ($tenant->adminUser) {
            $tenant->adminUser->notify(new TenantStatusChangedNotification(
                status: $transition['status']->value,
                shopName: $tenant->display_name,
                reason: $reason
            ));
        }

        return [
            'success' => true,
            'message' => $transition['message'],
            'status_text' => ucfirst($transition['status']->value),
            'badge_class' => 'bg-'.$transition['status']->badgeClass(),
        ];
    }
}
