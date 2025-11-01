import '../../domain/entities/quantity.dart';

class QuantityModel extends Quantity {
  const QuantityModel({
    required super.id,
    required super.projectId,
    super.projectName,
    super.projectCode,
    required super.workItemId,
    required super.workItemName,
    required super.workItemCode,
    required super.workItemUnit,
    super.workItemUnitPrice,
    super.projectStructureId,
    super.projectStructureName,
    super.projectFloorId,
    super.projectFloorName,
    super.projectUnitId,
    super.projectUnitCode,
    required super.plannedQuantity,
    required super.completedQuantity,
    required super.unit,
    required super.measurementDate,
    super.measurementMethod,
    super.verifiedBy,
    super.verifiedByName,
    super.verifiedAt,
    super.approvedBy,
    super.approvedByName,
    super.approvedAt,
    super.notes,
    required super.createdAt,
    required super.updatedAt,
  });

  factory QuantityModel.fromJson(Map<String, dynamic> json) {
    return QuantityModel(
      id: json['id'] as int,
      projectId: json['project_id'] as int,
      projectName: json['project_name'] as String?,
      projectCode: json['project_code'] as String?,
      workItemId: json['work_item_id'] as int,
      workItemName: json['work_item_name'] as String? ?? '',
      workItemCode: json['work_item_code'] as String? ?? '',
      workItemUnit: json['work_item_unit'] as String? ?? json['unit'] as String? ?? '',
      workItemUnitPrice: json['work_item_unit_price'] != null
          ? _parseDouble(json['work_item_unit_price'])
          : null,
      projectStructureId: json['project_structure_id'] as int?,
      projectStructureName: json['project_structure_name'] as String?,
      projectFloorId: json['project_floor_id'] as int?,
      projectFloorName: json['project_floor_name'] as String?,
      projectUnitId: json['project_unit_id'] as int?,
      projectUnitCode: json['project_unit_code'] as String?,
      plannedQuantity: _parseDouble(json['planned_quantity']),
      completedQuantity: _parseDouble(json['completed_quantity']),
      unit: json['unit'] as String? ?? '',
      measurementDate: DateTime.parse(json['measurement_date'] as String),
      measurementMethod: json['measurement_method'] as String?,
      verifiedBy: json['verified_by'] as int?,
      verifiedByName: json['verified_by_name'] as String?,
      verifiedAt: json['verified_at'] != null
          ? DateTime.parse(json['verified_at'] as String)
          : null,
      approvedBy: json['approved_by'] as int?,
      approvedByName: json['approved_by_name'] as String?,
      approvedAt: json['approved_at'] != null
          ? DateTime.parse(json['approved_at'] as String)
          : null,
      notes: json['notes'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'project_id': projectId,
      'work_item_id': workItemId,
      'project_structure_id': projectStructureId,
      'project_floor_id': projectFloorId,
      'project_unit_id': projectUnitId,
      'planned_quantity': plannedQuantity,
      'completed_quantity': completedQuantity,
      'unit': unit,
      'measurement_date': measurementDate.toIso8601String().split('T')[0],
      'measurement_method': measurementMethod,
      'notes': notes,
    };
  }
}
