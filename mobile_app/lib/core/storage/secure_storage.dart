import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../constants/api_constants.dart';

class SecureStorage {
  static final SecureStorage _instance = SecureStorage._internal();
  factory SecureStorage() => _instance;
  SecureStorage._internal();

  final FlutterSecureStorage _storage = const FlutterSecureStorage(
    aOptions: AndroidOptions(
      encryptedSharedPreferences: true,
    ),
    iOptions: IOSOptions(
      accessibility: KeychainAccessibility.first_unlock,
    ),
  );

  // Token Management
  Future<void> saveToken(String token) async {
    await _storage.write(key: ApiConstants.tokenKey, value: token);
  }

  Future<String?> getToken() async {
    return await _storage.read(key: ApiConstants.tokenKey);
  }

  Future<void> deleteToken() async {
    await _storage.delete(key: ApiConstants.tokenKey);
  }

  // User Data
  Future<void> saveUserData(String userData) async {
    await _storage.write(key: ApiConstants.userKey, value: userData);
  }

  Future<String?> getUserData() async {
    return await _storage.read(key: ApiConstants.userKey);
  }

  Future<void> deleteUserData() async {
    await _storage.delete(key: ApiConstants.userKey);
  }

  // Device Name
  Future<void> saveDeviceName(String deviceName) async {
    await _storage.write(key: ApiConstants.deviceNameKey, value: deviceName);
  }

  Future<String?> getDeviceName() async {
    return await _storage.read(key: ApiConstants.deviceNameKey);
  }

  // Clear All
  Future<void> clearAll() async {
    await _storage.deleteAll();
  }

  // Generic Methods
  Future<void> write(String key, String value) async {
    await _storage.write(key: key, value: value);
  }

  Future<String?> read(String key) async {
    return await _storage.read(key: key);
  }

  Future<void> delete(String key) async {
    await _storage.delete(key: key);
  }

  Future<bool> containsKey(String key) async {
    return await _storage.containsKey(key: key);
  }

  Future<Map<String, String>> readAll() async {
    return await _storage.readAll();
  }
}
