import 'package:pos_mobile/features/auth/models/customer.dart';

class CreditInfo {
  const CreditInfo({
    required this.balance,
    required this.balanceLabel,
    required this.minRedeemBalance,
    required this.minRedeemBalanceLabel,
    required this.remainingToUnlock,
    required this.remainingToUnlockLabel,
    required this.unlockProgress,
    required this.canRedeem,
  });

  final double balance;
  final String balanceLabel;
  final double minRedeemBalance;
  final String minRedeemBalanceLabel;
  final double remainingToUnlock;
  final String remainingToUnlockLabel;
  final double unlockProgress;
  final bool canRedeem;

  factory CreditInfo.fromJson(Map<String, dynamic> json) {
    return CreditInfo(
      balance: _asDouble(json['balance']),
      balanceLabel: json['balance_label']?.toString() ?? '',
      minRedeemBalance: _asDouble(json['min_redeem_balance']),
      minRedeemBalanceLabel:
          json['min_redeem_balance_label']?.toString() ?? '',
      remainingToUnlock: _asDouble(json['remaining_to_unlock']),
      remainingToUnlockLabel:
          json['remaining_to_unlock_label']?.toString() ?? '',
      unlockProgress: _asDouble(json['unlock_progress']),
      canRedeem: json['can_redeem'] == true,
    );
  }
}

class DashboardStats {
  const DashboardStats({
    required this.visits,
    required this.lifetimeValueLabel,
    required this.averageSpendLabel,
    required this.loyaltyPoints,
    required this.vehiclesCount,
    required this.openOrdersCount,
    required this.paidOrdersCount,
    this.lastVisitAtLabel,
  });

  final int visits;
  final String lifetimeValueLabel;
  final String averageSpendLabel;
  final int loyaltyPoints;
  final int vehiclesCount;
  final int openOrdersCount;
  final int paidOrdersCount;
  final String? lastVisitAtLabel;

  factory DashboardStats.fromJson(
    Map<String, dynamic>? json, {
    Customer? customer,
    int vehiclesCountFallback = 0,
  }) {
    json ??= const <String, dynamic>{};
    final visits = _asInt(json['visits']) ?? customer?.totalVisits ?? 0;
    final lifetimeLabel = json['lifetime_value_label']?.toString() ??
        customer?.lifetimeValueLabel ??
        '';
    return DashboardStats(
      visits: visits,
      lifetimeValueLabel: lifetimeLabel,
      averageSpendLabel: json['average_spend_label']?.toString() ??
          (visits > 0 && customer != null
              ? customer.lifetimeValueLabel
              : ''),
      loyaltyPoints:
          _asInt(json['loyalty_points']) ?? customer?.loyaltyPoints ?? 0,
      vehiclesCount: _asInt(json['vehicles_count']) ?? vehiclesCountFallback,
      openOrdersCount: _asInt(json['open_orders_count']) ?? 0,
      paidOrdersCount: _asInt(json['paid_orders_count']) ?? 0,
      lastVisitAtLabel: json['last_visit_at_label']?.toString() ??
          customer?.lastVisitAtLabel,
    );
  }
}

class DashboardVehicle {
  const DashboardVehicle({
    required this.id,
    required this.label,
    this.plateNumber,
    this.color,
    this.odometer,
    this.isDefault = false,
  });

  final int id;
  final String label;
  final String? plateNumber;
  final String? color;
  final String? odometer;
  final bool isDefault;

  factory DashboardVehicle.fromJson(Map<String, dynamic> json) {
    return DashboardVehicle(
      id: _asInt(json['id']) ?? 0,
      label: json['label']?.toString() ?? '',
      plateNumber: json['plate_number']?.toString(),
      color: json['color']?.toString(),
      odometer: json['odometer']?.toString(),
      isDefault: json['is_default'] == true,
    );
  }
}

class CreditActivity {
  const CreditActivity({
    required this.id,
    required this.type,
    required this.amountLabel,
    required this.direction,
    this.description,
    this.orderNumber,
    this.createdAtLabel,
  });

  final int id;
  final String type;
  final String amountLabel;
  final String direction;
  final String? description;
  final String? orderNumber;
  final String? createdAtLabel;

  factory CreditActivity.fromJson(Map<String, dynamic> json) {
    return CreditActivity(
      id: _asInt(json['id']) ?? 0,
      type: json['type']?.toString() ?? '',
      amountLabel: json['amount_label']?.toString() ?? '',
      direction: json['direction']?.toString() ?? 'credit',
      description: json['description']?.toString(),
      orderNumber: json['order_number']?.toString(),
      createdAtLabel: json['created_at_label']?.toString(),
    );
  }
}

class RecentOrder {
  const RecentOrder({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.statusLabel,
    required this.statusClass,
    required this.totalAmountLabel,
    required this.itemsCount,
    required this.creditEarned,
    required this.creditEarnedLabel,
    required this.creditApplied,
    this.createdAtLabel,
    this.serviceSummary,
    this.vehicleLabel,
    this.plateNumber,
  });

  final int id;
  final String orderNumber;
  final String status;
  final String statusLabel;
  final String statusClass;
  final String totalAmountLabel;
  final int itemsCount;
  final double creditEarned;
  final String creditEarnedLabel;
  final double creditApplied;
  final String? createdAtLabel;
  final String? serviceSummary;
  final String? vehicleLabel;
  final String? plateNumber;

  factory RecentOrder.fromJson(Map<String, dynamic> json) {
    final vehicle = json['vehicle'];
    return RecentOrder(
      id: _asInt(json['id']) ?? 0,
      orderNumber: json['order_number']?.toString() ?? '',
      status: json['status']?.toString() ?? '',
      statusLabel: json['status_label']?.toString() ?? '',
      statusClass: json['status_class']?.toString() ?? 'warning',
      totalAmountLabel: json['total_amount_label']?.toString() ?? '',
      itemsCount: _asInt(json['items_count']) ?? 0,
      creditEarned: _asDouble(json['credit_earned']),
      creditEarnedLabel: json['credit_earned_label']?.toString() ?? '',
      creditApplied: _asDouble(json['credit_applied']),
      createdAtLabel: json['created_at_label']?.toString(),
      serviceSummary: json['service_summary']?.toString(),
      vehicleLabel: vehicle is Map<String, dynamic>
          ? vehicle['label']?.toString()
          : null,
      plateNumber: vehicle is Map<String, dynamic>
          ? vehicle['plate_number']?.toString()
          : null,
    );
  }
}

class CustomerDashboard {
  const CustomerDashboard({
    required this.customer,
    required this.credit,
    required this.stats,
    required this.vehicles,
    required this.recentCredits,
    required this.recentOrders,
  });

  final Customer customer;
  final CreditInfo credit;
  final DashboardStats stats;
  final List<DashboardVehicle> vehicles;
  final List<CreditActivity> recentCredits;
  final List<RecentOrder> recentOrders;

  factory CustomerDashboard.fromJson(Map<String, dynamic> json) {
    final customerJson = json['customer'];
    final creditJson = json['credit'];
    final statsJson = json['stats'];
    final vehiclesJson = json['vehicles'];
    final creditsJson = json['recent_credits'];
    final ordersJson = json['recent_orders'];

    final customer = Customer.fromJson(
      customerJson is Map<String, dynamic>
          ? customerJson
          : const <String, dynamic>{},
    );
    final vehicles = vehiclesJson is List
        ? vehiclesJson
            .whereType<Map<String, dynamic>>()
            .map(DashboardVehicle.fromJson)
            .toList()
        : const <DashboardVehicle>[];

    return CustomerDashboard(
      customer: customer,
      credit: CreditInfo.fromJson(
        creditJson is Map<String, dynamic>
            ? creditJson
            : const <String, dynamic>{},
      ),
      stats: DashboardStats.fromJson(
        statsJson is Map<String, dynamic> ? statsJson : null,
        customer: customer,
        vehiclesCountFallback: vehicles.length,
      ),
      vehicles: vehicles,
      recentCredits: creditsJson is List
          ? creditsJson
              .whereType<Map<String, dynamic>>()
              .map(CreditActivity.fromJson)
              .toList()
          : const [],
      recentOrders: ordersJson is List
          ? ordersJson
              .whereType<Map<String, dynamic>>()
              .map(RecentOrder.fromJson)
              .toList()
          : const [],
    );
  }
}

int? _asInt(Object? value) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value?.toString() ?? '');
}

double _asDouble(Object? value) {
  if (value is double) return value;
  if (value is num) return value.toDouble();
  return double.tryParse(value?.toString() ?? '') ?? 0;
}
