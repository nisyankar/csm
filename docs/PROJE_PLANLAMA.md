# 🏗️ SPT İNŞAAT PUANTAJ VE PROJE YÖNETİM SİSTEMİ
## Kapsamlı Modül Planlama ve Roadmap

---

## 📊 MEVCUT SİSTEM ANALİZİ

### ✅ TAMAMLANMIŞ MODÜLLER

#### 1. **👥 ÇALIŞAN YÖNETİMİ** - %100 Tamamlandı
**Durum:** ✅ Tam Fonksiyonel + Taşeron Entegrasyonu 🆕

**Özellikler:**
- ✅ Çalışan temel bilgileri (TC, ad, soyad, doğum tarihi)
- ✅ İletişim bilgileri (telefon, email, adres)
- ✅ Pozisyon ve kategori yönetimi
- ✅ Maaş bilgileri (günlük, saatlik, aylık)
- ✅ Departman ataması
- ✅ Proje ataması
- ✅ Yönetici ilişkileri
- ✅ Durum yönetimi (aktif, pasif, askıda)
- ✅ QR kod entegrasyonu
- ✅ Fotoğraf yönetimi
- ✅ **Taşeron çalışanı işaretleme** 🆕
- ✅ **Taşeron ilişkilendirme** 🆕
- ✅ **Taşeron sözleşme tarihleri** 🆕
- ✅ Soft delete

**Veritabanı:**
- ✅ `employees`
  - ✅ `is_subcontractor_employee` (boolean) 🆕
  - ✅ `subcontractor_id` (foreign key) 🆕
  - ✅ `subcontractor_contract_start` (date) 🆕
  - ✅ `subcontractor_contract_end` (date) 🆕
- ✅ `employee_salary_history`
- ✅ `employee_project_assignments`

**Sayfalar:**
- ✅ Çalışan Listesi (Index)
- ✅ Çalışan Ekleme (Create)
- ✅ Çalışan Düzenleme (Edit)
- ✅ Çalışan Detay (Show)

---

#### 2. **⏰ PUANTAJ YÖNETİMİ** - %100 Tamamlandı
**Durum:** ✅ Tam Fonksiyonel

**Özellikler:**
- ✅ Günlük puantaj girişi
- ✅ Çalışma saatleri (başlangıç, bitiş, ara)
- ✅ Mesai türleri (normal, fazla mesai, gece, hafta sonu)
- ✅ İzin türleri entegrasyonu
- ✅ Proje bazlı çalışma kaydı
- ✅ Toplu puantaj girişi (BulkEntry)
- ✅ Onay süreci (pending, approved, rejected)
- ✅ Revizyon yönetimi
- ✅ Maaş hesaplama entegrasyonu
- ✅ Aylık ve haftalık raporlar
- ✅ Excel export

**Veritabanı:**
- ✅ `timesheets`
- ✅ `timesheet_approvals`
- ✅ `timesheet_revisions`

**Sayfalar:**
- ✅ Puantaj Listesi (Index)
- ✅ Toplu Puantaj Girişi (BulkEntry)
- ✅ Puantaj Onay Ekranı

---

#### 3. **🏖️ İZİN YÖNETİMİ** - %100 Tamamlandı
**Durum:** ✅ Tam Fonksiyonel

**Özellikler:**
- ✅ İzin türleri (yıllık, mazeret, hastalık, ücretsiz)
- ✅ İzin bakiye hesaplama
- ✅ İzin talebi oluşturma
- ✅ Onay süreci (yönetici onayı)
- ✅ İzin geçmişi
- ✅ Devredilen izinler
- ✅ Otomatik izin hesaplama (kıdem bazlı)
- ✅ İzin parametreleri (yıllık hakediş, mevzuat kuralları)
- ✅ SGK bildirimi entegrasyonu hazırlığı

**Veritabanı:**
- ✅ `leave_types`
- ✅ `leave_requests`
- ✅ `leave_parameters`
- ✅ `leave_calculations`
- ✅ `leave_balance_logs`

**Sayfalar:**
- ✅ İzin Talep Listesi
- ✅ İzin Talep Formu
- ✅ İzin Onay Ekranı
- ✅ İzin Bakiye Görüntüleme

---

#### 4. **🛒 SATIN ALMA YÖNETİMİ** - %100 Tamamlandı
**Durum:** ✅ Tam Fonksiyonel

**Özellikler:**
- ✅ Satın alma talebi (Purchasing Request)
- ✅ Malzeme/Hizmet tanımlama
- ✅ Talep kalemleri (çoklu satır)
- ✅ Tedarikçi yönetimi
- ✅ Tedarikçi teklif alma
- ✅ Satın alma siparişi (Purchase Order)
- ✅ Onay süreci (pending, approved, rejected)
- ✅ Teslimat takibi
- ✅ Malzeme envanteri
- ✅ Proje bazlı malzeme takibi
- ✅ Tedarikçi performans değerlendirmesi altyapısı

**Veritabanı:**
- ✅ `materials`
- ✅ `purchasing_requests`
- ✅ `purchasing_items`
- ✅ `suppliers`
- ✅ `supplier_quotations`
- ✅ `purchase_orders`
- ✅ `deliveries`

**Sayfalar:**
- ✅ Satın Alma Talep Listesi (Index)
- ✅ Yeni Talep Oluşturma (Create)
- ✅ Talep Düzenleme (Edit)
- ✅ Talep Detay (Show)
- ✅ Sipariş Yönetimi

---

#### 5. **📁 DEPARTMAN YÖNETİMİ** - %100 Tamamlandı
**Durum:** ✅ Tam Fonksiyonel

**Özellikler:**
- ✅ Departman oluşturma
- ✅ Departman yöneticisi atama
- ✅ Çalışan departman ataması
- ✅ Bütçe yönetimi

**Veritabanı:**
- ✅ `departments`

---

#### 6. **🏗️ PROJE YÖNETİMİ (TEMEL)** - %85 Tamamlandı
**Durum:** ✅ Temel Özellikler Hazır + Taşeron İlişkisi Eklendi

**Özellikler:**
- ✅ Proje temel bilgileri (ad, kod, açıklama)
- ✅ Lokasyon ve GPS koordinatları
- ✅ Tarih yönetimi (başlangıç, planlanan/gerçek bitiş)
- ✅ Bütçe ve harcama takibi
- ✅ Proje ve şantiye müdürü atama
- ✅ Durum yönetimi (planlama, aktif, beklemede, tamamlandı, iptal)
- ✅ Proje türü (konut, ticari, altyapı, endüstriyel)
- ✅ Öncelik yönetimi
- ✅ Müşteri bilgileri
- ✅ Tahmini personel sayısı
- ✅ Çalışan proje ataması (many-to-many)
- ✅ **Taşeron proje ataması (many-to-many)** 🆕
- ✅ Hesaplanan alanlar (bütçe kullanım %, kalan bütçe, ilerleme %)
- ⏳ Gelişmiş proje takibi (hakediş, ilerleme)

**Veritabanı:**
- ✅ `projects`
- ✅ `employee_project_assignments`
- ✅ `project_subcontractor` (pivot table) 🆕

**Sayfalar:**
- ✅ Proje Listesi (Index)
- ✅ Yeni Proje (Create)
- ✅ Proje Düzenleme (Edit)
- ✅ Proje Detay (Show)
- ⏳ Proje Detay - Taşeron Sekmesi (planlanan)

---

#### 7. **👤 KULLANICI VE YETKİ YÖNETİMİ** - %100 Tamamlandı
**Durum:** ✅ Tam Fonksiyonel

**Özellikler:**
- ✅ Kullanıcı kaydı
- ✅ Rol bazlı yetkilendirme (Spatie Permission)
- ✅ Admin, HR, Proje Müdürü, Şantiye Şefi rolleri
- ✅ Oturum yönetimi
- ✅ Şifre sıfırlama

**Veritabanı:**
- ✅ `users`
- ✅ `roles` (Spatie)
- ✅ `permissions` (Spatie)

---

### ⚠️ DEVAM EDEN / PLANLANAN MODÜLLER

#### 8. **📄 BELGE/DÖKÜMAN YÖNETİMİ** - %20 Tamamlandı
**Durum:** ⏳ Temel Yapı Hazır, Özellikler Eksik

**Mevcut:**
- ✅ Document modeli var
- ✅ Veritabanı tablosu hazır (`documents`)

**Eksik Özellikler:**
- ❌ Dosya yükleme ve depolama
- ❌ Döküman kategorileri
- ❌ Versiyon kontrolü
- ❌ Erişim yönetimi
- ❌ Onay süreçleri
- ❌ Arama ve filtreleme
- ❌ Preview özelliği
- ❌ İlişkili dökümanlar (proje, çalışan, vb.)

**İhtiyaçlar:**
```php
// Eklenecek özellikler
- document_categories (kategoriler)
- document_versions (versiyon kontrolü)
- document_permissions (erişim kontrolü)
- document_approvals (onaylar)
- document_attachments (ek dosyalar)
```

---

## 🎯 GELİŞTİRİLECEK YENİ MODÜLLER

### **FAZ 1 - TAŞERON VE SÖZLEŞME YÖNETİMİ** (Öncelik: YÜKSEK)

#### 9. **🏢 TAŞERON/ALT YÜKLENİCİ YÖNETİMİ** - %80 Tamamlandı 🆕
**Durum:** ✅ Temel Modül Hazır, İleri Özellikler Bekliyor

**✅ Tamamlanan Özellikler:**
- ✅ Taşeron firma bilgileri
  - ✅ Firma adı, ticari ünvan
  - ✅ Vergi dairesi ve numarası
  - ✅ Adres ve iletişim bilgileri (şehir, ilçe, posta kodu)
  - ✅ Yetkili kişi bilgileri (ad, telefon, email, ünvan)
  - ✅ Banka hesap bilgileri (banka, şube, IBAN, hesap no)
- ✅ Taşeron kategorileri (25 adet hazır kategori)
  - ✅ Betonarme İşleri
  - ✅ Elektrik İşleri
  - ✅ Mekanik Tesisat
  - ✅ Alçı ve Sıva İşleri
  - ✅ Boya, Seramik, Doğrama, Demir, Ahşap, Çatı
  - ✅ İzolasyon, Hafriyat, Yol, Asansör, Peyzaj
  - ✅ Cam, Yangın, Güvenlik, Asma Tavan, Merdiven
  - ✅ Mermer, Havalandırma, Temizlik, Nakliye, Diğer
- ✅ **Belge ve Sertifika Yönetimi**
  - ✅ Kapasite raporu
  - ✅ Deneyim belgesi
  - ✅ ISO sertifikaları (9001, 14001, 45001)
  - ✅ SGK/Vergi borcu yok belgeleri
  - ✅ Ticaret sicil, imza sirküleri
  - ✅ Belge geçerlilik takibi (süresi dolacaklar)
  - ✅ Dosya yükleme ve indirme
- ✅ **Performans Değerlendirmesi**
  - ✅ 5 yıldız puan sistemi
  - ✅ Kalite, Zaman, Güvenlik, İletişim, Maliyet puanları
  - ✅ Proje bazlı değerlendirme
  - ✅ Güçlü/Zayıf yönler, Öneriler
  - ✅ "Tekrar işe alınır mı?" işaretleme
  - ✅ Otomatik ortalama puan hesaplama
- ✅ Durum yönetimi
  - ✅ Aktif/Pasif/Kara liste
  - ✅ Onaylı/Onay bekleyen
- ✅ **Proje İlişkilendirmesi**
  - ✅ Bir projede birden fazla taşeron atanabilir
  - ✅ Her atama için iş türü tanımı (Elektrik Tesisatı vs.)
  - ✅ İş kapsamı detayı
  - ✅ Sözleşme tutarı
  - ✅ Başlangıç/Bitiş tarihleri
  - ✅ Atama durumu (aktif, tamamlandı, feshedildi, askıya alındı)
- ✅ **Çalışan İlişkilendirmesi**
  - ✅ Employee modelinde taşeron işaretleme
  - ✅ Hangi taşeronun elemanı olduğu
  - ✅ Taşeron sözleşme başlangıç/bitiş tarihleri

**⏳ Gelecek Özellikler:**
- ⏳ İSG belgeleri ve kaza kayıtları
- ⏳ Çalışan sayısı takibi
- ⏳ Belge hatırlatıcıları (email/bildirim)

**Veritabanı Tabloları:**
```sql
CREATE TABLE subcontractors (
    id BIGINT PRIMARY KEY,
    company_name VARCHAR(255),
    tax_office VARCHAR(100),
    tax_number VARCHAR(20),
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    authorized_person VARCHAR(100),
    authorized_phone VARCHAR(20),
    bank_name VARCHAR(100),
    iban VARCHAR(34),
    category_id BIGINT,
    rating DECIMAL(3,2), -- 0.00 - 5.00
    status ENUM('active', 'inactive', 'blacklisted'),
    is_approved BOOLEAN,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE subcontractor_categories (
    id BIGINT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE subcontractor_certifications (
    id BIGINT PRIMARY KEY,
    subcontractor_id BIGINT,
    certificate_type VARCHAR(100), -- 'kapasite', 'iso', 'sgk', 'vergi'
    certificate_number VARCHAR(50),
    issue_date DATE,
    expiry_date DATE,
    document_path VARCHAR(255),
    status ENUM('valid', 'expired', 'pending'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE subcontractor_ratings (
    id BIGINT PRIMARY KEY,
    subcontractor_id BIGINT,
    project_id BIGINT,
    quality_score DECIMAL(3,2),
    timeline_score DECIMAL(3,2),
    safety_score DECIMAL(3,2),
    overall_score DECIMAL(3,2),
    comments TEXT,
    rated_by BIGINT, -- user_id
    rated_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Sayfalar:**
- [ ] Taşeron Listesi
- [ ] Yeni Taşeron Ekleme
- [ ] Taşeron Düzenleme
- [ ] Taşeron Detay ve Geçmiş
- [ ] Taşeron Performans Raporu

---

#### 10. **📝 SÖZLEŞME YÖNETİMİ** - %0
**Durum:** 🔴 Başlanmadı

**Gerekli Özellikler:**

**A. Ana Sözleşmeler (Müşteri)**
- Sözleşme temel bilgileri
- Sözleşme türü (götürü bedel, birim fiyat, karma)
- Sözleşme bedeli
- İş programı
- Ödeme şartları
- Teminatlar (kesin teminat, geçici teminat)
- Cayma bedeli

**B. Alt Sözleşmeler (Taşeron)**
- Taşeron seçimi
- İş tanımı ve kapsamı
- Birim fiyatlar
- Toplam tutar
- Ödeme planı
- Kesintiler (SGK, stopaj, KDV)
- Garanti süreleri

**C. Sözleşme Kalemleri (Poz Listesi)**
- Poz numarası
- İş tanımı
- Birim
- Miktar
- Birim fiyat
- Toplam tutar
- İş grubu/kategorisi

**D. Sözleşme Ekleri ve Revizyonları**
- Ek sözleşme no
- Tarih
- Sebep
- Değişiklik tutarı
- Onay durumu
- Dökümanlar

**Veritabanı Tabloları:**
```sql
CREATE TABLE contracts (
    id BIGINT PRIMARY KEY,
    contract_number VARCHAR(50) UNIQUE,
    contract_type ENUM('main', 'subcontractor'),
    project_id BIGINT,
    party_type ENUM('client', 'subcontractor'),
    party_id BIGINT, -- client_id veya subcontractor_id
    contract_method ENUM('lump_sum', 'unit_price', 'mixed'),
    contract_amount DECIMAL(15,2),
    vat_rate DECIMAL(5,2),
    total_amount DECIMAL(15,2),
    start_date DATE,
    planned_end_date DATE,
    actual_end_date DATE,
    payment_terms TEXT,
    guarantee_type VARCHAR(100),
    guarantee_amount DECIMAL(15,2),
    guarantee_expiry_date DATE,
    status ENUM('draft', 'active', 'completed', 'terminated'),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE contract_items (
    id BIGINT PRIMARY KEY,
    contract_id BIGINT,
    item_code VARCHAR(50),
    description TEXT,
    unit VARCHAR(20), -- m3, m2, adet, kg
    quantity DECIMAL(15,3),
    unit_price DECIMAL(15,2),
    total_price DECIMAL(15,2),
    category VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE contract_amendments (
    id BIGINT PRIMARY KEY,
    contract_id BIGINT,
    amendment_number VARCHAR(50),
    amendment_date DATE,
    reason TEXT,
    amount_change DECIMAL(15,2),
    time_extension_days INT,
    status ENUM('pending', 'approved', 'rejected'),
    approved_by BIGINT,
    approved_at TIMESTAMP,
    document_path VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE contract_guarantees (
    id BIGINT PRIMARY KEY,
    contract_id BIGINT,
    guarantee_type ENUM('temporary', 'definitive', 'advance_payment'),
    guarantee_method ENUM('bank_letter', 'cash', 'insurance'),
    amount DECIMAL(15,2),
    bank_name VARCHAR(100),
    guarantee_number VARCHAR(50),
    start_date DATE,
    expiry_date DATE,
    status ENUM('active', 'returned', 'expired', 'cashed'),
    document_path VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Sayfalar:**
- [ ] Sözleşme Listesi
- [ ] Yeni Sözleşme
- [ ] Sözleşme Detay
- [ ] Poz Listesi Yönetimi
- [ ] Sözleşme Ekleri
- [ ] Teminat Takibi

---

#### 11. **💰 HAKEDİŞ SİSTEMİ** - %0
**Durum:** 🔴 Başlanmadı

**Gerekli Özellikler:**

**A. Müşteri Hakedişleri (GELİR)**
- Hakediş numarası ve dönemi
- Geçici/Kesin hakediş
- Metraj tutanakları
  - Sözleşme kalemleri bazında
  - Önceki hakediş miktarı
  - Bu hakediş miktarı
  - Kümülatif miktar
- Hakediş tutarı hesaplama
  - Brüt tutar
  - KDV
  - Stopaj kesintisi
  - SGK prim kesintisi
  - Damga vergisi
  - Net tutar
- Hakediş belgesi
- Fatura bilgileri
- Ödeme durumu
- Geciken ödemeler

**B. Taşeron Hakedişleri (GİDER)**
- Taşeron metraj tutanakları
- Hakediş hesaplama
- Kesinti yönetimi
  - Stopaj
  - SGK işçi + işveren payı
  - KDV
  - Cezalar/Primler
- Ödeme planı
- Ödeme onayları
- Ödeme yapılma durumu

**C. Raporlama**
- Hakediş karşılaştırma (gelir vs gider)
- Nakit akış tahmini
- Kar/Zarar analizi
- Gecikmiş ödemeler raporu

**Veritabanı Tabloları:**
```sql
CREATE TABLE progress_payments (
    id BIGINT PRIMARY KEY,
    payment_number VARCHAR(50),
    payment_type ENUM('client', 'subcontractor'),
    contract_id BIGINT,
    project_id BIGINT,
    period_start DATE,
    period_end DATE,
    is_final BOOLEAN DEFAULT FALSE,
    gross_amount DECIMAL(15,2),
    vat_amount DECIMAL(15,2),
    withholding_tax DECIMAL(15,2),
    sgk_deduction DECIMAL(15,2),
    stamp_tax DECIMAL(15,2),
    other_deductions DECIMAL(15,2),
    net_amount DECIMAL(15,2),
    cumulative_amount DECIMAL(15,2),
    payment_percentage DECIMAL(5,2),
    status ENUM('draft', 'pending', 'approved', 'paid'),
    invoice_number VARCHAR(50),
    invoice_date DATE,
    payment_date DATE,
    due_date DATE,
    approved_by BIGINT,
    approved_at TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE progress_payment_items (
    id BIGINT PRIMARY KEY,
    progress_payment_id BIGINT,
    contract_item_id BIGINT,
    item_code VARCHAR(50),
    description TEXT,
    unit VARCHAR(20),
    unit_price DECIMAL(15,2),
    contract_quantity DECIMAL(15,3),
    previous_quantity DECIMAL(15,3),
    current_quantity DECIMAL(15,3),
    cumulative_quantity DECIMAL(15,3),
    current_amount DECIMAL(15,2),
    cumulative_amount DECIMAL(15,2),
    completion_percentage DECIMAL(5,2),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE progress_payment_deductions (
    id BIGINT PRIMARY KEY,
    progress_payment_id BIGINT,
    deduction_type VARCHAR(50), -- 'stopaj', 'sgk_isci', 'sgk_isveren', 'ceza', 'prim'
    description TEXT,
    rate DECIMAL(5,2),
    amount DECIMAL(15,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE payment_transactions (
    id BIGINT PRIMARY KEY,
    progress_payment_id BIGINT,
    transaction_type ENUM('payment_in', 'payment_out'),
    amount DECIMAL(15,2),
    payment_method ENUM('bank_transfer', 'check', 'cash', 'credit_card'),
    bank_name VARCHAR(100),
    reference_number VARCHAR(100),
    transaction_date DATE,
    notes TEXT,
    created_by BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Sayfalar:**
- [ ] Hakediş Listesi
- [ ] Yeni Hakediş (Müşteri)
- [ ] Yeni Hakediş (Taşeron)
- [ ] Hakediş Detay ve Onay
- [ ] Metraj Tutanağı
- [ ] Ödeme Takibi
- [ ] Hakediş Karşılaştırma Raporu

---

### **FAZ 2 - FİZİKİ İLERLEME VE RAPORLAMA** (Öncelik: ORTA)

#### 12. **📈 FİZİKİ VE MALİ İLERLEME TAKİBİ** - %0
**Durum:** 🔴 Başlanmadı

**Gerekli Özellikler:**
- Proje programı (Gantt Chart)
- İş kalemleri ve bağımlılıklar
- Kritik yol (CPM)
- Planlanan vs gerçekleşen ilerleme
- S-Eğrisi
- Earned Value Management (EVM)
  - BCWS (Planned Value)
  - BCWP (Earned Value)
  - ACWP (Actual Cost)
  - CPI (Cost Performance Index)
  - SPI (Schedule Performance Index)
- Kilometre taşları
- İlerleme raporları
- Gecikmeler ve sebepleri

**Veritabanı Tabloları:**
```sql
CREATE TABLE project_schedule (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    task_name VARCHAR(255),
    task_code VARCHAR(50),
    description TEXT,
    planned_start_date DATE,
    planned_end_date DATE,
    actual_start_date DATE,
    actual_end_date DATE,
    duration_days INT,
    parent_task_id BIGINT,
    predecessor_tasks TEXT, -- JSON array
    progress_percentage DECIMAL(5,2),
    is_milestone BOOLEAN,
    is_critical_path BOOLEAN,
    status ENUM('not_started', 'in_progress', 'completed', 'delayed'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE work_items (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    contract_item_id BIGINT,
    schedule_task_id BIGINT,
    item_code VARCHAR(50),
    description TEXT,
    unit VARCHAR(20),
    total_quantity DECIMAL(15,3),
    completed_quantity DECIMAL(15,3),
    remaining_quantity DECIMAL(15,3),
    planned_value DECIMAL(15,2), -- BCWS
    earned_value DECIMAL(15,2), -- BCWP
    actual_cost DECIMAL(15,2), -- ACWP
    completion_percentage DECIMAL(5,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE progress_reports (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    report_date DATE,
    reporting_period ENUM('daily', 'weekly', 'monthly'),
    physical_progress DECIMAL(5,2),
    financial_progress DECIMAL(5,2),
    cpi DECIMAL(5,2), -- Cost Performance Index
    spi DECIMAL(5,2), -- Schedule Performance Index
    eac DECIMAL(15,2), -- Estimate at Completion
    etc DECIMAL(15,2), -- Estimate to Complete
    variance_schedule DECIMAL(15,2), -- SV = BCWP - BCWS
    variance_cost DECIMAL(15,2), -- CV = BCWP - ACWP
    weather_condition VARCHAR(100),
    manpower_count INT,
    equipment_count INT,
    issues TEXT,
    achievements TEXT,
    next_period_plan TEXT,
    photos TEXT, -- JSON array of photo paths
    reported_by BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE milestone_tracking (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    schedule_task_id BIGINT,
    milestone_name VARCHAR(255),
    planned_date DATE,
    actual_date DATE,
    status ENUM('upcoming', 'achieved', 'missed'),
    delay_days INT,
    delay_reason TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Sayfalar:**
- [ ] Proje Programı (Gantt)
- [ ] İş Kalemleri Listesi
- [ ] Günlük İlerleme Girişi
- [ ] İlerleme Raporu
- [ ] S-Eğrisi Grafiği
- [ ] EVM Dashboard
- [ ] Kilometre Taşları

---

### **FAZ 3 - DESTEK MODÜLLER** (Öncelik: ORTA)

#### 13. **📦 MALZEME VE EKİPMAN YÖNETİMİ (GELİŞMİŞ)** - %30
**Durum:** 🟡 Satın Alma Modülü Hazır, Geliştirilecek

**Mevcut:**
- ✅ Malzeme tanımlama (`materials`)
- ✅ Tedarikçi yönetimi (`suppliers`)
- ✅ Teslimat takibi (`deliveries`)

**Eklenecek Özellikler:**
- Proje malzeme ihtiyaç listesi
- Şantiye deposu stok yönetimi
- Malzeme sarfiyat takibi
- Fire ve ıskarta yönetimi
- Ekipman kiralama/satın alma
- Ekipman bakım planı
- Yakıt tüketimi
- Operatör ataması
- Maliyet analizi

**Yeni Tablolar:**
```sql
CREATE TABLE project_materials (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    material_id BIGINT,
    required_quantity DECIMAL(15,3),
    unit VARCHAR(20),
    planned_delivery_date DATE,
    status ENUM('planned', 'ordered', 'delivered', 'used'),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE material_usage (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    material_id BIGINT,
    usage_date DATE,
    quantity DECIMAL(15,3),
    unit VARCHAR(20),
    work_item_id BIGINT,
    wastage_quantity DECIMAL(15,3),
    wastage_reason TEXT,
    used_by BIGINT, -- employee_id
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE project_equipment (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    equipment_name VARCHAR(255),
    equipment_type VARCHAR(100), -- 'excavator', 'crane', 'mixer', 'pump'
    ownership_type ENUM('owned', 'rented'),
    rental_company VARCHAR(255),
    rental_start_date DATE,
    rental_end_date DATE,
    daily_rental_cost DECIMAL(15,2),
    status ENUM('active', 'maintenance', 'idle', 'returned'),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE equipment_maintenance (
    id BIGINT PRIMARY KEY,
    equipment_id BIGINT,
    maintenance_type ENUM('routine', 'repair', 'breakdown'),
    maintenance_date DATE,
    description TEXT,
    cost DECIMAL(15,2),
    downtime_hours INT,
    performed_by VARCHAR(255),
    next_maintenance_date DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

#### 14. **📁 DÖKÜMAN YÖNETİMİ (GELİŞTİRME)** - %20
**Durum:** 🟡 Temel Yapı Var, Geliştirilecek

**Mevcut:**
- ✅ Document modeli
- ✅ Temel tablo yapısı

**Eklenecek Özellikler:**
- Döküman kategorileri
  - Projeler (çizimler, ruhsatlar, tutanaklar)
  - Çalışanlar (sözleşmeler, sertifikalar, kimlik)
  - Tedarikçi/Taşeron (sözleşmeler, belgeler)
  - Muhasebe (faturalar, makbuzlar)
- Dosya yükleme (PDF, Word, Excel, resim, CAD)
- Versiyon kontrolü
- Döküman onay süreci
- Erişim kontrolü (rol bazlı)
- OCR (metin tanıma)
- Tam metin arama
- Toplu indirme
- QR kod ile erişim

**Yeni/Güncellenecek Tablolar:**
```sql
CREATE TABLE document_categories (
    id BIGINT PRIMARY KEY,
    name VARCHAR(100),
    parent_category_id BIGINT,
    icon VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE documents (
    id BIGINT PRIMARY KEY,
    category_id BIGINT,
    related_type VARCHAR(50), -- 'project', 'employee', 'subcontractor', 'contract'
    related_id BIGINT,
    document_name VARCHAR(255),
    document_number VARCHAR(100),
    document_type VARCHAR(50), -- 'pdf', 'docx', 'xlsx', 'dwg', 'jpg'
    file_path VARCHAR(255),
    file_size BIGINT,
    version VARCHAR(20),
    issue_date DATE,
    expiry_date DATE,
    status ENUM('draft', 'active', 'expired', 'archived'),
    is_confidential BOOLEAN,
    requires_approval BOOLEAN,
    uploaded_by BIGINT,
    description TEXT,
    tags TEXT, -- JSON array
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE document_versions (
    id BIGINT PRIMARY KEY,
    document_id BIGINT,
    version VARCHAR(20),
    file_path VARCHAR(255),
    change_description TEXT,
    uploaded_by BIGINT,
    created_at TIMESTAMP
);

CREATE TABLE document_permissions (
    id BIGINT PRIMARY KEY,
    document_id BIGINT,
    user_id BIGINT,
    role_id BIGINT,
    permission_type ENUM('view', 'download', 'edit', 'delete'),
    granted_by BIGINT,
    created_at TIMESTAMP
);

CREATE TABLE document_approvals (
    id BIGINT PRIMARY KEY,
    document_id BIGINT,
    approver_id BIGINT,
    approval_level INT,
    status ENUM('pending', 'approved', 'rejected'),
    comments TEXT,
    approved_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

#### 15. **🎯 İHALE VE TEKLİF YÖNETİMİ** - %0
**Durum:** 🔴 Başlanmadı

**Gerekli Özellikler:**
- İhale ilanı oluşturma
- Teknik şartname
- İdari şartname
- İş programı
- Teklif davet listesi
- Teklif alma
- Teklif zarfı açılışı
- Teknik değerlendirme
- Ticari değerlendirme
- Fiyat karşılaştırma tablosu
- İhale sonuç raporu
- Gerekçeli ret yazıları

**Veritabanı Tabloları:**
```sql
CREATE TABLE tenders (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    tender_number VARCHAR(50),
    tender_name VARCHAR(255),
    tender_type ENUM('open', 'restricted', 'negotiated'),
    work_category VARCHAR(100),
    estimated_amount DECIMAL(15,2),
    announcement_date DATE,
    site_visit_date DATE,
    bid_submission_deadline TIMESTAMP,
    bid_opening_date TIMESTAMP,
    contract_duration_days INT,
    technical_specs TEXT,
    administrative_specs TEXT,
    status ENUM('announced', 'submission', 'evaluation', 'awarded', 'cancelled'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE tender_participants (
    id BIGINT PRIMARY KEY,
    tender_id BIGINT,
    subcontractor_id BIGINT,
    invitation_sent_date DATE,
    specifications_received BOOLEAN,
    site_visit_attended BOOLEAN,
    bid_submitted BOOLEAN,
    bid_bond_amount DECIMAL(15,2),
    status ENUM('invited', 'registered', 'submitted', 'disqualified'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE tender_bids (
    id BIGINT PRIMARY KEY,
    tender_id BIGINT,
    participant_id BIGINT,
    subcontractor_id BIGINT,
    submission_date TIMESTAMP,
    bid_amount DECIMAL(15,2),
    discount_percentage DECIMAL(5,2),
    final_amount DECIMAL(15,2),
    completion_time_days INT,
    technical_score DECIMAL(5,2),
    financial_score DECIMAL(5,2),
    total_score DECIMAL(5,2),
    is_winner BOOLEAN,
    status ENUM('submitted', 'under_review', 'accepted', 'rejected'),
    rejection_reason TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE bid_evaluations (
    id BIGINT PRIMARY KEY,
    bid_id BIGINT,
    evaluator_id BIGINT,
    evaluation_type ENUM('technical', 'financial'),
    criteria VARCHAR(100),
    score DECIMAL(5,2),
    max_score DECIMAL(5,2),
    comments TEXT,
    evaluated_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Sayfalar:**
- [ ] İhale Listesi
- [ ] Yeni İhale İlanı
- [ ] Teklif Davet
- [ ] Teklif Listesi
- [ ] Teklif Değerlendirme
- [ ] Karşılaştırma Tablosu
- [ ] İhale Sonuç Raporu

---

### **FAZ 4 - İLERİ ÖZELLİKLER** (Öncelik: DÜŞÜK)

#### 16. **⚠️ RİSK YÖNETİMİ** - %0
**Durum:** 🔴 Başlanmadı

**Gerekli Özellikler:**
- Risk tanımlama
- Risk kategorileri (maliyet, zaman, kalite, güvenlik, yasal)
- Risk değerlendirme matrisi (olasılık x etki)
- Risk puanı hesaplama
- Risk önleme ve azaltma planları
- Risk takibi
- Olay raporlama (kaza, hasar, gecikme)
- Risk kaydı (risk register)

**Veritabanı Tabloları:**
```sql
CREATE TABLE project_risks (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    risk_code VARCHAR(50),
    risk_title VARCHAR(255),
    risk_category ENUM('cost', 'schedule', 'quality', 'safety', 'legal', 'environmental'),
    risk_description TEXT,
    probability ENUM('very_low', 'low', 'medium', 'high', 'very_high'), -- 1-5
    impact ENUM('very_low', 'low', 'medium', 'high', 'very_high'), -- 1-5
    risk_score INT, -- probability * impact
    risk_level ENUM('low', 'medium', 'high', 'critical'),
    status ENUM('identified', 'assessed', 'mitigated', 'closed'),
    identified_by BIGINT,
    identified_date DATE,
    owner_id BIGINT, -- Risk sahibi
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE risk_mitigation_actions (
    id BIGINT PRIMARY KEY,
    risk_id BIGINT,
    action_type ENUM('avoid', 'reduce', 'transfer', 'accept'),
    action_description TEXT,
    responsible_person BIGINT,
    target_date DATE,
    completion_date DATE,
    status ENUM('planned', 'in_progress', 'completed'),
    effectiveness ENUM('ineffective', 'partially_effective', 'effective'),
    cost DECIMAL(15,2),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE incident_reports (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    incident_number VARCHAR(50),
    incident_type ENUM('accident', 'injury', 'damage', 'delay', 'quality_issue', 'near_miss'),
    severity ENUM('minor', 'moderate', 'serious', 'critical'),
    incident_date TIMESTAMP,
    location TEXT,
    description TEXT,
    immediate_cause TEXT,
    root_cause TEXT,
    persons_involved TEXT,
    witnesses TEXT,
    injuries TEXT,
    damages DECIMAL(15,2),
    corrective_actions TEXT,
    reported_by BIGINT,
    investigated_by BIGINT,
    status ENUM('reported', 'investigating', 'closed'),
    photos TEXT, -- JSON array
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

#### 17. **✅ KALİTE KONTROL** - %0
**Durum:** 🔴 Başlanmadı

**Gerekli Özellikler:**
- Kalite kontrol planı
- Muayene ve test planları
- Test prosedürleri
- Numune alma
- Laboratuvar testleri
- Test sonuçları
- Uygunsuzluk raporları (NCR)
- Düzeltici ve önleyici faaliyetler (CAPA)
- Malzeme onay formu
- Uygulama kontrol formları

**Veritabanı Tabloları:**
```sql
CREATE TABLE quality_inspections (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    inspection_number VARCHAR(50),
    inspection_type VARCHAR(100), -- 'beton', 'demir', 'siva', 'boyama'
    work_item_id BIGINT,
    inspection_date DATE,
    location TEXT,
    inspector_id BIGINT,
    checklist TEXT, -- JSON array
    result ENUM('passed', 'failed', 'conditional'),
    defects_found TEXT,
    photos TEXT, -- JSON array
    status ENUM('scheduled', 'completed', 'approved'),
    approved_by BIGINT,
    approved_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE test_reports (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    inspection_id BIGINT,
    test_number VARCHAR(50),
    test_type VARCHAR(100), -- 'beton_dayanim', 'demir_cekme', 'toprak_sikistirma'
    material_id BIGINT,
    sample_date DATE,
    test_date DATE,
    laboratory VARCHAR(255),
    test_standard VARCHAR(100), -- 'TS EN 206', 'TS 708'
    required_value VARCHAR(50),
    actual_value VARCHAR(50),
    result ENUM('passed', 'failed'),
    certificate_number VARCHAR(50),
    certificate_path VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE non_conformance_reports (
    id BIGINT PRIMARY KEY,
    project_id BIGINT,
    ncr_number VARCHAR(50),
    detection_date DATE,
    detected_by BIGINT,
    work_item_id BIGINT,
    location TEXT,
    description TEXT,
    category ENUM('material', 'workmanship', 'design', 'documentation'),
    severity ENUM('minor', 'major', 'critical'),
    responsible_party ENUM('contractor', 'subcontractor', 'supplier'),
    responsible_id BIGINT,
    status ENUM('open', 'investigating', 'correcting', 'verified', 'closed'),
    photos TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE corrective_actions (
    id BIGINT PRIMARY KEY,
    ncr_id BIGINT,
    action_type ENUM('corrective', 'preventive'),
    action_description TEXT,
    responsible_person BIGINT,
    target_date DATE,
    completion_date DATE,
    verification_method TEXT,
    verified_by BIGINT,
    verified_date DATE,
    status ENUM('planned', 'in_progress', 'completed', 'verified'),
    effectiveness ENUM('effective', 'partially_effective', 'ineffective'),
    cost DECIMAL(15,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

#### 18. **📊 RAPORLAMA VE DASHBOARD** - %10
**Durum:** 🟡 Temel Raporlar Var, Geliştirilecek

**Mevcut:**
- ✅ Çalışan listesi raporları
- ✅ Puantaj raporları
- ✅ Temel istatistikler

**Eklenecek Raporlar:**
- Günlük/Haftalık/Aylık ilerleme raporu
- Finansal raporlar
  - Gelir-Gider analizi
  - Kar-Zarar projeksiyonu
  - Nakit akış tahmini
  - Bütçe gerçekleşme
- Performans raporları
  - Taşeron performansı
  - Tedarikçi performansı
  - İş güvenliği istatistikleri
- Yönetici dashboard'u
  - Tüm projeler özeti
  - KPI'lar
  - Uyarılar ve bildirimler
  - Grafikler
- Excel/PDF export
- Otomatik rapor gönderimi (email)

---

## 📅 UYGULAMA ROADMAP

### **Q1 2025 - FAZ 1: Temel Proje Modülleri**
**Tahmini Süre:** 2-3 Ay

#### Ay 1: Taşeron ve Sözleşme Altyapısı
- [x] Proje temel yapısı (Tamamlandı)
- [ ] Taşeron yönetimi (4 hafta)
  - Hafta 1: Model ve migration
  - Hafta 2: Controller ve validasyon
  - Hafta 3: Frontend sayfaları
  - Hafta 4: Test ve düzeltmeler
- [ ] Sözleşme yönetimi (4 hafta)
  - Hafta 1-2: Ana yapı
  - Hafta 3: Poz listesi
  - Hafta 4: Ek sözleşmeler

#### Ay 2-3: Hakediş Sistemi
- [ ] Müşteri hakedişleri (3 hafta)
- [ ] Taşeron hakedişleri (3 hafta)
- [ ] Metraj tutanakları (2 hafta)
- [ ] Ödeme takibi (2 hafta)
- [ ] Raporlama (2 hafta)

**Çıktı:** Taşeron, sözleşme ve hakediş sistemi tamamen çalışır

---

### **Q2 2025 - FAZ 2: İlerleme Takibi**
**Tahmini Süre:** 2 Ay

#### Ay 4: İş Programı ve İlerleme
- [ ] Proje programı (Gantt) (3 hafta)
- [ ] İş kalemleri (2 hafta)
- [ ] İlerleme girişi (3 hafta)

#### Ay 5: Performans ve Raporlama
- [ ] S-Eğrisi ve EVM (2 hafta)
- [ ] İlerleme raporları (2 hafta)
- [ ] Dashboard (2 hafta)
- [ ] Excel/PDF export (2 hafta)

**Çıktı:** Fiziki ve mali ilerleme tam kontrolde

---

### **Q3 2025 - FAZ 3: Destek Modüller**
**Tahmini Süre:** 2-3 Ay

#### Ay 6-7: Malzeme, Ekipman, Döküman
- [ ] Malzeme yönetimi geliştirme (3 hafta)
- [ ] Ekipman yönetimi (3 hafta)
- [ ] Döküman yönetimi geliştirme (4 hafta)
- [ ] İhale yönetimi (4 hafta)

**Çıktı:** Tam kaynak yönetimi

---

### **Q4 2025 - FAZ 4: İleri Özellikler**
**Tahmini Süre:** 2 Ay

#### Ay 8-9: Risk ve Kalite
- [ ] Risk yönetimi (3 hafta)
- [ ] Kalite kontrol (3 hafta)
- [ ] Gelişmiş raporlama (2 hafta)
- [ ] Mobil uygulama (isteğe bağlı) (4 hafta)

**Çıktı:** Kurumsal seviye proje yönetimi

---

## 🎯 MODÜL ÖNCELİK MATRİSİ

| Modül | Durum | Öncelik | Başlangıç | Tahmini Süre | Bağımlılık |
|-------|-------|---------|-----------|--------------|------------|
| Çalışan Yönetimi | ✅ %100 | - | - | - | - |
| Puantaj Yönetimi | ✅ %100 | - | - | - | Çalışan |
| İzin Yönetimi | ✅ %100 | - | - | - | Çalışan |
| Satın Alma | ✅ %100 | - | - | - | - |
| Proje (Temel) | ✅ %80 | - | - | - | - |
| **Taşeron Yönetimi** | 🔴 %0 | 🔥 YÜKSEK | Hemen | 4 hafta | Proje |
| **Sözleşme Yönetimi** | 🔴 %0 | 🔥 YÜKSEK | 4. hafta | 4 hafta | Taşeron, Proje |
| **Hakediş Sistemi** | 🔴 %0 | 🔥 YÜKSEK | 8. hafta | 6 hafta | Sözleşme |
| İlerleme Takibi | 🔴 %0 | 🟡 ORTA | 14. hafta | 8 hafta | Proje, Sözleşme |
| Malzeme (Gelişmiş) | 🟡 %30 | 🟡 ORTA | 22. hafta | 3 hafta | Proje |
| Döküman (Gelişmiş) | 🟡 %20 | 🟡 ORTA | 25. hafta | 4 hafta | - |
| İhale Yönetimi | 🔴 %0 | 🟡 ORTA | 29. hafta | 4 hafta | Taşeron |
| Risk Yönetimi | 🔴 %0 | 🔵 DÜŞÜK | 33. hafta | 3 hafta | Proje |
| Kalite Kontrol | 🔴 %0 | 🔵 DÜŞÜK | 36. hafta | 3 hafta | Proje |
| Gelişmiş Raporlama | 🟡 %10 | 🟡 ORTA | Sürekli | - | Tüm modüller |

---

## 🎨 SİSTEM MİMARİSİ

### Teknoloji Stack
- **Backend:** Laravel 10 + PHP 8.2
- **Frontend:** Vue 3 (Composition API) + Inertia.js
- **UI:** Tailwind CSS
- **Veritabanı:** MySQL 8.0
- **Cache:** Redis
- **Queue:** Laravel Queue
- **Storage:** Local + S3 (dökümanlar için)
- **Raporlama:** Laravel Excel, DomPDF
- **Grafik:** Chart.js, ApexCharts

### Güvenlik
- ✅ Spatie Permission (rol ve yetki yönetimi)
- ✅ CSRF koruması
- ✅ XSS koruması
- ✅ SQL injection koruması
- [ ] İki faktörlü kimlik doğrulama (2FA)
- [ ] Audit log (tüm işlemlerin kaydı)
- [ ] IP kısıtlama
- [ ] Rate limiting

---

## 💰 MALİYET TAHMİNİ (Adam/Saat)

| Modül | Geliştirme | Test | Toplam Saat | Hafta |
|-------|-----------|------|-------------|-------|
| Taşeron Yönetimi | 120 | 40 | 160 | 4 |
| Sözleşme Yönetimi | 140 | 40 | 180 | 4.5 |
| Hakediş Sistemi | 200 | 60 | 260 | 6.5 |
| İlerleme Takibi | 240 | 80 | 320 | 8 |
| Malzeme/Ekipman | 100 | 40 | 140 | 3.5 |
| Döküman Geliştirme | 120 | 40 | 160 | 4 |
| İhale Yönetimi | 120 | 40 | 160 | 4 |
| Risk Yönetimi | 80 | 30 | 110 | 2.75 |
| Kalite Kontrol | 100 | 40 | 140 | 3.5 |
| **TOPLAM** | **1220** | **410** | **1630** | **~40** |

**Not:** 1 hafta = 40 saat varsayılmıştır.

---

## ✅ BAŞLANGIC ÖNERİSİ

### Hemen Başlayalım: **FAZ 1 - Modül 9: Taşeron Yönetimi**

**Sebep:**
1. En kritik ihtiyaç
2. Diğer modüller (sözleşme, hakediş) buna bağımlı
3. Hızlı değer üretir
4. Mevcut altyapıya kolay entegre

**İlk Adımlar:**
1. ✅ Migration oluştur
2. ✅ Model ve ilişkiler
3. ✅ Controller (CRUD)
4. ✅ Vue sayfaları (Index, Create, Edit, Show)
5. ✅ Test ve validasyon

**Başlayalım mı?** 🚀

---

*Son Güncelleme: 19 Ekim 2025*
*Proje Versiyonu: 1.0.0*
*Durum: Aktif Geliştirme*
