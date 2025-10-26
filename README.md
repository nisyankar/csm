# SPT - Şantiye Proje Takip Sistemi

İnşaat şantiyelerinin yönetimi için geliştirilmiş kapsamlı proje takip sistemi.

## Özellikler

### Puantaj Yönetimi
- **Gelişmiş Onay Sistemi**: Çok aşamalı onay süreci (draft, submitted, approved, rejected)
- **Onay Logları**: Tüm onay işlemlerinin detaylı kayıtları
- **İK Müdahale Sistemi**: İK'nın onaylanmış puantajlara müdahale edebilmesi
- **İzin Entegrasyonu**: İzin taleplerinin otomatik puantaja yansıması
- **Fazla Mesai Takibi**: Hafta içi (%50), hafta sonu (%100), resmi tatil (%200)
- **Haftalık Hesaplamalar**: Otomatik haftalık özet ve fazla mesai hesaplamaları
- **Toplu Giriş**: Aylık veya haftalık toplu puantaj girişi
- **Proje Detay Takibi**: Yapı, kat, daire ve iş kalemi bazında detaylı kayıt

### Çalışan Yönetimi
- Çalışan bilgileri ve departman atamaları
- Vardiya yönetimi
- Maaş geçmişi takibi
- Proje atamaları
- Taşeron çalışan desteği

### İzin Yönetimi
- Yıllık izin, hastalık izni, ücretsiz izin vb. tüm izin tipleri
- **Resmi Tatil Yönetimi**: Arefe (yarım gün) desteği ile
- **Akıllı İzin Hesaplama**: Proje bazlı hafta tatili + resmi tatil entegrasyonu
- Otomatik izin bakiyesi hesaplama
- İzin onay süreci
- İzin-puantaj senkronizasyonu
- Detaylı izin bakiyesi logları

### Proje Yönetimi
- Proje yapı ve kat tanımlamaları
- **Özelleştirilebilir Hafta Tatili**: Proje bazında hafta sonu günleri
- İş kalemleri ve metraj takibi
- Proje-departman ilişkileri
- Taşeron firma yönetimi
- Proje dokümantasyonu

### Satın Alma Modülü
- Satın alma talepleri
- Tedarikçi yönetimi
- Teklif karşılaştırma
- Sipariş takibi
- Teslimat yönetimi

### Keşif & Metraj Modülü 🆕
- **Proje Yapısı Entegrasyonu**: Yapı/Kat/Birim bazlı metraj kayıtları
- **İş Kalemi Takibi**: İş kalemleri ile ilişkilendirilmiş metraj hesaplama
- **Planlanan ve Tamamlanan**: Metraj planlama ve gerçekleşme takibi
- **Hakediş Entegrasyonu**: Metraj kayıtlarından hakediş oluşturma
- **İlerleme İzleme**: Tamamlanma yüzdesi ve kalan miktar hesaplama
- **Ölçüm Yöntemleri**: Farklı ölçüm yöntemleri desteği
- **Onay Süreci**: Doğrulama ve onaylama akışı
- **Proje Show Sayfası**: Metraj kayıtları tab görünümü

### Hakediş Modülü
- **Metraj Bazlı Hakediş**: Keşif/Metraj kayıtlarından otomatik veri çekme
- **Akıllı Form**: Proje/yapı/kat/birim seçimine göre ilgili metraj bulma
- **Otomatik Limit Kontrolü**: Kalan hakediş yapılabilir miktar hesaplama
- **Metraj Aşımı Takibi**: Planlanan metrajı aşan hakediş kayıtlarının otomatik tespiti
- **Aşım Raporu**: Metraj aşımı kayıtlarının filtrelenebilir raporu
- **Aşım Uyarıları**: Kullanıcıya aşım durumunda görsel uyarı
- İş kalemi bazlı metraj takibi
- Onay süreci (completed → approved → paid)
- Ödeme durumu takibi
- Proje yapı/kat/birim detaylı kayıt
- **Metraj İlişkisi**: Her hakediş metraj kaydına bağlı

### Finansal Yönetim Modülü 🆕
- **Event-Driven Otomatik Kayıt**: Puantaj, Hakediş, Satınalma modüllerinden
- **Gelir/Gider Kategorileri**: Hiyerarşik kategori yapısı
- **Ödeme Takibi**: Beklemede, Kısmi, Ödendi durumları
- **Kar/Zarar Raporları**: Proje, yıl, ay bazlı analiz
- **Dashboard**: Özet finansal widget'lar
- **Bütçe vs Gerçekleşen**: Varyans analizi

## Teknoloji Stack

- **Backend**: Laravel 11
- **Frontend**: Vue.js 3 + Inertia.js
- **Veritabanı**: MariaDB / MySQL
- **Authentication**: Laravel Sanctum
- **Yetkilendirme**: Spatie Laravel Permission

## Kurulum

### Gereksinimler
- PHP 8.2+
- Composer
- Node.js 18+
- MariaDB 10.6+ veya MySQL 8.0+

### Adımlar

1. Bağımlılıkları yükleyin:
```bash
composer install
npm install
```

2. Environment dosyasını yapılandırın:
```bash
cp .env.example .env
php artisan key:generate
```

3. Veritabanını oluşturun ve migration'ları çalıştırın:
```bash
php artisan migrate --seed
```

4. Frontend asset'lerini derleyin:
```bash
npm run build
```

5. Uygulamayı çalıştırın:
```bash
php artisan serve
```

## Veritabanı Yapısı

### Ana Tablolar
- `timesheets`: Puantaj kayıtları (gelişmiş onay sistemi ile)
- `employees`: Çalışan bilgileri
- `projects`: Proje tanımları
- `leave_requests`: İzin talepleri
- `timesheet_approval_logs`: Puantaj onay geçmişi
- `shifts`: Vardiya tanımları
- `departments`: Departman tanımları

### Yedek Tablolar
- `timesheets_old_backup`: Eski puantaj sistemi (referans için)
- `timesheets_v3_backup`: V3 puantaj sistemi (referans için)

## Geliştirme Notları

### Son Güncellemeler

#### 26 Ekim 2025 - Keşif/Metraj ve Hakediş Entegrasyonu 🎯
- **Metraj-Hakediş İlişkisi**: İş akışı düzeltildi (Metraj → Hakediş)
- **Akıllı Hakediş Formu**: Proje/yapı/kat/birim seçince otomatik metraj bulma
- **Otomatik Limit Kontrolü**: Daha önce hakediş yapılan miktarı çıkarıp kullanılabilir kalan gösterir
- **İlişkili Kayıt Görünümü**:
  - Proje Show'da Keşif/Metraj tabı eklendi
  - Metraj Show'da İlişkili Hakediş widget'ı eklendi
- **Backend İyileştirmeler**:
  - QuantityController'a search() API endpoint eklendi
  - Project-Quantity relationship eklendi
  - Null-safe accessor metodları güncellendi
- **Frontend İyileştirmeler**:
  - Hakediş form'unda metraj bilgi widget'ı (yeşil: bulundu, sarı: bulunamadı)
  - Quantity Stats computed properties
  - Payment status labels ve variant helpers

#### 26 Ekim 2025 - Finansal Yönetim Modülü Tamamlandı 🎉
- **Event-Driven Finansal Sistem**: Puantaj, Hakediş, Satınalma onaylarından otomatik finansal kayıt
- **Gelir/Gider Kategori Yönetimi**: Hiyerarşik yapı ile esnek kategorizasyon
- **Kar/Zarar Raporları**: Proje ve kategori bazlı detaylı analiz
- **Financial Dashboard**: Gelir, gider, kar marjı özet kartları
- **Ödeme Durumu Takibi**: Onaylı puantajlar otomatik "ödendi" olarak işaretlenir
- **Bütçe vs Gerçekleşen**: Otomatik varyans hesaplaması
- 6 Vue sayfası + Full-width profesyonel tasarım

#### 24 Ekim 2025 - İzin ve Tatil Sistemi
- **Resmi Tatil Sistemi**: Arefe (yarım gün) desteği ile eklendi
- **Proje Bazlı Hafta Tatili**: Her proje için özelleştirilebilir hafta sonu günleri
- **Akıllı İzin Hesaplama**: Proje kuralları + resmi tatiller entegre edildi
- Tüm puantaj versiyonları (`timesheets_v2`, `timesheets_v3`) tek bir `timesheets` tablosunda birleştirildi
- Model isimleri standartlaştırıldı (`TimesheetV2` → `Timesheet`)
- Gelişmiş onay sistemi ve haftalık hesaplama özenlikleri eklendi
- İzin-puantaj entegrasyonu tamamlandı

## Dokümantasyon

Detaylı proje planı ve durum takibi için:
- **[docs/PROJE_PLANI.md](docs/PROJE_PLANI.md)** - Kapsamlı proje planı, tamamlanan görevler, yapılacaklar listesi

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.
