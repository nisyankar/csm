import '../../domain/entities/project.dart';

/// Data model for Project API responses
/// Handles JSON serialization/deserialization
class ProjectModel extends Project {
  const ProjectModel({
    required super.id,
    required super.projectCode,
    required super.name,
    super.description,
    required super.location,
    required super.city,
    super.district,
    super.fullAddress,
    super.coordinates,
    required super.startDate,
    required super.plannedEndDate,
    super.actualEndDate,
    super.budget,
    super.laborBudget,
    required super.spentAmount,
    required super.status,
    required super.type,
    required super.priority,
    super.clientName,
    super.clientContact,
    super.contactPhone,
    super.contactEmail,
    super.estimatedEmployees,
    super.notes,
    super.projectManagerId,
    super.siteManagerId,
    super.projectManagerName,
    super.siteManagerName,
    required super.createdAt,
    required super.updatedAt,
  });

  /// Create ProjectModel from JSON
  factory ProjectModel.fromJson(Map<String, dynamic> json) {
    return ProjectModel(
      id: json['id'] as int,
      projectCode: json['project_code'] as String,
      name: json['name'] as String,
      description: json['description'] as String?,
      location: json['location'] as String,
      city: json['city'] as String,
      district: json['district'] as String?,
      fullAddress: json['full_address'] as String?,
      coordinates: json['coordinates'] as String?,
      startDate: DateTime.parse(json['start_date'] as String),
      plannedEndDate: DateTime.parse(json['planned_end_date'] as String),
      actualEndDate: json['actual_end_date'] != null
          ? DateTime.parse(json['actual_end_date'] as String)
          : null,
      budget: json['budget'] != null
          ? double.parse(json['budget'].toString())
          : null,
      laborBudget: json['labor_budget'] != null
          ? double.parse(json['labor_budget'].toString())
          : null,
      spentAmount: json['spent_amount'] != null
          ? double.parse(json['spent_amount'].toString())
          : 0.0,
      status: json['status'] as String,
      type: json['type'] as String,
      priority: json['priority'] as String,
      clientName: json['client_name'] as String?,
      clientContact: json['client_contact'] as String?,
      contactPhone: json['contact_phone'] as String?,
      contactEmail: json['contact_email'] as String?,
      estimatedEmployees: json['estimated_employees'] as int?,
      notes: json['notes'] as String?,
      projectManagerId: json['project_manager_id'] as int?,
      siteManagerId: json['site_manager_id'] as int?,
      projectManagerName: _extractManagerName(json, 'project_manager'),
      siteManagerName: _extractManagerName(json, 'site_manager'),
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }

  /// Extract manager name from nested object or null
  static String? _extractManagerName(
      Map<String, dynamic> json, String key) {
    if (json[key] == null) return null;
    if (json[key] is Map) {
      final manager = json[key] as Map<String, dynamic>;
      return '${manager['first_name'] ?? ''} ${manager['last_name'] ?? ''}'
          .trim();
    }
    return null;
  }

  /// Convert ProjectModel to JSON
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'project_code': projectCode,
      'name': name,
      'description': description,
      'location': location,
      'city': city,
      'district': district,
      'full_address': fullAddress,
      'coordinates': coordinates,
      'start_date': startDate.toIso8601String(),
      'planned_end_date': plannedEndDate.toIso8601String(),
      'actual_end_date': actualEndDate?.toIso8601String(),
      'budget': budget,
      'labor_budget': laborBudget,
      'spent_amount': spentAmount,
      'status': status,
      'type': type,
      'priority': priority,
      'client_name': clientName,
      'client_contact': clientContact,
      'contact_phone': contactPhone,
      'contact_email': contactEmail,
      'estimated_employees': estimatedEmployees,
      'notes': notes,
      'project_manager_id': projectManagerId,
      'site_manager_id': siteManagerId,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  /// Convert to domain entity
  Project toDomain() {
    return Project(
      id: id,
      projectCode: projectCode,
      name: name,
      description: description,
      location: location,
      city: city,
      district: district,
      fullAddress: fullAddress,
      coordinates: coordinates,
      startDate: startDate,
      plannedEndDate: plannedEndDate,
      actualEndDate: actualEndDate,
      budget: budget,
      laborBudget: laborBudget,
      spentAmount: spentAmount,
      status: status,
      type: type,
      priority: priority,
      clientName: clientName,
      clientContact: clientContact,
      contactPhone: contactPhone,
      contactEmail: contactEmail,
      estimatedEmployees: estimatedEmployees,
      notes: notes,
      projectManagerId: projectManagerId,
      siteManagerId: siteManagerId,
      projectManagerName: projectManagerName,
      siteManagerName: siteManagerName,
      createdAt: createdAt,
      updatedAt: updatedAt,
    );
  }
}
