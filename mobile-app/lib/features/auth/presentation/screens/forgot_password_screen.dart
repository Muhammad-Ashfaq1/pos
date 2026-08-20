import 'package:flutter/material.dart';
import 'package:pos_mobile/app/theme/app_spacing.dart';
import 'package:pos_mobile/core/api/api_exception.dart';
import 'package:pos_mobile/core/app_services.dart';
import 'package:pos_mobile/core/widgets/app_buttons.dart';
import 'package:pos_mobile/core/widgets/app_controls.dart';
import 'package:pos_mobile/core/widgets/app_text_field.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _shopController = TextEditingController();
  final _emailController = TextEditingController();
  bool _loading = false;
  String? _shopMessage;
  String? _emailMessage;
  AppFieldStatus _shopStatus = AppFieldStatus.normal;
  AppFieldStatus _emailStatus = AppFieldStatus.normal;

  @override
  void initState() {
    super.initState();
    final remembered = AppServices.session.shopSlug;
    if (remembered != null && remembered.isNotEmpty) {
      _shopController.text = remembered;
    }
  }

  @override
  void dispose() {
    _shopController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final shop = _shopController.text.trim();
    final email = _emailController.text.trim();
    var hasError = false;
    setState(() {
      _shopMessage = null;
      _emailMessage = null;
      if (shop.isEmpty) {
        _shopStatus = AppFieldStatus.error;
        _shopMessage = 'Enter your shop code';
        hasError = true;
      } else {
        _shopStatus = AppFieldStatus.normal;
      }
      if (email.isEmpty) {
        _emailStatus = AppFieldStatus.error;
        _emailMessage = 'Enter your email';
        hasError = true;
      } else {
        _emailStatus = AppFieldStatus.normal;
      }
    });
    if (hasError) return;

    setState(() => _loading = true);
    try {
      await AppServices.session.rememberShop(shop);
      final message = await AppServices.auth.forgotPassword(
        shop: shop,
        email: email,
      );
      if (!mounted) return;
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
      Navigator.of(context).pop();
    } on ApiException catch (error) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _shopMessage = error.fieldError('shop');
        _emailMessage = error.fieldError('email') ?? error.message;
        if (_shopMessage != null) _shopStatus = AppFieldStatus.error;
        if (_emailMessage != null) _emailStatus = AppFieldStatus.error;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
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
              const Align(
                alignment: Alignment.centerLeft,
                child: AppBackButton(),
              ),
              const SizedBox(height: AppSpacing.xxl),
              const AppAuthHeader(
                title: 'Forgot password',
                subtitle:
                    'We will email a reset link if that shop account exists.',
              ),
              const SizedBox(height: AppSpacing.xl),
              AppTextField(
                controller: _shopController,
                hintText: 'Shop code',
                prefixIcon: Icons.storefront_outlined,
                textInputAction: TextInputAction.next,
                autocorrect: false,
                enableSuggestions: false,
                status: _shopStatus,
                message: _shopMessage,
              ),
              const SizedBox(height: AppSpacing.md),
              AppTextField(
                controller: _emailController,
                hintText: 'Email',
                prefixIcon: Icons.mail_outline_rounded,
                keyboardType: TextInputType.emailAddress,
                textInputAction: TextInputAction.done,
                autocorrect: false,
                enableSuggestions: false,
                status: _emailStatus,
                message: _emailMessage,
                onSubmitted: (_) => _submit(),
              ),
              const SizedBox(height: AppSpacing.xl),
              AppPrimaryButton(
                label: 'Send reset link',
                isLoading: _loading,
                onPressed: _submit,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
