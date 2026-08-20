import 'package:flutter_test/flutter_test.dart';
import 'package:pos_mobile/features/auth/models/customer.dart';
import 'package:pos_mobile/features/home/models/dashboard.dart';

void main() {
  test('Customer.fromJson maps the Sanctum login payload', () {
    final customer = Customer.fromJson({
      'id': 12,
      'name': 'Olivia Bennett',
      'email': 'olivia@obtainsolutions.com',
      'phone': '+1 555 880 0101',
      'credit_balance': 64.5,
      'credit_balance_label': '\$64.50',
      'total_visits': 3,
      'lifetime_value': 180,
      'lifetime_value_label': '\$180.00',
      'shop': {
        'id': 1,
        'name': 'Al Rukn Al Thaki',
        'slug': 'al-rukn-al-thaki',
      },
    });

    expect(customer.id, 12);
    expect(customer.name, 'Olivia Bennett');
    expect(customer.creditBalance, 64.5);
    expect(customer.shop?.slug, 'al-rukn-al-thaki');
  });

  test('CustomerDashboard.fromJson maps the home payload', () {
    final dashboard = CustomerDashboard.fromJson({
      'customer': {
        'id': 1,
        'name': 'Olivia Bennett',
        'email': 'olivia@obtainsolutions.com',
        'credit_balance': 50,
        'credit_balance_label': '\$50.00',
        'total_visits': 2,
        'lifetime_value': 100,
        'lifetime_value_label': '\$100.00',
      },
      'credit': {
        'balance': 50,
        'balance_label': '\$50.00',
        'min_redeem_balance': 50,
        'min_redeem_balance_label': '\$50.00',
        'can_redeem': true,
      },
      'recent_orders': [
        {
          'id': 9,
          'order_number': 'INV-1009',
          'status_label': 'paid',
          'total_amount_label': '\$42.00',
          'created_at_label': 'Aug 20, 2026 10:00 AM',
          'service_summary': 'Oil change',
        },
      ],
    });

    expect(dashboard.credit.canRedeem, isTrue);
    expect(dashboard.recentOrders, hasLength(1));
    expect(dashboard.recentOrders.first.orderNumber, 'INV-1009');
  });
}
