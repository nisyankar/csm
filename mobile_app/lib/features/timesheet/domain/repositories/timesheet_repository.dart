import '../entities/timesheet.dart';

abstract class TimesheetRepository {
  /// Puantaj listesi - sayfalama ile
  Future<List<Timesheet>> getTimesheets({
    int page = 1,
    int perPage = 15,
    int? employeeId,
    int? projectId,
    String? date,
    String? startDate,
    String? endDate,
    String? status,
  });

  /// Bugünkü durum
  Future<Map<String, dynamic>> getTodayStatus();

  /// Giriş yap (Check-in)
  Future<Timesheet> clockIn({
    required int projectId,
    String? checkInMethod,
    Map<String, double>? location,
    String? notes,
  });

  /// Çıkış yap (Check-out)
  Future<Timesheet> clockOut({
    int? timesheetId,
    String? checkOutMethod,
    Map<String, double>? location,
    String? notes,
  });

  /// Haftalık özet
  Future<Map<String, dynamic>> getWeekSummary();

  /// Aylık özet
  Future<Map<String, dynamic>> getMonthSummary({
    required int year,
    required int month,
  });

  /// Puantaj detayı
  Future<Timesheet> getTimesheetById(int id);

  /// Offline senkronizasyon
  Future<Map<String, dynamic>> syncOfflineData(
    List<Map<String, dynamic>> timesheets,
  );
}
