# SPT - Şantiye Proje Takip Sistemi
## 📋 Proje Planı ve Durum Takibi

**Son Güncelleme:** 25 Ekim 2025
**Versiyon:** 3.2.0
**Durum:** Aktif Geliştirme

---

## 📊 GÜNCEL DURUM ÖZETİ

### ✅ Son Tamamlanan Geliştirmeler (25 Ekim 2025)

#### 1. **Hakediş (Progress Payment) Takip Sistemi** ✨ YENİ ⭐
- [x] ProgressPayment model ve migration oluşturuldu
- [x] Hakediş CRUD Controller (ProgressPaymentController)
- [x] Dashboard sayfası (istatistikler, grafikler, son hakediş kayıtları)
  - Durum dağılımı, proje bazlı ilerleme
  - Taşeron performans tablosu
  - Onay bekleyenler listesi
  - **NaN hataları düzeltildi (computed column sum() sorunu)**
- [x] Index sayfası (liste görünümü + filtreler)
  - Proje, Taşeron, Durum, Yıl, Ay filtreleri
  - Arama özelliği
  - İlerleme çubukları
  - **Görüntüle ve Düzenle butonları eklendi**
- [x] Create/Edit sayfaları (modern full-width tasarım)
  - Proje bazlı taşeron filtreleme
  - Blok → Kat → Birim cascading dropdowns
  - İlerleme ve tutar otomatik hesaplama
  - Unit/Daire listing düzeltildi
- [x] Show sayfası (detay görünümü)
  - 3-column responsive layout
  - Timeline ve quick stats
  - Onay ve ödeme işlemleri
- [x] **Proje Show sayfasına Hakediş Kayıtları tab'ı eklendi**
  - Tab sistemi ile hakediş listesi
  - İstatistik kartları (toplam, tamamlanan, tutar, ilerleme)
  - NaN hataları parseFloat() ile düzeltildi
- [x] **Taşeron Show sayfasına Hakediş Kayıtları tab'ı eklendi**
  - Tab sistemi ile hakediş listesi
  - İstatistik kartları eklendi
  - **Card görünüm sorunları kalıcı çözüldü** (white/10 backdrop-blur pattern)
- [x] Onay ve ödeme workflow (planned → in_progress → completed → approved → paid)
- [x] Otomatik cascade güncelleme (Payment → Floor → Structure → Project)
- [x] Backend ilişkiler: projects, subcontractors, work_items, structures, floors, units
- [x] 108 test verisi (ProgressPaymentSeeder)
- [x] 25 iş kalemi (WorkItemSeeder)
- [x] Tüm sayfalar modern full-width tasarımda

**Özellikler:**
- Metraj ve hakediş tutarı takibi
- İlerleme yüzdesi hesaplama
- Proje yapısı entegrasyonu (Blok/Kat/Birim - opsiyonel)
- Taşeron bazlı performans raporlama
- Dönem (yıl/ay) filtreleme
- Durum bazlı raporlama

#### 2. **Resmi Tatil Yönetim Sistemi** ✨ YENİ
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

#### 11. **Hakediş Takip Sistemi** (100%) ✨
- [x] ProgressPayment model ve CRUD
- [x] Dashboard (istatistikler, grafikler)
- [x] Proje ve Taşeron entegrasyonu
- [x] Onay ve ödeme workflow
- [x] Otomatik cascade güncelleme

#### 12. **Çalışan Yönetimi - Create/Edit Sayfaları** (100%) ✅
- [x] Employee CreateSimple.vue (tek sayfa form)
- [x] Searchable select düzeltmeleri (Manager, Project)
- [x] Z-index ve overflow sorunları çözüldü
- [x] Ücret tipi alanları düzeltildi
- [x] Form validasyonu ve submission

#### 13. **Rol & Yetki Yönetim Sistemi** (0%) 🎯 PLANLANAN
- [ ] Gelişmiş rol tanımlama sistemi
- [ ] Proje bazlı yetkilendirme
- [ ] Çoklu proje yöneticisi/şantiye şefi desteği
- [ ] Modül bazlı yetki matrisi
- [ ] Rol hiyerarşisi ve devralma
- [ ] Kullanıcı-Rol-Proje atama arayüzü
- [ ] Kapsamlı activity log sistemi
- [ ] Log görüntüleme ve filtreleme arayüzü

#### 14. **İnşaat Ruhsat ve İzin Yönetimi** (0%) 🏗️ YENİ PLANLANAN
- [ ] Yapı ruhsatı takip sistemi
- [ ] İmar durumu kayıtları
- [ ] Ruhsat başvuru süreci takibi
- [ ] Ruhsat belgeleri dosya yönetimi
- [ ] Ruhsat geçerlilik tarih uyarıları
- [ ] İskan izni takibi
- [ ] Yapı kullanma izni süreçleri
- [ ] Proje bazlı ruhsat durumu raporları

#### 15. **Yapı Denetim Sistemi** (0%) 🔍 YENİ PLANLANAN
- [ ] Yapı denetim kuruluşu bilgileri
- [ ] Denetim raporları ve kayıtları
- [ ] Periyodik denetim planlaması
- [ ] Denetim bulguları ve uygunsuzluklar
- [ ] Düzeltici faaliyet takibi
- [ ] Denetim belgesi yönetimi
- [ ] Denetmen bilgileri ve görevlendirme
- [ ] Denetim tutanakları arşivleme

#### 16. **Satış ve Tapu Yönetimi** (0%) 🏘️ YENİ PLANLANAN
- [ ] Müşteri bilgileri (CRM entegrasyonu)
- [ ] Satış sözleşmeleri
- [ ] Tapu bilgileri ve devir işlemleri
- [ ] Ödeme planları ve taksitler
- [ ] Blok/Kat/Daire satış durumu
- [ ] Rezervasyon sistemi
- [ ] Satış performans raporları
- [ ] Bağımsız bölüm listesi
- [ ] Kat irtifakı/Kat mülkiyeti kayıtları

#### 17. **Finansal Yönetim ve Kar/Zarar Sistemi** (0%) 💰 YENİ PLANLANAN
- [ ] Gelir kaynakları modülü
  - [ ] Satış gelirleri (daireler, işyerleri)
  - [ ] Hakediş tahsilatları
  - [ ] Fatura kesimi ve takibi
  - [ ] Gelir kategorilendirme
- [ ] Gider yönetimi modülü
  - [ ] Personel giderleri (maaş, prim)
  - [ ] Malzeme giderleri
  - [ ] Taşeron ödemeleri
  - [ ] Genel giderler (elektrik, su, vs.)
  - [ ] Gider kategorilendirme
- [ ] Proje bazlı maliyet merkezi
  - [ ] Proje özel gider/gelir takibi
  - [ ] Bütçe vs gerçekleşen karşılaştırma
- [ ] Finansal raporlama
  - [ ] Proje bazlı kar/zarar raporu
  - [ ] Aylık/Yıllık gelir-gider tabloları
  - [ ] Nakit akış raporu
  - [ ] Karlılık analizi
  - [ ] Dashboard widget'ları (gelir, gider, kar trendi)
- [ ] Entegrasyon altyapısı
  - [ ] Puantaj sisteminden personel giderleri
  - [ ] Satınalma sisteminden malzeme giderleri
  - [ ] Hakediş sisteminden taşeron ödemeleri
  - [ ] Satış sisteminden gelir kayıtları
  - [ ] Otomatik finansal kayıt oluşturma

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
4. [x] **Dashboard widget sistemini kur** ✨ (24 Ekim 2025)
   - [x] StatCard.vue - İstatistik kartları widget'ı
   - [x] ActivityWidget.vue - Aktivite listeleri widget'ı
   - [x] AlertWidget.vue - Uyarı kartları widget'ı
   - [x] QuickActionWidget.vue - Hızlı işlem kartları widget'ı
   - [x] Admin Dashboard'u yeni widget sistemiyle güncelle
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
- [x] ~~Employee Create page hatası: Button.vue "Cannot read properties of undefined (reading 'default')" `/employees/create`~~ (25 Ekim 2025)
- [x] ~~Employee Create: setData is not a function error~~ (25 Ekim 2025)
- [x] ~~Employee Create: leave-parameters.index route missing~~ (25 Ekim 2025)
- [x] ~~Manager and Project searchable select issues~~ (25 Ekim 2025)
- [ ] TimesheetV3Controller hala mevcut (kaldırılacak)
- [ ] Bazı route'lar eski controller'ı kullanıyor
- [ ] Test coverage yok

### Teknik Düzeltmeler (25 Ekim 2025)
#### NaN Hatası Çözümü - Computed Columns
**Problem:** Laravel migration'da `total_amount` computed column olarak tanımlanmış (`->storedAs('completed_quantity * unit_price')`). Eloquent'te `sum('total_amount')` kullanıldığında NaN hatası veriyordu.

**Çözüm:** Computed column'lar üzerinde doğrudan aggregate fonksiyonlar çalışmadığı için raw SQL kullanıldı:
```php
// ❌ Hatalı
$total = ProgressPayment::sum('total_amount');

// ✅ Doğru
$total = ProgressPayment::selectRaw('SUM(completed_quantity * unit_price) as total')->value('total') ?? 0;
```

**Etkilenen Dosyalar:**
- `app/Http/Controllers/ProgressPaymentController.php` (dashboard method)
- `resources/js/Pages/Projects/Show.vue` (progressPaymentStats computed)
- `resources/js/Pages/Subcontractors/Show.vue` (progressPaymentStats computed)

#### Card Görünüm Düzeltmesi
**Problem:** Subcontractor Show sayfasında header stats kartları mor/beyaz yarı saydam arka plan kullanıyordu ve mor gradient üzerinde metin okunmuyordu.

**Çözüm:** Project Show sayfasındaki glass-morphism pattern kopyalandı:
```css
/* ❌ Eski - okunmuyor */
bg-purple-800 bg-opacity-40 border-purple-400 border-opacity-30

/* ✅ Yeni - net okunuyor */
bg-white/10 backdrop-blur-sm border-white/30
```

### Sonraki Sohbet İçin
Bu dokümandan yeni sohbet başlatırken özet geçebilirsiniz. Tüm tamamlanan görevler [x] ile işaretli, yapılacaklar [ ] ile işaretli.

---

---

## 🔐 ROL & YETKİ YÖNETİM SİSTEMİ - DETAYLI PLAN

### 📋 Sistem Mimarisi

#### 1. Rol Hiyerarşisi

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Super Admin (God Mode)                                   │
│    └─ Tüm sistem ayarlarına erişim                         │
│    └─ Rol tanımlama ve yetki atama                         │
│    └─ Kullanıcı yönetimi (CRUD)                            │
│    └─ Tüm projelere erişim                                 │
│    └─ Sistem loglarını görüntüleme                         │
│    └─ Parametrik ayarlar                                    │
├─────────────────────────────────────────────────────────────┤
│ 2. Genel Yönetici (Company Admin)                          │
│    └─ Tüm projeleri görüntüleyebilir                       │
│    └─ Onay yetkisi (Puantaj, Satınalma, Hakediş)          │
│    └─ Tüm raporlara erişim                                 │
│    └─ Kullanıcı ekleme (sınırlı - kendi bölümü)           │
│    └─ Dashboard: Tüm projeler                              │
├─────────────────────────────────────────────────────────────┤
│ 3. Proje Yöneticisi (Project Manager) ⭐ ÇOK SEÇİLEBİLİR  │
│    └─ Atandığı projelere TAM erişim                        │
│    └─ Puantaj yönetimi (kendi projeleri)                   │
│    └─ Hakediş yönetimi (kendi projeleri)                   │
│    └─ Satınalma yönetimi (kendi projeleri)                 │
│    └─ Onay yetkisi (1. kademe - kendi projeleri)          │
│    └─ Rapor görüntüleme (kendi projeleri)                  │
│    └─ Dashboard: Sadece atandığı projeler                   │
├─────────────────────────────────────────────────────────────┤
│ 4. Şantiye Şefi (Site Manager) ⭐ ÇOK SEÇİLEBİLİR         │
│    └─ Atandığı projelere erişim                            │
│    └─ Puantaj girişi (kendi projeleri)                     │
│    └─ Malzeme talep (kendi projeleri)                      │
│    └─ Günlük rapor girişi                                  │
│    └─ Sadece görüntüleme (hakediş, bütçe)                  │
│    └─ Dashboard: Sadece atandığı projeler                   │
├─────────────────────────────────────────────────────────────┤
│ 5. Muhasebe/Finans Kullanıcısı                             │
│    └─ Tüm projelerin finansal verileri                     │
│    └─ Hakediş onaylama (final)                             │
│    └─ Ödeme işlemleri                                       │
│    └─ Maliyet raporları                                     │
│    └─ Bütçe analizi                                         │
├─────────────────────────────────────────────────────────────┤
│ 6. İnsan Kaynakları                                         │
│    └─ Çalışan yönetimi (CRUD)                              │
│    └─ İzin onaylama                                         │
│    └─ Puantaj onaylama                                      │
│    └─ Bordro hazırlama                                      │
│    └─ Sicil kartları                                        │
├─────────────────────────────────────────────────────────────┤
│ 7. Satınalma Uzmanı                                         │
│    └─ Satınalma talepleri yönetimi                         │
│    └─ Tedarikçi yönetimi                                    │
│    └─ Fiyat karşılaştırma                                   │
│    └─ Sipariş takibi                                        │
│    └─ Stok yönetimi                                         │
├─────────────────────────────────────────────────────────────┤
│ 8. Çalışan (Employee)                                       │
│    └─ Kendi bilgilerini görüntüleme                        │
│    └─ İzin talebi oluşturma                                │
│    └─ Puantaj görüntüleme (sadece kendisi)                 │
│    └─ Sicil kartı görüntüleme                              │
└─────────────────────────────────────────────────────────────┘
```

#### 2. Modül Bazlı Yetki Matrisi

Her modül için standart yetkiler:
- `view` - Görüntüleme
- `create` - Oluşturma
- `edit` - Düzenleme
- `delete` - Silme
- `approve` - Onaylama
- `export` - Dışa Aktarma

**Örnek Yetki Kodları:**
```
projects.view
projects.create
projects.edit
projects.delete
projects.export

employees.view
employees.create
employees.edit
employees.delete
employees.approve
employees.export

timesheets.view
timesheets.create
timesheets.edit
timesheets.delete
timesheets.approve
timesheets.export

... (tüm modüller için)
```

#### 3. Proje Bazlı Yetkilendirme

**Yeni Tablo: `user_project_roles`**
```sql
CREATE TABLE user_project_roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by BIGINT UNSIGNED,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_project_role (user_id, project_id, role_id)
);
```

**Özellikler:**
- Bir kullanıcı birden fazla projeye atanabilir
- Bir projede birden fazla proje yöneticisi olabilir
- Bir projede birden fazla şantiye şefi olabilir
- Yetki sona erme tarihi (opsiyonel)
- Atayan kişi tracking

#### 4. Activity Log Sistemi

**Yeni Tablo: `activity_logs`**
```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    record_type VARCHAR(100),
    record_id BIGINT UNSIGNED,
    project_id BIGINT UNSIGNED NULL,
    description TEXT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_module (module),
    INDEX idx_action (action),
    INDEX idx_project (project_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
);
```

**Loglanacak İşlemler:**
- `view` - Görüntüleme
- `create` - Oluşturma
- `update` - Güncelleme
- `delete` - Silme
- `approve` - Onaylama
- `reject` - Reddetme
- `export` - Dışa Aktarma
- `import` - İçe Aktarma
- `login` - Giriş
- `logout` - Çıkış

**Modüller:**
- `projects` - Projeler
- `employees` - Çalışanlar
- `timesheets` - Puantaj
- `leave_requests` - İzinler
- `progress_payments` - Hakediş
- `purchasing` - Satınalma
- `materials` - Malzemeler
- `users` - Kullanıcılar
- `roles` - Roller
- `system` - Sistem

### 📝 Geliştirme Görevleri

#### Faz 1: Database & Models (1 Hafta)
- [ ] `user_project_roles` migration oluştur
- [ ] `activity_logs` migration oluştur
- [ ] UserProjectRole model oluştur
- [ ] ActivityLog model oluştur
- [ ] Role model'e helper metodlar ekle
- [ ] User model'e proje ilişkileri ekle
- [ ] Seeder'lar oluştur (test verileri)

#### Faz 2: Permission System (1 Hafta)
- [ ] Modül bazlı yetki tanımları oluştur
- [ ] PermissionSeeder (tüm modüller için CRUD yetkiler)
- [ ] RolePermissionSeeder (rol-yetki eşleştirmeleri)
- [ ] Middleware: CheckProjectAccess
- [ ] Middleware: CheckModulePermission
- [ ] Policy'ler güncelle (proje bazlı kontroller)

#### Faz 3: Admin Panel (2 Hafta)
- [ ] Rol Yönetimi sayfası
  - [ ] Rol listesi
  - [ ] Rol oluştur/düzenle
  - [ ] Yetki ataması (checkbox matrisi)
  - [ ] Rol önizleme
- [ ] Kullanıcı Yönetimi sayfası
  - [ ] Kullanıcı listesi
  - [ ] Kullanıcı oluştur/düzenle
  - [ ] Rol ataması
  - [ ] Proje-Rol ataması (multi-select)
  - [ ] Kullanıcı aktivite geçmişi
- [ ] Proje-Kullanıcı Atama sayfası
  - [ ] Proje seç
  - [ ] Kullanıcı-Rol matrisi
  - [ ] Toplu atama özelliği
  - [ ] Sona erme tarihi belirleme

#### Faz 4: Activity Log System (1 Hafta)
- [ ] ActivityLogService oluştur
- [ ] Trait: LogsActivity (tüm model'lere eklenecek)
- [ ] Observer'lar (otomatik loglama)
- [ ] Activity Log sayfası
  - [ ] Filtreleme (tarih, modül, aksiyon, kullanıcı, proje)
  - [ ] Arama
  - [ ] Export (Excel, CSV)
  - [ ] Detay modal (eski/yeni değerler karşılaştırma)
  - [ ] Grafikler (zaman bazlı aktivite)

#### Faz 5: Frontend Integration (1 Hafta)
- [ ] Tüm Controller'lara proje bazlı filtre ekle
- [ ] Dashboard'ları güncelle (rol bazlı)
- [ ] Menü sistemini güncelle (yetki bazlı)
- [ ] Buton/aksiyon gizleme (yetki bazlı)
- [ ] Composable: usePermissions
- [ ] Composable: useProjectAccess

#### Faz 6: Testing & Documentation (1 Hafta)
- [ ] Unit testler (Permission, Role, User)
- [ ] Feature testler (Middleware, Policy)
- [ ] Integration testler (End-to-end yetki kontrolleri)
- [ ] Dokümantasyon (rol tanımları, yetki matrisi)

### 🎯 Örnek Kullanım Senaryoları

#### Senaryo 1: Çoklu Proje Yöneticisi
```
Kullanıcı: Ahmet Yılmaz
Rol: Proje Yöneticisi

Atamalar:
- Proje A (Konut İnşaatı) → Proje Yöneticisi
- Proje B (Villa Projesi) → Proje Yöneticisi
- Proje C (AVM İnşaatı) → Şantiye Şefi

Dashboard'da:
- Proje A ve B için tam yetki (puantaj, hakediş, satınalma, onay)
- Proje C için sınırlı yetki (sadece puantaj girişi, görüntüleme)
- Diğer projeler görünmez
```

#### Senaryo 2: Onay Workflow
```
İşlem: Hakediş Onaylama

1. Şantiye Şefi (Mehmet) → Hakediş oluşturur
2. Proje Yöneticisi (Ahmet) → 1. kademe onaylar
3. Genel Yönetici (Ali) → 2. kademe onaylar
4. Muhasebe (Ayşe) → Final onay + Ödeme

Activity Log:
- 10:00 - Mehmet - Hakediş oluşturuldu (ID: 123)
- 11:00 - Ahmet - Hakediş onaylandı (1. kademe)
- 14:00 - Ali - Hakediş onaylandı (2. kademe)
- 15:30 - Ayşe - Hakediş onaylandı (Final) + Ödeme yapıldı
```

#### Senaryo 3: Proje Bazlı Dashboard
```
Super Admin Dashboard:
- Tüm projeler (10 proje)
- Tüm istatistikler
- Sistem geneli metrikler

Proje Yöneticisi Dashboard (Ahmet):
- Sadece Proje A ve B
- Bu projelere ait istatistikler
- Onay bekleyen işler (sadece bu projeler)

Şantiye Şefi Dashboard (Mehmet):
- Sadece Proje C
- Puantaj özeti
- Malzeme talepleri
- Hakediş görüntüleme (sadece okuma)
```

### 💡 Ek Öneriler

#### 1. İki Aşamalı Onay Sistemi
```
Kritik İşlemler (Hakediş, Satınalma > 50.000 TL):
1. Proje Yöneticisi onayı
2. Genel Yönetici / Muhasebe onayı

Normal İşlemler (Puantaj, Malzeme Talebi):
1. Proje Yöneticisi / Şantiye Şefi onayı
```

#### 2. Vekalet Sistemi (Delegation)
```
Kullanıcı izinde iken yetkilerini başkasına devredebilir:
- Başlangıç/Bitiş tarihi
- Hangi yetkilerin devredileceği (seçmeli)
- Activity log'da izlenebilir
```

#### 3. IP Kısıtlama
```
Belirli roller için IP whitelist:
- Super Admin → Sadece ofis IP'si
- Muhasebe → Sadece ofis IP'si
- Diğer roller → Kısıtlama yok
```

#### 4. Zaman Bazlı Yetkiler
```
Vardiya bazlı yetkilendirme:
- Gece vardiyası sadece puantaj girişi
- Gündüz vardiyası tüm yetkiler
```

---

**Son Güncelleme:** 25 Ekim 2025, 23:00
**Güncelleyen:** Development Team
**Versiyon:** 3.3.0
**Önemli Değişiklik:**
- Employee Create page hataları düzeltildi
- Kapsamlı Rol & Yetki Yönetim Sistemi planlandı
- Activity Log sistemi tasarımı tamamlandı
- Çoklu proje yöneticisi/şantiye şefi desteği planlandı
