import 'package:pos_mobile/features/auth/models/customer.dart';

class CreditInfo {
  const CreditInfo({
    required this.balance,
    required this.balanceLabel,
    required this.minRedeemBalance,
    required this.minRedeemBalanceLabel,
    required this.canRedeem,
  });

  final double balance;
  final String balanceLabel;
  final double minRedeemBalance;
  final String minRedeemBalanceLabel;
  final bool canRedeem;

  factory CreditInfo.fromJson(Map<String, dynamic> json) {
    return CreditInfo(
      balance: _asDouble(json['balance']),
      balanceLabel: json['balance_label']?.toString() ?? '',
      minRedeemBalance: _asDouble(json['min_redeem_balance']),
      minRedeemBalanceLabel:
          json['min_redeem_balance_label']?.toString() ?? '',
      canRedeem: json['can_redeem'] == true,
    );
  }
}

class RecentOrder {
  const RecentOrder({
    required this.id,
    required this.orderNumber,
    required this.statusLabel,
    required this.totalAmountLabel,
    this.createdAtLabel,
    this.serviceSummary,
  });

  final int id;
  final String orderNumber;
  final String statusLabel;
  final String totalAmountLabel;
  final String? createdAtLabel;
  final String? serviceSummary;

  factory RecentOrder.fromJson(Map<String, dynamic> json) {
    return RecentOrder(
      id: _asInt(json['id']) ?? 0,
      orderNumber: json['order_number']?.toString() ?? '',
      statusLabel: json['status_label']?.toString() ?? '',
      totalAmountLabel: json['total_amount_label']?.toString() ?? '',
      createdAtLabel: json['created_at_label']?.toString(),
      serviceSummary: json['service_summary']?.toString(),
    );
  }
}

class CustomerDashboard {
  const CustomerDashboard({
    required this.customer,
    required this.credit,
    required this.recentOrders,
  });

  final Customer customer;
  final CreditInfo credit;
  final List<RecentOrder> recentOrders;

  factory CustomerDashboard.fromJson(Map<String, dynamic> json) {
    final customerJson = json['customer'];
    final creditJson = json['credit'];
    final ordersJson = json['recent_orders'];

    return CustomerDashboard(
      customer: Customer.fromJson(
        customerJson is Map<String, dynamic>
            ? customerJson
            : const <String, dynamic>{},
      ),
      credit: CreditInfo.fromJson(
        creditJson is Map<String, dynamic>
            ? creditJson
            : const <String, dynamic>{},
      ),
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
