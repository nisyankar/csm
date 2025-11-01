import '../../domain/entities/timesheet.dart';

/// Current project info from backend
class CurrentProject {
  final int id;
  final String name;
  final String projectCode;
  final List<String> allowedCheckInMethods;

  const CurrentProject({
    required this.id,
    required this.name,
    required this.projectCode,
    required this.allowedCheckInMethods,
  });

  factory CurrentProject.fromJson(Map<String, dynamic> json) {
    return CurrentProject(
      id: json['id'] as int,
      name: json['name'] as String,
      projectCode: json['project_code'] as String,
      allowedCheckInMethods: (json['allowed_check_in_methods'] as List<dynamic>?)
          ?.map((e) => e.toString())
          .toList() ?? ['manual'],
    );
  }
}

/// State for today's timesheet status
class TodayTimesheetState {
  final Timesheet? activeTimesheet;
  final bool isCheckedIn;
  final String? currentWorkingHours;
  final CurrentProject? currentProject;
  final bool isLoading;
  final String? error;

  const TodayTimesheetState({
    this.activeTimesheet,
    this.isCheckedIn = false,
    this.currentWorkingHours,
    this.currentProject,
    this.isLoading = false,
    this.error,
  });

  TodayTimesheetState copyWith({
    Timesheet? activeTimesheet,
    bool? isCheckedIn,
    String? currentWorkingHours,
    CurrentProject? currentProject,
    bool? isLoading,
    String? error,
  }) {
    return TodayTimesheetState(
      activeTimesheet: activeTimesheet ?? this.activeTimesheet,
      isCheckedIn: isCheckedIn ?? this.isCheckedIn,
      currentWorkingHours: currentWorkingHours ?? this.currentWorkingHours,
      currentProject: currentProject ?? this.currentProject,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

/// State for timesheets list
class TimesheetsState {
  final List<Timesheet> timesheets;
  final bool isLoading;
  final String? error;
  final bool hasMore;
  final int currentPage;

  const TimesheetsState({
    this.timesheets = const [],
    this.isLoading = false,
    this.error,
    this.hasMore = true,
    this.currentPage = 1,
  });

  TimesheetsState copyWith({
    List<Timesheet>? timesheets,
    bool? isLoading,
    String? error,
    bool? hasMore,
    int? currentPage,
  }) {
    return TimesheetsState(
      timesheets: timesheets ?? this.timesheets,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      hasMore: hasMore ?? this.hasMore,
      currentPage: currentPage ?? this.currentPage,
    );
  }
}
