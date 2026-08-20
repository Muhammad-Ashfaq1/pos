import 'dart:io';

import 'package:flutter/foundation.dart';

/// Laravel origin used by `/api/v1/customer/*`.
///
/// Override at run time:
/// `flutter run --dart-define=API_BASE_URL=http://192.168.1.10:8000`
class ApiConfig {
  ApiConfig._();

  static const String prefix = '/api/v1/customer';

  static String get baseUrl {
    const fromDefine = String.fromEnvironment('API_BASE_URL');
    if (fromDefine.isNotEmpty) {
      return _trimSlash(fromDefine);
    }
    if (!kIsWeb && Platform.isAndroid) {
      return 'http://10.0.2.2:8000';
    }
    return 'http://127.0.0.1:8000';
  }

  static String get deviceName {
    if (kIsWeb) return 'flutter-web';
    if (Platform.isIOS) return 'flutter-ios';
    if (Platform.isAndroid) return 'flutter-android';
    return 'flutter';
  }

  static String _trimSlash(String value) {
    return value.endsWith('/') ? value.substring(0, value.length - 1) : value;
  }
}
