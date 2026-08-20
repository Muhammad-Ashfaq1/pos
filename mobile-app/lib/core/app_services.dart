import 'package:pos_mobile/core/api/api_client.dart';
import 'package:pos_mobile/core/auth/auth_session.dart';
import 'package:pos_mobile/features/auth/data/auth_repository.dart';
import 'package:pos_mobile/features/home/data/dashboard_repository.dart';

/// Tiny service locator — no Riverpod/Bloc.
class AppServices {
  AppServices._();

  static final AuthSession session = AuthSession();
  static final ApiClient api = ApiClient(session: session);
  static final AuthRepository auth = AuthRepository(api: api, session: session);
  static final DashboardRepository dashboard = DashboardRepository(api: api);
}
