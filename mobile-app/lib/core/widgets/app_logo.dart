import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:pos_mobile/app/theme/app_colors.dart';
import 'package:pos_mobile/app/theme/app_spacing.dart';
import 'package:pos_mobile/app/theme/app_text_styles.dart';

class AppLogo extends StatelessWidget {
  const AppLogo({
    this.compact = false,
    this.onDark = false,
    super.key,
  });

  final bool compact;
  final bool onDark;

  @override
  Widget build(BuildContext context) {
    final textStyle =
        onDark ? AppTextStyles.displayLogo : AppTextStyles.displayLogoBlue;
    final size = compact ? 28.0 : 36.0;

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        SvgPicture.asset(
          'assets/brand/autoserve.svg',
          width: size,
          height: size,
        ),
        const SizedBox(width: AppSpacing.xs),
        Text(
          'AutoServe',
          style: textStyle.copyWith(fontSize: compact ? 20 : 26),
        ),
      ],
    );
  }
}

class DottedLoader extends StatefulWidget {
  const DottedLoader({
    this.color = AppColors.white,
    this.size = 36,
    super.key,
  });

  final Color color;
  final double size;

  @override
  State<DottedLoader> createState() => _DottedLoaderState();
}

class _DottedLoaderState extends State<DottedLoader>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    )..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: widget.size,
      height: widget.size,
      child: AnimatedBuilder(
        animation: _controller,
        builder: (context, _) {
          return CustomPaint(
            painter: _DottedLoaderPainter(
              progress: _controller.value,
              color: widget.color,
            ),
          );
        },
      ),
    );
  }
}

class _DottedLoaderPainter extends CustomPainter {
  _DottedLoaderPainter({required this.progress, required this.color});

  final double progress;
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = size.width / 2 - 3;
    const count = 8;
    final activeIndex = (progress * count).floor() % count;

    for (var i = 0; i < count; i++) {
      final angle = (i / count) * math.pi * 2 - math.pi / 2;
      final distance =
          math.min((i - activeIndex).abs(), count - (i - activeIndex).abs());
      final opacity = (1 - (distance / (count / 2))).clamp(0.25, 1.0);
      final dx = center.dx + radius * math.cos(angle);
      final dy = center.dy + radius * math.sin(angle);
      canvas.drawCircle(
        Offset(dx, dy),
        3.2,
        Paint()..color = color.withValues(alpha: opacity),
      );
    }
  }

  @override
  bool shouldRepaint(covariant _DottedLoaderPainter oldDelegate) {
    return oldDelegate.progress != progress || oldDelegate.color != color;
  }
}
