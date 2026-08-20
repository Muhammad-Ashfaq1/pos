import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

/// Shared navigation timing for smooth screen changes.
class AppTransitions {
  AppTransitions._();

  static const Duration page = Duration(milliseconds: 320);
  static const Duration pageReverse = Duration(milliseconds: 280);
  static const Duration fade = Duration(milliseconds: 420);
  static const Duration tab = Duration(milliseconds: 240);
  static const Duration content = Duration(milliseconds: 220);

  /// Cupertino-style slide used app-wide via [PageTransitionsTheme].
  static const PageTransitionsTheme pageTheme = PageTransitionsTheme(
    builders: {
      TargetPlatform.android: CupertinoPageTransitionsBuilder(),
      TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
      TargetPlatform.macOS: CupertinoPageTransitionsBuilder(),
      TargetPlatform.windows: _FadeSlidePageTransitionsBuilder(),
      TargetPlatform.linux: _FadeSlidePageTransitionsBuilder(),
    },
  );

  /// Soft fade replace (splash → login/home, auth → home).
  static Future<T?> fadeReplace<T extends Object?>(
    BuildContext context,
    Widget page, {
    String? routeName,
  }) {
    return Navigator.of(context).pushReplacement(
      PageRouteBuilder<T>(
        settings: RouteSettings(name: routeName),
        transitionDuration: fade,
        reverseTransitionDuration: pageReverse,
        pageBuilder: (context, animation, secondaryAnimation) => page,
        transitionsBuilder: (context, animation, secondaryAnimation, child) {
          final curved = CurvedAnimation(
            parent: animation,
            curve: Curves.easeOutCubic,
            reverseCurve: Curves.easeInCubic,
          );
          return FadeTransition(opacity: curved, child: child);
        },
      ),
    );
  }
}

class _FadeSlidePageTransitionsBuilder extends PageTransitionsBuilder {
  const _FadeSlidePageTransitionsBuilder();

  @override
  Widget buildTransitions<T>(
    PageRoute<T> route,
    BuildContext context,
    Animation<double> animation,
    Animation<double> secondaryAnimation,
    Widget child,
  ) {
    final curved = CurvedAnimation(
      parent: animation,
      curve: Curves.easeOutCubic,
      reverseCurve: Curves.easeInCubic,
    );
    return FadeTransition(
      opacity: curved,
      child: SlideTransition(
        position: Tween<Offset>(
          begin: const Offset(0.04, 0.02),
          end: Offset.zero,
        ).animate(curved),
        child: child,
      ),
    );
  }
}
