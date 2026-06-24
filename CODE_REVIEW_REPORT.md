# 📋 تقرير مراجعة الكود — مشروع «إزرع شجرة»

> مراجعة شاملة بدون أي تعديل على الكود.

---

## 🔴 مشاكل حرجة (Critical)

### 1. SQL Injection في `PlantRepository::getNearby`
**الملف:** `app/Repositories/PlantRepository.php:106-122`
```
ST_GeomFromText('POINT({$lng} {$lat})')
```
المتغيرات `$lng` و `$lat` مُدخلة مباشرة في SQL بدون parameter binding في `ST_GeomFromText`. رغم وجود validation في Controller، إلا أن هذا لا يكفي لمنع SQL Injection لأن القيم تُمرر كـ string داخل raw SQL.

### 2. SQL Injection في `Plant::booted` (saving event)
**الملف:** `app/Models/Plant.php:41-47`
```
$plant->location = DB::raw(
    "ST_GeomFromText('POINT({$plant->longitude} {$plant->latitude})')"
);
```
نفس المشكلة — القيم تُدخل مباشرة في raw SQL.

### 3. `FirebaseNotificationService` — كل الإشعارات معطلة (TODO)
**الملف:** `app/Services/FirebaseNotificationService.php`
كل الدوال (`sendToUser`, `sendToAdmin`, `sendToAll`, `sendToTopic`) فارغة وتحتوي على `// TODO`. هذا يعني:
- المستخدم لا يستلم إشعار عند قبول/رفض نبتته
- الإشعارات المرسلة من الداشبورد لا تصل للموبايل
- إشعار "نبتة جديدة" لا يصل للمشرفين

### 4. `AppVersionController::store` — Mass Assignment
**الملف:** `app/Http/Controllers/Admin/AppVersionController.php:32`
```
$version = AppVersion::create($request->all());
```
يستخدم `$request->all()` بدلاً من `$request->only([...])` أو `$request->validated()`. هذا يسمح بإضافة حقول غير مرغوبة.

### 5. `AppVersionController::update` — Mass Assignment
**الملف:** `app/Http/Controllers/Admin/AppVersionController.php:48`
```
$appVersion->update($request->all());
```
نفس المشكلة — يستخدم `$request->all()` بدلاً من الحقول المحددة.

---

## 🟠 مشاكل أمنية (Security)

### 6. `SettingController::update` — يقبل أي key/value بدون تحقق
**الملف:** `app/Http/Controllers/Admin/SettingController.php:27-38`
```
foreach ($request->all() as $key => $value) {
    if (in_array($key, $skipKeys)) continue;
    Setting::setValue($key, $value);
}
```
لا يوجد whitelist للمفاتيح المسموح بها. أي مشرف يمكنه إضافة/تعديل أي setting، بما في ذلك إعدادات حساسة مثل `otp_api_key` أو `maintenance_mode`.

### 7. `AdminMiddleware` — لا يتحقق من `is_active`
**الملف:** `app/Middleware/AdminMiddleware.php:13`
```
if (!$request->user() || !($request->user() instanceof Admin))
```
يتحقق فقط من نوع المستخدم، لكن لا يتحقق من `is_active`. مشرف موقوف (is_active = false) لا يزال يستطيع استخدام الـ API طالما لديه توكن صالح.

### 8. `AuthService::loginUser` — لا فرق في رسالة الخطأ
**الملف:** `app/Services/AuthService.php:15-21`
عند فشل الدخول أو الحساب موقوف، يرجع `null` في الحالتين. هذا جيد للأمان (لا يكشف إذا كان الهاتف موجود)، لكن قد يربك المستخدم.

### 9. لا يوجد rate limiting على admin logout
**الملف:** `routes/api.php:145`
```
Route::post('auth/logout', [AdminAuthController::class, 'logout']);
```
داخل مجموعة `admin` middleware — لا مشكلة أمنية كبيرة، لكن لو تسرب التوكن يمكن استخدامه حتى انتهاء صلاحيته.

### 10. `NotificationController::send` — `auth()->id()` قد يرجع null
**الملف:** `app/Http/Controllers/Admin/NotificationController.php:44`
```
'sent_by' => auth()->id(),
```
يستخدم `auth()->id()` بدلاً من `request()->user()->id`. في سياق API مع Sanctum هذا يعمل، لكن `auth()` قد يرجع null في بعض الحالات.

---

## 🟡 مشاكل وظيفية (Functional Bugs)

### 11. `UserController::index` — هيكل response مختلف عن باقي الـ endpoints
**الملف:** `app/Http/Controllers/Admin/UserController.php:66-73`
```
return response()->json([
    'success' => true,
    'users' => UserResource::collection($users)->resolve(),
    'totalPage' => $users->lastPage(),
    'totalUsers' => $users->total(),
    'page' => $users->currentPage(),
    'stats' => $stats,
]);
```
كل الـ endpoints الأخرى تستخدم `data` + `meta`، لكن هذا الـ endpoint يستخدم `users`, `totalPage`, `totalUsers`, `page`. هذا قد يسبب مشاكل في الفرونت إن لم يكن متوافقاً.

### 12. `BannerController` و `ScreenAdController` — لا يحذفون الصور القديمة
**الملف:** `app/Http/Controllers/Admin/BannerController.php:56-58`
**الملف:** `app/Http/Controllers/Admin/ScreenAdController.php:53-56`
عند تعديل بانر/إعلان وتغيير الصورة، الصورة القديمة لا تُحذف من التخزين. هذا يسبب تراكم ملفات orphaned.

### 13. `BannerController::destroy` و `ScreenAdController::destroy` — لا يحذفون الصورة
**الملف:** `app/Http/Controllers/Admin/BannerController.php:70-75`
**الملف:** `app/Http/Controllers/Admin/ScreenAdController.php:62-67`
عند حذف بانر/إعلان، الصورة المرتبطة لا تُحذف من التخزين.

### 14. `PlantController::destroy` (App) — لا يحذف صورة النبتة
**الملف:** `app/Http/Controllers/App/PlantController.php:66-75`
عند حذف نبتة، الصورة لا تُحذف من التخزين.

### 15. `AdminPlantController::destroy` — لا يحذف صورة النبتة
**الملف:** `app/Http/Controllers/Admin/PlantController.php:82-87`
نفس المشكلة — حذف النبتة من قاعدة البيانات بدون حذف الصورة.

### 16. `NotificationController::update` — لا يحذف الصورة القديمة
**الملف:** `app/Http/Controllers/Admin/NotificationController.php:70-73`
عند تحديث إشعار وتغيير الصورة، الصورة القديمة لا تُحذف.

### 17. `ScreenAdController::update` (Admin) — الصورة required في التعديل
**الملف:** `app/Http/Controllers/Admin/ScreenAdController.php:50`
```
'image' => 'required|image|max:5120',
```
الصورة مطلوبة حتى في التعديل. هذا يعني لا يمكن تعديل الإعلان بدون تغيير الصورة. يجب أن تكون `nullable`.

### 18. مسار `roles` مكرر في `routes/api.php`
**الملف:** `routes/api.php:243` و `routes/api.php:253`
```
Route::middleware('permission:read_roles')->get('roles', [RoleController::class, 'index']);  // line 243
...
Route::middleware('permission:read_roles')->group(function () {
    Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index']);       // line 253
```
نفس المسار مسجل مرتين. Laravel سيستخدم آخر واحد فقط، لكن هذا قد يسبب سلوك غير متوقع.

### 19. `AdminNotificationController` — لا يستخدم `ApiResponse` بشكل متسق
**الملف:** `app/Http/Controllers/Admin/AdminNotificationController.php`
يستخدم `auth()->user()` بدلاً من `request()->user()`. في سياق API مع Sanctum، `auth()` قد لا يعمل بشكل صحيح في جميع الحالات.

### 20. `PlantRepository::getNearby` — لا يتحقق من قيم سالبة للـ radius
**الملف:** `app/Repositories/PlantRepository.php:104`
```
public function getNearby(float $lat, float $lng, int $radius = 5000): Collection
```
رغم وجود validation في Controller (`min:100|max:50000`)، لكن الـ Repository نفسه لا يتحقق.

### 21. `CampaignController::update` (Admin) — يستخدم `$request->except('image')` لكن `is_active` قد لا تكون موجودة
**الملف:** `app/Http/Controllers/Admin/CampaignController.php:59`
```
$data = $request->except('image');
```
إذا لم يتم إرسال `is_active`، سيتم تعيينها كـ null في الـ update (إذا كانت mass assignable). لكن الـ validation يتطلب `required|boolean`، فهذا ليس مشكلة فعلية.

### 22. `OtpService::isDisabled` — قيمة default هي `true` (string)
**الملف:** `app/Services/OtpService.php:26`
```
return !Setting::getValue('feature_otp_verification', true);
```
القيمة الافتراضية هي `true` (boolean)، لكن `Setting::getValue` يرجع string من قاعدة البيانات. المقارنة `!true` ستكون `false`، لكن `!'true'` (string) ستكون `false` أيضاً. هذا قد يعمل بشكل غير متوقع إذا كانت القيمة المخزنة هي `'false'` (string) — `!'false'` ستكون `false` (لأن أي string غير فارغ هو truthy).

---

## 🟢 مشاكل تصميمية وأفضل الممارسات (Code Quality)

### 23. لا يوجد API Resources موحد في عدة endpoints
عدة endpoints ترجع بيانات مباشرة بدون Resource:
- `BannerController` (public route) — يرجع Collection مباشرة
- `GovernorateController` (public route) — يرجع Resource Collection لكن بدون `success` wrapper
- `SettingController::index` — يرجع `pluck` مباشرة
- `LeaderboardController` — يرجع array مباشرة

### 24. `BlogController` — لا يستخدم `ApiResponse` trait
**الملف:** `app/Http/Controllers/App/BlogController.php`
يرجع `response()->json(...)` مباشرة بدلاً من استخدام `ApiResponse`.

### 25. لا يوجد logging للعمليات الحساسة
- حذف النباتات، البانرات، الإعلانات، الحملات، المستخدمين — لا يوجد log لمن قام بالحذف ومتى.
- تغيير الأدوار والصلاحيات — لا يوجد audit trail.

### 26. `PlantService::store` — `admin_id` = null في status log
**الملف:** `app/Services/PlantService.php:50`
```
'admin_id' => null,
```
عند الاعتماد التلقائي، `admin_id` يسجل كـ null. هذا قد يسبب مشاكل في العرض إذا كان الـ frontend يتوقع قيمة.

### 27. لا يوجد token expiration
**الملف:** `app/Services/AuthService.php:23, 58`
```
$token = $user->createToken('user-token')->plainTextToken;
$token = $admin->createToken('admin-token')->plainTextToken;
```
التوكنات لا تحتوي على `expires_at`. ستبقى صالحة للأبد ما لم تُحذف يدوياً.

### 28. `Admin` model — لا يوجد `is_trusted` field
**الملف:** `app/Models/Admin.php`
جدول `admins` لا يحتوي على `governorate_id` في الـ migration الأولي، لكن الـ Model يسمح به. يجب التأكد من وجود migration يضيف هذا الحقل.

### 29. `routes/api.php` — استيراد مكرر
**الملف:** `routes/api.php:9`
```
use App\Http\Controllers\Admin\PlantController as AdminPlantController;
```
و في line 243:
```
Route::middleware('permission:read_roles')->get('roles', [RoleController::class, 'index']);
```
`RoleController` غير مُستورد في أعلى الملف، لكنه يعمل بسبب الـ full namespace في line 253.

### 30. `NotificationLog` — لا يوجد `updated_at`
**الملف:** `app/Models/NotificationLog.php:10`
```
public $timestamps = false;
```
لكن الـ migration تحتوي على `created_at` فقط. هذا يعني لا يمكن تتبع تعديل الإشعارات.

### 31. `PlantReport` — لا يوجد `resolved_at` أو `resolved_by`
**الملف:** `app/Models/PlantReport.php`
عند حل بلاغ، لا يُسجل من حله ولا متى. فقط يتغير `status` إلى `resolved`.

### 32. لا يوجد pagination في `AdminNotificationController::index`
**الملف:** `app/Http/Controllers/Admin/AdminNotificationController.php:17-19`
```
$notifications = $user->notifications()->take(20)->get()
```
يأخذ آخر 20 إشعار فقط بدون pagination. إذا كان هناك المزيد، لا يمكن للمستخدم رؤيتها.

---

## 🔵 مشاكل الفرونت (Frontend)

### 33. `axios.ts` — 401 interceptor لا يفرق بين login و باقي الصفحات
**الملف:** `dashboard/src/plugins/axios.ts:36-47`
عند استلام 401، يحذف التوكن ويوجه لـ `/login`. لكن إذا كان المستخدم في صفحة login بالفعل، هذا يسبب redirect غير ضروري.

### 34. `login.vue` — يخزن التوكن كـ JSON string
**الملف:** `dashboard/src/pages/login.vue:45`
```
localStorage.setItem('accessToken', JSON.stringify(data.token))
```
ثم في `axios.ts:24`:
```
config.headers.Authorization = token ? `Bearer ${JSON.parse(token)}` : ''
```
هذا يعمل لكنه غير ضروري — تخزين string كـ JSON string يعني إضافة علامات اقتباس إضافية.

### 35. `plants-map.vue` — لا يوجد error handling لـ `fetchPlants`
**الملف:** `dashboard/src/pages/plants-map.vue:148-163`
```
plantStore.fetchAllPlantsForMap(params).then(response => {
    plants.value = response.data.data
    ...
}).finally(() => {
    loading.value = false
})
```
لا يوجد `.catch()` — إذا فشل الطلب، `plants.value` يبقى فارغ بدون رسالة خطأ للمستخدم.

### 36. `plants-map.vue` — لا يوجد error handling لـ `fetchGovernorates`
**الملف:** `dashboard/src/pages/plants-map.vue:167-169`
نفس المشكلة — لا يوجد `.catch()`.

### 37. `home.vue` — لا يوجد error handling لـ `fetchStatistics`
**الملف:** `dashboard/src/pages/dashboards/home.vue:96-109`
لا يوجد `.catch()` — إذا فشل الطلب، البطاقات تبقى بصفر بدون رسالة.

---

## 📊 ملخص المشاكل

| الخطورة | العدد | الوصف |
|---------|-------|-------|
| 🔴 حرجة | 5 | SQL Injection, FCM معطل, Mass Assignment |
| 🟠 أمنية | 5 | Settings بدون whitelist, AdminMiddleware, Token expiration |
| 🟡 وظيفية | 12 | صور غير محذوفة, response غير متسق, مسارات مكررة |
| 🟢 جودة الكود | 8 | لا Resources موحدة, لا logging, لا audit trail |
| 🔵 فرونت | 5 | لا error handling, تخزين غير ضروري |
| **الإجمالي** | **35** | |

---

## ✅ نقاط إيجابية

- بنية المشروع منظمة (Services, Repositories, Actions, Resources)
- استخدام Form Requests للـ validation
- نظام الصلاحيات (Spatie) مطبق بشكل صحيح
- ApiResponse trait موحد لمعظم الـ endpoints
- استخدام GIS (ST_Distance_Sphere) للبحث المكاني
- نظام OTP مع rate limiting و cooldown
- دعم RTL كامل في الداشبورد
- فصل بين App API و Admin API
- استخدام Sanctum للـ authentication
- middleware للصيانة (maintenance mode)
- فلترة تلقائية بـ governorate للمشرفين
