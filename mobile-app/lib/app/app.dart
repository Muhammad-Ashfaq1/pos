import 'package:flutter/material.dart';
import 'package:pos_mobile/app/routes/app_routes.dart';
import 'package:pos_mobile/app/theme/app_theme.dart';
import 'package:pos_mobile/features/auth/presentation/screens/forgot_password_screen.dart';
import 'package:pos_mobile/features/auth/presentation/screens/login_screen.dart';
import 'package:pos_mobile/features/auth/presentation/screens/splash_screen.dart';
import 'package:pos_mobile/features/home/presentation/screens/home_screen.dart';

class PosApp extends StatelessWidget {
  const PosApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'AutoServe',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light(),
      initialRoute: AppRoutes.splash,
      routes: {
        AppRoutes.splash: (_) => const SplashScreen(),
        AppRoutes.login: (_) => const LoginScreen(),
        AppRoutes.forgotPassword: (_) => const ForgotPasswordScreen(),
        AppRoutes.home: (_) => const HomeScreen(),
      },
    );
  }
}
