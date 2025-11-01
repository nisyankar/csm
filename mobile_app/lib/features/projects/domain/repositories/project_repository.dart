import '../../domain/entities/project.dart';

/// Abstract repository interface for Project operations
/// Follows repository pattern for clean architecture
abstract class ProjectRepository {
  /// Get list of projects with optional filtering
  Future<List<Project>> getProjects({
    String? search,
    String? status,
    String? type,
    String? city,
    int? projectManagerId,
    String? priority,
    String? filter, // delayed, over_budget, no_manager, ending_soon
    String sort = 'created_at',
    String direction = 'desc',
    int page = 1,
    int perPage = 20,
  });

  /// Get single project by ID
  Future<Project> getProject(int id);

  /// Get project statistics
  Future<Map<String, dynamic>> getProjectStats();

  /// Create new project
  Future<Project> createProject(Map<String, dynamic> data);

  /// Update existing project
  Future<Project> updateProject(int id, Map<String, dynamic> data);

  /// Delete project
  Future<void> deleteProject(int id);

  /// Update project status
  Future<Project> updateProjectStatus(
    int id, {
    required String status,
    String? statusReason,
  });

  /// Assign employee to project
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
  });

  /// Remove employee from project
  Future<void> removeEmployee({
    required int projectId,
    required int employeeId,
    String? endReason,
  });
}
