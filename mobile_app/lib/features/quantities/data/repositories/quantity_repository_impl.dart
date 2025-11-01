import '../../../../core/api/api_client.dart';
import '../../domain/entities/quantity.dart';
import '../../domain/repositories/quantity_repository.dart';
import '../models/quantity_model.dart';

class QuantityRepositoryImpl implements QuantityRepository {
  final ApiClient _apiClient;

  QuantityRepositoryImpl(this._apiClient);

  @override
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
  }) async {
    final queryParams = <String, dynamic>{
      'page': page ?? 1,
      'per_page': perPage ?? 20,
      'sort': sort ?? 'measurement_date',
      'direction': direction ?? 'desc',
      if (search != null && search.isNotEmpty) 'search': search,
      if (projectId != null) 'project_id': projectId,
      if (workItemId != null) 'work_item_id': workItemId,
      if (structureId != null) 'structure_id': structureId,
      if (floorId != null) 'floor_id': floorId,
      if (unitId != null) 'unit_id': unitId,
      if (verified != null) 'verified': verified.toString(),
      if (approved != null) 'approved': approved.toString(),
    };

    final response = await _apiClient.get(
      '/v1/quantities',
      queryParameters: queryParams,
    );

    if (response.data['success'] == true) {
      final data = response.data['data'];
      final List quantitiesJson = data['data'] as List? ?? data as List;
      return quantitiesJson
          .map((json) => QuantityModel.fromJson(json as Map<String, dynamic>))
          .toList();
    }

    throw ApiException(
      message: 'Metrajlar yüklenemedi',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<Quantity> getQuantity(int id) async {
    final response = await _apiClient.get('/v1/quantities/$id');

    if (response.data['success'] == true) {
      return QuantityModel.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    }

    throw ApiException(
      message: 'Metraj bulunamadı',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<List<Quantity>> getQuantitiesByProject(int projectId) async {
    final response = await _apiClient.get('/v1/quantities/project/$projectId');

    if (response.data['success'] == true) {
      final List quantitiesJson = response.data['data'] as List;
      return quantitiesJson
          .map((json) => QuantityModel.fromJson(json as Map<String, dynamic>))
          .toList();
    }

    throw ApiException(
      message: 'Proje metrajları yüklenemedi',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<Map<String, dynamic>> getStats({int? projectId}) async {
    final queryParams = <String, dynamic>{
      if (projectId != null) 'project_id': projectId,
    };

    final response = await _apiClient.get(
      '/v1/quantities/stats',
      queryParameters: queryParams,
    );

    if (response.data['success'] == true) {
      return response.data['data'] as Map<String, dynamic>;
    }

    throw ApiException(
      message: 'İstatistikler yüklenemedi',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
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
  }) async {
    final data = {
      'project_id': projectId,
      'work_item_id': workItemId,
      if (projectStructureId != null) 'project_structure_id': projectStructureId,
      if (projectFloorId != null) 'project_floor_id': projectFloorId,
      if (projectUnitId != null) 'project_unit_id': projectUnitId,
      'planned_quantity': plannedQuantity,
      'completed_quantity': completedQuantity ?? 0,
      'unit': unit,
      'measurement_date': measurementDate.toIso8601String().split('T')[0],
      if (measurementMethod != null) 'measurement_method': measurementMethod,
      if (notes != null) 'notes': notes,
    };

    final response = await _apiClient.post('/v1/quantities', data: data);

    if (response.data['success'] == true) {
      return QuantityModel.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    }

    throw ApiException(
      message: response.data['message'] as String? ?? 'Metraj oluşturulamadı',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<Quantity> updateQuantity({
    required int id,
    double? plannedQuantity,
    double? completedQuantity,
    String? unit,
    DateTime? measurementDate,
    String? measurementMethod,
    String? notes,
  }) async {
    final data = <String, dynamic>{
      if (plannedQuantity != null) 'planned_quantity': plannedQuantity,
      if (completedQuantity != null) 'completed_quantity': completedQuantity,
      if (unit != null) 'unit': unit,
      if (measurementDate != null)
        'measurement_date': measurementDate.toIso8601String().split('T')[0],
      if (measurementMethod != null) 'measurement_method': measurementMethod,
      if (notes != null) 'notes': notes,
    };

    final response = await _apiClient.put('/v1/quantities/$id', data: data);

    if (response.data['success'] == true) {
      return QuantityModel.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    }

    throw ApiException(
      message: response.data['message'] as String? ?? 'Metraj güncellenemedi',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<void> deleteQuantity(int id) async {
    final response = await _apiClient.delete('/v1/quantities/$id');

    if (response.data['success'] != true) {
      throw ApiException(
        message: response.data['message'] as String? ?? 'Metraj silinemedi',
        statusCode: response.statusCode ?? 0,
      );
    }
  }

  @override
  Future<Quantity> verifyQuantity(int id) async {
    final response = await _apiClient.post('/v1/quantities/$id/verify');

    if (response.data['success'] == true) {
      return QuantityModel.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    }

    throw ApiException(
      message: response.data['message'] as String? ?? 'Metraj doğrulanamadı',
      statusCode: response.statusCode ?? 0,
    );
  }

  @override
  Future<Quantity> approveQuantity(int id) async {
    final response = await _apiClient.post('/v1/quantities/$id/approve');

    if (response.data['success'] == true) {
      return QuantityModel.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    }

    throw ApiException(
      message: response.data['message'] as String? ?? 'Metraj onaylanamadı',
      statusCode: response.statusCode ?? 0,
    );
  }
}
