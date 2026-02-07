class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final int? organizationId;
  final bool isActive;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.organizationId,
    required this.isActive,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      role: json['role'],
      organizationId: json['organization_id'],
      isActive: json['is_active'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'organization_id': organizationId,
      'is_active': isActive,
    };
  }

  bool get isAdmin => role == 'admin';
  bool get isResponsable => role == 'responsable';
  bool get isGestionnaire => role == 'gestionnaire';
  bool get isTeamLeader => role == 'team_leader';
  bool get isOuvrier => role == 'ouvrier';
  
  // Manager includes both responsable and gestionnaire
  bool get isManager => isResponsable || isGestionnaire;
}
