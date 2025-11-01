# SPT Mobile - İnşaat Yönetim Sistemi

Flutter tabanlı mobil uygulama (Android & iOS).

## 🚀 Özellikler

### ✅ Tamamlanan Modüller
- **Authentication** - Login, Logout, Token Yönetimi
- **Dashboard** - Kullanıcı bilgisi, Hızlı erişim kartları (5 modül)
- **Projects (Projeler)** - Proje listesi, detay, filtreleme, sonsuz kaydırma
- **Quantities (Metraj)** - Metraj listesi, detay, doğrulama, onaylama, filtreleme
- **Navigation** - GoRouter ile otomatik auth redirect

### 🔄 Geliştirilecek Modüller
- Timesheet (Puantaj) - Clock In/Out, QR kod okutma
- Progress Payments (Hakediş) - Onay/Red işlemleri
- Materials (Malzeme) - Stok takibi, malzeme yönetimi
- Notifications - Push notifications, bildirim yönetimi

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
│   ├── api/              # Dio API Client, Interceptors
│   ├── constants/        # API & App Constants
│   └── storage/          # Secure Storage (Token)
├── features/
│   ├── auth/             # Login/Logout
│   │   ├── data/         # Repositories
│   │   ├── domain/       # Entities, Use Cases
│   │   └── presentation/ # Pages, Providers, State
│   ├── dashboard/        # Ana Ekran
│   │   └── presentation/ # Dashboard sayfası
│   ├── projects/         # Projeler Modülü
│   │   ├── data/         # Models, Repository Impl
│   │   ├── domain/       # Entities, Repository Interface
│   │   └── presentation/ # Pages, Providers, State
│   └── quantities/       # Metraj Modülü
│       ├── data/         # Models, Repository Impl
│       ├── domain/       # Entities, Repository Interface
│       └── presentation/ # Pages, Providers, State
└── main.dart             # App Entry Point
```

**Clean Architecture:**
- `domain/` - Business logic, entities, repository interfaces
- `data/` - API models, repository implementations
- `presentation/` - UI pages, state management (Riverpod)

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

## 🎨 Genel Kurallar ve Pattern'ler

### 1. Statü Değişiklikleri Sonrası Otomatik Refresh
**Kural:** Kullanıcı bir işlem yaptığında (doğrulama, onaylama, güncelleme, silme vb.), sayfa otomatik olarak yenilenmelidir.

**Örnek** (Metraj Doğrulama):
```dart
Future<void> _onVerify() async {
  final confirmed = await _showConfirmDialog(...);

  if (confirmed == true) {
    await ref.read(quantityDetailProvider.notifier).verify(widget.quantityId);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(...);
      // ✅ Otomatik refresh
      await _onRefresh();
    }
  }
}
```

**Uygulandığı Modüller:**
- ✅ Quantities (Metraj) - Doğrulama ve onaylama sonrası
- 🔜 Timesheet - Check-in/out sonrası
- 🔜 Leave Requests - Onay/red sonrası

### 2. Kullanıcı Geri Bildirimi
**Kural:** Her işlem sonucunda kullanıcıya SnackBar ile bilgi verilmelidir.

```dart
// Başarılı işlem
ScaffoldMessenger.of(context).showSnackBar(
  const SnackBar(
    content: Text('İşlem başarılı'),
    backgroundColor: Colors.green,
  ),
);

// Hata durumu
ScaffoldMessenger.of(context).showSnackBar(
  const SnackBar(
    content: Text('Bir hata oluştu'),
    backgroundColor: Colors.red,
  ),
);
```

### 3. Onay Diyalogları
**Kural:** Geri alınamaz işlemler (silme, onaylama, vb.) için onay diyalogu kullanılmalıdır.

```dart
Future<bool?> _showConfirmDialog(String title, String message) {
  return showDialog<bool>(
    context: context,
    builder: (context) => AlertDialog(
      title: Text(title),
      content: Text(message),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(false),
          child: const Text('İptal'),
        ),
        ElevatedButton(
          onPressed: () => Navigator.of(context).pop(true),
          child: const Text('Onayla'),
        ),
      ],
    ),
  );
}
```

### 4. API Resource Pattern (Backend)
**Kural:** API Response'lar her zaman Resource sınıfları kullanılarak dönülmelidir.

```php
// ✅ Doğru
public function show(Quantity $quantity): JsonResponse
{
    $quantity->load(['project', 'workItem']);
    return response()->json([
        'success' => true,
        'data' => new QuantityResource($quantity),
    ]);
}

// ❌ Yanlış - Model doğrudan dönülmemeli
return response()->json(['data' => $quantity]);
```

### 5. Pull-to-Refresh ve Infinite Scroll
**Kural:** Liste sayfalarında her zaman:
- Pull-to-refresh (RefreshIndicator)
- Infinite scroll (ScrollController)
- Loading indicators

Olmalıdır.

### 6. State Management
**Kural:** Riverpod StateNotifier kullanılarak state yönetilmelidir.

```dart
@riverpod
class QuantityDetail extends _$QuantityDetail {
  @override
  QuantityDetailState build() => const QuantityDetailState();

  Future<void> refresh(int id) async { /* ... */ }
  Future<void> verify(int id) async { /* ... */ }
}
```

### 7. Error Handling
**Kural:** Tüm API çağrılarında try-catch kullanılmalı ve kullanıcıya anlamlı hata mesajı gösterilmelidir.

```dart
try {
  final result = await _repository.verify(id);
  state = state.copyWith(quantity: result, isLoading: false);
} catch (e) {
  state = state.copyWith(
    error: e.toString(),
    isLoading: false,
  );
}
```

## 🎯 Tamamlanan Modüller Detayı

### Projects (Projeler)
- ✅ Liste sayfası (filtreleme, arama, sıralama)
- ✅ Detay sayfası (proje bilgileri, finansal durum, zaman çizelgesi)
- ✅ Infinite scroll (sayfalama)
- ✅ Pull-to-refresh
- ✅ Status ve öncelik filtreleri

### Quantities (Metraj)
- ✅ Liste sayfası (arama, filtreleme)
- ✅ Detay sayfası (metraj bilgileri, konum, ölçüm detayları)
- ✅ Doğrulama (Verification) işlemi
- ✅ Onaylama (Approval) işlemi
- ✅ Progress bar ile tamamlanma oranı
- ✅ İki aşamalı onay süreci (Doğrulama → Onay)
- ✅ Infinite scroll

---

**Versiyon:** 1.1.0
**Son Güncelleme:** 2025-11-01
