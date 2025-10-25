# SPT - Şantiye Proje Takip Sistemi
## 📋 Ana Proje Planı ve İndeks

**Son Güncelleme:** 25 Ekim 2025
**Versiyon:** 4.0.0
**Durum:** Aktif Geliştirme - Construction ERP

---

## 🎯 PROJE VİZYONU

SPT (Şantiye Proje Takip Sistemi), Türkiye inşaat sektörüne özel, **yerli ve tam kapsamlı bir Construction ERP** platformudur.

### Hedef Kullanıcılar
- 🏗️ Küçük/Orta ölçekli müteahhitler (5-100 çalışan)
- 🏢 Kurumsal inşaat firmaları (100-500 çalışan)
- 🏛️ Büyük holdinglerin inşaat kolları

### Temel Değer Önerisi
- ✅ Şantiye gerçeğine uygun tasarım (Türkiye iş yasaları, tatiller, vardiya)
- ✅ Tam entegre finansal sistem (gelir/gider → kar/zarar)
- ✅ Saha operasyonlarından üst yönetime kadar end-to-end çözüm
- ✅ Açık kaynak temel, SaaS veya on-premise kurulum seçeneği

---

## 📊 GÜNCEL DURUM ÖZETİ

| Metrik | Değer | Durum |
|--------|-------|-------|
| **Tamamlanan Fazlar** | 1/5 | ✅ Faz 1 tamamlandı |
| **Modül Sayısı** | 12/30+ | 🔄 %40 tamamlandı |
| **Database Tabloları** | 26+ tablo | ✅ İlişkisel yapı kuruldu |
| **Test Coverage** | %0 | ⚠️ Hedef: %80 |
| **ERP Olgunluk Seviyesi** | %90 | 🚀 Construction ERP seviyesinde |

### Son Tamamlanan (25 Ekim 2025)
- ✅ Employee Create/Edit sayfaları (CreateSimple.vue)
- ✅ Searchable Select dropdown düzeltmeleri
- ✅ Card.vue overflow sorunu çözüldü
- ✅ Hakediş Takip Sistemi (100%)
- ✅ İzin Yönetimi + Resmi Tatiller + Proje bazlı hafta tatilleri

---

## 📁 PROJE PLANI YAPISI

Proje, karmaşıklığı yönetmek için **5 faza** bölünmüştür. Her fazın detaylı planı ayrı bir dokümandadır:

### ✅ [Faz 1: Temel Altyapı](./faz1-temel-altyapi.md)
**Durum:** Tamamlandı (100%)
**Modüller:** Çalışan, Proje, Puantaj, İzin, Taşeron, Malzeme, Satınalma, Hakediş
**Tarih:** Ağustos - Ekim 2025

---

### 🔄 [Faz 2: Operasyonel Çekirdek](./faz2-operasyonel-moduller.md)
**Durum:** Devam Ediyor (%15)
**Odak:** İnşaat operasyonlarının tamamı (ruhsat, denetim, satış, finans, keşif)
**Hedef:** Aralık 2025

**Öncelikli Modüller:**
1. 💰 **Finansal Yönetim** (gelir/gider/kar-zarar) - Kritik
2. 📐 **Keşif & Metraj** (hakediş otomasyonu) - Kritik
3. 📄 **Sözleşme Yönetimi** (merkezi contract sistemi) - Kritik
4. 🏘️ **Satış & Tapu Yönetimi** (müşteri, sözleşme, ödeme)
5. 🏗️ **Ruhsat Yönetimi** (yapı ruhsatı, iskan)
6. 🔍 **Yapı Denetim Sistemi** (denetim raporları)
7. 📦 **Basit Stok Takibi** (depo/lokasyon)

---

### 📋 [Faz 3: Gelişmiş Modüller](./faz3-gelismis-moduller.md)
**Durum:** Planlanıyor
**Odak:** Operasyonel derinleştirme ve özel yönetim sistemleri
**Hedef:** Ocak - Mart 2026

**Modüller:**
- 🚜 Ekipman & Makine Yönetimi
- 👷 İş Sağlığı & Güvenliği (İSG)
- 📦 Çoklu Depo/Lokasyon Sistemi
- 📊 Gantt/Timeline (basit)
- 📈 Raporlama Katmanı Derinleştirme
- 🎯 Rol & Yetki Sistemi (proje bazlı yetkilendirme)

---

### 🔮 [Faz 4: İleri Seviye](./faz4-ileri-seviye.md)
**Durum:** Gelecek
**Odak:** Kurumsal özellikler ve entegrasyonlar
**Hedef:** Nisan - Haziran 2026

**Modüller:**
- 📅 CPM/PERT İş Programı
- 🧠 Predictive Analytics (AI tahminleme)
- 🧾 E-fatura/Muhasebe Entegrasyonu
- 🔌 API & 3. Parti Entegrasyonlar
- 🌐 Multi-Company (çoklu şirket) desteği

---

### 🚀 [Faz 5: Mobil & Otomasyon](./roadmap-gelecek.md)
**Durum:** Uzun Vadeli
**Odak:** Mobil cihazlar ve otomasyon
**Hedef:** 2026 Q3-Q4

**Özellikler:**
- 📱 Mobil PWA/Native App
- 🔲 QR Kod Puantaj
- 🤖 AI Önerileri ve Anomali Tespiti
- 📡 IoT Entegrasyonu (şantiye sensörleri)

---

## 🔗 [Entegrasyon Planı](./entegrasyon-plani.md)

Tüm modüllerin birbirine nasıl bağlandığını, otomatik veri akışlarını ve servis mimarisini açıklayan teknik dokümandır.

**Kapsam:**
- Modül arası veri akışı
- Otomatik finansal kayıt servisleri
- Event/Listener yapısı
- Shared service'ler
- Database trigger'lar

---

## 🔧 TEKNOLOJİ STACK

### Backend
- **Framework:** Laravel 11
- **Database:** MariaDB 10.11
- **Authentication:** Laravel Sanctum
- **Authorization:** Spatie Laravel Permission
- **Queue:** Laravel Queue

### Frontend
- **Framework:** Vue 3 (Composition API)
- **Bridge:** Inertia.js
- **UI:** Tailwind CSS
- **Icons:** Heroicons
- **Charts:** ApexCharts (planlı)

### Development
- **Version Control:** Git
- **Package Manager:** Composer (PHP), npm (JS)
- **Environment:** MSYS2 / Windows

---

## 📈 BAŞARI METRİKLERİ

### Kod Metrikleri
- **Modüller:** 12/30+ (%40)
- **Test Coverage:** %0 → Hedef: %80
- **Database Tables:** 26+ tablo
- **API Endpoints:** 150+
- **Vue Components:** 80+

### Geliştirme İlerlemesi
- **Faz 1:** %100 ✅ (Temel modüller)
- **Faz 2:** %15 🔄 (Operasyonel çekirdek)
- **Faz 3:** %0 📋 (Gelişmiş modüller)
- **Faz 4:** %0 🔮 (İleri seviye)
- **Faz 5:** %0 🚀 (Mobil & Otomasyon)

**Toplam Proje İlerleme:** **~40%**

---

## 📝 ÖNEMLİ KARARLAR VE MİMARİ SEÇİMLER

### 1. Tek Sayfa Form Yaklaşımı
- Multi-step formlar yerine tek sayfada tüm alanlar (CreateSimple pattern)
- Daha az hata, daha hızlı geliştirme

### 2. Puantaj Konsolidasyonu
- Tüm versiyonlar tek tabloda birleştirildi (timesheets)
- Kod tabanı temizlendi

### 3. Proje Bazlı Hafta Tatili
- Her proje kendi hafta tatillerini belirleyebilir
- JSON kolonu ile esnek yapı

### 4. Finansal Entegrasyon Mimarisi
- Tüm modüller `financial_transactions` tablosuna otomatik kayıt atar
- `source_module` + `source_id` ile kaynak takibi

### 5. Modüler Dosya Yapısı
- Her faz ayrı MD dosyasında
- Büyük dokümantasyon parçalanmış durumda

---

## 📞 DÖKÜMAN KAYNAKLAR

| Dosya | Açıklama |
|-------|----------|
| [PROJE_PLANI.md](./PROJE_PLANI.md) | Bu dosya - Ana indeks |
| [faz1-temel-altyapi.md](./faz1-temel-altyapi.md) | Faz 1 detayları |
| [faz2-operasyonel-moduller.md](./faz2-operasyonel-moduller.md) | Faz 2 detayları |
| [faz3-gelismis-moduller.md](./faz3-gelismis-moduller.md) | Faz 3 detayları |
| [faz4-ileri-seviye.md](./faz4-ileri-seviye.md) | Faz 4 detayları |
| [entegrasyon-plani.md](./entegrasyon-plani.md) | Modül entegrasyonları |
| [README.md](../README.md) | Kurulum ve genel bilgi |

---

## 🎯 SONRAKİ ADIMLAR

### Şu An Aktif Sprint (Faz 2 - Sprint 1)

**Hedef:** Finansal Yönetim + Keşif & Metraj

**Görevler:**
1. [ ] Finansal Yönetim migration'ları
2. [ ] FinancialTransaction model ve ilişkiler
3. [ ] Otomatik kayıt servisleri (Event/Listener)
4. [ ] Keşif & Metraj migration'ları
5. [ ] Quantity model ve hakediş entegrasyonu
6. [ ] Dashboard finansal widget'ları

**Tahmini Süre:** 2 hafta

---

## 📊 PROJE ROADMAP

```
2025 Q3          ✅ Faz 1 Tamamlandı
  └─ Ağustos      Temel altyapı
  └─ Eylül        Puantaj & İzin
  └─ Ekim         Hakediş & Widget

2025 Q4          🔄 Faz 2 Devam Ediyor
  └─ Kasım        Finansal + Keşif
  └─ Aralık       Satış + Ruhsat + Denetim

2026 Q1          📋 Faz 3 Planlanıyor
  └─ Ocak         Ekipman + İSG
  └─ Şubat        Çoklu Depo + Gantt
  └─ Mart         Raporlama + Yetki

2026 Q2          🔮 Faz 4 Gelecek
  └─ Nisan        CPM + Analytics
  └─ Mayıs        E-fatura
  └─ Haziran      API & Entegrasyonlar

2026 Q3-Q4       🚀 Faz 5 Uzun Vadeli
  └─ Mobil        PWA/Native App
  └─ Otomasyon    AI & IoT
```

---

**Son Güncelleme:** 25 Ekim 2025, 23:45
**Güncelleyen:** Development Team
**Versiyon:** 4.0.0

**Önemli Değişiklikler:**
- 🔄 Proje planı fazlara bölündü (5 faz)
- 📁 Her faz için ayrı MD dosyası oluşturuldu
- 📊 ERP seviyesine yükselme hedefi belirlendi
- 🎯 Faz 2 öncelikleri netleştirildi
- 📋 Entegrasyon planı ayrıştırıldı
