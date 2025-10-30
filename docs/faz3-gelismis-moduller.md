# FAZ 3: Gelişmiş Modüller
## 🚧 DEVAM EDİYOR (33.33%)

**Hedef:** Ocak - Mart 2026
**Durum:** Devam Ediyor
**Modül Sayısı:** 6 (2 tamamlandı)

---

## 🎯 MODÜLLER

### 1. ✅ Ekipman & Makine Yönetimi 🚜
**Durum:** Tamamlandı (%100)
**Database:** `equipments`, `equipment_usages`, `equipment_maintenance`
**Özellikler:**
- ✅ 17 farklı ekipman tipi (Ekskavatör, Buldozer, Vinç, Jeneratör vb.)
- ✅ 3 Sahiplik türü: Mülkiyetli, Kiralık, Leasing
- ✅ Ekipman kullanım kayıtları (operatör, proje, tarih, süre)
- ✅ Sayaç okuma takibi (saat, km, çevrim)
- ✅ Yakıt tüketimi ve maliyet hesaplama
- ✅ Bakım ve onarım kayıtları
- ✅ 4 bakım türü: Rutin, Önleyici, Düzeltici, Arıza
- ✅ Periyodik bakım hatırlatıcı sistemi
- ✅ Finansal entegrasyon (kira ve bakım giderleri otomatik kayıt)
- ✅ Ekipman durumu yönetimi (Müsait, Kullanımda, Bakımda, Arızalı)
- ✅ Otomatik kod oluşturma (EKP-001, BKM-001)
- ✅ Modern full-width UI (yellow-amber-orange gradient)
- ✅ NULL-safe pagination (template-based rendering)
- ✅ 9 Vue sayfası: Equipments (Index, Create, Edit, Show) + EquipmentUsages (Index, Create, Edit) + EquipmentMaintenance (Index, Create, Edit)

### 2. ✅ İş Sağlığı & Güvenliği (İSG) 👷
**Durum:** Tamamlandı (%100)
**Database:** `safety_incidents`, `safety_trainings`, `safety_inspections`, `risk_assessments`, `ppe_assignments`
**Özellikler:**
- ✅ İş kazası kayıtları (ramak kala, yaralanma, ölümlü vb.)
- ✅ İSG eğitim planlama ve takibi
- ✅ Güvenlik denetim kontrol listeleri
- ✅ Risk analiz ve değerlendirme formları (RAMS)
- ✅ KKD (Kişisel Koruyucu Donanım) zimmet yönetimi
- ✅ Kök sebep analizi ve düzeltici faaliyetler
- ✅ Full-width modern UI tasarımı
- ✅ Durum bazlı renkli badge'ler
- ✅ Önem derecesi takibi (düşük, orta, yüksek, kritik)
- 🔜 PDF export (planlanan)

### 3. Çoklu Depo/Lokasyon Sistemi 📦
**Database:** `warehouses` (genişletilmiş), `stock_movements` (transfer desteği)
**Özellikler:**
- Depolar arası transfer
- Toplu sayım ve stok fark raporu
- Depo sorumlusu yetkileri

### 4. Gantt/Timeline (Basit) 📊
**Database:** `project_schedules`
**Özellikler:**
- Task yönetimi (start, end, duration, progress)
- Bağımlılıklar (predecessors JSON)
- Gantt chart görseli (ApexCharts)
- Hakediş ilerlemesiyle entegrasyon

### 5. Raporlama Katmanı Derinleştirme 📈
**Service:** `ReportBuilderService`
**Özellikler:**
- PDF/Excel export (maatwebsite/excel)
- Planlı e-posta raporu
- Dashboard builder
- KPI tanımlama sistemi

### 6. Rol & Yetki Sistemi (Proje Bazlı) 🎯
**Database:** `user_project_roles`, `activity_logs`
**Özellikler:**
- Proje bazlı yetkilendirme
- Çoklu proje yöneticisi/şantiye şefi
- Activity log (tüm işlem geçmişi)
- Middleware: CheckProjectAccess

---

**Önceki Faz:** [Faz 2: Operasyonel Çekirdek](./faz2-operasyonel-moduller.md)
**Sonraki Faz:** [Faz 4: İleri Seviye](./faz4-ileri-seviye.md)
