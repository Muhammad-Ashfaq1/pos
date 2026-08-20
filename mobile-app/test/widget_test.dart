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
      'customer_type': 'registered',
      'customer_type_label': 'Registered',
      'credit_balance': 64.5,
      'credit_balance_label': '\$64.50',
      'loyalty_points_balance': 40,
      'total_visits': 3,
      'lifetime_value': 180,
      'lifetime_value_label': '\$180.00',
      'last_visit_at_label': 'Aug 20, 2026',
      'shop': {
        'id': 1,
        'name': 'Al Rukn Al Thaki',
        'slug': 'al-rukn-al-thaki',
      },
    });

    expect(customer.id, 12);
    expect(customer.name, 'Olivia Bennett');
    expect(customer.creditBalance, 64.5);
    expect(customer.loyaltyPoints, 40);
    expect(customer.customerTypeLabel, 'Registered');
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
        'remaining_to_unlock': 0,
        'remaining_to_unlock_label': '\$0.00',
        'unlock_progress': 1,
        'can_redeem': true,
      },
      'stats': {
        'visits': 2,
        'lifetime_value_label': '\$100.00',
        'average_spend_label': '\$50.00',
        'loyalty_points': 10,
        'vehicles_count': 1,
        'open_orders_count': 0,
        'paid_orders_count': 2,
        'last_visit_at_label': 'Aug 20, 2026',
      },
      'vehicles': [
        {
          'id': 3,
          'label': '2019 Toyota Corolla',
          'plate_number': 'OLV-2019',
          'is_default': true,
        },
      ],
      'recent_credits': [
        {
          'id': 1,
          'type': 'earn',
          'amount_label': '+\$8.00',
          'direction': 'credit',
          'description': 'Visit earn',
        },
      ],
      'recent_orders': [
        {
          'id': 9,
          'order_number': 'INV-1009',
          'status': 'paid',
          'status_label': 'paid',
          'status_class': 'success',
          'total_amount_label': '\$42.00',
          'items_count': 2,
          'credit_earned': 4.2,
          'credit_earned_label': '\$4.20',
          'credit_applied': 0,
          'created_at_label': 'Aug 20, 2026 10:00 AM',
          'service_summary': 'Oil change',
          'vehicle': {'label': '2019 Toyota Corolla', 'plate_number': 'OLV-2019'},
        },
      ],
    });

    expect(dashboard.credit.canRedeem, isTrue);
    expect(dashboard.stats.vehiclesCount, 1);
    expect(dashboard.vehicles, hasLength(1));
    expect(dashboard.recentCredits.first.type, 'earn');
    expect(dashboard.recentOrders, hasLength(1));
    expect(dashboard.recentOrders.first.orderNumber, 'INV-1009');
    expect(dashboard.recentOrders.first.vehicleLabel, '2019 Toyota Corolla');
  });
}
