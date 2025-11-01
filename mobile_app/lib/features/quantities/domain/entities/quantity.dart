class Quantity {
  final int id;
  final int projectId;
  final String? projectName;
  final String? projectCode;
  final int workItemId;
  final String workItemName;
  final String workItemCode;
  final String workItemUnit;
  final double? workItemUnitPrice;
  final int? projectStructureId;
  final String? projectStructureName;
  final int? projectFloorId;
  final String? projectFloorName;
  final int? projectUnitId;
  final String? projectUnitCode;
  final double plannedQuantity;
  final double completedQuantity;
  final String unit;
  final DateTime measurementDate;
  final String? measurementMethod;
  final int? verifiedBy;
  final String? verifiedByName;
  final DateTime? verifiedAt;
  final int? approvedBy;
  final String? approvedByName;
  final DateTime? approvedAt;
  final String? notes;
  final DateTime createdAt;
  final DateTime updatedAt;

  const Quantity({
    required this.id,
    required this.projectId,
    this.projectName,
    this.projectCode,
    required this.workItemId,
    required this.workItemName,
    required this.workItemCode,
    required this.workItemUnit,
    this.workItemUnitPrice,
    this.projectStructureId,
    this.projectStructureName,
    this.projectFloorId,
    this.projectFloorName,
    this.projectUnitId,
    this.projectUnitCode,
    required this.plannedQuantity,
    required this.completedQuantity,
    required this.unit,
    required this.measurementDate,
    this.measurementMethod,
    this.verifiedBy,
    this.verifiedByName,
    this.verifiedAt,
    this.approvedBy,
    this.approvedByName,
    this.approvedAt,
    this.notes,
    required this.createdAt,
    required this.updatedAt,
  });

  // Computed properties
  double get remainingQuantity => plannedQuantity - completedQuantity;

  double get completionPercentage {
    if (plannedQuantity == 0) return 0;
    return (completedQuantity / plannedQuantity) * 100;
  }

  bool get isVerified => verifiedAt != null;
  bool get isApproved => approvedAt != null;
  bool get isPending => !isVerified;
  bool get isAwaitingApproval => isVerified && !isApproved;

  String get statusLabel {
    if (isApproved) return 'Onaylı';
    if (isVerified) return 'Doğrulandı';
    return 'Beklemede';
  }

  String get locationLabel {
    final parts = <String>[];
    if (projectStructureName != null) parts.add(projectStructureName!);
    if (projectFloorName != null) parts.add(projectFloorName!);
    if (projectUnitCode != null) parts.add(projectUnitCode!);
    return parts.isEmpty ? 'Konum belirtilmemiş' : parts.join(' - ');
  }
}
