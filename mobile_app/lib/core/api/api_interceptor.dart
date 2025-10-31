import 'package:dio/dio.dart';
import 'package:logger/logger.dart';
import '../constants/api_constants.dart';
import '../storage/secure_storage.dart';

class ApiInterceptor extends Interceptor {
  final SecureStorage _storage;
  final Logger _logger;

  ApiInterceptor(this._storage, this._logger);

  @override
  void onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    // Add Authorization token if available
    final token = await _storage.getToken();
    if (token != null) {
      options.headers[ApiConstants.authorizationHeader] =
          '${ApiConstants.bearerPrefix} $token';
    }

    _logger.d('Request: ${options.method} ${options.path}');
    super.onRequest(options, handler);
  }

  @override
  void onResponse(
    Response response,
    ResponseInterceptorHandler handler,
  ) {
    _logger.d('Response: ${response.statusCode} ${response.requestOptions.path}');
    super.onResponse(response, handler);
  }

  @override
  void onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    _logger.e('Error: ${err.response?.statusCode} ${err.requestOptions.path}');

    // Handle 401 Unauthorized - Token expired or invalid
    if (err.response?.statusCode == 401) {
      _logger.w('Unauthorized: Token may be expired or invalid');
      // Clear token and user data
      await _storage.clearAll();
      // TODO: Navigate to login screen
    }

    super.onError(err, handler);
  }
}
