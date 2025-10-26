# FAZ 2: Operasyonel Çekirdek
## 🔄 DEVAM EDİYOR (%35)

**Başlangıç:** 25 Ekim 2025
**Hedef Bitiş:** Aralık 2025
**Durum:** Aktif Sprint - Finansal Modül Tamamlandı ✅
**Modül Sayısı:** 7 (1/7 tamamlandı)

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

### 2. Keşif & Metraj Yönetimi (0%) 📐 **PRİORİTE 2**

#### Hedef
Hakediş tutarlarını manuel değil, ölçülen metrajlardan otomatik hesaplamak.

#### Database
```sql
quantities (
    id, project_id, work_item_id,
    structure_id, floor_id, unit_id,  -- Opsiyonel lokasyon
    planned_quantity, completed_quantity,
    measurement_date, measurement_method,
    verified_by, approved_by, notes
)
```

#### Özellikler
- 📐 Proje keşfi yüklenebilir (Excel/BOQ import)
- 🔁 Gerçekleşen metraj kaydı (ölçüm bazlı)
- 🔗 Hakediş ile otomatik ilişki
- 📊 Keşif vs Gerçekleşen metraj raporu

#### Entegrasyon
- **Hakediş:** `completed_quantity` metrajdan çekilir
- **Finansal:** `realized_cost = completed_quantity * unit_price`
- **İş Kalemleri:** `planned_quantity` tanımlanır

#### Sprint Görevler
- [ ] Migration
- [ ] Quantity model
- [ ] ProgressPaymentController güncellemesi (metraj entegrasyonu)
- [ ] Frontend: Metraj giriş formu
- [ ] Keşif import/export (Excel)

---

### 3. Sözleşme Yönetimi (0%) 📄 **PRİORİTE 3**

#### Hedef
Taşeron, tedarikçi, müşteri ilişkilerini merkezi sözleşme sistemi ile yönetmek.

#### Database
```sql
contracts (
    id, contract_type,  -- subcontractor, supplier, consultant, customer
    related_id,  -- ilgili taraf ID'si
    contract_number, start_date, end_date,
    value, currency, status,
    termination_reason, notes
)
```

#### Entegrasyon
- **Hakediş:** `contract_id` referansı
- **Satınalma:** `contract_id` referansı
- **Satış:** `contract_id` referansı

#### Sprint Görevler
- [ ] Migration
- [ ] Contract model (polymorphic)
- [ ] Mevcut tablolara `contract_id` ekleme
- [ ] Sözleşme CRUD sayfaları
- [ ] Sözleşme süresi ve teminat takibi

---

### 4. Satış ve Tapu Yönetimi (0%) 🏘️

#### Database
```sql
customers (
    id, first_name, last_name, tc_number,
    phone, email, address, occupation, marital_status
)

unit_sales (
    id, unit_id, customer_id,
    sale_date, sale_price, payment_type,
    down_payment, installment_count, installment_amount,
    contract_number, contract_date,
    deed_transfer_date, deed_number,
    status  -- reserved, contracted, deed_transferred, cancelled
)

sale_payments (
    id, unit_sale_id, payment_date, due_date, amount,
    payment_type, payment_method, receipt_number,
    status  -- pending, paid, overdue, cancelled
)
```

#### Özellikler
- Müşteri CRM
- Rezervasyon/satış sözleşmeleri
- Ödeme planı ve taksit takibi
- Tapu devir işlemleri
- Blok/Kat/Daire satış durumu görselleştirme

#### Sprint Görevler
- [ ] Migrations
- [ ] Customer, UnitSale, SalePayment modelleri
- [ ] CRUD sayfaları
- [ ] Ödeme planı otomasyonu
- [ ] Satış durumu dashboard widget

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
- **Ruhsat revizyonları** (proje değişiklikleri) takibi

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
| Keşif & Metraj | 🔜 | %0 | 7 gün | - |
| Sözleşme Yönetimi | 🔜 | %0 | 5 gün | - |
| Satış ve Tapu | 🔜 | %0 | 10 gün | - |
| Ruhsat Yönetimi | 🔜 | %0 | 3 gün | - |
| Yapı Denetim | 🔜 | %0 | 3 gün | - |
| Stok Takibi | 🔜 | %0 | 3 gün | - |
| **TOPLAM** | **🔄** | **%35** | **36 gün** | **2 gün** |

---

**Son Güncelleme:** 26 Ekim 2025
**Versiyon:** 1.1
**Önceki Faz:** [Faz 1: Temel Altyapı](./faz1-temel-altyapi.md)
**Sonraki Faz:** [Faz 3: Gelişmiş Modüller](./faz3-gelismis-moduller.md)