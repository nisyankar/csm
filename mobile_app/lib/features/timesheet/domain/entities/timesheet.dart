class Timesheet {
  final int id;
  final int employeeId;
  final int? projectId;
  final DateTime date;
  final String? checkInTime;
  final String? checkOutTime;
  final String? checkInMethod;
  final String? checkOutMethod;
  final String? checkInLocation;
  final String? checkOutLocation;
  final double? totalHours;
  final double? regularHours;
  final double? overtimeHours;
  final int? breakDuration;
  final String status;
  final String approvalStatus;
  final String? notes;
  final String? rejectionReason;
  final bool isOvertime;
  final bool isLate;
  final bool isEarlyLeave;
  final String? employeeName;
  final String? employeeCode;
  final String? projectName;
  final String? projectCode;
  final DateTime? approvedAt;
  final DateTime createdAt;
  final DateTime updatedAt;

  const Timesheet({
    required this.id,
    required this.employeeId,
    this.projectId,
    required this.date,
    this.checkInTime,
    this.checkOutTime,
    this.checkInMethod,
    this.checkOutMethod,
    this.checkInLocation,
    this.checkOutLocation,
    this.totalHours,
    this.regularHours,
    this.overtimeHours,
    this.breakDuration,
    required this.status,
    required this.approvalStatus,
    this.notes,
    this.rejectionReason,
    required this.isOvertime,
    required this.isLate,
    required this.isEarlyLeave,
    this.employeeName,
    this.employeeCode,
    this.projectName,
    this.projectCode,
    this.approvedAt,
    required this.createdAt,
    required this.updatedAt,
  });

  /// Durum etiketi - Türkçe
  String get durumEtiketi {
    switch (status) {
      case 'active':
        return 'Aktif';
      case 'completed':
        return 'Tamamlandı';
      case 'present':
        return 'Çalışıyor';
      case 'absent':
        return 'Yok';
      case 'leave':
        return 'İzinli';
      case 'holiday':
        return 'Tatil';
      default:
        return status;
    }
  }

  /// Onay durumu etiketi - Türkçe
  String get onayDurumuEtiketi {
    switch (approvalStatus) {
      case 'pending':
        return 'Bekliyor';
      case 'approved':
        return 'Onaylandı';
      case 'rejected':
        return 'Reddedildi';
      default:
        return approvalStatus;
    }
  }

  /// Giriş yöntemi etiketi - Türkçe
  String get girisYontemiEtiketi {
    if (checkInMethod == null) return '-';
    switch (checkInMethod) {
      case 'manual':
        return 'Manuel';
      case 'qr':
        return 'QR Kod';
      case 'gps':
        return 'GPS';
      case 'nfc':
        return 'NFC';
      case 'biometric':
        return 'Biyometrik';
      case 'mobile_offline':
        return 'Mobil (Offline)';
      default:
        return checkInMethod!;
    }
  }

  /// Çıkış yöntemi etiketi - Türkçe
  String get cikisYontemiEtiketi {
    if (checkOutMethod == null) return '-';
    switch (checkOutMethod) {
      case 'manual':
        return 'Manuel';
      case 'qr':
        return 'QR Kod';
      case 'gps':
        return 'GPS';
      case 'nfc':
        return 'NFC';
      case 'biometric':
        return 'Biyometrik';
      case 'mobile_offline':
        return 'Mobil (Offline)';
      default:
        return checkOutMethod!;
    }
  }

  /// Check-in yapılmış mı?
  bool get hasCheckedIn => checkInTime != null;

  /// Check-out yapılmış mı?
  bool get hasCheckedOut => checkOutTime != null;

  /// Onaylandı mı?
  bool get isApproved => approvalStatus == 'approved';

  /// Reddedildi mi?
  bool get isRejected => approvalStatus == 'rejected';

  /// Beklemede mi?
  bool get isPending => approvalStatus == 'pending';

  /// Toplam çalışma saati - Metin olarak
  String get totalHoursText {
    if (totalHours == null || totalHours == 0) return '-';
    final hours = totalHours!.floor();
    final minutes = ((totalHours! - hours) * 60).round();
    return '${hours}s ${minutes}dk';
  }

  /// Normal çalışma saati - Metin olarak
  String get regularHoursText {
    if (regularHours == null || regularHours == 0) return '-';
    final hours = regularHours!.floor();
    final minutes = ((regularHours! - hours) * 60).round();
    return '${hours}s ${minutes}dk';
  }

  String get overtimeHoursText {
    if (overtimeHours == null || overtimeHours == 0) return '-';
    final hours = overtimeHours!.floor();
    final minutes = ((overtimeHours! - hours) * 60).round();
    return '${hours}s ${minutes}dk';
  }

  /// Çalışma süresi hesapla (saat cinsinden)
  double? get workingHours {
    if (checkInTime == null) return null;
    if (checkOutTime == null) return null;

    // Parse check-in and check-out times
    final checkIn = DateTime.parse('2000-01-01 $checkInTime');
    final checkOut = DateTime.parse('2000-01-01 $checkOutTime');

    final duration = checkOut.difference(checkIn);
    return duration.inMinutes / 60.0;
  }
}
