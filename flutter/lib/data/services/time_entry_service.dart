import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';
import '../models/time_entry.dart';
import 'auth_service.dart';

class TimeEntryService {
  final AuthService _authService = AuthService();

  Future<Map<String, dynamic>> getTimeEntries({String? filter}) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      String url = '${AppConfig.apiBaseUrl}/time-entries';
      if (filter != null) {
        url += '?$filter=true';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final entries = (data['data'] as List)
            .map((e) => TimeEntry.fromJson(e))
            .toList();
        return {'success': true, 'entries': entries};
      } else {
        return {'success': false, 'message': 'Failed to fetch time entries'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  Future<Map<String, dynamic>> clockIn({
    int? projectId,
    String? description,
    double? latitude,
    double? longitude,
    double? locationAccuracy,
    bool qrCodeScanned = false,
  }) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final body = <String, dynamic>{
        if (projectId != null) 'project_id': projectId,
        if (description != null) 'description': description,
        if (latitude != null) 'latitude': latitude,
        if (longitude != null) 'longitude': longitude,
        if (locationAccuracy != null) 'location_accuracy': locationAccuracy,
        'qr_code_scanned': qrCodeScanned,
      };

      final response = await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/time-entries/start'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode(body),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        final entry = TimeEntry.fromJson(data);
        return {'success': true, 'entry': entry};
      } else {
        final error = jsonDecode(response.body);
        return {'success': false, 'message': error['message'] ?? 'Clock in failed'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  Future<Map<String, dynamic>> clockOut() async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/time-entries/stop'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final entry = TimeEntry.fromJson(data);
        return {'success': true, 'entry': entry};
      } else {
        final error = jsonDecode(response.body);
        return {'success': false, 'message': error['message'] ?? 'Clock out failed'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  Future<Map<String, dynamic>> createManualEntry({
    required DateTime startTime,
    required DateTime endTime,
    int? projectId,
    String? description,
    double? latitude,
    double? longitude,
    double? locationAccuracy,
  }) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final body = <String, dynamic>{
        'start_time': startTime.toIso8601String(),
        'end_time': endTime.toIso8601String(),
        if (projectId != null) 'project_id': projectId,
        if (description != null) 'description': description,
        if (latitude != null) 'latitude': latitude,
        if (longitude != null) 'longitude': longitude,
        if (locationAccuracy != null) 'location_accuracy': locationAccuracy,
      };

      final response = await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/time-entries'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode(body),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        final entry = TimeEntry.fromJson(data);
        return {'success': true, 'entry': entry};
      } else {
        final error = jsonDecode(response.body);
        return {'success': false, 'message': error['message'] ?? 'Failed to create entry'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  Future<Map<String, dynamic>> deleteEntry(int id) async {
    try {
      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      final response = await http.delete(
        Uri.parse('${AppConfig.apiBaseUrl}/time-entries/$id'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        return {'success': true};
      } else {
        return {'success': false, 'message': 'Failed to delete entry'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }
}
