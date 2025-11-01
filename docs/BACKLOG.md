# Backlog - Mevcut Modül Geliştirmeleri

**Durum:** Planlama Aşaması
**Güncelleme:** 1 Kasım 2025

Bu dosya, mevcut modüllere eklenecek geliştirme ve iyileştirme taleplerini içerir.

---

## 🎯 Yüksek Öncelikli Geliştirmeler

### 1. Metraj Girişi UX İyileştirmeleri 📊

**Modül:** Metraj Yönetimi (Quantities)
**Öncelik:** Yüksek
**Talep Tarihi:** 1 Kasım 2025
**Durum:** Planlama

#### 1.1. Otomatik Alan Doldurma
**Sayfa:** `/quantities/create`

**Problem:**
- Kullanıcı blok/kat/birim seçtiğinde, metrekare bilgileri manuel olarak girilmek zorunda
- Birim (daire) tablosunda zaten gross_area ve net_area bilgisi mevcut
- Aynı veriyi iki kere girmek verimsiz ve hata riski taşıyor

**Çözüm:**
```javascript
// Vue component'te cascade dropdown watch
watch([selectedStructure, selectedFloor, selectedUnit], async ([structure, floor, unit]) => {
  if (unit?.id) {
    // Fetch unit details from API
    const response = await axios.get(`/api/units/${unit.id}`);

    // Auto-populate form fields
    form.gross_area = response.data.gross_area;
    form.net_area = response.data.net_area;

    // Show notification
    toast.info('Alan bilgileri otomatik dolduruldu (düzenleyebilirsiniz)');
  }
});
```

**API Endpoint (Yeni):**
```php
// routes/api.php
Route::get('/units/{unit}', [UnitController::class, 'show']);

// UnitController.php
public function show(ProjectUnit $unit)
{
    return response()->json([
        'id' => $unit->id,
        'unit_code' => $unit->unit_code,
        'gross_area' => $unit->gross_area,
        'net_area' => $unit->net_area,
        'balcony_area' => $unit->balcony_area,
        'unit_type' => $unit->unit_type,
        'full_code' => $unit->full_code,
    ]);
}
```

**Teknik Gereksinimler:**
- [ ] Backend: `GET /api/units/{id}` endpoint'i ekle
- [ ] Frontend: Vue watch() ile unit seçimini dinle
- [ ] Frontend: Otomatik doldurma + kullanıcı bildirimi
- [ ] UX: Doldurulmuş alanlar editlenebilir olmalı (kullanıcı override edebilmeli)
- [ ] Validasyon: Eğer unit'te alan bilgisi yoksa sessizce geç

**Fayda:**
- ⚡ Veri girişi %50 hızlanır
- ✅ Tutarlılık: birim tablosundaki alanlar kullanılır
- 🎯 Kullanıcı deneyimi iyileşir

---

#### 1.2. Toplu Metraj Girişi (Bulk Entry)
**Sayfa:** `/quantities/bulk-create` (yeni)

**Problem:**
- Büyük projelerde yüzlerce metraj girişi tek tek yapılıyor
- Blok → Kat → Birim hiyerarşisi her metraj için ayrı ayrı seçiliyor
- Zaman kaybı ve kullanıcı yorgunluğu

**Çözüm:**
Hiyerarşik grid görünümü ile toplu metraj girişi:

```
📐 TOPLU METRAJ GİRİŞİ

Proje: [Çamlıca Residence      ▼]
İş Kalemi: [Sıva İşleri        ▼]

┌─────────────────────────────────────────────────────────────────┐
│ Blok A                                                 [Kaydet]  │
│   └─ Zemin Kat                                                  │
│       ├─ Daire 1    │ Brüt: [120 m²] │ Net: [95 m²] │ [✓]      │
│       ├─ Daire 2    │ Brüt: [120 m²] │ Net: [95 m²] │ [✓]      │
│       └─ Daire 3    │ Brüt: [135 m²] │ Net: [110 m²]│ [✓]      │
│   └─ 1. Kat                                                     │
│       ├─ Daire 4    │ Brüt: [120 m²] │ Net: [95 m²] │ [✓]      │
│       └─ Daire 5    │ Brüt: [120 m²] │ Net: [95 m²] │ [✓]      │
│                                                                  │
│ Blok B                                                 [Kaydet]  │
│   └─ Zemin Kat                                                  │
│       ├─ Daire 1    │ Brüt: [140 m²] │ Net: [115 m²]│ [✓]      │
│       └─ Daire 2    │ Brüt: [140 m²] │ Net: [115 m²]│ [✓]      │
└─────────────────────────────────────────────────────────────────┘

[Tümünü Otomatik Doldur]  [Seçilenleri Kaydet]  [İptal]
```

**Görsel Tasarım:**
- DWG Import'taki hiyerarşik yapıya benzer
- Indentation: Blok (ml-0), Kat (ml-8), Birim (ml-16)
- Renk kodları: Blok (purple), Kat (blue), Birim (teal)
- Checkbox: Hangi birimlere metraj girilecek seçimi
- Inline editable fields: Brüt alan, Net alan, Miktar

**Veri Yapısı:**
```javascript
const bulkEntries = ref({
  project_id: 1,
  work_item_id: 5,
  entries: [
    {
      structure_id: 1,
      floor_id: 1,
      unit_id: 1,
      gross_area: 120,
      net_area: 95,
      quantity: 95,
      selected: true
    },
    // ... daha fazla entry
  ]
});
```

**Backend API:**
```php
// routes/web.php
Route::get('/quantities/bulk-create', [QuantityController::class, 'bulkCreate']);
Route::post('/quantities/bulk-store', [QuantityController::class, 'bulkStore']);

// QuantityController.php
public function bulkCreate(Request $request)
{
    $project = Project::with(['structures.floors.units'])->find($request->project_id);

    return Inertia::render('Quantities/BulkCreate', [
        'project' => $project,
        'workItems' => WorkItem::where('project_id', $project->id)->get(),
    ]);
}

public function bulkStore(Request $request)
{
    $validated = $request->validate([
        'project_id' => 'required|exists:projects,id',
        'work_item_id' => 'required|exists:work_items,id',
        'entries' => 'required|array|min:1',
        'entries.*.unit_id' => 'required|exists:project_units,id',
        'entries.*.gross_area' => 'nullable|numeric',
        'entries.*.net_area' => 'nullable|numeric',
        'entries.*.quantity' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        $created = [];

        foreach ($validated['entries'] as $entry) {
            $unit = ProjectUnit::find($entry['unit_id']);

            $quantity = Quantity::create([
                'project_id' => $validated['project_id'],
                'work_item_id' => $validated['work_item_id'],
                'structure_id' => $unit->structure_id,
                'floor_id' => $unit->floor_id,
                'unit_id' => $unit->id,
                'gross_area' => $entry['gross_area'] ?? $unit->gross_area,
                'net_area' => $entry['net_area'] ?? $unit->net_area,
                'quantity' => $entry['quantity'],
                'unit_type' => 'm2',
            ]);

            $created[] = $quantity;
        }

        DB::commit();

        return redirect('/quantities')->with('success', count($created) . ' adet metraj kaydı oluşturuldu.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Toplu kayıt sırasında hata: ' . $e->getMessage());
    }
}
```

**Teknik Gereksinimler:**
- [ ] Backend: `GET /quantities/bulk-create` route + view
- [ ] Backend: `POST /quantities/bulk-store` endpoint (transaction-safe)
- [ ] Frontend: Vue component `BulkCreate.vue`
- [ ] Frontend: Hiyerarşik ağaç görünümü (collapsible/expandable)
- [ ] Frontend: Inline editing (gross_area, net_area, quantity)
- [ ] Frontend: "Tümünü Otomatik Doldur" butonu (unit.gross_area/net_area'yı çek)
- [ ] Frontend: "Seçilenleri Kaydet" butonu (sadece checkbox işaretli olanlar)
- [ ] UX: Loading state, progress bar (çok kayıt varsa)
- [ ] UX: Error handling (hangi kayıtlar başarısız?)

**Fayda:**
- ⚡ Veri girişi 10x hızlanır (100 birim → 1 işlem)
- 🎯 Projelerde standart metraj şablonları kullanılabilir
- ✅ Tutarlılık: Tüm birimler aynı anda işlenir
- 🧠 Kullanıcı yorgunluğu azalır

**Referans:**
- DWG Import'taki hiyerarşik görünüm (resources/js/Pages/DwgImports/Review.vue)
- Excel benzeri inline editing
- Google Sheets benzeri bulk entry

---

## 🔄 Gelecek İyileştirmeler

### 2. Dashboard KPI Özelleştirme
**Modül:** Dashboard
**Öncelik:** Orta
**Durum:** Gelecek

Kullanıcıların dashboard'daki KPI widget'larını özelleştirebilmesi:
- Drag & drop widget sırası
- Widget göster/gizle
- Kişiselleştirilmiş metrikler

---

### 3. Gantt Şeması İyileştirmeleri
**Modül:** İş Programı
**Öncelik:** Orta
**Durum:** Gelecek

- Gantt üzerinde drag & drop ile tarih değiştirme
- Critical path görselleştirme
- Milestone marker'ları
- Zoom in/out (gün/hafta/ay görünümü)

---

### 4. Puantaj Mobil Fotoğraf Doğrulama
**Modül:** Puantaj
**Öncelik:** Düşük
**Durum:** Gelecek

- QR kod ile giriş/çıkış
- Selfie doğrulama (fraud prevention)
- GPS location tracking

---

## 📝 Notlar

- Bu geliştirmeler mevcut modüllere eklenir
- Faz 4'teki yeni modüllerden bağımsızdır
- Kullanıcı feedback'lerine göre öncelik değişebilir
- Her geliştirme için ayrı branch açılır
- Test coverage %80'in üzerinde olmalı

---

**İlgili Dosyalar:**
- [Faz 3: Gelişmiş Modüller](./faz3-gelismis-moduller.md)
- [Faz 4: İleri Seviye](./faz4-ileri-seviye.md)
- [API Test Guide](./API-TEST-GUIDE.md)
