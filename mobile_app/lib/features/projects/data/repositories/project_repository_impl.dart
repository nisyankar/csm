import '../../../../core/api/api_client.dart';
import '../../domain/entities/project.dart';
import '../../domain/repositories/project_repository.dart';
import '../models/project_model.dart';

/// Implementation of ProjectRepository
/// Makes HTTP requests to Laravel backend API
class ProjectRepositoryImpl implements ProjectRepository {
  final ApiClient _apiClient;

  ProjectRepositoryImpl(this._apiClient);

  @override
  Future<List<Project>> getProjects({
    String? search,
    String? status,
    String? type,
    String? city,
    int? projectManagerId,
    String? priority,
    String? filter,
    String sort = 'created_at',
    String direction = 'desc',
    int page = 1,
    int perPage = 20,
  }) async {
    final queryParams = <String, dynamic>{
      'page': page,
      'per_page': perPage,
      'sort': sort,
      'direction': direction,
    };

    if (search != null) queryParams['search'] = search;
    if (status != null) queryParams['status'] = status;
    if (type != null) queryParams['type'] = type;
    if (city != null) queryParams['city'] = city;
    if (projectManagerId != null) {
      queryParams['project_manager_id'] = projectManagerId;
    }
    if (priority != null) queryParams['priority'] = priority;
    if (filter != null) queryParams['filter'] = filter;

    final response = await _apiClient.get(
      '/v1/projects',
      queryParameters: queryParams,
    );

    if (response.data['success'] == true) {
      final List projectsJson = response.data['data']['data'] as List;
      return projectsJson
          .map((json) => ProjectModel.fromJson(json as Map<String, dynamic>))
          .toList();
    }

    throw ApiException(
      message: response.data['message'] ?? 'Projeler yüklenemedi',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<Project> getProject(int id) async {
    final response = await _apiClient.get('/v1/projects/$id');

    if (response.data['success'] == true) {
      return ProjectModel.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    }

    throw ApiException(
      message: response.data['message'] ?? 'Proje yüklenemedi',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<Map<String, dynamic>> getProjectStats() async {
    final response = await _apiClient.get('/v1/projects/stats');

    if (response.data['success'] == true) {
      return response.data['data'] as Map<String, dynamic>;
    }

    throw ApiException(
      message: response.data['message'] ?? 'İstatistikler yüklenemedi',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<Project> createProject(Map<String, dynamic> data) async {
    final response = await _apiClient.post('/v1/projects', data: data);

    if (response.data['success'] == true) {
      return ProjectModel.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    }

    throw ApiException(
      message: response.data['message'] ?? 'Proje oluşturulamadı',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<Project> updateProject(int id, Map<String, dynamic> data) async {
    final response = await _apiClient.put('/v1/projects/$id', data: data);

    if (response.data['success'] == true) {
      return ProjectModel.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    }

    throw ApiException(
      message: response.data['message'] ?? 'Proje güncellenemedi',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<void> deleteProject(int id) async {
    final response = await _apiClient.delete('/v1/projects/$id');

    if (response.data['success'] != true) {
      throw ApiException(
        message: response.data['message'] ?? 'Proje silinemedi',
        statusCode: response.statusCode ?? 0,
      );
    }
  }

  @override
  Future<Project> updateProjectStatus(
    int id, {
    required String status,
    String? statusReason,
  }) async {
    final data = <String, dynamic>{
      'status': status,
      if (statusReason != null) 'status_reason': statusReason,
    };

    final response =
        await _apiClient.put('/v1/projects/$id/status', data: data);

    if (response.data['success'] == true) {
      return ProjectModel.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    }

    throw ApiException(
      message: response.data['message'] ?? 'Durum güncellenemedi',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<void> assignEmployee({
    required int projectId,
    required int employeeId,
    required String roleInProject,
    required String assignmentType,
    required int workPercentage,
    required DateTime startDate,
    DateTime? plannedEndDate,
    double? projectDailyRate,
    double? projectHourlyRate,
    String? responsibilities,
    bool setAsCurrent = false,
  }) async {
    final data = <String, dynamic>{
      'employee_id': employeeId,
      'role_in_project': roleInProject,
      'assignment_type': assignmentType,
      'work_percentage': workPercentage,
      'start_date': startDate.toIso8601String(),
      if (plannedEndDate != null)
        'planned_end_date': plannedEndDate.toIso8601String(),
      if (projectDailyRate != null) 'project_daily_rate': projectDailyRate,
      if (projectHourlyRate != null) 'project_hourly_rate': projectHourlyRate,
      if (responsibilities != null) 'responsibilities': responsibilities,
      'set_as_current': setAsCurrent,
    };

    final response = await _apiClient.post(
      '/v1/projects/$projectId/assign-employee',
      data: data,
    );

    if (response.data['success'] != true) {
      throw ApiException(
        message: response.data['message'] ?? 'Çalışan atanamadı',
        statusCode: response.statusCode ?? 0,
      );
    }
  }

  @override
  Future<void> removeEmployee({
    required int projectId,
    required int employeeId,
    String? endReason,
  }) async {
    final data = <String, dynamic>{
      'employee_id': employeeId,
      if (endReason != null) 'end_reason': endReason,
    };

    final response = await _apiClient.post(
      '/v1/projects/$projectId/remove-employee',
      data: data,
    );

    if (response.data['success'] != true) {
      throw ApiException(
        message: response.data['message'] ?? 'Çalışan çıkarılamadı',
        statusCode: response.statusCode ?? 0,
      );
    }
  }
}
