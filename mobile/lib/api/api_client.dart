import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

class ApiException implements Exception {
  ApiException(this.message, {this.statusCode});

  final String message;
  final int? statusCode;

  @override
  String toString() => message;
}

class ApiClient {
  ApiClient({String? baseUrl, http.Client? httpClient})
      : baseUrl = _normalize(baseUrl ?? _defaultBaseUrl()),
        _http = httpClient ?? http.Client();

  final String baseUrl;
  final http.Client _http;
  String? token;

  static String _defaultBaseUrl() {
    const fromEnv = String.fromEnvironment('API_BASE_URL');
    if (fromEnv.isNotEmpty) {
      return fromEnv;
    }
    if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000';
    }
    return 'http://127.0.0.1:8000';
  }

  static String _normalize(String url) => url.endsWith('/') ? url.substring(0, url.length - 1) : url;

  Future<Map<String, dynamic>> get(String path) => _send('GET', path);

  Future<Map<String, dynamic>> post(String path, [Map<String, dynamic>? body]) =>
      _send('POST', path, body);

  Future<Map<String, dynamic>> _send(String method, String path, [Map<String, dynamic>? body]) async {
    final uri = Uri.parse('$baseUrl/api$path');
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (token != null && token!.isNotEmpty) 'Authorization': 'Bearer $token',
    };

    final http.Response response;
    if (method == 'GET') {
      response = await _http.get(uri, headers: headers);
    } else {
      response = await _http.post(uri, headers: headers, body: jsonEncode(body ?? {}));
    }

    Map<String, dynamic> json = {};
    if (response.body.isNotEmpty) {
      final decoded = jsonDecode(response.body);
      if (decoded is Map<String, dynamic>) {
        json = decoded;
      }
    }

    if (response.statusCode >= 400) {
      final message = json['message']?.toString() ??
          (json['errors'] is Map ? (json['errors'] as Map).values.first.toString() : null) ??
          'Erreur ${response.statusCode}';
      throw ApiException(message, statusCode: response.statusCode);
    }

    return json;
  }
}
