import 'package:flutter/material.dart';
import 'package:pos_mobile/app/routes/app_routes.dart';
import 'package:pos_mobile/app/theme/app_spacing.dart';
import 'package:pos_mobile/app/theme/app_text_styles.dart';
import 'package:pos_mobile/core/api/api_exception.dart';
import 'package:pos_mobile/core/app_services.dart';
import 'package:pos_mobile/core/navigation/app_transitions.dart';
import 'package:pos_mobile/core/widgets/app_buttons.dart';
import 'package:pos_mobile/core/widgets/app_logo.dart';
import 'package:pos_mobile/core/widgets/app_text_field.dart';
import 'package:pos_mobile/features/home/presentation/screens/home_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _shopController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _shopFocus = FocusNode();
  final _emailFocus = FocusNode();
  final _passwordFocus = FocusNode();

  bool _loading = false;
  bool _showWelcome = false;
  String? _shopMessage;
  String? _emailMessage;
  String? _passwordMessage;
  AppFieldStatus _shopStatus = AppFieldStatus.normal;
  AppFieldStatus _emailStatus = AppFieldStatus.normal;
  AppFieldStatus _passwordStatus = AppFieldStatus.normal;

  @override
  void initState() {
    super.initState();
    final remembered = AppServices.session.shopSlug;
    if (remembered != null && remembered.isNotEmpty) {
      _shopController.text = remembered;
    }
    _shopFocus.addListener(_syncFocus);
    _emailFocus.addListener(_syncFocus);
    _passwordFocus.addListener(_syncFocus);
  }

  void _syncFocus() {
    setState(() {
      if (_shopFocus.hasFocus) {
        _shopStatus = AppFieldStatus.focused;
        _showWelcome = true;
      } else if (_shopStatus == AppFieldStatus.focused) {
        _shopStatus = AppFieldStatus.normal;
      }
      if (_emailFocus.hasFocus) {
        _emailStatus = AppFieldStatus.focused;
        _showWelcome = true;
      } else if (_emailStatus == AppFieldStatus.focused) {
        _emailStatus = AppFieldStatus.normal;
      }
      if (_passwordFocus.hasFocus) {
        _passwordStatus = AppFieldStatus.focused;
        _showWelcome = true;
      } else if (_passwordStatus == AppFieldStatus.focused) {
        _passwordStatus = AppFieldStatus.normal;
      }
    });
  }

  @override
  void dispose() {
    _shopController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _shopFocus.dispose();
    _emailFocus.dispose();
    _passwordFocus.dispose();
    super.dispose();
  }

  void _clearFieldErrors() {
    _shopMessage = null;
    _emailMessage = null;
    _passwordMessage = null;
    if (_shopStatus == AppFieldStatus.error) {
      _shopStatus = AppFieldStatus.focused;
    }
    if (_emailStatus == AppFieldStatus.error) {
      _emailStatus = AppFieldStatus.focused;
    }
    if (_passwordStatus == AppFieldStatus.error) {
      _passwordStatus = AppFieldStatus.focused;
    }
  }

  Future<void> _signIn() async {
    final shop = _shopController.text.trim();
    final email = _emailController.text.trim();
    final password = _passwordController.text;

    var hasError = false;
    setState(() {
      if (shop.isEmpty) {
        _shopStatus = AppFieldStatus.error;
        _shopMessage = 'Enter your shop code';
        hasError = true;
      }
      if (email.isEmpty) {
        _emailStatus = AppFieldStatus.error;
        _emailMessage = 'Enter your email';
        hasError = true;
      }
      if (password.isEmpty) {
        _passwordStatus = AppFieldStatus.error;
        _passwordMessage = 'Enter your password';
        hasError = true;
      }
    });
    if (hasError) return;

    setState(() => _loading = true);

    try {
      await AppServices.session.rememberShop(shop);
      await AppServices.auth.login(
        shop: shop,
        email: email,
        password: password,
      );
      if (!mounted) return;
      AppTransitions.fadeReplace(
        context,
        const HomeScreen(),
        routeName: AppRoutes.home,
      );
    } on ApiException catch (error) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        final shopError = error.fieldError('shop');
        final emailError = error.fieldError('email');
        final passwordError = error.fieldError('password');

        if (shopError != null) {
          _shopStatus = AppFieldStatus.error;
          _shopMessage = shopError;
        }
        if (emailError != null) {
          _emailStatus = AppFieldStatus.error;
          _emailMessage = emailError;
        }
        if (passwordError != null) {
          _passwordStatus = AppFieldStatus.error;
          _passwordMessage = passwordError;
        }
        if (shopError == null && emailError == null && passwordError == null) {
          _emailStatus = AppFieldStatus.error;
          _passwordStatus = AppFieldStatus.error;
          _emailMessage = error.message;
        }
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Could not sign in. Please try again.'),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(
            AppSpacing.screenHorizontal,
            AppSpacing.md,
            AppSpacing.screenHorizontal,
            AppSpacing.xl,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: AppSpacing.xxl),
              const Center(child: AppLogo()),
              const SizedBox(height: AppSpacing.xl),
              SizedBox(
                height: 36,
                child: Align(
                  alignment: Alignment.centerLeft,
                  child: AnimatedOpacity(
                    opacity: _showWelcome ? 1 : 0,
                    duration: AppTransitions.content,
                    curve: Curves.easeOutCubic,
                    child: Text(
                      'Hey! Welcome back',
                      style: AppTextStyles.h2,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.xs),
              Text(
                'Sign in to your shop account',
                style: AppTextStyles.bodySecondary,
              ),
              const SizedBox(height: AppSpacing.xl),
              AppTextField(
                controller: _shopController,
                focusNode: _shopFocus,
                hintText: 'Shop code',
                prefixIcon: Icons.storefront_outlined,
                textInputAction: TextInputAction.next,
                textCapitalization: TextCapitalization.none,
                autocorrect: false,
                enableSuggestions: false,
                keyboardType: TextInputType.text,
                status: _shopStatus,
                message: _shopMessage,
                onChanged: (_) {
                  setState(() {
                    _showWelcome = true;
                    _clearFieldErrors();
                  });
                },
              ),
              const SizedBox(height: AppSpacing.md),
              AppTextField(
                controller: _emailController,
                focusNode: _emailFocus,
                hintText: 'Email',
                prefixIcon: Icons.mail_outline_rounded,
                keyboardType: TextInputType.emailAddress,
                textInputAction: TextInputAction.next,
                autocorrect: false,
                enableSuggestions: false,
                status: _emailStatus,
                message: _emailMessage,
                onChanged: (_) {
                  setState(() {
                    _showWelcome = true;
                    _clearFieldErrors();
                  });
                },
              ),
              const SizedBox(height: AppSpacing.md),
              AppPasswordField(
                controller: _passwordController,
                focusNode: _passwordFocus,
                status: _passwordStatus,
                message: _passwordMessage,
                textInputAction: TextInputAction.done,
                onSubmitted: (_) => _signIn(),
                onChanged: (_) {
                  setState(_clearFieldErrors);
                },
              ),
              Align(
                alignment: Alignment.centerRight,
                child: TextButton(
                  onPressed: () => Navigator.of(context)
                      .pushNamed(AppRoutes.forgotPassword),
                  child: Text('Forgot Password?', style: AppTextStyles.link),
                ),
              ),
              const SizedBox(height: AppSpacing.sm),
              AppPrimaryButton(
                label: 'Sign In',
                isLoading: _loading,
                onPressed: _signIn,
              ),
              const SizedBox(height: AppSpacing.xl),
              Text(
                'Use the shop code from your invite or visit. '
                'The same email can exist at more than one shop.',
                style: AppTextStyles.bodySmall,
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
