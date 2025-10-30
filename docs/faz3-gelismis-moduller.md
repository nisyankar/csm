# FAZ 3: Gelişmiş Modüller
## 🚧 DEVAM EDİYOR (16.67%)

**Hedef:** Ocak - Mart 2026
**Durum:** Devam Ediyor
**Modül Sayısı:** 6 (1 tamamlandı)

---

## 🎯 MODÜLLER

### 1. Ekipman & Makine Yönetimi 🚜
**Database:** `equipments`, `equipment_usages`, `equipment_maintenance`
**Özellikler:**
- Kira ve mülkiyetli makine takibi
- Günlük kullanım/operatör kaydı
- Yakıt tüketimi
- Bakım geçmişi ve arıza kayıtları
- Maliyetlerin financial_transactions'a otomatik aktarımı

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
