# 📋 FAZ 2: GÜNLÜK OPERASYONLAR VE TAKİP SİSTEMLERİ

**Durum:** 🔄 Planlama Aşamasında
**Başlangıç:** Faz 1 tamamlandıktan sonra
**Önceki Faz:** [Faz 1 - Proje Yapısı](./PROJE_YAPISI_YENİDEN_TASARIM.md) ✅

---

## 🎯 FAZ 2 HEDEFLERI

Faz 1'de oluşturduğumuz fiziksel yapı ve iş takip sistemi üzerine, **günlük şantiye operasyonlarını** yönetecek modüller ekleyeceğiz:

1. **Günlük Raporlar** - Şantiye günlük aktivite raporları
2. **Hasar/Eksiklik Takibi** - Tespit edilen sorunların kaydı ve çözümü
3. **Denetim Kayıtları** - Kalite ve güvenlik denetimleri
4. **Fotoğraf Galerisi** - İlerleme fotoğrafları ve timelapse
5. **Ekipman Takibi** - Şantiye ekipmanlarının yönetimi
6. **Malzeme Talep Sistemi** - Malzeme ihtiyaç ve tedarik takibi

---

## 📊 1. GÜNLÜK RAPORLAR MODÜLÜ

### Amaç
Şantiye şefi veya proje yöneticisinin günlük olarak şantiyede neler olup bittiğini kaydetmesi.

### Veri Modeli

```sql
CREATE TABLE daily_reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    project_id BIGINT NOT NULL,
    report_date DATE NOT NULL,
    reported_by BIGINT NOT NULL,

    -- Hava Durumu
    weather_condition ENUM('sunny', 'cloudy', 'rainy', 'snowy', 'windy', 'stormy'),
    temperature DECIMAL(5,2) NULL COMMENT 'Celsius',
    weather_notes TEXT NULL,

    -- İş Gücü
    total_workers INT DEFAULT 0,
    subcontractor_workers INT DEFAULT 0,
    internal_workers INT DEFAULT 0,

    -- İş Durumu
    work_summary TEXT NOT NULL COMMENT 'Günün genel özeti',
    completed_works JSON NULL COMMENT 'Tamamlanan işler',
    ongoing_works JSON NULL COMMENT 'Devam eden işler',
    planned_works JSON NULL COMMENT 'Yarın planlanan işler',

    -- Sorunlar ve Engeller
    has_delays BOOLEAN DEFAULT false,
    delay_reasons JSON NULL,
    has_accidents BOOLEAN DEFAULT false,
    accident_details JSON NULL,
    has_material_shortage BOOLEAN DEFAULT false,
    material_shortage_details JSON NULL,

    -- Ziyaretçiler
    visitors JSON NULL COMMENT 'Müşteri, müfettiş vb.',

    -- Ekipman
    equipment_usage JSON NULL COMMENT 'Kullanılan ekipmanlar',

    -- Fotoğraflar
    photos JSON NULL COMMENT 'Günlük fotoğraflar',

    -- Onay
    approval_status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft',
    approved_by BIGINT NULL,
    approved_at TIMESTAMP NULL,

    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,

    UNIQUE KEY unique_project_date (project_id, report_date)
);
```

### Özellikler
- ✅ Her proje için günlük tek rapor
- ✅ Hava durumu takibi (işçi verimliliği için)
- ✅ İşçi sayıları
- ✅ Tamamlanan ve devam eden işler
- ✅ Gecikmeler ve sebepler
- ✅ Kaza kayıtları
- ✅ Malzeme sıkıntıları
- ✅ Ziyaretçi kayıtları
- ✅ Ekipman kullanımı
- ✅ Onay süreci

### API Endpoints
```
GET    /api/v1/daily-reports
POST   /api/v1/daily-reports
GET    /api/v1/daily-reports/{id}
PUT    /api/v1/daily-reports/{id}
DELETE /api/v1/daily-reports/{id}
POST   /api/v1/daily-reports/{id}/submit
POST   /api/v1/daily-reports/{id}/approve
POST   /api/v1/daily-reports/{id}/reject
GET    /api/v1/projects/{id}/daily-reports/summary
```

---

## 🔧 2. HASAR/EKSİKLİK TAKİP MODÜLÜ

### Amaç
Şantiyede tespit edilen hataları, eksiklikleri, hasarları kaydetmek ve çözüm sürecini takip etmek.

### Veri Modeli

```sql
CREATE TABLE defects (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    project_id BIGINT NOT NULL,
    structure_id BIGINT NULL,
    floor_id BIGINT NULL,
    unit_id BIGINT NULL,

    -- Defect Bilgileri
    defect_code VARCHAR(50) UNIQUE NOT NULL COMMENT 'Örn: DEF-2025-001',
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,

    -- Kategori
    category ENUM('structural', 'finishing', 'electrical', 'plumbing', 'hvac', 'safety', 'other') NOT NULL,

    -- Öncelik
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',

    -- Sorumlular
    reported_by BIGINT NOT NULL,
    assigned_to BIGINT NULL COMMENT 'Düzeltmeden sorumlu kişi/taşeron',
    assigned_to_type ENUM('employee', 'subcontractor') NULL,

    -- Durum
    status ENUM('reported', 'acknowledged', 'in_progress', 'resolved', 'verified', 'rejected', 'wont_fix') DEFAULT 'reported',

    -- Tarihler
    reported_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE NULL,
    resolved_date TIMESTAMP NULL,
    verified_date TIMESTAMP NULL,

    -- Maliyet
    estimated_cost DECIMAL(12,2) NULL,
    actual_cost DECIMAL(12,2) NULL,

    -- Medya
    photos JSON NULL COMMENT 'Fotoğraflar (tespit + çözüm sonrası)',
    documents JSON NULL COMMENT 'İlgili dökümanlar',

    -- Çözüm
    resolution_notes TEXT NULL,

    -- İlişkiler
    related_work_item_id BIGINT NULL,
    related_subcontractor_id BIGINT NULL,

    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (structure_id) REFERENCES project_structures(id) ON DELETE SET NULL,
    FOREIGN KEY (floor_id) REFERENCES project_floors(id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES project_units(id) ON DELETE SET NULL,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (related_work_item_id) REFERENCES work_items(id) ON DELETE SET NULL,
    FOREIGN KEY (related_subcontractor_id) REFERENCES subcontractors(id) ON DELETE SET NULL,

    INDEX idx_project_status (project_id, status),
    INDEX idx_severity (severity),
    INDEX idx_assigned (assigned_to, status)
);
```

### Özellikler
- ✅ Benzersiz defect kodu
- ✅ Konum bazlı kayıt (Blok/Kat/Daire)
- ✅ Öncelik ve aciliyet seviyeleri
- ✅ Atama sistemi (çalışan veya taşeron)
- ✅ Durum takibi (7 farklı durum)
- ✅ Maliyet tahmini ve gerçek maliyet
- ✅ Fotoğraf eklem (tespit + çözüm sonrası)
- ✅ Çözüm notu
- ✅ İlişkili iş kalemi ve taşeron

### API Endpoints
```
GET    /api/v1/defects
POST   /api/v1/defects
GET    /api/v1/defects/{id}
PUT    /api/v1/defects/{id}
DELETE /api/v1/defects/{id}
POST   /api/v1/defects/{id}/assign
POST   /api/v1/defects/{id}/resolve
POST   /api/v1/defects/{id}/verify
POST   /api/v1/defects/{id}/reject
GET    /api/v1/projects/{id}/defects/stats
GET    /api/v1/defects/by-severity
GET    /api/v1/defects/overdue
```

---

## 📋 3. DENETİM KAYITLARI MODÜLÜ

### Amaç
Kalite kontrol, güvenlik denetimleri, resmi müfettiş ziyaretlerini kaydetmek.

### Veri Modeli

```sql
CREATE TABLE inspections (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    project_id BIGINT NOT NULL,
    structure_id BIGINT NULL,
    floor_id BIGINT NULL,
    unit_id BIGINT NULL,

    -- Denetim Bilgileri
    inspection_code VARCHAR(50) UNIQUE NOT NULL,
    inspection_type ENUM('quality', 'safety', 'regulatory', 'client', 'internal', 'final') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,

    -- Denetçi
    inspector_name VARCHAR(255) NOT NULL,
    inspector_organization VARCHAR(255) NULL COMMENT 'Belediye, İSG, Müşteri vb.',
    inspector_phone VARCHAR(20) NULL,
    inspector_email VARCHAR(255) NULL,

    -- Tarih
    inspection_date DATE NOT NULL,
    inspection_time TIME NULL,

    -- Sonuç
    result ENUM('passed', 'passed_with_conditions', 'failed', 'pending') DEFAULT 'pending',
    score INT NULL COMMENT '0-100 arası puan',

    -- Bulgular
    findings JSON NULL COMMENT 'Detaylı bulgular listesi',
    positive_findings JSON NULL,
    negative_findings JSON NULL,

    -- Gerekli Düzeltmeler
    required_actions JSON NULL,
    action_deadline DATE NULL,

    -- Medya
    photos JSON NULL,
    documents JSON NULL COMMENT 'Denetim raporları, sertifikalar',

    -- Takip
    follow_up_required BOOLEAN DEFAULT false,
    follow_up_date DATE NULL,
    follow_up_completed BOOLEAN DEFAULT false,

    -- İlişkiler
    related_work_items JSON NULL,

    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (structure_id) REFERENCES project_structures(id) ON DELETE SET NULL,
    FOREIGN KEY (floor_id) REFERENCES project_floors(id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES project_units(id) ON DELETE SET NULL,

    INDEX idx_project_type (project_id, inspection_type),
    INDEX idx_result (result),
    INDEX idx_date (inspection_date)
);
```

### Özellikler
- ✅ Farklı denetim tipleri
- ✅ Harici denetçi bilgileri
- ✅ Sonuç ve skorlama
- ✅ Pozitif ve negatif bulgular
- ✅ Gerekli düzeltme listesi
- ✅ Takip tarihi ve tamamlanma
- ✅ Döküman ve fotoğraf ekleme

---

## 📸 4. FOTOĞRAF GALERİSİ & TİMELAPSE

### Amaç
Projenin ilerleyişini görsel olarak kaydetmek, timelapse video oluşturmak.

### Veri Modeli

```sql
CREATE TABLE project_photos (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    project_id BIGINT NOT NULL,
    structure_id BIGINT NULL,
    floor_id BIGINT NULL,
    unit_id BIGINT NULL,

    -- Fotoğraf Bilgileri
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT NOT NULL COMMENT 'bytes',
    mime_type VARCHAR(100) NOT NULL,

    -- Metadata
    title VARCHAR(255) NULL,
    description TEXT NULL,
    caption VARCHAR(500) NULL,

    -- Kategori
    category ENUM('progress', 'defect', 'inspection', 'safety', 'before', 'after', 'timelapse', 'other') DEFAULT 'progress',

    -- Konum ve Yön
    camera_angle ENUM('front', 'back', 'left', 'right', 'top', 'interior', 'detail') NULL,
    gps_latitude DECIMAL(10, 8) NULL,
    gps_longitude DECIMAL(11, 8) NULL,

    -- Zaman
    taken_at TIMESTAMP NOT NULL,
    uploaded_by BIGINT NOT NULL,

    -- İlişkiler
    related_to_type ENUM('work_assignment', 'defect', 'inspection', 'daily_report') NULL,
    related_to_id BIGINT NULL,

    -- Etiketler
    tags JSON NULL,

    -- Görünürlük
    is_public BOOLEAN DEFAULT false,
    is_featured BOOLEAN DEFAULT false,

    -- Timelapse
    is_timelapse_frame BOOLEAN DEFAULT false,
    timelapse_sequence_number INT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (structure_id) REFERENCES project_structures(id) ON DELETE SET NULL,
    FOREIGN KEY (floor_id) REFERENCES project_floors(id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES project_units(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,

    INDEX idx_project_category (project_id, category),
    INDEX idx_taken_at (taken_at),
    INDEX idx_timelapse (project_id, is_timelapse_frame, timelapse_sequence_number)
);
```

### Özellikler
- ✅ Konum bazlı fotoğraflar
- ✅ Kategorizasyon
- ✅ Kamera açısı bilgisi
- ✅ GPS koordinatları
- ✅ İlişkili kayıt (defect, inspection, vb.)
- ✅ Etiketleme sistemi
- ✅ Timelapse için sıralama
- ✅ Öne çıkan fotoğraflar

---

## 🚜 5. EKİPMAN TAKİBİ MODÜLÜ

### Amaç
Şantiyedeki ekipmanları, kullanımlarını, bakımlarını takip etmek.

### Veri Modeli

```sql
CREATE TABLE equipment (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    equipment_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    category ENUM('heavy_machinery', 'tools', 'vehicles', 'safety', 'scaffolding', 'other') NOT NULL,
    type VARCHAR(100) NOT NULL COMMENT 'Vinç, Ekskavatör, Matkap vb.',

    -- Sahiplik
    ownership_type ENUM('owned', 'rented', 'subcontractor') NOT NULL,
    owner VARCHAR(255) NULL,

    -- Teknik Bilgiler
    brand VARCHAR(100) NULL,
    model VARCHAR(100) NULL,
    serial_number VARCHAR(100) NULL,
    purchase_date DATE NULL,
    purchase_price DECIMAL(12,2) NULL,

    -- Kiralama (Eğer kiralık ise)
    rental_company VARCHAR(255) NULL,
    daily_rental_cost DECIMAL(10,2) NULL,
    rental_start_date DATE NULL,
    rental_end_date DATE NULL,

    -- Durum
    status ENUM('available', 'in_use', 'maintenance', 'broken', 'retired') DEFAULT 'available',
    condition ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',

    -- Lokasyon
    current_project_id BIGINT NULL,
    current_structure_id BIGINT NULL,
    storage_location VARCHAR(255) NULL,

    -- Bakım
    last_maintenance_date DATE NULL,
    next_maintenance_date DATE NULL,
    maintenance_interval_days INT NULL,

    -- Medya
    photos JSON NULL,
    documents JSON NULL COMMENT 'Kullanım kılavuzu, garanti, vb.',

    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (current_project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (current_structure_id) REFERENCES project_structures(id) ON DELETE SET NULL,

    INDEX idx_status (status),
    INDEX idx_project (current_project_id)
);

CREATE TABLE equipment_usage_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    equipment_id BIGINT NOT NULL,
    project_id BIGINT NOT NULL,
    structure_id BIGINT NULL,

    assigned_to BIGINT NULL,
    assigned_by BIGINT NOT NULL,

    start_date DATE NOT NULL,
    end_date DATE NULL,

    purpose TEXT NULL,
    usage_notes TEXT NULL,

    hours_used DECIMAL(8,2) NULL,
    fuel_consumption DECIMAL(10,2) NULL,

    status ENUM('active', 'returned', 'broken', 'lost') DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (structure_id) REFERENCES project_structures(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT,

    INDEX idx_equipment (equipment_id, status),
    INDEX idx_dates (start_date, end_date)
);
```

### Özellikler
- ✅ Ekipman envanteri
- ✅ Sahiplik durumu (kendi/kiralık)
- ✅ Kullanım logları
- ✅ Bakım takibi
- ✅ Maliyet hesaplama
- ✅ Konum takibi

---

## 📦 6. MALZEME TALEP SİSTEMİ

### Amaç
Şantiyeden malzeme ihtiyaçlarını talep etmek ve satın alma sürecini takip etmek.

### Veri Modeli

```sql
CREATE TABLE material_requests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    request_code VARCHAR(50) UNIQUE NOT NULL,
    project_id BIGINT NOT NULL,
    structure_id BIGINT NULL,
    floor_id BIGINT NULL,

    -- Talep Eden
    requested_by BIGINT NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- İhtiyaç Tarihi
    needed_by_date DATE NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',

    -- Durum
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'ordered', 'partially_delivered', 'delivered', 'cancelled') DEFAULT 'draft',

    -- Onay
    approved_by BIGINT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,

    -- Açıklama
    purpose TEXT NOT NULL,
    notes TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (structure_id) REFERENCES project_structures(id) ON DELETE SET NULL,
    FOREIGN KEY (floor_id) REFERENCES project_floors(id) ON DELETE SET NULL,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_project_status (project_id, status),
    INDEX idx_needed_date (needed_by_date)
);

CREATE TABLE material_request_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    request_id BIGINT NOT NULL,
    material_id BIGINT NULL,

    material_name VARCHAR(255) NOT NULL,
    specification TEXT NULL,
    unit VARCHAR(50) NOT NULL,

    requested_quantity DECIMAL(12,2) NOT NULL,
    approved_quantity DECIMAL(12,2) NULL,
    delivered_quantity DECIMAL(12,2) DEFAULT 0,

    estimated_unit_price DECIMAL(12,2) NULL,
    actual_unit_price DECIMAL(12,2) NULL,

    notes TEXT NULL,

    FOREIGN KEY (request_id) REFERENCES material_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE SET NULL
);
```

### Özellikler
- ✅ Malzeme talep formu
- ✅ Onay süreci
- ✅ İhtiyaç tarihi takibi
- ✅ Miktar onaylama
- ✅ Teslimat takibi
- ✅ Maliyet karşılaştırma

---

## 🎯 FAZ 2 UYGULAMA SIRASI

### 1. Adım: Günlük Raporlar ⏳
- [ ] Migration oluştur
- [ ] Model oluştur
- [ ] API Controller
- [ ] Routes
- [ ] Seeder (demo data)

### 2. Adım: Hasar/Eksiklik Takibi ⏳
- [ ] Migration oluştur
- [ ] Model oluştur
- [ ] API Controller
- [ ] Routes

### 3. Adım: Denetim Kayıtları ⏳
- [ ] Migration oluştur
- [ ] Model oluştur
- [ ] API Controller
- [ ] Routes

### 4. Adım: Fotoğraf Galerisi ⏳
- [ ] Migration oluştur
- [ ] Model oluştur
- [ ] File upload sistemi
- [ ] API Controller
- [ ] Timelapse generator

### 5. Adım: Ekipman Takibi ⏳
- [ ] Migration oluştur
- [ ] Model oluştur
- [ ] API Controller
- [ ] Routes
- [ ] Bakım hatırlatma sistemi

### 6. Adım: Malzeme Talep ⏳
- [ ] Migration oluştur
- [ ] Model oluştur
- [ ] API Controller
- [ ] Routes
- [ ] Onay workflow

---

## 📱 FLUTTER MOBİL UYGULAMA

### Planlanan Özellikler
- [ ] QR kod ile puantaj girişi
- [ ] Günlük rapor girişi (offline)
- [ ] Fotoğraf çekme ve upload
- [ ] Hasar/eksiklik bildirimi
- [ ] Push notification
- [ ] Offline çalışma (sync)

### Teknik Stack
- **Framework:** Flutter
- **State Management:** Riverpod / Bloc
- **API:** Laravel Sanctum (Token-based auth)
- **Local DB:** SQLite / Hive
- **Storage:** Shared Preferences
- **Camera:** camera plugin
- **QR:** qr_code_scanner plugin

---

**🎯 Sonraki Adım:** Faz 2 modüllerine başlamak için onayınızı bekliyoruz!

**Son Güncelleme:** 2025-10-20
**Durum:** 📋 Planlama
**Öncelik:** Günlük Raporlar → Hasar Takibi → Diğerleri

---

## ⚠️ MEVCUT SORUNLAR VE DÜZELTİLMESİ GEREKENLER

### 1. Puantaj Kaydetme Sorunu (ÖNCELİKLİ) 🔴

**Sorun:** Puantaj modülünde kaydetme işlemi çalışmıyor.

**Detaylar:**
- Kullanıcılar puantaj girmeye çalıştığında hata alıyor
- Form validation veya database insert sırasında problem oluşuyor
- Mevcut puantaj kayıtları görüntülenemiyor veya eksik görünüyor

**Yapılması Gerekenler:**
- [ ] TimesheetController'da `store()` metodunu kontrol et
- [ ] Validation kurallarını gözden geçir
- [ ] Database constraint'lerini kontrol et
- [ ] Error log'larını incele
- [ ] Frontend form validation kontrolü
- [ ] API response formatını kontrol et

**İlgili Dosyalar:**
- `app/Http/Controllers/TimesheetController.php`
- `resources/js/Pages/Timesheets/Create.vue`
- `database/migrations/*_create_timesheets_table.php`

**Test Adımları:**
1. Puantaj oluşturma formunu aç
2. Tüm gerekli alanları doldur
3. Kaydet butonuna tıkla
4. Console ve network tab'ı kontrol et
5. Laravel log dosyasını kontrol et (`storage/logs/laravel.log`)

---

### 2. Timesheet Modeline Yeni Alanlar Eklendi

**Durum:** ✅ Migration tamamlandı, Model güncellendi

Faz 1'de `timesheets` tablosuna şu alanlar eklendi:
- `structure_id` - Hangi blokta çalıştı
- `floor_id` - Hangi katta çalıştı  
- `unit_id` - Hangi dairede çalıştı
- `work_item_id` - Hangi işi yaptı
- `assignment_id` - Hangi iş atamasına bağlı

**Yapılması Gerekenler:**
- [ ] Puantaj formuna bu alanları ekle (opsiyonel)
- [ ] Dropdown'lar ekle (Blok → Kat → Daire zincirleme)
- [ ] İş kalemi seçimi ekle
- [ ] Mevcut puantajları gösterimde bu bilgileri göster
- [ ] Raporlarda konum ve iş bazlı filtreleme

**Frontend Tasarım:**
```
📋 Puantaj Formu (Yeni Hali)
┌─────────────────────────────────────┐
│ Çalışan: [Dropdown]                 │
│ Tarih: [Date Picker]                │
│ ─────────────────────────────       │
│ 📍 Konum (Opsiyonel):               │
│   Blok: [A Blok ▼]                  │
│   Kat: [5. Kat ▼]                   │
│   Daire: [Daire 12 ▼]               │
│ ─────────────────────────────       │
│ 🔨 İş (Opsiyonel):                  │
│   İş Kalemi: [Elektrik ▼]           │
│ ─────────────────────────────       │
│ Saat: [09:00] - [18:00]             │
│ Mola: [1 saat]                      │
│ ─────────────────────────────       │
│ [İptal]  [Kaydet]                   │
└─────────────────────────────────────┘
```

---

### 3. Employee-Project Assignment İyileştirmesi

**Durum:** ✅ Çözüldü - AssignEmployeesToProjectSeeder ile

**Problem:** Çalışanlar projelere atanmamıştı, bu yüzden puantaj ve raporlarda görünmüyordu.

**Çözüm:** 
```bash
php artisan db:seed --class=AssignEmployeesToProjectSeeder
```

Bu seeder:
- Tüm çalışanları demo projesine atar
- Kategorilerine göre rol belirler
- `current_project_id` alanını günceller
- `employee_project` pivot tablosunu doldurur

---

## 🎯 FAZ 2 ÖNCELİK SIRASI (GÜNCELLENDİ)

### ⚠️ Acil (Hemen Yapılmalı)
1. **Puantaj Kaydetme Sorununu Çöz** 🔴
   - Debug ve fix
   - Test et
   - Yeni alanları forma ekle

### 🚀 Yüksek Öncelik
2. **Günlük Raporlar Modülü**
   - Şantiye günlük özeti
   - Hava durumu kaydı
   - İşçi sayıları

3. **Hasar/Eksiklik Takibi**
   - Defect kayıt sistemi
   - Fotoğraf ekleme
   - Çözüm takibi

### 📋 Orta Öncelik
4. **Fotoğraf Galerisi**
   - Upload sistemi
   - Kategorizasyon
   - Timelapse

5. **Denetim Kayıtları**
   - İSG denetimleri
   - Kalite kontrol

### 📦 Düşük Öncelik
6. **Ekipman Takibi**
7. **Malzeme Talep Sistemi**

---

## 🐛 HATA AYIKLAMA KILAVUZU

### Puantaj Sorunlarını Giderme

#### 1. Backend Kontrolü
```bash
# Laravel log'ları kontrol et
tail -f storage/logs/laravel.log

# Database'i kontrol et
php artisan tinker
>>> \App\Models\Timesheet::count()
>>> \App\Models\Timesheet::latest()->first()
```

#### 2. Frontend Kontrolü
```javascript
// Browser Console'da
// Network tab'ı aç
// Form submit ederken göz at
// Response'u kontrol et
```

#### 3. Database Migration Kontrolü
```bash
# Migration durumunu kontrol et
php artisan migrate:status

# Eğer eksik migration varsa
php artisan migrate

# Rollback ve tekrar migrate (DİKKATLİ!)
php artisan migrate:fresh --seed
```

#### 4. Common Errors

**Error: "Column not found"**
```
Çözüm: Migration eksik, tekrar migrate et
php artisan migrate
```

**Error: "Foreign key constraint"**
```
Çözüm: İlişkili kayıt yok (employee, project vb.)
Seed'leri çalıştır
```

**Error: "SQLSTATE[23000]: Integrity constraint violation"**
```
Çözüm: Unique constraint ihlali veya NULL olamaz
Validation kurallarını kontrol et
```

---

**Son Güncelleme:** 2025-10-20 (Puantaj sorunu eklendi)
**Durum:** 📋 Planlama + Sorun Tespiti
**Öncelik:** 🔴 Puantaj Fix → Günlük Raporlar → Hasar Takibi
