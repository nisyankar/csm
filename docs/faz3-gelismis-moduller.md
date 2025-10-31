# FAZ 3: Gelişmiş Modüller
## 🚧 DEVAM EDİYOR (62%)

**Hedef:** Ocak - Mart 2026
**Durum:** Devam Ediyor
**Modül Sayısı:** 8 (5 tamamlandı ✅, 3 planlama/geliştirme aşamasında 🔄)

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

### 5. 🔄 Raporlama Katmanı Derinleştirme 📈
**Durum:** Planlama (%0)
**Service:** `ReportBuilderService`
**Özellikler:**
- PDF/Excel export (maatwebsite/excel)
- Planlı e-posta raporu
- Dashboard builder
- KPI tanımlama sistemi

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

### 7. 🆕 Geçici Görevlendirme & Puantaj Transferi 🔀
**Durum:** Teknik Borç / Planlama (%0)
**Database:** `temporary_assignments`, güncelleme: `timesheets` (assigned_project_id)
**Özellikler:**
- 🔄 Personelin farklı projeye geçici görevlendirmesi
- 🔄 Görevlendirildiği projede puantaj görünürlüğü
- 🔄 Finansal transaction otomasyonu:
  - ❌ Günlük maaş otomatik gider kaydı **KALDIRILMALI** (bordro sistemiyle çakışma)
  - ✅ Bordro programından alınan tahakkuk listesi manuel import
  - ✅ Geçici görevlendirmeler bordro hesabında göz önünde bulundurulmalı
  - ✅ Puantaj bazlı iş gücü maliyet raporu (proje bazlı)
- 🔄 Timeline tracking (başlangıç-bitiş tarihi)
- 🔄 Onay mekanizması (proje yöneticisi onayı)

### 8. 🆕 AutoCAD DWG Entegrasyonu 🏗️
**Durum:** Teknik Analiz / Planlama (%0)
**Mevcut Modeller:** `Project`, `ProjectStructure`, `ProjectFloor`, `ProjectUnit`
**Özellikler:**
- 🔄 DWG dosyası yükleme arayüzü (Vue)
- 🔄 Python servis entegrasyonu (ezdxf kütüphanesi)
- 🔄 Background job (Laravel Queue)
- 🔄 DWG parsing ve JSON çıktı oluşturma:
  - Yapı bilgileri (blok/bina)
  - Kat bilgileri (floor level)
  - Daire/birim bilgileri (unit)
  - Metraj/alan bilgileri
- 🔄 Otomatik model doldurma (ProjectFloor, ProjectUnit vb.)
- 🔄 İşlem sonuç raporu
- 🔄 Hata yönetimi ve validasyon
- 🔄 Python script güvenliği (sandbox)

**Teknik Stack:**
```
DWG Upload (Vue/Inertia)
  → Laravel Controller (uploadDWG)
  → Queue Job (ProcessDWGFile)
  → Python Script (ezdxf parser)
  → JSON Output
  → Model Creation (Project*, Floor*, Unit*)
  → User Notification
```

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

1. **Rol & Yetki Sistemi** implementasyonunu tamamla
2. **AutoCAD DWG Entegrasyonu** için Python servis altyapısını kur
3. **Geçici Görevlendirme** modülünü tasarla ve geliştir
4. **Puantaj finansal entegrasyonu** düzeltmesi yap
5. **Gantt/Timeline** modülünü başlat

---

**Önceki Faz:** [Faz 2: Operasyonel Çekirdek](./faz2-operasyonel-moduller.md)
**Sonraki Faz:** [Faz 4: İleri Seviye](./faz4-ileri-seviye.md)
