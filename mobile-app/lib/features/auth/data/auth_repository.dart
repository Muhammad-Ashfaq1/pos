import 'package:pos_mobile/core/api/api_client.dart';
import 'package:pos_mobile/core/api/api_config.dart';
import 'package:pos_mobile/core/api/api_exception.dart';
import 'package:pos_mobile/core/auth/auth_session.dart';
import 'package:pos_mobile/features/auth/models/customer.dart';

class AuthRepository {
  AuthRepository({required this.api, required this.session});

  final ApiClient api;
  final AuthSession session;

  Future<Customer> login({
    required String email,
    required String password,
  }) async {
    final payload = await api.post('/login', body: {
      'email': email,
      'password': password,
      'device_name': ApiConfig.deviceName,
    });

    final token = payload['token']?.toString();
    final data = payload['data'];
    if (token == null || token.isEmpty || data is! Map<String, dynamic>) {
      throw const ApiException(message: 'Unexpected login response.');
    }

    final customer = Customer.fromJson(data);
    await session.save(
      token: token,
      shopSlug: customer.shop?.slug ?? '',
      customer: customer,
    );
    return customer;
  }

  Future<Customer> me() async {
    final payload = await api.get('/me');
    final data = payload['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException(message: 'Unexpected profile response.');
    }
    final customer = Customer.fromJson(data);
    session.customer = customer;
    return customer;
  }

  Future<String> forgotPassword({
    required String shop,
    required String email,
  }) async {
    final payload = await api.post('/forgot-password', body: {
      'shop': shop,
      'email': email,
    });
    return payload['message']?.toString() ??
        'If an account exists for that email, a reset link has been sent.';
  }

  Future<void> logout() async {
    try {
      await api.post('/logout', auth: true);
    } on ApiException {
      // Token may already be invalid — still clear local session.
    } finally {
      await session.clearToken();
    }
  }
}
