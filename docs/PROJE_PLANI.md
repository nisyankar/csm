# SPT - Şantiye Proje Takip Sistemi
## 📋 Proje Planı ve Durum Takibi

**Son Güncelleme:** 24 Ekim 2025
**Versiyon:** 3.0.0
**Durum:** Aktif Geliştirme

---

## 📊 GÜNCEL DURUM ÖZETİ

### ✅ Son Tamamlanan Geliştirmeler (24 Ekim 2025)

#### 1. **Resmi Tatil Yönetim Sistemi** ✨ YENİ
- [x] Holiday model ve migration oluşturuldu
- [x] Arefe (yarım gün tatil) desteği eklendi
  - `is_half_day`, `half_day_start` kolonları
  - Yarım gün tatiller 0.5 gün olarak hesaplanıyor
- [x] HolidayController CRUD API'leri
- [x] 2025 Türkiye resmi tatilleri seeded
  - 17 tatil (2 arefe günü dahil)
- [x] İzin Parametreleri sayfasına "Resmi Tatiller" tab'ı eklendi
- [x] Yıl bazlı tatil görüntüleme (2020-2030)
- [x] Tatil ekleme/düzenleme/silme modal'ları

#### 2. **Proje Bazlı Hafta Tatili Sistemi** ✨ YENİ
- [x] Projects tablosuna `weekend_days` kolonu eklendi (JSON)
- [x] Proje bazında özelleştirilebilir hafta sonu günleri
  - Varsayılan: `["saturday", "sunday"]`
  - Bazı projeler: `["sunday"]` (sadece Pazar tatil)
- [x] Project model'e helper metodlar eklendi
  - `isWeekendDay()`, `getWeekendDaysDisplay()`
- [x] Proje Create/Edit formlarına hafta tatili seçimi eklendi
- [x] Proje Show sayfasına hafta tatili gösterimi eklendi

#### 3. **Gelişmiş İzin Hesaplama Sistemi** ⭐ GÜNCEL
- [x] Çalışan bazlı proje hafta tatili entegrasyonu
  - API endpoint: `/leave-requests/api/employee-project-settings`
- [x] İzin talep formuna otomatik hesaplama eklendi
  - Toplam gün sayısı
  - Hafta sonu günleri (projeye göre)
  - Resmi tatil günleri (hafta içi olanlar)
  - Yarım gün tatiller (arefe × 0.5)
  - **Net kullanılacak izin günü**
- [x] Görsel hesaplama kartı
  - 4 metrik kutusu (Toplam, Hafta Sonu, Resmi Tatil, Kullanılacak İzin)
  - Formül açıklaması
  - Resmi tatil detayları listesi
- [x] Akıllı tatil filtreleme
  - Hafta sonuna denk gelen tatiller çift sayılmıyor
  - Projeye göre dinamik hafta sonu hesaplama

**Hesaplama Formülü:**
```
Kullanılacak İzin = Toplam Gün
                  - Hafta Sonu (projeye göre)
                  - Tam Gün Tatiller
                  - (Yarım Gün Tatiller × 0.5)
```

**Örnek Senaryo:**
```
Proje A (Cumartesi+Pazar tatil):
  22-28 Nisan 2025: 7 gün - 2 hafta sonu - 0.5 arefe = 4.5 gün izin

Proje B (Sadece Pazar tatil):
  22-28 Nisan 2025: 7 gün - 1 hafta sonu - 0.5 arefe = 5.5 gün izin
```

#### 4. **Puantaj Sistemi Konsolidasyonu** (23 Ekim 2025)
- [x] Tüm puantaj versiyonları birleştirildi (`timesheets_v2` + `timesheets_v3` → `timesheets`)
- [x] Model isimleri standardize edildi
- [x] Gelişmiş onay sistemi tam entegre
- [x] Fazla mesai takibi eklendi
- [x] İzin entegrasyonu tamamlandı
- [x] Haftalık hesaplama cache sistemi

**Etki:**
- Kod tabanı temizlendi
- Versiyon karmaşası ortadan kalktı
- Tüm özellikler tek tabloda birleşti
- İzin ve tatil sistemleri tam entegre

---

## 📋 MODÜL DURUMU

### ✅ Tamamlanan Modüller

#### 1. **Temel Altyapı** (100%)
- [x] Laravel 11 kurulumu
- [x] Inertia.js + Vue 3 entegrasyonu
- [x] Tailwind CSS yapılandırması
- [x] Veritabanı migrasyonları
- [x] Kimlik doğrulama sistemi (Laravel Breeze)
- [x] Rol ve yetki yönetimi (Spatie Permission)

#### 2. **Çalışan Yönetimi** (95%)
- [x] Çalışan CRUD işlemleri
- [x] Çalışan kategorileri (Yönetici, Mühendis, Usta, İşçi, Teknisyen, Sistem Admin)
- [x] Departman yapısı
- [x] Maaş geçmişi takibi
- [x] Çalışan-Proje atamaları
- [x] Taşeron çalışan desteği (`is_subcontractor`, `subcontractor_id`)
- [ ] Performans değerlendirme sistemi

#### 3. **Proje Yönetimi** (95%) ⭐ GÜNCEL
- [x] Proje oluşturma ve yönetimi
- [x] **Hafta tatili günleri yönetimi** ✨ YENİ
- [x] Proje yapısı (Blok/Kat/Birim) sistemi
- [x] İş kalemleri (WorkItems) yapısı
- [x] Proje durumu takibi
- [x] Bütçe yönetimi
- [x] Proje-Taşeron atamaları
- [ ] Proje timeline/Gantt chart
- [ ] Proje raporlama (kısmi)

#### 4. **Taşeron Yönetimi** (100%)
- [x] Taşeron CRUD işlemleri
- [x] Taşeron kategorileri
- [x] Proje-Taşeron atamaları
- [x] Sözleşme bilgileri
- [x] İş kapsamı tanımları
- [x] Durum takibi

#### 5. **Puantaj Sistemi** (95%) ⭐
- [x] Günlük puantaj girişi
- [x] Toplu puantaj girişi (BulkEntry.vue)
- [x] Gelişmiş onay sistemi (draft, submitted, approved, rejected)
- [x] TimesheetApprovalService
- [x] TimesheetApprovalLog (Onay geçmişi)
- [x] Vardiya yönetimi (Shift model)
- [x] Fazla mesai hesaplaması
- [x] Haftalık özet ve hesaplamalar
- [x] İzin entegrasyonu (LeaveTimesheetSyncService)
- [x] Proje detay takibi (structure_id, floor_id, unit_id, work_item_id)
- [ ] Mobil QR kod girişi
- [ ] Kilitli puantaj düzenleme koruması (kısmi)

#### 6. **İzin Yönetimi** (95%) ⭐ GÜNCEL
- [x] İzin türleri (LeaveTypes)
- [x] İzin parametreleri (LeaveParameters)
- [x] **Resmi tatiller yönetimi** ✨ YENİ
  - [x] Holiday model
  - [x] Arefe (yarım gün) desteği
  - [x] Yıl bazlı görüntüleme
  - [x] CRUD modal'ları
- [x] **Gelişmiş izin hesaplama** ✨ YENİ
  - [x] Proje bazlı hafta sonu hesaplama
  - [x] Resmi tatil entegrasyonu
  - [x] Arefe günleri (0.5 gün)
  - [x] Görsel hesaplama kartı
- [x] İzin bakiye hesaplama (LeaveCalculations)
- [x] İzin talep sistemi (LeaveRequests)
- [x] İzin onay akışı
- [x] İzin bakiye logları (LeaveBalanceLogs)
- [x] Frontend sayfası (LeaveRequests/Index.vue)
- [x] LeaveTimesheetSyncService (Puantaj entegrasyonu)
- [ ] Otomatik yıllık bakiye hesaplama (kısmi)
- [ ] Yıllık izin devretme kuralları

#### 7. **Malzeme Yönetimi** (70%)
- [x] Malzeme tanımlama (Materials)
- [x] Malzeme kategorileri
- [x] Birim fiyat takibi
- [x] Teknik özellikler (TS standartları)
- [x] MaterialSeeder ile demo veriler
- [ ] Stok takibi
- [ ] Malzeme çıkış/giriş işlemleri
- [ ] Minimum stok uyarıları

#### 8. **Satınalma Modülü** (75%)
- [x] Satınalma talebi (PurchasingRequests)
- [x] Talep kalemleri (PurchasingItems)
- [x] Talep onay akışı
- [x] Tedarikçi yönetimi (Suppliers)
- [x] Teklif karşılaştırma (SupplierQuotations)
- [x] Sipariş oluşturma (PurchaseOrders)
- [x] Teslimat takibi (Deliveries)
- [ ] Fiyat karşılaştırma grafikleri
- [ ] Tedarikçi performans değerlendirme

#### 9. **Günlük Rapor Sistemi** (80%)
- [x] Günlük rapor oluşturma
- [x] Hava durumu kaydı
- [x] İş ilerlemesi
- [x] Kullanılan malzemeler
- [x] Ekipman bilgileri
- [ ] Fotoğraf yükleme
- [ ] PDF export

#### 10. **İş Kalemleri** (60%)
- [x] İş kategorileri
- [x] İş kalemleri tanımı (WorkItems)
- [x] Birim fiyat listesi
- [ ] Metraj girişi
- [ ] Hakediş hesaplama
- [ ] İş programı

---

## 🗄️ VERİTABANI YAPISI

### Ana Tablolar

| Tablo | Açıklama | Durum |
|-------|----------|-------|
| `users` | Kullanıcılar | ✅ |
| `employees` | Çalışanlar | ✅ |
| `departments` | Departmanlar | ✅ |
| `projects` | Projeler (+ weekend_days) | ✅ ⭐ |
| `shifts` | Vardiyalar | ✅ |
| `timesheets` | Puantaj kayıtları (Birleştirilmiş) | ✅ ⭐ |
| `timesheet_approval_logs` | Puantaj onay logları | ✅ |
| `leave_types` | İzin türleri | ✅ |
| `leave_parameters` | İzin parametreleri | ✅ |
| `leave_requests` | İzin talepleri | ✅ |
| `leave_calculations` | İzin hesaplamaları | ✅ |
| `leave_balance_logs` | İzin bakiye logları | ✅ |
| **`holidays`** | **Resmi tatiller** | ✅ ⭐ YENİ |
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

### Yedek Tablolar

| Tablo | Açıklama |
|-------|----------|
| `timesheets_old_backup` | Eski puantaj sistemi (legacy) |
| `timesheets_v3_backup` | V3 puantaj sistemi (legacy) |

---

## 🎯 ÖNCELİKLİ YAPILACAKLAR

### Kritik (1 Hafta)
1. [x] ~~TimesheetV3Controller'ı kaldır, tüm route'ları TimesheetController'a taşı~~ (23 Ekim 2025)
2. [x] ~~BulkEntry.vue'yu güncelle (model referansları doğru mu kontrol et)~~ (23 Ekim 2025)
3. [x] ~~Test verileri oluştur (TimesheetSeeder)~~ (15 Ekim 2025 - TimesheetDemoSeeder)
4. [ ] Dashboard widget sistemini kur
5. [ ] İlerleme takip ekranlarını tamamla

### Hızlı Geliştirmeler (2 Hafta)
1. [ ] Puantaj toplu onay özelliğini route'lara bağla
2. [ ] İzin takvimi görünümü (resmi tatilleri göster)
3. [ ] Dashboard grafiklerini ekle
4. [ ] Stok giriş/çıkış ekranları
5. [ ] Excel export fonksiyonları
6. [ ] Blok/Kat/Birim ilerleme yüzdesi

### Orta Vadeli (1 Ay)
1. [ ] Mobil QR kod puantaj girişi
2. [ ] Günlük rapor fotoğraf yükleme
3. [ ] PDF export (puantaj, izin, raporlar)
4. [ ] Otomatik yıllık izin bakiye hesaplama
5. [ ] Proje Gantt chart görünümü

---

## 📊 TEKNİK BORÇ

### Kod Kalitesi
- [x] ~~Kullanılmayan dosyaları temizle (TimesheetV3Controller)~~ (23-24 Ekim 2025)
- [x] ~~Proje planlama MD dosyalarını birleştir~~ (24 Ekim 2025)
- [ ] PHPDoc comment'leri ekle
- [ ] Frontend type safety (TypeScript migration)
- [ ] Kod standardizasyonu (PSR-12)

### Performans
- [ ] Database index optimizasyonu
- [ ] Eager loading kullanımı (N+1 query problemleri)
- [ ] Query caching
- [ ] API response caching

### Güvenlik
- [ ] CSRF token kontrolü
- [ ] Rate limiting
- [ ] API authentication strengthen
- [ ] XSS prevention checks

### Test Coverage
- [ ] Unit testler (Model, Service)
- [ ] Feature testler (Controller, API)
- [ ] Integration testler
- **Mevcut Coverage:** %0 → **Hedef: %80**

---

## 🚀 SONRA Kİ SPRINT'LER

### Sprint 1: Puantaj ve Dashboard (15 Kasım 2025)
1. [ ] TimesheetV3 temizliği
2. [ ] Test verileri oluşturma
3. [ ] Dashboard widget sistemi
4. [ ] Toplu onay UI'ı

### Sprint 2: Stok ve İlerleme (30 Kasım 2025)
1. [ ] Stok giriş/çıkış modülü
2. [ ] İlerleme takip ekranları
3. [ ] Excel export/import
4. [ ] Grafik entegrasyonu

### Sprint 3: Keşif ve Hakediş (31 Aralık 2025)
1. [ ] Keşif yönetimi modülü
2. [ ] Metraj sistemi
3. [ ] Hakediş hesaplama
4. [ ] Finans raporları

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

## 📈 BAŞARI METRİKLERİ

### Kod Metrikleri (Güncel)
- **Modüller:** 11/30 (%37) ⬆️
- **Test Coverage:** %0 (Hedef: %80)
- **Database Tables:** 26+ tablo
- **Seed Data:** 300+ kayıt

### Geliştirme İlerlemesi
- **Faz 1:** %85 ⬆️ (Temel modüller + İzin/Tatil sistemi)
- **Faz 2:** %15 (Günlük operasyonlar - kısmi)
- **Faz 3:** %0 (Satış ve CRM)
- **Faz 4:** %0 (İleri seviye)

---

## 📝 GÜNCEL COMMIT GEÇMİŞİ

```
[son] - Add project weekend days and advanced leave calculation with holidays
[son-1] - Consolidate and rename all timesheet components (BREAKING CHANGE)
580bad7 - Migrate TimesheetV3 features to TimesheetV2
7b58e93 - Fix timesheet approval system and deprecation warnings
26fe803 - Add timesheet approval system and leave management integration
```

---

## 📞 KAYNAKLAR

- **README.md:** Genel proje açıklaması ve kurulum
- **docs/PROJE_PLANI.md:** Bu dosya - Teknik durum ve güncellemeler
- **Database Seeders:** Demo veri ve test senaryoları
- **Migration Files:** Veritabanı şema geçmişi

---

## 📋 NOTLAR

### Önemli Kararlar
1. **Puantaj Konsolidasyonu:** Tüm versiyonlar tek tabloda birleştirildi (23 Ekim)
2. **Resmi Tatil Sistemi:** Arefe desteği ile tamamlandı (24 Ekim)
3. **Proje Bazlı Hafta Tatili:** Esnek hafta sonu tanımları eklendi (24 Ekim)
4. **İzin Hesaplama:** Tatil ve proje kuralları tam entegre (24 Ekim)

### Bilinen Sorunlar
- TimesheetV3Controller hala mevcut (kaldırılacak)
- Bazı route'lar eski controller'ı kullanıyor
- Test coverage yok

### Sonraki Sohbet İçin
Bu dokümandan yeni sohbet başlatırken özet geçebilirsiniz. Tüm tamamlanan görevler [x] ile işaretli, yapılacaklar [ ] ile işaretli.

---

**Son Güncelleme:** 24 Ekim 2025, 14:30
**Güncelleyen:** Development Team
**Versiyon:** 3.0.0
**Önemli Değişiklik:** Resmi tatil ve proje bazlı hafta tatili sistemleri eklendi.
