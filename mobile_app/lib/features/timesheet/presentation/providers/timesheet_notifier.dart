import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/models/timesheet_model.dart';
import '../../domain/repositories/timesheet_repository.dart';
import 'timesheet_state.dart';

/// Notifier for Today's Timesheet Status
class TodayTimesheetNotifier extends StateNotifier<TodayTimesheetState> {
  final TimesheetRepository _repository;

  TodayTimesheetNotifier(this._repository) : super(const TodayTimesheetState());

  /// Load today's timesheet status
  Future<void> loadTodayStatus() async {
    state = const TodayTimesheetState(isLoading: true);

    try {
      final response = await _repository.getTodayStatus();

      final hasCheckedIn = response['has_clocked_in'] as bool? ?? false;
      final workingHours = response['working_hours']?.toString();

      // Parse timesheet data if exists
      final timesheetData = response['timesheet'];
      final activeTimesheet = timesheetData != null
          ? TimesheetModel.fromJson(timesheetData as Map<String, dynamic>)
          : null;

      // Parse current project
      final projectData = response['current_project'];
      final currentProject = projectData != null
          ? CurrentProject.fromJson(projectData as Map<String, dynamic>)
          : null;

      state = TodayTimesheetState(
        activeTimesheet: activeTimesheet,
        isCheckedIn: hasCheckedIn,
        currentWorkingHours: workingHours,
        currentProject: currentProject,
        isLoading: false,
      );
    } catch (e) {
      state = TodayTimesheetState(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Clock-in
  Future<void> clockIn({
    required int projectId,
    String? checkInMethod,
    Map<String, double>? location,
    String? notes,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      await _repository.clockIn(
        projectId: projectId,
        checkInMethod: checkInMethod,
        location: location,
        notes: notes,
      );

      // Refresh today's status from backend to get updated data
      await loadTodayStatus();
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      rethrow;
    }
  }

  /// Clock-out
  Future<void> clockOut({
    int? timesheetId,
    String? checkOutMethod,
    Map<String, double>? location,
    String? notes,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      await _repository.clockOut(
        timesheetId: timesheetId,
        checkOutMethod: checkOutMethod,
        location: location,
        notes: notes,
      );

      // Refresh today's status from backend to get updated data
      await loadTodayStatus();
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      rethrow;
    }
  }

  /// Refresh today's status
  Future<void> refresh() async {
    await loadTodayStatus();
  }

  /// Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }
}

/// Notifier for Timesheets List
class TimesheetsNotifier extends StateNotifier<TimesheetsState> {
  final TimesheetRepository _repository;

  TimesheetsNotifier(this._repository) : super(const TimesheetsState());

  /// Load timesheets with optional filters
  Future<void> loadTimesheets({
    int? employeeId,
    int? projectId,
    String? date,
    String? startDate,
    String? endDate,
    String? status,
    bool refresh = false,
  }) async {
    if (refresh) {
      state = const TimesheetsState(isLoading: true);
    } else if (state.isLoading) {
      return;
    } else {
      state = state.copyWith(isLoading: true, error: null);
    }

    try {
      final timesheets = await _repository.getTimesheets(
        employeeId: employeeId,
        projectId: projectId,
        date: date,
        startDate: startDate,
        endDate: endDate,
        status: status,
        page: refresh ? 1 : state.currentPage,
        perPage: 15,
      );

      state = TimesheetsState(
        timesheets: refresh ? timesheets : [...state.timesheets, ...timesheets],
        isLoading: false,
        hasMore: timesheets.length >= 15,
        currentPage: refresh ? 1 : state.currentPage,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Load more timesheets (pagination)
  Future<void> loadMore({
    int? employeeId,
    int? projectId,
    String? date,
    String? startDate,
    String? endDate,
    String? status,
  }) async {
    if (!state.hasMore || state.isLoading) return;

    state = state.copyWith(isLoading: true);

    try {
      final timesheets = await _repository.getTimesheets(
        employeeId: employeeId,
        projectId: projectId,
        date: date,
        startDate: startDate,
        endDate: endDate,
        status: status,
        page: state.currentPage + 1,
        perPage: 15,
      );

      state = TimesheetsState(
        timesheets: [...state.timesheets, ...timesheets],
        isLoading: false,
        hasMore: timesheets.length >= 15,
        currentPage: state.currentPage + 1,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Refresh timesheets list
  Future<void> refresh({
    int? employeeId,
    int? projectId,
    String? date,
    String? startDate,
    String? endDate,
    String? status,
  }) async {
    await loadTimesheets(
      employeeId: employeeId,
      projectId: projectId,
      date: date,
      startDate: startDate,
      endDate: endDate,
      status: status,
      refresh: true,
    );
  }

  /// Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }
}
