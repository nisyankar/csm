import 'dart:convert';
import '../../domain/entities/timesheet.dart';

class TimesheetModel extends Timesheet {
  const TimesheetModel({
    required super.id,
    required super.employeeId,
    super.projectId,
    required super.date,
    super.checkInTime,
    super.checkOutTime,
    super.checkInMethod,
    super.checkOutMethod,
    super.checkInLocation,
    super.checkOutLocation,
    super.totalHours,
    super.regularHours,
    super.overtimeHours,
    super.breakDuration,
    required super.status,
    required super.approvalStatus,
    super.notes,
    super.rejectionReason,
    required super.isOvertime,
    required super.isLate,
    required super.isEarlyLeave,
    super.employeeName,
    super.employeeCode,
    super.projectName,
    super.projectCode,
    super.approvedAt,
    required super.createdAt,
    required super.updatedAt,
  });

  factory TimesheetModel.fromJson(Map<String, dynamic> json) {
    return TimesheetModel(
      id: json['id'] as int,
      employeeId: json['employee_id'] as int,
      projectId: json['project_id'] as int?,
      date: DateTime.parse(json['date'] as String),
      checkInTime: json['check_in_time'] as String?,
      checkOutTime: json['check_out_time'] as String?,
      checkInMethod: json['check_in_method'] as String?,
      checkOutMethod: json['check_out_method'] as String?,
      checkInLocation: _parseLocation(json['check_in_location']),
      checkOutLocation: _parseLocation(json['check_out_location']),
      totalHours: _parseDouble(json['total_hours']),
      regularHours: _parseDouble(json['regular_hours']),
      overtimeHours: _parseDouble(json['overtime_hours']),
      breakDuration: _parseDouble(json['break_duration'])?.toInt(),
      status: json['status'] as String? ?? 'active',
      approvalStatus: json['approval_status'] as String? ?? 'pending',
      notes: json['notes'] as String?,
      rejectionReason: json['rejection_reason'] as String?,
      isOvertime: _parseBool(json['is_overtime']),
      isLate: _parseBool(json['is_late']),
      isEarlyLeave: _parseBool(json['is_early_leave']),
      employeeName: json['employee_name'] as String?,
      employeeCode: json['employee_code'] as String?,
      projectName: json['project_name'] as String?,
      projectCode: json['project_code'] as String?,
      approvedAt: json['approved_at'] != null
          ? DateTime.parse(json['approved_at'] as String)
          : null,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'employee_id': employeeId,
      'project_id': projectId,
      'date': date.toIso8601String().split('T')[0],
      'check_in_time': checkInTime,
      'check_out_time': checkOutTime,
      'check_in_method': checkInMethod,
      'check_out_method': checkOutMethod,
      'check_in_location': checkInLocation,
      'check_out_location': checkOutLocation,
      'total_hours': totalHours,
      'regular_hours': regularHours,
      'overtime_hours': overtimeHours,
      'break_duration': breakDuration,
      'status': status,
      'approval_status': approvalStatus,
      'notes': notes,
      'rejection_reason': rejectionReason,
      'is_overtime': isOvertime,
      'is_late': isLate,
      'is_early_leave': isEarlyLeave,
      'employee_name': employeeName,
      'employee_code': employeeCode,
      'project_name': projectName,
      'project_code': projectCode,
      'approved_at': approvedAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  // Double dönüşüm yardımcısı (API'den int, double veya String gelebilir)
  static double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }

  // Boolean dönüşüm yardımcısı (veritabanından 0/1 gelir)
  static bool _parseBool(dynamic value) {
    if (value == null) return false;
    if (value is bool) return value;
    if (value is int) return value == 1;
    if (value is String) return value.toLowerCase() == 'true' || value == '1';
    return false;
  }

  // Konum dönüşüm yardımcısı (API'den JSON object gelir)
  static String? _parseLocation(dynamic value) {
    if (value == null) return null;
    if (value is String) return value;
    if (value is Map) return json.encode(value);
    return null;
  }

  // Clock-in için (sunucuya gönderilecek)
  Map<String, dynamic> toClockInJson({
    required int projectId,
    String? checkInMethod,
    Map<String, double>? location,
    String? notes,
  }) {
    return {
      'project_id': projectId,
      if (checkInMethod != null) 'check_in_method': checkInMethod,
      if (location != null)
        'check_in_location': {
          'latitude': location['latitude'],
          'longitude': location['longitude'],
        },
      if (notes != null) 'notes': notes,
    };
  }

  // Clock-out için (sunucuya gönderilecek)
  Map<String, dynamic> toClockOutJson({
    int? timesheetId,
    String? checkOutMethod,
    Map<String, double>? location,
    String? notes,
  }) {
    return {
      if (timesheetId != null) 'timesheet_id': timesheetId,
      if (checkOutMethod != null) 'check_out_method': checkOutMethod,
      if (location != null)
        'check_out_location': {
          'latitude': location['latitude'],
          'longitude': location['longitude'],
        },
      if (notes != null) 'notes': notes,
    };
  }

  // Offline sync için
  Map<String, dynamic> toOfflineSyncJson() {
    return {
      'project_id': projectId,
      'work_date': date.toIso8601String().split('T')[0],
      'check_in_time': checkInTime,
      if (checkOutTime != null) 'check_out_time': checkOutTime,
      if (notes != null) 'notes': notes,
    };
  }
}
