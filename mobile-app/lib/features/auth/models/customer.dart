class ShopInfo {
  const ShopInfo({this.id, this.name, this.slug});

  final int? id;
  final String? name;
  final String? slug;

  factory ShopInfo.fromJson(Map<String, dynamic>? json) {
    if (json == null) return const ShopInfo();
    return ShopInfo(
      id: _asInt(json['id']),
      name: json['name']?.toString(),
      slug: json['slug']?.toString(),
    );
  }
}

class Customer {
  const Customer({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.customerType,
    this.customerTypeLabel,
    required this.creditBalance,
    required this.creditBalanceLabel,
    this.loyaltyPoints = 0,
    required this.totalVisits,
    required this.lifetimeValue,
    required this.lifetimeValueLabel,
    this.lastVisitAt,
    this.lastVisitAtLabel,
    this.shop,
  });

  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? customerType;
  final String? customerTypeLabel;
  final double creditBalance;
  final String creditBalanceLabel;
  final int loyaltyPoints;
  final int totalVisits;
  final double lifetimeValue;
  final String lifetimeValueLabel;
  final String? lastVisitAt;
  final String? lastVisitAtLabel;
  final ShopInfo? shop;

  factory Customer.fromJson(Map<String, dynamic> json) {
    return Customer(
      id: _asInt(json['id']) ?? 0,
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString(),
      customerType: json['customer_type']?.toString(),
      customerTypeLabel: json['customer_type_label']?.toString(),
      creditBalance: _asDouble(json['credit_balance']),
      creditBalanceLabel: json['credit_balance_label']?.toString() ?? '',
      loyaltyPoints: _asInt(json['loyalty_points_balance']) ?? 0,
      totalVisits: _asInt(json['total_visits']) ?? 0,
      lifetimeValue: _asDouble(json['lifetime_value']),
      lifetimeValueLabel: json['lifetime_value_label']?.toString() ?? '',
      lastVisitAt: json['last_visit_at']?.toString(),
      lastVisitAtLabel: json['last_visit_at_label']?.toString(),
      shop: json['shop'] is Map<String, dynamic>
          ? ShopInfo.fromJson(json['shop'] as Map<String, dynamic>)
          : null,
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
