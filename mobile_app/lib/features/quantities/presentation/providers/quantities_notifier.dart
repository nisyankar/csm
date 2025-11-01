import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/repositories/quantity_repository.dart';
import 'quantities_state.dart';

/// Notifier for Quantities List
class QuantitiesNotifier extends StateNotifier<QuantitiesState> {
  final QuantityRepository _repository;

  QuantitiesNotifier(this._repository) : super(const QuantitiesState());

  /// Load quantities with optional filters
  Future<void> loadQuantities({
    String? search,
    int? projectId,
    int? workItemId,
    int? structureId,
    int? floorId,
    int? unitId,
    bool? verified,
    bool? approved,
    String? sort,
    String? direction,
    bool refresh = false,
  }) async {
    if (refresh) {
      state = const QuantitiesState(isLoading: true);
    } else if (state.isLoading) {
      return;
    } else {
      state = state.copyWith(isLoading: true, error: null);
    }

    try {
      final quantities = await _repository.getQuantities(
        search: search,
        projectId: projectId,
        workItemId: workItemId,
        structureId: structureId,
        floorId: floorId,
        unitId: unitId,
        verified: verified,
        approved: approved,
        sort: sort,
        direction: direction,
        page: refresh ? 1 : state.currentPage,
        perPage: 20,
      );

      state = QuantitiesState(
        quantities: refresh ? quantities : [...state.quantities, ...quantities],
        isLoading: false,
        hasMore: quantities.length >= 20,
        currentPage: refresh ? 1 : state.currentPage,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Load more quantities (pagination)
  Future<void> loadMore({
    String? search,
    int? projectId,
    int? workItemId,
    int? structureId,
    int? floorId,
    int? unitId,
    bool? verified,
    bool? approved,
    String? sort,
    String? direction,
  }) async {
    if (!state.hasMore || state.isLoading) return;

    state = state.copyWith(isLoading: true);

    try {
      final quantities = await _repository.getQuantities(
        search: search,
        projectId: projectId,
        workItemId: workItemId,
        structureId: structureId,
        floorId: floorId,
        unitId: unitId,
        verified: verified,
        approved: approved,
        sort: sort,
        direction: direction,
        page: state.currentPage + 1,
        perPage: 20,
      );

      state = QuantitiesState(
        quantities: [...state.quantities, ...quantities],
        isLoading: false,
        hasMore: quantities.length >= 20,
        currentPage: state.currentPage + 1,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Refresh quantities list
  Future<void> refresh({
    String? search,
    int? projectId,
    int? workItemId,
    int? structureId,
    int? floorId,
    int? unitId,
    bool? verified,
    bool? approved,
    String? sort,
    String? direction,
  }) async {
    await loadQuantities(
      search: search,
      projectId: projectId,
      workItemId: workItemId,
      structureId: structureId,
      floorId: floorId,
      unitId: unitId,
      verified: verified,
      approved: approved,
      sort: sort,
      direction: direction,
      refresh: true,
    );
  }

  /// Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }
}

/// Notifier for Single Quantity Detail
class QuantityDetailNotifier extends StateNotifier<QuantityDetailState> {
  final QuantityRepository _repository;

  QuantityDetailNotifier(this._repository) : super(const QuantityDetailState());

  /// Load quantity by ID
  Future<void> loadQuantity(int id) async {
    state = const QuantityDetailState(isLoading: true);

    try {
      final quantity = await _repository.getQuantity(id);
      state = QuantityDetailState(
        quantity: quantity,
        isLoading: false,
      );
    } catch (e) {
      state = QuantityDetailState(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Refresh quantity data
  Future<void> refresh(int id) async {
    await loadQuantity(id);
  }

  /// Verify quantity
  Future<void> verify(int id) async {
    try {
      final updatedQuantity = await _repository.verifyQuantity(id);
      state = QuantityDetailState(
        quantity: updatedQuantity,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
      );
    }
  }

  /// Approve quantity
  Future<void> approve(int id) async {
    try {
      final updatedQuantity = await _repository.approveQuantity(id);
      state = QuantityDetailState(
        quantity: updatedQuantity,
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

/// Notifier for Quantity Statistics
class QuantityStatsNotifier extends StateNotifier<QuantityStatsState> {
  final QuantityRepository _repository;

  QuantityStatsNotifier(this._repository) : super(const QuantityStatsState());

  /// Load quantity statistics
  Future<void> loadStats({int? projectId}) async {
    state = const QuantityStatsState(isLoading: true);

    try {
      final stats = await _repository.getStats(projectId: projectId);
      state = QuantityStatsState(
        stats: stats,
        isLoading: false,
      );
    } catch (e) {
      state = QuantityStatsState(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Refresh statistics
  Future<void> refresh({int? projectId}) async {
    await loadStats(projectId: projectId);
  }
}
