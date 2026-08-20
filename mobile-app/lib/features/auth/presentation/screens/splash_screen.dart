import 'package:flutter/material.dart';
import 'package:pos_mobile/app/routes/app_routes.dart';
import 'package:pos_mobile/app/theme/app_colors.dart';
import 'package:pos_mobile/core/api/api_exception.dart';
import 'package:pos_mobile/core/app_services.dart';
import 'package:pos_mobile/core/navigation/app_transitions.dart';
import 'package:pos_mobile/core/widgets/app_logo.dart';
import 'package:pos_mobile/features/auth/presentation/screens/login_screen.dart';
import 'package:pos_mobile/features/home/presentation/screens/home_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _bootstrap();
  }

  Future<void> _bootstrap() async {
    final started = DateTime.now();
    Widget next = const LoginScreen();
    String routeName = AppRoutes.login;

    if (AppServices.session.isAuthenticated) {
      try {
        await AppServices.auth.me();
        next = const HomeScreen();
        routeName = AppRoutes.home;
      } on ApiException {
        await AppServices.session.clearToken();
      }
    }

    final elapsed = DateTime.now().difference(started);
    const minSplash = Duration(milliseconds: 900);
    if (elapsed < minSplash) {
      await Future<void>.delayed(minSplash - elapsed);
    }

    if (!mounted) return;
    AppTransitions.fadeReplace(context, next, routeName: routeName);
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: AppColors.splashBlue,
      body: SafeArea(
        child: Column(
          children: [
            Spacer(flex: 3),
            Center(child: AppLogo(onDark: true)),
            Spacer(flex: 4),
            Padding(
              padding: EdgeInsets.only(bottom: 28),
              child: DottedLoader(),
            ),
          ],
        ),
      ),
    );
  }
}
