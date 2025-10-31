# FAZ 3: Gelişmiş Modüller
## ✅ TAMAMLANDI (100%)

**Hedef:** Ocak - Mart 2026
**Durum:** Tamamlandı
**Modül Sayısı:** 9 (9 tamamlandı ✅)

---

## 🎯 MODÜLLER

### 1. ✅ Ekipman & Makine Yönetimi 🚜
**Durum:** Tamamlandı (%100)
**Database:** `equipments`, `equipment_usages`, `equipment_maintenance`
**Özellikler:**
- ✅ 17 farklı ekipman tipi (Ekskavatör, Buldozer, Vinç, Jeneratör vb.)
- ✅ 3 Sahiplik türü: Mülkiyetli, Kiralık, Leasing
- ✅ Ekipman kullanım kayıtları (operatör, proje, tarih, süre)
- ✅ Sayaç okuma takibi (saat, km, çevrim)
- ✅ Yakıt tüketimi ve maliyet hesaplama
- ✅ Bakım ve onarım kayıtları
- ✅ 4 bakım türü: Rutin, Önleyici, Düzeltici, Arıza
- ✅ Periyodik bakım hatırlatıcı sistemi
- ✅ Finansal entegrasyon (kira ve bakım giderleri otomatik kayıt)
- ✅ Ekipman durumu yönetimi (Müsait, Kullanımda, Bakımda, Arızalı)
- ✅ Otomatik kod oluşturma (EKP-001, BKM-001)
- ✅ Modern full-width UI (yellow-amber-orange gradient)
- ✅ NULL-safe pagination (template-based rendering)
- ✅ 9 Vue sayfası: Equipments (Index, Create, Edit, Show) + EquipmentUsages (Index, Create, Edit) + EquipmentMaintenance (Index, Create, Edit)

### 2. ✅ İş Sağlığı & Güvenliği (İSG) 👷
**Durum:** Tamamlandı (%100)
**Database:** `safety_incidents`, `safety_trainings`, `safety_inspections`, `risk_assessments`, `ppe_assignments`
**Özellikler:**
- ✅ İş kazası kayıtları (ramak kala, yaralanma, ölümlü vb.)
- ✅ İSG eğitim planlama ve takibi
- ✅ Güvenlik denetim kontrol listeleri
- ✅ Risk analiz ve değerlendirme formları (RAMS)
- ✅ KKD (Kişisel Koruyucu Donanım) zimmet yönetimi
- ✅ Kök sebep analizi ve düzeltici faaliyetler
- ✅ Full-width modern UI tasarımı
- ✅ Durum bazlı renkli badge'ler
- ✅ Önem derecesi takibi (düşük, orta, yüksek, kritik)
- 🔜 PDF export (planlanan)

### 3. ✅ Çoklu Depo/Lokasyon Sistemi 📦
**Durum:** Tamamlandı (%100)
**Database:** `warehouses`, `stock_movements` (to_warehouse_id eklendi), `stock_counts`
**Özellikler:**
- ✅ Depolar arası transfer sistemi
- ✅ StockTransfer controller ve Vue sayfaları (Index, Create)
- ✅ Toplu sayım sistemi (StockCount)
- ✅ Stok fark takibi (fazla, eksik, eşit)
- ✅ Sayım onay/red süreci
- ✅ Onaylanan sayımlarda otomatik adjustment hareketi
- ✅ Depo bazlı stok sorgulama (getStockByMaterial)
- ✅ Transfer öncesi stok kontrolü
- ✅ Otomatik referans numarası (SAY-001, SAY-002...)
- ✅ Modern full-width UI (blue-indigo-purple gradient)
- ✅ NULL-safe pagination
- ✅ Sidebar entegrasyonu (Satınalma & Stok menüsü altında)
- ✅ 5 Vue sayfası: StockTransfers (Index, Create), StockCounts (Index, Create, Show)
- ✅ Seeder: WarehouseManagementSeeder (5 depo, 32 hareket, 5 transfer)

### 4. ✅ Gantt/Timeline (Basit) 📊
**Durum:** Tamamlandı (%100)
**Database:** `project_schedules`
**Özellikler:**
- ✅ Task yönetimi (start, end, duration, progress)
- ✅ 5 Task tipi: phase, milestone, activity, deliverable, meeting
- ✅ 6 Durum: not_started, in_progress, completed, delayed, on_hold, cancelled
- ✅ 4 Öncelik seviyesi: low, medium, high, critical
- ✅ Hiyerarşik yapı (parent-child tasks)
- ✅ Bağımlılıklar (predecessors JSON: FS, SS, FF, SF)
- ✅ Otomatik task code (TASK-001, TASK-002...)
- ✅ İlerleme takibi (completion_percentage)
- ✅ Gecikme tespiti (is_delayed, delay_days)
- ✅ Maliyet takibi (estimated vs actual cost)
- ✅ Proje-Employee-Department ilişkileri
- ✅ Controller: Full CRUD + Gantt data formatting + updateProgress
- ✅ Routes: RESTful routes + progress update + gantt view
- ✅ Seeder: 11 örnek görev (3 faz, sub-tasks, milestones)
- ✅ Modern full-width UI (orange-amber-yellow gradient)
- ✅ 4 Vue sayfası: ProjectSchedules (Index, Create, Edit, Show)
- ✅ Numaralı form bölümleri (1-6) ile modern UI/UX
- ✅ Progress bar ve circular progress göstergeleri
- ✅ Quick Actions panel (Başlat, Tamamla, İlerleme Güncelle)
- ✅ Alt görev listesi ve bağımlılık gösterimi
- ✅ Sidebar entegrasyonu (Proje Yönetimi menüsü)
- ✅ CheckRole middleware düzeltildi (| separator desteği)
- ✅ Tarih formatı düzeltildi (split('T')[0])
- ✅ **Gantt chart görseli (ApexCharts rangeBar):**
  - ApexCharts ve vue3-apexcharts entegrasyonu
  - Gantt.vue sayfası (Timeline görselleştirme)
  - Zoom seviyeleri (günlük, haftalık, aylık)
  - Renkli task tipleri (mor=faz, sarı=milestone, mavi=aktivite, yeşil=çıktı, gri=toplantı)
  - Filtreler (task tipi, durum, öncelik)
  - İnteraktif tooltip'ler (task detayları)
  - Click to navigate (task detay sayfası)
  - Task legend
- ✅ **Hakediş ilerlemesiyle entegrasyon:**
  - Migration: progress_payments tablosuna project_schedule_id eklendi
  - auto_update_schedule flag (otomatik senkronizasyon kontrolü)
  - ProgressPayment → ProjectSchedule ilişkisi
  - ProgressPayment::syncScheduleProgress() metodu
  - ProjectSchedule::syncProgressFromPayments() metodu
  - Hakediş güncellendiğinde takvim otomatik güncelleniyor
  - Tamamlanma yüzdesi, gerçekleşen maliyet ve durum senkronizasyonu

### 5. ✅ Raporlama Katmanı Derinleştirme 📈
**Durum:** Tamamlandı (%100)
**Database:** `report_templates`, `scheduled_reports`, `kpi_definitions`, `user_dashboards`
**Service:** `ReportBuilderService`, `KpiCalculatorService`, `DashboardService`
**Özellikler:**
- ✅ **PDF/Excel Export Sistemi:**
  - maatwebsite/excel paketi entegrasyonu
  - barryvdh/laravel-dompdf paketi entegrasyonu
  - ReportBuilderService (multi-format export)
  - Modül bazlı rapor şablonları
  - 5 örnek rapor şablonu (Hakediş, Puantaj, Finansal, İSG, Ekipman)
- ✅ **KPI Tanımlama Sistemi:**
  - KpiDefinition model ve migration
  - KpiCalculatorService (6 önceden tanımlı formül)
  - Formüller: project_completion, cost_variance, labor_productivity, safety_incident_rate, equipment_utilization, on_time_delivery
  - Hedef değer ve uyarı eşiği takibi
  - Modül bazlı KPI filtreleme
  - 6 örnek KPI tanımı (seeder)
- ✅ **Dashboard Yönetimi:**
  - DashboardService (widget veri sağlama)
  - UserDashboard model (JSON layout desteği)
  - 8 widget tipi: projects_summary, financial_summary, kpi_overview, progress_payments, timesheets_summary, safety_incidents, equipment_status, recent_activities
  - Kullanıcı bazlı özelleştirilebilir dashboard
- ✅ **Modern UI/UX (Teal-Cyan-Blue Gradient):**
  - KPIs/Index.vue (full-width, modern filtreleme)
  - KPIs/Create.vue (form validasyonu, formula seçici)
  - KPIs/Edit.vue (inline düzenleme)
  - Aktif/Pasif durum badge'leri
  - Modül badge'leri (renkli kategorizasyon)
- ✅ **Controller & Routes:**
  - KpiController (full CRUD + calculate endpoint)
  - kpis.* route grubu (admin, project_manager)
  - RESTful API yapısı
- ✅ **Sidebar Entegrasyonu:**
  - "Raporlama" menü grubu eklendi
  - Chart-bar icon
  - KPI Tanımları linki
- ✅ **Seeder & Test Data:**
  - ReportingSeeder (6 KPI + 5 rapor şablonu)
  - Gerçekçi örnek veriler
  - firstOrCreate ile güvenli seeding
- ✅ NULL-safe pagination
- ✅ Breadcrumb navigation
- 🔜 Planlı e-posta raporu (gelecek sprint)
- 🔜 Dashboard builder UI (gelecek sprint)

### 6. ✅ Rol & Yetki Sistemi (Proje Bazlı) 🎯
**Durum:** Tamamlandı (%100)
**Database:** `user_project_roles`, `activity_logs`, `route_permissions`
**Özellikler:**
- ✅ **Proje bazlı rol yönetimi:**
  - UserProjectRole model ve migration
  - 7 farklı rol tipi (project_manager, site_manager, engineer, foreman, viewer, inspector, safety_officer)
  - Tarih aralığı ile geçici atama desteği
  - Rol bazlı permission sistemi (JSON)
  - Aktif/Pasif durum kontrolü
- ✅ **Activity Log Sistemi:**
  - Tüm sistem aktivitelerini kaydetme
  - 8 farklı aktivite tipi (created, updated, deleted, viewed, logged_in, logged_out, access_denied, custom)
  - 4 önem seviyesi (info, warning, error, critical)
  - Polymorphic ilişki desteği (subject)
  - IP adresi, user agent, route bilgisi kaydı
  - Proje bazlı filtreleme
- ✅ **Middleware:**
  - CheckProjectAccess: Proje erişim kontrolü
  - Dinamik project_id tespiti (route params, form data)
  - Otomatik activity log kaydı (erişim engelleme)
- ✅ **User Model Geliştirmeleri:**
  - projectRoles() ilişkisi
  - activityLogs() ilişkisi
  - canAccessProject() - Proje erişim kontrolü (yeni sistem + eski sistem uyumlu)
  - getProjectRole() - Kullanıcının projedeki rolünü getir
  - getAccessibleProjects() - Erişilebilir projeleri listele
- ✅ **Modern UI/UX:**
  - UserProjectRoles Index.vue (purple-indigo-blue gradient)
  - ActivityLogs Index.vue (slate-gray gradient)
  - Hakediş modülüne benzer full-width tasarım
  - Modern card'lar ve filtreler
  - Rol badge'leri (renkli)
  - Önem seviyesi göstergeleri
- ✅ **Controller'lar:**
  - UserProjectRoleController (CRUD + activate/deactivate + byUser/byProject)
  - ActivityLogController (index, show, userActivity, projectActivity, export)
- ✅ **Route'lar ve Sidebar:**
  - user-project-roles.* route grubu (admin, hr)
  - activity-logs.* route grubu (admin, hr)
  - route-permissions.* route grubu (admin only)
  - Sidebar'a "Rol & Yetki Yönetimi" bölümü eklendi
  - Route Yetkileri menüsü (sadece admin)
- ✅ **Route-based Permission Management:**
  - RoutePermission model ve migration
  - RoutePermissionController (full CRUD + sync + bulk update)
  - Otomatik route sync (syncFromRoutes) - Laravel route list'ten tüm route'ları çeker
  - Anlamlı Türkçe isim oluşturma (generateDisplayName)
  - Action type belirleme (view, create, edit, delete, vb.)
  - RoutePermissions/Index.vue (orange-red gradient, modern UI)
  - Modül bazlı filtreleme
  - Toplu yetki güncelleme
  - Tekil route düzenleme (modal)
  - Tablo görünümü: Anlamlı isim, route adı, URI, izinli roller
  - 11 farklı rol desteği (admin, hr, project_manager, site_manager, engineer, foreman, inspector, safety_officer, viewer, accounting, finance)
- ✅ **Vue Sayfaları:**
  - UserProjectRoles/Index.vue, Create.vue, Edit.vue
  - ActivityLogs/Index.vue, Show.vue
  - RoutePermissions/Index.vue
  - Tüm sayfalar modern full-width gradient header'lı
  - Pagination null-safe düzeltildi (dynamic component kullanımı)
- ✅ **Teknik İyileştirmeler:**
  - Modal component path düzeltmesi (@/Components/UI/Modal.vue)
  - UserProjectRoleController prop isim tutarlılığı
  - Migration şema iyileştirmesi (nullable fields)
  - Pagination Link null href hatası düzeltildi

### 7. ✅ Geçici Görevlendirme & Puantaj Transferi 🔀
**Durum:** Tamamlandı (%100)
**Database:** `temporary_assignments` (with SoftDeletes), `timesheets` (temporary_assignment_id eklendi)
**Özellikler:**
- ✅ Personelin farklı projeye geçici görevlendirmesi
- ✅ 4 durum tipi: pending, active, completed, cancelled
- ✅ **Migration:**
  - temporary_assignments tablosu (15 alan + indexes)
  - **preferred_shift_id** kolonu eklendi (nullable FK to shifts)
  - timesheets tablosuna temporary_assignment_id foreign key
  - SoftDeletes desteği
  - Tarih bazlı indexler
- ✅ **TemporaryAssignment Model:**
  - **9 ilişki** (employee, fromProject, toProject, **preferredShift**, requestedBy, approvedBy, timesheets)
  - 5 scope (active, pending, forEmployee, forProject, expiringSoon)
  - 4 accessor (status_label, duration_days, is_active, is_expired)
  - 5 method (approve, reject, complete, cancel, getProgressPercentage, getRemainingDays)
- ✅ **TemporaryAssignmentService:**
  - createAssignment() - Çakışma kontrolü ile oluşturma
  - approveAssignment() - Onaylama
  - rejectAssignment() - Reddetme
  - getActiveAssignment() - Aktif görevlendirme getir
  - checkConflicts() - Tarih çakışma kontrolü
  - autoCompleteExpired() - Süresi dolmuş görevlendirmeleri otomatik tamamla (cron job)
  - getAssignmentHistory() - Çalışan geçmişi
  - transferTimesheet() - Puantaj transferi
  - getExpiringSoon() - 7 gün içinde süresi dolacaklar
  - getStatistics() - Dashboard istatistikleri
  - extendAssignment() - Görevlendirme süresini uzat
- ✅ **TemporaryAssignmentController:**
  - Full CRUD (index, create, store, show, edit, update, destroy)
  - approve() - Onaylama endpoint
  - reject() - Reddetme endpoint (neden ile)
  - complete() - Tamamlama endpoint
  - byEmployee() - Çalışan bazlı API
  - byProject() - Proje bazlı API
  - checkConflicts() - AJAX çakışma kontrolü
- ✅ **Routes (16 route):**
  - RESTful CRUD routes
  - /approve, /reject, /complete action routes
  - /employee/{id}, /project/{id} API routes
  - /check-conflicts AJAX endpoint
  - Middleware: role:admin|hr|project_manager
- ✅ **Modern Vue Sayfaları (4 sayfa):**
  - **Index.vue**: Full-width gradient header (indigo-purple-pink), stats cards (Total, Pending, Active, Expiring), filtreler (employee, from/to project, status, search), modern tablo, NULL-safe pagination
  - **Create.vue**: Multi-step form layout (**5 bölüm**), employee selection, project transfer visualization (from → to), **vardiya seçimi (Step 3 - zorunlu)**, date range picker, conflict warning, duration calculator, reason & notes
  - **Edit.vue**: Status-aware editing (active: sadece end_date uzatma, pending: full edit + **vardiya değişikliği**), conditional fields, validation
  - **Show.vue**: Comprehensive info display (**10 card section** - shift info card eklendi), progress bar with time elapsed, remaining days calculation, status-based action buttons, timeline view, related timesheets, approval info, **vardiya bilgileri (name, code, daily_hours)**
- ✅ **Model Entegrasyonları:**
  - Timesheet model: temporaryAssignment() relationship + scopeForAssignment()
  - Employee model: temporaryAssignments() relationship + getActiveTemporaryAssignment()
- ✅ **Console Command:**
  - AutoCompleteAssignments command (assignments:auto-complete)
  - Otomatik günlük çalışma (01:00)
  - routes/console.php'de Schedule::command() ile tanımlı
  - TemporaryAssignmentService->autoCompleteExpired() kullanımı
- ✅ **Seeder:**
  - TemporaryAssignmentSeeder (10 örnek görevlendirme)
  - Karışık durumlar (pending, active, completed)
  - Bazıları süresi dolmak üzere (7 gün içinde)
  - Çakışma kontrolü ile güvenli seeding
- ✅ **Sidebar Entegrasyonu:**
  - "Çalışan Yönetimi" menüsü altında
  - "Geçici Görevlendirme" linki eklendi
  - route().current('temporary-assignments.*') active kontrolü
- ✅ **Tasarım Standartları:**
  - Modern full-width gradient header (indigo-purple-pink theme)
  - Card-based layout (her bölüm card içinde)
  - NULL-safe operations (optional chaining ve fallback'ler)
  - Responsive grid layouts
  - Status badge'leri (renkli göstergeler)
  - Icon kullanımı (Heroicons)
  - Breadcrumb navigation
  - Form validation ve error display
  - Loading states ve disabled buttons
- ✅ **Özellikler:**
  - Çakışan görevlendirme engelleme
  - Onay/red süreci (proje yöneticisi)
  - **Vardiya seçimi** (geçici görevlendirme için tercih edilen vardiya)
  - **Puantaj otomatik bağlama** (active assignment check, varsayılan vardiya ile)
  - **İzin entegrasyonu** (LeaveTimesheetSyncService):
    - Geçici görevlendirme sırasında alınan izin **hedef projeye** kaydedilir
    - temporary_assignment_id ilişkisi kurulur
    - Notlarda geçici görevlendirme bilgisi eklenir
  - Süresi dolan görevlendirmeleri otomatik tamamlama
  - İlerleme takibi (progress percentage)
  - Kalan gün hesaplama
  - Süre uzatma (aktif görevlendirmeler için)
  - Proje ve çalışan bazlı filtreleme
  - Çalışan geçmişi görüntüleme
  - 7 gün içinde süresi dolacak uyarıları

### 8. ✅ AutoCAD DWG/DXF İçe Aktarım 🏗️
**Durum:** Tamamlandı (%100) - 1 Kasım 2025
**Database:** `dwg_imports`
**Python:** `ezdxf` library integration
**Queue Jobs:** `ProcessDwgFile`, `ApplyDwgImportMappings`
**Özellikler:**
- ✅ **Esnek İmport Tipleri (4 Mod):**
  - `comprehensive`: Toplu içe aktarım (Yapı + Kat + Birim hepsi birden)
  - `structures_only`: Sadece yapılar (bina/blok yapısı)
  - `floors_only`: Sadece katlar (mevcut yapılara kat ekleme)
  - `units_only`: Sadece birimler (mevcut katlara daire/birim ekleme)
- ✅ **3 Aşamalı İmport Akışı:**
  - **Upload**: DWG/DXF dosyası yükleme + import tipi seçimi
  - **Parsing & Review**: Python ile parse → `ready_for_review` durumu → Layer eşleştirme UI
  - **Approval**: Kullanıcı onayı → Queue job ile kayıt oluşturma → `completed`
- ✅ **Layer Mapping Sistemi:**
  - DWG'den tespit edilen her layer için eşleştirme seçenekleri:
    - **Mevcut'a Bağla**: Projedeki mevcut yapı/kat'a bağlama
    - **Yeni Oluştur**: DWG'den gelen isimle yeni kayıt oluşturma
    - **Atla**: Layer'ı import etmeme
  - Akıllı eşleştirme UI (dropdown'lar, nested selections)
  - Manuel düzenleme imkanı (isimlendirme, birleştirme)
- ✅ **Migration & Model:**
  - `dwg_imports` tablosu: import_type, status, detected_layers, layer_mappings, parsed_data, created_structures
  - 5 durum: `pending`, `processing`, `ready_for_review`, `completed`, `failed`
  - DwgImport model: Türkçe accessor'lar, helper methods, scopes
  - Relationships: project, uploader
  - SoftDeletes desteği
- ✅ **Python Parser (ezdxf):**
  - `scripts/parse_dwg.py` - DWG/DXF dosya parsing
  - Layer analizi, block analizi, text entity parsing, polyline area calculation
  - Akıllı kat numarası tespiti (Zemin, Bodrum, -1, +1, Çatı, vs.)
  - JSON çıktı formatı: `{success, message, data: {structures, floors, units}, stats}`
  - Fallback mekanizması (parsing başarısız olursa örnek yapı oluşturma)
- ✅ **Queue Jobs (Background Processing):**
  - **ProcessDwgFile**: Python script çalıştır → Parse → Layer bilgisi çıkar → `ready_for_review`
  - **ApplyDwgImportMappings**: Kullanıcı eşleştirmelerini uygula → Kayıt oluştur → `completed`
  - 10 dakika timeout, 3 retry desteği
  - Hata yönetimi: Exception logging, failed() handler, error_details JSON
  - `extractLayerInformation()`: Tespit edilen layer'ları kullanıcıya sunmak için formatlama
- ✅ **Controller & Routes:**
  - DwgImportController: index, create, store, show, updateMappings, approve, destroy
  - 7 route: Liste, yükleme, detay, mapping güncelleme, onaylama, silme
  - Middleware: role:admin|project_manager
  - File validation: .dwg, .dxf, max 50MB
  - FormData ile dosya upload
- ✅ **Modern Vue Sayfaları (3 sayfa):**
  - **Index.vue**: Full-width gradient header (blue-cyan-teal), filtreleme (proje, durum), status badge'leri, istatistikler (yapı/kat/birim sayıları), pagination, delete modal, NULL-safe rendering
  - **Create.vue**: 4 adımlı wizard form:
    - Step 1: Proje seçimi
    - Step 2: İmport tipi seçimi (4 radio card)
    - Step 3: Drag & drop dosya yükleme (file size display)
    - Step 4: Notlar (opsiyonel)
  - **Show.vue**: Layer mapping interface, processing status display, error display, file info card, **layer eşleştirme UI**:
    - Her layer için 3 seçenek (radio cards)
    - Mevcut yapı/kat dropdown'ları (nested, grouped)
    - Yeni oluştur input (editable)
    - Atla seçeneği
    - Kaydet ve Onayla butonları
    - Tamamlanma sonuçları (istatistik card'ları)
- ✅ **Teknik Özellikler:**
  - Python-Laravel entegrasyonu (exec ile script çalıştırma)
  - JSON data exchange (Python → Laravel)
  - Multi-platform Python executable detection (python3, python, Windows paths)
  - Transaction-safe kayıt oluşturma (DB::beginTransaction/commit/rollBack)
  - Import type filtering (comprehensive/structures_only/floors_only/units_only)
  - Mevcut kayıtlara bağlama veya yeni oluşturma desteği
  - File storage: storage/app/dwg_imports (UUID filenames)
- ✅ **Sidebar Entegrasyonu:**
  - "Proje Yönetimi" menüsü altında
  - "DWG İçe Aktarım" linki eklendi
  - route().current('dwg-imports.*') active kontrolü
- ✅ **Tasarım Standartları:**
  - Modern full-width gradient header (blue-cyan-teal theme)
  - Card-based layout
  - NULL-safe operations
  - Responsive grid layouts
  - Status badge'leri (5 renk: gray, blue, yellow, green, red)
  - Icon kullanımı (document/upload icons)
  - Breadcrumb navigation
  - Loading states (spinner animasyonları)
  - Form validation ve error handling
  - Drag & drop file upload UI

**Teknik Stack:**
```
DWG Upload (Vue/Inertia + Drag&Drop)
  → Laravel Controller (DwgImportController::store)
  → Queue Job (ProcessDwgFile)
  → Python Script (parse_dwg.py - ezdxf)
  → JSON Output (structures, floors, units)
  → Status: ready_for_review
  → User Layer Mapping (Show.vue)
  → updateMappings() → approve()
  → Queue Job (ApplyDwgImportMappings)
  → Model Creation (ProjectStructure, ProjectFloor, ProjectUnit)
  → Status: completed
```

**Hiyerarşik Görünüm:**
- Yapılar: `ml-0`, purple-500 border-l-4, purple-50 bg
- Katlar: `ml-8`, blue-500 border-l-4, blue-50 bg
- Birimler: `ml-16`, teal-500 border-l-4, teal-50 bg

**Auto-Refresh:**
```javascript
watch(() => import_.value?.status, (status) => {
  if (status === 'processing' || status === 'pending') {
    pollingInterval = setInterval(() => {
      router.reload({ only: ['import'] })
    }, 3000)
  }
})
```

**Düzeltilen Hatalar:**
1. ✅ Field 'code' doesn't have default value → `generateStructureCode()` eklendi
2. ✅ Field 'structure_id' doesn't have default value → Floor relationship'ten alınıyor
3. ✅ Wrong field 'unit_number' → `unit_code` olarak değiştirildi
4. ✅ Invalid status 'available' → `not_started` (valid: not_started, in_progress, completed, delivered, sold)

**Kurulum:**
```bash
# Python dependencies
pip install ezdxf

# Laravel Queue
php artisan queue:work --tries=3 --timeout=300

# Permissions (Linux/Mac)
chmod +x scripts/parse_dwg.py
```

### 9. 🆕 Flutter Mobil Uygulama (iOS & Android) 📱
**Durum:** Backend API Hazır (%40) → Frontend Geliştirme Bekliyor
**Platform:** Flutter 3.x + Laravel Sanctum API
**Özellikler:**
- 🔄 **Authentication & Session:**
  - Laravel Sanctum API token entegrasyonu
  - Login/Logout/Remember Me
  - Biometric authentication (Face ID, Touch ID, Fingerprint)
  - Secure token storage (flutter_secure_storage)
  - Auto-refresh token mekanizması
- 🔄 **Ana Modüller:**
  - Dashboard (KPI'lar, son aktiviteler)
  - Projeler (liste, detay, oluştur)
  - Hakediş (liste, detay, onay/red)
  - Puantaj (giriş/çıkış, liste, tarih seçici)
  - Metraj (liste, detay, fotoğraf ekleme)
  - Stok (liste, transfer, sayım)
  - İSG (kaza kaydı, denetim, fotoğraf)
  - Ekipman (liste, kullanım kaydı, bakım)
- 🔄 **Offline Support:**
  - Hive/SQLite local database
  - Sync mekanizması (background sync)
  - Conflict resolution stratejisi
  - Queue sistemi (pending requests)
- 🔄 **Kamera & Medya:**
  - Fotoğraf çekme ve yükleme
  - QR kod okuma (ekipman, malzeme)
  - PDF görüntüleme
  - Image compression
- 🔄 **Bildirimler:**
  - Firebase Cloud Messaging (FCM)
  - Push notification (hakediş onayı, görevlendirme vb.)
  - Local notification (hatırlatıcılar)
- 🔄 **UI/UX:**
  - Material Design 3
  - Dark/Light theme
  - Responsive layout (tablet desteği)
  - Pull-to-refresh
  - Infinite scroll pagination
  - Skeleton loaders
  - Türkçe dil desteği
- 🔄 **Güvenlik:**
  - SSL pinning
  - Jailbreak/Root detection
  - API request encryption (opsiyonel)
  - Biometric auth
- 🔄 **Harita & Konum:**
  - Google Maps entegrasyonu
  - Proje/şantiye lokasyonu
  - GPS koordinat kaydı
  - Geo-fencing (şantiye giriş/çıkış)

**Teknik Stack:**
```
Flutter 3.x
├── State Management: Riverpod / Bloc
├── API Client: Dio + Retrofit
├── Local DB: Hive / Drift (SQLite)
├── Routing: Go Router
├── Auth: flutter_secure_storage + Sanctum
├── Notifications: firebase_messaging
├── Camera: image_picker, camera
├── Maps: google_maps_flutter
├── QR: mobile_scanner
└── Biometric: local_auth
```

**✅ API Endpoints (Laravel - TAMAMLANDI):**
```
Laravel API (Laravel Sanctum) ✅
├── ✅ /api/v1/auth/login (login + token)
├── ✅ /api/v1/auth/logout (token iptal)
├── ✅ /api/v1/auth/me (user info)
├── ✅ /api/v1/auth/refresh (token yenileme)
├── ✅ /api/v1/auth/change-password
├── ✅ /api/v1/auth/register-device (FCM token)
├── ✅ /api/v1/mobile/timesheet/clock-in
├── ✅ /api/v1/mobile/timesheet/clock-out
├── ✅ /api/v1/mobile/timesheet/today-status
├── ✅ /api/v1/mobile/timesheet/week-summary
├── ✅ /api/v1/mobile/timesheet/month-summary
├── ✅ /api/v1/mobile/timesheets (list, filter, pagination)
├── ✅ /api/v1/mobile/sync/timesheets (offline sync)
├── ✅ /api/v1/projects/* (Mevcut ApiProjectController)
├── 🔄 /api/v1/progress-payments/* (yapılacak)
├── 🔄 /api/v1/quantities/* (yapılacak)
├── 🔄 /api/v1/materials/* (yapılacak - zaten mevcut)
└── 🔄 /api/v1/notifications/* (yapılacak)
```

**✅ Oluşturulan Dosyalar:**
- ✅ `app/Http/Controllers/Api/AuthController.php` - Authentication API
- ✅ `app/Http/Controllers/Api/TimesheetController.php` - Timesheet API (Clock In/Out)
- ✅ `app/Http/Resources/Api/ProjectResource.php` - JSON transformer
- ✅ `app/Http/Resources/Api/EmployeeResource.php` - JSON transformer
- ✅ `app/Http/Resources/Api/TimesheetResource.php` - JSON transformer
- ✅ `app/Http/Resources/Api/ProgressPaymentResource.php` - JSON transformer
- ✅ `docs/API-TEST-GUIDE.md` - API test kılavuzu (cURL örnekleri)
- ✅ Migration: `create_personal_access_tokens_table` (Sanctum)
- ✅ User Model: `HasApiTokens` trait eklendi
- ✅ API Routes: `/api/v1/*` route'ları tanımlandı

**Deployment:**
- iOS: App Store (TestFlight için beta)
- Android: Google Play Store (Internal Testing)
- CI/CD: Codemagic / GitHub Actions
- Versioning: Semantic versioning (1.0.0)

---

## 📋 TEKNİK BORÇLAR

### 1. Puantaj Finansal Entegrasyonu
**Durum:** ⚠️ Acil Düzeltme Gerekli
**Problem:**
- Puantajlardan günlük maaş otomatik gider kaydı yapılıyor
- Bordro programından gelen tahakkuk listesiyle çakışma yaratıyor
- Çift kayıt riski

**Çözüm:**
- Puantajdan otomatik finansal transaction **devre dışı bırakılmalı**
- Bordro programından tahakkuk listesi import sistemi kurulmalı
- Geçici görevlendirmeler bordro hesabına dahil edilmeli
- İş gücü maliyet raporu proje bazlı ayrı tutulmalı

### 2. User Model - hasRole() Metodu
**Durum:** ✅ Tamamlandı
**Çözüm:** `user_type` field'ına göre custom hasRole() metodu eklendi

---

## 🔮 SONRAKI ADIMLAR

1. ✅ ~~**Rol & Yetki Sistemi** implementasyonunu tamamla~~
2. **Raporlama Katmanı** - PDF/Excel export sistemi
3. **Flutter Mobil Uygulama** - Laravel API backend kurulumu (Sanctum)
4. **Flutter Mobil Uygulama** - iOS/Android app geliştirme
5. **Geçici Görevlendirme** modülünü tasarla ve geliştir
6. **AutoCAD DWG Entegrasyonu** için Python servis altyapısını kur
7. **Puantaj finansal entegrasyonu** düzeltmesi yap

---

**Önceki Faz:** [Faz 2: Operasyonel Çekirdek](./faz2-operasyonel-moduller.md)
**Sonraki Faz:** [Faz 4: İleri Seviye](./faz4-ileri-seviye.md)
