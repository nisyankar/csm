# Satınalma Talepleri Onay Mekanizması

## Genel Bakış

SPT İnşaat Puantaj Sistemi'nde satınalma talepleri, çok katmanlı bir onay mekanizmasıyla yönetilir. Bu dokümantasyon, sistemin nasıl çalıştığını, rolleri ve iş akışını detaylı olarak açıklar.

---

## Roller ve Yetkiler

### 1. **Talep Oluşturan Roller**
- **Şantiye Şefi (site_manager)**
- **Proje Yöneticisi (project_manager)**

**Yetkiler:**
- Yeni satınalma talebi oluşturma
- Taslak (draft) durumundaki talepleri düzenleme
- Taslak talepleri onaya gönderme
- Kendi oluşturdukları talepleri görüntüleme

### 2. **Onay Veren Roller**

#### Şef (Supervisor)
- **Kim:** Departman şefleri
- **Yetkiler:**
  - Beklemedeki (pending) talepleri onaylama
  - Talepleri reddetme
  - Onay notları ekleme

#### Yönetici (Manager / Admin / HR)
- **Kim:** Üst yönetim, admin, İK
- **Yetkiler:**
  - Beklemedeki (pending) talepleri onaylama
  - Talepleri reddetme
  - Onay notları ekleme
  - Tüm talepleri görüntüleme

### 3. **Satınalma Sorumlusu**
- Onaylanmış talepleri sipariş olarak işleme
- Malzeme girişlerini kaydetme
- Tedarikçi ilişkilerini yönetme

---

## Durum Geçişleri (Status Flow)

```
┌─────────────────────────────────────────────────────────┐
│                    YENİ TALEP                          │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
            ┌──────────┐
            │  draft   │  (Taslak)
            └────┬─────┘
                 │
          [submit()]
                 │
                 ▼
            ┌──────────┐
            │ pending  │  (Beklemede/Onay Aşamasında)
            └────┬─────┘
                 │
        ┌────────┴────────┐
        │                 │
   [Şef Onayı]      [Yönetici Onayı]
        │                 │
        ▼                 ▼
    approved_by_     approved_by_
    supervisor       manager
        │                 │
        └────────┬────────┘
                 │
        (Her ikisi de onayladıysa)
                 │
                 ▼
            ┌──────────┐
            │ approved │  (Onaylandı)
            └────┬─────┘
                 │
                 ▼
            ┌──────────┐
            │ ordered  │  (Sipariş Verildi)
            └────┬─────┘
                 │
        ┌────────┴────────┐
        │                 │
        ▼                 ▼
  ┌──────────┐      ┌──────────┐
  │partially │      │ received │  (Teslim Alındı)
  │_received │      └──────────┘
  └──────────┘

VEYA

   pending ──[reject()]──> rejected  (Reddedildi)

   draft/pending/approved ──[cancel()]──> cancelled  (İptal Edildi)
```

---

## İş Akışı Detayları

### 1. Talep Oluşturma (draft)

**Yapan:** Şantiye Şefi veya Proje Yöneticisi

**Adımlar:**
1. Yeni talep formu doldurulur
2. Proje seçilir
3. Departman seçilir (opsiyonel)
4. Malzemeler eklenir:
   - Kayıtlı malzemelerden seçilebilir (otomatik doldurma)
   - Manuel giriş yapılabilir
5. Aciliyet durumu belirlenir (low, medium, high, urgent)
6. Kategori seçilir (consumables, equipment, tools, materials, services, other)
7. Talep edilme tarihi belirlenir

**Durum:** `draft`

**Özellikler:**
- Taslak kaydedilebilir ve daha sonra düzenlenebilir
- Onaya gönderilmeden silinebilir
- Birden fazla kez düzenlenebilir

---

### 2. Onaya Gönderme (pending)

**Yapan:** Talep Oluşturan

**Adımlar:**
1. Taslak talebi gözden geçir
2. "Onaya Gönder" butonuna tıkla
3. Sistem otomatik olarak:
   - Durumu `pending` yapar
   - `submitted_at` zaman damgası ekler
   - İlgili şef ve yöneticilere bildirim gönderir

**Durum Değişimi:** `draft` → `pending`

**Kısıtlamalar:**
- Onaya gönderildikten sonra düzenlenemez
- Sadece onay verenler veya talep sahibi iptal edebilir

---

### 3. Onay Süreci (pending → approved)

SPT sisteminde **çift onay mekanizması** vardır:

#### 3.1. Şef Onayı

**Yapan:** Departman Şefi

**Metod:** `approveBySupervisor($userId, $notes)`

**Adımlar:**
1. Şef beklemedeki talebi görüntüler
2. Malzemeleri ve miktarları kontrol eder
3. Onay verir veya reddeder
4. Opsiyonel not ekleyebilir

**Veritabanı Değişiklikleri:**
```php
approved_by_supervisor = user_id
supervisor_notes = "Onay notu"
// Eğer manager de onaylamışsa:
status = 'approved'
approved_at = now()
```

#### 3.2. Yönetici Onayı

**Yapan:** Proje Yöneticisi, Admin veya İK

**Metod:** `approveByManager($userId, $notes)`

**Adımlar:**
1. Yönetici beklemedeki talebi görüntüler
2. Bütçe kontrolü yapar
3. Şef onayını kontrol eder
4. Onay verir veya reddeder
5. Opsiyonel not ekleyebilir

**Veritabanı Değişiklikleri:**
```php
approved_by_manager = user_id
manager_notes = "Onay notu"
// Eğer supervisor de onaylamışsa:
status = 'approved'
approved_at = now()
```

**ÖNEMLİ:** Talep tam onaylanmış sayılması için **hem şef hem yönetici** onayı gereklidir. Onay sırası önemli değildir.

**Durum Değişimi:** `pending` → `approved` (her iki onay da alındığında)

---

### 4. Reddetme (rejected)

**Yapan:** Şef veya Yönetici

**Metod:** `reject($reason)`

**Adımlar:**
1. Onay veren talep detayını görüntüler
2. Red nedeni zorunlu olarak girilir
3. "Reddet" butonuna tıklar

**Veritabanı Değişiklikleri:**
```php
status = 'rejected'
rejection_reason = "Red nedeni açıklaması"
```

**Durum Değişimi:** `pending` → `rejected`

**Özellikler:**
- Reddedilen talepler silinemez (kayıt tutmak için)
- Talep sahibi red nedenini görebilir
- Reddedilen talep tekrar onaya gönderilemez
- Yeni talep oluşturulması gerekir

---

### 5. Sipariş Verme (ordered)

**Yapan:** Satınalma Sorumlusu

**Adımlar:**
1. Onaylanmış talepleri listeler
2. Tedarikçi seçer
3. Sipariş detaylarını girer:
   - Sipariş numarası
   - Tedarikçi bilgileri
   - Fiyat teklifi
   - Tahmini teslimat tarihi
4. Sipariş verildiğini işaretler

**Durum Değişimi:** `approved` → `ordered`

**Not:** Bu özellik henüz tam implement edilmemiş olabilir.

---

### 6. Malzeme Girişi (partially_received / received)

**Yapan:** Depo Sorumlusu

**Adımlar:**
1. Malzeme geldiğinde kayıt yapar
2. Gelen miktarları işaretler
3. Kalite kontrolü yapar
4. Sistem otomatik durum günceller:
   - Tüm kalemler tamamsa: `received`
   - Bazı kalemler eksikse: `partially_received`

**Durum Değişimi:**
- `ordered` → `partially_received` (kısmi teslimat)
- `ordered` → `received` (tam teslimat)
- `partially_received` → `received` (kalan teslimat)

---

### 7. İptal Etme (cancelled)

**Yapan:** Talep Sahibi, Şef, Yönetici veya Admin

**Metod:** `cancel()`

**İptal Edilebilir Durumlar:**
- `draft`
- `pending`
- `approved`

**İptal Edilemez Durumlar:**
- `ordered` (Sipariş verilmiş)
- `partially_received` (Kısmi teslimat alınmış)
- `received` (Teslimat tamamlanmış)
- `rejected` (Zaten reddedilmiş)
- `cancelled` (Zaten iptal edilmiş)

**Durum Değişimi:** `draft/pending/approved` → `cancelled`

---

## Teknik Detaylar

### Veritabanı Şeması

**purchasing_requests tablosu:**

```php
// Temel Bilgiler
id
request_number          // Otomatik: PR-YYYYMMDD-XXXX
requested_by            // Talep eden kullanıcı ID
project_id              // İlgili proje
department_id           // İlgili departman (nullable)

// Durum ve Kategori
status                  // draft, pending, approved, rejected, ordered, etc.
urgency                 // low, medium, high, urgent
category                // consumables, equipment, tools, materials, etc.

// Tarihler
required_date           // Malzeme gerekli olduğu tarih
submitted_at            // Onaya gönderilme tarihi
approved_at             // Tam onay tarihi

// Onay Bilgileri
approved_by_supervisor  // Şef onayı (user_id)
supervisor_notes        // Şef onay notu
approved_by_manager     // Yönetici onayı (user_id)
manager_notes           // Yönetici onay notu
rejection_reason        // Red nedeni

// Diğer
description             // Genel açıklama
notes                   // Ek notlar
total_estimated_cost    // Toplam tahmini maliyet

created_at
updated_at
```

**purchasing_items tablosu:**

```php
id
purchasing_request_id   // İlgili talep
material_id             // Bağlı malzeme (nullable)
item_name               // Malzeme adı
description             // Açıklama
quantity                // Miktar
unit                    // Birim (adet, kg, m, etc.)
estimated_unit_price    // Birim fiyat tahmini
total_price             // Toplam fiyat (quantity × unit_price)
specification           // Teknik şartname
category                // Kategori
```

---

## API Endpoint'leri

### Liste ve Görüntüleme
```
GET  /purchasing-requests              # Talep listesi
GET  /purchasing-requests/create       # Yeni talep formu
GET  /purchasing-requests/{id}         # Talep detayı
GET  /purchasing-requests/{id}/edit    # Talep düzenleme formu
```

### İşlemler
```
POST   /purchasing-requests            # Yeni talep oluştur
PUT    /purchasing-requests/{id}       # Talebi güncelle
DELETE /purchasing-requests/{id}       # Talebi sil (sadece draft/rejected)
```

### Onay İşlemleri
```
POST /purchasing-requests/{id}/submit                  # Onaya gönder
POST /purchasing-requests/{id}/approve-by-supervisor   # Şef onayı
POST /purchasing-requests/{id}/approve-by-manager      # Yönetici onayı
POST /purchasing-requests/{id}/reject                  # Reddet
POST /purchasing-requests/{id}/cancel                  # İptal et
```

---

## Kullanım Senaryoları

### Senaryo 1: Başarılı Onay Süreci

1. **Şantiye Şefi** yeni talep oluşturur (draft)
2. 10 adet çimento, 5 ton demir talebinde bulunur
3. Talebi onaya gönderir (pending)
4. **Departman Şefi** talebi inceler ve onaylar
   - `approved_by_supervisor` = şef_id
5. **Proje Yöneticisi** bütçeyi kontrol eder ve onaylar
   - `approved_by_manager` = yönetici_id
   - `status` = 'approved'
6. **Satınalma Sorumlusu** tedarikçiye sipariş verir (ordered)
7. Malzemeler gelir (received)

**Sonuç:** ✅ Başarılı - Malzemeler temin edildi

---

### Senaryo 2: Red Edilme

1. **Proje Yöneticisi** yeni talep oluşturur (draft)
2. Lüks ofis mobilyaları talebinde bulunur
3. Talebi onaya gönderir (pending)
4. **Departman Şefi** onaylar
5. **Genel Müdür** bütçe aşımı nedeniyle reddeder
   - `rejection_reason` = "Bütçe yetersiz, öncelik düşük"
   - `status` = 'rejected'

**Sonuç:** ❌ Reddedildi - Talep gerçekleşmedi

---

### Senaryo 3: İptal

1. **Şantiye Şefi** acil talep oluşturur (draft)
2. Onaya gönderir (pending)
3. Şef onaylar
4. İhtiyaç ortadan kalktığı için **Şantiye Şefi** iptal eder
   - `status` = 'cancelled'

**Sonuç:** ⚠️ İptal - Talep işleme alınmadı

---

## Önemli Notlar

### 🔒 Güvenlik
- Kullanıcılar sadece yetkili oldukları talepleri görebilir
- Onay yetkileri rol bazlıdır
- Tüm işlemler kullanıcı ID'si ile loglanır

### 📊 Raporlama
- Tüm durum değişiklikleri kayıt altındadır
- Hangi kullanıcının ne zaman onay verdiği takip edilir
- Red nedenleri saklanır

### ⚡ Performans
- Talepler indekslenmiştir (status, project_id, etc.)
- Eager loading kullanılarak N+1 sorunu önlenir
- Sayfalama (pagination) aktiftir

### 🔔 Bildirimler
Şu anki yapıda henüz implement edilmemiş, ancak şu noktalarda bildirim gönderilebilir:
- Talep onaya gönderildiğinde → Onay verenler
- Onay/Red kararı verildiğinde → Talep sahibi
- Malzeme teslimatında → İlgili taraflar

---

## Geliştirme Önerileri

### Kısa Vadeli
1. ✅ Malzeme seçiminde otomatik doldurma (TAMAMLANDI)
2. ⏳ E-posta/SMS bildirimleri
3. ⏳ Sipariş verme ekranı
4. ⏳ Malzeme giriş ekranı

### Orta Vadeli
1. ⏳ Bütçe kontrol otomasyonu
2. ⏳ Tedarikçi entegrasyonu
3. ⏳ QR kod ile malzeme takibi
4. ⏳ Mobil uygulama

### Uzun Vadeli
1. ⏳ AI ile talep önceliklendirme
2. ⏳ Otomatik tedarikçi karşılaştırma
3. ⏳ Stok tahmin modeli
4. ⏳ Blok zincir ile şeffaflık

---

## Sık Sorulan Sorular

### S1: Şef ve yönetici aynı anda onay verebilir mi?
**C:** Evet, onay sırası önemli değildir. Sistem her iki onayı da aldığında durumu `approved` yapar.

### S2: Reddedilen bir talep tekrar onaya gönderilebilir mi?
**C:** Hayır. Reddedilen talep için yeni bir talep oluşturulması gerekir.

### S3: Draft durumunda ne kadar süre bekleyebilir?
**C:** Sınırsız. Ancak otomatik temizleme politikası eklenebilir (örn: 30 günden eski draft'lar silinir).

### S4: Sipariş verildikten sonra iptal edilebilir mi?
**C:** Hayır. `ordered` durumuna geçtikten sonra iptal edilemez. Tedarikçi ile manuel koordinasyon gerekir.

### S5: Kısmi teslimat nasıl yönetilir?
**C:** Sistem `partially_received` durumu ile kısmi teslimatları takip eder. Depo sorumlusu gelen miktarları kaydeder.

---

## Versiyon Geçmişi

| Versiyon | Tarih | Değişiklikler |
|----------|-------|---------------|
| 1.0 | 2025-01-20 | İlk versiyon - Temel onay mekanizması |
| 1.1 | 2025-01-20 | Malzeme seçiminde otomatik doldurma eklendi |

---

## İletişim

Sorular ve öneriler için:
- Geliştirici: SPT Development Team
- E-posta: [proje email adresi]
- Dokümantasyon: `/docs` klasörü

---

**Son Güncelleme:** 20 Ocak 2025
**Hazırlayan:** Claude Code Assistant (Anthropic)
