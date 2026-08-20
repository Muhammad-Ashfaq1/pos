import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;
import 'package:pos_mobile/core/api/api_config.dart';
import 'package:pos_mobile/core/api/api_exception.dart';
import 'package:pos_mobile/core/auth/auth_session.dart';

class ApiClient {
  ApiClient({
    required this.session,
    http.Client? httpClient,
  }) : _http = httpClient ?? http.Client();

  final AuthSession session;
  final http.Client _http;

  static const _timeout = Duration(seconds: 20);

  Future<Map<String, dynamic>> get(
    String path, {
    bool auth = true,
  }) {
    return _send(
      method: 'GET',
      path: path,
      auth: auth,
    );
  }

  Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? body,
    bool auth = false,
  }) {
    return _send(
      method: 'POST',
      path: path,
      body: body,
      auth: auth,
    );
  }

  Future<Map<String, dynamic>> _send({
    required String method,
    required String path,
    Map<String, dynamic>? body,
    required bool auth,
  }) async {
    final uri = Uri.parse('${ApiConfig.baseUrl}${ApiConfig.prefix}$path');
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    if (auth) {
      final token = session.token;
      if (token == null || token.isEmpty) {
        throw const ApiException(
          message: 'Please sign in again.',
          statusCode: 401,
        );
      }
      headers['Authorization'] = 'Bearer $token';
    }

    try {
      late final http.Response response;
      if (method == 'GET') {
        response = await _http.get(uri, headers: headers).timeout(_timeout);
      } else {
        response = await _http
            .post(
              uri,
              headers: headers,
              body: body == null ? null : jsonEncode(body),
            )
            .timeout(_timeout);
      }

      if (response.statusCode >= 200 && response.statusCode < 300) {
        if (response.body.isEmpty) return <String, dynamic>{};
        final decoded = jsonDecode(response.body);
        if (decoded is Map<String, dynamic>) return decoded;
        return <String, dynamic>{'data': decoded};
      }

      throw ApiException.fromResponse(response);
    } on ApiException {
      rethrow;
    } on SocketException {
      throw ApiException.network();
    } on TimeoutException {
      throw ApiException.network();
    } on http.ClientException {
      throw ApiException.network();
    }
  }
}
