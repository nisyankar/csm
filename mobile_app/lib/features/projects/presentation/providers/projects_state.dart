import '../../domain/entities/project.dart';

/// State for Projects List
class ProjectsState {
  final List<Project> projects;
  final bool isLoading;
  final String? error;
  final bool hasMore;
  final int currentPage;

  const ProjectsState({
    this.projects = const [],
    this.isLoading = false,
    this.error,
    this.hasMore = true,
    this.currentPage = 1,
  });

  ProjectsState copyWith({
    List<Project>? projects,
    bool? isLoading,
    String? error,
    bool? hasMore,
    int? currentPage,
  }) {
    return ProjectsState(
      projects: projects ?? this.projects,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      hasMore: hasMore ?? this.hasMore,
      currentPage: currentPage ?? this.currentPage,
    );
  }
}

/// State for Single Project Detail
class ProjectDetailState {
  final Project? project;
  final bool isLoading;
  final String? error;

  const ProjectDetailState({
    this.project,
    this.isLoading = false,
    this.error,
  });

  ProjectDetailState copyWith({
    Project? project,
    bool? isLoading,
    String? error,
  }) {
    return ProjectDetailState(
      project: project ?? this.project,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

/// State for Project Statistics
class ProjectStatsState {
  final Map<String, dynamic>? stats;
  final bool isLoading;
  final String? error;

  const ProjectStatsState({
    this.stats,
    this.isLoading = false,
    this.error,
  });

  ProjectStatsState copyWith({
    Map<String, dynamic>? stats,
    bool? isLoading,
    String? error,
  }) {
    return ProjectStatsState(
      stats: stats ?? this.stats,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}
