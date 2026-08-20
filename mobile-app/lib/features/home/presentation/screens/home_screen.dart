import 'package:flutter/material.dart';
import 'package:pos_mobile/app/routes/app_routes.dart';
import 'package:pos_mobile/app/theme/app_colors.dart';
import 'package:pos_mobile/app/theme/app_radius.dart';
import 'package:pos_mobile/app/theme/app_shadows.dart';
import 'package:pos_mobile/app/theme/app_spacing.dart';
import 'package:pos_mobile/app/theme/app_text_styles.dart';
import 'package:pos_mobile/core/api/api_exception.dart';
import 'package:pos_mobile/core/app_services.dart';
import 'package:pos_mobile/core/navigation/app_transitions.dart';
import 'package:pos_mobile/core/widgets/app_logo.dart';
import 'package:pos_mobile/features/auth/presentation/screens/login_screen.dart';
import 'package:pos_mobile/features/home/models/dashboard.dart';

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
    final customer =
        _dashboard?.customer ?? AppServices.session.customer;
    final firstName = (customer?.name ?? 'there').split(' ').first;

    return Scaffold(
      body: SafeArea(
        child: RefreshIndicator(
          color: AppColors.primary,
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
              const SizedBox(height: AppSpacing.xl),
              Text('Welcome back, $firstName', style: AppTextStyles.h1),
              if (customer?.shop?.name != null) ...[
                const SizedBox(height: AppSpacing.xs),
                Text(customer!.shop!.name!, style: AppTextStyles.bodySecondary),
              ],
              const SizedBox(height: AppSpacing.xl),
              if (_loading && _dashboard == null)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 48),
                  child: Center(child: CircularProgressIndicator()),
                )
              else if (_error != null && _dashboard == null)
                _ErrorCard(message: _error!, onRetry: _load)
              else if (_dashboard != null) ...[
                _CreditCard(credit: _dashboard!.credit),
                const SizedBox(height: AppSpacing.md),
                Row(
                  children: [
                    Expanded(
                      child: _StatCard(
                        label: 'Visits',
                        value: '${_dashboard!.customer.totalVisits}',
                      ),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: _StatCard(
                        label: 'Lifetime spend',
                        value: _dashboard!.customer.lifetimeValueLabel,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppSpacing.xl),
                Text('Recent visits', style: AppTextStyles.h3),
                const SizedBox(height: AppSpacing.md),
                if (_dashboard!.recentOrders.isEmpty)
                  Text(
                    'No service history yet.',
                    style: AppTextStyles.bodySecondary,
                  )
                else
                  for (final order in _dashboard!.recentOrders)
                    _OrderTile(order: order),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _CreditCard extends StatelessWidget {
  const _CreditCard({required this.credit});

  final CreditInfo credit;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: credit.canRedeem
            ? AppColors.creditUnlocked
            : AppColors.selectedCardFill,
        borderRadius: AppRadius.lgAll,
        border: Border.all(
          color: credit.canRedeem
              ? AppColors.success.withValues(alpha: 0.35)
              : AppColors.selectedCardBorder,
        ),
        boxShadow: AppShadows.soft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Store credit', style: AppTextStyles.bodySmall),
          const SizedBox(height: AppSpacing.xs),
          Text(credit.balanceLabel, style: AppTextStyles.h1),
          const SizedBox(height: AppSpacing.sm),
          Text(
            credit.canRedeem
                ? 'Ready to use at checkout'
                : 'Usable when balance reaches ${credit.minRedeemBalanceLabel}',
            style: AppTextStyles.bodySecondary,
          ),
        ],
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  const _StatCard({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: AppRadius.mdAll,
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: AppTextStyles.bodySmall),
          const SizedBox(height: AppSpacing.xs),
          Text(value, style: AppTextStyles.h3),
        ],
      ),
    );
  }
}

class _OrderTile extends StatelessWidget {
  const _OrderTile({required this.order});

  final RecentOrder order;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: AppSpacing.sm),
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: AppRadius.mdAll,
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(order.orderNumber, style: AppTextStyles.label),
              ),
              Text(order.totalAmountLabel, style: AppTextStyles.label),
            ],
          ),
          const SizedBox(height: AppSpacing.xxs),
          Text(
            [
              order.statusLabel,
              if (order.createdAtLabel != null) order.createdAtLabel!,
            ].join(' · '),
            style: AppTextStyles.bodySmall,
          ),
          if (order.serviceSummary != null &&
              order.serviceSummary!.isNotEmpty) ...[
            const SizedBox(height: AppSpacing.xxs),
            Text(order.serviceSummary!, style: AppTextStyles.bodySecondary),
          ],
        ],
      ),
    );
  }
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: AppRadius.mdAll,
        border: Border.all(color: AppColors.border),
      ),
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
