import '../../domain/entities/quantity.dart';

/// State for quantities list
class QuantitiesState {
  final List<Quantity> quantities;
  final bool isLoading;
  final String? error;
  final bool hasMore;
  final int currentPage;

  const QuantitiesState({
    this.quantities = const [],
    this.isLoading = false,
    this.error,
    this.hasMore = true,
    this.currentPage = 1,
  });

  QuantitiesState copyWith({
    List<Quantity>? quantities,
    bool? isLoading,
    String? error,
    bool? hasMore,
    int? currentPage,
  }) {
    return QuantitiesState(
      quantities: quantities ?? this.quantities,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      hasMore: hasMore ?? this.hasMore,
      currentPage: currentPage ?? this.currentPage,
    );
  }
}

/// State for single quantity detail
class QuantityDetailState {
  final Quantity? quantity;
  final bool isLoading;
  final String? error;

  const QuantityDetailState({
    this.quantity,
    this.isLoading = false,
    this.error,
  });

  QuantityDetailState copyWith({
    Quantity? quantity,
    bool? isLoading,
    String? error,
  }) {
    return QuantityDetailState(
      quantity: quantity ?? this.quantity,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

/// State for quantity statistics
class QuantityStatsState {
  final Map<String, dynamic>? stats;
  final bool isLoading;
  final String? error;

  const QuantityStatsState({
    this.stats,
    this.isLoading = false,
    this.error,
  });

  QuantityStatsState copyWith({
    Map<String, dynamic>? stats,
    bool? isLoading,
    String? error,
  }) {
    return QuantityStatsState(
      stats: stats ?? this.stats,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}
