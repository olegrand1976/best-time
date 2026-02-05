import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';
import '../models/project.dart';
import 'auth_service.dart';

class ProjectService {
  final AuthService _authService = AuthService();

  Future<Map<String, dynamic>> getProjects() async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.get(
        Uri.parse('${AppConfig.apiBaseUrl}/projects'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final projects = (data['data'] as List)
            .map((e) => Project.fromJson(e))
            .toList();
        return {'success': true, 'projects': projects};
      } else {
        return {'success': false, 'message': 'Failed to fetch projects'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }
}
