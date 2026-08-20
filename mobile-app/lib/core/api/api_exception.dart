import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiException implements Exception {
  const ApiException({
    required this.message,
    this.statusCode,
    this.errors = const {},
  });

  final String message;
  final int? statusCode;
  final Map<String, List<String>> errors;

  bool get isUnauthenticated => statusCode == 401;

  String? fieldError(String field) {
    final messages = errors[field];
    if (messages == null || messages.isEmpty) return null;
    return messages.first;
  }

  factory ApiException.fromResponse(http.Response response) {
    Map<String, dynamic> body = const {};
    try {
      final decoded = jsonDecode(response.body);
      if (decoded is Map<String, dynamic>) {
        body = decoded;
      }
    } catch (_) {
      // Non-JSON error page.
    }

    final errors = <String, List<String>>{};
    final rawErrors = body['errors'];
    if (rawErrors is Map) {
      rawErrors.forEach((key, value) {
        if (value is List) {
          errors[key.toString()] = value.map((item) => item.toString()).toList();
        } else if (value != null) {
          errors[key.toString()] = [value.toString()];
        }
      });
    }

    final message = body['message']?.toString() ??
        (errors.values.isNotEmpty
            ? errors.values.first.first
            : 'Something went wrong. Please try again.');

    return ApiException(
      message: message,
      statusCode: response.statusCode,
      errors: errors,
    );
  }

  factory ApiException.network() {
    return const ApiException(
      message: 'Could not reach the shop. Check that the API is running.',
    );
  }

  @override
  String toString() => message;
}
