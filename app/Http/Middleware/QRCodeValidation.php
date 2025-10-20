<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class QRCodeValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = 'timesheet'): Response
    {
        $qrData = $request->input('qr_data') ?? $request->header('X-QR-Data');
        
        if (!$qrData) {
            return response()->json([
                'message' => 'QR kod verisi bulunamadı.',
                'required_field' => 'qr_data'
            ], 400);
        }

        // QR kod formatını doğrula
        $validation = $this->validateQRFormat($qrData, $type);
        if (!$validation['valid']) {
            return response()->json([
                'message' => 'QR kod formatı geçersiz.',
                'error' => $validation['error'],
                'expected_format' => $validation['expected_format']
            ], 400);
        }

        // QR kod süresini kontrol et
        if ($this->isQRExpired($validation['data'])) {
            return response()->json([
                'message' => 'QR kod süresi dolmuş.',
                'expired_at' => $validation['data']['expires_at'] ?? null
            ], 400);
        }

        // QR kod kullanılmış mı kontrol et (tek kullanımlık ise)
        if ($validation['data']['single_use'] ?? false) {
            if ($this->isQRUsed($qrData)) {
                return response()->json([
                    'message' => 'Bu QR kod daha önce kullanılmış.',
                    'qr_id' => $validation['data']['id'] ?? null
                ], 400);
            }
            
            // QR kod kullanıldı olarak işaretle
            $this->markQRAsUsed($qrData);
        }

        // Doğrulanmış QR verilerini request'e ekle
        $request->merge([
            'validated_qr_data' => $validation['data']
        ]);

        return $next($request);
    }

    /**
     * QR kod formatını doğrula
     */
    private function validateQRFormat(string $qrData, string $type): array
    {
        try {
            $decoded = base64_decode($qrData);
            if (!$decoded) {
                return [
                    'valid' => false,
                    'error' => 'QR kod decode edilemedi.',
                    'expected_format' => 'Base64 encoded JSON'
                ];
            }

            $data = json_decode($decoded, true);
            if (!$data) {
                return [
                    'valid' => false,
                    'error' => 'QR kod JSON formatında değil.',
                    'expected_format' => 'Base64 encoded JSON'
                ];
            }

            // Tip bazlı validasyon
            $validation = match($type) {
                'timesheet' => $this->validateTimesheetQR($data),
                'employee' => $this->validateEmployeeQR($data),
                'project' => $this->validateProjectQR($data),
                'access' => $this->validateAccessQR($data),
                default => $this->validateGenericQR($data)
            };

            if ($validation['valid']) {
                $validation['data'] = $data;
            }

            return $validation;

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => 'QR kod parse edilemedi: ' . $e->getMessage(),
                'expected_format' => 'Valid Base64 encoded JSON'
            ];
        }
    }

    /**
     * Puantaj QR kod validasyonu
     */
    private function validateTimesheetQR(array $data): array
    {
        $required = ['employee_id', 'project_id', 'action', 'timestamp'];
        $missing = array_diff($required, array_keys($data));
        
        if (!empty($missing)) {
            return [
                'valid' => false,
                'error' => 'Eksik alanlar: ' . implode(', ', $missing),
                'expected_format' => 'JSON with fields: ' . implode(', ', $required)
            ];
        }

        if (!in_array($data['action'], ['checkin', 'checkout', 'break_start', 'break_end'])) {
            return [
                'valid' => false,
                'error' => 'Geçersiz action değeri.',
                'expected_format' => 'action: checkin|checkout|break_start|break_end'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Çalışan QR kod validasyonu
     */
    private function validateEmployeeQR(array $data): array
    {
        $required = ['employee_id', 'employee_code'];
        $missing = array_diff($required, array_keys($data));
        
        if (!empty($missing)) {
            return [
                'valid' => false,
                'error' => 'Eksik alanlar: ' . implode(', ', $missing),
                'expected_format' => 'JSON with fields: ' . implode(', ', $required)
            ];
        }

        return ['valid' => true];
    }

    /**
     * Proje QR kod validasyonu
     */
    private function validateProjectQR(array $data): array
    {
        $required = ['project_id', 'project_code'];
        $missing = array_diff($required, array_keys($data));
        
        if (!empty($missing)) {
            return [
                'valid' => false,
                'error' => 'Eksik alanlar: ' . implode(', ', $missing),
                'expected_format' => 'JSON with fields: ' . implode(', ', $required)
            ];
        }

        return ['valid' => true];
    }

    /**
     * Erişim QR kod validasyonu
     */
    private function validateAccessQR(array $data): array
    {
        $required = ['access_type', 'location', 'timestamp'];
        $missing = array_diff($required, array_keys($data));
        
        if (!empty($missing)) {
            return [
                'valid' => false,
                'error' => 'Eksik alanlar: ' . implode(', ', $missing),
                'expected_format' => 'JSON with fields: ' . implode(', ', $required)
            ];
        }

        return ['valid' => true];
    }

    /**
     * Genel QR kod validasyonu
     */
    private function validateGenericQR(array $data): array
    {
        if (!isset($data['type'])) {
            return [
                'valid' => false,
                'error' => 'QR kod tipi belirtilmemiş.',
                'expected_format' => 'JSON with type field'
            ];
        }

        return ['valid' => true];
    }

    /**
     * QR kod süresi dolmuş mu?
     */
    private function isQRExpired(array $data): bool
    {
        if (!isset($data['expires_at'])) {
            return false; // Süresiz QR kod
        }

        return now()->timestamp > $data['expires_at'];
    }

    /**
     * QR kod daha önce kullanılmış mı?
     */
    private function isQRUsed(string $qrData): bool
    {
        $cacheKey = 'used_qr:' . hash('sha256', $qrData);
        return Cache::has($cacheKey);
    }

    /**
     * QR kod kullanıldı olarak işaretle
     */
    private function markQRAsUsed(string $qrData): void
    {
        $cacheKey = 'used_qr:' . hash('sha256', $qrData);
        // 24 saat boyunca cache'de tut
        Cache::put($cacheKey, true, now()->addDay());
    }
}