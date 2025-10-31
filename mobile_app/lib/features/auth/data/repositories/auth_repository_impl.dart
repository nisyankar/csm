import 'dart:convert';
import '../../../../core/api/api_client.dart';
import '../../../../core/constants/api_constants.dart';
import '../../../../core/storage/secure_storage.dart';
import '../../domain/entities/user.dart';
import '../../domain/repositories/auth_repository.dart';
import '../models/auth_response.dart';

class AuthRepositoryImpl implements AuthRepository {
  final ApiClient _apiClient;
  final SecureStorage _storage;

  AuthRepositoryImpl(this._apiClient, this._storage);

  @override
  Future<AuthResponse> login(LoginRequest request) async {
    try {
      final response = await _apiClient.post(
        ApiConstants.login,
        data: request.toJson(),
      );

      if (response.data['success'] == true) {
        final authResponse = AuthResponse.fromJson(response.data['data']);

        // Save token and user data
        await _storage.saveToken(authResponse.token);
        await _storage.saveUserData(jsonEncode(authResponse.user.toJson()));
        await _storage.saveDeviceName(request.deviceName);

        return authResponse;
      } else {
        throw ApiException(
          message: response.data['message'] ?? 'Giriş başarısız',
          statusCode: response.statusCode ?? 0,
        );
      }
    } catch (e) {
      rethrow;
    }
  }

  @override
  Future<void> logout() async {
    try {
      await _apiClient.post(ApiConstants.logout);
    } catch (e) {
      // Continue with local logout even if API call fails
    } finally {
      await _storage.clearAll();
    }
  }

  @override
  Future<void> logoutAll() async {
    try {
      await _apiClient.post(ApiConstants.logoutAll);
    } catch (e) {
      // Continue with local logout even if API call fails
    } finally {
      await _storage.clearAll();
    }
  }

  @override
  Future<User> getMe() async {
    try {
      final response = await _apiClient.get(ApiConstants.me);

      if (response.data['success'] == true) {
        final user = User.fromJson(response.data['data']);
        await _storage.saveUserData(jsonEncode(user.toJson()));
        return user;
      } else {
        throw ApiException(
          message: response.data['message'] ?? 'Kullanıcı bilgisi alınamadı',
          statusCode: response.statusCode ?? 0,
        );
      }
    } catch (e) {
      rethrow;
    }
  }

  @override
  Future<void> changePassword(ChangePasswordRequest request) async {
    try {
      final response = await _apiClient.post(
        ApiConstants.changePassword,
        data: request.toJson(),
      );

      if (response.data['success'] != true) {
        throw ApiException(
          message: response.data['message'] ?? 'Şifre değiştirilemedi',
          statusCode: response.statusCode ?? 0,
        );
      }
    } catch (e) {
      rethrow;
    }
  }

  @override
  Future<String?> getStoredToken() async {
    return await _storage.getToken();
  }

  @override
  Future<User?> getStoredUser() async {
    final userData = await _storage.getUserData();
    if (userData != null) {
      try {
        return User.fromJson(jsonDecode(userData));
      } catch (e) {
        return null;
      }
    }
    return null;
  }

  @override
  Future<bool> isLoggedIn() async {
    final token = await _storage.getToken();
    return token != null && token.isNotEmpty;
  }
}
