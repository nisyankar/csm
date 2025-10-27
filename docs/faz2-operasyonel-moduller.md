# FAZ 2: Operasyonel Çekirdek
## 🔄 DEVAM EDİYOR (%70)

**Başlangıç:** 25 Ekim 2025
**Hedef Bitiş:** Aralık 2025
**Durum:** Aktif Sprint - Satış ve Tapu Yönetimi Tamamlandı ✅
**Modül Sayısı:** 7 (4 tamamlandı: Finansal, Keşif/Metraj, Sözleşme, Satış/Tapu)

---

## 📋 GENEL BAKIŞ

Faz 2, inşaat operasyonlarının tamamını kapsayacak şekilde sistemi genişletir. Bu fazda, finansal yönetim, keşif/metraj, sözleşme yönetimi ve satış süreçleri entegre edilecektir.

### Hedef
İnşaat ERP'sinin operasyonel çekirdeğini tamamlamak: **Finansal sistem + Keşif/Metraj + Satış**

---

## 🎯 MODÜLLER

### 1. Finansal Yönetim ve Kar/Zarar Sistemi (100%) 💰 **PRİORİTE 1** ✅

#### Hedef
Tüm modüllerden gelen gelir/gider verilerini tek noktada toplayıp kar/zarar analizi yapmak.

#### Database
```sql
-- Gelir kategorileri (hiyerarşik)
income_categories (id, name, code, parent_id, description, is_active)

-- Gider kategorileri (hiyerarşik)
expense_categories (id, name, code, parent_id, description, is_active)

-- Finansal işlemler (tek tablo)
financial_transactions (
    id, project_id, transaction_type, category_id,
    transaction_date, amount, description,
    source_module, source_id,  -- Otomatik entegrasyon
    invoice_number, invoice_date,
    payment_method, payment_status, paid_amount,
    accounting_code, notes,
    created_by, approved_by, approved_at
)

-- Bütçe vs Gerçekleşen
budget_vs_actual (
    id, project_id, year, month,
    category_type, category_id,
    budget_amount, actual_amount,
    variance GENERATED, variance_percentage GENERATED
)
```

#### Otomatik Entegrasyon (Event-Driven)
- **Puantaj** → expense (personel maaş gideri) ✅
  - Event: `TimesheetApprovedEvent`
  - Listener: `CreateFinancialTransactionForTimesheet`
  - Ödeme Durumu: `paid` (otomatik ödendi)
- **Satınalma** → expense (malzeme gideri) ✅
  - Event: `PurchaseOrderApprovedEvent`
  - Listener: `CreateFinancialTransactionForPurchaseOrder`
  - Ödeme Durumu: `pending` (beklemede)
- **Hakediş** → expense (taşeron ödemesi) ✅
  - Event: `ProgressPaymentPaidEvent`
  - Listener: `CreateFinancialTransactionForProgressPayment`
  - Ödeme Durumu: `paid` (ödendi)
- **Satış** → income (gelir kaydı) 🔜

#### Raporlar
- Proje bazlı kar/zarar ✅
- Aylık/Yıllık gelir-gider ✅
- Nakit akış raporu ✅
- Bütçe vs gerçekleşen karşılaştırma ✅
- Karlılık analizi ✅

#### Sprint Görevler
- [x] Migration'lar (4 tablo tamamlandı)
- [x] Model'ler ve ilişkiler (FinancialTransaction, IncomeCategory, ExpenseCategory, BudgetVsActual)
- [x] FinancialTransactionService (otomatik kayıt)
- [x] Event/Listener yapısı (TimesheetApproved, PurchaseOrderApproved, ProgressPaymentPaid)
- [x] API Controllers (CRUD + Raporlama endpoints)
- [x] Web Controllers (Inertia/Vue sayfaları)
- [x] Vue Sayfaları (Index, Create, Edit, Show, Dashboard, ProfitLoss)
- [x] Sidebar menü entegrasyonu
- [x] Dashboard widget'ları
- [x] Raporlama sayfaları

#### Tamamlanan Dosyalar
**Backend:**
- ✅ `database/migrations/2025_10_25_*_financial_tables.php` (4 migration)
- ✅ `app/Models/FinancialTransaction.php`
- ✅ `app/Models/IncomeCategory.php`
- ✅ `app/Models/ExpenseCategory.php`
- ✅ `app/Models/BudgetVsActual.php`
- ✅ `app/Services/Financial/FinancialTransactionService.php`
- ✅ `app/Events/TimesheetApprovedEvent.php`
- ✅ `app/Events/PurchaseOrderApprovedEvent.php`
- ✅ `app/Events/ProgressPaymentPaidEvent.php`
- ✅ `app/Listeners/CreateFinancialTransactionForTimesheet.php`
- ✅ `app/Listeners/CreateFinancialTransactionForPurchaseOrder.php`
- ✅ `app/Listeners/CreateFinancialTransactionForProgressPayment.php`
- ✅ `app/Http/Controllers/Api/FinancialTransactionController.php`
- ✅ `app/Http/Controllers/Api/IncomeCategoryController.php`
- ✅ `app/Http/Controllers/Api/ExpenseCategoryController.php`
- ✅ `app/Http/Controllers/FinancialController.php`
- ✅ `database/seeders/FinancialCategoriesSeeder.php`

**Frontend:**
- ✅ `resources/js/Pages/Financial/Index.vue`
- ✅ `resources/js/Pages/Financial/Create.vue`
- ✅ `resources/js/Pages/Financial/Edit.vue`
- ✅ `resources/js/Pages/Financial/Show.vue`
- ✅ `resources/js/Pages/Financial/Dashboard.vue`
- ✅ `resources/js/Pages/Financial/ProfitLoss.vue`
- ✅ `resources/js/Layouts/Sidebar.vue` (Finansal Yönetim menüsü eklendi)

**Routes:**
- ✅ `routes/api.php` (API endpoints: CRUD, payment, approve, reports)
- ✅ `routes/web.php` (Web routes: Dashboard, Index, Create, Show, Edit, ProfitLoss)

#### Özellikler
- ✅ Gelir/Gider kategori yönetimi (hiyerarşik)
- ✅ Finansal işlem CRUD (manuel + otomatik)
- ✅ Ödeme takibi (pending, partial, paid)
- ✅ Onay sistemi
- ✅ Proje bazlı filtreleme
- ✅ Tarih aralığı filtreleme
- ✅ Kar/Zarar raporu (proje, yıl, ay bazlı)
- ✅ Kategori bazlı dökümler
- ✅ Dashboard özet widget'ları (gelir, gider, kar, marj)
- ✅ Otomatik Event-driven entegrasyon
- ✅ Hakediş-style full-width profesyonel tasarım
- ✅ Akıllı ödeme durumu (puantaj → paid, satınalma → pending)

#### Test Sonuçları
- **Toplam İşlem**: 65+ kayıt
- **Puantaj Entegrasyonu**: 62 otomatik kayıt (31,000 TL)
- **Hakediş Entegrasyonu**: 1 kayıt (21,000 TL)
- **Dashboard**: Tüm özet kartlar çalışıyor
- **Kar/Zarar Raporu**: Kategori dökümü aktif
- **Ödeme Durumu**: Puantajlar "ödendi", satınalma "beklemede"

---

### 2. Keşif & Metraj Yönetimi (95%) 📐 **PRİORİTE 2** ✅

#### Hedef
Hakediş tutarlarını manuel değil, ölçülen metrajlardan otomatik hesaplamak ve metraj aşımlarını takip etmek.

#### Database
```sql
quantities (
    id, project_id, work_item_id,
    project_structure_id, project_floor_id, project_unit_id,  -- Opsiyonel lokasyon
    planned_quantity, completed_quantity, remaining_quantity,
    unit, status, completion_percentage,
    measurement_date, measurement_method,
    verified_by, verified_at, approved_by, approved_at,
    notes, created_at, updated_at, deleted_at
)
```

#### Özellikler Tamamlandı ✅
- ✅ Proje yapısı entegrasyonu (Yapı/Kat/Birim)
- ✅ İş kalemi bazlı metraj kayıtları
- ✅ Planlanan ve tamamlanan miktar takibi
- ✅ Hakediş ile otomatik ilişki (quantity_id foreign key)
- ✅ **Metraj Aşımı Takip Sistemi**:
  - ✅ Otomatik aşım tespiti (is_quantity_overrun flag)
  - ✅ Aşım miktarı ve tutarı kaydetme
  - ✅ Kullanıcıya görsel uyarı (sarı alert box)
  - ✅ Filtrelenebilir Metraj Aşımı Raporu
  - ✅ Proje/taşeron/iş kalemi bazlı raporlama
  - ✅ Tarih aralığı filtreleme
- ✅ İlerleme yüzdesi otomatik hesaplama
- ✅ Ölçüm yöntemleri ve onay süreci
- ✅ Proje Show sayfasında Keşif/Metraj tabı
- ✅ Metraj Show sayfasında İlişkili Hakediş widget'ı

#### Entegrasyon Tamamlandı ✅
- **Hakediş:** Akıllı form ile otomatik metraj bulma ✅
  - Proje/Yapı/Kat/Birim seçildiğinde ilgili metraj API'den çekilir
  - `planned_quantity`, `unit`, `quantity_id` otomatik doldurulur
  - Daha önce hakediş yapılan miktar çıkarılarak kalan gösterilir
- **Finansal:** İlerleme ve tutar hesaplamaları entegre
- **İş Kalemleri:** WorkItem relationship ile tam entegrasyon
- **Proje:** Project-Quantity relationship eklendi

#### Sprint Görevler
- [x] Migration (quantities table)
- [x] Quantity model ve ilişkiler
- [x] QuantityService (business logic)
- [x] QuantityController (CRUD + search API)
- [x] ProgressPaymentController güncellemesi (metraj entegrasyonu)
- [x] Frontend: Metraj CRUD sayfaları (Dashboard, Index, Create, Edit, Show)
- [x] Hakediş Create sayfasına metraj auto-fill widget'ı
- [x] Metraj Show sayfasına İlişkili Hakediş tablosu
- [x] Project Show sayfasına Keşif/Metraj tabı
- [x] QuantitySeeder (69 test verisi)
- [x] Sidebar menü entegrasyonu
- [x] **Metraj Aşımı Sistemi**:
  - [x] Migration (is_quantity_overrun, overrun_amount, overrun_notes)
  - [x] ProgressPayment model güncellemesi
  - [x] Otomatik aşım tespiti (ProgressPaymentController::store)
  - [x] Kullanıcı uyarı sistemi (Create.vue)
  - [x] Metraj Aşımı Raporu sayfası (Vue)
  - [x] QuantityOverrunReport controller method
  - [x] Route kayıt (/progress-payments/quantity-overrun-report)
  - [x] Sidebar menü entegrasyonu
- [ ] Keşif import/export (Excel) - Sonraki sprint

#### Tamamlanan Dosyalar (26 Ekim 2025)
**Backend:**
- ✅ `database/migrations/2025_10_26_create_quantities_table.php`
- ✅ `database/migrations/2025_10_26_add_quantity_overrun_tracking_to_progress_payments_table.php`
- ✅ `app/Models/Quantity.php` (ilişkiler ve accessor'lar)
- ✅ `app/Models/ProgressPayment.php` (metraj aşımı alanları)
- ✅ `app/Services/QuantityService.php`
- ✅ `app/Http/Controllers/QuantityController.php` (search API dahil)
- ✅ `app/Http/Controllers/ProgressPaymentController.php` (metraj entegrasyonu + aşım tespiti + rapor)
- ✅ `database/seeders/QuantitySeeder.php` (69 kayıt)
- ✅ `routes/web.php` (quantities routes + search endpoint + overrun report)

**Frontend:**
- ✅ `resources/js/Pages/Quantities/Dashboard.vue`
- ✅ `resources/js/Pages/Quantities/Index.vue`
- ✅ `resources/js/Pages/Quantities/Create.vue`
- ✅ `resources/js/Pages/Quantities/Edit.vue`
- ✅ `resources/js/Pages/Quantities/Show.vue` (İlişkili Hakediş widget'ı ile)
- ✅ `resources/js/Pages/ProgressPayments/Create.vue` (metraj auto-fill + aşım uyarısı)
- ✅ `resources/js/Pages/ProgressPayments/QuantityOverrunReport.vue` (aşım raporu)
- ✅ `resources/js/Pages/Projects/Show.vue` (Keşif/Metraj tabı)
- ✅ `resources/js/Layouts/Sidebar.vue` (Metraj Aşımı Raporu menü öğesi)

**Entegrasyon:**
- ✅ Project model'e quantities relationship
- ✅ ProgressPayment model'e quantity_id kolonu
- ✅ Null-safe accessor metodları (ProjectUnit, Employee, Subcontractor, Document)

---

### 3. Sözleşme Yönetimi (100%) 📄 **PRİORİTE 3** ✅

#### Hedef
Taşeron, tedarikçi ilişkilerini merkezi sözleşme sistemi ile yönetmek.

#### Database
```sql
contracts (
    id, contract_type,  -- subcontractor, supplier
    contract_number, contract_name,
    project_id, subcontractor_id,
    work_description, scope_of_work,
    contract_value, currency,
    payment_terms, signing_date, start_date, end_date,
    warranty_type, warranty_amount, warranty_start_date, warranty_end_date,
    status,  -- draft, active, completed, terminated, expired
    termination_date, termination_reason,
    documents, notes,
    created_by, updated_by, approved_by, approved_at
)
```

#### Entegrasyon
- **Hakediş:** `contract_id` referansı ✅
- **Satınalma:** `contract_id` referansı (opsiyonel) ✅
- **Proje:** Proje detay sayfasında Sözleşmeler tabı ✅

#### Sprint Görevler
- [x] Migrations (3 migration: contracts table, progress_payments contract_id, purchasing_requests contract_id)
- [x] Contract model (relationships: project, subcontractor, progressPayments, purchasingRequests)
- [x] Mevcut tablolara `contract_id` ekleme
- [x] ContractService (business logic: create, update, activate, terminate, complete)
- [x] ContractController (web + API endpoints)
- [x] Sözleşme CRUD sayfaları (Dashboard, Index, Show, Create, Edit)
- [x] Sözleşme süresi ve teminat takibi
- [x] Otomatik sözleşme numarası oluşturma (duplicate-safe)
- [x] Proje detay sayfasına sözleşmeler tabı ekleme
- [x] Form validation ve hata gösterimi
- [x] Durum yönetimi (draft → active → completed/terminated/expired)
- [x] Dashboard istatistikleri ve süre dolacak sözleşmeler
- [x] Seeder (15 örnek sözleşme)

#### Tamamlanan Dosyalar
**Backend:**
- ✅ `database/migrations/2025_10_26_create_contracts_table.php`
- ✅ `database/migrations/2025_10_26_add_contract_id_to_progress_payments_table.php`
- ✅ `database/migrations/2025_10_26_add_contract_id_to_purchasing_requests_table.php`
- ✅ `app/Models/Contract.php`
- ✅ `app/Models/Subcontractor.php` (contracts relationship eklendi)
- ✅ `app/Models/ProgressPayment.php` (contract_id eklendi)
- ✅ `app/Models/Project.php` (contracts relationship eklendi)
- ✅ `app/Services/Contract/ContractService.php`
- ✅ `app/Http/Controllers/ContractController.php`
- ✅ `app/Http/Controllers/Api/ContractController.php`
- ✅ `routes/web.php` (contract routes)
- ✅ `routes/api.php` (contract API routes)
- ✅ `database/seeders/ContractSeeder.php`

**Frontend:**
- ✅ `resources/js/Pages/Contracts/Dashboard.vue`
- ✅ `resources/js/Pages/Contracts/Index.vue`
- ✅ `resources/js/Pages/Contracts/Show.vue`
- ✅ `resources/js/Pages/Contracts/Create.vue`
- ✅ `resources/js/Pages/Contracts/Edit.vue`
- ✅ `resources/js/Pages/Projects/Show.vue` (contracts tab eklendi)
- ✅ `resources/js/Layouts/Sidebar.vue` (menu eklendi)

#### Özellikler
- ✅ Sözleşme türü (Taşeron/Tedarikçi)
- ✅ Otomatik sözleşme numarası (PRJ-CODE-TS-YYYY-0001)
- ✅ Teminat yönetimi (Banka Mektubu, Nakit, Çek, Teminatsız)
- ✅ Sözleşme durumu yönetimi ve lifecycle
- ✅ Hakediş ile entegrasyon
- ✅ Satınalma ile opsiyonel entegrasyon
- ✅ Dashboard: İstatistikler, süresi dolacak sözleşmeler
- ✅ Filtreleme (Proje, Taşeron, Durum, Tarih)
- ✅ Full-width tasarım ve card layout
- ✅ Pagination ve arama
- ✅ Proje bazlı sözleşme görüntüleme

---

### 4. Satış ve Tapu Yönetimi (100%) 🏘️ **PRİORİTE 3** ✅

#### Hedef
Konut satışlarını, müşteri takibini, ödeme planlarını ve tapu devir işlemlerini merkezi sistemden yönetmek.

#### Database (Zaten Mevcut)
```sql
customers (
    id, customer_type, first_name, last_name, company_name,
    tc_number, tax_number, phone, email, address,
    occupation, marital_status, customer_status, notes
)

unit_sales (
    id, project_id, project_unit_id, customer_id,
    sale_number, sale_type, list_price, discount_amount, final_price,
    currency, down_payment, installment_count, monthly_installment,
    payment_method, status, deed_status, deed_type,
    title_deed_number, deed_transfer_date, deed_documents,
    contract_documents, payment_documents, deed_notes
)

sale_payments (
    id, unit_sale_id, customer_id, payment_type,
    installment_number, amount, paid_amount, remaining_amount,
    currency, due_date, payment_date, status
)
```

#### Özellikler Tamamlandı ✅
- ✅ Müşteri CRM (Bireysel/Kurumsal)
- ✅ Rezervasyon/satış sözleşmeleri
- ✅ Otomatik ödeme planı oluşturma (Peşinat + Taksitler)
- ✅ Taksit takibi ve ödeme yönetimi
- ✅ Otomatik gelir kaydı (Financial entegrasyon)
- ✅ Cascade dropdown (Proje → Blok → Kat → Birim)
- ✅ Birim satış durumu otomatik güncelleme
- ✅ **Basit Tapu Takibi**:
  - ✅ Tapu durumu yönetimi (Devredilmedi, İşlemde, Devredildi, Ertelendi)
  - ✅ Tapu belgesi yükleme sistemi (PDF, JPG, PNG)
  - ✅ Tapu bilgileri (Tip, Numara, Devir Tarihi, Notlar)
  - ✅ UnitSale Show sayfasında interaktif tapu bölümü
  - ✅ Modal-based güncelleme ve belge yükleme
- ✅ **Satış Durumu Görselleştirme**:
  - ✅ Proje bazlı satış istatistikleri
  - ✅ Blok seçimi ve kat progress bar'ları
  - ✅ Renk kodlu birim grid'i (Müsait, Satıldı, Rezerve, Gecikmiş)
  - ✅ Birim detay modal'ı (Müşteri, fiyat, ödeme bilgileri)
  - ✅ Satış oranı ve tutarları dashboard
  - ✅ Index sayfası (Proje listesi + istatistikler)

#### Entegrasyon Tamamlandı ✅
- **Project Units**: Birim satış durumu otomatik güncelleme (is_sold, sale_date) ✅
- **Finansal Sistem**: Satış ödeme onayı → Gelir kaydı (Event-driven ready) ✅
- **Cascade API**: Proje → Yapı → Kat → Birim hiyerarşik dropdown ✅
- **Routes**: Sales modülü altında organize edildi ✅

#### Sprint Görevler
- [x] Mevcut migrations kontrol (Customer, UnitSale, SalePayment zaten var)
- [x] SalesStatusController (satış durumu görselleştirme)
- [x] UnitSaleController tapu method'ları (updateDeedStatus, uploadDeedDocument)
- [x] Vue Componentleri:
  - [x] SalesStatus/Show.vue (Blok/Kat/Birim görselleştirme)
  - [x] SalesStatus/Index.vue (Proje listesi)
  - [x] UnitSales/Show.vue (Tapu bölümü ve modallar)
- [x] API Endpoints:
  - [x] GET /sales-status (Proje listesi)
  - [x] GET /sales-status/{project} (Proje satış durumu)
  - [x] GET /sales-status/api/structure/{structure} (Blok katları)
  - [x] GET /sales-status/api/floor/{floor}/units (Kat birimleri)
  - [x] POST /unit-sales/{unitSale}/deed/update-status (Tapu güncelleme)
  - [x] POST /unit-sales/{unitSale}/deed/upload-document (Tapu belgesi yükleme)
- [x] Routes entegrasyonu
- [x] Dokümantasyon güncelleme

#### Tamamlanan Dosyalar (27 Ekim 2025)
**Backend:**
- ✅ `app/Http/Controllers/SalesStatusController.php`
- ✅ `app/Http/Controllers/UnitSaleController.php` (tapu method'ları eklendi)
- ✅ `routes/web.php` (sales-status ve deed routes)

**Frontend:**
- ✅ `resources/js/Pages/Sales/SalesStatus/Index.vue`
- ✅ `resources/js/Pages/Sales/SalesStatus/Show.vue`
- ✅ `resources/js/Pages/Sales/UnitSales/Show.vue` (tapu bölümü ve modallar)

#### Özellikler Detayı
**Satış Durumu Görselleştirme:**
- Proje istatistikleri (Toplam, Satılan, Rezerve, Müsait birim)
- Satış oranı progress bar
- Blok seçim sidebar (sticky)
- Kat bazlı satış progress'i
- Expandable kat detayları
- Renk kodlu birim kartları:
  - 🟢 Müsait (available)
  - 🔴 Satıldı (sold)
  - 🟡 Rezerve (reserved)
  - 🟠 Gecikmiş (delayed)
- Birim hover tooltip (Müşteri adı, durum)
- Birim detay modal'ı (Alan, fiyat, ödeme tamamlanma)
- Satış detayına hızlı geçiş butonu

**Tapu Yönetimi:**
- Modal-based güncelleme formu
- Tapu durumu dropdown (4 durum)
- Tapu tipi, numarası, devir tarihi
- Tapu notları (textarea)
- Belge yükleme modal'ı (drag-drop ready)
- Belge listesi ve indirme
- Otomatik tarih ayarlama (transferred durumunda)
- Storage'da deed_documents klasörü

#### Test Sonuçları
- **Satış Durumu**: Proje satış istatistikleri doğru hesaplanıyor
- **Blok/Kat Navigation**: Smooth geçişler, API çağrıları çalışıyor ✅
- **Birim Grid**: Renk kodları ve hover tooltips aktif
- **Tapu Güncelleme**: Form validation ve başarılı kayıt
- **Belge Yükleme**: 10MB limit, PDF/JPG/PNG desteği

#### Bug Fixes (27 Ekim 2025)
- ✅ **Blok Seçimi Sorunu Düzeltildi**:
  - Problem: Bloklara tıklandığında katlar görünmüyordu
  - Sebep: `SalesStatusController::getStructureDetails()` metodunda `floor_order` kolonu kullanılıyordu ama database'de bu kolon yoktu
  - Çözüm: `orderBy('floor_order', 'desc')` → `orderBy('floor_number', 'desc')` olarak düzeltildi
  - Dosya: `app/Http/Controllers/SalesStatusController.php:88`
  - Durum: Test edildi ve çalışıyor ✅

---

### 5. İnşaat Ruhsat ve İzin Yönetimi (0%) 🏗️

#### Database
```sql
construction_permits (
    id, project_id, permit_type,  -- building, demolition, occupancy, usage
    permit_number, application_date, approval_date, expiry_date,
    status, issuing_authority, zoning_status,
    documents JSON, notes
)
```

#### Özellikler
- Yapı ruhsatı, yıkım ruhsatı, iskan izni, yapı kullanma izni
- Başvuru süreç takibi
- Belge yönetimi (dosya upload)
- Süre dolumu uyarıları
 
#### Sprint Görevler
- [ ] Migration
- [ ] ConstructionPermit model
- [ ] CRUD sayfaları
- [ ] Dosya upload sistemi
- [ ] Süre dolumu notification

---

### 6. Yapı Denetim Sistemi (0%) 🔍

#### Database
```sql
inspection_companies (
    id, company_name, license_number,
    contact_person, phone, email, address
)

inspections (
    id, project_id, inspection_company_id,
    inspector_name, inspection_date, inspection_type,
    status, findings,
    non_conformities JSON,  -- [{description, severity, deadline}]
    corrective_actions JSON,  -- [{action, responsible, deadline, status}]
    report_path, next_inspection_date
)
```

#### Özellikler
- Denetim kuruluşu kayıtları
- Periyodik/özel/final denetim raporları
- Uygunsuzluk ve düzeltici faaliyet takibi
- **Denetim fotoğrafları ve ekler** (dosya yönetimi)
- Denetim tutanakları arşivleme

#### Sprint Görevler
- [ ] Migrations
- [ ] InspectionCompany, Inspection modelleri
- [ ] CRUD sayfaları
- [ ] Dosya upload ve görüntüleme
- [ ] Uygunsuzluk takip sistemi

---

### 7. Basit Stok Takibi (0%) 📦

#### Database
```sql
warehouses (
    id, project_id, name, location, responsible_user_id
)

stock_movements (
    id, warehouse_id, material_id,
    movement_type,  -- in, out, transfer
    quantity, unit_price, total_cost,
    source_module, source_id,  -- Otomatik entegrasyon
    notes, created_by
)
```

#### Özellikler
- Depo tanımlama
- Stok giriş/çıkış kayıtları
- Otomatik entegrasyon:
  - **Satınalma Teslimatı** → Stok artışı
  - **Günlük Rapor** → Stok azalışı
- Anlık stok durumu raporları

#### Sprint Görevler
- [ ] Migrations
- [ ] Warehouse, StockMovement modelleri
- [ ] Materials tablosuna `current_stock` ekleme
- [ ] Stok giriş/çıkış formları
- [ ] Otomatik entegrasyon servisleri

---

## 📅 SPRINT PLANI

### ✅ Sprint 1 (25 Ekim - 26 Ekim) - TAMAMLANDI
**Hedef:** Finansal Yönetim Modülü
- ✅ Finansal migrations ve modeller (4 tablo)
- ✅ Otomatik kayıt servisleri (Event/Listener yapısı)
- ✅ Vue sayfaları (6 sayfa: Index, Create, Edit, Show, Dashboard, ProfitLoss)
- ✅ API ve Web Controllers
- ✅ Puantaj, Hakediş, Satınalma entegrasyonu
- ✅ Sidebar menü entegrasyonu
- ✅ Test ve bug düzeltmeleri

### Sprint 2 (27 Ekim - 10 Kasım)
**Hedef:** Keşif & Metraj Yönetimi
- Keşif & Metraj migrations
- Quantity model ve ilişkiler
- Hakediş entegrasyonu (metrajdan otomatik)
- Excel import/export
- Metraj giriş formları

### Sprint 3 (11-25 Kasım)
**Hedef:** Sözleşme Yönetimi
- Contract sistemi (polymorphic)
- Mevcut modüllere contract_id ekleme
- Sözleşme CRUD sayfaları
- Süre ve teminat takibi

### Sprint 4 (26 Kasım - 10 Aralık)
**Hedef:** Satış ve Tapu Yönetimi
- Müşteri CRM
- Rezervasyon/satış sözleşmeleri
- Ödeme planı ve taksit takibi
- Tapu devir işlemleri
- Satış durumu dashboard

### Sprint 5 (11-20 Aralık)
**Hedef:** Ruhsat + Denetim + Stok
- Ruhsat yönetimi
- Yapı denetim sistemi
- Basit stok takibi
- Dosya upload sistemleri

### Sprint 6 (21-31 Aralık)
**Hedef:** Test & Polish & Optimizasyon
- Entegrasyon testleri
- Dashboard widget'ları
- Raporlama sayfaları
- Performance optimizasyonu
- Bug fixes ve refactoring

---

## 🔗 ENTEGRASYON NOKTALARI

### Faz 1 Modüllerinde Yapılacak Değişiklikler

#### Hakediş (ProgressPayment)
- [ ] `completed_quantity` → Quantity tablosundan çekilecek
- [ ] `contract_id` kolonu eklenecek
- [ ] Otomatik financial_transaction kaydı

#### Satınalma (Purchasing)
- [ ] `contract_id` kolonu eklenecek
- [ ] Teslimat → Stok artışı entegrasyonu
- [ ] Otomatik financial_transaction kaydı

#### Malzeme (Materials)
- [ ] `current_stock` kolonu eklenecek
- [ ] Warehouse ilişkisi

#### Puantaj (Timesheet)
- [ ] Aylık maaş hesaplama → Otomatik financial_transaction

#### Proje (Projects)
- [ ] Finansal widget'lar (gelir, gider, kar)
- [ ] Keşif vs Gerçekleşen metraj widget'ı

---

## 📊 BAŞARI METRİKLERİ

### Hedefler
- **Modül Sayısı:** 7/7 (%0 → %100)
- **Otomatik Finansal Kayıt:** Tüm modüllerden
- **Keşif Entegrasyonu:** Hakediş tam otomatik
- **Satış Takibi:** Müşteri-Ödeme-Tapu

### Teknik Hedefler
- Database trigger'lar ve event'ler
- Service katmanı oluşturma
- Dosya upload sistemi (ruhsat, denetim)
- Excel import/export (keşif)

---

## 🎯 TAMAMLANAN MODÜLLER

### 1. Finansal Yönetim ✅ (26 Ekim 2025)
- **Backend**: 19 dosya (migrations, models, services, events, listeners, controllers, seeders)
- **Frontend**: 6 Vue sayfası (full-width profesyonel tasarım)
- **Entegrasyon**: 3 event-listener çifti (Puantaj, Hakediş, Satınalma)
- **Test**: 65+ finansal işlem otomatik oluşturuldu
- **Süre**: 2 gün

**Notlar:**
- Event-driven mimari sayesinde modüller birbirinden bağımsız
- Puantaj onayı → otomatik "ödendi" finansal kayıt
- Hakediş ödendi → otomatik "ödendi" finansal kayıt
- Satınalma onayı → otomatik "beklemede" finansal kayıt
- Dashboard ve raporlar gerçek zamanlı veri gösteriyor

---

## 📈 İLERLEME DETAYI

| Modül | Durum | Tamamlanma | Tahmini Süre | Gerçek Süre |
|-------|-------|------------|--------------|-------------|
| Finansal Yönetim | ✅ | %100 | 5 gün | 2 gün |
| Keşif & Metraj | ✅ | %100 | 7 gün | 1.5 gün |
| Sözleşme Yönetimi | ✅ | %100 | 5 gün | 1 gün |
| Satış ve Tapu | ✅ | %100 | 10 gün | 1 gün |
| Ruhsat Yönetimi | 🔜 | %0 | 3 gün | - |
| Yapı Denetim | 🔜 | %0 | 3 gün | - |
| Stok Takibi | 🔜 | %0 | 3 gün | - |
| **TOPLAM** | **🔄** | **%57** | **36 gün** | **5.5 gün** |

---

**Son Güncelleme:** 27 Ekim 2025
**Versiyon:** 1.2
**Önceki Faz:** [Faz 1: Temel Altyapı](./faz1-temel-altyapi.md)
**Sonraki Faz:** [Faz 3: Gelişmiş Modüller](./faz3-gelismis-moduller.md)