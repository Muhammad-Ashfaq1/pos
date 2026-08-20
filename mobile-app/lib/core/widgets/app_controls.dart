import 'package:flutter/material.dart';
import 'package:pos_mobile/app/theme/app_spacing.dart';
import 'package:pos_mobile/app/theme/app_text_styles.dart';

class AppAuthHeader extends StatelessWidget {
  const AppAuthHeader({
    required this.title,
    this.subtitle,
    super.key,
  });

  final String title;
  final String? subtitle;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: AppTextStyles.h1),
        if (subtitle != null) ...[
          const SizedBox(height: AppSpacing.xs),
          Text(subtitle!, style: AppTextStyles.bodySecondary),
        ],
      ],
    );
  }
}
