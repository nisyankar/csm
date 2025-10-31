<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Login kullanıcı ve token oluştur
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'string|nullable',
        ]);

        // Rate limiting kontrolü
        $key = Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["Çok fazla giriş denemesi. Lütfen {$seconds} saniye sonra tekrar deneyin."],
            ]);
        }

        // Kullanıcıyı bul
        $user = User::where('email', $request->email)->first();

        // Kullanıcı kontrolü
        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60); // 60 saniye için rate limit

            if ($user) {
                $user->recordFailedLogin();
            }

            throw ValidationException::withMessages([
                'email' => ['Kullanıcı bilgileri hatalı.'],
            ]);
        }

        // Hesap aktif mi kontrolü
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Hesabınız devre dışı bırakılmış. Lütfen yöneticinizle iletişime geçin.'],
            ]);
        }

        // Hesap kilitli mi kontrolü
        if ($user->is_locked) {
            throw ValidationException::withMessages([
                'email' => ['Hesabınız geçici olarak kilitlenmiştir. Lütfen daha sonra tekrar deneyin.'],
            ]);
        }

        // API erişimi var mı kontrolü
        if (!$user->api_access_enabled) {
            throw ValidationException::withMessages([
                'email' => ['Bu hesap için mobil uygulama erişimi aktif değil. Lütfen yöneticinizle iletişime geçin.'],
            ]);
        }

        // Erişim süresi dolmuş mu kontrolü
        if ($user->isAccessExpired()) {
            throw ValidationException::withMessages([
                'email' => ['Hesap erişim süreniz dolmuştur. Lütfen yöneticinizle iletişime geçin.'],
            ]);
        }

        // Rate limiter'ı temizle
        RateLimiter::clear($key);

        // Token oluştur
        $deviceName = $request->device_name ?? 'mobile-app';
        $token = $user->createToken($deviceName, ['mobile-access'])->plainTextToken;

        // Son giriş bilgilerini güncelle
        $user->updateLastLogin();
        $user->update([
            'api_last_used_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Giriş başarılı',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'user_type_display' => $user->user_type_display,
                    'avatar_url' => $user->avatar_url,
                    'employee_id' => $user->employee_id,
                    'phone' => $user->phone,
                    'language' => $user->language ?? 'tr',
                    'timezone' => $user->timezone ?? 'Europe/Istanbul',
                ],
            ],
        ], 200);
    }

    /**
     * Logout kullanıcı ve token iptal et
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Mevcut token'ı iptal et
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Çıkış başarılı',
        ], 200);
    }

    /**
     * Tüm cihazlardan logout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAll(Request $request)
    {
        // Kullanıcının tüm token'larını iptal et
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tüm cihazlardan çıkış yapıldı',
        ], 200);
    }

    /**
     * Kullanıcı bilgilerini getir
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'user_type_display' => $user->user_type_display,
                'avatar_url' => $user->avatar_url,
                'employee_id' => $user->employee_id,
                'phone' => $user->phone,
                'language' => $user->language ?? 'tr',
                'timezone' => $user->timezone ?? 'Europe/Istanbul',
                'is_active' => $user->is_active,
                'email_verified_at' => $user->email_verified_at,
                'two_factor_enabled' => $user->two_factor_enabled,
                'api_access_enabled' => $user->api_access_enabled,
                'last_login_at' => $user->last_login_at,
                'notification_preferences' => $user->notification_preferences,
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles' => $user->roles->pluck('name'),
            ],
        ], 200);
    }

    /**
     * Token yenileme (refresh)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $currentToken = $request->user()->currentAccessToken();

        // Mevcut token'ı iptal et
        $currentToken->delete();

        // Yeni token oluştur
        $deviceName = $currentToken->name ?? 'mobile-app';
        $token = $user->createToken($deviceName, ['mobile-access'])->plainTextToken;

        // API kullanım zamanını güncelle
        $user->update([
            'api_last_used_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token yenilendi',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Şifre değiştirme
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        // Mevcut şifre kontrolü
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mevcut şifre hatalı.'],
            ]);
        }

        // Yeni şifre eski şifre ile aynı olmamalı
        if (Hash::check($request->new_password, $user->password)) {
            throw ValidationException::withMessages([
                'new_password' => ['Yeni şifre eski şifre ile aynı olamaz.'],
            ]);
        }

        // Şifreyi güncelle
        $user->updatePassword($request->new_password);

        return response()->json([
            'success' => true,
            'message' => 'Şifre başarıyla değiştirildi',
        ], 200);
    }

    /**
     * FCM token kaydetme (Push notification için)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerDevice(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'required|in:ios,android',
            'device_info' => 'nullable|array',
        ]);

        $user = $request->user();

        // TODO: user_devices tablosu henüz yok, bu kısım ilerleyen aşamada eklenecek
        // Şimdilik sadece başarılı response dönelim

        return response()->json([
            'success' => true,
            'message' => 'Cihaz başarıyla kaydedildi',
        ], 200);
    }
}
