class TimeEntry {
  final int id;
  final int userId;
  final int? projectId;
  final DateTime startTime;
  final DateTime? endTime;
  final String? description;
  final int? duration;
  final double? latitude;
  final double? longitude;
  final double? locationAccuracy;
  final DateTime? locationCapturedAt;
  final bool qrCodeScanned;
  final String? projectName;
  final bool isActive;

  TimeEntry({
    required this.id,
    required this.userId,
    this.projectId,
    required this.startTime,
    this.endTime,
    this.description,
    this.duration,
    this.latitude,
    this.longitude,
    this.locationAccuracy,
    this.locationCapturedAt,
    required this.qrCodeScanned,
    this.projectName,
    required this.isActive,
  });

  factory TimeEntry.fromJson(Map<String, dynamic> json) {
    return TimeEntry(
      id: json['id'],
      userId: json['user_id'],
      projectId: json['project_id'],
      startTime: DateTime.parse(json['start_time']),
      endTime: json['end_time'] != null ? DateTime.parse(json['end_time']) : null,
      description: json['description'],
      duration: json['duration'],
      latitude: json['latitude'] != null ? double.parse(json['latitude'].toString()) : null,
      longitude: json['longitude'] != null ? double.parse(json['longitude'].toString()) : null,
      locationAccuracy: json['location_accuracy'] != null ? double.parse(json['location_accuracy'].toString()) : null,
      locationCapturedAt: json['location_captured_at'] != null ? DateTime.parse(json['location_captured_at']) : null,
      qrCodeScanned: json['qr_code_scanned'] ?? false,
      projectName: json['project']?['name'],
      isActive: json['is_active'] ?? false,
    );
  }

  String get durationFormatted {
    if (duration == null) return '--:--:--';
    final hours = duration! ~/ 3600;
    final minutes = (duration! % 3600) ~/ 60;
    final seconds = duration! % 60;
    return '${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  bool get hasLocation => latitude != null && longitude != null;
}
