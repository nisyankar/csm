import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'location_service.dart';

/// LocationService provider
final locationServiceProvider = Provider<LocationService>((ref) {
  return LocationService();
});