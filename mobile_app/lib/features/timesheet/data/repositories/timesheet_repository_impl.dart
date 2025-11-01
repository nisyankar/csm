import '../../../../core/api/api_client.dart';
import '../../domain/entities/timesheet.dart';
import '../../domain/repositories/timesheet_repository.dart';
import '../models/timesheet_model.dart';

class TimesheetRepositoryImpl implements TimesheetRepository {
  final ApiClient apiClient;

  TimesheetRepositoryImpl({required this.apiClient});

  @override
  Future<List<Timesheet>> getTimesheets({
    int page = 1,
    int perPage = 15,
    int? employeeId,
    int? projectId,
    String? date,
    String? startDate,
    String? endDate,
    String? status,
  }) async {
    final queryParams = <String, dynamic>{
      'page': page,
      'per_page': perPage,
      if (employeeId != null) 'employee_id': employeeId,
      if (projectId != null) 'project_id': projectId,
      if (date != null) 'date': date,
      if (startDate != null) 'start_date': startDate,
      if (endDate != null) 'end_date': endDate,
      if (status != null) 'status': status,
    };

    final response = await apiClient.get(
      '/v1/mobile/timesheets',
      queryParameters: queryParams,
    );

    final List<dynamic> data = response.data['data'] as List<dynamic>;
    return data
        .map((json) => TimesheetModel.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  @override
  Future<Map<String, dynamic>> getTodayStatus() async {
    final response = await apiClient.get('/v1/mobile/timesheet/today-status');
    return response.data['data'] as Map<String, dynamic>;
  }

  @override
  Future<Timesheet> clockIn({
    required int projectId,
    String? checkInMethod,
    Map<String, double>? location,
    String? notes,
  }) async {
    final data = <String, dynamic>{
      'project_id': projectId,
      if (checkInMethod != null) 'check_in_method': checkInMethod,
      if (location != null)
        'check_in_location': {
          'latitude': location['latitude'],
          'longitude': location['longitude'],
        },
      if (notes != null) 'notes': notes,
    };

    final response = await apiClient.post('/v1/mobile/timesheet/clock-in', data: data);
    final timesheetData = response.data['data'] as Map<String, dynamic>;
    return TimesheetModel.fromJson(timesheetData);
  }

  @override
  Future<Timesheet> clockOut({
    int? timesheetId,
    String? checkOutMethod,
    Map<String, double>? location,
    String? notes,
  }) async {
    final data = <String, dynamic>{
      if (timesheetId != null) 'timesheet_id': timesheetId,
      if (checkOutMethod != null) 'check_out_method': checkOutMethod,
      if (location != null)
        'check_out_location': {
          'latitude': location['latitude'],
          'longitude': location['longitude'],
        },
      if (notes != null) 'notes': notes,
    };

    final response = await apiClient.post('/v1/mobile/timesheet/clock-out', data: data);
    final timesheetData = response.data['data'] as Map<String, dynamic>;
    return TimesheetModel.fromJson(timesheetData);
  }

  @override
  Future<Map<String, dynamic>> getWeekSummary() async {
    final response = await apiClient.get('/v1/mobile/timesheet/week-summary');
    return response.data['data'] as Map<String, dynamic>;
  }

  @override
  Future<Map<String, dynamic>> getMonthSummary({
    required int year,
    required int month,
  }) async {
    final response = await apiClient.get(
      '/v1/mobile/timesheet/month-summary',
      queryParameters: {
        'year': year,
        'month': month,
      },
    );
    return response.data['data'] as Map<String, dynamic>;
  }

  @override
  Future<Timesheet> getTimesheetById(int id) async {
    final response = await apiClient.get('/v1/mobile/timesheet/$id');
    final timesheetData = response.data['data'] as Map<String, dynamic>;
    return TimesheetModel.fromJson(timesheetData);
  }

  @override
  Future<Map<String, dynamic>> syncOfflineData(
    List<Map<String, dynamic>> timesheets,
  ) async {
    final response = await apiClient.post(
      '/v1/mobile/sync/timesheets',
      data: {'timesheets': timesheets},
    );
    return response.data['data'] as Map<String, dynamic>;
  }
}
