import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart';

/// GPS konum servisi
/// Konum izinlerini yönetir ve GPS koordinatlarını alır
class LocationService {
  /// GPS konumunu al
  ///
  /// [forceRequest] true ise, izin yoksa kullanıcıdan izin iste
  ///
  /// Returns: Position nesnesi veya hata durumunda exception fırlatır
  Future<Position> getCurrentLocation({bool forceRequest = true}) async {
    // Konum servisinin etkin olup olmadığını kontrol et
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw AppLocationServiceDisabledException(
        'Konum servisi kapalı. Lütfen GPS\'i açın.',
      );
    }

    // İzin durumunu kontrol et
    LocationPermission permission = await Geolocator.checkPermission();

    if (permission == LocationPermission.denied) {
      if (!forceRequest) {
        throw AppLocationPermissionDeniedException(
          'Konum izni reddedildi.',
        );
      }

      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        throw AppLocationPermissionDeniedException(
          'Konum izni reddedildi.',
        );
      }
    }

    if (permission == LocationPermission.deniedForever) {
      throw AppLocationPermissionPermanentlyDeniedException(
        'Konum izni kalıcı olarak reddedildi. Lütfen uygulama ayarlarından izin verin.',
      );
    }

    // Konumu al
    try {
      Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );
      return position;
    } catch (e) {
      throw AppLocationException('Konum alınamadı: ${e.toString()}');
    }
  }

  /// Konum izninin durumunu kontrol et
  Future<bool> hasLocationPermission() async {
    LocationPermission permission = await Geolocator.checkPermission();
    return permission == LocationPermission.whileInUse ||
        permission == LocationPermission.always;
  }

  /// Konum servisinin aktif olup olmadığını kontrol et
  Future<bool> isLocationServiceEnabled() async {
    return await Geolocator.isLocationServiceEnabled();
  }

  /// Uygulama ayarlarını aç (kalıcı izin reddi durumunda)
  Future<void> openAppSettings() async {
    await Geolocator.openAppSettings();
  }

  /// Uygulama konum ayarlarını aç
  Future<void> openLocationSettings() async {
    await Geolocator.openLocationSettings();
  }

  /// İki GPS koordinatı arasındaki mesafeyi hesapla (metre cinsinden)
  double calculateDistance(
    double lat1,
    double lon1,
    double lat2,
    double lon2,
  ) {
    return Geolocator.distanceBetween(lat1, lon1, lat2, lon2);
  }

  /// Konum akışını dinle (gerçek zamanlı konum takibi için)
  Stream<Position> getLocationStream() {
    return Geolocator.getPositionStream(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 10,
      ),
    );
  }
}

/// Konum servisi kapalı hatası
class AppLocationServiceDisabledException implements Exception {
  final String message;
  AppLocationServiceDisabledException(this.message);

  @override
  String toString() => message;
}

/// Konum izni reddedildi hatası
class AppLocationPermissionDeniedException implements Exception {
  final String message;
  AppLocationPermissionDeniedException(this.message);

  @override
  String toString() => message;
}

/// Konum izni kalıcı olarak reddedildi hatası
class AppLocationPermissionPermanentlyDeniedException implements Exception {
  final String message;
  AppLocationPermissionPermanentlyDeniedException(this.message);

  @override
  String toString() => message;
}

/// Genel konum hatası
class AppLocationException implements Exception {
  final String message;
  AppLocationException(this.message);

  @override
  String toString() => message;
}
