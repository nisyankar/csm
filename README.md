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

### Sözleşme Yönetimi Modülü 🆕
- **Taşeron/Tedarikçi Sözleşmeleri**: Merkezi sözleşme yönetim sistemi
- **Otomatik Numaralandırma**: Duplicate-safe sözleşme numarası oluşturma (PRJ-CODE-TS-YYYY-0001)
- **Teminat Yönetimi**: Banka Mektubu, Nakit, Çek, Teminatsız seçenekleri
- **Durum Yönetimi**: Draft → Active → Completed/Terminated/Expired lifecycle
- **Hakediş Entegrasyonu**: Sözleşmelere bağlı hakediş kayıtları
- **Satınalma Entegrasyonu**: Opsiyonel sözleşme bağlantısı
- **Proje Entegrasyonu**: Proje detay sayfasında sözleşmeler tabı
- **Dashboard**: İstatistikler, süresi dolacak sözleşmeler, son sözleşmeler
- **Filtreleme**: Proje, taşeron, durum, tarih bazlı arama
- **Süre Takibi**: Sözleşme süresi dolacak olanlar için otomatik uyarı

### Satış ve Tapu Yönetimi Modülü 🆕
- **Müşteri CRM**: Bireysel ve kurumsal müşteri yönetimi
- **Satış Yönetimi**: Rezervasyon, kesin satış, ön satış süreçleri
- **Otomatik Ödeme Planı**: Peşinat + taksit planlaması
- **Ödeme Takibi**: Detaylı taksit ve ödeme durumu izleme
- **Cascade Dropdown**: Proje → Blok → Kat → Birim hiyerarşik seçimi
- **Satış Durumu Görselleştirme**:
  - Proje bazlı satış istatistikleri ve dashboard
  - Blok seçimi ve kat bazlı progress bar'lar
  - Renk kodlu birim grid (Müsait, Satıldı, Rezerve, Gecikmiş)
  - İnteraktif birim kartları ve detay modal'ları
  - Müşteri ve fiyat bilgileri ile hover tooltips
- **Basit Tapu Takibi**:
  - Tapu durumu yönetimi (Devredilmedi, İşlemde, Devredildi, Ertelendi)
  - Tapu belgesi yükleme sistemi (PDF, JPG, PNG - Max 10MB)
  - Tapu bilgileri (Tip, Numara, Devir Tarihi, Notlar)
  - Modal-based güncelleme ve belge yönetimi
- **Finansal Entegrasyon**: Satış ödemelerinden otomatik gelir kaydı (Event-driven ready)

### Yapı Denetim Sistemi Modülü 🆕
- **Denetim Kuruluşları Yönetimi**: Lisanslı yapı denetim firmalarının takibi
- **Denetim Kayıtları**: Periyodik, özel ve final denetim kayıtları
- **Otomatik Denetim Numaralandırma**: Duplicate-safe numara oluşturma (DEN-PRJ-YYYY-001)
- **Uygunsuzluk Takibi**: Minor, Major, Critical seviyelerinde uygunsuzluk kayıtları
- **Düzeltici Faaliyetler**: Uygunsuzluklara karşı aksiyonların takibi ve durumu
- **Denetim Raporları**: PDF rapor yükleme ve ek dosya yönetimi
- **Denetim Türleri**: Periyodik (3 aylık), Özel, Final denetimleri
- **Durum Yönetimi**: Scheduled → Completed → Pending Action → Closed lifecycle
- **Dashboard**: İstatistikler, yaklaşan/gecikmiş denetimler, uygunsuzluk sayıları
- **Filtreleme**: Proje, denetim kuruluşu, tür, durum, tarih bazlı arama
- **Ruhsat Yönetimi**:
  - Yapı Ruhsatı (Building Permit) takibi
  - İskan İzni (Occupancy Permit) yönetimi
  - Yapı Kullanma İzni (Usage Permit) kayıtları
  - Otomatik ruhsat numarası oluşturma (YR-PRJ-YYYY-001)
  - İmar durumu ve ihraç makamı bilgileri

### Stok Yönetimi Modülü 🆕
- **Depo Yönetimi**: Proje bazlı depo tanımlama ve sorumlu atama
- **Stok Hareketleri**: Giriş, çıkış, transfer ve düzeltme kayıtları
- **Transaction-Safe Stok**: DB transaction ile güvenli stok güncellemeleri
- **Otomatik Stok Hesaplama**: Mevcut stok takibi (current_stock)
- **Minimum Stok Seviyesi**: Kritik stok uyarıları için altyapı
- **Polymorphic İlişki**: Satınalma, üretim gibi modüllerle entegrasyon hazır
- **4 Hareket Tipi**: Giriş (in), Çıkış (out), Transfer (transfer), Düzeltme (adjustment)
- **Yetersiz Stok Kontrolü**: Çıkış işlemlerinde otomatik kontrol
- **Rollback Mekanizması**: Hareket silme/güncelleme için otomatik stok geri alma
- **Filtreleme**: Depo, malzeme, hareket tipi, tarih aralığı bazlı arama
- **Modern UI**: Cyan-emerald gradient tema, hakediş modülü tasarımıyla tutarlı
- **Satınalma Entegrasyonu**: "Satınalma & Stok" menü grubu altında organize
- **6 Vue Sayfası**: Warehouses (Index, Create, Edit) + StockMovements (Index, Create, Edit)
- **Gerçekçi Seeder**: Her proje için 2-3 depo + çoklu stok hareketleri

### İş Sağlığı ve Güvenliği (İSG) Modülü 🆕
- **İş Kazası Yönetimi**: Ramak kala, yaralanma, ölümlü kaza kayıtları
- **Kök Sebep Analizi**: RCA (Root Cause Analysis) ve düzeltici faaliyetler
- **İSG Eğitimleri**: Eğitim planlama, katılımcı takibi, sertifika yönetimi
- **Güvenlik Denetimleri**: Kontrol listeleri, skor sistemi, periyodik denetimler
- **Risk Değerlendirmesi**: RAMS (Risk Assessment Method Statement) formları
- **KKD Yönetimi**: Kişisel koruyucu donanım zimmet ve takibi
- **5 Alt Modül**: Safety Incidents, Trainings, Inspections, Risk Assessments, PPE Assignments
- **Önem Derecesi Takibi**: Düşük, Orta, Yüksek, Kritik seviyeler
- **Durum Yönetimi**: Raporlandı → İnceleniyor → Çözüldü → Kapatıldı lifecycle
- **Modern UI**: Kırmızı-turuncu gradient tema, full-width tasarım
- **7 Vue Sayfası**: Create (5), Edit (1), Show (1) - tümü modern tasarım
- **Filtreleme**: Proje, tür, önem derecesi, durum bazlı arama
- **Gerçekçi Seeder**: 2 kaza, 2 eğitim, 1 denetim, 1 risk değerlendirmesi, 2 KKD kaydı

### Rol & Yetki Yönetimi Modülü 🆕
- **Proje Bazlı Rol Sistemi**: Kullanıcıların projelere özel rol ve yetki ataması
- **7 Rol Tipi**: Proje Müdürü, Şantiye Şefi, Mühendis, Usta Başı, Denetçi, İSG Uzmanı, Görüntüleyici
- **Activity Log Sistemi**: Tüm sistem aktivitelerinin detaylı kaydı
- **8 Aktivite Tipi**: Created, Updated, Deleted, Viewed, Login, Logout, Access Denied, Custom
- **4 Önem Seviyesi**: Info, Warning, Error, Critical
- **Route Permission Management**: Tüm route'lar için yetki yönetimi
- **Otomatik Route Sync**: Laravel route list'ten otomatik senkronizasyon
- **Anlamlı Türkçe İsimler**: Route'lar için otomatik Türkçe isim oluşturma
- **11 Farklı Rol**: Admin, HR, Proje Müdürü, Şantiye Şefi, Mühendis, Usta Başı, Denetçi, İSG, Görüntüleyici, Muhasebe, Finans
- **Toplu Yetki Yönetimi**: Birden fazla route için aynı anda yetki atama
- **Filtreleme**: Modül, durum, proje erişimi bazlı arama
- **CheckProjectAccess Middleware**: Otomatik proje erişim kontrolü
- **Polymorphic Activity Log**: Tüm modeller için activity tracking
- **Modern UI**: Purple-indigo-blue (Proje Rolleri), Slate-gray (Activity Logs), Orange-red (Route Yetkileri) gradient temalar
- **3 Database Migration**: user_project_roles, activity_logs, route_permissions
- **3 Model**: UserProjectRole, ActivityLog, RoutePermission (ilişkiler, scope'lar, helper metodları)
- **3 Controller**: Full CRUD, sync, bulk update, filtreleme
- **6 Vue Sayfası**: UserProjectRoles (Index, Create, Edit), ActivityLogs (Index, Show), RoutePermissions (Index)
- **Sidebar Entegrasyonu**: "Rol & Yetki Yönetimi" menü grubu (shield-check ikonu)
- **Routes**: `/user-project-roles/*`, `/activity-logs/*`, `/route-permissions/*`

### Ekipman & Makine Yönetimi Modülü 🆕
- **Ekipman Envanteri**: 17 farklı ekipman tipi (Ekskavatör, Buldozer, Vinç, Loader, Kompaktör, Jeneratör vb.)
- **Sahiplik Yönetimi**: Mülkiyetli, Kiralık, Leasing olmak üzere 3 sahiplik türü
- **Kullanım Kayıtları**: Operatör, proje, tarih, süre bazlı detaylı kullanım takibi
- **Sayaç Yönetimi**: Saat, kilometre, çevrim cinsinden sayaç okuma ve toplam hesaplama
- **Yakıt Takibi**: Yakıt tüketimi ve maliyet kayıtları
- **Bakım Sistemi**: Rutin, Önleyici, Düzeltici, Arıza olmak üzere 4 bakım türü
- **Periyodik Bakım**: Bakım hatırlatıcı ve son bakım tarihi takibi
- **Maliyet Analizi**: İşletme, bakım, yakıt maliyetlerinin toplam hesaplaması
- **Finansal Entegrasyon**: Kira ve bakım giderlerinin otomatik finansal kayıt oluşturması
- **Ekipman Durumları**: Müsait, Kullanımda, Bakımda, Arızalı durum yönetimi
- **Otomatik Kodlama**: EKP-001 (ekipman), BKM-001 (bakım) otomatik kod oluşturma
- **Modern UI**: Yellow-amber-orange gradient tema, full-width tasarım
- **NULL-Safe Pagination**: Template-based pagination rendering
- **3 Database Migration**: equipments, equipment_usages, equipment_maintenance
- **3 Model**: Equipment, EquipmentUsage, EquipmentMaintenance (relationships, scopes, accessors)
- **3 Controller**: Full CRUD, filtering, statistics, financial integration
- **9 Vue Sayfası**: Equipments (Index, Create, Edit, Show), EquipmentUsages (Index, Create, Edit), EquipmentMaintenance (Index, Create, Edit)
- **Sidebar Entegrasyonu**: "Ekipman Yönetimi" menü grubu (truck ikonu)
- **EquipmentManagementSeeder**: 5 ekipman, 4 kullanım, 5 bakım kaydı
- **Routes**: `/equipments/*`, `/equipment-usages/*`, `/equipment-maintenance/*`

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

#### 31 Ekim 2025 - Rol & Yetki Yönetimi Modülü Tamamlandı 🎉
- **Proje Bazlı Rol Sistemi**: Kullanıcıların projelere özel rol ve yetki ataması
  - UserProjectRole model ve migration
  - 7 farklı rol tipi (project_manager, site_manager, engineer, foreman, viewer, inspector, safety_officer)
  - Tarih aralığı ile geçici atama desteği (start_date, end_date)
  - Rol bazlı permission sistemi (JSON)
  - Aktif/Pasif durum kontrolü ve otomatik geçerlilik kontrolü
- **Activity Log Sistemi**: Kapsamlı sistem aktivite izleme
  - 8 farklı aktivite tipi (created, updated, deleted, viewed, logged_in, logged_out, access_denied, custom)
  - 4 önem seviyesi (info, warning, error, critical)
  - Polymorphic ilişki desteği (subject_type, subject_id)
  - IP adresi, user agent, route bilgisi otomatik kaydı
  - Proje bazlı filtreleme ve kullanıcı bazlı activity history
  - Static helper metodları (logCreated, logUpdated, logDeleted vb.)
- **Route Permission Management**: Granüler yetki kontrolü
  - RoutePermission model ve migration
  - Otomatik route sync (Laravel route list'ten)
  - Anlamlı Türkçe isim oluşturma sistemi (generateDisplayName)
  - Action type belirleme (view, create, edit, delete, vb.)
  - 11 farklı rol desteği (admin, hr, project_manager, site_manager, engineer, foreman, inspector, safety_officer, viewer, accounting, finance)
  - Modül bazlı filtreleme ve toplu yetki güncelleme
  - Public route desteği ve proje erişimi kontrolü
- **Middleware Geliştirmeleri**:
  - CheckProjectAccess: Otomatik proje erişim kontrolü
  - Dinamik project_id tespiti (route params, form data)
  - Erişim engelleme durumunda otomatik activity log kaydı
- **User Model Güncellemeleri**:
  - projectRoles() ilişkisi
  - activityLogs() ilişkisi
  - canAccessProject() - Yeni ve eski sistem birlikte çalışıyor
  - getProjectRole() - Kullanıcının projedeki rolünü getir
  - getAccessibleProjects() - Erişilebilir projeleri listele
- **Modern Full-Width UI Tasarımı**:
  - UserProjectRoles: Purple-indigo-blue gradient header
  - ActivityLogs: Slate-gray gradient header
  - RoutePermissions: Orange-red gradient header
  - Hakediş modülüne benzer modern card'lar ve filtreler
  - Rol ve önem seviyesi için renkli badge'ler
  - NULL-safe pagination (dynamic component kullanımı)
  - Modal-based düzenleme formu (route permissions)
- **Controller'lar**:
  - UserProjectRoleController: Full CRUD + activate/deactivate + byUser/byProject metodları
  - ActivityLogController: index, show, userActivity, projectActivity, export metodları
  - RoutePermissionController: CRUD + syncFromRoutes + bulkUpdate + batchAssignByModule
- **Routes ve Sidebar**:
  - user-project-roles.* route grubu (admin, hr erişimi)
  - activity-logs.* route grubu (admin, hr erişimi)
  - route-permissions.* route grubu (sadece admin)
  - "Rol & Yetki Yönetimi" sidebar bölümü eklendi
  - Route Yetkileri menüsü (admin only)
- **6 Vue Sayfası**: Modern full-width gradient header'lı
  - UserProjectRoles (Index, Create, Edit)
  - ActivityLogs (Index, Show)
  - RoutePermissions (Index)
- **Teknik İyileştirmeler**:
  - Modal component path düzeltmesi (@/Components/UI/Modal.vue)
  - UserProjectRoleController prop isim tutarlılığı
  - Migration şema iyileştirmesi (nullable fields)
  - Pagination Link null href hatası düzeltildi (tüm sayfalarda)
  - TECHNICAL_DEBT.md dosyası oluşturuldu (ProgressPayments form hatası dokümante edildi)

#### 30 Ekim 2025 - Ekipman & Makine Yönetimi Modülü Tamamlandı 🎉
- **Ekipman Envanteri Sistemi**: 17 farklı ekipman tipi ile kapsamlı envanter yönetimi
- **3 Sahiplik Türü**: Mülkiyetli, Kiralık, Leasing seçenekleri
- **Kullanım Kayıtları**:
  - Operatör, proje, tarih, süre bazlı detaylı kayıt
  - Sayaç okuma takibi (saat, km, çevrim)
  - Yakıt tüketimi ve maliyet hesaplaması
  - Durum bazlı ekipman kullanılabilirlik kontrolü
- **Bakım ve Onarım Sistemi**:
  - 4 Bakım Türü: Rutin, Önleyici, Düzeltici, Arıza
  - Maliyet ayrımı (işçilik, parça, dış servis)
  - Periyodik bakım hatırlatıcı (next_maintenance_date)
  - Bakım geçmişi ve toplam bakım maliyeti
- **Finansal Entegrasyon**:
  - Kira giderleri otomatik financial_transactions kaydı
  - Bakım maliyetleri otomatik kayıt
  - Proje bazlı maliyet izleme
- **Ekipman Durumları**: Müsait → Kullanımda → Bakımda → Arızalı lifecycle
- **Otomatik Kod Sistemi**:
  - Ekipman: EKP-001, EKP-002...
  - Bakım: BKM-001, BKM-002...
- **Modern Full-Width Tasarım**:
  - Yellow-amber-orange gradient header (ekipman teması)
  - NULL-safe pagination (template-based)
  - Responsive grid layout
  - İstatistikler widget'ları
- **3 Database Migration**: equipments, equipment_usages, equipment_maintenance
- **3 Model**: Equipment, EquipmentUsage, EquipmentMaintenance (NULL-safe accessors)
- **3 Controller**: CRUD işlemleri, filtreleme, statistics, financial integration
- **9 Vue Sayfası**: Modern tasarım ile Index, Create, Edit, Show sayfaları
- **Sidebar Entegrasyonu**: "Ekipman Yönetimi" menü grubu (truck ikonu)
- **EquipmentManagementSeeder**: 5 ekipman + 4 kullanım + 5 bakım kaydı
- **Routes**: `/equipments/*`, `/equipment-usages/*`, `/equipment-maintenance/*`
- **Bug Fix**: Pagination null URL hatası tüm modüllerde düzeltildi (template-based rendering)

#### 30 Ekim 2025 - İş Sağlığı ve Güvenliği (İSG) Modülü Tamamlandı 🎉
- **5 Alt Modül Sistemi**: İş Kazaları, Eğitimler, Denetimler, Risk Değerlendirmeleri, KKD Atamaları
- **İş Kazası Yönetimi**:
  - 6 Olay Türü: Küçük/Büyük Yaralanma, Ramak Kala, Mal Hasarı, Çevresel, Ölümlü
  - 4 Önem Derecesi: Düşük, Orta, Yüksek, Kritik
  - Kök sebep analizi (RCA) ve düzeltici faaliyet takibi
  - İş kaybı günü, tıbbi tedavi, iş durdurma flag'leri
  - Yetkili makamlara bildirim durumu
- **İSG Eğitim Sistemi**:
  - 7 Eğitim Türü: Temel İSG, Yangın, İlk Yardım, Yüksekte Çalışma, Kapalı Alan, Elektrik, Diğer
  - Eğitmen ve şirket bilgileri
  - Eğitim süresi ve lokasyon takibi
- **Güvenlik Denetim Sistemi**:
  - 5 Denetim Türü: Günlük, Haftalık, Aylık, Üç Aylık, Özel
  - Kontrol edilen/başarılı/başarısız madde sayacı
  - Skor sistemi (0-100)
  - 4 Genel Durum: Başarılı, Notlarla Geçti, Aksiyon Gerekli, Başarısız
- **Risk Değerlendirmesi (RAMS)**:
  - İş aktivitesi bazlı risk analizi
  - 4 Risk Seviyesi: Düşük, Orta, Yüksek, Kritik
  - Kontrol tedbirleri ve geçerlilik tarihleri
- **KKD Yönetimi**:
  - 9 KKD Türü: Baret, İş Ayakkabısı, Eldiven, Gözlük, Yelek, Emniyet Kemeri, Maske, Kulak Koruyucu, Diğer
  - Marka, model, beden, miktar bilgileri
  - Birim fiyat ve son kullanma tarihi takibi
- **Modern Full-Width Tasarım**:
  - Kırmızı-turuncu gradient header (İSG teması)
  - Breadcrumb navigation
  - Responsive grid layout (lg:grid-cols-2)
  - Gradient butonlar ve modern input stilleri
  - Durum bazlı renkli badge'ler
- **5 Database Migration**: safety_incidents, safety_trainings, safety_inspections, risk_assessments, ppe_assignments
- **5 Model**: İlişkiler, scope'lar, accessor metodları, casts
- **5 Controller**: CRUD işlemleri, filtreleme, transaction-safe operations
- **7 Vue Sayfası**: Create (5), Edit (1), Show (1) - tümü modern tasarım
- **Sidebar Entegrasyonu**: "İş Sağlığı ve Güvenliği" menü grubu (shield-exclamation ikonu)
- **SafetyManagementSeeder**: 2 kaza, 2 eğitim, 1 denetim, 1 risk değerlendirmesi, 2 KKD kaydı
- **Routes**: `/safety-incidents/*`, `/safety-trainings/*`, `/safety-inspections/*`, `/risk-assessments/*`, `/ppe-assignments/*`

#### 29 Ekim 2025 - Stok Yönetimi Modülü Tamamlandı 🎉
- **Depo Yönetimi Sistemi**: Proje bazlı depo tanımlama
- **Stok Hareketi Kayıtları**: Giriş, çıkış, transfer, düzeltme işlemleri
- **Transaction-Safe İşlemler**: DB::transaction() ile güvenli stok güncellemeleri
- **Otomatik Stok Takibi**:
  - materials tablosuna current_stock ve min_stock_level kolonları
  - Her stok hareketi ile otomatik güncelleme
  - Yetersiz stok kontrolü (çıkış işlemlerinde)
- **Rollback Mekanizması**:
  - Hareket silme: Stok otomatik geri alınır
  - Hareket güncelleme: Eski hareket iptal, yeni hareket uygulanır
- **Polymorphic İlişki**: reference_type/reference_id ile modül entegrasyonu
- **3 Migration**: warehouses, stock_movements, materials güncelleme
- **2 Model**: Warehouse (SoftDeletes), StockMovement
- **2 Controller**: WarehouseController, StockMovementController (CRUD + stok yönetimi)
- **6 Vue Sayfası**: Modern cyan-emerald gradient tema
- **Sidebar Entegrasyonu**: "Satınalma & Stok" menü grubu
- **StockManagementSeeder**: Gerçekçi demo veriler (her proje 2-3 depo + stok hareketleri)
- **Routes**: `/warehouses/*` ve `/stock-movements/*` rotaları

#### 28 Ekim 2025 - Yapı Denetim Sistemi Modülü Tamamlandı 🎉
- **Denetim Kuruluşları Yönetimi**: Lisans numarası, iletişim bilgileri, aktif/pasif durum
- **Denetim Kayıtları Sistemi**: Periyodik, özel ve final denetim türleri
- **Otomatik Numaralandırma**: Duplicate-safe denetim numarası (DEN-PRJ-YYYY-001)
- **Uygunsuzluk Yönetimi**:
  - 3 Seviye: Minor (küçük), Major (büyük), Critical (kritik)
  - Fotoğraf yükleme ve son tarih takibi
  - Kritik uygunsuzluk sayacı
- **Düzeltici Faaliyetler**:
  - Sorumlu kişi ve son tarih ataması
  - Durum takibi (Pending, In Progress, Completed)
  - Tamamlanma tarihi kaydı
- **Rapor Yönetimi**:
  - PDF rapor yükleme (10MB limit)
  - Sınırsız ek dosya desteği
  - Dosya meta bilgileri (isim, tip, boyut, tarih)
- **Dashboard**:
  - Toplam/planlanan/bekleyen denetim sayıları
  - Kritik uygunsuzluk sayacı
  - Yaklaşan denetimler (7 gün içinde)
  - Gecikmiş denetim uyarıları
  - Denetim türü dağılımı
- **Ruhsat Yönetimi**:
  - Yapı Ruhsatı (Building), İskan İzni (Occupancy), Kullanma İzni (Usage)
  - Otomatik ruhsat numaraları (YR/İİ/YKİ-PRJ-YYYY-001)
  - İhraç makamı ve imar durumu bilgileri
  - Başvuru, onay, geçerlilik tarihleri
- **8 Vue Sayfası**: Full-width modern tasarım (purple gradient)
- **Seeders**: 5 denetim kuruluşu + otomatik periyodik denetimler + ruhsat kayıtları
- **Routes**: `/inspections/*` ve `/inspection-companies/*` altında organize edildi
- **Sidebar Entegrasyonu**: "Yapı Denetim" menü grubu eklendi

#### 27 Ekim 2025 - Satış ve Tapu Yönetimi Modülü Tamamlandı 🎉
- **Satış Durumu Görselleştirme Sistemi**: Proje bazlı interaktif satış dashboard'u
- **Renk Kodlu Birim Grid**: Blok/Kat/Birim hiyerarşik görünümü
- **4 Renk Durumu**: Müsait (yeşil), Satıldı (kırmızı), Rezerve (sarı), Gecikmiş (turuncu)
- **İnteraktif Kartlar**: Hover tooltips ve detay modal'ları
- **Satış İstatistikleri**: Toplam, satılan, rezerve, müsait birim sayıları
- **Progress Bar'lar**: Kat bazlı satış tamamlanma oranları
- **Basit Tapu Takibi**:
  - Modal-based tapu durumu güncelleme formu
  - Tapu belgesi yükleme (PDF, JPG, PNG - 10MB)
  - Tapu bilgileri (Tip, numara, devir tarihi, notlar)
  - UnitSale Show sayfasında interaktif tapu bölümü
- **3 Vue Sayfası**: SalesStatus Index, Show + UnitSale Show güncelleme
- **SalesStatusController**: 4 endpoint (index, show, structure details, floor units)
- **Routes**: `/sales/sales-status` altında organize edildi
- **Sidebar Entegrasyonu**: "Satış Durumu" menü öğesi eklendi
- **Bug Fix**: Blok seçimi sorunu düzeltildi (floor_order → floor_number) ✅

#### 26 Ekim 2025 - Sözleşme Yönetimi Modülü Tamamlandı 🎉
- **Merkezi Sözleşme Sistemi**: Taşeron ve tedarikçi sözleşmelerinin tek noktadan yönetimi
- **3 Migration**: contracts table, progress_payments contract_id, purchasing_requests contract_id
- **Otomatik Numaralandırma**: Duplicate-safe sözleşme numarası (PRJ-CODE-TS-YYYY-0001)
- **Teminat Yönetimi**: Banka Mektubu, Nakit, Çek, Teminatsız seçenekleri
- **Lifecycle Management**: Draft → Active → Completed/Terminated/Expired durum akışı
- **Proje Entegrasyonu**:
  - Project Show sayfasına Sözleşmeler tabı eklendi
  - Proje seçimi ile otomatik form doldurma
- **Hakediş Entegrasyonu**: Hakediş kayıtlarına contract_id bağlantısı
- **Dashboard**: İstatistikler, süresi dolacak sözleşmeler (30 gün), son sözleşmeler
- **Filtreleme Sistemi**: Proje, taşeron, durum, tarih bazlı arama
- **ContractService**: Business logic, otomatik numaralandırma, lifecycle methods
- **5 Vue Sayfası**: Dashboard, Index, Show, Create, Edit (full-width tasarım)
- **Form Validation**: Comprehensive hata gösterimi (genel liste + input bazlı)
- **15 Seeder Kayıt**: Test verisi ile sistem hazır

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
