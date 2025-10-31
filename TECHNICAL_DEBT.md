# Teknik Borç Listesi

## 🐛 Acil Düzeltilmesi Gerekenler

_Şu anda acil düzeltilmesi gereken teknik borç bulunmamaktadır._

---

## 📝 İyileştirmeler

### 1. ProgressPayments Link Method Hatası
**Durum:** ✅ Düzeltildi (2025-10-31)
**Dosyalar:**
- `resources/js/Pages/ProgressPayments/Show.vue`

**Yapılan Düzeltme:**
- Show.vue:220-228 satırlarındaki Link component'i template literal yerine `route()` helper kullanacak şekilde değiştirildi
- `/quantities/${id}` → `route('quantities.show', id)`
- Edit.vue'daki tüm Link kullanımları kontrol edildi, sorun tespit edilmedi
- Form submission'lar zaten `form.put()` ile doğru şekilde yapılıyordu

### 2. Pagination Component Standardizasyonu
**Durum:** ✅ Düzeltildi
- ActivityLogs, UserProjectRoles, RoutePermissions sayfalarında pagination linkleri düzeltildi

### 3. Modal Component Import Path
**Durum:** ✅ Düzeltildi
- `@/Components/Modal.vue` → `@/Components/UI/Modal.vue`

---

## 🔮 Gelecek Geliştirmeler

### Route Permission Sync Optimizasyonu
- Route sync işlemi sırasında batch insert kullanılabilir
- Chunk'lar halinde işlenebilir (100'er route)

### Activity Log Performans
- Old/new values için index eklenmeli
- Polymorphic ilişkiler için composite index

---

**Son Güncelleme:** 2025-10-31
**Güncelleyen:** Claude Code Assistant
