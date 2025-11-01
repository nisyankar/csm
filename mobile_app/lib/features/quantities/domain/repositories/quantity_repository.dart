import '../entities/quantity.dart';

abstract class QuantityRepository {
  /// Get quantities with optional filters
  Future<List<Quantity>> getQuantities({
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
    int? page,
    int? perPage,
  });

  /// Get single quantity by ID
  Future<Quantity> getQuantity(int id);

  /// Get quantities by project
  Future<List<Quantity>> getQuantitiesByProject(int projectId);

  /// Get quantity statistics
  Future<Map<String, dynamic>> getStats({int? projectId});

  /// Create new quantity
  Future<Quantity> createQuantity({
    required int projectId,
    required int workItemId,
    int? projectStructureId,
    int? projectFloorId,
    int? projectUnitId,
    required double plannedQuantity,
    double? completedQuantity,
    required String unit,
    required DateTime measurementDate,
    String? measurementMethod,
    String? notes,
  });

  /// Update quantity
  Future<Quantity> updateQuantity({
    required int id,
    double? plannedQuantity,
    double? completedQuantity,
    String? unit,
    DateTime? measurementDate,
    String? measurementMethod,
    String? notes,
  });

  /// Delete quantity
  Future<void> deleteQuantity(int id);

  /// Verify quantity
  Future<Quantity> verifyQuantity(int id);

  /// Approve quantity
  Future<Quantity> approveQuantity(int id);
}
