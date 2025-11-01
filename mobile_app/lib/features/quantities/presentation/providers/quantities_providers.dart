import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/api/api_client.dart';
import '../../data/repositories/quantity_repository_impl.dart';
import '../../domain/repositories/quantity_repository.dart';
import 'quantities_notifier.dart';
import 'quantities_state.dart';

/// Provider for ApiClient
final quantityApiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient();
});

/// Provider for QuantityRepository
final quantityRepositoryProvider = Provider<QuantityRepository>((ref) {
  final apiClient = ref.watch(quantityApiClientProvider);
  return QuantityRepositoryImpl(apiClient);
});

/// Provider for Quantities List State
final quantitiesProvider =
    StateNotifierProvider.autoDispose<QuantitiesNotifier, QuantitiesState>((ref) {
  final repository = ref.watch(quantityRepositoryProvider);
  return QuantitiesNotifier(repository);
});

/// Provider for Quantity Detail State
final quantityDetailProvider =
    StateNotifierProvider.autoDispose<QuantityDetailNotifier, QuantityDetailState>((ref) {
  final repository = ref.watch(quantityRepositoryProvider);
  return QuantityDetailNotifier(repository);
});

/// Provider for Quantity Stats State
final quantityStatsProvider =
    StateNotifierProvider.autoDispose<QuantityStatsNotifier, QuantityStatsState>((ref) {
  final repository = ref.watch(quantityRepositoryProvider);
  return QuantityStatsNotifier(repository);
});
