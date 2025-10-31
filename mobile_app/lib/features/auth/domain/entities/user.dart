class User {
  final int id;
  final String name;
  final String email;
  final String userType;
  final String userTypeDisplay;
  final String? avatarUrl;
  final String? language;
  final String? timezone;
  final int? employeeId;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.userType,
    required this.userTypeDisplay,
    this.avatarUrl,
    this.language,
    this.timezone,
    this.employeeId,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      userType: json['user_type'] as String,
      userTypeDisplay: json['user_type_display'] as String,
      avatarUrl: json['avatar_url'] as String?,
      language: json['language'] as String?,
      timezone: json['timezone'] as String?,
      employeeId: json['employee_id'] as int?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'user_type': userType,
      'user_type_display': userTypeDisplay,
      'avatar_url': avatarUrl,
      'language': language,
      'timezone': timezone,
      'employee_id': employeeId,
    };
  }

  User copyWith({
    int? id,
    String? name,
    String? email,
    String? userType,
    String? userTypeDisplay,
    String? avatarUrl,
    String? language,
    String? timezone,
    int? employeeId,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      userType: userType ?? this.userType,
      userTypeDisplay: userTypeDisplay ?? this.userTypeDisplay,
      avatarUrl: avatarUrl ?? this.avatarUrl,
      language: language ?? this.language,
      timezone: timezone ?? this.timezone,
      employeeId: employeeId ?? this.employeeId,
    );
  }

  bool get isAdmin => userType == 'admin';
  bool get isManager => userType == 'project_manager' || userType == 'site_manager';
  bool get isEmployee => employeeId != null;
}
