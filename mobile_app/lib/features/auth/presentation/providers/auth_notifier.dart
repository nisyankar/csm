import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/api/api_client.dart';
import '../../data/models/auth_response.dart';
import '../../domain/repositories/auth_repository.dart';
import 'auth_state.dart';

class AuthNotifier extends StateNotifier<AuthState> {
  final AuthRepository _authRepository;

  AuthNotifier(this._authRepository) : super(AuthState.initial()) {
    _checkAuthStatus();
  }

  // Check if user is already logged in
  Future<void> _checkAuthStatus() async {
    state = AuthState.loading();
    try {
      final isLoggedIn = await _authRepository.isLoggedIn();
      if (isLoggedIn) {
        final user = await _authRepository.getStoredUser();
        if (user != null) {
          state = AuthState.authenticated(user);
        } else {
          state = AuthState.unauthenticated();
        }
      } else {
        state = AuthState.unauthenticated();
      }
    } catch (e) {
      state = AuthState.unauthenticated();
    }
  }

  // Login
  Future<bool> login(String email, String password, String deviceName) async {
    state = AuthState.loading();
    try {
      final request = LoginRequest(
        email: email,
        password: password,
        deviceName: deviceName,
      );

      final authResponse = await _authRepository.login(request);
      state = AuthState.authenticated(authResponse.user);
      return true;
    } on ApiException catch (e) {
      state = AuthState.error(e.message);
      return false;
    } catch (e) {
      state = AuthState.error('Giriş yapılamadı: ${e.toString()}');
      return false;
    }
  }

  // Logout
  Future<void> logout() async {
    try {
      await _authRepository.logout();
    } finally {
      state = AuthState.unauthenticated();
    }
  }

  // Logout from all devices
  Future<void> logoutAll() async {
    try {
      await _authRepository.logoutAll();
    } finally {
      state = AuthState.unauthenticated();
    }
  }

  // Refresh user data
  Future<void> refreshUser() async {
    try {
      final user = await _authRepository.getMe();
      state = AuthState.authenticated(user);
    } on ApiException catch (e) {
      if (e.isUnauthorized) {
        state = AuthState.unauthenticated();
      }
    } catch (e) {
      // Keep current state if refresh fails
    }
  }

  // Change password
  Future<bool> changePassword(
    String currentPassword,
    String newPassword,
    String newPasswordConfirmation,
  ) async {
    try {
      final request = ChangePasswordRequest(
        currentPassword: currentPassword,
        newPassword: newPassword,
        newPasswordConfirmation: newPasswordConfirmation,
      );

      await _authRepository.changePassword(request);
      return true;
    } catch (e) {
      return false;
    }
  }

  // Clear error
  void clearError() {
    if (state.hasError) {
      state = state.copyWith(
        status: AuthStatus.unauthenticated,
        errorMessage: null,
      );
    }
  }
}
