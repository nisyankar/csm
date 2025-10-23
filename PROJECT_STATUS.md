# SPT - Şantiye Proje Takip Sistemi

**Proje Durumu ve Teknik Dokümantasyon**

**Son Güncelleme:** 23 Ekim 2025
**Versiyon:** 2.1.0
**Durum:** Geliştirme Aşaması

---

## 📊 GÜNCEL DURUM ÖZETİ

### ✅ Son Tamamlanan Geliştirmeler (23 Ekim 2025)

#### 1. **Puantaj Sistemi Konsolidasyonu** ✨ YENİ
- ✅ Tüm puantaj versiyonları birleştirildi (`timesheets_v2` + `timesheets_v3` → `timesheets`)
- ✅ Model isimleri standardize edildi (versiyon numaraları kaldırıldı)
  - `TimesheetV2` → `Timesheet`
  - `TimesheetV2Controller` → `TimesheetController`
- ✅ Gelişmiş onay sistemi tam entegre
  - Onay durumları: `draft`, `submitted`, `approved`, `rejected`
  - TimesheetApprovalLog ile tam audit trail
  - İK override yetkisi
- ✅ Fazla mesai takibi eklendi
  - Hafta içi (50%), Hafta sonu (100%), Resmi tatil (200%)
  - `overtime_hours`, `overtime_type` kolonları
- ✅ İzin entegrasyonu tamamlandı
  - `leave_request_id`, `auto_generated_from_leave`, `is_leave_day`
  - Otomatik izinden puantaj oluşturma
- ✅ Haftalık hesaplama cache sistemi
  - `week_number`, `year`, `weekly_total_hours`, `weekly_required_hours`
  - Otomatik hafta bilgisi hesaplama (boot event)
- ✅ Zaman takibi eklendi
  - `start_time`, `end_time`, `break_duration`
- ✅ Giriş metodu takibi
  - `entry_method`: manual, bulk, import, leave_sync, system
- ✅ Eski tablolar yedeklendi
  - `timesheets_old_backup` (eski sistem)
  - `timesheets_v3_backup` (V3 sistemi)

**Etki:** Kod tabanı temizlendi, versiyon karmaşası ortadan kalktı, tüm özellikler tek tabloda birleşti.

---

## 📋 MODÜL DURUMU

### ✅ Tamamlanan Modüller

#### 1. **Temel Altyapı** (100%)
- ✅ Laravel 11 kurulumu
- ✅ Inertia.js + Vue 3 entegrasyonu
- ✅ Tailwind CSS yapılandırması
- ✅ Veritabanı migrasyonları
- ✅ Kimlik doğrulama sistemi (Laravel Breeze)
- ✅ Rol ve yetki yönetimi (Spatie Permission)

#### 2. **Çalışan Yönetimi** (95%)
- ✅ Çalışan CRUD işlemleri
- ✅ Çalışan kategorileri (Yönetici, Mühendis, Usta, İşçi, Teknisyen, Sistem Admin)
- ✅ Departman yapısı
- ✅ Maaş geçmişi takibi
- ✅ Çalışan-Proje atamaları
- ✅ Taşeron çalışan desteği (`is_subcontractor`, `subcontractor_id`)
- ⚠️ Performans değerlendirme sistemi (eksik)

#### 3. **Proje Yönetimi** (90%)
- ✅ Proje oluşturma ve yönetimi
- ✅ Proje yapısı (Blok/Kat/Birim) sistemi
  - ProjectStructures (Bloklar)
  - ProjectFloors (Katlar)
  - ProjectUnits (Daireler/Birimler)
- ✅ İş kalemleri (WorkItems) yapısı
- ✅ Proje durumu takibi (Planlanan, Devam Eden, Tamamlanan)
- ✅ Bütçe yönetimi
- ✅ Proje-Taşeron atamaları
- ⚠️ Proje timeline/Gantt chart (eksik)
- ⚠️ Proje raporlama (kısmi)

#### 4. **Taşeron Yönetimi** (100%)
- ✅ Taşeron CRUD işlemleri
- ✅ Taşeron kategorileri (Elektrik, Mekanik, Demir, Boya, vb.)
- ✅ Proje-Taşeron atamaları (project_subcontractor pivot)
- ✅ Sözleşme bilgileri
- ✅ İş kapsamı tanımları
- ✅ Durum takibi (Aktif, Tamamlandı, Askıda, İptal)

#### 5. **Puantaj Sistemi** (95%) ⭐ GÜNCEL
- ✅ Günlük puantaj girişi
- ✅ Toplu puantaj girişi (BulkEntry.vue - Excel benzeri arayüz)
- ✅ **Gelişmiş onay sistemi**
  - draft (Taslak)
  - submitted (Onaya Gönderildi)
  - approved (Onaylandı)
  - rejected (Reddedildi)
- ✅ **TimesheetApprovalService**
  - approveMonthlyTimesheets (Aylık toplu onay)
  - approveSingle (Tekil onay)
  - reject (Red)
  - hrOverride (İK müdahale)
  - getPendingApprovals (Onay bekleyenler)
  - getApprovalStats (İstatistikler)
- ✅ **TimesheetApprovalLog** (Onay geçmişi)
  - Tüm onay işlemleri loglanıyor
  - Old/new values kaydediliyor
  - IP address takibi
- ✅ **Vardiya yönetimi** (Shift model)
  - Gündüz, Gece, Hafta Sonu, Resmi Tatil
  - Çalışma saatleri ve gün sayısı
- ✅ **Fazla mesai hesaplaması**
  - overtime_hours, overtime_type
  - Hafta içi %50, Hafta sonu %100, Resmi tatil %200
- ✅ **Haftalık özet ve hesaplamalar**
  - Otomatik hafta bilgisi (week_number, year)
  - Haftalık toplam saat (weekly_total_hours)
  - Haftalık gerekli saat (weekly_required_hours)
  - Haftalık fazla mesai (weekly_overtime_hours)
- ✅ **İzin entegrasyonu**
  - LeaveTimesheetSyncService
  - Onaylanan izinler otomatik puantaj oluşturuyor
  - `auto_generated_from_leave`, `is_leave_day` bayrakları
- ✅ **Proje detay takibi**
  - structure_id (Yapı/Blok)
  - floor_id (Kat)
  - unit_id (Daire/Birim)
  - work_item_id (İş kalemi)
- ⚠️ Mobil QR kod girişi (eksik)
- ⚠️ Kilitli puantaj düzenleme koruması (kısmi)

#### 6. **İzin Yönetimi** (90%)
- ✅ İzin türleri (LeaveTypes)
  - Yıllık, Hastalık, Mazeret, Evlilik, Ölüm, Doğum, Babalık, Ücretsiz vb.
- ✅ İzin parametreleri (LeaveParameters)
  - İş Kanunu uyumlu parametreler
  - Çalışma süresi, cinsiyet, yaş bazlı kurallar
- ✅ İzin bakiye hesaplama (LeaveCalculations)
- ✅ İzin talep sistemi (LeaveRequests)
- ✅ İzin onay akışı
- ✅ İzin bakiye logları (LeaveBalanceLogs)
- ✅ Frontend sayfası (LeaveRequests/Index.vue)
- ✅ LeaveTimesheetSyncService (Puantaj entegrasyonu)
- ⚠️ Otomatik yıllık bakiye hesaplama (kısmi)
- ⚠️ Yıllık izin devretme kuralları (eksik)

#### 7. **Malzeme Yönetimi** (70%)
- ✅ Malzeme tanımlama (Materials)
- ✅ Malzeme kategorileri
- ✅ Birim fiyat takibi
- ✅ Teknik özellikler (TS standartları)
- ✅ MaterialSeeder ile demo veriler
- ⚠️ Stok takibi (eksik)
- ⚠️ Malzeme çıkış/giriş işlemleri (eksik)
- ⚠️ Minimum stok uyarıları (eksik)

#### 8. **Satınalma Modülü** (75%)
- ✅ Satınalma talebi (PurchasingRequests)
- ✅ Talep kalemleri (PurchasingItems)
- ✅ Talep onay akışı
- ✅ Tedarikçi yönetimi (Suppliers)
- ✅ Teklif karşılaştırma (SupplierQuotations)
- ✅ Sipariş oluşturma (PurchaseOrders)
- ✅ Teslimat takibi (Deliveries)
- ⚠️ Fiyat karşılaştırma grafikleri (eksik)
- ⚠️ Tedarikçi performans değerlendirme (eksik)

#### 9. **Günlük Rapor Sistemi** (80%)
- ✅ Günlük rapor oluşturma
- ✅ Hava durumu kaydı
- ✅ İş ilerlemesi
- ✅ Kullanılan malzemeler
- ✅ Ekipman bilgileri
- ⚠️ Fotoğraf yükleme (eksik)
- ⚠️ PDF export (eksik)

#### 10. **İş Kalemleri** (60%)
- ✅ İş kategorileri
- ✅ İş kalemleri tanımı (WorkItems)
- ✅ Birim fiyat listesi
- ⚠️ Metraj girişi (eksik)
- ⚠️ Hakediş hesaplama (eksik)
- ⚠️ İş programı (eksik)

---

## 🗄️ VERİTABANI YAPISI

### Ana Tablolar

| Tablo | Açıklama | Durum |
|-------|----------|-------|
| `users` | Kullanıcılar | ✅ |
| `employees` | Çalışanlar | ✅ |
| `departments` | Departmanlar | ✅ |
| `projects` | Projeler | ✅ |
| `shifts` | Vardiyalar | ✅ |
| **`timesheets`** | **Puantaj kayıtları (Birleştirilmiş)** | ✅ ⭐ |
| `timesheet_approval_logs` | Puantaj onay logları | ✅ ⭐ |
| `leave_types` | İzin türleri | ✅ |
| `leave_parameters` | İzin parametreleri | ✅ |
| `leave_requests` | İzin talepleri | ✅ |
| `leave_calculations` | İzin hesaplamaları | ✅ |
| `leave_balance_logs` | İzin bakiye logları | ✅ |
| `materials` | Malzemeler | ✅ |
| `suppliers` | Tedarikçiler | ✅ |
| `purchasing_requests` | Satınalma talepleri | ✅ |
| `purchasing_items` | Satınalma kalemleri | ✅ |
| `supplier_quotations` | Tedarikçi teklifleri | ✅ |
| `purchase_orders` | Satınalma siparişleri | ✅ |
| `deliveries` | Teslimatlar | ✅ |
| `subcontractors` | Taşeronlar | ✅ |
| `project_subcontractor` | Proje-Taşeron ilişkisi | ✅ |
| `project_structures` | Proje yapıları (Bloklar) | ✅ |
| `project_floors` | Proje katları | ✅ |
| `project_units` | Proje birimleri (Daireler) | ✅ |
| `work_items` | İş kalemleri | ✅ |

### Yedek Tablolar (Referans İçin)

| Tablo | Açıklama |
|-------|----------|
| `timesheets_old_backup` | Eski puantaj sistemi (legacy) |
| `timesheets_v3_backup` | V3 puantaj sistemi (legacy) |

> **Not:** Backup tabloları gerektiğinde veri taşıma veya karşılaştırma için kullanılabilir.

---

## 🏗️ MİMARİ YAPISI

### Backend

```
app/
├── Models/
│   ├── Timesheet.php              ⭐ (Birleştirilmiş)
│   ├── TimesheetApprovalLog.php   ⭐ (Yeni)
│   ├── Employee.php
│   ├── Project.php
│   ├── Shift.php
│   ├── LeaveRequest.php
│   └── ...
├── Services/
│   └── Timesheet/
│       ├── TimesheetApprovalService.php       ⭐
│       ├── LeaveTimesheetSyncService.php      ⭐
│       └── WeeklyOvertimeCalculator.php       ⭐
├── Http/Controllers/
│   ├── TimesheetController.php    ⭐ (Yeniden adlandırıldı)
│   ├── TimesheetV3Controller.php  (Kaldırılacak)
│   └── ...
└── database/
    ├── migrations/
    │   ├── 2025_10_23_060447_add_v3_features_to_timesheets_v2_table.php  ⭐
    │   ├── 2025_10_23_061109_consolidate_timesheet_tables...php          ⭐
    │   └── ...
    └── seeders/
        ├── DatabaseSeeder.php
        ├── EmployeeProjectAssignmentSeeder.php
        ├── LeaveParametersSeeder.php
        ├── MaterialSeeder.php
        ├── ProjectStructureSeeder.php
        └── ProjectSubcontractorSeeder.php
```

### Frontend

```
resources/js/
├── Pages/
│   ├── TimesheetsV3/
│   │   └── BulkEntry.vue          (Timesheet modelini kullanıyor)
│   ├── LeaveRequests/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   └── Edit.vue
│   └── ...
└── Components/
```

---

## 🔧 TEKNOLOJİ STACK

### Backend
- **Framework:** Laravel 11
- **Database:** MariaDB 10.11
- **Authentication:** Laravel Sanctum
- **Yetkilendirme:** Spatie Laravel Permission
- **Queue:** Laravel Queue (planlı)

### Frontend
- **Framework:** Vue 3 (Composition API)
- **Bridge:** Inertia.js
- **UI:** Tailwind CSS
- **Icons:** Heroicons
- **Charts:** Chart.js / ApexCharts (planlı)

### Development
- **Version Control:** Git
- **Package Manager:** Composer (PHP), npm (JS)
- **Environment:** Laravel Sail / Docker

---

## 📝 GÜNCEL COMMIT GEÇMİŞİ

```
580bad7 - Consolidate and rename all timesheet components (BREAKING CHANGE)
7b58e93 - Migrate TimesheetV3 features to TimesheetV2
8bf7190 - Fix timesheet approval system and deprecation warnings
26fe803 - Add timesheet approval system and leave management integration
1b8f4bd - Puantaj modülü ve günlük rapor güncellemeleri
```

---

## 🎯 ÖNCELİKLİ YAPILACAKLAR (1-2 Hafta)

### Kritik
1. [ ] TimesheetV3Controller'ı kaldır, tüm route'ları TimesheetController'a taşı
2. [ ] BulkEntry.vue'yu güncelle (model referansları doğru mu kontrol et)
3. [ ] Test verileri oluştur (TimesheetSeeder)
4. [ ] Dashboard widget sistemini kur
5. [ ] İlerleme takip ekranlarını tamamla

### Hızlı Geliştirmeler
1. [ ] Puantaj toplu onay özelliğini route'lara bağla
2. [ ] İzin takvimi görünümü
3. [ ] Dashboard grafiklerini ekle
4. [ ] Stok giriş/çıkış ekranları
5. [ ] Excel export fonksiyonları
6. [ ] Blok/Kat/Birim ilerleme yüzdesi

---

## 📊 TEKNİK BORÇ

### Kod Kalitesi
- [ ] Kullanılmayan dosyaları temizle (TimesheetV3Controller, eski model dosyaları)
- [ ] PHPDoc comment'leri ekle
- [ ] Frontend type safety (TypeScript migration)

### Performans
- [ ] Database index optimizasyonu
- [ ] Eager loading kullanımı (N+1 query problemleri)
- [ ] Query caching

### Güvenlik
- [ ] CSRF token kontrolü
- [ ] Rate limiting
- [ ] API authentication strengthen

### Test Coverage
- [ ] Unit testler (Model, Service)
- [ ] Feature testler (Controller, API)
- **Mevcut Coverage:** %0 → **Hedef: %80**

---

## 📈 BAŞARI METRİKLERİ

### Kod Metrikleri (Güncel)
- **Modüller:** 10/30 (%33)
- **Test Coverage:** %0 (Hedef: %80)
- **Database Tables:** 25+ tablo
- **Seed Data:** 300+ kayıt

### Geliştirme İlerlemesi
- **Faz 1:** %75 (Temel modüller)
- **Faz 2:** %0 (Teknik ofis modülleri)
- **Faz 3:** %0 (Satış ve CRM)
- **Faz 4:** %0 (İleri seviye)

---

## 🚀 SONRAKİ ADIMLAR

### Sprint 1: Puantaj ve Dashboard (15 Kasım 2025)
1. TimesheetV3 temizliği
2. Test verileri oluşturma
3. Dashboard widget sistemi
4. Toplu onay UI'ı

### Sprint 2: Stok ve İlerleme (30 Kasım 2025)
1. Stok giriş/çıkış modülü
2. İlerleme takip ekranları
3. Excel export/import
4. Grafik entegrasyonu

### Sprint 3: Keşif ve Hakediş (31 Aralık 2025)
1. Keşif yönetimi modülü
2. Metraj sistemi
3. Hakediş hesaplama
4. Finans raporları

---

## 📞 KAYNAKLAR

- **README.md:** Genel proje açıklaması ve kurulum
- **PROJECT_STATUS.md:** Bu dosya - Teknik durum ve güncellemeler
- **Database Seeders:** Demo veri ve test senaryoları
- **Migration Files:** Veritabanı şema geçmişi

---

**Son Güncelleme:** 23 Ekim 2025, 09:00
**Güncelleyen:** Claude AI
**Versiyon:** 2.1.0
**Önemli Değişiklik:** Tüm puantaj versiyonları birleştirildi, versiyon numaraları kaldırıldı.

