class AppConfig {
  // API Configuration
  static const String apiBaseUrl = 'http://localhost:8000/api';
  
  // App Configuration
  static const String appName = 'Best Time';
  static const String appVersion = '1.0.0';
  
  // Storage Keys
  static const String authTokenKey = 'auth_token';
  static const String userDataKey = 'user_data';
  static const String languageKey = 'language';
  
  // Geolocation
  static const double defaultGeofenceRadius = 100.0; // meters
  static const int locationTimeoutSeconds = 30;
  
  // QR Code
  static const String qrCodeType = 'best_time_project';
  
  // Pagination
  static const int defaultPageSize = 50;
}
