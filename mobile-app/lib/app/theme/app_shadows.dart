import 'package:flutter/material.dart';
import 'package:pos_mobile/app/theme/app_colors.dart';

class AppShadows {
  AppShadows._();

  static List<BoxShadow> get card => [
        BoxShadow(
          color: AppColors.textPrimary.withValues(alpha: 0.06),
          blurRadius: 16,
          offset: const Offset(0, 6),
        ),
      ];

  static List<BoxShadow> get soft => [
        BoxShadow(
          color: AppColors.textPrimary.withValues(alpha: 0.04),
          blurRadius: 10,
          offset: const Offset(0, 4),
        ),
      ];
}
