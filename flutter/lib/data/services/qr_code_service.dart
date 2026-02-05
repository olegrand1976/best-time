import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';
import '../models/project.dart';
import 'auth_service.dart';

class QRCodeService {
  final AuthService _authService = AuthService();

  Future<Map<String, dynamic>> validateQRCode(String qrData) async {
    try {
      // Parse QR code data
      Map<String, dynamic> qrJson;
      try {
        qrJson = jsonDecode(qrData);
      } catch (e) {
        return {'success': false, 'message': 'Invalid QR code format'};
      }

      // Validate QR code type
      if (qrJson['type'] != AppConfig.qrCodeType) {
        return {'success': false, 'message': 'Invalid QR code type'};
      }

      final token = await _authService.getToken();
      if (token == null) {
        return {'success': false, 'message': 'Not authenticated'};
      }

      // Validate with backend
      final response = await http.post(
        Uri.parse('${AppConfig.apiBaseUrl}/qr-codes/validate'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({'qr_code_token': qrJson['token']}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['valid'] == true) {
          final project = Project.fromJson(data['project']);
          return {'success': true, 'project': project};
        } else {
          return {'success': false, 'message': data['message'] ?? 'Invalid QR code'};
        }
      } else {
        return {'success': false, 'message': 'QR code validation failed'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }
}
