# 🚀 Hızlı Başlangıç Kılavuzu

## 📍 Neredeyim?

Bu **Şantiye Süreç Takip Sistemi (SPT)** projesidir.
- ✅ **Mevcut Durum:** %67 tamamlanmış, çalışır durumda
- 🎯 **Hedef:** Livera Taşdelen şantiye süreçlerini tamamen dijitalleştirmek

---

## 🎯 Öncelikli Yapılacaklar (Bu Hafta)

### 1️⃣ Satın Alma Modülü - Veritabanı
```bash
# Yapılacak:
- [ ] purchasing_requests migration oluştur
- [ ] purchasing_items migration oluştur
- [ ] suppliers migration oluştur
- [ ] Model'leri oluştur ve ilişkileri tanımla
```

**Dosyalar:**
- `database/migrations/xxxx_create_purchasing_tables.php`
- `app/Models/PurchasingRequest.php`
- `app/Models/Supplier.php`

### 2️⃣ Yemek Sipariş - Basit Ekran
```bash
# Yapılacak:
- [ ] meal_orders migration oluştur
- [ ] Günlük sipariş ekranı (Vue component)
- [ ] Basit CRUD işlemleri
```

**Dosyalar:**
- `database/migrations/xxxx_create_meal_orders.php`
- `resources/js/Pages/Meals/Daily.vue`
- `app/Http/Controllers/MealOrderController.php`

### 3️⃣ Puantaj - Toplu Giriş
```bash
# Yapılacak:
- [ ] Toplu puantaj ekranı (tablo bazlı)
- [ ] Hızlı kaydetme butonu
- [ ] Excel import fonksiyonu
```

**Dosyalar:**
- `resources/js/Pages/Timesheets/BulkEntry.vue`
- `app/Http/Controllers/TimesheetController.php` (genişlet)

---

## 📂 Proje Yapısı

```
spt/
├── app/
│   ├── Models/              # ✅ Employee, Timesheet, LeaveRequest vb.
│   ├── Http/Controllers/    # ✅ 20+ controller
│   └── Helpers/             # ✅ PermissionHelper
├── database/
│   └── migrations/          # ✅ 25+ migration
├── resources/
│   └── js/
│       ├── Pages/           # ✅ Employee, Leave, Dashboard sayfaları
│       ├── Components/      # ✅ UI components
│       └── Layouts/         # ✅ AppLayout, Sidebar, Navbar
├── routes/
│   ├── web.php             # ✅ 500+ satır route tanımı
│   └── api.php             # ⚠️ Minimal
└── PROJE_PLANI.md          # 📋 Detaylı plan (checkbox)
```

---

## 🛠️ Geliştirme Ortamı

### Başlatma Komutları
```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite (Frontend)
npm run dev

# Terminal 3: Queue (opsiyonel)
php artisan queue:work
```

### Veritabanı
```bash
# Migration çalıştır
php artisan migrate

# Seed (test verisi)
php artisan db:seed
```

### Yeni Model Oluşturma
```bash
# Model + Migration + Controller
php artisan make:model PurchasingRequest -mc

# Sadece Migration
php artisan make:migration create_purchasing_requests_table
```

### Yeni Vue Sayfası Oluşturma
```bash
# Manuel oluştur:
# resources/js/Pages/Purchasing/Index.vue

# Route ekle:
# routes/web.php
Route::get('/purchasing', [PurchasingController::class, 'index']);
```

---

## 📚 Önemli Dosyalar

| Dosya | Açıklama |
|-------|----------|
| `PROJE_PLANI.md` | 📋 Detaylı proje planı (checkbox formatında) |
| `routes/web.php` | 🛣️ Tüm route tanımları |
| `app/Models/Employee.php` | 👤 Çalışan modeli (referans) |
| `resources/js/Pages/Employees/Index.vue` | 🖥️ Liste sayfası örneği |
| `resources/js/Layouts/AppLayout.vue` | 🎨 Ana layout |

---

## 🔑 Önemli Bilgiler

### Mevcut Modüller (✅ Çalışıyor)
- ✅ Personel Yönetimi (CRUD, QR, Maaş)
- ✅ Puantaj Sistemi (Giriş/Çıkış, Onay)
- ✅ İzin Yönetimi (Talep, Onay, Bakiye)
- ✅ Proje & Departman Yönetimi
- ✅ Dashboard & Raporlar
- ✅ QR Kod Sistemi
- ✅ Bildirimler

### Eksik Modüller (❌ Yapılacak)
- ❌ Satın Alma Modülü
- ❌ Yemek Sipariş Modülü
- ⚠️ Puantaj Toplu Giriş (iyileştirme)
- ⚠️ SGK İşlemleri (manuel)
- ⚠️ Mobil PWA (planlama)

### Teknoloji Stack
- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Vue 3.5, Inertia.js 2.0
- **CSS:** TailwindCSS 4.1
- **DB:** MySQL/PostgreSQL
- **Auth:** Spatie Laravel Permission

---

## 🎨 UI Component'leri

### Hazır Components
```vue
<script setup>
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import Table from '@/Components/UI/Table.vue'
import Badge from '@/Components/UI/Badge.vue'
</script>

<template>
  <Button variant="primary" @click="save">Kaydet</Button>
  <Input v-model="form.name" label="Ad" />
  <Badge color="green">Aktif</Badge>
</template>
```

### Layout Kullanımı
```vue
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
</script>

<template>
  <AppLayout title="Sayfa Başlığı">
    <!-- İçerik buraya -->
  </AppLayout>
</template>
```

---

## 🚦 Git İş Akışı (Öneri)

```bash
# Yeni feature için branch
git checkout -b feature/purchasing-module

# Değişiklikleri commit et
git add .
git commit -m "feat: Add purchasing request model and migration"

# Push et
git push origin feature/purchasing-module

# Pull request oluştur
```

### Commit Mesaj Formatı
```
feat: Yeni özellik
fix: Hata düzeltme
docs: Dokümantasyon
refactor: Kod iyileştirme
test: Test ekleme
```

---

## 📊 İlerleme Takibi

### Nasıl Takip Edilir?
1. `PROJE_PLANI.md` dosyasını aç
2. Tamamladığın görevi `[ ]` → `[x]` yap
3. Kaydet ve commit et

### Haftalık Hedefler
- **1. Hafta:** Satın Alma + Yemek modülü temel
- **2. Hafta:** Puantaj iyileştirmeleri
- **3. Hafta:** SGK işlemleri başlangıç
- **4. Hafta:** Test ve iyileştirmeler

---

## 🆘 Yardım

### Hata Durumunda
```bash
# Cache temizle
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Composer güncel mi?
composer install

# Node modules güncel mi?
npm install

# Migration sorunları?
php artisan migrate:fresh --seed  # ⚠️ Dikkat: Tüm veriyi siler!
```

### Dokümantasyon
- 📋 [Detaylı Plan](PROJE_PLANI.md)
- 📖 [Laravel Docs](https://laravel.com/docs/12.x)
- 🎨 [Vue 3 Docs](https://vuejs.org/)
- 🔗 [Inertia.js Docs](https://inertiajs.com/)

---

## ✅ Checklist: İlk Gün

- [ ] Projeyi klonla/aç
- [ ] `composer install` çalıştır
- [ ] `npm install` çalıştır
- [ ] `.env` dosyasını ayarla
- [ ] `php artisan key:generate` çalıştır
- [ ] Veritabanı oluştur ve ayarla
- [ ] `php artisan migrate --seed` çalıştır
- [ ] `php artisan serve` + `npm run dev` başlat
- [ ] Tarayıcıda `http://localhost:8000` aç
- [ ] `PROJE_PLANI.md` dosyasını oku
- [ ] İlk görevi seç ve başla! 🚀

---

**Hazır mısın? Hadi başlayalım! 💪**
