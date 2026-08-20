import 'package:flutter/material.dart';
import 'package:pos_mobile/app/routes/app_routes.dart';
import 'package:pos_mobile/app/theme/app_colors.dart';
import 'package:pos_mobile/app/theme/app_spacing.dart';
import 'package:pos_mobile/app/theme/app_text_styles.dart';
import 'package:pos_mobile/core/api/api_exception.dart';
import 'package:pos_mobile/core/app_services.dart';
import 'package:pos_mobile/core/navigation/app_transitions.dart';
import 'package:pos_mobile/core/widgets/app_glass.dart';
import 'package:pos_mobile/core/widgets/app_logo.dart';
import 'package:pos_mobile/features/auth/presentation/screens/login_screen.dart';
import 'package:pos_mobile/features/home/models/dashboard.dart';
import 'package:pos_mobile/features/home/presentation/widgets/dashboard_sections.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  CustomerDashboard? _dashboard;
  String? _error;
  bool _loading = true;
  bool _signingOut = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final dashboard = await AppServices.dashboard.fetch();
      if (!mounted) return;
      setState(() {
        _dashboard = dashboard;
        _loading = false;
      });
    } on ApiException catch (error) {
      if (!mounted) return;
      if (error.isUnauthenticated) {
        await AppServices.session.clearToken();
        if (!mounted) return;
        AppTransitions.fadeReplace(
          context,
          const LoginScreen(),
          routeName: AppRoutes.login,
        );
        return;
      }
      setState(() {
        _error = error.message;
        _loading = false;
      });
    }
  }

  Future<void> _signOut() async {
    setState(() => _signingOut = true);
    await AppServices.auth.logout();
    if (!mounted) return;
    AppTransitions.fadeReplace(
      context,
      const LoginScreen(),
      routeName: AppRoutes.login,
    );
  }

  @override
  Widget build(BuildContext context) {
    final customer = _dashboard?.customer ?? AppServices.session.customer;
    final firstName = (customer?.name ?? 'there').split(' ').first;

    return Scaffold(
      backgroundColor: Colors.transparent,
      body: AppGlassBackdrop(
        child: SafeArea(
          child: RefreshIndicator(
            color: AppColors.primary,
            backgroundColor: AppColors.white,
            onRefresh: _load,
            child: ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(
                AppSpacing.screenHorizontal,
                AppSpacing.md,
                AppSpacing.screenHorizontal,
                AppSpacing.xxl,
              ),
              children: [
                Row(
                  children: [
                    const AppLogo(compact: true),
                    const Spacer(),
                    TextButton(
                      onPressed: _signingOut ? null : _signOut,
                      child: _signingOut
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : Text('Sign out', style: AppTextStyles.link),
                    ),
                  ],
                ),
                const SizedBox(height: AppSpacing.lg),
                Text('Overview', style: AppTextStyles.h1),
                const SizedBox(height: AppSpacing.xxs),
                Text(
                  'Welcome back, $firstName — visits and store credit at a glance',
                  style: AppTextStyles.bodySecondary,
                ),
                if (customer?.shop?.name != null) ...[
                  const SizedBox(height: AppSpacing.xxs),
                  Text(customer!.shop!.name!, style: AppTextStyles.bodySmall),
                ],
                const SizedBox(height: AppSpacing.lg),
                if (_loading && _dashboard == null)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 64),
                    child: Center(child: CircularProgressIndicator()),
                  )
                else if (_error != null && _dashboard == null)
                  DashboardErrorCard(message: _error!, onRetry: _load)
                else if (_dashboard != null) ...[
                  DashboardProfileStrip(
                    name: _dashboard!.customer.name,
                    email: _dashboard!.customer.email,
                    phone: _dashboard!.customer.phone,
                    typeLabel: _dashboard!.customer.customerTypeLabel,
                  ),
                  const SizedBox(height: AppSpacing.md),
                  DashboardCreditHero(
                    credit: _dashboard!.credit,
                    shopName: _dashboard!.customer.shop?.name,
                  ),
                  const SizedBox(height: AppSpacing.md),
                  DashboardStatsGrid(stats: _dashboard!.stats),
                  const SizedBox(height: AppSpacing.xl),
                  const DashboardSectionHeader(
                    title: 'Vehicles on file',
                    subtitle: 'What this shop has saved for you',
                  ),
                  DashboardVehiclesPanel(vehicles: _dashboard!.vehicles),
                  const SizedBox(height: AppSpacing.xl),
                  const DashboardSectionHeader(
                    title: 'Credit activity',
                    subtitle: 'Latest wallet movements',
                  ),
                  DashboardCreditsPanel(entries: _dashboard!.recentCredits),
                  const SizedBox(height: AppSpacing.xl),
                  const DashboardSectionHeader(
                    title: 'Recent visits',
                    subtitle: 'Your latest service activity',
                  ),
                  DashboardOrdersPanel(orders: _dashboard!.recentOrders),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
