# FAZ 2: Operasyonel Çekirdek
## 🔄 DEVAM EDİYOR (%15)

**Başlangıç:** Kasım 2025
**Hedef Bitiş:** Aralık 2025
**Durum:** Aktif Sprint
**Modül Sayısı:** 7

---

## 📋 GENEL BAKIŞ

Faz 2, inşaat operasyonlarının tamamını kapsayacak şekilde sistemi genişletir. Bu fazda, finansal yönetim, keşif/metraj, sözleşme yönetimi ve satış süreçleri entegre edilecektir.

### Hedef
İnşaat ERP'sinin operasyonel çekirdeğini tamamlamak: **Finansal sistem + Keşif/Metraj + Satış**

---

## 🎯 MODÜLLER

### 1. Finansal Yönetim ve Kar/Zarar Sistemi (0%) 💰 **PRİORİTE 1**

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

#### Otomatik Entegrasyon
- **Puantaj** → expense (personel maaş gideri)
- **Satınalma** → expense (malzeme gideri)
- **Hakediş** → expense (taşeron ödemesi)
- **Satış** → income (gelir kaydı)

#### Raporlar
- Proje bazlı kar/zarar
- Aylık/Yıllık gelir-gider
- Nakit akış raporu
- Bütçe vs gerçekleşen karşılaştırma
- Karlılık analizi

#### Sprint Görevler
- [ ] Migration'lar
- [ ] Model'ler ve ilişkiler
- [ ] FinancialTransactionService (otomatik kayıt)
- [ ] Event/Listener yapısı
- [ ] Dashboard widget'ları
- [ ] Raporlama sayfaları

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

### Sprint 1 (1-15 Kasım)
**Hedef:** Finansal Yönetim + Keşif & Metraj
- Finansal migrations ve modeller
- Otomatik kayıt servisleri (Event/Listener)
- Keşif & Metraj migrations
- Hakediş entegrasyonu

### Sprint 2 (16-30 Kasım)
**Hedef:** Sözleşme + Satış Yönetimi
- Contract sistemi
- Müşteri CRM
- Satış ve ödeme takibi

### Sprint 3 (1-15 Aralık)
**Hedef:** Ruhsat + Denetim + Stok
- Ruhsat yönetimi
- Yapı denetim sistemi
- Basit stok takibi

### Sprint 4 (16-31 Aralık)
**Hedef:** Test & Polish
- Entegrasyon testleri
- Dashboard widget'ları
- Raporlama sayfaları
- Bug fixes

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

**Son Güncelleme:** 25 Ekim 2025
**Versiyon:** 1.0
**Önceki Faz:** [Faz 1: Temel Altyapı](./faz1-temel-altyapi.md)
**Sonraki Faz:** [Faz 3: Gelişmiş Modüller](./faz3-gelismis-moduller.md)