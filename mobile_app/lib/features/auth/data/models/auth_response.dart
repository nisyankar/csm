import '../../domain/entities/user.dart';

class AuthResponse {
  final String token;
  final String tokenType;
  final User user;

  AuthResponse({
    required this.token,
    required this.tokenType,
    required this.user,
  });

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    return AuthResponse(
      token: json['token'] as String,
      tokenType: json['token_type'] as String? ?? 'Bearer',
      user: User.fromJson(json['user'] as Map<String, dynamic>),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'token': token,
      'token_type': tokenType,
      'user': user.toJson(),
    };
  }
}

class LoginRequest {
  final String email;
  final String password;
  final String deviceName;

  LoginRequest({
    required this.email,
    required this.password,
    required this.deviceName,
  });

  Map<String, dynamic> toJson() {
    return {
      'email': email,
      'password': password,
      'device_name': deviceName,
    };
  }
}

class ChangePasswordRequest {
  final String currentPassword;
  final String newPassword;
  final String newPasswordConfirmation;

  ChangePasswordRequest({
    required this.currentPassword,
    required this.newPassword,
    required this.newPasswordConfirmation,
  });

  Map<String, dynamic> toJson() {
    return {
      'current_password': currentPassword,
      'new_password': newPassword,
      'new_password_confirmation': newPasswordConfirmation,
    };
  }
}
