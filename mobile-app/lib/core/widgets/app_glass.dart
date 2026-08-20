import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:pos_mobile/app/theme/app_colors.dart';
import 'package:pos_mobile/app/theme/app_radius.dart';
import 'package:pos_mobile/app/theme/app_spacing.dart';
import 'package:pos_mobile/app/theme/app_text_styles.dart';

/// Tones from web `pos-tone-*` (lake theme RGB).
enum AppGlassTone { primary, success, warning, info, secondary }

extension AppGlassToneStyle on AppGlassTone {
  Color get color {
    switch (this) {
      case AppGlassTone.primary:
        return AppColors.primary;
      case AppGlassTone.success:
        return AppColors.success;
      case AppGlassTone.warning:
        return AppColors.warningOrange;
      case AppGlassTone.info:
        return AppColors.info;
      case AppGlassTone.secondary:
        return AppColors.secondaryTone;
    }
  }
}

/// Soft mesh behind glass cards — same idea as the portal page wash.
class AppGlassBackdrop extends StatelessWidget {
  const AppGlassBackdrop({required this.child, super.key});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [AppColors.pageWash, AppColors.page],
        ),
      ),
      child: Stack(
        children: [
          Positioned(
            top: -80,
            right: -60,
            child: _Blob(
              size: 280,
              color: AppColors.primary.withValues(alpha: 0.16),
            ),
          ),
          Positioned(
            top: 220,
            left: -90,
            child: _Blob(
              size: 220,
              color: AppColors.success.withValues(alpha: 0.10),
            ),
          ),
          Positioned(
            bottom: 40,
            right: -40,
            child: _Blob(
              size: 180,
              color: AppColors.info.withValues(alpha: 0.10),
            ),
          ),
          child,
        ],
      ),
    );
  }
}

class _Blob extends StatelessWidget {
  const _Blob({required this.size, required this.color});

  final double size;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          gradient: RadialGradient(
            colors: [color, color.withValues(alpha: 0)],
          ),
        ),
      ),
    );
  }
}

/// Translucent card matching `.pos-glass-card` + `.pos-tone-*`.
class AppGlassCard extends StatelessWidget {
  const AppGlassCard({
    required this.child,
    this.tone = AppGlassTone.primary,
    this.padding,
    this.onTap,
    super.key,
  });

  final Widget child;
  final AppGlassTone tone;
  final EdgeInsetsGeometry? padding;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final accent = tone.color;

    final body = Container(
      decoration: BoxDecoration(
        borderRadius: AppRadius.lgAll,
        boxShadow: [
          BoxShadow(
            color: accent.withValues(alpha: 0.28),
            blurRadius: 28,
            offset: const Offset(0, 12),
            spreadRadius: -8,
          ),
          BoxShadow(
            color: AppColors.textPrimary.withValues(alpha: 0.04),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: AppRadius.lgAll,
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
          child: DecoratedBox(
            decoration: BoxDecoration(
              borderRadius: AppRadius.lgAll,
              color: AppColors.white.withValues(alpha: 0.62),
              border: Border.all(color: AppColors.white.withValues(alpha: 0.85)),
            ),
            child: Stack(
              children: [
                Positioned.fill(
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [
                          accent.withValues(alpha: 0.14),
                          accent.withValues(alpha: 0.04),
                          AppColors.white.withValues(alpha: 0.18),
                        ],
                        stops: const [0, 0.45, 0.85],
                      ),
                    ),
                  ),
                ),
                Positioned(
                  top: -56,
                  right: -40,
                  child: IgnorePointer(
                    child: Container(
                      width: 160,
                      height: 160,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: RadialGradient(
                          colors: [
                            accent.withValues(alpha: 0.18),
                            accent.withValues(alpha: 0.05),
                            accent.withValues(alpha: 0),
                          ],
                          stops: const [0, 0.45, 0.7],
                        ),
                      ),
                    ),
                  ),
                ),
                Padding(
                  padding: padding ?? const EdgeInsets.all(AppSpacing.lg),
                  child: child,
                ),
              ],
            ),
          ),
        ),
      ),
    );

    if (onTap == null) return body;
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: AppRadius.lgAll,
        child: body,
      ),
    );
  }
}

class AppGlassIconTile extends StatelessWidget {
  const AppGlassIconTile({
    required this.icon,
    required this.tone,
    this.size = 40,
    super.key,
  });

  final IconData icon;
  final AppGlassTone tone;
  final double size;

  @override
  Widget build(BuildContext context) {
    final accent = tone.color;
    return Container(
      width: size,
      height: size,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        borderRadius: AppRadius.mdAll,
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [accent, accent.withValues(alpha: 0.82)],
        ),
        boxShadow: [
          BoxShadow(
            color: accent.withValues(alpha: 0.45),
            blurRadius: 12,
            offset: const Offset(0, 6),
            spreadRadius: -4,
          ),
        ],
      ),
      child: Icon(icon, color: AppColors.white, size: size * 0.48),
    );
  }
}

class AppGlassStat extends StatelessWidget {
  const AppGlassStat({
    required this.tone,
    required this.icon,
    required this.label,
    required this.value,
    this.note,
    super.key,
  });

  final AppGlassTone tone;
  final IconData icon;
  final String label;
  final String value;
  final String? note;

  @override
  Widget build(BuildContext context) {
    return AppGlassCard(
      tone: tone,
      padding: const EdgeInsets.all(AppSpacing.md),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              AppGlassIconTile(icon: icon, tone: tone, size: 36),
              const SizedBox(width: AppSpacing.sm),
              Expanded(
                child: Text(
                  label.toUpperCase(),
                  style: AppTextStyles.statLabel,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.sm),
          Text(
            value,
            style: AppTextStyles.h3.copyWith(fontSize: 20),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          if (note != null && note!.isNotEmpty) ...[
            const SizedBox(height: AppSpacing.xxs),
            Text(
              note!,
              style: AppTextStyles.bodySmall,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ],
      ),
    );
  }
}

class AppGlassPill extends StatelessWidget {
  const AppGlassPill({
    required this.label,
    this.tone = AppGlassTone.primary,
    super.key,
  });

  final String label;
  final AppGlassTone tone;

  @override
  Widget build(BuildContext context) {
    final accent = tone.color;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: accent.withValues(alpha: 0.12),
        borderRadius: AppRadius.fullAll,
        border: Border.all(color: accent.withValues(alpha: 0.22)),
      ),
      child: Text(
        label,
        style: AppTextStyles.bodySmall.copyWith(
          color: accent,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}
