import 'package:flutter/material.dart';
import 'package:pos_mobile/app/theme/app_colors.dart';
import 'package:pos_mobile/app/theme/app_spacing.dart';
import 'package:pos_mobile/app/theme/app_text_styles.dart';

enum AppFieldStatus { normal, focused, error, success }

class AppTextField extends StatelessWidget {
  const AppTextField({
    required this.controller,
    this.hintText,
    this.prefixIcon,
    this.suffix,
    this.obscureText = false,
    this.keyboardType,
    this.textInputAction,
    this.textCapitalization = TextCapitalization.none,
    this.autocorrect = true,
    this.enableSuggestions = true,
    this.onChanged,
    this.onSubmitted,
    this.focusNode,
    this.status = AppFieldStatus.normal,
    this.message,
    this.enabled = true,
    this.autofocus = false,
    super.key,
  });

  final TextEditingController controller;
  final String? hintText;
  final IconData? prefixIcon;
  final Widget? suffix;
  final bool obscureText;
  final TextInputType? keyboardType;
  final TextInputAction? textInputAction;
  final TextCapitalization textCapitalization;
  final bool autocorrect;
  final bool enableSuggestions;
  final ValueChanged<String>? onChanged;
  final ValueChanged<String>? onSubmitted;
  final FocusNode? focusNode;
  final AppFieldStatus status;
  final String? message;
  final bool enabled;
  final bool autofocus;

  Color get _borderColor {
    switch (status) {
      case AppFieldStatus.error:
        return AppColors.error;
      case AppFieldStatus.success:
        return AppColors.success;
      case AppFieldStatus.focused:
        return AppColors.primary;
      case AppFieldStatus.normal:
        return AppColors.border;
    }
  }

  Color get _iconColor {
    switch (status) {
      case AppFieldStatus.error:
        return AppColors.error;
      case AppFieldStatus.success:
        return AppColors.success;
      case AppFieldStatus.focused:
        return AppColors.primary;
      case AppFieldStatus.normal:
        return AppColors.iconGrey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TextField(
          controller: controller,
          focusNode: focusNode,
          obscureText: obscureText,
          keyboardType: keyboardType,
          textInputAction: textInputAction,
          textCapitalization: textCapitalization,
          autocorrect: autocorrect,
          enableSuggestions: enableSuggestions,
          onChanged: onChanged,
          onSubmitted: onSubmitted,
          enabled: enabled,
          autofocus: autofocus,
          style: AppTextStyles.field,
          decoration: InputDecoration(
            hintText: hintText,
            prefixIcon: prefixIcon == null
                ? null
                : Icon(prefixIcon, color: _iconColor, size: AppSpacing.iconMd),
            suffixIcon: suffix,
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: _borderColor),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: _borderColor, width: 1.5),
            ),
            disabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: AppColors.border),
            ),
          ),
        ),
        if (message != null && message!.isNotEmpty) ...[
          const SizedBox(height: AppSpacing.xs),
          Row(
            children: [
              Icon(
                status == AppFieldStatus.success
                    ? Icons.check_circle
                    : Icons.cancel,
                size: 16,
                color: status == AppFieldStatus.success
                    ? AppColors.success
                    : AppColors.error,
              ),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  message!,
                  style: status == AppFieldStatus.success
                      ? AppTextStyles.success
                      : AppTextStyles.error,
                ),
              ),
            ],
          ),
        ],
      ],
    );
  }
}

class AppPasswordField extends StatefulWidget {
  const AppPasswordField({
    required this.controller,
    this.hintText = 'Password',
    this.focusNode,
    this.status = AppFieldStatus.normal,
    this.message,
    this.onChanged,
    this.onSubmitted,
    this.textInputAction,
    super.key,
  });

  final TextEditingController controller;
  final String hintText;
  final FocusNode? focusNode;
  final AppFieldStatus status;
  final String? message;
  final ValueChanged<String>? onChanged;
  final ValueChanged<String>? onSubmitted;
  final TextInputAction? textInputAction;

  @override
  State<AppPasswordField> createState() => _AppPasswordFieldState();
}

class _AppPasswordFieldState extends State<AppPasswordField> {
  bool _obscure = true;

  @override
  Widget build(BuildContext context) {
    return AppTextField(
      controller: widget.controller,
      hintText: widget.hintText,
      prefixIcon: Icons.lock_outline_rounded,
      obscureText: _obscure,
      autocorrect: false,
      enableSuggestions: false,
      focusNode: widget.focusNode,
      status: widget.status,
      message: widget.message,
      onChanged: widget.onChanged,
      onSubmitted: widget.onSubmitted,
      textInputAction: widget.textInputAction,
      suffix: IconButton(
        onPressed: () => setState(() => _obscure = !_obscure),
        tooltip: _obscure ? 'Show password' : 'Hide password',
        icon: Icon(
          _obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined,
          color: AppColors.iconGrey,
        ),
      ),
    );
  }
}
