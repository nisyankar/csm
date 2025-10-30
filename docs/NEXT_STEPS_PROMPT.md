# Sonraki Adımlar - FAZ 3 Devam

## 🎯 Mevcut Durum
- ✅ **İSG Modülü Tamamlandı** (Faz 3 - %16.67)
- ✅ 5 alt modül: İş Kazaları, Eğitimler, Denetimler, Risk Değerlendirmeleri, KKD Atamaları
- ✅ 7 Vue sayfası: Tümü modern full-width tasarım
- ✅ Kırmızı-turuncu gradient tema, hakediş modülü tasarım standardına uygun

## 📋 FAZ 3 Kalan Modüller

### 1. Ekipman & Makine Yönetimi 🚜 (ÖNCELİK: YÜKSEK)
**Tahmini Süre:** 1-2 gün
**Özellikler:**
- Kira ve mülkiyetli makine takibi
- Günlük kullanım/operatör kaydı
- Yakıt tüketimi
- Bakım geçmişi ve arıza kayıtları
- Maliyetlerin financial_transactions'a otomatik aktarımı

**Database Schema:**
```sql
-- equipments table
id, project_id, name, type (owned/rented), brand, model, serial_number,
purchase_date, rental_start_date, rental_end_date, rental_cost_per_day,
plate_number, insurance_expiry, status (active/maintenance/out_of_service),
current_location, notes, created_at, updated_at, deleted_at

-- equipment_usages table
id, equipment_id, project_id, operator_id (employee_id), usage_date,
start_time, end_time, hours_used, fuel_consumed, fuel_cost,
work_description, location, notes, created_at, updated_at

-- equipment_maintenance table
id, equipment_id, maintenance_type (routine/repair/breakdown),
maintenance_date, description, cost, vendor_name, next_maintenance_date,
performed_by_id (user_id), notes, created_at, updated_at, deleted_at
```

**Prompt Önerisi:**
```
Ekipman & Makine Yönetimi modülünü implement et.

Gereksinimler:
1. 3 Database Migration: equipments, equipment_usages, equipment_maintenance
2. 3 Model: Equipment, EquipmentUsage, EquipmentMaintenance (ilişkiler, scopes)
3. 3 Controller: CRUD + filtreleme + finansal entegrasyon
4. Sidebar: "Ekipman Yönetimi" menü grubu (truck/wrench ikonları)
5. Vue Sayfaları (9 adet):
   - Equipments: Index, Create, Edit, Show
   - EquipmentUsages: Index, Create, Edit
   - EquipmentMaintenance: Index, Create, Edit
6. Modern UI: Sarı-turuncu gradient tema (ekipman teması)
7. Finansal Entegrasyon: Kira maliyetleri ve bakım giderleri otomatik FinancialTransaction
8. Seeder: 5-6 ekipman (vinç, dozer, ekskavatör), 10-15 kullanım kaydı, 5 bakım kaydı
9. Routes: /equipments/*, /equipment-usages/*, /equipment-maintenance/*

Tasarım Standardı: Hakediş ve İSG modüllerindeki modern full-width tasarımı koru.
```

---

### 2. Çoklu Depo/Lokasyon Sistemi 📦 (ÖNCELİK: ORTA)
**Not:** Zaten `warehouses` ve `stock_movements` tabloları var. Bu görev genişletme odaklı.

**Tahmini Süre:** 4-6 saat

**Eklenecek Özellikler:**
- Depolar arası transfer UI/UX iyileştirmesi
- Toplu stok sayım sistemi
- Stok fark raporu (sayım sonucu - sistem stok)
- Depo sorumlusu yetkileri (middleware)

**Prompt Önerisi:**
```
Mevcut Stok Yönetimi modülünü genişlet:

1. Toplu Stok Sayım Sistemi:
   - stock_counts tablosu (id, warehouse_id, count_date, status, counted_by)
   - stock_count_items tablosu (id, stock_count_id, material_id, system_quantity, counted_quantity, variance)
   - StockCountController: create, store, approve
   - Vue sayfaları: StockCounts Index, Create, Show

2. Stok Fark Raporu:
   - Sayım - Sistem farkı gösterimi
   - Otomatik düzeltme önerisi
   - Approve edilince stock adjustment kaydı oluştur

3. Depo Yetkileri:
   - CheckWarehouseAccess middleware
   - User-Warehouse pivot table (warehouse_managers)
   - Sadece atanan depolara erişim

Tasarım: Mevcut cyan-emerald gradient temayı koru.
```

---

### 3. Gantt/Timeline (Basit) 📊 (ÖNCELİK: DÜŞÜK)
**Tahmini Süre:** 2-3 gün

**Özellikler:**
- Task yönetimi (start, end, duration, progress)
- Bağımlılıklar (predecessors JSON)
- Gantt chart görseli (ApexCharts veya dhtmlxGantt)
- Hakediş ilerlemesiyle entegrasyon

**Database Schema:**
```sql
-- project_schedules table
id, project_id, task_name, task_type (milestone/task),
start_date, end_date, duration_days, progress_percentage,
assigned_to_id (user_id), predecessors (JSON: [task_id, task_id]),
status (not_started/in_progress/completed/on_hold),
notes, created_at, updated_at, deleted_at
```

**Prompt Önerisi:**
```
Basit Gantt/Timeline modülü implement et:

1. Database: project_schedules migration
2. Model: ProjectSchedule (ilişkiler, scope'lar, cast predecessors to array)
3. Controller: ProjectScheduleController (CRUD + Gantt data API endpoint)
4. Vue Sayfaları:
   - ProjectSchedules Index (liste görünümü + Gantt'a geçiş butonu)
   - ProjectSchedules Gantt (ApexCharts timeline chart)
   - ProjectSchedules Create/Edit (task form + predecessor seçimi)
5. Hakediş Entegrasyonu:
   - Task progress'i hakediş tamamlanma yüzdesiyle senkronize et
   - ProgressPayment → ProjectSchedule ilişkisi
6. Sidebar: "Proje Programı" menü öğesi (chart-gantt ikonu)
7. Seeder: Her proje için 8-10 task (milestone + bağımlılıklar)

Tasarım: İndigo-purple gradient tema (proje yönetimi teması)
```

---

### 4. Raporlama Katmanı Derinleştirme 📈 (ÖNCELİK: ORTA-YÜKSEK)
**Tahmini Süre:** 2-3 gün

**Özellikler:**
- PDF/Excel export (maatwebsite/excel, DomPDF)
- Planlı e-posta raporu (Laravel Scheduler)
- Dashboard builder (kullanıcı widget seçimi)
- KPI tanımlama sistemi

**Prompt Önerisi:**
```
Raporlama sistemi implement et:

1. Excel Export:
   - composer require maatwebsite/excel
   - Export sınıfları: TimesheetsExport, ProgressPaymentsExport, SafetyIncidentsExport
   - Controller metodları: exportToExcel() her modülde

2. PDF Export:
   - composer require barryvdh/laravel-dompdf
   - PDF şablonları: resources/views/pdf/* (Blade templates)
   - PdfExportService: generateReport() metodu

3. Planlı Raporlar:
   - scheduled_reports tablosu (report_type, frequency, recipients, filters)
   - Console Command: app/Console/Commands/SendScheduledReports.php
   - Kernel.php'de schedule tanımı

4. Dashboard Builder (opsiyonel - uzun):
   - dashboard_widgets tablosu
   - Widget seçimi UI
   - Kullanıcı bazlı widget tercihlerini kaydet

5. KPI Sistemi:
   - kpis tablosu (name, formula, target_value, current_value)
   - KpiService: calculateKpis()
   - Dashboard'a KPI widget'ları

Tasarım: Mevcut dashboard'a entegre et (analytics teması).
```

---

### 5. Rol & Yetki Sistemi (Proje Bazlı) 🎯 (ÖNCELİK: DÜŞÜK)
**Not:** Spatie Permission zaten var, proje bazlı genişletme gerekiyor.

**Tahmini Süre:** 1-2 gün

**Özellikler:**
- Proje bazlı yetkilendirme
- Çoklu proje yöneticisi/şantiye şefi
- Activity log (tüm işlem geçmişi)
- Middleware: CheckProjectAccess

**Database Schema:**
```sql
-- user_project_roles table
id, user_id, project_id, role_id, assigned_at, assigned_by_id

-- activity_logs table
id, user_id, project_id, action, model_type, model_id,
old_values (JSON), new_values (JSON), ip_address, user_agent,
created_at
```

**Prompt Önerisi:**
```
Proje bazlı rol sistemi implement et:

1. Database:
   - user_project_roles pivot migration
   - activity_logs migration

2. Middleware:
   - CheckProjectAccess (proje erişim kontrolü)
   - Route'lara middleware ekle

3. Activity Logging:
   - ActivityLogService (logActivity metodu)
   - Model Event Listeners (created, updated, deleted)
   - Tüm CRUD işlemlerinde otomatik log

4. UI:
   - Proje Show sayfasına "Takım" tabı
   - Kullanıcı atama/çıkarma
   - ActivityLog Index sayfası (filtreleme)

5. Seeder:
   - Her projeye 2-3 kullanıcı ata (project_manager, site_manager)

Tasarım: Admin panel teması (gray-blue gradient).
```

---

## 🚀 Önerilen Sıralama

### Öncelik 1: Ekipman Yönetimi (1-2 gün)
- En çok talep edilen modül
- Finansal entegrasyon ile değer katıyor

### Öncelik 2: Raporlama Katmanı (2-3 gün)
- Tüm modüllere export yetenekleri kazandırıyor
- Planlı raporlar iş verimliliği artırıyor

### Öncelik 3: Çoklu Depo Genişletme (4-6 saat)
- Mevcut sisteme hızlı ekleme
- Stok sayım ihtiyacı kritik

### Öncelik 4: Gantt/Timeline (2-3 gün)
- Proje yönetimi tamamlayıcısı
- ApexCharts entegrasyonu vakit alabilir

### Öncelik 5: Proje Bazlı Rol (1-2 gün)
- Güvenlik ve yetkilendirme
- Activity log gelecekte audit için değerli

---

## 📊 Faz 3 İlerleme Tahmini

| Modül | Durum | Süre | Tamamlanma |
|-------|-------|------|------------|
| İSG Modülü | ✅ Tamamlandı | 1 gün | %16.67 |
| Ekipman Yönetimi | 🔜 Sırada | 1-2 gün | %33.33 |
| Raporlama Katmanı | 🔜 Beklemede | 2-3 gün | %50.00 |
| Çoklu Depo | 🔜 Beklemede | 0.5 gün | %58.33 |
| Gantt/Timeline | 🔜 Beklemede | 2-3 gün | %83.33 |
| Proje Rol Sistemi | 🔜 Beklemede | 1-2 gün | %100.00 |

**Toplam Kalan Süre:** 7-11 gün (iş günü)
**Tahmini Bitiş:** 10-14 Kasım 2025

---

## 💡 Tasarım Standartları

Tüm yeni modüller için:
- ✅ Full-width layout (`:full-width="true"`)
- ✅ Gradient header (modül temasına göre)
- ✅ Breadcrumb navigation
- ✅ Modern card design (`rounded-xl`, `shadow-sm`)
- ✅ Responsive grid (`lg:grid-cols-2`)
- ✅ Gradient buttons
- ✅ Status badges (durum bazlı renkler)
- ✅ Filtreleme ve arama

**Gradient Tema Önerileri:**
- Ekipman: `from-yellow-600 via-orange-600 to-amber-700`
- Raporlama: `from-purple-600 via-indigo-600 to-purple-700`
- Gantt: `from-indigo-600 via-blue-600 to-indigo-700`
- Roller: `from-gray-600 via-slate-600 to-gray-700`

---

## 🎯 Kullanım

Yeni modül başlatmak için yukarıdaki prompt'lardan birini kopyala ve aşağıdaki gibi kullan:

```
[Prompt Metni]

Ek Notlar:
- Mevcut code style ve architecture'ı takip et
- Transaction-safe DB operations kullan (DB::transaction)
- Comprehensive seeder ekle (test data)
- README.md ve faz3 docs'u güncelle
```

---

## 📝 Notlar

- Her modül bittikten sonra `README.md` ve `docs/faz3-gelismis-moduller.md` güncellenmeli
- Seeder'lar `DatabaseSeeder.php`'ye eklenmeli
- Sidebar icon'ları FontAwesome 6 standardında olmalı
- Route isimlendirme: `/module-name/*` (kebab-case)

**Son Güncelleme:** 30 Ekim 2025
