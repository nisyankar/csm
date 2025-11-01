import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../projects/presentation/providers/projects_providers.dart';
import '../../data/repositories/timesheet_repository_impl.dart';
import '../../domain/repositories/timesheet_repository.dart';
import 'timesheet_state.dart';
import 'timesheet_notifier.dart';

// Repository provider
final timesheetRepositoryProvider = Provider<TimesheetRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return TimesheetRepositoryImpl(apiClient: apiClient);
});

// Today timesheet state notifier provider
final todayTimesheetProvider =
    StateNotifierProvider<TodayTimesheetNotifier, TodayTimesheetState>((ref) {
  final repository = ref.watch(timesheetRepositoryProvider);
  return TodayTimesheetNotifier(repository);
});

// Timesheets list state notifier provider
final timesheetsProvider =
    StateNotifierProvider<TimesheetsNotifier, TimesheetsState>((ref) {
  final repository = ref.watch(timesheetRepositoryProvider);
  return TimesheetsNotifier(repository);
});

// Week summary provider
final weekSummaryProvider = FutureProvider.autoDispose((ref) async {
  final repository = ref.watch(timesheetRepositoryProvider);
  return await repository.getWeekSummary();
});

// Month summary provider
final monthSummaryProvider = FutureProvider.autoDispose.family<Map<String, dynamic>, MonthParams>((ref, params) async {
  final repository = ref.watch(timesheetRepositoryProvider);
  return await repository.getMonthSummary(
    year: params.year,
    month: params.month,
  );
});

class MonthParams {
  final int year;
  final int month;

  MonthParams({required this.year, required this.month});

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is MonthParams &&
          runtimeType == other.runtimeType &&
          year == other.year &&
          month == other.month;

  @override
  int get hashCode => year.hashCode ^ month.hashCode;
}
