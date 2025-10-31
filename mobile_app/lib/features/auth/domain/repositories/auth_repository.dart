import '../../data/models/auth_response.dart';
import '../entities/user.dart';

abstract class AuthRepository {
  Future<AuthResponse> login(LoginRequest request);
  Future<void> logout();
  Future<void> logoutAll();
  Future<User> getMe();
  Future<void> changePassword(ChangePasswordRequest request);
  Future<String?> getStoredToken();
  Future<User?> getStoredUser();
  Future<bool> isLoggedIn();
}
