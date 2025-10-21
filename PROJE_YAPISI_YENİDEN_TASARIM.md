# 🏗️ İNŞAAT PROJESİ YAPI YENİDEN TASARIMI

## 📋 MEVCUT DURUM ANALİZİ

### ❌ Tespit Edilen Sorunlar

#### 1. **Department Kavramı Yanlış Yorumlanmış**
Şu anki `departments` tablosu aslında:
- "İş fazları" veya "İş kalemleri" için kullanılmış
- Örnek tipler: `structural`, `mechanical`, `electrical`, `finishing`
- Bu, proje **işlerini** temsil ediyor, **fiziksel yapıyı** değil

**Sorun:** Gerçek inşaatlarda:
```
❌ ŞU ANKİ YAPI:
Proje → Department (Elektrik işleri)
         → Hangi blokta?
         → Hangi katta?
         → Hangi dairede?
         CEVAP YOK!

✅ OLMASI GEREKEN:
Proje → Blok A → Kat 5 → Daire 12 → Elektrik İşleri
                                   → Sıva İşleri
                                   → Boya İşleri
```

#### 2. **Fiziksel Yapı Hiyerarşisi Eksik**

**Gerçek Senaryo:**
```
📍 "Yıldız Residence" Projesi:
├── 🏢 A Blok (12 katlı, 120 daire)
│   ├── Bodrum -2 (Otopark)
│   ├── Bodrum -1 (Depo + Teknik Hacimler)
│   ├── Zemin Kat (Dükkanlar: 6 adet)
│   ├── Kat 1-10 (Her katta 12 daire)
│   │   ├── 🏠 A-101 (2+1, 85m²)
│   │   │   ├── ✅ Kaba inşaat: %100
│   │   │   ├── 🔄 İnce inşaat: %60
│   │   │   │   ├── Sıva: ✅ Tamamlandı
│   │   │   │   ├── Alçıpan: 🔄 Devam ediyor
│   │   │   │   └── Fayans: ⏳ Başlanmadı
│   │   │   ├── ⚡ Elektrik: %40
│   │   │   ├── 💧 Tesisat: %70
│   │   │   └── 🎨 Bitirme: %0
│   │   ├── 🏠 A-102 (3+1, 120m²)
│   │   └── ... (10 daire daha)
│   └── Çatı Katı
├── 🏢 B Blok (10 katlı, 90 daire)
└── 🏢 C Blok (8 katlı, 60 daire)
```

**Şu anki sistemde bu hiyerarşi tanımlanamıyor!**

#### 3. **Taşeron Atamaları Yetersiz**

```
Gerçek İhtiyaç:
"Mustafa Elektrik" → A Bloku'nun tüm elektrik işlerini yapıyor
"Ahmet İnşaat" → B Bloku'nun kaba inşaatını yapıyor
"Mehmet Sıva" → Tüm projenin iç sıva işlerini yapıyor
```

Şu anki `project_subcontractor` tablosu sadece:
- Proje bazında atama yapabiliyor
- Hangi blok/kat/iş kalemi olduğu kayıtlı değil

#### 4. **Puantaj Sistemi Eksik**

```
Gerekli Bilgi:
"Ali, bugün A Blok, 5. Kat, Daire 12'de elektrik tesisatı çekti"

Şu anki sistem:
"Ali, bugün Yıldız Residence projesinde çalıştı"
❌ Nerede?
❌ Ne iş yaptı?
```

---

## 🎯 YENİ YAPI TASARIMI

### 1️⃣ VERİ MODELİ HİYERAŞİSİ

```
projects (Ana Proje)
  ↓
project_structures (Bloklar/Binalar)
  ↓
project_floors (Katlar) [Opsiyonel]
  ↓
project_units (Daireler/Mağazalar/Ofisler) [Opsiyonel]
  ↓
work_categories (İş Kategorileri: Kaba İnşaat, İnce İnşaat, Tesisat...)
  ↓
work_items (İş Kalemleri: Kazı, Kalıp, Sıva, Elektrik...)
  ↓
work_item_assignments (İş Atamaları: Belirli bir birime, belirli bir işi atama)
  ↓
work_progress (İş İlerleme Kayıtları)
```

### 2️⃣ TABLO DETAYLARI

#### 📊 `project_structures` (Bloklar/Binalar)

```sql
- id
- project_id (foreign → projects)
- code (A, B, C, 1, 2, vb.)
- name (A Blok, B Blok, Ana Bina, vb.)
- structure_type (residential_block, office_block, commercial, villa, infrastructure)
- total_floors (Toplam kat sayısı)
- total_units (Toplam birim sayısı: daire, ofis, vs.)
- total_area (m²)
- status (not_started, in_progress, completed)
- planned_start_date
- planned_end_date
- actual_start_date
- actual_end_date
- supervisor_id (foreign → employees)
- notes
- metadata (JSON: Özel alanlar)
- timestamps, soft_deletes
```

**Örnek Veri:**
```php
[
    'code' => 'A',
    'name' => 'A Blok',
    'structure_type' => 'residential_block',
    'total_floors' => 12,
    'total_units' => 120,
    'total_area' => 14500.00,
]
```

---

#### 📊 `project_floors` (Katlar) [OPSIYONEL]

```sql
- id
- structure_id (foreign → project_structures)
- floor_number (INT: -2, -1, 0, 1, 2, ... zemin=0, bodrum negatif)
- floor_name (Bodrum -2, Bodrum -1, Zemin, Kat 1, Çatı)
- floor_type (basement, ground, standard, roof, technical)
- total_units (Bu kattaki birim sayısı)
- floor_area (m²)
- height (Kat yüksekliği - metre)
- status (not_started, in_progress, completed)
- planned_start_date
- planned_end_date
- actual_start_date
- actual_end_date
- notes
- timestamps, soft_deletes
```

**Ne Zaman Kullanılır?**
- Çok katlı binalar (10+ kat)
- Her katta farklı tip işler yapılıyorsa
- Kat bazında ilerleme takibi gerekiyorsa

**Örnek Veri:**
```php
[
    'floor_number' => 5,
    'floor_name' => 'Kat 5',
    'floor_type' => 'standard',
    'total_units' => 12,
    'floor_area' => 1200.00,
    'height' => 3.00,
]
```

---

#### 📊 `project_units` (Daireler/Ofisler/Mağazalar) [OPSIYONEL]

```sql
- id
- structure_id (foreign → project_structures)
- floor_id (foreign → project_floors) [NULL ise kat yok]
- unit_code (A-101, B-205, D12, vb.)
- unit_type (apartment, office, shop, warehouse, parking_space, technical_room)
- room_configuration (2+1, 3+1, 4+2, studio, vb.)
- gross_area (m² - Brüt alan)
- net_area (m² - Net alan)
- balcony_area (m²)
- garden_area (m²)
- direction (north, south, east, west, northeast, vb.)
- status (not_started, in_progress, completed, delivered)
- planned_completion_date
- actual_completion_date
- owner_name (Satılmışsa alıcı adı)
- owner_contact (İletişim)
- notes
- metadata (JSON)
- timestamps, soft_deletes
```

**Ne Zaman Kullanılır?**
- Daire bazında takip gerekiyorsa
- Satış/Teslimat takibi yapılacaksa
- Her daire farklı özellik/durumdaysa

**Örnek Veri:**
```php
[
    'unit_code' => 'A-501',
    'unit_type' => 'apartment',
    'room_configuration' => '3+1',
    'gross_area' => 120.00,
    'net_area' => 105.00,
    'balcony_area' => 8.50,
    'direction' => 'south',
]
```

---

#### 📊 `work_categories` (İş Kategorileri)

```sql
- id
- name (Kaba İnşaat, İnce İnşaat, Tesisat, Bitirme İşleri)
- code (KAB, INC, TST, BTM)
- description
- icon (FontAwesome icon adı)
- color (#hex renk kodu)
- order (Sıralama)
- is_active
- timestamps
```

**Sabit Kategoriler (Seed):**
```php
[
    ['code' => 'KAB', 'name' => 'Kaba İnşaat', 'icon' => 'fa-hard-hat'],
    ['code' => 'INC', 'name' => 'İnce İnşaat', 'icon' => 'fa-paint-roller'],
    ['code' => 'ELK', 'name' => 'Elektrik Tesisatı', 'icon' => 'fa-bolt'],
    ['code' => 'SUT', 'name' => 'Su Tesisatı', 'icon' => 'fa-faucet'],
    ['code' => 'ISI', 'name' => 'Isıtma-Soğutma', 'icon' => 'fa-temperature-half'],
    ['code' => 'BTM', 'name' => 'Bitirme İşleri', 'icon' => 'fa-check-circle'],
    ['code' => 'DIS', 'name' => 'Dış Cephe', 'icon' => 'fa-building'],
    ['code' => 'PEY', 'name' => 'Peyzaj', 'icon' => 'fa-tree'],
]
```

---

#### 📊 `work_items` (İş Kalemleri)

```sql
- id
- category_id (foreign → work_categories)
- name (Kazı, Temel, Kolon-Kiriş, Sıva, Fayans, Elektrik Panosu)
- code (KAZ, TML, KLN, SIV, FAY, vb.)
- description
- unit (m², m³, adet, metre, kg)
- estimated_duration_days (Tahmini süre)
- requires_approval (Onay gerekiyor mu?)
- order (Sıralama)
- is_active
- timestamps
```

**Örnek Kategoriye Göre İşler:**

**Kaba İnşaat:**
```php
['code' => 'KAZ', 'name' => 'Kazı İşleri', 'unit' => 'm³'],
['code' => 'TML', 'name' => 'Temel', 'unit' => 'm³'],
['code' => 'KLN', 'name' => 'Kolon-Kiriş-Perde', 'unit' => 'm³'],
['code' => 'DOS', 'name' => 'Döşeme', 'unit' => 'm²'],
['code' => 'IZO', 'name' => 'İzolasyon', 'unit' => 'm²'],
```

**İnce İnşaat:**
```php
['code' => 'SIV', 'name' => 'Sıva', 'unit' => 'm²'],
['code' => 'ALC', 'name' => 'Alçıpan', 'unit' => 'm²'],
['code' => 'FAY', 'name' => 'Fayans-Seramik', 'unit' => 'm²'],
['code' => 'MER', 'name' => 'Mermer-Granit', 'unit' => 'm²'],
```

---

#### 📊 `work_item_assignments` (İş Atamaları)

**EN ÖNEMLİ TABLO!** - İş takibinin kalbi

```sql
- id
- project_id (foreign → projects)
- structure_id (foreign → project_structures) [NULL = Tüm proje]
- floor_id (foreign → project_floors) [NULL = Kat yok/tüm katlar]
- unit_id (foreign → project_units) [NULL = Birim yok/tüm birimler]
- work_item_id (foreign → work_items)

-- Taşeron/Ekip Bilgisi
- assignment_type (subcontractor, internal_team)
- subcontractor_id (foreign → subcontractors) [NULL ise dahili ekip]
- supervisor_id (foreign → employees) [Sorumlu şef/mühendis]

-- Miktar ve Maliyet
- quantity (Miktar: 500 m², 50 m³, vb.)
- unit_price (Birim fiyat)
- total_price (Toplam tutar = quantity * unit_price)
- completed_quantity (Tamamlanan miktar)
- remaining_quantity (Kalan miktar)

-- Tarih ve Durum
- status (not_started, in_progress, completed, on_hold, cancelled)
- priority (low, medium, high, critical)
- planned_start_date
- planned_end_date
- actual_start_date
- actual_end_date

-- İlerleme
- progress_percentage (0-100, otomatik hesaplanan veya manuel)
- last_progress_update (Son ilerleme güncellemesi)

-- Notlar
- notes
- metadata (JSON)
- timestamps, soft_deletes

-- İndeksler
INDEX (project_id, status)
INDEX (structure_id, work_item_id)
INDEX (subcontractor_id)
INDEX (supervisor_id)
UNIQUE (structure_id, floor_id, unit_id, work_item_id) [Aynı yere aynı iş 2 kez atanamaz]
```

**Örnek Senaryolar:**

```php
// Senaryo 1: "Mustafa Elektrik" tüm A Blok'un elektrik işini alıyor
[
    'structure_id' => 1, // A Blok
    'floor_id' => NULL, // Tüm katlar
    'unit_id' => NULL, // Tüm daireler
    'work_item_id' => 15, // Elektrik tesisatı
    'assignment_type' => 'subcontractor',
    'subcontractor_id' => 5, // Mustafa Elektrik
    'quantity' => 12000, // m² (tüm blok elektrik kablo uzunluğu)
    'unit_price' => 25.00,
    'total_price' => 300000.00,
]

// Senaryo 2: "Ahmet Sıva" sadece 5. kattaki dairelerin sıvalarını yapıyor
[
    'structure_id' => 1, // A Blok
    'floor_id' => 5, // Sadece 5. Kat
    'unit_id' => NULL, // Bu kattaki tüm daireler
    'work_item_id' => 8, // Sıva işi
    'assignment_type' => 'subcontractor',
    'subcontractor_id' => 8,
    'quantity' => 1200, // m²
    'unit_price' => 35.00,
    'total_price' => 42000.00,
]

// Senaryo 3: Dahili ekiple kazı işi
[
    'structure_id' => 2, // B Blok
    'floor_id' => NULL,
    'unit_id' => NULL,
    'work_item_id' => 1, // Kazı
    'assignment_type' => 'internal_team',
    'subcontractor_id' => NULL,
    'supervisor_id' => 12, // Mühendis Mehmet
    'quantity' => 1500, // m³
    'unit_price' => 0, // Dahili ekip
    'total_price' => 0,
]

// Senaryo 4: Belirli bir daireye özel iş (VIP müşteri)
[
    'structure_id' => 1, // A Blok
    'floor_id' => 10, // Kat 10
    'unit_id' => 120, // Daire A-1001 (penthouse)
    'work_item_id' => 25, // Özel mutfak montajı
    'assignment_type' => 'subcontractor',
    'subcontractor_id' => 20,
    'quantity' => 1, // adet
    'unit_price' => 50000.00,
    'total_price' => 50000.00,
]
```

---

#### 📊 `work_progress` (İş İlerleme Kayıtları)

Günlük/haftalık ilerleme raporları

```sql
- id
- assignment_id (foreign → work_item_assignments)
- reported_by (foreign → employees) [Kim rapor etti?]
- report_date (Tarih)
- completed_quantity (Bu raporda tamamlanan miktar)
- total_completed_quantity (Toplam tamamlanan)
- progress_percentage (İlerleme %)
- quality_rating (1-5: Kalite değerlendirmesi)
- issues (JSON: Sorunlar, gecikmeler)
- photos (JSON: Fotoğraf yolları)
- notes
- approved_by (foreign → employees)
- approved_at
- timestamps
```

**Örnek:**
```php
[
    'assignment_id' => 42,
    'reported_by' => 15, // Şantiye şefi
    'report_date' => '2025-10-20',
    'completed_quantity' => 120, // Bugün 120 m² sıva yapıldı
    'total_completed_quantity' => 850, // Toplamda 850 m²
    'progress_percentage' => 70.83, // %70.83 (850/1200)
    'quality_rating' => 4,
    'issues' => [
        'weather_delay' => 'Yağmur nedeniyle 2 saat kayıp',
        'material_shortage' => 'Alçı eksikliği var, yarın gelecek'
    ],
    'photos' => [
        '/storage/progress/2025-10-20/photo1.jpg',
        '/storage/progress/2025-10-20/photo2.jpg'
    ]
]
```

---

### 3️⃣ YENİ İLİŞKİLER

#### Taşeron İlişkileri

```php
// Model: Subcontractor
public function workAssignments()
{
    return $this->hasMany(WorkItemAssignment::class);
}

public function activeProjects()
{
    return $this->belongsToMany(Project::class, 'work_item_assignments')
        ->distinct();
}

public function totalContractValue()
{
    return $this->workAssignments()->sum('total_price');
}
```

#### Personel İlişkileri

**ESKİ:**
```php
Employee → Project (Çok belirsiz)
```

**YENİ:**
```php
// Puantaj tablosuna eklenecek alanlar
timesheets:
- structure_id (Hangi blok?)
- floor_id (Hangi kat?)
- unit_id (Hangi daire?)
- work_item_id (Ne iş yaptı?)
- assignment_id (Hangi iş ataması altında?)

// Örnek sorgu
$employee->timesheets()
    ->with(['structure', 'floor', 'unit', 'workItem'])
    ->where('work_date', today())
    ->get();

// Sonuç:
"Ali bugün A Blok, 5. Kat, Daire 12'de elektrik tesisatı çekti (8 saat)"
```

---

### 4️⃣ ESNEKLİK: FARKLI PROJE TİPLERİ

#### Konut Projesi (Rezidans)
```
Project → Structures (Bloklar) → Floors (Katlar) → Units (Daireler) → Work Items
```

#### Ticari Proje (AVM/Plaza)
```
Project → Structures (Binalar) → Floors (Katlar) → Units (Mağazalar/Ofisler) → Work Items
```

#### Villa Projesi
```
Project → Structures (Villalar) → Floors (Zemin/Üst Kat) [Opsiyonel] → Work Items
```

#### Altyapı Projesi (Yol/Köprü)
```
Project → Structures (Segmentler/Bölümler) → Work Items
(Floors ve Units kullanılmaz)
```

#### Restorasyon Projesi
```
Project → Structures (Bina Bölümleri) → Floors → Work Items
```

**Esneklik Sağlayan Alanlar:**
- `metadata` (JSON): Proje tipine özel alanlar
- `structure_type`: residential_block, villa, commercial, infrastructure, restoration
- `floor_id` ve `unit_id`: NULL olabilir (opsiyonel)

---

## 🔄 ESKİ ⟹ YENİ DÖNÜŞÜM STRATEJISI

### Seçenek 1: Mevcut Departments Tablosunu Koruma

```
departments → work_categories & work_items dönüşümü

Avantajlar:
✅ Mevcut veri korunur
✅ Eski kodlar çalışmaya devam eder

Dezavantajlar:
❌ Karmaşık migration
❌ İki farklı yaklaşım karışır
```

### Seçenek 2: Tamamen Yeni Başlangıç (ÖNERİLEN)

```
1. Yeni tabloları oluştur:
   - project_structures
   - project_floors
   - project_units
   - work_categories
   - work_items
   - work_item_assignments
   - work_progress

2. departments tablosunu SİL:
   - Henüz production'da veri yok
   - Temiz başlangıç
   - Daha sağlıklı yapı

3. DepartmentController ve Model'i yeniden yaz:
   - Hepsi yeni yapıya göre
```

---

## 📊 RAPORLAMA YETENEKLERİ

### Yeni Sistemde Elde Edilebilecek Raporlar

#### 1. Proje Genel Durum
```sql
SELECT
    s.name AS blok,
    wi.name AS iş_kalemi,
    wa.progress_percentage AS tamamlanma,
    wa.status AS durum,
    sc.company_name AS taşeron
FROM work_item_assignments wa
JOIN project_structures s ON wa.structure_id = s.id
JOIN work_items wi ON wa.work_item_id = wi.id
LEFT JOIN subcontractors sc ON wa.subcontractor_id = sc.id
WHERE wa.project_id = 1
ORDER BY s.code, wi.category_id, wi.order
```

**Sonuç:**
```
Blok   | İş Kalemi         | Tamamlanma | Durum       | Taşeron
-------|-------------------|------------|-------------|------------------
A Blok | Kazı              | 100%       | completed   | Dahili Ekip
A Blok | Temel             | 100%       | completed   | Ahmet İnşaat
A Blok | Kolon-Kiriş       | 85%        | in_progress | Ahmet İnşaat
A Blok | Sıva              | 60%        | in_progress | Mustafa Sıva
A Blok | Elektrik          | 40%        | in_progress | Yıldırım Elektrik
B Blok | Kazı              | 100%       | completed   | Dahili Ekip
B Blok | Temel             | 70%        | in_progress | Ahmet İnşaat
```

#### 2. Blok Bazında İlerleme
```php
ProjectStructure::with(['workAssignments.workItem'])
    ->where('project_id', 1)
    ->get()
    ->map(function($structure) {
        return [
            'blok' => $structure->name,
            'toplam_is' => $structure->workAssignments->count(),
            'tamamlanan' => $structure->workAssignments
                ->where('status', 'completed')->count(),
            'devam_eden' => $structure->workAssignments
                ->where('status', 'in_progress')->count(),
            'genel_ilerleme' => $structure->workAssignments
                ->avg('progress_percentage'),
        ];
    });
```

#### 3. Taşeron Performans
```php
Subcontractor::with(['workAssignments'])
    ->get()
    ->map(function($sub) {
        return [
            'taşeron' => $sub->company_name,
            'toplam_iş' => $sub->workAssignments->count(),
            'tamamlanan' => $sub->workAssignments
                ->where('status', 'completed')->count(),
            'devam_eden' => $sub->workAssignments
                ->where('status', 'in_progress')->count(),
            'toplam_tutar' => $sub->workAssignments->sum('total_price'),
            'ortalama_ilerleme' => $sub->workAssignments
                ->avg('progress_percentage'),
            'gecikmiş_iş' => $sub->workAssignments
                ->where('planned_end_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
        ];
    });
```

#### 4. Kat Bazında Detay (Belirli bir blok için)
```php
ProjectFloor::with(['units.workAssignments.workItem'])
    ->where('structure_id', 1) // A Blok
    ->orderBy('floor_number')
    ->get();
```

#### 5. Maliyet Analizi
```sql
SELECT
    wc.name AS kategori,
    SUM(wa.total_price) AS toplam_maliyet,
    SUM(wa.completed_quantity * wa.unit_price) AS ödenen,
    SUM((wa.quantity - wa.completed_quantity) * wa.unit_price) AS kalan
FROM work_item_assignments wa
JOIN work_items wi ON wa.work_item_id = wi.id
JOIN work_categories wc ON wi.category_id = wc.id
WHERE wa.project_id = 1
GROUP BY wc.id
```

---

## ✅ UYGULAMA PLANI

### AŞAMA 1: VERİ MODELİ OLUŞTURMA

**1.1. Migration'ları Oluştur**
```bash
php artisan make:migration create_project_structures_table
php artisan make:migration create_project_floors_table
php artisan make:migration create_project_units_table
php artisan make:migration create_work_categories_table
php artisan make:migration create_work_items_table
php artisan make:migration create_work_item_assignments_table
php artisan make:migration create_work_progress_table
```

**1.2. Model'leri Oluştur**
```bash
php artisan make:model ProjectStructure
php artisan make:model ProjectFloor
php artisan make:model ProjectUnit
php artisan make:model WorkCategory
php artisan make:model WorkItem
php artisan make:model WorkItemAssignment
php artisan make:model WorkProgress
```

**1.3. Seeder'ları Oluştur**
```bash
php artisan make:seeder WorkCategorySeeder
php artisan make:seeder WorkItemSeeder
```

**1.4. Mevcut Tabloları Güncelle**
```bash
# Puantaj sistemine yeni alanlar ekle
php artisan make:migration add_work_fields_to_timesheets_table

# Taşeron atamalarını kaldır (yeni sistemde work_item_assignments kullanılacak)
php artisan make:migration drop_project_subcontractor_table
```

**1.5. Department Tablosunu Kaldır**
```bash
# departments ile ilişkili tüm migration ve model'leri kaldır
# Eğer başka yerde kullanılıyorsa (şirket departmanları için), yeniden adlandır
```

---

### AŞAMA 2: MODEL İLİŞKİLERİ

**2.1. Project Model**
```php
// İlişkiler
public function structures() // Bloklar
public function workAssignments() // Tüm iş atamaları
public function activeWorkAssignments() // Devam eden işler
public function completedWorkAssignments() // Tamamlanan işler

// Hesaplamalar
public function getTotalProgressPercentage() // Genel ilerleme
public function getTotalBudget() // Toplam bütçe
public function getSpentAmount() // Harcanan
public function getRemainingBudget() // Kalan
```

**2.2. ProjectStructure Model**
```php
public function project()
public function floors()
public function units()
public function workAssignments()
public function supervisor()

// Hesaplamalar
public function getProgressPercentage()
public function getCompletedWorkCount()
public function getActiveWorkCount()
```

**2.3. WorkItemAssignment Model**
```php
public function project()
public function structure()
public function floor()
public function unit()
public function workItem()
public function subcontractor()
public function supervisor()
public function progressReports()

// Helper Methods
public function updateProgress($quantity, $notes)
public function complete()
public function hold($reason)
public function assignToSubcontractor($subcontractorId)
```

---

### AŞAMA 3: CONTROLLER'LAR

**3.1. Yeni Controller'lar**
```bash
php artisan make:controller ProjectStructureController --resource
php artisan make:controller WorkItemAssignmentController --resource
php artisan make:controller WorkProgressController --resource
```

**3.2. API Endpoint'leri**
```php
// routes/api.php veya routes/web.php

// Proje yapısı
GET    /api/projects/{project}/structures
POST   /api/projects/{project}/structures
PUT    /api/structures/{structure}
DELETE /api/structures/{structure}

// İş atamaları
GET    /api/projects/{project}/work-assignments
POST   /api/projects/{project}/work-assignments
PUT    /api/work-assignments/{assignment}
DELETE /api/work-assignments/{assignment}

// İlerleme raporları
GET    /api/work-assignments/{assignment}/progress
POST   /api/work-assignments/{assignment}/progress

// Raporlama
GET    /api/projects/{project}/progress-report
GET    /api/projects/{project}/cost-analysis
GET    /api/subcontractors/{subcontractor}/performance
GET    /api/structures/{structure}/status
```

---

### AŞAMA 4: FRONTEND (Blade Views)

**4.1. Proje Detay Sayfası**
```
resources/views/projects/show.blade.php
├── Genel Bilgiler
├── Bloklar Listesi (Tabs)
│   ├── A Blok
│   │   ├── Katlar (Accordion)
│   │   │   ├── Kat 1
│   │   │   │   ├── Daireler (Küçük kartlar)
│   │   │   │   └── İş Kalemleri (Tablo)
│   │   │   └── Kat 2...
│   │   └── Genel İş Atamaları (Tüm blok için)
│   ├── B Blok
│   └── C Blok
└── Genel İlerleme (Dashboard)
```

**4.2. İş Atama Sayfası**
```
resources/views/work-assignments/create.blade.php
├── Proje Seçimi
├── Blok Seçimi
├── Kat Seçimi (Opsiyonel)
├── Daire Seçimi (Opsiyonel)
├── İş Kalemi Seçimi (Kategoriye göre gruplu)
├── Taşeron/Ekip Seçimi
├── Miktar ve Fiyat
└── Tarih ve Notlar
```

**4.3. İlerleme Raporu Sayfası**
```
resources/views/work-progress/create.blade.php
├── İş Ataması Seçimi
├── Tamamlanan Miktar
├── Kalite Değerlendirmesi
├── Sorunlar ve Gecikmeler
├── Fotoğraf Yükleme
└── Onay Talep Et
```

**4.4. Taşeron Performans Sayfası**
```
resources/views/subcontractors/performance.blade.php
├── Genel Bilgiler
├── Aktif İşler
├── Tamamlanan İşler
├── Performans Metrikleri
│   ├── Zamanında Teslim Oranı
│   ├── Kalite Skoru
│   ├── Ortalama Gecikme
│   └── Toplam Ciro
└── Grafikler
```

---

### AŞAMA 5: MEVCUT SİSTEMİ GÜNCELLEMe

**5.1. Puantaj Sistemi**
```php
// database/migrations/..._add_work_fields_to_timesheets_table.php

Schema::table('timesheets', function (Blueprint $table) {
    $table->foreignId('structure_id')->nullable()
        ->constrained('project_structures')->onDelete('set null');

    $table->foreignId('floor_id')->nullable()
        ->constrained('project_floors')->onDelete('set null');

    $table->foreignId('unit_id')->nullable()
        ->constrained('project_units')->onDelete('set null');

    $table->foreignId('work_item_id')->nullable()
        ->constrained('work_items')->onDelete('set null');

    $table->foreignId('assignment_id')->nullable()
        ->constrained('work_item_assignments')->onDelete('set null');
});
```

**5.2. Timesheet Model Güncelleme**
```php
// app/Models/Timesheet.php

public function structure()
{
    return $this->belongsTo(ProjectStructure::class);
}

public function floor()
{
    return $this->belongsTo(ProjectFloor::class);
}

public function unit()
{
    return $this->belongsTo(ProjectUnit::class);
}

public function workItem()
{
    return $this->belongsTo(WorkItem::class);
}

public function assignment()
{
    return $this->belongsTo(WorkItemAssignment::class);
}

// Accessor: Nerede çalıştı?
public function getWorkLocationAttribute(): string
{
    $parts = [];

    if ($this->structure) {
        $parts[] = $this->structure->name;
    }

    if ($this->floor) {
        $parts[] = $this->floor->floor_name;
    }

    if ($this->unit) {
        $parts[] = $this->unit->unit_code;
    }

    if ($this->workItem) {
        $parts[] = $this->workItem->name;
    }

    return implode(' › ', $parts) ?: 'Belirtilmemiş';
}
```

---

## 🎨 ÖRNEK KULLANIM SENARYOLARI

### Senaryo 1: Yeni Proje Oluşturma

```php
// 1. Proje oluştur
$project = Project::create([
    'name' => 'Yıldız Residence',
    'project_code' => 'YLD-2025',
    // ... diğer alanlar
]);

// 2. Blokları ekle
$blokA = $project->structures()->create([
    'code' => 'A',
    'name' => 'A Blok',
    'structure_type' => 'residential_block',
    'total_floors' => 12,
    'total_units' => 120,
    'total_area' => 14500.00,
]);

$blokB = $project->structures()->create([
    'code' => 'B',
    'name' => 'B Blok',
    'structure_type' => 'residential_block',
    'total_floors' => 10,
    'total_units' => 90,
    'total_area' => 11000.00,
]);

// 3. Katları ekle (A Blok için)
for ($i = -2; $i <= 10; $i++) {
    $floorName = match(true) {
        $i == -2 => 'Bodrum -2 (Otopark)',
        $i == -1 => 'Bodrum -1 (Depo)',
        $i == 0 => 'Zemin Kat',
        $i > 0 => "Kat $i",
    };

    $floorType = match(true) {
        $i < 0 => 'basement',
        $i == 0 => 'ground',
        $i == 10 => 'roof',
        default => 'standard',
    };

    $blokA->floors()->create([
        'floor_number' => $i,
        'floor_name' => $floorName,
        'floor_type' => $floorType,
        'total_units' => $i > 0 ? 12 : 0,
        'floor_area' => 1200.00,
    ]);
}

// 4. Daireleri ekle (örnek: 1. kat)
$kat1 = $blokA->floors()->where('floor_number', 1)->first();

for ($daire = 1; $daire <= 12; $daire++) {
    $kat1->units()->create([
        'structure_id' => $blokA->id,
        'unit_code' => sprintf('A-%d%02d', 1, $daire),
        'unit_type' => 'apartment',
        'room_configuration' => $daire % 3 == 0 ? '3+1' : '2+1',
        'gross_area' => $daire % 3 == 0 ? 120.00 : 85.00,
        'net_area' => $daire % 3 == 0 ? 105.00 : 72.00,
        'balcony_area' => 8.00,
        'direction' => ['north', 'south', 'east', 'west'][rand(0, 3)],
    ]);
}

// 5. İş atamaları oluştur
// A Blok'un tamamı için kaba inşaat
WorkItemAssignment::create([
    'project_id' => $project->id,
    'structure_id' => $blokA->id,
    'work_item_id' => WorkItem::where('code', 'KAB')->first()->id,
    'assignment_type' => 'subcontractor',
    'subcontractor_id' => 5, // Ahmet İnşaat
    'supervisor_id' => 10,
    'quantity' => 14500, // m²
    'unit_price' => 450.00,
    'total_price' => 6525000.00,
    'planned_start_date' => '2025-01-15',
    'planned_end_date' => '2025-06-30',
]);

// Sadece 1. kattaki dairelerin elektrik işleri
WorkItemAssignment::create([
    'project_id' => $project->id,
    'structure_id' => $blokA->id,
    'floor_id' => $kat1->id,
    'work_item_id' => WorkItem::where('code', 'ELK')->first()->id,
    'assignment_type' => 'subcontractor',
    'subcontractor_id' => 8, // Yıldırım Elektrik
    'supervisor_id' => 12,
    'quantity' => 1200, // m² (kablo uzunluğu)
    'unit_price' => 25.00,
    'total_price' => 30000.00,
    'planned_start_date' => '2025-04-01',
    'planned_end_date' => '2025-05-15',
]);
```

---

### Senaryo 2: Günlük İlerleme Raporu

```php
// Şantiye şefi, günlük ilerleme raporu giriyor
$assignment = WorkItemAssignment::find(42); // A Blok, Kat 5, Sıva işi

WorkProgress::create([
    'assignment_id' => $assignment->id,
    'reported_by' => auth()->user()->employee->id,
    'report_date' => today(),
    'completed_quantity' => 120, // Bugün 120 m² tamamlandı
    'total_completed_quantity' => 850, // Toplamda 850 m²
    'progress_percentage' => (850 / 1200) * 100, // %70.83
    'quality_rating' => 4,
    'issues' => [
        'weather' => 'Sabah yağmur vardı, 2 saat kayıp',
        'material' => 'Alçı biraz geç geldi',
    ],
    'photos' => [
        '/storage/progress/2025-10-20/photo1.jpg',
        '/storage/progress/2025-10-20/photo2.jpg',
    ],
    'notes' => 'Genel olarak iyi gidiyor. Yarın bitirmek için ekstra eleman getirilecek.',
]);

// İş atamasını güncelle
$assignment->update([
    'completed_quantity' => 850,
    'remaining_quantity' => 350,
    'progress_percentage' => 70.83,
    'last_progress_update' => now(),
]);
```

---

### Senaryo 3: Puantaj Kaydı (Geliştirilmiş)

```php
// Eski sistem:
Timesheet::create([
    'employee_id' => 25,
    'project_id' => 1,
    'work_date' => today(),
    'hours_worked' => 8,
    // ❌ Nerede çalıştı? Ne yaptı? Belirsiz!
]);

// Yeni sistem:
$kat5 = ProjectFloor::where('floor_number', 5)
    ->where('structure_id', 1)
    ->first();

$sivaIsi = WorkItem::where('code', 'SIV')->first();

$assignment = WorkItemAssignment::where('structure_id', 1)
    ->where('floor_id', $kat5->id)
    ->where('work_item_id', $sivaIsi->id)
    ->first();

Timesheet::create([
    'employee_id' => 25,
    'project_id' => 1,
    'structure_id' => 1, // A Blok
    'floor_id' => $kat5->id, // 5. Kat
    'work_item_id' => $sivaIsi->id, // Sıva işi
    'assignment_id' => $assignment->id,
    'work_date' => today(),
    'hours_worked' => 8,
]);

// Sonuç:
echo $timesheet->work_location;
// "A Blok › Kat 5 › Sıva"
```

---

### Senaryo 4: Raporlama

```php
// Proje genel durumu
$project = Project::with([
    'structures.workAssignments.workItem.category',
    'structures.workAssignments.subcontractor'
])->find(1);

foreach ($project->structures as $structure) {
    echo "{$structure->name} - İlerleme: {$structure->progress_percentage}%\n";

    foreach ($structure->workAssignments as $assignment) {
        echo "  • {$assignment->workItem->name}: {$assignment->progress_percentage}%";
        echo " ({$assignment->subcontractor?->company_name ?? 'Dahili Ekip'})\n";
    }
}

// Çıktı:
// A Blok - İlerleme: 65%
//   • Kazı: 100% (Dahili Ekip)
//   • Temel: 100% (Ahmet İnşaat)
//   • Kolon-Kiriş: 85% (Ahmet İnşaat)
//   • Sıva: 60% (Mustafa Sıva)
//   • Elektrik: 40% (Yıldırım Elektrik)
// B Blok - İlerleme: 45%
//   • Kazı: 100% (Dahili Ekip)
//   • Temel: 70% (Ahmet İnşaat)
```

---

## 🚀 SONRAKI ADIMLAR

### Onayın İçin Bekleyen Kararlar:

1. **Department Tablosu:**
   - [ ] Tamamen sil ve yeni yapıya geç (ÖNERİLEN)
   - [ ] Korun ama "şirket departmanları" için yeniden adlandır
   - [ ] Migration ile dönüştür (karmaşık)

2. **Floors ve Units Opsiyonelliği:**
   - [ ] Her projede mutlaka kullanılsın
   - [ ] Proje tipine göre opsiyonel (ÖNERİLEN)

3. **Seed Data:**
   - [ ] Standart iş kategorileri ve kalemleri seed ile dolduralım mı?
   - [ ] Yoksa kullanıcı kendisi mi tanımlasın?

4. **Mevcut Veri:**
   - Şu an database'de projeler var mı?
   - Varsa örnek veri mi yoksa gerçek mi?

### Kodlamaya Geçmek İçin Onayın:

✅ **Bunu onayla, kodlamaya geçelim:**

"Tamam, bu plana göre devam et. Department tablosunu sil, yeni yapıyı oluştur. Floors ve Units opsiyonel olsun. Standart iş kategorilerini ve kalemlerini seed ile doldur."

Sonra sırasıyla:
1. Migration'ları oluşturacağım
2. Model'leri oluşturacağım
3. Seeder'ları yazacağım
4. Controller'ları yapacağım
5. View'ları tasarlayacağım

🎯 **Hazırım! Onayını bekliyorum.**

---

## ✅ FAZ 1 TAMAMLANDI (2025-10-20)

### 🎉 Tamamlanan İşler

#### 1. **Database Yapısı** ✅
- ✅ 8 Migration oluşturuldu ve çalıştırıldı
  - `project_structures` - Bloklar/Binalar
  - `project_floors` - Katlar
  - `project_units` - Daireler/Birimler
  - `work_categories` - İş Kategorileri
  - `work_items` - İş Kalemleri
  - `work_item_assignments` - İş Atamaları
  - `work_progress` - İlerleme Raporları (Onay Süreçli)
  - `timesheets` tablosuna yeni alanlar eklendi

#### 2. **Model İmplementasyonları** ✅
- ✅ 7 Model tam implementasyon
  - **ProjectStructure** - Relationships, Scopes, Helper Methods
  - **ProjectFloor** - Floor display, Statistics
  - **ProjectUnit** - Unit types, Progress calculation
  - **WorkCategory** - Active scope
  - **WorkItem** - Category relationship
  - **WorkItemAssignment** - Complex relationships, Auto-calculations
  - **WorkProgress** - Approval workflow, Issue tracking
- ✅ **Project** modeline yeni relationships eklendi
  - `structures()`, `floors()`, `units()`
  - `workAssignments()`, `workProgressReports()`

#### 3. **API Controllers** ✅
- ✅ **Faz 1 Controller'ları** (5 adet)
  - `ProjectStructureController` - 6 endpoints
  - `ProjectFloorController` - 5 endpoints
  - `ProjectUnitController` - 5 endpoints
  - `WorkItemAssignmentController` - 5 endpoints
  - `WorkProgressController` - 7 endpoints (approve/reject)

- ✅ **Core API Controller'ları** (4 adet)
  - `ProjectController` - 14 endpoints
  - `SubcontractorController` - 11 endpoints
  - `MaterialController` - 8 endpoints
  - `DepartmentController` - 10 endpoints

**Toplam: 9 Controller, ~70+ API Endpoint**

#### 4. **Routes** ✅
- ✅ `api.php` - Tüm API route'ları tanımlandı
- ✅ `web.php` - Web route'ları tanımlandı
- ✅ Role-based middleware uygulandı

#### 5. **Seeder'lar** ✅
- ✅ **WorkCategorySeeder** - 8 standart kategori
  - Kaba İnşaat (KAB)
  - İnce İnşaat (INC)
  - Elektrik Tesisatı (ELK)
  - Su Tesisatı (SUT)
  - Isıtma-Soğutma (ISI)
  - Dış Cephe (DIS)
  - Bitirme İşleri (BTM)
  - Peyzaj (PEY)

- ✅ **Phase1TestDataSeeder** - Demo veri
  - 1 Demo Proje (Şehir Konutları)
  - 3 Blok (A: 8 kat, B: 10 kat, C: 6 kat)
  - 24 Kat toplamda
  - 96 Daire (her katta 4 daire)
  - 13 İş Kalemi (Kazı, Temel, Sıva, vb.)
  - 4 Örnek İş Ataması (farklı statülerde)

#### 6. **Vite Build** ✅
- ✅ Frontend assets build edildi
- ✅ Dashboard açılıyor

### 🎯 Sistem Özellikleri

#### Proje Yapısı Hiyerarşisi
```
Proje
  └─ Yapılar (Bloklar: A, B, C...)
      ├─ Katlar (-2, -1, 0, 1, 2...)
      │   └─ Birimler (Daire 1, Daire 2...)
      └─ İş Atamaları
          ├─ Blok seviyesi (Tüm blok için)
          ├─ Kat seviyesi (Belirli kat için)
          └─ Daire seviyesi (Belirli daire için)
```

#### İş Takibi
- ✅ 8 İş kategorisi (özelleştirilebilir)
- ✅ İş kalemleri hiyerarşik yapı
- ✅ Esnek atama: Proje geneli / Blok / Kat / Daire
- ✅ Taşeron veya kendi ekibi seçimi
- ✅ İlerleme raporları (günlük/haftalık)
- ✅ Onay süreci (pending → approved/rejected)
- ✅ Otomatik progress hesaplama
- ✅ Kalite derecelendirme (1-5 yıldız)
- ✅ Sorun takibi (delay, quality, safety, vb.)
- ✅ Fotoğraf ekleme

#### API Endpoint Örnekleri
```bash
# Structures
GET    /api/v1/project-management/structures
POST   /api/v1/project-management/structures
GET    /api/v1/project-management/structures/{id}/progress

# Floors
GET    /api/v1/project-management/floors
POST   /api/v1/project-management/floors

# Units
GET    /api/v1/project-management/units
GET    /api/v1/project-management/units?structure_id=1&floor_id=3

# Work Assignments
GET    /api/v1/project-management/work-assignments
POST   /api/v1/project-management/work-assignments

# Work Progress
GET    /api/v1/project-management/work-progress
POST   /api/v1/project-management/work-progress
POST   /api/v1/project-management/work-progress/{id}/approve
POST   /api/v1/project-management/work-progress/{id}/reject

# Projects
GET    /api/v1/projects/{id}/dashboard
GET    /api/v1/projects/stats

# Subcontractors
GET    /api/v1/subcontractors/stats
POST   /api/v1/subcontractors/{id}/approve
POST   /api/v1/subcontractors/{id}/blacklist
```

### 📊 Test Data Özeti
```
✅ 1 Demo Proje
✅ 3 Yapı (Blok)
✅ 24 Kat
✅ 96 Birim (Daire)
✅ 13 İş Kalemi
✅ 4 İş Ataması (farklı statüler)
```

### 🚀 Sonraki Adımlar

#### Faz 1 Kalan İşler (Opsiyonel)
- [ ] Web UI sayfaları (Vue.js/Inertia)
- [ ] API endpoint testleri (Postman)
- [ ] WorkItemSeeder (daha fazla iş kalemi)
- [ ] Daha fazla test data

#### Faz 2 - Günlük Operasyonlar (Planlanan)
- [ ] Günlük Raporlar Modülü
- [ ] Hasar/Eksiklik Takip Modülü
- [ ] Denetim Kayıtları Modülü
- [ ] Fotoğraf Galerisi & Timelapse
- [ ] Ekipman Takibi
- [ ] Malzeme Talep Sistemi

#### Flutter Mobile App (Yeni)
- [ ] Mobile app mimarisi planlaması
- [ ] API authentication (Laravel Sanctum)
- [ ] Offline-first mimari
- [ ] Fotoğraf çekme ve upload
- [ ] QR kod okuma
- [ ] Puantaj girişi

---

**🎉 Faz 1 başarıyla tamamlandı ve sistem test edilmeye hazır!**

**Son Güncelleme:** 2025-10-20
**Durum:** ✅ Tamamlandı ve çalışıyor
**API Status:** ✅ 70+ endpoint hazır
**Test Data:** ✅ Yüklü ve kullanılabilir

