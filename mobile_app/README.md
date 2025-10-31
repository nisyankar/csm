# SPT Mobile - İnşaat Yönetim Sistemi

Flutter tabanlı mobil uygulama (Android & iOS).

## 🚀 Özellikler

### ✅ Tamamlanan (v1.0)
- **Authentication** - Login, Logout, Token Yönetimi
- **Dashboard** - Kullanıcı bilgisi, Hızlı erişim
- **Navigation** - GoRouter ile otomatik auth redirect

### 🔄 Geliştirilecek
- Timesheet (Puantaj) - Clock In/Out
- Progress Payments (Hakediş) - Onay/Red
- Materials (Malzeme) - Stok takibi
- Notifications - Push notifications
- Projects - Proje listesi

## 🔧 Kurulum ve Çalıştırma

### 1. Dependencies Yükleme
```bash
cd mobile_app
flutter pub get
```

### 2. Backend API URL Ayarlama
`lib/core/constants/api_constants.dart` dosyasında:

```dart
// Android Emulator için:
static const String baseUrl = 'http://10.0.2.2/api';

// iOS Simulator için:
// static const String baseUrl = 'http://localhost/api';
```

### 3. Uygulamayı Çalıştırma

**Android Emulator ile:**
```bash
# 1. Emulator başlat (Android Studio'dan veya komut satırından)
# 2. Uygulamayı çalıştır:
flutter run
```

**iOS Simulator ile (sadece Mac):**
```bash
open -a Simulator  # Simulator'ı başlat
flutter run -d ios
```

**Chrome (Web test için):**
```bash
flutter run -d chrome
```

### 4. Hot Reload Kullanımı
Uygulama çalışırken:
- `r` tuşuna basarak Hot Reload
- `R` tuşuna basarak Hot Restart
- `q` tuşuna basarak Quit

## 🧪 Test Kullanıcısı

Backend'de test kullanıcısı oluşturun:

```php
php artisan tinker

$user = User::create([
    'name' => 'Test Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'user_type' => 'admin',
    'is_active' => true,
]);
```

**Login Bilgileri:**
- Email: `admin@test.com`
- Password: `password`

## 📱 Cihaz/Emulator Seçimi

Bağlı cihazları görmek için:
```bash
flutter devices
```

Belirli bir cihazda çalıştırmak için:
```bash
flutter run -d <device-id>
```

## 🏗️ Proje Yapısı

```
lib/
├── core/
│   ├── api/              # Dio API Client
│   ├── constants/        # API & App Constants
│   └── storage/          # Secure Storage
├── features/
│   ├── auth/             # Login/Logout
│   └── dashboard/        # Ana Ekran
└── main.dart
```

## 📚 Kullanılan Paketler

- `flutter_riverpod` - State management
- `dio` - HTTP client
- `flutter_secure_storage` - Token storage
- `go_router` - Navigation
- `firebase_messaging` - Push notifications
- `image_picker` - Camera/Gallery
- `mobile_scanner` - QR scanner

Tüm paketler için: `pubspec.yaml`

## 🔍 Analiz ve Test

```bash
flutter analyze  # Code analysis
flutter test     # Run tests
```

## 📦 Build

**Android APK:**
```bash
flutter build apk --release
```

**iOS IPA (sadece Mac):**
```bash
flutter build ios --release
```

## 📄 API Dokümantasyonu

Backend API dokümantasyonu: `../docs/API-TEST-GUIDE.md`

---

**Versiyon:** 1.0.0
**Son Güncelleme:** 2025-10-31
