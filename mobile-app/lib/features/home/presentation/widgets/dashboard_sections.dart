import 'package:flutter/material.dart';
import 'package:pos_mobile/app/theme/app_radius.dart';
import 'package:pos_mobile/app/theme/app_spacing.dart';
import 'package:pos_mobile/app/theme/app_text_styles.dart';
import 'package:pos_mobile/core/widgets/app_glass.dart';
import 'package:pos_mobile/features/home/models/dashboard.dart';

class DashboardCreditHero extends StatelessWidget {
  const DashboardCreditHero({required this.credit, this.shopName, super.key});

  final CreditInfo credit;
  final String? shopName;

  @override
  Widget build(BuildContext context) {
    final tone =
        credit.canRedeem ? AppGlassTone.success : AppGlassTone.primary;
    final progress = credit.unlockProgress.clamp(0.0, 1.0);

    return AppGlassCard(
      tone: tone,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              AppGlassIconTile(
                icon: Icons.account_balance_wallet_outlined,
                tone: tone,
              ),
              const SizedBox(width: AppSpacing.sm),
              Expanded(
                child: Text(
                  'STORE CREDIT',
                  style: AppTextStyles.statLabel,
                ),
              ),
              AppGlassPill(
                label: credit.canRedeem ? 'Ready to use' : 'Locked',
                tone: tone,
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          Text(credit.balanceLabel, style: AppTextStyles.heroValue),
          if (shopName != null && shopName!.isNotEmpty) ...[
            const SizedBox(height: AppSpacing.xs),
            Text('at $shopName', style: AppTextStyles.bodySecondary),
          ],
          const SizedBox(height: AppSpacing.md),
          ClipRRect(
            borderRadius: AppRadius.fullAll,
            child: LinearProgressIndicator(
              value: progress,
              minHeight: 8,
              backgroundColor: tone.color.withValues(alpha: 0.15),
              color: tone.color,
            ),
          ),
          const SizedBox(height: AppSpacing.sm),
          Text(
            credit.canRedeem
                ? 'Usable at checkout against your next bill.'
                : '${credit.remainingToUnlockLabel} more to unlock '
                    '(${credit.minRedeemBalanceLabel} minimum).',
            style: AppTextStyles.bodySmall,
          ),
        ],
      ),
    );
  }
}

class DashboardStatsGrid extends StatelessWidget {
  const DashboardStatsGrid({required this.stats, super.key});

  final DashboardStats stats;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: AppGlassStat(
                tone: AppGlassTone.info,
                icon: Icons.event_available_rounded,
                label: 'Visits',
                value: '${stats.visits}',
                note: 'Completed service visits',
              ),
            ),
            const SizedBox(width: AppSpacing.sm),
            Expanded(
              child: AppGlassStat(
                tone: AppGlassTone.warning,
                icon: Icons.receipt_long_rounded,
                label: 'Lifetime spend',
                value: stats.lifetimeValueLabel,
                note: 'Across all paid visits',
              ),
            ),
          ],
        ),
        const SizedBox(height: AppSpacing.sm),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: AppGlassStat(
                tone: AppGlassTone.primary,
                icon: Icons.payments_outlined,
                label: 'Avg visit',
                value: stats.averageSpendLabel,
                note: 'Lifetime ÷ visits',
              ),
            ),
            const SizedBox(width: AppSpacing.sm),
            Expanded(
              child: AppGlassStat(
                tone: AppGlassTone.secondary,
                icon: Icons.schedule_rounded,
                label: 'Last visit',
                value: stats.lastVisitAtLabel?.isNotEmpty == true
                    ? stats.lastVisitAtLabel!
                    : '—',
                note: stats.openOrdersCount > 0
                    ? '${stats.openOrdersCount} open order(s)'
                    : '${stats.paidOrdersCount} paid',
              ),
            ),
          ],
        ),
        const SizedBox(height: AppSpacing.sm),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: AppGlassStat(
                tone: AppGlassTone.success,
                icon: Icons.stars_rounded,
                label: 'Loyalty pts',
                value: '${stats.loyaltyPoints}',
                note: 'Reward points on file',
              ),
            ),
            const SizedBox(width: AppSpacing.sm),
            Expanded(
              child: AppGlassStat(
                tone: AppGlassTone.info,
                icon: Icons.directions_car_filled_rounded,
                label: 'Vehicles',
                value: '${stats.vehiclesCount}',
                note: 'On file at this shop',
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class DashboardSectionHeader extends StatelessWidget {
  const DashboardSectionHeader({
    required this.title,
    this.subtitle,
    super.key,
  });

  final String title;
  final String? subtitle;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: AppSpacing.sm),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: AppTextStyles.h3),
          if (subtitle != null) ...[
            const SizedBox(height: 2),
            Text(subtitle!, style: AppTextStyles.bodySmall),
          ],
        ],
      ),
    );
  }
}

class DashboardVehiclesPanel extends StatelessWidget {
  const DashboardVehiclesPanel({required this.vehicles, super.key});

  final List<DashboardVehicle> vehicles;

  @override
  Widget build(BuildContext context) {
    return AppGlassCard(
      tone: AppGlassTone.info,
      padding: const EdgeInsets.all(AppSpacing.md),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (vehicles.isEmpty)
            Text('No vehicles on file yet.', style: AppTextStyles.bodySecondary)
          else
            for (var i = 0; i < vehicles.length; i++) ...[
              if (i > 0) const SizedBox(height: AppSpacing.sm),
              _VehicleRow(vehicle: vehicles[i]),
            ],
        ],
      ),
    );
  }
}

class _VehicleRow extends StatelessWidget {
  const _VehicleRow({required this.vehicle});

  final DashboardVehicle vehicle;

  @override
  Widget build(BuildContext context) {
    final details = [
      if (vehicle.plateNumber != null && vehicle.plateNumber!.isNotEmpty)
        vehicle.plateNumber!,
      if (vehicle.color != null && vehicle.color!.isNotEmpty) vehicle.color!,
      if (vehicle.odometer != null && vehicle.odometer!.isNotEmpty)
        '${vehicle.odometer} km',
    ].join(' · ');

    return Row(
      children: [
        const AppGlassIconTile(
          icon: Icons.directions_car_filled_rounded,
          tone: AppGlassTone.info,
          size: 36,
        ),
        const SizedBox(width: AppSpacing.sm),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                vehicle.label.isEmpty ? 'Vehicle' : vehicle.label,
                style: AppTextStyles.label,
              ),
              if (details.isNotEmpty)
                Text(details, style: AppTextStyles.bodySmall),
            ],
          ),
        ),
        if (vehicle.isDefault)
          const AppGlassPill(label: 'Default', tone: AppGlassTone.info),
      ],
    );
  }
}

class DashboardCreditsPanel extends StatelessWidget {
  const DashboardCreditsPanel({required this.entries, super.key});

  final List<CreditActivity> entries;

  @override
  Widget build(BuildContext context) {
    return AppGlassCard(
      tone: AppGlassTone.success,
      padding: const EdgeInsets.all(AppSpacing.md),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (entries.isEmpty)
            Text(
              'No credit activity yet.',
              style: AppTextStyles.bodySecondary,
            )
          else
            for (var i = 0; i < entries.length; i++) ...[
              if (i > 0) const Divider(height: 18),
              _CreditRow(entry: entries[i]),
            ],
        ],
      ),
    );
  }
}

class _CreditRow extends StatelessWidget {
  const _CreditRow({required this.entry});

  final CreditActivity entry;

  @override
  Widget build(BuildContext context) {
    final isCredit = entry.direction == 'credit';
    final tone = isCredit ? AppGlassTone.success : AppGlassTone.warning;

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        AppGlassIconTile(
          icon: isCredit
              ? Icons.south_west_rounded
              : Icons.north_east_rounded,
          tone: tone,
          size: 32,
        ),
        const SizedBox(width: AppSpacing.sm),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                entry.description?.isNotEmpty == true
                    ? entry.description!
                    : entry.type,
                style: AppTextStyles.label,
              ),
              Text(
                [
                  if (entry.orderNumber != null && entry.orderNumber!.isNotEmpty)
                    entry.orderNumber!,
                  if (entry.createdAtLabel != null) entry.createdAtLabel!,
                ].join(' · '),
                style: AppTextStyles.bodySmall,
              ),
            ],
          ),
        ),
        Text(
          entry.amountLabel,
          style: AppTextStyles.label.copyWith(color: tone.color),
        ),
      ],
    );
  }
}

class DashboardOrdersPanel extends StatelessWidget {
  const DashboardOrdersPanel({required this.orders, super.key});

  final List<RecentOrder> orders;

  @override
  Widget build(BuildContext context) {
    if (orders.isEmpty) {
      return AppGlassCard(
        tone: AppGlassTone.secondary,
        child: Text(
          'No service history yet.',
          style: AppTextStyles.bodySecondary,
        ),
      );
    }

    return Column(
      children: [
        for (final order in orders) ...[
          _OrderCard(order: order),
          const SizedBox(height: AppSpacing.sm),
        ],
      ],
    );
  }
}

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.order});

  final RecentOrder order;

  AppGlassTone get _tone {
    switch (order.statusClass) {
      case 'success':
        return AppGlassTone.success;
      case 'danger':
        return AppGlassTone.warning;
      default:
        return AppGlassTone.primary;
    }
  }

  @override
  Widget build(BuildContext context) {
    final vehicle = [
      if (order.vehicleLabel != null && order.vehicleLabel!.isNotEmpty)
        order.vehicleLabel!,
      if (order.plateNumber != null && order.plateNumber!.isNotEmpty)
        order.plateNumber!,
    ].join(' · ');

    return AppGlassCard(
      tone: _tone,
      padding: const EdgeInsets.all(AppSpacing.md),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              AppGlassIconTile(
                icon: Icons.receipt_long_rounded,
                tone: _tone,
                size: 36,
              ),
              const SizedBox(width: AppSpacing.sm),
              Expanded(
                child: Text(order.orderNumber, style: AppTextStyles.label),
              ),
              AppGlassPill(label: order.statusLabel, tone: _tone),
            ],
          ),
          const SizedBox(height: AppSpacing.sm),
          Row(
            children: [
              Expanded(
                child: Text(
                  [
                    if (order.createdAtLabel != null) order.createdAtLabel!,
                    '${order.itemsCount} item(s)',
                  ].join(' · '),
                  style: AppTextStyles.bodySmall,
                ),
              ),
              Text(order.totalAmountLabel, style: AppTextStyles.h3),
            ],
          ),
          if (vehicle.isNotEmpty) ...[
            const SizedBox(height: AppSpacing.xxs),
            Text(vehicle, style: AppTextStyles.bodySmall),
          ],
          if (order.serviceSummary != null &&
              order.serviceSummary!.isNotEmpty) ...[
            const SizedBox(height: AppSpacing.xxs),
            Text(order.serviceSummary!, style: AppTextStyles.bodySecondary),
          ],
          if (order.creditEarned > 0) ...[
            const SizedBox(height: AppSpacing.xs),
            Text(
              '+${order.creditEarnedLabel} credit earned',
              style: AppTextStyles.success,
            ),
          ],
          if (order.creditApplied > 0) ...[
            const SizedBox(height: AppSpacing.xxs),
            Text(
              'Store credit applied on this visit',
              style: AppTextStyles.bodySmall,
            ),
          ],
        ],
      ),
    );
  }
}

class DashboardErrorCard extends StatelessWidget {
  const DashboardErrorCard({
    required this.message,
    required this.onRetry,
    super.key,
  });

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return AppGlassCard(
      tone: AppGlassTone.warning,
      child: Column(
        children: [
          Text(message, style: AppTextStyles.body, textAlign: TextAlign.center),
          const SizedBox(height: AppSpacing.md),
          TextButton(
            onPressed: onRetry,
            child: Text('Try again', style: AppTextStyles.link),
          ),
        ],
      ),
    );
  }
}

class DashboardProfileStrip extends StatelessWidget {
  const DashboardProfileStrip({
    required this.name,
    this.email,
    this.phone,
    this.typeLabel,
    super.key,
  });

  final String name;
  final String? email;
  final String? phone;
  final String? typeLabel;

  @override
  Widget build(BuildContext context) {
    return AppGlassCard(
      tone: AppGlassTone.secondary,
      padding: const EdgeInsets.all(AppSpacing.md),
      child: Row(
        children: [
          const AppGlassIconTile(
            icon: Icons.person_rounded,
            tone: AppGlassTone.secondary,
          ),
          const SizedBox(width: AppSpacing.sm),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name, style: AppTextStyles.label),
                if (email != null && email!.isNotEmpty)
                  Text(email!, style: AppTextStyles.bodySmall),
                if (phone != null && phone!.isNotEmpty)
                  Text(phone!, style: AppTextStyles.bodySmall),
              ],
            ),
          ),
          if (typeLabel != null && typeLabel!.isNotEmpty)
            AppGlassPill(
              label: typeLabel!,
              tone: AppGlassTone.secondary,
            ),
        ],
      ),
    );
  }
}
