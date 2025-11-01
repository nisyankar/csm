/// Domain entity for Project
/// Represents a construction project in the business logic layer
class Project {
  final int id;
  final String projectCode;
  final String name;
  final String? description;
  final String location;
  final String city;
  final String? district;
  final String? fullAddress;
  final String? coordinates;
  final DateTime startDate;
  final DateTime plannedEndDate;
  final DateTime? actualEndDate;
  final double? budget;
  final double? laborBudget;
  final double spentAmount;
  final String status; // planning, active, on_hold, completed, cancelled
  final String type; // residential, commercial, infrastructure, industrial, other
  final String priority; // low, medium, high, critical
  final String? clientName;
  final String? clientContact;
  final String? contactPhone;
  final String? contactEmail;
  final int? estimatedEmployees;
  final String? notes;
  final int? projectManagerId;
  final int? siteManagerId;
  final String? projectManagerName;
  final String? siteManagerName;
  final DateTime createdAt;
  final DateTime updatedAt;

  // Computed properties
  double get remainingBudget => (budget ?? 0) - spentAmount;
  double get budgetUsagePercentage =>
      budget != null && budget! > 0 ? (spentAmount / budget!) * 100 : 0;
  bool get isDelayed =>
      status != 'completed' &&
      status != 'cancelled' &&
      DateTime.now().isAfter(plannedEndDate);
  int get daysRemaining => plannedEndDate.difference(DateTime.now()).inDays;

  const Project({
    required this.id,
    required this.projectCode,
    required this.name,
    this.description,
    required this.location,
    required this.city,
    this.district,
    this.fullAddress,
    this.coordinates,
    required this.startDate,
    required this.plannedEndDate,
    this.actualEndDate,
    this.budget,
    this.laborBudget,
    required this.spentAmount,
    required this.status,
    required this.type,
    required this.priority,
    this.clientName,
    this.clientContact,
    this.contactPhone,
    this.contactEmail,
    this.estimatedEmployees,
    this.notes,
    this.projectManagerId,
    this.siteManagerId,
    this.projectManagerName,
    this.siteManagerName,
    required this.createdAt,
    required this.updatedAt,
  });

  /// Get status display name in Turkish
  String get statusDisplay {
    switch (status) {
      case 'planning':
        return 'Planlamada';
      case 'active':
        return 'Aktif';
      case 'on_hold':
        return 'Beklemede';
      case 'completed':
        return 'Tamamlandı';
      case 'cancelled':
        return 'İptal';
      default:
        return status;
    }
  }

  /// Get type display name in Turkish
  String get typeDisplay {
    switch (type) {
      case 'residential':
        return 'Konut';
      case 'commercial':
        return 'Ticari';
      case 'infrastructure':
        return 'Altyapı';
      case 'industrial':
        return 'Endüstriyel';
      case 'other':
        return 'Diğer';
      default:
        return type;
    }
  }

  /// Get priority display name in Turkish
  String get priorityDisplay {
    switch (priority) {
      case 'low':
        return 'Düşük';
      case 'medium':
        return 'Orta';
      case 'high':
        return 'Yüksek';
      case 'critical':
        return 'Kritik';
      default:
        return priority;
    }
  }

  Project copyWith({
    int? id,
    String? projectCode,
    String? name,
    String? description,
    String? location,
    String? city,
    String? district,
    String? fullAddress,
    String? coordinates,
    DateTime? startDate,
    DateTime? plannedEndDate,
    DateTime? actualEndDate,
    double? budget,
    double? laborBudget,
    double? spentAmount,
    String? status,
    String? type,
    String? priority,
    String? clientName,
    String? clientContact,
    String? contactPhone,
    String? contactEmail,
    int? estimatedEmployees,
    String? notes,
    int? projectManagerId,
    int? siteManagerId,
    String? projectManagerName,
    String? siteManagerName,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Project(
      id: id ?? this.id,
      projectCode: projectCode ?? this.projectCode,
      name: name ?? this.name,
      description: description ?? this.description,
      location: location ?? this.location,
      city: city ?? this.city,
      district: district ?? this.district,
      fullAddress: fullAddress ?? this.fullAddress,
      coordinates: coordinates ?? this.coordinates,
      startDate: startDate ?? this.startDate,
      plannedEndDate: plannedEndDate ?? this.plannedEndDate,
      actualEndDate: actualEndDate ?? this.actualEndDate,
      budget: budget ?? this.budget,
      laborBudget: laborBudget ?? this.laborBudget,
      spentAmount: spentAmount ?? this.spentAmount,
      status: status ?? this.status,
      type: type ?? this.type,
      priority: priority ?? this.priority,
      clientName: clientName ?? this.clientName,
      clientContact: clientContact ?? this.clientContact,
      contactPhone: contactPhone ?? this.contactPhone,
      contactEmail: contactEmail ?? this.contactEmail,
      estimatedEmployees: estimatedEmployees ?? this.estimatedEmployees,
      notes: notes ?? this.notes,
      projectManagerId: projectManagerId ?? this.projectManagerId,
      siteManagerId: siteManagerId ?? this.siteManagerId,
      projectManagerName: projectManagerName ?? this.projectManagerName,
      siteManagerName: siteManagerName ?? this.siteManagerName,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}
