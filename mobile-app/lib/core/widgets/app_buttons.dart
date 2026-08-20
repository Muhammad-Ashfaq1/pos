import 'package:flutter/material.dart';
import 'package:pos_mobile/app/theme/app_colors.dart';
import 'package:pos_mobile/app/theme/app_radius.dart';
import 'package:pos_mobile/app/theme/app_spacing.dart';
import 'package:pos_mobile/app/theme/app_text_styles.dart';

class AppPrimaryButton extends StatelessWidget {
  const AppPrimaryButton({
    required this.label,
    required this.onPressed,
    this.isLoading = false,
    this.isExpanded = true,
    super.key,
  });

  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  final bool isExpanded;

  @override
  Widget build(BuildContext context) {
    final button = ElevatedButton(
      onPressed: isLoading ? null : onPressed,
      style: ElevatedButton.styleFrom(
        elevation: 0,
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.white,
        disabledBackgroundColor: AppColors.primary.withValues(alpha: 0.45),
        disabledForegroundColor: AppColors.white,
        minimumSize: const Size.fromHeight(AppSpacing.buttonHeight),
        shape: RoundedRectangleBorder(borderRadius: AppRadius.pillAll),
        textStyle: AppTextStyles.button,
      ),
      child: isLoading
          ? const SizedBox(
              width: 22,
              height: 22,
              child: CircularProgressIndicator(
                strokeWidth: 2.4,
                color: AppColors.white,
              ),
            )
          : Text(label, style: AppTextStyles.button),
    );

    if (!isExpanded) return button;
    return SizedBox(width: double.infinity, child: button);
  }
}

class AppBackButton extends StatelessWidget {
  const AppBackButton({
    this.onPressed,
    this.icon = Icons.chevron_left_rounded,
    this.circular = false,
    this.size = AppSpacing.touchTarget,
    super.key,
  });

  final VoidCallback? onPressed;
  final IconData icon;
  final bool circular;
  final double size;

  @override
  Widget build(BuildContext context) {
    final radius = circular ? BorderRadius.circular(size / 2) : AppRadius.mdAll;

    return Semantics(
      button: true,
      label: 'Back',
      child: Material(
        color: AppColors.backButtonBg,
        shape: circular ? const CircleBorder() : null,
        borderRadius: circular ? null : radius,
        child: InkWell(
          onTap: onPressed ?? () => Navigator.of(context).maybePop(),
          customBorder: circular ? const CircleBorder() : null,
          borderRadius: circular ? null : radius,
          child: SizedBox(
            width: size,
            height: size,
            child: Icon(
              icon,
              color: AppColors.textPrimary,
              size: circular ? 24 : 28,
            ),
          ),
        ),
      ),
    );
  }
}
