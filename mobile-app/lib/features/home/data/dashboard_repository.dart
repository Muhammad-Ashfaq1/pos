import 'package:pos_mobile/core/api/api_client.dart';
import 'package:pos_mobile/core/api/api_exception.dart';
import 'package:pos_mobile/features/home/models/dashboard.dart';

class DashboardRepository {
  DashboardRepository({required this.api});

  final ApiClient api;

  Future<CustomerDashboard> fetch() async {
    final payload = await api.get('/dashboard');
    final data = payload['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException(message: 'Unexpected dashboard response.');
    }
    return CustomerDashboard.fromJson(data);
  }
}
