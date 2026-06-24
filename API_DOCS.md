# 📡 توثيق API — مشروع إزرع شجرة

**Base URL:** `https://nbta.diyala.net/api`

**Content-Type:** `application/json`

**Authentication:** `Authorization: Bearer {token}`

---

## 📋 جدول المحتويات

- [🔓 Routes العامة](#-routes-العامة)
- [🔒 Routes المستخدم (App)](#-routes-المستخدم-app)
- [🛡️ Routes الداشبورد (Admin)](#️-routes-الداشبورد-admin)

---

## 🔓 Routes العامة

> لا تحتاج توكن

---

### `POST /auth/login`
**تسجيل دخول المستخدم**

**Body:**
```json
{
  "phone": "07801234567",
  "password": "password123"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "token": "1|abc123xyz...",
    "user": {
      "id": 1,
      "name": "أحمد علي",
      "phone": "07801234567",
      "email": null,
      "profile_photo": "https://nbta.diyala.net/storage/profiles/photo.jpg",
      "governorate": { "id": 1, "name_ar": "ديالى", "name_en": "Diyala" },
      "is_active": true,
      "is_trusted": false,
      "plants_count": 5,
      "created_at": "2026-06-01"
    }
  }
}
```

**Response 401:**
```json
{ "success": false, "message": "بيانات الدخول غير صحيحة" }
```

---

### `POST /auth/register`
**تسجيل مستخدم جديد**

**Body:**
```json
{
  "name": "أحمد علي",
  "phone": "07801234567",
  "password": "password123",
  "password_confirmation": "password123",
  "governorate_id": 1
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "تم إنشاء الحساب بنجاح",
  "data": {
    "token": "2|def456...",
    "user": { "id": 2, "name": "أحمد علي", ... }
  }
}
```

---

### `POST /auth/forgot-password`
**إرسال OTP لاستعادة كلمة السر**

**Body:**
```json
{ "phone": "07801234567" }
```

**Response 200:**
```json
{ "success": true, "message": "تم إرسال رمز التحقق" }
```

---

### `POST /auth/verify-otp`
**التحقق من OTP**

**Body:**
```json
{
  "phone": "07801234567",
  "code": "123456"
}
```

**Response 200:**
```json
{ "success": true, "message": "تم التحقق بنجاح" }
```

**Response 422:**
```json
{ "success": false, "message": "الرمز غير صحيح أو منتهي الصلاحية" }
```

---

### `POST /auth/reset-password`
**تغيير كلمة السر بعد التحقق**

**Body:**
```json
{
  "phone": "07801234567",
  "code": "123456",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response 200:**
```json
{ "success": true, "message": "تم تغيير كلمة السر بنجاح" }
```

---

### `POST /admin/auth/login`
**تسجيل دخول الأدمن**

**Body:**
```json
{
  "email": "admin@nbta.iq",
  "password": "admin123"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "token": "3|ghi789...",
    "admin": {
      "id": 1,
      "name": "المشرف العام",
      "email": "admin@nbta.iq",
      "governorate_id": null,
      "roles": ["superadmin"]
    }
  }
}
```

---

### `GET /banners`
**جلب البانرات النشطة**

**Response 200:**
```json
[
  {
    "id": 1,
    "image": "https://nbta.diyala.net/storage/banners/b1.jpg",
    "link": "https://example.com",
    "is_active": true,
    "sort_order": 1
  }
]
```

---

### `GET /governorates`
**جلب قائمة المحافظات**

**Response 200:**
```json
[
  { "id": 1, "name_ar": "ديالى", "name_en": "Diyala" },
  { "id": 2, "name_ar": "بغداد", "name_en": "Baghdad" }
]
```

---

### `GET /screen-ads`
**جلب إعلانات شاشة البداية**

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "image": "https://nbta.diyala.net/storage/screen-ads/ad1.jpg",
      "is_active": true
    }
  ]
}
```

---

### `GET /leaderboard`
**جدول المتصدرين**

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "أحمد علي",
      "profile_photo": "...",
      "plants_count": 25,
      "rank": 1
    }
  ]
}
```

---

### `GET /campaigns`
**جلب الحملات النشطة**

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "حملة تشجير الربيع",
      "description": "...",
      "image": "https://...",
      "starts_at": "2026-03-01",
      "ends_at": "2026-06-01"
    }
  ]
}
```

---

### `GET /app-settings`
**إعدادات التطبيق العامة**

**Response 200:**
```json
{
  "success": true,
  "data": {
    "maintenance_mode": false,
    "plant_approval_required": true,
    "map_provider": "osm"
  }
}
```

---

### `GET /app-version/check`
**التحقق من إصدار التطبيق**

**Query Params:** `platform=android&version_code=10`

**Response 200 (لا يحتاج تحديث):**
```json
{ "success": true, "data": { "needs_update": false } }
```

**Response 200 (يحتاج تحديث):**
```json
{
  "success": true,
  "data": {
    "needs_update": true,
    "update_type": "forced",
    "version_number": "2.0.0",
    "store_url": "https://play.google.com/...",
    "release_notes": "إصلاح مشاكل وتحسينات"
  }
}
```

---

### `GET /blog`
**جلب المقالات / الأخبار**

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "title": "فوائد زراعة الأشجار", "body": "...", "image": "..." }
  ]
}
```

---

## 🔒 Routes المستخدم (App)

> يتطلب: `Authorization: Bearer {user_token}`

---

### `POST /auth/logout`
**تسجيل الخروج**

**Response 200:**
```json
{ "success": true, "message": "تم تسجيل الخروج" }
```

---

### `GET /profile`
**جلب بيانات المستخدم الحالي**

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "أحمد علي",
    "phone": "07801234567",
    "email": null,
    "profile_photo": "https://...",
    "governorate": { "id": 1, "name_ar": "ديالى", "name_en": "Diyala" },
    "is_active": true,
    "is_trusted": false,
    "plants_count": 5,
    "created_at": "2026-06-01"
  }
}
```

---

### `PUT /profile`
**تحديث بيانات الحساب**

**Body:**
```json
{
  "name": "أحمد محمد",
  "governorate_id": 2
}
```

**Response 200:**
```json
{ "success": true, "message": "تم تحديث البيانات" }
```

---

### `POST /profile/photo`
**تحديث صورة الملف الشخصي**

**Body:** `multipart/form-data`
| Field | Type |
|-------|------|
| photo | image (max 2MB) |

**Response 200:**
```json
{
  "success": true,
  "message": "تم تحديث الصورة",
  "data": { "photo": "https://nbta.diyala.net/storage/profiles/new.jpg" }
}
```

---

### `PUT /profile/password`
**تغيير كلمة السر**

**Body:**
```json
{
  "current_password": "oldpass123",
  "password": "newpass123",
  "password_confirmation": "newpass123"
}
```

**Response 200:**
```json
{ "success": true, "message": "تم تغيير كلمة السر" }
```

---

### `DELETE /profile`
**حذف الحساب نهائياً**

**Response 200:**
```json
{ "success": true, "message": "تم حذف الحساب" }
```

---

### `POST /plants`
**إضافة نبتة جديدة**

**Body:** `multipart/form-data`
| Field | Type | Required |
|-------|------|----------|
| name | string | ✅ |
| type | string | ✅ |
| age | string | ✅ |
| latitude | decimal | ✅ |
| longitude | decimal | ✅ |
| governorate_id | integer | ✅ |
| notes | string | ❌ |
| image | image file | ❌ |
| campaign_id | integer | ❌ |

**Response 201:**
```json
{
  "success": true,
  "message": "تم إضافة النبتة",
  "data": {
    "id": 42,
    "name": "نخلة",
    "type": "نخيل",
    "age": "3 سنوات",
    "image": "https://...",
    "latitude": "33.34100000",
    "longitude": "44.36100000",
    "status": "pending",
    "governorate": { "id": 1, "name_ar": "ديالى", "name_en": "Diyala" },
    "user": { "id": 1, "name": "أحمد علي" },
    "created_at": "2026-06-22"
  }
}
```

---

### `GET /plants/my`
**نباتاتي (مع pagination)**

**Query Params:** `page=1&per_page=15`

**Response 200:**
```json
{
  "success": true,
  "data": [ { "id": 1, "name": "نخلة", "status": "approved", ... } ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

---

### `GET /plants/geojson`
**خريطة النباتات بصيغة GeoJSON**

**Query Params:** `governorate_id=1` (اختياري)

**Response 200:**
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": { "type": "Point", "coordinates": [44.361, 33.341] },
      "properties": { "id": 1, "name": "نخلة", "status": "approved" }
    }
  ]
}
```

---

### `GET /plants/nearby`
**النباتات القريبة**

**Query Params:** `lat=33.341&lng=44.361&radius=5000`

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "نخلة", "distance": 250, ... }
  ]
}
```

---

### `GET /plants/{id}`
**تفاصيل نبتة**

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "نخلة",
    "type": "نخيل",
    "age": "3 سنوات",
    "status": "approved",
    "image": "https://...",
    "latitude": "33.34100000",
    "longitude": "44.36100000",
    "notes": "نبتة صحية",
    "governorate": { "id": 1, "name_ar": "ديالى" },
    "user": { "id": 1, "name": "أحمد علي" },
    "status_logs": [
      {
        "id": 1,
        "old_status": "pending",
        "new_status": "approved",
        "reason": null,
        "admin": { "id": 1, "name": "المشرف" },
        "created_at": "2026-06-10T10:00:00"
      }
    ],
    "created_at": "2026-06-01"
  }
}
```

---

### `DELETE /plants/{id}`
**حذف نبتة (المالك فقط)**

**Response 200:**
```json
{ "success": true, "message": "تم حذف النبتة" }
```

**Response 403:**
```json
{ "success": false, "message": "غير مصرح" }
```

---

### `POST /plants/{id}/report`
**الإبلاغ عن نبتة**

**Body:**
```json
{ "reason": "هذه النبتة غير حقيقية" }
```

**Response 201:**
```json
{ "success": true, "message": "تم استلام بلاغك بنجاح، شكراً لتعاونك" }
```

**Response 422 (مبلّغ مسبقاً):**
```json
{ "success": false, "message": "لقد قمت بالإبلاغ عن هذه النبتة مسبقاً وبلاغك قيد المراجعة" }
```

---

### `POST /device-token`
**حفظ توكن الإشعارات**

**Body:**
```json
{
  "token": "fcm_token_here",
  "platform": "android"
}
```

**Response 200:**
```json
{ "success": true, "message": "تم حفظ التوكن" }
```

---

## 🛡️ Routes الداشبورد (Admin)

> يتطلب: `Authorization: Bearer {admin_token}`
> بعض الـ endpoints تتطلب صلاحية معينة موضحة بـ 🔑

---

### `POST /admin/auth/logout`
**تسجيل خروج الأدمن**

**Response 200:**
```json
{ "success": true, "message": "تم تسجيل الخروج" }
```

---

### `GET /admin/statistics`
**الإحصائيات العامة للداشبورد**

**Response 200:**
```json
{
  "success": true,
  "data": {
    "total_plants": 1200,
    "plants_this_month": 85,
    "most_active_governorate": { "id": 1, "name_ar": "ديالى", "plants_count": 430 },
    "pending_requests": 23,
    "total_users": 540,
    "users_this_month": 18,
    "total_reports": 12,
    "pending_reports": 4,
    "resolved_reports": 8
  }
}
```

---

### `GET /admin/statistics/governorates`
**إحصائيات النباتات لكل محافظة**

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name_ar": "ديالى", "name_en": "Diyala", "plants_count": 430 },
    { "id": 2, "name_ar": "بغداد", "name_en": "Baghdad", "plants_count": 320 }
  ]
}
```

---

### `GET /admin/users` 🔑 `read_users`
**قائمة المستخدمين**

**Query Params:**
| Param | Type | Description |
|-------|------|-------------|
| q | string | بحث بالاسم أو الهاتف أو الإيميل |
| governorate_id | integer | فلترة بالمحافظة |
| status | `active` / `suspended` | فلترة بالحالة |
| is_trusted | `true` / `false` | فلترة بالتوثيق |
| page | integer | رقم الصفحة |
| itemsPerPage | integer | عدد العناصر (-1 للكل) |

**Response 200:**
```json
{
  "success": true,
  "users": [
    {
      "id": 1,
      "name": "أحمد علي",
      "phone": "07801234567",
      "email": null,
      "profile_photo": "https://...",
      "governorate": { "id": 1, "name_ar": "ديالى", "name_en": "Diyala" },
      "is_active": true,
      "is_trusted": false,
      "plants_count": 12,
      "created_at": "2026-06-01"
    }
  ],
  "totalUsers": 540,
  "totalPage": 36,
  "page": 1,
  "stats": {
    "total": 540,
    "trusted": 45,
    "active": 510,
    "suspended": 30
  }
}
```

---

### `GET /admin/users/{id}` 🔑 `read_users`
**تفاصيل مستخدم**

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "أحمد علي",
    "phone": "07801234567",
    "email": null,
    "profile_photo": "https://...",
    "governorate": { "id": 1, "name_ar": "ديالى" },
    "is_active": true,
    "is_trusted": false,
    "plants_count": 12,
    "approved_plants_count": 8,
    "rejected_plants_count": 2,
    "plants": [ { "id": 1, "name": "نخلة", "status": "approved", ... } ],
    "created_at": "2026-06-01"
  }
}
```

---

### `PUT /admin/users/{id}` 🔑 `write_users`
**تعديل بيانات مستخدم**

**Body:**
```json
{
  "name": "أحمد محمد",
  "phone": "07801234568",
  "governorate_id": 2
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم تحديث بيانات المستخدم بنجاح",
  "data": { "id": 1, "name": "أحمد محمد", ... }
}
```

---

### `PATCH /admin/users/{id}/toggle` 🔑 `write_users`
**تفعيل/إيقاف مستخدم**

**Response 200:**
```json
{ "success": true, "message": "تم تفعيل المستخدم" }
```

---

### `PATCH /admin/users/{id}/toggle-trusted` 🔑 `write_users`
**توثيق/إلغاء توثيق مستخدم**

**Response 200:**
```json
{ "success": true, "message": "تم توثيق المستخدم" }
```

---

### `GET /admin/plants` 🔑 `read_plants`
**قائمة النباتات**

**Query Params:**
| Param | Type | Description |
|-------|------|-------------|
| q | string | بحث |
| status | `pending` / `approved` / `rejected` | فلترة |
| governorate_id | integer | فلترة بالمحافظة |
| page | integer | رقم الصفحة |
| itemsPerPage | integer | عدد العناصر |

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "نخلة",
      "type": "نخيل",
      "age": "3 سنوات",
      "status": "pending",
      "image": "https://...",
      "latitude": "33.34100000",
      "longitude": "44.36100000",
      "governorate": { "id": 1, "name_ar": "ديالى" },
      "user": { "id": 1, "name": "أحمد علي" },
      "status_logs": [],
      "created_at": "2026-06-01"
    }
  ],
  "meta": { "current_page": 1, "last_page": 10, "per_page": 15, "total": 150 }
}
```

---

### `GET /admin/plants/{id}` 🔑 `read_plants`
**تفاصيل نبتة**

**Response 200:** نفس structure نبتة واحدة مع `status_logs` كاملة

---

### `PATCH /admin/plants/{id}/approve` 🔑 `write_plants`
**قبول نبتة**

**Response 200:**
```json
{
  "success": true,
  "message": "تمت الموافقة على النبتة",
  "data": { "id": 1, "status": "approved", ... }
}
```

---

### `PATCH /admin/plants/{id}/reject` 🔑 `write_plants`
**رفض نبتة**

**Body:**
```json
{ "rejection_reason": "الصورة غير واضحة" }
```

**Response 200:**
```json
{
  "success": true,
  "message": "تم رفض النبتة",
  "data": { "id": 1, "status": "rejected", "rejection_reason": "الصورة غير واضحة", ... }
}
```

---

### `PATCH /admin/plants/{id}/pending` 🔑 `write_plants`
**إعادة النبتة لقيد المراجعة**

**Response 200:**
```json
{ "success": true, "message": "تم تعليق النبتة", "data": { ... } }
```

---

### `DELETE /admin/plants/{id}` 🔑 `create_plants`
**حذف نبتة**

**Response 200:**
```json
{ "success": true, "message": "تم حذف النبتة" }
```

---

### `GET /admin/reports` 🔑 `read_reports`
**قائمة البلاغات**

**Query Params:** `status=pending`, `governorate_id=1`, `page=1`, `itemsPerPage=15`

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "reason": "نبتة غير حقيقية",
      "status": "pending",
      "reporter": { "id": 5, "name": "خالد", "profile_photo": "..." },
      "plant": { "id": 10, "name": "نخلة", "image": "..." }
    }
  ],
  "meta": { "current_page": 1, "last_page": 2, "per_page": 15, "total": 20 }
}
```

---

### `PATCH /admin/reports/{id}/resolve` 🔑 `write_reports`
**حل بلاغ**

**Body:**
```json
{ "action": "resolved" }
```

**Response 200:**
```json
{ "success": true, "message": "تم حل البلاغ" }
```

---

### `GET /admin/admin-notifications`
**إشعارات الداشبورد للأدمن**

**Response 200:**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": "uuid-here",
        "type": "new_plant",
        "title": "نبتة جديدة",
        "subtitle": "أحمد أضاف نخلة",
        "plant_id": 42,
        "report_id": null,
        "time": "منذ 5 دقائق",
        "isSeen": false
      }
    ],
    "unreadCount": 3
  }
}
```

---

### `POST /admin/admin-notifications/mark-read`
**تحديد إشعارات كمقروءة**

**Body:**
```json
{ "ids": ["uuid-1", "uuid-2"] }
```

**Response 200:**
```json
{ "success": true, "message": "تم التحديد كمقروء" }
```

---

### `DELETE /admin/admin-notifications/{id}`
**حذف إشعار**

**Response 200:**
```json
{ "success": true, "message": "تم مسح الإشعار" }
```

---

### `GET /admin/banners` 🔑 `read_banners`
**قائمة البانرات**

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "image": "https://...", "link": "https://...", "is_active": true, "sort_order": 1 }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 4 }
}
```

---

### `POST /admin/banners` 🔑 `create_banners`
**إضافة بانر**

**Body:** `multipart/form-data`
| Field | Type | Required |
|-------|------|----------|
| image | image file | ✅ |
| link | string (URL) | ❌ |
| sort_order | integer | ❌ |

**Response 201:**
```json
{ "success": true, "message": "تم إضافة البانر", "data": { "id": 5, ... } }
```

---

### `PUT /admin/banners/{id}` 🔑 `write_banners`
**تعديل بانر**

**Body:** `multipart/form-data` (نفس الـ POST)

**Response 200:**
```json
{ "success": true, "message": "تم تحديث البانر", "data": { ... } }
```

---

### `PATCH /admin/banners/{id}/toggle` 🔑 `write_banners`
**تفعيل/إيقاف بانر**

**Response 200:**
```json
{ "success": true, "message": "تم تحديث حالة البانر" }
```

---

### `DELETE /admin/banners/{id}` 🔑 `create_banners`
**حذف بانر**

**Response 200:**
```json
{ "success": true, "message": "تم حذف البانر" }
```

---

### `GET /admin/screen-ads` 🔑 `read_screen_ads`
**إعلانات شاشة البداية**

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "image": "https://...", "is_active": true }
  ],
  "meta": { ... }
}
```

---

### `POST /admin/screen-ads` 🔑 `create_screen_ads`
**إضافة إعلان شاشة**

**Body:** `multipart/form-data`
| Field | Type | Required |
|-------|------|----------|
| image | image file | ✅ |

**Response 201:**
```json
{ "success": true, "data": { "id": 2, "image": "https://...", "is_active": true } }
```

---

### `PUT /admin/screen-ads/{id}` 🔑 `write_screen_ads`
**تعديل إعلان شاشة** (يرسل كـ POST مع `_method=PUT`)

---

### `PATCH /admin/screen-ads/{id}/toggle-status` 🔑 `write_screen_ads`
**تفعيل/إيقاف إعلان**

**Response 200:**
```json
{ "success": true, "message": "تم تحديث الحالة" }
```

---

### `DELETE /admin/screen-ads/{id}` 🔑 `create_screen_ads`
**حذف إعلان شاشة**

**Response 200:**
```json
{ "success": true, "message": "تم حذف الإعلان" }
```

---

### `GET /admin/governorates` 🔑 `read_governorates`
**قائمة المحافظات للأدمن**

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name_ar": "ديالى", "name_en": "Diyala", "is_covered": true }
  ]
}
```

---

### `PATCH /admin/governorates/{id}/toggle-coverage` 🔑 `write_governorates`
**تفعيل/إيقاف تغطية محافظة**

**Response 200:**
```json
{ "success": true, "message": "تم تحديث التغطية" }
```

---

### `GET /admin/notifications` 🔑 `read_notifications`
**سجل الإشعارات المرسلة**

**Query Params:** `page=1&itemsPerPage=15`

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "تحديث مهم",
      "body": "تم إطلاق نسخة جديدة",
      "target": "all",
      "topic": null,
      "image": null,
      "sent_by": { "id": 1, "name": "المشرف العام" },
      "created_at": "2026-06-20T10:00:00"
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 42 }
}
```

---

### `POST /admin/notifications/send` 🔑 `create_notifications`
**إرسال إشعار جديد**

**Body:** `multipart/form-data`
| Field | Type | Values | Required |
|-------|------|--------|----------|
| title | string | - | ✅ |
| body | string | - | ✅ |
| target | string | `all`, `android`, `ios`, `topic` | ✅ |
| topic | string | اسم الـ topic | إذا target=topic |
| image | image file | - | ❌ |

**Response 200:**
```json
{ "success": true, "message": "تم إرسال الإشعار" }
```

---

### `POST /admin/notifications/{id}` 🔑 `write_notifications`
**تعديل إشعار محفوظ**

**Body:**
```json
{
  "title": "عنوان جديد",
  "body": "نص جديد",
  "target": "all"
}
```

**Response 200:**
```json
{ "success": true, "message": "تم تحديث الإشعار" }
```

---

### `DELETE /admin/notifications/{id}` 🔑 `create_notifications`
**حذف إشعار محفوظ**

**Response 200:**
```json
{ "success": true, "message": "تم حذف الإشعار" }
```

---

### `GET /admin/settings` 🔑 `read_settings`
**جلب الإعدادات**

**Response 200:**
```json
{
  "success": true,
  "data": {
    "maintenance_mode": "false",
    "plant_approval_required": "true",
    "map_provider": "osm",
    "firebase_server_key": "...",
    "app_name": "إزرع شجرة"
  }
}
```

---

### `PUT /admin/settings` 🔑 `write_settings`
**تحديث الإعدادات**

**Body:** `multipart/form-data` (أي key/value)
```json
{
  "maintenance_mode": "true",
  "plant_approval_required": "false"
}
```

**Response 200:**
```json
{ "success": true, "message": "تم تحديث الإعدادات" }
```

---

### `PUT /admin/settings/coverage` 🔑 `write_settings`
**تحديث إعدادات التغطية**

**Body:**
```json
{
  "coverage_mode": "custom",
  "governorate_ids": [1, 3, 5]
}
```

**Response 200:**
```json
{ "success": true, "message": "تم تحديث إعدادات التغطية" }
```

---

### `GET /admin/app-versions` 🔑 `read_app_versions`
**إصدارات التطبيق**

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "platform": "android",
      "version_number": "1.5.0",
      "version_code": 15,
      "update_type": "optional",
      "store_url": "https://play.google.com/...",
      "release_notes": "تحسينات عامة",
      "is_active": true
    }
  ]
}
```

---

### `POST /admin/app-versions` 🔑 `create_app_versions`
**إضافة إصدار جديد**

**Body:**
```json
{
  "platform": "android",
  "version_number": "2.0.0",
  "version_code": 20,
  "update_type": "forced",
  "store_url": "https://play.google.com/...",
  "release_notes": "إضافات كبيرة",
  "is_active": true
}
```

**Response 201:**
```json
{ "success": true, "data": { "id": 2, ... } }
```

---

### `PUT /admin/app-versions/{id}` 🔑 `write_app_versions`
**تعديل إصدار** (نفس body الـ POST)

---

### `DELETE /admin/app-versions/{id}` 🔑 `create_app_versions`
**حذف إصدار**

**Response 200:**
```json
{ "success": true, "message": "تم حذف الإصدار" }
```

---

### `GET /admin/user-levels` 🔑 `read_user_levels`
**مستويات المستخدمين (شارات اللوحة)**

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "مبتدئ", "min_plants": 1, "max_plants": 10, "badge_icon": "🌱" }
  ]
}
```

---

### `POST /admin/user-levels` 🔑 `write_user_levels`
**إضافة مستوى**

**Body:**
```json
{
  "name": "خبير",
  "min_plants": 50,
  "max_plants": 100,
  "badge_icon": "🌳"
}
```

---

### `PUT /admin/user-levels/{id}` 🔑 `write_user_levels`
**تعديل مستوى**

---

### `DELETE /admin/user-levels/{id}` 🔑 `write_user_levels`
**حذف مستوى**

**Response 200:**
```json
{ "success": true, "message": "تم الحذف" }
```

---

### `GET /admin/campaigns` 🔑 `read_campaigns`
**قائمة الحملات**

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "حملة الربيع",
      "description": "...",
      "image": "https://...",
      "starts_at": "2026-03-01",
      "ends_at": "2026-06-01",
      "plants_count": 120
    }
  ]
}
```

---

### `POST /admin/campaigns` 🔑 `write_campaigns`
**إنشاء حملة**

**Body:** `multipart/form-data`
| Field | Type | Required |
|-------|------|----------|
| title | string | ✅ |
| description | string | ❌ |
| image | image file | ❌ |
| starts_at | date | ❌ |
| ends_at | date | ❌ |

---

### `PUT /admin/campaigns/{id}` 🔑 `write_campaigns`
**تعديل حملة**

---

### `DELETE /admin/campaigns/{id}` 🔑 `write_campaigns`
**حذف حملة**

**Response 200:**
```json
{ "success": true, "message": "تم حذف الحملة" }
```

---

### `GET /admin/roles` 🔑 `read_roles`
**قائمة الأدوار**

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "superadmin",
      "guard_name": "admin",
      "permissions": [
        { "id": 1, "name": "read_users" },
        { "id": 2, "name": "write_users" }
      ]
    }
  ]
}
```

---

### `GET /admin/roles/{id}` 🔑 `read_roles`
**تفاصيل دور**

**Response 200:** نفس object الدور أعلاه

---

### `POST /admin/roles` 🔑 `create_roles`
**إنشاء دور جديد**

**Body:**
```json
{
  "name": "محرر",
  "permissions": ["read_plants", "write_plants", "read_reports"]
}
```

**Response 201:**
```json
{ "success": true, "message": "تم إنشاء الدور بنجاح", "data": { ... } }
```

---

### `PUT /admin/roles/{id}` 🔑 `write_roles`
**تعديل دور (ما عدا superadmin)**

**Body:** نفس POST

---

### `DELETE /admin/roles/{id}` 🔑 `create_roles`
**حذف دور (ما عدا superadmin)**

**Response 200:**
```json
{ "success": true, "message": "تم حذف الدور بنجاح" }
```

---

### `GET /admin/permissions` 🔑 `read_roles`
**قائمة الصلاحيات**

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "read_users", "guard_name": "admin" },
    { "id": 2, "name": "write_users", "guard_name": "admin" }
  ]
}
```

---

### `GET /admin/admins` 🔑 `read_roles`
**قائمة المشرفين**

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "المشرف العام",
      "email": "admin@nbta.iq",
      "is_active": true,
      "governorate_id": null,
      "governorate": null,
      "roles": [{ "id": 1, "name": "superadmin" }]
    }
  ]
}
```

---

### `POST /admin/admins` 🔑 `create_roles`
**إضافة مشرف جديد**

**Body:**
```json
{
  "name": "مشرف ديالى",
  "email": "diyala@nbta.iq",
  "password": "pass123",
  "role": "admin",
  "governorate_id": 1
}
```

**Response 201:**
```json
{ "success": true, "message": "تم إنشاء المشرف بنجاح", "data": { ... } }
```

---

### `PUT /admin/admins/{id}` 🔑 `write_roles`
**تعديل مشرف**

**Body:**
```json
{
  "name": "اسم جديد",
  "email": "new@nbta.iq",
  "password": "newpass",
  "role": "admin",
  "governorate_id": 2
}
```

---

### `DELETE /admin/admins/{id}` 🔑 `create_roles`
**حذف مشرف**

**Response 200:**
```json
{ "success": true, "message": "تم حذف المشرف بنجاح" }
```

**Response 403 (حذف نفسك):**
```json
{ "success": false, "message": "لا يمكنك حذف حسابك الخاص" }
```

---

## 📌 ملاحظات عامة

### هيكل الـ Response الموحد:
```json
{
  "success": true | false,
  "message": "رسالة",
  "data": { ... } | null
}
```

### أكواد الخطأ الشائعة:
| Code | المعنى |
|------|--------|
| 401 | توكن منتهي أو غير صحيح |
| 403 | ليس لديك صلاحية |
| 404 | العنصر غير موجود |
| 422 | بيانات الطلب غير صحيحة (Validation) |
| 429 | طلبات كثيرة (Rate Limit) |
| 500 | خطأ في السيرفر |

### Rate Limiting:
- Auth routes: **6 طلبات / دقيقة**
- باقي الـ routes: بدون حد

### Pagination (الصفحات):
```json
{
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```
