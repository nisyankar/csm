# 🌐 SPT İNŞAAT - SaaS MİMARİ TASARIMI
## Evrensel & Özelleştirilebilir İnşaat Yönetim Platformu

---

## 🎯 HEDEF: EVRENSEL SaaS PLATFORMU

### **Vizyon**
Her ölçekte inşaat şirketinin kullanabileceği, sektörel standartlara uygun, ama özelleştirilebilir bir platform.

### **Temel Prensipler**
1. **🌍 Evrensellik:** Türkiye'deki tüm inşaat şirketleri için uygun
2. **🔧 Esneklik:** Her şirketin kendi iş akışını kurabilmesi
3. **📦 Modülerlik:** İhtiyaca göre modül aktif/pasif edilebilir
4. **🏢 Multi-Tenancy:** Her müşteri izole ortamda çalışır
5. **⚙️ Konfigürasyon Odaklı:** Kod değişikliği olmadan özelleştirme

---

## 🏗️ SEKTÖREL ANALİZ: FARKLI İNŞAAT FİRMALARI

### **Tip 1: Küçük İnşaat Firması** (1-50 çalışan)
**Örnek:** Yerel konut müteahhiti, küçük ölçekli projeler

**İhtiyaçlar:**
- ✅ Basit sözleşme yönetimi
- ✅ 2-3 taşeron
- ✅ Basit hakediş (aylık, götürü bedel)
- ✅ Temel puantaj
- ❌ Karmaşık EVM analizi gerekmez
- ❌ Risk yönetimi ihtiyacı düşük

**Kullanacağı Modüller:**
- Çalışan Yönetimi (Basit)
- Puantaj (Basit)
- Proje Yönetimi (Basit)
- Taşeron (Basit)
- Hakediş (Basit)

---

### **Tip 2: Orta Ölçekli İnşaat Firması** (50-200 çalışan)
**Örnek:** Bölgesel yapı kooperatifleri, küçük AVM, plaza projeleri

**İhtiyaçlar:**
- ✅ Detaylı sözleşme yönetimi
- ✅ 10-20 taşeron
- ✅ Birim fiyat sözleşmeler
- ✅ Aylık hakediş + kesintiler
- ✅ Malzeme ve ekipman takibi
- ✅ İhale süreci
- ⚠️ Risk yönetimi (orta seviye)

**Kullanacağı Modüller:**
- Tüm Temel Modüller
- Gelişmiş Hakediş
- Malzeme Yönetimi
- İhale Yönetimi
- Basit Risk Yönetimi

---

### **Tip 3: Büyük İnşaat Firması** (200+ çalışan, çoklu proje)
**Örnek:** TOKİ, büyük holding şirketleri, mega projeler

**İhtiyaçlar:**
- ✅ Çok detaylı sözleşme yönetimi
- ✅ 50+ taşeron portföyü
- ✅ Karmaşık ödeme şartları
- ✅ Detaylı hakediş ve fiyat farkları
- ✅ EVM ve S-Eğrisi analizleri
- ✅ Kapsamlı risk yönetimi
- ✅ ISO kalite yönetimi
- ✅ Çoklu proje yönetimi
- ✅ Kurumsal raporlama

**Kullanacağı Modüller:**
- TÜM MODÜLLER
- Gelişmiş Dashboard
- API Entegrasyonları
- Özel Raporlar
- Multi-proje Konsolidasyon

---

### **Tip 4: Özel Sektörler**

#### A. **Altyapı Firmaları** (Yol, köprü, tünel, baraj)
**Farkları:**
- Farklı iş kalemleri (kazı, dolgu, beton hacmi)
- Uzun süreli projeler (2-5 yıl)
- Ağır makine parkuru önemli
- Metraj tutanakları çok kritik
- Çevre izinleri ve raporlama

#### B. **Tadilat & Onarım Firmaları**
**Farkları:**
- Kısa süreli işler (1 gün - 2 hafta)
- Çok sayıda küçük proje
- Taşeron kullanımı az
- Hızlı fiyat teklifi
- Malzeme odaklı

#### C. **Dekorasyon & İç Mimarlık**
**Farkları:**
- Estetik odaklı
- Malzeme çeşitliliği çok fazla
- Numune onayları önemli
- Görsel döküman (render, fotoğraf)
- Ödeme planı farklı (peşinat, ara, son)

---

## 🏛️ EVRENSELLEŞTİRME STRATEJİSİ

### **1. ESNEK SÖZLEŞME TİPLERİ**

#### Mevcut Plan (Sabit)
```php
// ❌ Sınırlı
$contract_method = ['lump_sum', 'unit_price', 'mixed'];
```

#### SaaS İçin (Esnek)
```php
// ✅ Özelleştirilebilir
CREATE TABLE contract_types (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT,
    name VARCHAR(100), -- "Götürü Bedel", "Birim Fiyat", "Yüzde İmalat"
    code VARCHAR(50),
    calculation_method ENUM('lump_sum', 'unit_price', 'percentage', 'time_material', 'custom'),
    is_system_default BOOLEAN, -- Sistem varsayılanı mı?
    is_active BOOLEAN,
    settings JSON, -- Özel ayarlar
    created_at TIMESTAMP
);
```

**Örnek Kullanım:**
```json
{
  "calculation_method": "percentage",
  "settings": {
    "base_percentage": 15,
    "min_amount": 100000,
    "payment_terms": "monthly",
    "retention_percentage": 5
  }
}
```

---

### **2. ÖZELLEŞTİRİLEBİLİR HAKEDİŞ KESİNTİLERİ**

#### Mevcut Plan (Sabit)
```php
// ❌ Herkes için aynı
$deductions = ['kdv', 'stopaj', 'sgk', 'damga_vergisi'];
```

#### SaaS İçin (Esnek)
```sql
CREATE TABLE deduction_types (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT,
    name VARCHAR(100), -- "Stopaj", "SGK", "İş Kazası Sigortası"
    code VARCHAR(50),
    deduction_category ENUM('tax', 'insurance', 'penalty', 'retention', 'other'),
    calculation_type ENUM('percentage', 'fixed_amount', 'formula'),
    default_rate DECIMAL(5,2), -- Varsayılan oran
    default_amount DECIMAL(15,2), -- Varsayılan tutar
    formula TEXT, -- Özel formül: "gross_amount * 0.15 - previous_deductions"
    is_mandatory BOOLEAN,
    is_system_default BOOLEAN,
    applies_to ENUM('all', 'client_only', 'subcontractor_only'),
    legal_basis TEXT, -- "Gelir Vergisi Kanunu Md. 94"
    notes TEXT,
    is_active BOOLEAN,
    created_at TIMESTAMP
);
```

**Örnek Kullanım:**
```json
// Firma A: Normal inşaat
{
  "deductions": [
    {"code": "stopaj", "rate": 3},
    {"code": "sgk", "rate": 7.5},
    {"code": "kdv", "rate": 20}
  ]
}

// Firma B: Özel sektör (vergi muafiyeti)
{
  "deductions": [
    {"code": "sgk", "rate": 7.5},
    {"code": "isveren_sigortasi", "rate": 2}
  ]
}

// Firma C: Yurt dışı proje
{
  "deductions": [
    {"code": "foreign_tax", "rate": 10},
    {"code": "insurance", "amount": 5000}
  ]
}
```

---

### **3. SEKTÖRE ÖZEL İŞ KALEMLERİ (POZ LİSTESİ)**

#### Strateji: Şablon Kütüphanesi + Özel Ekleme

```sql
CREATE TABLE item_templates (
    id BIGINT PRIMARY KEY,
    category VARCHAR(100), -- "Bina", "Yol", "Tünel", "Dekorasyon"
    subcategory VARCHAR(100),
    item_code VARCHAR(50), -- "01.010", "02.020"
    description TEXT,
    unit VARCHAR(20),
    typical_unit_price DECIMAL(15,2), -- Referans fiyat (piyasa ortalaması)
    specification TEXT, -- Teknik şartname
    is_system_template BOOLEAN, -- Sistem varsayılanı
    source VARCHAR(100), -- "Çevre ve Şehircilik Bakanlığı 2024"
    created_at TIMESTAMP
);

CREATE TABLE tenant_custom_items (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT,
    template_id BIGINT, -- null ise tamamen özel
    custom_code VARCHAR(50),
    custom_description TEXT,
    unit VARCHAR(20),
    estimated_unit_price DECIMAL(15,2),
    notes TEXT,
    is_active BOOLEAN,
    created_at TIMESTAMP
);
```

**Sistem Şablonları:**
```
[BINA]
  01.010 - Kazı (m3)
  01.020 - Dolgu (m3)
  02.010 - Temel Betonu (m3)
  02.020 - Kolon Betonu (m3)

[YOL]
  10.010 - Üstyapı Kazısı (m3)
  10.020 - Granüler Dolgu (m3)
  10.030 - Asfalt Kaplama (ton)

[DEKORASYON]
  30.010 - Alçı Duvar (m2)
  30.020 - Parke Döşeme (m2)
```

**Her firma kendi ekler:**
```json
{
  "tenant_id": 123,
  "custom_items": [
    {
      "code": "OZEL.001",
      "description": "Akustik Panel Montajı",
      "unit": "adet",
      "price": 850.00
    }
  ]
}
```

---

### **4. TAŞERON KATEGORİLERİ - ESNEK YAPILANDIRMA**

```sql
CREATE TABLE subcontractor_category_templates (
    id BIGINT PRIMARY KEY,
    name VARCHAR(100), -- "Betonarme", "Elektrik", "Mekanik"
    parent_category_id BIGINT,
    industry_type VARCHAR(50), -- "building", "infrastructure", "decoration"
    is_system_default BOOLEAN,
    icon VARCHAR(50),
    color VARCHAR(20),
    created_at TIMESTAMP
);

CREATE TABLE tenant_subcontractor_categories (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT,
    template_id BIGINT, -- null ise özel kategori
    custom_name VARCHAR(100),
    parent_id BIGINT,
    required_certifications JSON, -- ["kapasite_raporu", "iso_9001"]
    is_active BOOLEAN,
    created_at TIMESTAMP
);
```

**Örnekler:**

```json
// Bina İnşaat Firması
{
  "categories": [
    "Betonarme",
    "Duvar",
    "Sıva",
    "Boya",
    "Elektrik",
    "Sıhhi Tesisat",
    "Doğalgaz",
    "Asansör"
  ]
}

// Altyapı Firması
{
  "categories": [
    "Kazı & Hafriyat",
    "Beton Dökümü",
    "Asfalt",
    "Çelik Konstrüksiyon",
    "İzolasyon",
    "Sinyalizasyon"
  ]
}

// Dekorasyon Firması
{
  "categories": [
    "Alçı İşleri",
    "Ahşap İşler",
    "Metal İşler",
    "Aydınlatma",
    "Mobilya",
    "Tekstil"
  ]
}
```

---

### **5. HAKEDİŞ DÖNEM VE KURALLAR - KONFİGÜRE EDİLEBİLİR**

```sql
CREATE TABLE tenant_payment_settings (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT,

    -- Hakediş Dönemi
    default_period ENUM('weekly', 'bi_weekly', 'monthly', 'bi_monthly', 'quarterly', 'milestone_based'),
    period_start_day INT, -- Ayın hangi günü (1-31)

    -- Ödeme Şartları
    payment_terms_days INT, -- Hakediş sonrası kaç gün içinde ödeme
    advance_payment_allowed BOOLEAN,
    advance_payment_max_percentage DECIMAL(5,2),
    retention_percentage DECIMAL(5,2), -- Kesinti yüzdesi (genelde %5-10)
    retention_release_on ENUM('completion', 'warranty_end', 'custom_date'),
    warranty_period_months INT,

    -- Fatura Kuralları
    invoice_required BOOLEAN,
    invoice_currency VARCHAR(3), -- "TRY", "USD", "EUR"
    supports_multi_currency BOOLEAN,

    -- Onay Süreci
    approval_workflow JSON, -- [{"level": 1, "role": "site_manager"}, {"level": 2, "role": "project_manager"}]

    -- Özel Kurallar
    custom_rules JSON,

    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Örnekler:**

```json
// Küçük Firma - Basit
{
  "default_period": "monthly",
  "payment_terms_days": 30,
  "retention_percentage": 5,
  "approval_workflow": [
    {"level": 1, "role": "project_manager"}
  ]
}

// Büyük Firma - Karmaşık
{
  "default_period": "bi_monthly",
  "payment_terms_days": 45,
  "retention_percentage": 10,
  "advance_payment_allowed": true,
  "advance_payment_max_percentage": 20,
  "approval_workflow": [
    {"level": 1, "role": "site_manager", "threshold": 100000},
    {"level": 2, "role": "project_manager", "threshold": 500000},
    {"level": 3, "role": "cfo", "threshold": "unlimited"}
  ],
  "custom_rules": {
    "price_escalation": true,
    "price_indices": ["tuik_construction", "currency_basket"],
    "payment_holdback_on_delay": true
  }
}

// Yurt Dışı Proje
{
  "default_period": "milestone_based",
  "invoice_currency": "USD",
  "supports_multi_currency": true,
  "retention_percentage": 0,
  "custom_rules": {
    "letter_of_credit_required": true,
    "bank_guarantee_percentage": 15
  }
}
```

---

## 🏢 MULTI-TENANCY MİMARİSİ

### **Seçenek 1: Database Per Tenant (Önerilen)**

**Yapı:**
```
tenant_1_spt     (Firma A'nın veritabanı)
tenant_2_spt     (Firma B'nin veritabanı)
tenant_3_spt     (Firma C'nin veritabanı)
central_spt      (Merkezi yönetim, tenant listesi, billing)
```

**Avantajlar:**
- ✅ Tam izolasyon (güvenlik)
- ✅ Yedekleme ve restore kolaylığı
- ✅ Performans (her firma kendi kaynakları)
- ✅ Veri taşıma kolaylığı (ihracat)
- ✅ Özelleştirme esnekliği

**Dezavantajlar:**
- ❌ Schema güncellemeleri tüm DB'lere yapılmalı
- ❌ Daha fazla sunucu kaynağı

---

### **Seçenek 2: Shared Database with Tenant ID**

**Yapı:**
```
spt_database
  - projects (tenant_id ile filtreleme)
  - employees (tenant_id ile filtreleme)
  - contracts (tenant_id ile filtreleme)
```

**Avantajlar:**
- ✅ Tek schema güncelleme
- ✅ Daha az kaynak
- ✅ Kolay konsolidasyon

**Dezavantajlar:**
- ❌ Güvenlik riski (tenant karışması)
- ❌ Performans (büyük tablolar)
- ❌ Özelleştirme zorluğu

---

### **HİBRİT YAKLAŞIM (Önerilen)**

```
[SHARED - Central DB]
- tenants
- subscriptions
- payments
- system_templates (poz listesi, kategoriler)
- user_authentications

[TENANT SPECIFIC - Per Tenant DB]
- projects
- employees
- contracts
- progress_payments
- subcontractors
- tenant_settings
- tenant_custom_templates
```

**Kod Yapısı:**
```php
// Tenant belirleme (Middleware)
class TenantMiddleware
{
    public function handle($request, Closure $next)
    {
        $tenant = $this->identifyTenant($request);

        // Tenant veritabanına geç
        config(['database.connections.tenant.database' => "tenant_{$tenant->id}_spt"]);
        DB::connection('tenant')->reconnect();

        // Tenant'ı request'e ekle
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}

// Model kullanımı
class Project extends Model
{
    protected $connection = 'tenant'; // Tenant DB kullan
}

class SystemTemplate extends Model
{
    protected $connection = 'mysql'; // Merkezi DB kullan
}
```

---

## ⚙️ KONFİGÜRASYON SİSTEMİ

### **Tenant Ayarları Tablosu**

```sql
CREATE TABLE tenant_settings (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT,
    category VARCHAR(50), -- 'general', 'contract', 'payment', 'approval', 'notification'
    setting_key VARCHAR(100),
    setting_value JSON,
    data_type VARCHAR(20), -- 'string', 'number', 'boolean', 'json', 'array'
    is_editable_by_admin BOOLEAN,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    UNIQUE(tenant_id, category, setting_key)
);
```

**Örnek Ayarlar:**

```json
// Genel Ayarlar
{
  "category": "general",
  "settings": {
    "company_name": "ABC İnşaat Ltd.",
    "tax_number": "1234567890",
    "currency": "TRY",
    "language": "tr",
    "timezone": "Europe/Istanbul",
    "fiscal_year_start": "01-01",
    "logo_url": "/storage/logos/abc.png"
  }
}

// Sözleşme Ayarları
{
  "category": "contract",
  "settings": {
    "default_contract_type": "unit_price",
    "contract_numbering_format": "SZL-{year}-{sequence}",
    "auto_calculate_vat": true,
    "vat_rate": 20,
    "enable_price_escalation": true,
    "escalation_formula": "custom",
    "require_guarantee": true,
    "guarantee_percentage": 6
  }
}

// Hakediş Ayarları
{
  "category": "payment",
  "settings": {
    "default_period": "monthly",
    "numbering_format": "HKD-{project_code}-{year}-{sequence}",
    "auto_deductions": ["stopaj", "sgk", "kdv"],
    "retention_percentage": 5,
    "approval_required": true,
    "approval_levels": 2,
    "payment_terms_days": 30
  }
}

// Onay Süreçleri
{
  "category": "approval",
  "settings": {
    "contract_approval": {
      "enabled": true,
      "workflow": [
        {"level": 1, "role": "project_manager", "threshold": null},
        {"level": 2, "role": "general_manager", "threshold": 1000000}
      ]
    },
    "payment_approval": {
      "enabled": true,
      "workflow": [
        {"level": 1, "role": "site_manager", "threshold": 50000},
        {"level": 2, "role": "project_manager", "threshold": 200000},
        {"level": 3, "role": "finance_director", "threshold": null}
      ]
    }
  }
}

// Bildirim Ayarları
{
  "category": "notification",
  "settings": {
    "email_notifications": true,
    "sms_notifications": false,
    "notify_on_payment_approval": true,
    "notify_on_payment_due": true,
    "due_date_reminder_days": [7, 3, 1]
  }
}
```

---

## 📦 PAKET VE FİYATLANDIRMA MODELİ

### **Katmanlı Yapı**

```
┌─────────────────────────────────────┐
│  ENTERPRISE                          │  5000+ TL/ay
│  • Sınırsız Proje                    │
│  • Sınırsız Kullanıcı                │
│  • Tüm Modüller                      │
│  • Özel Raporlar                     │
│  • API Erişimi                       │
│  • Öncelikli Destek                  │
│  • Özel Eğitim                       │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  PROFESSIONAL                        │  2000 TL/ay
│  • 10 Aktif Proje                    │
│  • 50 Kullanıcı                      │
│  • Gelişmiş Modüller                 │
│  • Standart Raporlar                 │
│  • Email Destek                      │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  STANDARD                            │  800 TL/ay
│  • 3 Aktif Proje                     │
│  • 20 Kullanıcı                      │
│  • Temel Modüller                    │
│  • Temel Raporlar                    │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  STARTER                             │  300 TL/ay
│  • 1 Aktif Proje                     │
│  • 10 Kullanıcı                      │
│  • Sadece Temel Özellikler           │
└─────────────────────────────────────┘
```

### **Modül Bazlı Eklentiler (Add-ons)**

```json
{
  "core_modules": [
    "Çalışan Yönetimi",
    "Puantaj",
    "Proje (Temel)"
  ],
  "addon_modules": [
    {"name": "Taşeron Yönetimi", "price": 200},
    {"name": "Sözleşme Yönetimi", "price": 300},
    {"name": "Hakediş Sistemi", "price": 400},
    {"name": "Malzeme Yönetimi", "price": 200},
    {"name": "İhale Yönetimi", "price": 150},
    {"name": "Risk Yönetimi", "price": 150},
    {"name": "Kalite Kontrol", "price": 150},
    {"name": "Gelişmiş Raporlama", "price": 250}
  ]
}
```

---

## 🔧 UYGULAMA STRATEJİSİ

### **Faz 1: MVP (Minimum Viable Product)**
**Hedef:** Tek tenant (kendi kullanımınız) için çalışan sistem

**Özellikler:**
- ✅ Sabit yapı
- ✅ Temel modüller
- ✅ Hızlı geliştirme
- ❌ Multi-tenancy yok
- ❌ Konfigürasyon sistemi basit

**Süre:** 3-4 ay

---

### **Faz 2: Multi-Tenant Dönüşümü**
**Hedef:** 5-10 pilot müşteri

**Değişiklikler:**
- ✅ Multi-tenancy mimarisi
- ✅ Temel konfigürasyon sistemi
- ✅ Tenant yönetim paneli
- ✅ Abonelik ve faturalandırma
- ⚠️ Henüz tam esnek değil

**Süre:** 2-3 ay

---

### **Faz 3: Tam Esneklik**
**Hedef:** 50+ müşteri, farklı sektörler

**Değişiklikler:**
- ✅ Gelişmiş konfigürasyon
- ✅ Şablon kütüphanesi
- ✅ Her firma kendi şablonları
- ✅ API ve entegrasyonlar
- ✅ White-label (özel markalaşma)

**Süre:** 3-4 ay

---

## 📊 KARŞILAŞTIRMA: MEVCUT vs SaaS

| Özellik | Mevcut Plan (Tek Firma) | SaaS Plan (Evrensel) |
|---------|-------------------------|----------------------|
| Veritabanı | 1 DB | N DB (per tenant) |
| Sözleşme Tipleri | Sabit 3 tip | Sınırsız özel tipler |
| Hakediş Kesintileri | Sabit 4-5 kesinti | Özelleştirilebilir |
| İş Kalemleri | Sabit liste | Şablon + Özel |
| Taşeron Kategorileri | Sabit liste | Sektöre özel |
| Onay Süreçleri | Sabit | Konfigüre edilebilir |
| Raporlar | Sabit | Özelleştirilebilir |
| Geliştirme Süresi | 6-8 ay | 12-15 ay |
| İlk Yatırım | Düşük | Yüksek |
| Ölçeklenebilirlik | Sınırlı | Sınırsız |
| Gelir Potansiyeli | Tek müşteri | Binlerce müşteri |

---

## ✅ ÖNERİM: HİBRİT YAKLAŞIM

### **Aşama 1: Kendimiz İçin (MVP)** - 4 Ay
```
✅ Sabit yapı
✅ Hızlı geliştirme
✅ Kendi ihtiyaçlarımıza göre
✅ Test ve kullanım
```

### **Aşama 2: SaaS'a Dönüştürme** - 3 Ay
```
✅ Multi-tenancy ekleme
✅ Temel konfigürasyon
✅ İlk 5 müşteriyle pilot
✅ Geri bildirim toplama
```

### **Aşama 3: Evrenselleştirme** - 4 Ay
```
✅ Tam esneklik
✅ Şablon sistemi
✅ Sektörel paketler
✅ Piyasaya açılma
```

**Toplam:** 11 ay (MVP'den SaaS'a)

---

## 🎯 SONUÇ

**Evet, taşeron-sözleşme-hakediş modülleri %100 evrensel olmalı!**

**Nasıl:**
1. ✅ **Konfigürasyon odaklı** tasarım
2. ✅ **Şablon + Özelleştirme** yaklaşımı
3. ✅ **Multi-tenant** mimari
4. ✅ **Modüler** yapı
5. ✅ **Sektöre özel** paketler

**Şimdi ne yapmalıyız?**

### **Seçenek A: MVP İle Başla (Önerilen)** 🚀
```
→ Kendi firmanız için çalışan bir sistem
→ 4 ayda hazır
→ Sonra SaaS'a dönüştür
```

### **Seçenek B: Direkt SaaS** ⚡
```
→ Baştan esnek mimari
→ 8-10 ayda hazır
→ Daha fazla yatırım
→ Daha büyük hedef pazar
```

**Hangisini tercih ediyorsunuz?**

Ben **Seçenek A**'yı öneriyorum:
1. Hızlı başlangıç
2. Kendi ihtiyaçlarınızı test edin
3. Gerçek kullanım deneyimi
4. Sonra SaaS'a dönüştürün
5. Risk düşük, öğrenme yüksek

**Kararınız nedir?** 🤔
