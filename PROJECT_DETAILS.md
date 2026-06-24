# 📋 تفاصيل مشروع «إزرع شجرة» — مرجع تصميم كامل

> هذا الملف مرجع شامل لتصميم التطبيق بدون الحاجة لمراجعة الكود.

---

## 📌 نظرة عامة

| البيان | القيمة |
|--------|--------|
| **اسم المشروع** | إزرع شجرة (NBTA) |
| **الدومين** | `https://nbta.diyala.net` |
| **الوصف** | منصة لمتابعة زراعة الأشجار — تطبيق موبايل للمزارعين + لوحة تحكم للإدارة |
| **اللغة** | العربية (RTL) |
| **المنصة** | Android / iOS (تطبيق موبايل) + Web Dashboard |

---

## 🏗️ البنية التقنية

| الطبقة | التقنية |
|--------|---------|
| **Backend** | Laravel 11 (PHP) |
| **API** | RESTful — JSON |
| **Auth** | Laravel Sanctum (Token-based) |
| **Database** | MySQL |
| **Dashboard Frontend** | Vue 3 + Vuetify 3 + Pinia + Vite |
| **Mobile App** | Flutter / React Native (يستهلك نفس الـ API) |
| **Maps** | Leaflet + OpenStreetMap |
| **Notifications** | Firebase Cloud Messaging (FCM) |
| **Permissions** | Spatie Permission (Roles & Permissions) |

---

## 👥 أنواع المستخدمين

### 1. المستخدم (User) — تطبيق الموبايل
- يسجل بالهاتف + كلمة السر
- يضيف نباتات (صورة + موقع GPS + بيانات)
- يشاهد نباتاته على الخريطة
- يبلغ عن نباتات مزيفة
- يرى جدول المتصدرين (Leaderboard)
- يشارك في الحملات

### 2. المشرف (Admin) — لوحة التحكم
- يسجل بالبريد الإلكتروني + كلمة السر
- يدير النباتات (قبول / رفض / تعليق)
- يدير المستخدمين (تفعيل / إيقاف / توثيق)
- يرى البلاغات ويحلها
- يرسل إشعارات
- يدير البانرات والإعلانات
- يدير الحملات والمحافظات
- يدير الأدوار والصلاحيات

---

## 🗄️ قاعدة البيانات — الجداول والعلاقات

### جدول `users` (المستخدمين)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| name | string | الاسم |
| phone | string (unique) | رقم الهاتف |
| email | string (nullable, unique) | البريد الإلكتروني |
| password | string (hashed) | كلمة السر |
| profile_photo | string (nullable) | مسار الصورة |
| governorate_id | FK → governorates | المحافظة |
| is_active | boolean (default true) | حالة التفعيل |
| is_trusted | boolean (default false) | توثيق المستخدم |
| created_at / updated_at | timestamp | |

### جدول `admins` (المشرفين)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| name | string | الاسم |
| email | string (unique) | البريد الإلكتروني |
| password | string (hashed) | كلمة السر |
| is_active | boolean (default true) | حالة التفعيل |
| governorate_id | FK → governorates (nullable) | المحافظة المسؤول عنها |
| created_at / updated_at | timestamp | |

### جدول `governorates` (المحافظات)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| name_ar | string | الاسم بالعربية |
| name_en | string | الاسم بالإنجليزية |
| is_covered | boolean | هل المحافظة مغطاة |

### جدول `plants` (النباتات)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| user_id | FK → users | المزارع |
| governorate_id | FK → governorates | المحافظة |
| campaign_id | FK → campaigns (nullable) | الحملة |
| name | string | اسم النبتة |
| type | string | الصنف |
| age | string | العمر |
| image | string (nullable) | صورة النبتة |
| latitude | decimal(10,8) | خط العرض |
| longitude | decimal(11,8) | خط الطول |
| location | geometry (POINT) | موقع جغرافي |
| notes | text (nullable) | ملاحظات |
| status | enum: pending / approved / rejected | الحالة |
| rejection_reason | text (nullable) | سبب الرفض |
| reviewed_by | FK → admins (nullable) | المشرف المراجع |
| reviewed_at | timestamp (nullable) | تاريخ المراجعة |
| created_at / updated_at | timestamp | |

### جدول `plant_reports` (البلاغات)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| plant_id | FK → plants | النبتة المبلّغ عنها |
| reporter_id | FK → users | المبلّغ |
| reason | text | سبب البلاغ |
| status | enum: pending / resolved | الحالة |
| created_at / updated_at | timestamp | |

### جدول `plant_status_logs` (سجل تغييرات حالة النباتات)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| plant_id | FK → plants | النبتة |
| admin_id | FK → admins | المشرف |
| old_status | string | الحالة القديمة |
| new_status | string | الحالة الجديدة |
| reason | text (nullable) | السبب |
| created_at | timestamp | |

### جدول `banners` (البانرات)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| image | string | صورة البانر |
| link | string (nullable) | رابط عند الضغط |
| is_active | boolean | مفعّل أو لا |
| sort_order | integer | ترتيب العرض |
| created_at / updated_at | timestamp | |

### جدول `screen_ads` (إعلانات شاشة البداية)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| image | string | صورة الإعلان |
| is_active | boolean | مفعّل أو لا |
| sort_order | integer | ترتيب العرض |
| created_at / updated_at | timestamp | |

### جدول `campaigns` (الحملات)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| title | string | العنوان |
| description | text (nullable) | الوصف |
| image | string (nullable) | صورة الحملة |
| target_plants | integer (nullable) | الهدف |
| is_active | boolean | مفعّلة أو لا |
| start_date | date (nullable) | تاريخ البداية |
| end_date | date (nullable) | تاريخ النهاية |
| created_at / updated_at | timestamp | |

### جدول `notification_logs` (سجل الإشعارات)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| title | string | العنوان |
| body | text | النص |
| image | string (nullable) | صورة مرفقة |
| target | enum: all / android / ios / topic | الفئة المستهدفة |
| topic | string (nullable) | اسم الـ Topic |
| sent_by | FK → admins | المرسِل |
| created_at | timestamp | |

### جدول `app_versions` (إصدارات التطبيق)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| platform | enum: android / ios | المنصة |
| version_number | string | رقم الإصدار |
| version_code | integer | كود الإصدار |
| update_type | enum: optional / forced | نوع التحديث |
| store_url | string | رابط المتجر |
| release_notes | text (nullable) | ملاحظات الإصدار |
| is_active | boolean | مفعّل أو لا |
| created_at / updated_at | timestamp | |

### جدول `user_levels` (مستويات المستخدمين)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| name | string | اسم المستوى |
| min_plants | integer | الحد الأدنى لعدد النباتات |
| created_at / updated_at | timestamp | |

### جدول `device_tokens` (توكنات الأجهزة)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| user_id | FK → users | المستخدم |
| token | string | FCM Token |
| platform | enum: android / ios | المنصة |
| created_at / updated_at | timestamp | |

### جدول `settings` (الإعدادات)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| key | string (unique) | المفتاح |
| value | text (nullable) | القيمة |
| created_at / updated_at | timestamp | |

### جدول `otp_verifications` (رموز التحقق)
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | مفتاح رئيسي |
| phone | string | رقم الهاتف |
| code | string | الرمز |
| attempts | tinyint | عدد المحاولات |
| verified | boolean | تم التحقق |
| expires_at | timestamp | انتهاء الصلاحية |
| ip_address | string (nullable) | |
| created_at / updated_at | timestamp | |

### جداول الصلاحيات (Spatie)
- `roles` — الأدوار
- `permissions` — الصلاحيات
- `model_has_roles` — ربط الأدوار بالمشرفين
- `model_has_permissions` — ربط الصلاحيات المباشرة
- `role_has_permissions` — ربط الصلاحيات بالأدوار

---

## 🔗 العلاقات (ERD)

```
governorates (1) ──── (N) users
governorates (1) ──── (N) plants
governorates (1) ──── (N) admins

users (1) ──── (N) plants
users (1) ──── (N) device_tokens
users (1) ──── (N) plant_reports (as reporter)

admins (1) ──── (N) plants (as reviewer)
admins (1) ──── (N) plant_status_logs
admins (1) ──── (N) notification_logs (as sender)

plants (1) ──── (N) plant_reports
plants (1) ──── (N) plant_status_logs
plants (N) ──── (1) campaigns

campaigns (1) ──── (N) plants
```

---

## 📱 شاشات تطبيق الموبايل

### 1. شاشة البداية (Splash)
- إعلانات شاشة البداية (`screen_ads`)
- شعار التطبيق
- التحقق من إصدار التطبيق

### 2. تسجيل الدخول / التسجيل
- **تسجيل الدخول:** هاتف + كلمة سر
- **تسجيل جديد:** اسم + هاتف + كلمة سر + تأكيد + محافظة
- **نسيت كلمة السر:** هاتف → OTP → كلمة سر جديدة
- التحقق من OTP (رمز 6 أرقام)

### 3. الصفحة الرئيسية
- بانرات متحركة (`banners`)
- حملات نشطة (`campaigns`)
- إحصائيات سريعة (عدد نباتاتي)
- زر «أضف نبتة جديدة»

### 4. إضافة نبتة
- اسم النبتة
- الصنف (type)
- العمر (age)
- صورة (from camera/gallery)
- الموقع (GPS auto + خريطة)
- المحافظة (auto from GPS or manual)
- ملاحظات (optional)
- الحملة (optional)

### 5. نباتاتي
- قائمة نباتات المستخدم
- فلترة بالحالة (معتمد / معلق / مرفوض)
- لكل نبتة: صورة، اسم، حالة، تاريخ

### 6. تفاصيل النبتة
- صورة كبيرة
- كل البيانات (اسم، صنف، عمر، موقع)
- حالة المراجعة + سبب الرفض (إن وجد)
- سجل تغييرات الحالة
- زر التعديل / الحذف
- زر الإبلاغ عن نبتة (للنباتات الأخرى)

### 7. الخريطة
- خريطة تفاعلية (OpenStreetMap)
- علامات لكل النباتات المعتمدة
- ألوان مختلفة حسب الحالة:
  - 🟢 أخضر = معتمد
  - 🟠 برتقالي = معلق
  - 🔴 أحمر = مرفوض
- النقر على علامة يفتح popup بتفاصيل النبتة

### 8. جدول المتصدرين (Leaderboard)
- ترتيب المستخدمين حسب عدد النباتات المعتمدة
- شارات/مستويات (user_levels)
- صورة + اسم + عدد النباتات + الترتيب

### 9. الملف الشخصي
- صورة + اسم + هاتف + بريد + محافظة
- إحصائيات (إجمالي النباتات، المعتمدة، المرفوضة)
- تعديل البيانات
- تغيير الصورة
- تغيير كلمة السر
- حذف الحساب

### 10. الإشعارات
- إشعارات FCM
- إشعار عند قبول/رفض نبتة
- إشعار بحملة جديدة

### 11. المدونة / الأخبار
- قائمة مقالات/أخبار (`blog`)

---

## 🖥️ شاشات لوحة التحكم (Dashboard)

### 1. تسجيل دخول المشرف
- بريد إلكتروني + كلمة سر
- شعار النظام

### 2. الصفحة الرئيسية (Home)
بطاقات إحصائية:
- إجمالي الأشجار (مع رابط لصفحة المحافظات)
- أشجار هذا الشهر
- طلبات معلقة
- إجمالي المستخدمين
- إجمالي الإبلاغات
- إبلاغات معلقة
- إبلاغات محلولة

### 3. إحصائيات المحافظات
- جدول بكل محافظة + عدد النباتات
- أكثر محافظة نشاطاً

### 4. إدارة النباتات
- جدول مع pagination + بحث + فلترة
- فلترة بالحالة (pending / approved / rejected)
- فلترة بالمحافظة
- لكل نبتة: صورة، اسم، المزارع، المحافظة، الحالة، التاريخ
- إجراءات: قبول / رفض (مع سبب) / تعليق / حذف
- صفحة تفاصيل النبتة مع سجل التغييرات

### 5. خريطة النباتات
- خريطة Leaflet بكامل النباتات
- فلترة بالحالة + المحافظة
- إحصائيات على البطاقات (إجمالي، معتمد، معلق، مرفوض)
- النقر على علامة يفتح popup مع رابط لتفاصيل النبتة

### 6. إدارة المستخدمين
- جدول مع pagination + بحث + فلترة
- فلترة بالحالة (نشط / موقوف) + التوثيق + المحافظة
- إحصائيات (إجمالي، موثق، نشط، موقوف)
- لكل مستخدم: صورة، اسم، هاتف، بريد، محافظة، حالة، توثيق
- إجراءات: تفعيل/إيقاف، توثيق/إلغاء توثيق، تعديل
- صفحة تفاصيل المستخدم:
  - بيانات أساسية + تعديل
  - تبويب: نظرة عامة (إحصائيات)
  - تبويب: النباتات (قائمة نباتاته)
  - تبويب: الإشعارات
  - تبويب: الأمان

### 7. إدارة البلاغات
- جدول البلاغات مع pagination
- فلترة بالحالة (pending / resolved)
- لكل بلاغ: النبتة، المبلّغ، السبب، الحالة
- إجراء: حل البلاغ

### 8. إدارة البانرات
- جدول البانرات
- إضافة / تعديل / حذف
- تفعيل / إيقاف
- ترتيب العرض

### 9. إدارة إعلانات الشاشة
- جدول الإعلانات
- إضافة / تعديل / حذف
- تفعيل / إيقاف

### 10. إدارة الإشعارات
- سجل الإشعارات المرسلة
- إرسال إشعار جديد (title + body + target + image)
- تعديل / حذف إشعار محفوظ
- الفئة المستهدفة: all / android / ios / topic

### 11. إدارة الحملات
- قائمة الحملات
- إضافة / تعديل / حذف
- عنوان + وصف + صورة + هدف + تواريخ

### 12. إدارة المحافظات
- قائمة المحافظات
- تفعيل/إيقاف التغطية

### 13. إدارة الإعدادات
- إعدادات عامة (key/value)
- وضع الصيانة
- إعدادات التغطية

### 14. إدارة إصدارات التطبيق
- قائمة الإصدارات (Android / iOS)
- إضافة / تعديل / حذف
- نوع التحديث: اختياري / إجباري

### 15. إدارة مستويات المستخدمين
- قائمة المستويات
- إضافة / تعديل / حذف
- اسم + حد أدنى لعدد النباتات

### 16. إدارة الأدوار والصلاحيات
- قائمة الأدوار (مع الصلاحيات)
- إضافة / تعديل / حذف دور
- قائمة الصلاحيات
- إدارة المشرفين (إضافة / تعديل / حذف)
- ربط مشرف بدور + محافظة

### 17. إشعارات الداشبورد (NavBar)
- أيقونة جرس بأعلى الصفحة
- عداد الإشعارات غير المقروءة
- إشعارات فورية (نبتة جديدة، بلاغ جديد)
- تحديد كمقروء / حذف
- Polling كل 60 ثانية

---

## 🔐 نظام الصلاحيات

### الأدوار
| الدور | الوصف |
|-------|-------|
| **superadmin** | صلاحية كاملة (manage all) |
| **admin** | صلاحيات حسب الأذونات المحددة |

### الصلاحيات (Permissions)
| الصلاحية | الوصف |
|----------|-------|
| `read_users` | عرض المستخدمين |
| `write_users` | تعديل المستخدمين |
| `read_plants` | عرض النباتات |
| `write_plants` | مراجعة النباتات (قبول/رفض) |
| `create_plants` | حذف النباتات |
| `read_reports` | عرض البلاغات |
| `write_reports` | حل البلاغات |
| `read_banners` | عرض البانرات |
| `write_banners` | تعديل البانرات |
| `create_banners` | إضافة/حذف البانرات |
| `read_screen_ads` | عرض إعلانات الشاشة |
| `write_screen_ads` | تعديل إعلانات الشاشة |
| `create_screen_ads` | إضافة/حذف إعلانات الشاشة |
| `read_governorates` | عرض المحافظات |
| `write_governorates` | تعديل تغطية المحافظات |
| `read_notifications` | عرض سجل الإشعارات |
| `create_notifications` | إرسال/حذف إشعارات |
| `write_notifications` | تعديل الإشعارات |
| `read_settings` | عرض الإعدادات |
| `write_settings` | تعديل الإعدادات |
| `read_app_versions` | عرض الإصدارات |
| `write_app_versions` | تعديل الإصدارات |
| `create_app_versions` | إضافة/حذف الإصدارات |
| `read_user_levels` | عرض المستويات |
| `write_user_levels` | تعديل المستويات |
| `read_campaigns` | عرض الحملات |
| `write_campaigns` | تعديل الحملات |
| `read_roles` | عرض الأدوار والمشرفين |
| `write_roles` | تعديل الأدوار والمشرفين |
| `create_roles` | إضافة/حذف الأدوار والمشرفين |

---

## 🎨 نظام الألوان (Dashboard)

| اللون | الاستخدام |
|-------|-----------|
| **أخضر (#28C76F)** | معتمد / نشط / نجاح |
| **برتقالي (#FF9F43)** | معلق / تحذير |
| **أحمر (#EA5455)** | مرفوض / خطأ / بلاغ |
| **بنفسجي (#7367F0)** | لون أساسي / روابط |
| **أزرق** | معلومات / مستخدمين |

---

## 📐 هيكل الـ API

### Base URL
```
https://nbta.diyala.net/api
```

### الـ Response الموحد
```json
{
  "success": true | false,
  "message": "رسالة",
  "data": { ... } | null
}
```

### الـ Pagination
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

### الـ Authentication
```
Authorization: Bearer {token}
```

---

## 🔄 مسارات المستخدم (User Flows)

### مسار المزارع
```
تسجيل → إضافة نبتة (صورة + GPS) → انتظار المراجعة → 
إشعار بالقبول/الرفض → النبتة تظهر على الخريطة →
تراكم النباتات → التقدم في جدول المتصدرين
```

### مسار المشرف
```
تسجيل دخول → الصفحة الرئيسية (إحصائيات) →
مراجعة النباتات المعلقة → قبول/رفض →
مراجعة البلاغات → حلها →
إرسال إشعار للمستخدمين
```

### مسار البلاغ
```
مستخدم يبلغ عن نبتة → البلاغ يظهر للإدارة →
المشرف يراجع البلاغ → يحلّه →
النبتة قد تُحذف أو تبقى
```

---

## 📊 البيانات المعروضة في Resources

### UserResource
```json
{
  "id": 1,
  "name": "أحمد علي",
  "email": null,
  "phone": "07801234567",
  "profile_photo": "https://...",
  "governorate": { "id": 1, "name_ar": "ديالى", "name_en": "Diyala", "is_covered": true },
  "is_active": true,
  "is_trusted": false,
  "plants_count": 12,
  "approved_plants_count": 8,
  "rejected_plants_count": 2,
  "plants": [...],
  "created_at": "2026-06-01"
}
```

### PlantResource
```json
{
  "id": 1,
  "name": "نخلة",
  "type": "نخيل",
  "age": "3 سنوات",
  "image": "https://...",
  "notes": "نبتة صحية",
  "latitude": "33.34100000",
  "longitude": "44.36100000",
  "status": "approved",
  "rejection_reason": null,
  "reviewed_at": "2026-06-10 15:30",
  "campaign_name": "حملة الربيع",
  "user": { "id": 1, "name": "أحمد علي", ... },
  "governorate": { "id": 1, "name_ar": "ديالى", ... },
  "status_logs": [
    {
      "id": 1,
      "old_status": "pending",
      "new_status": "approved",
      "reason": null,
      "created_at": "2026-06-10 15:30",
      "admin": { "name": "المشرف العام", "profile_photo": null }
    }
  ],
  "created_at": "2026-06-01 10:00"
}
```

### GovernorateResource
```json
{
  "id": 1,
  "name_ar": "ديالى",
  "name_en": "Diyala",
  "is_covered": true,
  "plants_count": 430
}
```

---

## 🗂️ هيكل مجلدات المشروع

```
izra3-shajara/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          ← controllers لوحة التحكم
│   │   │   ├── App/            ← controllers تطبيق الموبايل
│   │   │   └── Auth/           ← controllers المصادقة
│   │   └── Resources/          ← API Resources (JSON transformers)
│   ├── Models/                 ← Eloquent Models
│   ├── Services/               ← Business logic (AuthService, etc.)
│   └── Middleware/             ← AdminMiddleware, CheckMaintenanceMode
├── database/
│   └── migrations/             ← Database schema
├── routes/
│   ├── api.php                 ← كل API routes
│   └── web.php                 ← Dashboard SPA serving
├── dashboard/                   ← Vue 3 Dashboard Frontend
│   ├── src/
│   │   ├── pages/              ← صفحات الداشبورد (file-based routing)
│   │   ├── views/              ← مكونات الصفحات
│   │   ├── stores/             ← Pinia stores
│   │   ├── plugins/            ← axios, i18n, casl
│   │   └── layouts/            ← Layouts + NavBar components
│   ├── vite.config.ts
│   └── .env.production
├── public/
│   └── dashboard/              ← Build output (Vue dashboard)
└── API_DOCS.md                 ← توثيق الـ API الكامل
```

---

## 🌐 الـ Pinia Stores (Dashboard)

| Store | الوصف |
|-------|-------|
| `usePlantStore` | جلب/فلترة النباتات، قبول/رفض، خريطة |
| `useUserStore` | جلب/فلترة المستخدمين، تفعيل/توثيق |
| `useSettingStore` | الإعدادات، المحافظات، الإحصائيات |
| `useReportStore` | البلاغات، حل البلاغات |
| `useBannerStore` | البانرات |
| `useScreenAdStore` | إعلانات الشاشة |
| `useNotificationStore` | الإشعارات |
| `useRoleStore` | الأدوار والصلاحيات |

---

## ⚙️ ميزات تقنية مهمة

- **OTP:** رمز 6 أرقام لاستعادة كلمة السر (صلاحية 5 دقائق)
- **Rate Limiting:** 6 طلبات/دقيقة لـ auth routes
- **Geometry:** النباتات تخزن كـ POINT (GIS) للبحث المكاني
- **Image Storage:** `storage/app/public/` مع symlink لـ `public/storage/`
- **FCM:** إشعارات Firebase للموبايل
- **Maintenance Mode:** يمكن تفعيله من الإعدادات
- **App Version Check:** التحقق من إصدار التطبيق وإجبار التحديث
- **Governorate Coverage:** تحديد المحافظات المغطاة
- **Admin Governorate Filter:** المشرف يرى فقط بيانات محافظته (إن معيّن)
- **CASL:** صلاحيات على مستوى الواجهة (frontend route guards)
