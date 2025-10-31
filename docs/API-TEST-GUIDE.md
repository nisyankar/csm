# SPT Mobil API Test Kılavuzu

## Genel Bilgiler

**Base URL:** `http://localhost/api/v1`
**Authentication:** Bearer Token (Sanctum)
**Content-Type:** `application/json`

---

## 1. Authentication API

### 1.1 Login

**Endpoint:** `POST /auth/login`

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password",
  "device_name": "iPhone 13 Pro"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Giriş başarılı",
  "data": {
    "token": "1|abc123...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "user_type": "admin",
      "user_type_display": "Sistem Yöneticisi",
      "avatar_url": "https://...",
      "language": "tr",
      "timezone": "Europe/Istanbul"
    }
  }
}
```

**Test Command (cURL):**
```bash
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password",
    "device_name": "Test Device"
  }'
```

---

### 1.2 Get User Info (Me)

**Endpoint:** `GET /auth/me`
**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "user_type": "admin",
    "permissions": ["view-dashboard", "manage-employees"],
    "roles": ["admin"]
  }
}
```

**Test Command:**
```bash
curl -X GET http://localhost/api/v1/auth/me \
  -H "Authorization: Bearer {YOUR_TOKEN}"
```

---

### 1.3 Logout

**Endpoint:** `POST /auth/logout`
**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "message": "Çıkış başarılı"
}
```

---

### 1.4 Refresh Token

**Endpoint:** `POST /auth/refresh`
**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "message": "Token yenilendi",
  "data": {
    "token": "2|xyz456...",
    "token_type": "Bearer"
  }
}
```

---

## 2. Mobile Timesheet API

### 2.1 Clock In (Giriş)

**Endpoint:** `POST /mobile/timesheet/clock-in`
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "project_id": 1,
  "check_in_method": "manual",
  "check_in_location": {
    "latitude": 41.0082,
    "longitude": 28.9784
  },
  "notes": "Şantiyeye geldim"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Giriş başarıyla kaydedildi",
  "data": {
    "id": 123,
    "employee_id": 5,
    "project_id": 1,
    "date": "2025-10-31",
    "check_in_time": "08:30:00",
    "check_out_time": null,
    "status": "active",
    "employee": { ... },
    "project": { ... }
  }
}
```

**Test Command:**
```bash
curl -X POST http://localhost/api/v1/mobile/timesheet/clock-in \
  -H "Authorization: Bearer {YOUR_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "project_id": 1,
    "check_in_method": "manual",
    "notes": "Test girişi"
  }'
```

---

### 2.2 Clock Out (Çıkış)

**Endpoint:** `POST /mobile/timesheet/clock-out`
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "timesheet_id": 123,
  "check_out_method": "manual",
  "check_out_location": {
    "latitude": 41.0082,
    "longitude": 28.9784
  },
  "notes": "İş bitti, ayrılıyorum"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Çıkış başarıyla kaydedildi",
  "data": {
    "id": 123,
    "check_in_time": "08:30:00",
    "check_out_time": "17:30:00",
    "total_hours": 8.00,
    "regular_hours": 8.00,
    "overtime_hours": 0.00,
    "status": "completed"
  }
}
```

**Test Command:**
```bash
curl -X POST http://localhost/api/v1/mobile/timesheet/clock-out \
  -H "Authorization: Bearer {YOUR_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "notes": "İş tamamlandı"
  }'
```

---

### 2.3 Today Status

**Endpoint:** `GET /mobile/timesheet/today-status`
**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "has_clocked_in": true,
    "has_clocked_out": false,
    "timesheet": {
      "id": 123,
      "check_in_time": "08:30:00",
      "check_out_time": null,
      "project": { ... }
    },
    "working_hours": 2.5
  }
}
```

**Test Command:**
```bash
curl -X GET http://localhost/api/v1/mobile/timesheet/today-status \
  -H "Authorization: Bearer {YOUR_TOKEN}"
```

---

### 2.4 Week Summary

**Endpoint:** `GET /mobile/timesheet/week-summary`
**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_days": 5,
    "total_hours": 42.5,
    "total_regular_hours": 40.0,
    "total_overtime_hours": 2.5,
    "timesheets": [ ... ]
  }
}
```

---

### 2.5 Month Summary

**Endpoint:** `GET /mobile/timesheet/month-summary?year=2025&month=10`
**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "year": 2025,
    "month": 10,
    "month_name": "October",
    "total_days": 22,
    "total_hours": 176.0,
    "total_regular_hours": 176.0,
    "total_overtime_hours": 0.0,
    "timesheets": [ ... ]
  }
}
```

---

### 2.6 List Timesheets

**Endpoint:** `GET /mobile/timesheets?per_page=15&page=1`
**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number (default: 1)
- `employee_id` (optional): Filter by employee
- `project_id` (optional): Filter by project
- `date` (optional): Filter by specific date
- `start_date` & `end_date` (optional): Date range filter

**Response (200):**
```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

---

### 2.7 Offline Sync

**Endpoint:** `POST /mobile/sync/timesheets`
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "timesheets": [
    {
      "project_id": 1,
      "date": "2025-10-29",
      "check_in_time": "08:00:00",
      "check_out_time": "17:00:00",
      "notes": "Offline kaydı"
    },
    {
      "project_id": 1,
      "date": "2025-10-30",
      "check_in_time": "08:30:00",
      "check_out_time": "17:30:00",
      "notes": "Offline kaydı 2"
    }
  ]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Senkronizasyon tamamlandı",
  "data": {
    "success": [
      { "date": "2025-10-29", "timesheet_id": 124 },
      { "date": "2025-10-30", "timesheet_id": 125 }
    ],
    "failed": []
  }
}
```

---

## 3. Projects API

### 3.1 List Projects

**Endpoint:** `GET /projects`
**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "project_code": "PRJ-001",
      "name": "İstanbul Residence",
      "status": "active",
      "budget": 5000000.00,
      "completion_percentage": 65
    }
  ]
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Bu kayda erişim yetkiniz yok."
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation hatası",
  "errors": {
    "email": ["Email alanı zorunludur"],
    "password": ["Şifre en az 8 karakter olmalıdır"]
  }
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "İşlem sırasında bir hata oluştu: [error details]"
}
```

---

## Testing Tips

### 1. Test User Oluşturma

```bash
php artisan tinker
```

```php
$user = User::factory()->create([
    'email' => 'test@example.com',
    'password' => bcrypt('password123'),
    'api_access_enabled' => true,
    'is_active' => true,
]);

$employee = Employee::factory()->create([
    'user_id' => $user->id,
]);

$user->employee_id = $employee->id;
$user->save();
```

### 2. Postman Collection

1. Create a new collection: "SPT Mobile API"
2. Add environment variables:
   - `base_url`: http://localhost/api/v1
   - `token`: (will be set after login)
3. Add pre-request script to automatically add Bearer token:

```javascript
pm.request.headers.add({
    key: 'Authorization',
    value: 'Bearer ' + pm.environment.get('token')
});
```

### 3. Test Sequence

1. **Login** → Save token
2. **Get Me** → Verify authentication
3. **Today Status** → Check if already clocked in
4. **Clock In** → If not clocked in
5. **Today Status** → Verify clock in
6. **Clock Out** → End the day
7. **Week Summary** → Check weekly stats

---

## Rate Limiting

- Login endpoint: **60 requests per minute** (per IP)
- Other authenticated endpoints: **120 requests per minute** (per user)

---

## Security Notes

1. **HTTPS Only:** Production'da sadece HTTPS kullanılmalı
2. **Token Security:** Token'ları güvenli şekilde sakla (flutter_secure_storage)
3. **Token Expiration:** Sanctum token'ları default olarak süresi dolmaz, ancak config'de ayarlanabilir
4. **Permissions:** Her endpoint user type'a göre yetki kontrolü yapar
5. **Rate Limiting:** API abuse'i önlemek için rate limiting aktif

---

## 3. Progress Payments API (Hakediş Yönetimi)

### 3.1 Hakediş Listesi

**Endpoint:** `GET /v1/progress-payments`
**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `project_id`: Proje ID (zorunlu)
- `status`: Status filtresi (pending, in_progress, completed, approved, rejected, paid)
- `pending_approval`: Sadece onay bekleyenler (true/false)
- `year`: Yıl filtresi
- `month`: Ay filtresi
- `subcontractor_id`: Taşeron ID
- `per_page`: Sayfa başına kayıt (default: 20)

**Test Command:**
```bash
curl -X GET "http://localhost/api/v1/progress-payments?project_id=1&status=completed" \
  -H "Authorization: Bearer {YOUR_TOKEN}"
```

---

### 3.2 Onay Bekleyen Hakedişler

**Endpoint:** `GET /v1/progress-payments/pending-approvals`
**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "project": {
        "id": 1,
        "name": "Proje Adı",
        "code": "PRJ-001"
      },
      "subcontractor": {
        "id": 1,
        "name": "Taşeron Adı"
      },
      "completed_quantity": 100.00,
      "unit_price": 50.00,
      "total_amount": 5000.00,
      "completion_percentage": 100.00,
      "status": "completed"
    }
  ]
}
```

---

### 3.3 Hakediş Onaylama

**Endpoint:** `POST /v1/progress-payments/{id}/approve`
**Headers:** `Authorization: Bearer {token}`
**Required Role:** admin, project_manager, site_manager

**Request Body:**
```json
{
  "notes": "Hakediş onaylandı"
}
```

**Test Command:**
```bash
curl -X POST http://localhost/api/v1/progress-payments/1/approve \
  -H "Authorization: Bearer {YOUR_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "notes": "Onaylandı"
  }'
```

---

### 3.4 Hakediş Reddetme

**Endpoint:** `POST /v1/progress-payments/{id}/reject`
**Headers:** `Authorization: Bearer {token}`
**Required Role:** admin, project_manager, site_manager

**Request Body:**
```json
{
  "rejection_reason": "Eksik dökümanlar"
}
```

---

### 3.5 Hakediş İstatistikleri

**Endpoint:** `GET /v1/progress-payments/statistics?project_id=1`
**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "total_count": 50,
    "by_status": {
      "pending": 5,
      "in_progress": 10,
      "completed": 15,
      "approved": 18,
      "rejected": 2,
      "paid": 0
    },
    "total_amount": {
      "planned": 1000000.00,
      "completed": 850000.00,
      "approved": 750000.00
    },
    "average_completion": 85.50
  }
}
```

---

## 4. Materials API (Malzeme Yönetimi)

### 4.1 Düşük Stok Seviyesindeki Malzemeler

**Endpoint:** `GET /v1/materials/low-stock`
**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Çimento 50kg",
      "category": "İnşaat Malzemeleri",
      "unit": "adet",
      "estimated_unit_price": 45.00,
      "current_stock": 50.00,
      "min_stock_level": 100.00,
      "is_low_stock": true,
      "material_code": "MAT-001",
      "is_active": true
    }
  ]
}
```

**Test Command:**
```bash
curl -X GET http://localhost/api/v1/materials/low-stock \
  -H "Authorization: Bearer {YOUR_TOKEN}"
```

---

### 4.2 Malzeme Listesi

**Endpoint:** `GET /v1/materials`
**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `search`: Arama terimi
- `category`: Kategori filtresi
- `is_active`: Aktif/pasif filtresi (true/false)
- `per_page`: Sayfa başına kayıt (default: 15)

---

## 5. Notifications API (Bildirimler)

### 5.1 Bildirim Listesi

**Endpoint:** `GET /v1/notifications`
**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `unread_only`: Sadece okunmayanlar (true/false)
- `per_page`: Sayfa başına kayıt (default: 20)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "abc-123",
      "type": "progress_payment_approved",
      "title": "Hakediş Onaylandı",
      "message": "Hakediş #123 onaylandı",
      "data": {
        "progress_payment_id": 123,
        "action_url": "/progress-payments/123"
      },
      "is_read": false,
      "read_at": null,
      "created_at": "2025-10-31 14:30:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95,
    "unread_count": 12
  }
}
```

---

### 5.2 Okunmamış Bildirimleri Getir

**Endpoint:** `GET /v1/notifications/unread`
**Headers:** `Authorization: Bearer {token}`

---

### 5.3 Okunmamış Bildirim Sayısı

**Endpoint:** `GET /v1/notifications/unread-count`
**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "unread_count": 12
  }
}
```

---

### 5.4 Bildirimi Okundu Olarak İşaretle

**Endpoint:** `POST /v1/notifications/{id}/mark-as-read`
**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "Bildirim okundu olarak işaretlendi",
  "data": {
    "id": "abc-123",
    "read_at": "2025-10-31 14:35:00"
  }
}
```

---

### 5.5 Tüm Bildirimleri Okundu Olarak İşaretle

**Endpoint:** `POST /v1/notifications/mark-all-read`
**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "12 bildirim okundu olarak işaretlendi",
  "data": {
    "marked_count": 12
  }
}
```

---

### 5.6 FCM Token Kaydetme (Push Notifications)

**Endpoint:** `POST /v1/notifications/register-device`
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "fcm_token": "fcm_token_string_here",
  "device_name": "iPhone 13 Pro",
  "platform": "ios"
}
```

**Test Command:**
```bash
curl -X POST http://localhost/api/v1/notifications/register-device \
  -H "Authorization: Bearer {YOUR_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "fcm_token": "fcm_token_here",
    "device_name": "Test Device",
    "platform": "android"
  }'
```

---

## 6. File Upload API (Dosya Yükleme)

### 6.1 Genel Dosya Yükleme

**Endpoint:** `POST /v1/files/upload`
**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: multipart/form-data`

**Form Data:**
- `file`: File (max 10MB)
- `type`: image, document, avatar
- `reference_type`: (optional) project, material, timesheet
- `reference_id`: (optional) Reference ID

**Response:**
```json
{
  "success": true,
  "message": "Dosya başarıyla yüklendi",
  "data": {
    "filename": "doc_123456789.pdf",
    "original_name": "document.pdf",
    "path": "uploads/documents/doc_123456789.pdf",
    "url": "/storage/uploads/documents/doc_123456789.pdf",
    "full_url": "http://localhost/storage/uploads/documents/doc_123456789.pdf",
    "size": 524288,
    "mime_type": "application/pdf",
    "extension": "pdf"
  }
}
```

---

### 6.2 Görsel Yükleme

**Endpoint:** `POST /v1/files/upload-image`
**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: multipart/form-data`

**Form Data:**
- `image`: Image file (jpeg, png, jpg, gif - max 5MB)
- `reference_type`: (optional)
- `reference_id`: (optional)
- `resize`: (optional) true/false

---

### 6.3 Çoklu Görsel Yükleme

**Endpoint:** `POST /v1/files/upload-multiple-images`
**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: multipart/form-data`

**Form Data:**
- `images[]`: Multiple image files (max 10 images)
- `reference_type`: (optional)
- `reference_id`: (optional)

**Response:**
```json
{
  "success": true,
  "message": "3 görsel başarıyla yüklendi",
  "data": [
    {
      "filename": "img_1234567890_abc123.jpg",
      "path": "uploads/images/img_1234567890_abc123.jpg",
      "url": "/storage/uploads/images/img_1234567890_abc123.jpg",
      "full_url": "http://localhost/storage/uploads/images/img_1234567890_abc123.jpg"
    }
  ]
}
```

---

### 6.4 Profil Fotoğrafı Yükleme

**Endpoint:** `POST /v1/files/upload-avatar`
**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: multipart/form-data`

**Form Data:**
- `avatar`: Image file (jpeg, png, jpg - max 2MB)

---

### 6.5 Base64 Görsel Yükleme (Mobil Kamera)

**Endpoint:** `POST /v1/files/upload-base64`
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "image": "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
  "reference_type": "timesheet",
  "reference_id": 123
}
```

**Test Command:**
```bash
curl -X POST http://localhost/api/v1/files/upload-base64 \
  -H "Authorization: Bearer {YOUR_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "image": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
  }'
```

---

### 6.6 Dosya Silme

**Endpoint:** `POST /v1/files/delete`
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "path": "uploads/images/img_1234567890.jpg"
}
```

---

## Next Steps (Flutter Integration)

1. Dio client setup
2. AuthRepository implementation
3. Token interceptor
4. Error handling
5. Offline storage (Hive)
6. Background sync service
7. Push notifications setup (FCM)
8. Image picker & camera integration
9. File upload progress tracking

---

## API Endpoints Özeti

| Modül | Endpoint | Method | Açıklama |
|-------|----------|--------|----------|
| **Auth** | /auth/login | POST | Giriş yap |
| **Auth** | /auth/me | GET | Kullanıcı bilgisi |
| **Auth** | /auth/logout | POST | Çıkış yap |
| **Timesheet** | /mobile/timesheet/clock-in | POST | Giriş yap |
| **Timesheet** | /mobile/timesheet/clock-out | POST | Çıkış yap |
| **Timesheet** | /mobile/timesheet/today-status | GET | Bugünkü durum |
| **Progress Payments** | /v1/progress-payments | GET | Hakediş listesi |
| **Progress Payments** | /v1/progress-payments/{id}/approve | POST | Hakediş onayla |
| **Progress Payments** | /v1/progress-payments/{id}/reject | POST | Hakediş reddet |
| **Materials** | /v1/materials/low-stock | GET | Düşük stok malzemeler |
| **Notifications** | /v1/notifications | GET | Bildirim listesi |
| **Notifications** | /v1/notifications/unread-count | GET | Okunmamış sayısı |
| **Notifications** | /v1/notifications/mark-all-read | POST | Tümünü okundu yap |
| **Files** | /v1/files/upload-image | POST | Görsel yükle |
| **Files** | /v1/files/upload-base64 | POST | Base64 görsel yükle |

---

**Son Güncelleme:** 2025-10-31 (v1.1.0)
**API Version:** v1.1.0
**Yeni Eklenen Modüller:** Progress Payments, Materials (Low Stock), Notifications, File Upload
