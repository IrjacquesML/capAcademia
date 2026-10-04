import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../api/api_client.dart';
import '../models/models.dart';

class SessionController extends ChangeNotifier {
  SessionController({ApiClient? api}) : api = api ?? ApiClient();

  final ApiClient api;
  UserAccount? user;
  bool ready = false;
  String? error;

  Future<void> restore() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    if (token == null || token.isEmpty) {
      ready = true;
      notifyListeners();
      return;
    }
    api.token = token;
    try {
      final json = await api.get('/me');
      user = UserAccount.fromJson(json['user'] as Map<String, dynamic>);
    } on ApiException {
      await logout();
    } finally {
      ready = true;
      notifyListeners();
    }
  }

  Future<void> login(String email, String password) async {
    error = null;
    notifyListeners();
    try {
      final json = await api.post('/login', {'email': email, 'password': password});
      api.token = json['token']?.toString();
      user = UserAccount.fromJson(json['user'] as Map<String, dynamic>);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', api.token ?? '');
    } on ApiException catch (e) {
      error = e.message;
      rethrow;
    } finally {
      notifyListeners();
    }
  }

  Future<void> logout() async {
    try {
      if (api.token != null) {
        await api.post('/logout');
      }
    } catch (_) {
      // Déconnexion locale même si le réseau échoue.
    }
    api.token = null;
    user = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    notifyListeners();
  }
}
