import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/api/api_client.dart';
import '../../data/repositories/project_repository_impl.dart';
import '../../domain/repositories/project_repository.dart';
import 'projects_notifier.dart';
import 'projects_state.dart';

/// Provider for ApiClient
final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient();
});

/// Provider for ProjectRepository
final projectRepositoryProvider = Provider<ProjectRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return ProjectRepositoryImpl(apiClient);
});

/// Provider for Projects List State
final projectsProvider =
    StateNotifierProvider.autoDispose<ProjectsNotifier, ProjectsState>((ref) {
  final repository = ref.watch(projectRepositoryProvider);
  return ProjectsNotifier(repository);
});

/// Provider for Project Detail State
final projectDetailProvider =
    StateNotifierProvider.autoDispose<ProjectDetailNotifier, ProjectDetailState>((ref) {
  final repository = ref.watch(projectRepositoryProvider);
  return ProjectDetailNotifier(repository);
});

/// Provider for Project Stats State
final projectStatsProvider =
    StateNotifierProvider.autoDispose<ProjectStatsNotifier, ProjectStatsState>((ref) {
  final repository = ref.watch(projectRepositoryProvider);
  return ProjectStatsNotifier(repository);
});

/// Provider for filtered projects (with search, status filters, etc.)
final filteredProjectsProvider = StateNotifierProvider.family<
    ProjectsNotifier,
    ProjectsState,
    Map<String, dynamic>>((ref, filters) {
  final repository = ref.watch(projectRepositoryProvider);
  final notifier = ProjectsNotifier(repository);

  // Load projects with filters
  notifier.loadProjects(
    search: filters['search'] as String?,
    status: filters['status'] as String?,
    type: filters['type'] as String?,
    city: filters['city'] as String?,
    projectManagerId: filters['projectManagerId'] as int?,
    priority: filters['priority'] as String?,
    filter: filters['filter'] as String?,
  );

  return notifier;
});
