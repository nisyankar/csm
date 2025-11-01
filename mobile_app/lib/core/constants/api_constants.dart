import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;

class ApiConstants {
  // Base URL - Automatically configured based on platform
  static String get baseUrl {
    if (kIsWeb) {
      // Web platform
      return 'http://localhost:8000/api';
    } else if (Platform.isAndroid) {
      // Android emulator (10.0.2.2 is the host machine's localhost from emulator's perspective)
      return 'http://10.0.2.2:8000/api';
    } else {
      // iOS Simulator and other platforms
      return 'http://localhost:8000/api';
    }
  }

  // Production URL - Uncomment and configure when deploying to production
  // static const String baseUrl = 'https://your-domain.com/api';

  static const String apiVersion = 'v1';

  // Endpoints
  static const String login = '/v1/auth/login';
  static const String logout = '/v1/auth/logout';
  static const String logoutAll = '/v1/auth/logout-all';
  static const String me = '/v1/auth/me';
  static const String refresh = '/v1/auth/refresh';
  static const String changePassword = '/v1/auth/change-password';
  static const String registerDevice = '/v1/auth/register-device';

  // Timesheet
  static const String clockIn = '/v1/mobile/timesheet/clock-in';
  static const String clockOut = '/v1/mobile/timesheet/clock-out';
  static const String todayStatus = '/v1/mobile/timesheet/today-status';
  static const String weekSummary = '/v1/mobile/timesheet/week-summary';
  static const String monthSummary = '/v1/mobile/timesheet/month-summary';
  static const String timesheets = '/v1/mobile/timesheets';
  static const String syncTimesheets = '/v1/mobile/sync/timesheets';

  // Progress Payments (Hakediş)
  static const String progressPayments = '/v1/progress-payments';
  static const String progressPaymentsPending = '/v1/progress-payments/pending-approvals';
  static const String progressPaymentsStatistics = '/v1/progress-payments/statistics';

  // Materials
  static const String materials = '/v1/materials';
  static const String materialsLowStock = '/v1/materials/low-stock';

  // Notifications
  static const String notifications = '/v1/notifications';
  static const String notificationsUnread = '/v1/notifications/unread';
  static const String notificationsUnreadCount = '/v1/notifications/unread-count';
  static const String notificationsMarkAllRead = '/v1/notifications/mark-all-read';
  static const String notificationsRegisterDevice = '/v1/notifications/register-device';

  // File Upload
  static const String fileUpload = '/v1/files/upload';
  static const String fileUploadImage = '/v1/files/upload-image';
  static const String fileUploadMultiple = '/v1/files/upload-multiple-images';
  static const String fileUploadAvatar = '/v1/files/upload-avatar';
  static const String fileUploadBase64 = '/v1/files/upload-base64';
  static const String fileDelete = '/v1/files/delete';

  // Timeout
  static const Duration connectTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
  static const Duration sendTimeout = Duration(seconds: 30);

  // Headers
  static const String contentTypeJson = 'application/json';
  static const String contentTypeMultipart = 'multipart/form-data';
  static const String authorizationHeader = 'Authorization';
  static const String bearerPrefix = 'Bearer';

  // Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';
  static const String deviceNameKey = 'device_name';
}
