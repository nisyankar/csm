# FAZ 3: Gelişmiş Modüller
## 📋 PLANLANIY OR (0%)

**Hedef:** Ocak - Mart 2026
**Durum:** Planlanıyor
**Modül Sayısı:** 6

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

### 2. İş Sağlığı & Güvenliği (İSG) 👷
**Database:** `safety_incidents`, `safety_trainings`, `safety_inspections`, `risk_assessments`
**Özellikler:**
- İş kazası kayıtları + fotoğraf
- Eğitim planlama
- Kontrol listeleri
- Risk analiz formları
- PDF export

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
