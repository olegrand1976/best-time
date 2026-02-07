class Project {
  final int id;
  final String name;
  final String? client; // Deprecated, kept for backward compatibility
  final int? clientId;
  final String? clientName;
  final String status;
  final double? latitude;
  final double? longitude;
  final int? geofenceRadius;
  final String? qrCodeToken;

  Project({
    required this.id,
    required this.name,
    this.client,
    this.clientId,
    this.clientName,
    required this.status,
    this.latitude,
    this.longitude,
    this.geofenceRadius,
    this.qrCodeToken,
  });

  factory Project.fromJson(Map<String, dynamic> json) {
    // Handle client data - can be either old format (string) or new format (object with id)
    String? clientName;
    int? clientId;
    
    if (json['client'] != null) {
      if (json['client'] is String) {
        // Old format: client is a string
        clientName = json['client'];
      } else if (json['client'] is Map) {
        // New format: client is an object
        clientId = json['client']['id'];
        clientName = json['client']['name'];
      }
    }
    
    // Also check for client_id field directly
    if (json['client_id'] != null) {
      clientId = json['client_id'];
    }

    return Project(
      id: json['id'],
      name: json['name'],
      client: json['client'] is String ? json['client'] : null,
      clientId: clientId,
      clientName: clientName,
      status: json['status'],
      latitude: json['latitude'] != null ? double.parse(json['latitude'].toString()) : null,
      longitude: json['longitude'] != null ? double.parse(json['longitude'].toString()) : null,
      geofenceRadius: json['geofence_radius'],
      qrCodeToken: json['qr_code_token'],
    );
  }

  bool get hasLocation => latitude != null && longitude != null;
  bool get hasGeofence => hasLocation && geofenceRadius != null;
  
  // Helper to get client display name
  String get displayClientName => clientName ?? client ?? 'Sans client';
}
