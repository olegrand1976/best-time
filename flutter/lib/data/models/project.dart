class Project {
  final int id;
  final String name;
  final String? client;
  final String status;
  final double? latitude;
  final double? longitude;
  final int? geofenceRadius;
  final String? qrCodeToken;

  Project({
    required this.id,
    required this.name,
    this.client,
    required this.status,
    this.latitude,
    this.longitude,
    this.geofenceRadius,
    this.qrCodeToken,
  });

  factory Project.fromJson(Map<String, dynamic> json) {
    return Project(
      id: json['id'],
      name: json['name'],
      client: json['client'],
      status: json['status'],
      latitude: json['latitude'] != null ? double.parse(json['latitude'].toString()) : null,
      longitude: json['longitude'] != null ? double.parse(json['longitude'].toString()) : null,
      geofenceRadius: json['geofence_radius'],
      qrCodeToken: json['qr_code_token'],
    );
  }

  bool get hasLocation => latitude != null && longitude != null;
  bool get hasGeofence => hasLocation && geofenceRadius != null;
}
