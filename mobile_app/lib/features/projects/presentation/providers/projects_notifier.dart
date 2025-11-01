import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/repositories/project_repository.dart';
import 'projects_state.dart';

/// Notifier for Projects List
class ProjectsNotifier extends StateNotifier<ProjectsState> {
  final ProjectRepository _repository;

  ProjectsNotifier(this._repository) : super(const ProjectsState());

  /// Load projects with optional filters
  Future<void> loadProjects({
    String? search,
    String? status,
    String? type,
    String? city,
    int? projectManagerId,
    String? priority,
    String? filter,
    bool refresh = false,
  }) async {
    if (refresh) {
      state = const ProjectsState(isLoading: true);
    } else if (state.isLoading) {
      return; // Already loading
    } else {
      state = state.copyWith(isLoading: true, error: null);
    }

    try {
      final projects = await _repository.getProjects(
        search: search,
        status: status,
        type: type,
        city: city,
        projectManagerId: projectManagerId,
        priority: priority,
        filter: filter,
        page: refresh ? 1 : state.currentPage,
        perPage: 20,
      );

      state = ProjectsState(
        projects: refresh ? projects : [...state.projects, ...projects],
        isLoading: false,
        hasMore: projects.length >= 20,
        currentPage: refresh ? 1 : state.currentPage,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Load more projects (pagination)
  Future<void> loadMore({
    String? search,
    String? status,
    String? type,
    String? city,
    int? projectManagerId,
    String? priority,
    String? filter,
  }) async {
    if (!state.hasMore || state.isLoading) return;

    state = state.copyWith(isLoading: true);

    try {
      final projects = await _repository.getProjects(
        search: search,
        status: status,
        type: type,
        city: city,
        projectManagerId: projectManagerId,
        priority: priority,
        filter: filter,
        page: state.currentPage + 1,
        perPage: 20,
      );

      state = ProjectsState(
        projects: [...state.projects, ...projects],
        isLoading: false,
        hasMore: projects.length >= 20,
        currentPage: state.currentPage + 1,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Refresh projects list
  Future<void> refresh({
    String? search,
    String? status,
    String? type,
    String? city,
    int? projectManagerId,
    String? priority,
    String? filter,
  }) async {
    await loadProjects(
      search: search,
      status: status,
      type: type,
      city: city,
      projectManagerId: projectManagerId,
      priority: priority,
      filter: filter,
      refresh: true,
    );
  }

  /// Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }
}

/// Notifier for Single Project Detail
class ProjectDetailNotifier extends StateNotifier<ProjectDetailState> {
  final ProjectRepository _repository;

  ProjectDetailNotifier(this._repository) : super(const ProjectDetailState());

  /// Load project by ID
  Future<void> loadProject(int id) async {
    state = const ProjectDetailState(isLoading: true);

    try {
      final project = await _repository.getProject(id);
      state = ProjectDetailState(
        project: project,
        isLoading: false,
      );
    } catch (e) {
      state = ProjectDetailState(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Refresh project data
  Future<void> refresh(int id) async {
    await loadProject(id);
  }

  /// Update project status
  Future<void> updateStatus(
    int id, {
    required String status,
    String? statusReason,
  }) async {
    try {
      final updatedProject = await _repository.updateProjectStatus(
        id,
        status: status,
        statusReason: statusReason,
      );

      state = ProjectDetailState(
        project: updatedProject,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
      );
    }
  }

  /// Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }
}

/// Notifier for Project Statistics
class ProjectStatsNotifier extends StateNotifier<ProjectStatsState> {
  final ProjectRepository _repository;

  ProjectStatsNotifier(this._repository) : super(const ProjectStatsState());

  /// Load project statistics
  Future<void> loadStats() async {
    state = const ProjectStatsState(isLoading: true);

    try {
      final stats = await _repository.getProjectStats();
      state = ProjectStatsState(
        stats: stats,
        isLoading: false,
      );
    } catch (e) {
      state = ProjectStatsState(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Refresh statistics
  Future<void> refresh() async {
    await loadStats();
  }
}
