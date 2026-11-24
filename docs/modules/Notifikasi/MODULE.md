# Module: Notifikasi

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-24 20:25:25` @ `e156bb6`  

## Purpose
Pemberitahuan terpusat: navbar in-app alerts, queue email digest, browser Web Push, dan pengaturan preferensi user.

## Out of scope
- Hal-hal di luar tanggung jawab domain Notifikasi.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `push_subscriptions` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `notification` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `notification_recipients` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `notification_deliveries` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `notification_email_queue` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `user_notification_preferences` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `notification_event_types` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `notification_snoozes` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `google_calendar_connections` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `google_calendar_event_syncs` *(akses tulis eksklusif hanya untuk modul Notifikasi)*

### Shared Tables (Dibaca dari modul lain)
- `users`
- `proyek`
- `auth_device_sessions`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/notif/open/(:num)` | `Explicit` | `NotifikasiController::open/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/notif/icon/(:num)` | `Explicit` | `NotifikasiController::icon/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/google-calendar/connect` | `Explicit` | `GoogleCalendarController::connect` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/google-calendar/callback` | `Explicit` | `GoogleCalendarController::callback` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/google-calendar/disconnect` | `Explicit` | `GoogleCalendarController::disconnect` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/getnotif` | `Explicit` | `\App\Controllers\Api\Notifikasi\NotifikasiController::getNotif` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/loadnotif` | `Explicit` | `\App\Controllers\Api\Notifikasi\NotifikasiController::loadNotif` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/notif/summary` | `Explicit` | `\App\Controllers\Api\Notifikasi\NotifikasiController::getSummary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/notif/center` | `Explicit` | `\App\Controllers\Api\Notifikasi\NotifikasiController::getCenter` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/notif/snooze` | `Explicit` | `\App\Controllers\Api\Notifikasi\NotifikasiController::snooze` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/notif/mark-as-read/(:num)` | `Explicit` | `\App\Controllers\Api\Notifikasi\NotifikasiController::markAsRead/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/notif/mark-all-as-read` | `Explicit` | `\App\Controllers\Api\Notifikasi\NotifikasiController::markAllAsRead` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/notif/summary` | `Explicit` | `NotifikasiController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/notif/center` | `Explicit` | `NotifikasiController::center` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/snooze` | `Explicit` | `NotifikasiController::snooze` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/notif/load` | `Explicit` | `NotifikasiController::load` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/mark-as-read/(:num)` | `Explicit` | `NotifikasiController::markAsRead/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/mark-all-as-read` | `Explicit` | `NotifikasiController::markAllAsRead` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/notif/list` | `Explicit` | `NotifikasiController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/notif/push/status` | `Explicit` | `NotifPushController::status` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/push/subscribe` | `Explicit` | `NotifPushController::subscribe` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/push/unsubscribe` | `Explicit` | `NotifPushController::unsubscribe` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/push/test` | `Explicit` | `NotifPushController::test` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/notif/preferences` | `Explicit` | `NotificationPreferenceController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/preferences` | `Explicit` | `NotificationPreferenceController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/preferences/reset` | `Explicit` | `NotificationPreferenceController::reset` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `NotifikasiService.php`
- `create(NotificationData $data, NotificationAudience $audience)`
- `tambah_notif($target, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null, ?string $actionUrl = null, ?string $eventType = null)`
- `tambah_notif_user($user_id, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null, ?string $actionUrl = null, ?string $eventType = null)`
- `getActivity($all = false, $offset = null, $id_proyek = null, $limit = 10)`
#### `NotificationDispatchService.php`
- `dispatch(string $channel = 'web_push', int $limit = 100)`
- `dispatchForNotification(int $notificationId, string $channel = 'web_push', int $limit = 50)`
#### `NotificationPreferenceService.php`
- `isAllowed(int $userId, string $eventType, string $channel)`
- `getUserPreferences(int $userId, ?int $userGroupId = null)`
- `savePreferences(int $userId, array $preferences)`
- `resetToDefaults(int $userId)`
#### `NotificationNavigationService.php`
- `processOpen(int $notificationId, int $userId)`
#### `NotificationIconService.php`
- `getIconPath(int $idProyek)`
#### `WebPushService.php`
- `isReady()`
- `initError()`
- `validateVapid()`
- `subscribe(int $userId, array $subscriptionData, ?string $userAgent = null, ?int $deviceSessionId = null)`
- `unsubscribe(int $userId, string $endpoint)`
- `activeSubscriptionCount(int $userId)`
- `getActiveSubscriptionsForUser(int $userId)`
- `sendToUser(int $userId, string $title, string $body, string $url = '/')`
- `sendToGroup(string $groupId, string $title, string $body, string $url = '/', int $excludeUserId = 0)`
- `sendToSubscriptions(array $subscriptions, string $title, string $body, string $url = '/', ?string $icon = null, ?int $notificationId = null)`
- `buildActorPayload(string $message, ?string $actorName = null, ?string $actorUsername = null, ?string $department = null, ?string $projectName = null)`
#### `EmailDigestService.php`
- `processQueue()`
#### `GoogleCalendarService.php`
- `isConfigured()`
- `getConfigurationMessage()`
- `tablesReady()`
- `getStatus(int $userId)`
- `buildAuthUrl(int $userId)`
- `connectFromCallback(int $userId, string $state, string $code)`
- `disconnect(int $userId)`
- `syncUrgentTicketFromNotificationItem(object $item, int $userId, string $emailSentAt)`

## File Inventory
### Controllers (5)
- `app/Controllers/Web/Notifikasi/NotifikasiController.php` (45 baris, 2 public methods: `open(int $notificationId)`, `icon(int $idProyek)`)
- `app/Controllers/Web/Notifikasi/GoogleCalendarController.php` (55 baris, 3 public methods: `connect()`, `callback()`, `disconnect()`)
- `app/Controllers/Api/Notifikasi/NotifikasiController.php` (428 baris, 14 public methods: `tambah_notif($target = null, $notif = null, $add_by = null, $id_kavling = null, $id_konsumen = null, $type = null, $id_proyek = null, ?string $actionUrl = null, ?string $eventType = null)`, `summary()`, `getSummary()`, `center()`, `getCenter()`, `snooze()`, `load()`, `loadNotif($all = false)`, `getNotif($all = false)`, `list()`, `markAsRead($id)`, `markAllAsRead()`, `getActivity($all = false, $offset = null, $id_proyek = null, $limit = 10, ?string $intent = null, ?string $category = null)`, `getActivityRoute()`)
- `app/Controllers/Api/Notifikasi/NotifPushController.php` (212 baris, 4 public methods: `status()`, `subscribe()`, `unsubscribe()`, `test()`)
- `app/Controllers/Api/Notifikasi/NotificationPreferenceController.php` (86 baris, 3 public methods: `index()`, `save()`, `reset()`)

### Services (8)
- `app/Services/Notifikasi/NotifikasiService.php` (470 baris, 4 public methods)
- `app/Services/Notifikasi/NotificationDispatchService.php` (275 baris, 2 public methods)
- `app/Services/Notifikasi/NotificationPreferenceService.php` (316 baris, 4 public methods)
- `app/Services/Notifikasi/NotificationNavigationService.php` (91 baris, 1 public methods)
- `app/Services/Notifikasi/NotificationIconService.php` (43 baris, 1 public methods)
- `app/Services/Notifikasi/WebPushService.php` (246 baris, 11 public methods)
- `app/Services/Notifikasi/EmailDigestService.php` (538 baris, 1 public methods)
- `app/Services/Notifikasi/GoogleCalendarService.php` (611 baris, 8 public methods)

### Repositories (2)
- `app/Repositories/Notifikasi/NotificationRepository.php` (413 baris, 7 public methods: `listForUser(int $userId, int $groupId, ?int $idProyek = null, int $offset = 0, int $limit = 10, bool $all = false, ?string $intent = null, ?string $category = null)`, `unreadCountForUser(int $userId, int $groupId, ?int $idProyek = null)`, `markAsReadForUser(int $notificationId, int $userId)`, `markAllAsReadForUser(int $userId)`, `getUserGroupId(int $userId)`, `getProyekName(int $idProyek)`, `getActorDepartmentContext(int $userId)`)
- `app/Repositories/Notifikasi/NotifRepository.php` (29 baris, 1 public methods: `tambah_notif($target, $notif, $add_by, $id_kavling, $id_konsumen, $type = null)`)

### Models (1)
- `app/Models/PushSubscriptionModel.php`

### Views (1 folder)
- `app/Views/emails` (1 file: `email_digest.php`)

### JavaScript (1 file)
- `public/assets/js/push-subscription.js` (249 baris)

### Zero-Downtime Shims (14 file)
- `app/Controllers/Notif.php`
- `app/Controllers/GoogleCalendar.php`
- `app/Controllers/Api/NotifPushController.php`
- `app/Controllers/Api/NotificationPreferenceController.php`
- `app/Services/NotifikasiService.php`
- `app/Services/NotificationDispatchService.php`
- `app/Services/NotificationPreferenceService.php`
- `app/Services/NotificationNavigationService.php`
- `app/Services/NotificationIconService.php`
- `app/Services/WebPushService.php`
- `app/Services/EmailDigestService.php`
- `app/Services/GoogleCalendarService.php`
- `app/Repositories/NotificationRepository.php`
- `app/Repositories/NotifRepository.php`

## Key Functions
- **Web Controller (`App\Controllers\Web\Notifikasi\NotifikasiController`):**
  - `open(int $notificationId)`: Deep-linking resolver — memvalidasi otorisasi penerima, menandai notifikasi telah dibaca (`read_at`), otomatis mengubah konteks sesi proyek aktif via `ActiveProyekService::setActive()`, lalu mengarahkan browser ke `action_url` modul terkait.
  - `icon(int $idProyek)`: Endpoint biner pengunduh logo proyek dinamis dengan fallback icon SIGAPP resmi untuk avatar dropdown/push notification.
- **Web Controller (`App\Controllers\Web\Notifikasi\GoogleCalendarController`):**
  - `connect()`, `callback()`, `disconnect()`: Alur OAuth2 Google Calendar terisolasi untuk sinkronisasi otomatis tiket masalah urgent ke kalender pribadi user.
- **API Controller (`App\Controllers\Api\Notifikasi\NotifikasiController`):**
  - `summary()` & `getSummary()`: Dual-Mode response — menghasilkan REST API JSON standar (`BaseApiController`) untuk consumer modern/Vue/Capacitor dan payload kompatibel legacy untuk navbar dropdown (`unread`, `urgent_count`, dsb.). Mendukung filter query `intent` & `category`.
  - `center()` & `getCenter()`: Dual-Mode notification center feed dengan pemisahan tab `urgent` dan `activities` serta dukungan filter `intent` & `category`.
  - `load()`, `loadNotif()`, `getNotif()`, & `list()`: Paginasi feed aktivitas notifikasi dengan format plain-text ramah UI, dukungan filter `intent` & `category`, serta metadata taksonomi (`event_type`, `category`, `intent`, `target_module`, `ref_id`, `spa_route`).
  - `markAsRead(int $id)` & `markAllAsRead()`: Operasi mark-as-read per recipient tanpa mengubah status pengguna lain.
  - `snooze()`: Penundaan notifikasi berulang per user.
- **API Controller (`App\Controllers\Api\Notifikasi\NotifPushController`):**
  - `status()`, `subscribe()`, `unsubscribe()`, `test()`: Manajemen langganan push dual-provider (`web_push` VAPID & `fcm` token Capacitor Android/iOS) dengan validasi payload, pengikatan ke sesi perangkat aktif (`auth_device_sessions`) melalui session atau cookie `sigapp_spa_token` / Bearer token (`DeviceSessionService`), filter `provider = 'web_push'` pada `test()`, dan throttle limit pada testing.
- **API Controller (`App\Controllers\Api\Notifikasi\NotificationPreferenceController`):**
  - `index()`, `save()`, `reset()`: Manajemen matriks preferensi granular pengguna (kanal in-app, email, web_push) dengan pencegahan override pada event berstatus `is_mandatory`.
- **Service (`App\Services\Notifikasi\NotifikasiService`):**
  - `create(NotificationData $data, NotificationAudience $audience)`: Eksekusi transaksi DB (`transStart`/`transComplete`), resolusi penerima dinamis lintas departemen, pembuatan antrean outbox deliveries, dukungan fallback email queue legacy, serta pemanggilan `scheduleImmediatePushDispatch()` pasca-commit (`register_shutdown_function` + `fastcgi_finish_request()`/`litespeed_finish_request()` dengan guard CLI/testing dan toggle `NOTIF_IMMEDIATE_DISPATCH`).
  - `tambah_notif()` & `tambah_notif_user()`: Kontrak publik universal lintas modul yang menghubungkan panggilan dari seluruh modul bisnis SIGAPP ke arsitektur notifikasi modern.
- **Service (`App\Services\Notifikasi\NotificationDispatchService`):**
  - `dispatch(string $channel, int $limit)` & `dispatchForNotification(int $notificationId, string $channel = 'web_push', int $limit = 50)`: Pemroses delivery outbox (baik via cron sweeper maupun post-response immediate push per `notification_id`) dengan token claim concurrency guard, filter `provider = 'web_push'` pada `dispatchWebPush()`, handling retry otomatis, dan penonaktifan endpoint kadaluwarsa (HTTP 404/410).
- **Service (`App\Services\Notifikasi\WebPushService`):**
  - `getActiveSubscriptionsForUser(int $userId)`: Mengambil endpoint aktif yang hanya terafiliasi dengan sesi perangkat valid (`auth_device_sessions`), mencegah pengiriman push ke browser zombie/logout.
  - `sendToSubscriptions()`: Pengiriman batch terenkripsi via Guzzle PSR-18 client dengan header otentikasi VAPID.
- **Service (`App\Services\Notifikasi\NotificationPreferenceService`):**
  - `getUserPreferences()`, `savePreferences()`, `syncInAppVisibility()`: Pengaturan preferensi opt-in/opt-out dinamis dan sinkronisasi visibilitas in-app secara otomatis.
- **Repository (`App\Repositories\Notifikasi\NotificationRepository`):**
  - `listForUser(..., ?string $intent = null, ?string $category = null)` & `unreadCountForUser()`: Query Data Access teroptimasi dengan pemfilteran status baca, visibilitas in-app per recipient, filter taksonomi `intent`/`category` (batch scan), dan pelampiran metadata taksonomi via `NotificationTaxonomy::resolve()`.
  - `getUserGroupId(int $userId)`: Helper query aman untuk mendapatkan group ID pengguna tanpa melanggar layer di controller.
  - `getProyekName(int $idProyek)`: Query nama proyek untuk pembentukan payload teks notifikasi.
  - `getActorDepartmentContext(int $userId)`: Query agregasi aman (`MIN(auth_groups.name)`) untuk format notifikasi push yang patuh terhadap MySQL `ONLY_FULL_GROUP_BY`.
- **Model (`App\Models\PushSubscriptionModel`):**
  - `saveSubscription()`: Menyimpan atau memigrasikan langganan dual-provider (`web_push` via `endpoint_hash` SHA-256 & `fcm` via `fcm://<token>` + `platform`: `web`/`android`/`ios`).
  - `deleteSubscription(int $userId, string $endpointOrToken)`: Menonaktifkan subscription (`disabled_at`) berdasarkan endpoint Web Push maupun raw/prefixed `fcm_token`.
  - `activeForUser(int $userId, ?string $provider = null)`: Mengembalikan subscription aktif yang tertaut ke `auth_device_sessions` yang belum dicabut, belum kedaluwarsa, dan tidak idle > 30 hari (`last_seen_at >= -30 days`, fallback `created_at`).
- **Support & Taxonomy (`App\Support\NotificationTaxonomy`):**
  - `resolve(array|object $item)`: Pemetaan deterministik item notifikasi ke taksonomi 2-dimensi (`event_type`, `category`, `intent`: `action_required` | `urgent_alert` | `operational_info` | `system_master`, `target_module`, `ref_id`, `spa_route` termasuk deep-link `/permohonan?module=...&id=...`).
- **Frontend, Service Worker, & UI Shell (`public/sw.js`, `public/assets/js/scripts.js`, `public/assets/js/dashboard.js`, `app/Views/template/generate_menu.php`, `public/assets/css/style.css`):**
  - `public/sw.js`: Mengirim event realtime `BroadcastChannel('sigapp_notif_realtime')` (`PUSH_RECEIVED`) dan `client.postMessage({ type: 'SIGAPP_PUSH_RECEIVED' })` ke tab aktif saat event `push` diterima.
  - `initNotificationRealtimeListeners()`, `scheduleRealtimeNotifSync()`, & `visibilitychange` (`scripts.js`): Listener `BroadcastChannel` + `navigator.serviceWorker` `message` dengan debounce 400ms dan sinkronisasi ulang otomatis saat tab kembali fokus (>15 detik).
  - `#notif-intent-filter` (`.notif-intent-pill`), `#notif-permohonan-link`, & `.activity-intent-badge` (`generate_menu.php`, `style.css`, `scripts.js`): Filter intent di tab Aktivitas `#list-notif` (`Semua`, `Perlu Tindakan`, `Operasional`, `Sistem`), badge `Perlu Tindakan`, pintasan ke `/permohonan`, serta deep-link `handleNotificationClick()` untuk 6 event gap baru.
  - `window.openNotificationCenter()` (`scripts.js`) & `closeNotificationCenterModal()` (`scripts.js`): Membuka/menutup right-side full-height modal `#list-notif`, digunakan juga oleh `#dashboard-view-all-activity` di `dashboard.js`.
  - `#list-notif` `shown.bs.modal` & infinite scroll `#notification-center-body` (`scrollTop + innerHeight >= scrollHeight - 24`): Memuat/menyegarkan notification center saat drawer dibuka serta memuat halaman aktivitas berikutnya lewat `/loadnotif` saat tab `Aktivitas` aktif.

## Deprecated & Legacy Shims
Seluruh komponen di bawah ini dipertahankan secara sengaja demi **Zero-Downtime Migration** (kompatibilitas mundur total):

### 1. Deprecated Routes & Endpoints
- `GET /getnotif` -> **DEPRECATED**, gunakan rute terstruktur `GET /api/notif/summary` atau `GET /api/notif/load`.
- `GET /loadnotif` -> **DEPRECATED**, gunakan rute terstruktur `GET /api/notif/load`.
- `GET /notif/summary` -> **DEPRECATED**, gunakan rute terstruktur `GET /api/notif/summary`.
- `GET /notif/center` -> **DEPRECATED**, gunakan rute terstruktur `GET /api/notif/center`.
- `POST /notif/snooze` -> **DEPRECATED**, gunakan rute terstruktur `POST /api/notif/snooze`.
- `POST /notif/mark-as-read/(:num)` -> **DEPRECATED**, gunakan rute terstruktur `POST /api/notif/mark-as-read/(:num)`.
- `POST /notif/mark-all-as-read` -> **DEPRECATED**, gunakan rute terstruktur `POST /api/notif/mark-all-as-read`.
- `ANY /notif/tambah_notif` (AutoRoute Shim) -> **DEPRECATED**, gunakan backend contract PHP Service `NotifikasiService::tambah_notif()`.
- `ANY /notif/getactivity` (AutoRoute Shim) -> **DEPRECATED**, gunakan rute terstruktur `GET /api/notif/load` atau `GET /api/notif/list`.

### 2. Deprecated Classes & Namespaces (14 Zero-Downtime Shims)
- `app/Controllers/Notif.php` -> **DEPRECATED**, diteruskan ke `App\Controllers\Web\Notifikasi\NotifikasiController` & `App\Controllers\Api\Notifikasi\NotifikasiController`.
- `app/Controllers/GoogleCalendar.php` -> **DEPRECATED**, diteruskan ke `App\Controllers\Web\Notifikasi\GoogleCalendarController`.
- `app/Controllers/Api/NotifPushController.php` -> **DEPRECATED**, diteruskan ke `App\Controllers\Api\Notifikasi\NotifPushController`.
- `app/Controllers/Api/NotificationPreferenceController.php` -> **DEPRECATED**, diteruskan ke `App\Controllers\Api\Notifikasi\NotificationPreferenceController`.
- `app/Services/NotifikasiService.php` -> **DEPRECATED**, diteruskan ke `App\Services\Notifikasi\NotifikasiService`.
- `app/Services/NotificationDispatchService.php` -> **DEPRECATED**, diteruskan ke `App\Services\Notifikasi\NotificationDispatchService`.
- `app/Services/NotificationPreferenceService.php` -> **DEPRECATED**, diteruskan ke `App\Services\Notifikasi\NotificationPreferenceService`.
- `app/Services/NotificationNavigationService.php` -> **DEPRECATED**, diteruskan ke `App\Services\Notifikasi\NotificationNavigationService`.
- `app/Services/NotificationIconService.php` -> **DEPRECATED**, diteruskan ke `App\Services\Notifikasi\NotificationIconService`.
- `app/Services/WebPushService.php` -> **DEPRECATED**, diteruskan ke `App\Services\Notifikasi\WebPushService`.
- `app/Services/EmailDigestService.php` -> **DEPRECATED**, diteruskan ke `App\Services\Notifikasi\EmailDigestService`.
- `app/Services/GoogleCalendarService.php` -> **DEPRECATED**, diteruskan ke `App\Services\Notifikasi\GoogleCalendarService`.
- `app/Repositories/NotificationRepository.php` -> **DEPRECATED**, diteruskan ke `App\Repositories\Notifikasi\NotificationRepository`.
- `app/Repositories/NotifRepository.php` -> **DEPRECATED**, diteruskan ke `App\Repositories\Notifikasi\NotifRepository`.

### 3. Deprecated / Removed Frontend Selectors & Patterns (`2026-11-24`)
- CSS `.notification-center-menu` (`public/assets/css/style.css`) -> **REMOVED**, digantikan `.notification-center-modal` (right-side full-height modal drawer di luar `<nav class="header-navbar">`).
- Inline `<style>` `.notif-project-scroll` (`app/Views/template/generate_menu.php`) -> **REMOVED**, dipindahkan ke `public/assets/css/style.css` (`.notification-center-modal .notif-project-scroll`).
- Class `.scrollable-container` & `.media-list` pada `#notification-center-body` (`app/Views/template/generate_menu.php`) -> **REMOVED**, mencegah binding `PerfectScrollbar` (`app.js`) dan `max-height: 25rem` (`bootstrap-extended.css`) agar native flex scroll berjalan penuh.
- Pemanggilan `$('#header-notif').dropdown('toggle')` (`public/assets/js/dashboard.js`) & listener `shown.bs.dropdown` (`public/assets/js/scripts.js`) -> **REMOVED**, digantikan `window.openNotificationCenter()` / `$('#list-notif').modal('show')` dan listener `shown.bs.modal`.

### 4. Pembersihan Konsumen Shim Lintas Modul (`2026-11-24`)
- Pemanggilan legacy `\App\Controllers\Notif` di `App\Services\Keuangan\Cashout\CashoutSubkonService` dan import shim `App\Services\NotifikasiService` di `App\Services\Bpb\BpbService` -> **REMOVED**, keduanya telah beralih ke service modular `App\Services\Notifikasi\NotifikasiService`.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Notifikasi` untuk mengaudit dead code kandidat._

## Notes
- Modul Notifikasi adalah modul fondasi infrastruktur (Wave 0) yang menjadi backend notification engine bagi seluruh aplikasi web monolitik dan frontend mobile (Vue/Capacitor).
- **Arsitektur Zero Layer Violations:** Seluruh controller di namespace `Web\Notifikasi` dan `Api\Notifikasi` bersih 100% dari akses direct database (`db_connect()`, `$this->db`), seluruh data access didelegasikan ke `NotificationRepository`.
- **Integrasi Sesi Perangkat & Cutoff 30 Hari:** `PushSubscriptionModel::activeForUser()` memvalidasi sesi perangkat aktif (`auth_device_sessions`) yang belum dicabut, belum kedaluwarsa, dan tidak idle > 30 hari (`last_seen_at >= -30 days`), serta mendukung dual-provider (`web_push` VAPID dan `fcm` token Capacitor Android/iOS).
- **Support, Enums, Migrations, & Global Assets Terkait:**
  - `app/Support/NotificationTaxonomy.php` (`NotificationTaxonomy::resolve()` — pemetaan taksonomi 2-dimensi & `spa_route` ke `/permohonan` / modul target).
  - `app/Support/NotificationTextFormatter.php` (normalisasi plain-text & prefix jalan/kavling).
  - `app/Enums/NotificationEvent.php` & `app/Database/Seeds/NotificationEventTypeSeeder.php` (47 event registry, termasuk 6 event gap baru).
  - `app/Database/Migrations/2026-11-25-000001_UpgradePushSubscriptionsAndNotificationTaxonomy.php` (Batch 50: upgrade `push_subscriptions` dual-provider, sinkronisasi seeder, & registrasi menu `Permohonan & Approval` `id=52`).
  - `public/sw.js` (`BroadcastChannel('sigapp_notif_realtime')` + `client.postMessage`), `app/Views/template/generate_menu.php` (`#list-notif`, `#notif-intent-filter`, `#notif-permohonan-link`), `public/assets/css/style.css`, `public/assets/js/scripts.js`, & `public/assets/js/dashboard.js`.
- **Cross-Module Callers (`NotifikasiService` & `NotificationEvent`):**
  - `App\Services\Legal\LegalService` (`simpan`, `uploadDocument`) -> `legal_dokumen_updated` ke `'3;4;5;10'`.
  - `App\Services\Keuangan\PenagihanService` (`simpanPenagihan`, `updateStatusPenagihan`) -> `tagihan_signature_requested` ke `'9'`.
  - `App\Services\Keuangan\DireksiService` (`signTagihan`, `rejectTagihan`) -> `tagihan_signature_responded` ke `'3'`.
  - `App\Services\Bpb\BpbService` (`sign`, `submit`, dll.) -> `bpb_signature_requested`, `bpb_signature_completed`, `bpb_rejected`, `bpb_approved`, `bpb_status_changed`, serta `bpb_execution_requested` ke `'2;3'`.
  - `App\Services\Keuangan\DanaJaminanService` (`storePengajuan`, `cairkanPengajuan`) -> `dajam_pengajuan_created` (`'3;4'`) & `dajam_pencairan_completed` (`'3;4;9'`).
  - `App\Services\Keuangan\Cashout\CashoutSubkonService` (`pembayaran`, `notifyCashoutSubkon`) -> `cashout_subkon` ke `[7]`.
  - Modul lain pengonsumsi `NotifikasiService`: `SalesExternalService`, `KavlingRequestService`, `TiketMasalahService`, `ReferralService`, `PosisiKonsumenService`, `PembayaranService`, `ProduksiService`, dll.
- **Perubahan Historis (2026-10-23):**
  - **Registrasi 5 Event Kategori `Fee Sales`:** Menambahkan konstanta resmi di `App\Enums\NotificationEvent` dan `NotificationEventTypeSeeder` (`fee_sales_pengajuan_created`, `fee_sales_termin_generated`, `fee_sales_harga_net_updated`, `fee_sales_ttd_signed`, `fee_sales_pencairan`) dengan `relevant_groups = '3;4'`.
  - **Scoping Default Preferensi Berbasis `relevant_groups`:** `NotificationPreferenceService::getUserPreferences()` dan `syncInAppVisibility()` menggunakan `resolveUserGroups()` serta `isRelevantForGroups()` untuk mengevaluasi `e.relevant_groups` terhadap grup user (`auth_groups_users` + `$userGroupId`). User di luar grup terkait otomatis mendapatkan default `false` pada ketiga channel (`in_app`, `email`, `web_push`).
  - **Deep-Link Handler Frontend (`public/assets/js/scripts.js`):** `handleNotificationClick()` menangani `type && type.startsWith("fee_sales")` dengan mengarahkan ke `/notif/open/{id_notif}` menuju `action_url` (`fee-sales?open_mkdt={id_mkdt}` atau `fee-sales/pengajuan/{share_token}`).
  - **Deprekasi Alias Event Pendek `fee_sales_*`:** Kode pendek (`fee_sales_pengajuan`, `fee_sales_termin`, `fee_sales_harga_net`, `fee_sales_ttd`, `fee_sales_cair`) dinormalisasi via `NotificationEvent::fromLegacyType()`.

### Audit Verifikasi Migrasi (2026-11-22)
  - **Controllers, Shims & Routes (Bersih):** 2 Web Controller, 3 API Controller (seluruhnya mewarisi `BaseApiController` dengan dukungan `?format=raw` dan `/api/notif/*`), 14 Zero-Downtime Shims (`thin_shim`), serta 26 rute eksplisit terverifikasi bebas pelanggaran layering/HTML.
  - **Services Layering Findings (7 dari 8 Service):** `NotificationIconService` bersih dari akses DB langsung, namun 7 Service lain masih melakukan query/mutasi `$this->db->table()` langsung (0 mutasi tabel modul lain; `tiket_masalah` & `tiket_masalah_user` di `GoogleCalendarService` hanya `SELECT` read-only):
    - `NotifikasiService`: `db_connect()` (L20) untuk transaksi (L36, L54, L72) sekaligus `$this->db->table()` langsung (L38, L137, L150, L264, L284, L289, L340, L349, L379, L391, L422).
    - `NotificationDispatchService`: `db_connect()` tanpa transaksi (L17) dan `$this->db->table()` (L68, L85, L96, L102, L185, L201, L232, L238).
    - `NotificationPreferenceService`: `db_connect()` (L13) untuk transaksi (L158, L207) sekaligus `$this->db->table()` langsung (L29, L45, L66, L72, L116, L161, L171, L183, L192, L215, L235, L243, L249, L283).
    - `NotificationNavigationService`: `Database::connect()` tanpa transaksi (L14) dan `$this->db->table()` (L29, L40, L52).
    - `WebPushService`: `db_connect()` tanpa transaksi (L24) dan `$this->db->table()` pada `users`/`auth_groups_users` (L219, L229).
    - `EmailDigestService`: `db_connect()` tanpa transaksi (L20) dan `$this->db->table()` (L42, L163, L285, L313, L338, L354, L392, L472, L486).
    - `GoogleCalendarService`: `db_connect()` tanpa transaksi (L36) dan `$this->db->table()` (L85, L156, L183, L188, L198, L316, L473, L495, L508, L518, L540, L547).

## Change Log
- `2026-11-24` [`FEATURE`] — **Infrastruktur Notifikasi Realtime (Vue/Capacitor-Ready), Dual-Provider Push, Taksonomi 2-Dimensi, & 6 Event Gap Lintas Modul (PASS QA)**:
  - **Routes & Public Contracts:** Endpoint `GET /notif/summary`, `/notif/center`, `/loadnotif`, `/api/notif/summary`, `/api/notif/center`, `/api/notif/load`, `/api/notif/list`, dan `/getnotif` kini mendukung query filter `intent` & `category` serta mengembalikan metadata taksonomi (`event_type`, `category`, `intent`, `target_module`, `ref_id`, `spa_route`). `POST /api/notif/push/subscribe` & `unsubscribe` mendukung dual-provider (`web_push` & `fcm` + `platform`) serta resolusi `device_session_id` dari cookie `sigapp_spa_token` / Bearer token.
  - **Controller / Service / Repo / Model:**
    - `app/Enums/NotificationEvent.php`, `app/Database/Seeds/NotificationEventTypeSeeder.php`, & `app/Database/Migrations/2026-11-25-000001_UpgradePushSubscriptionsAndNotificationTaxonomy.php`: tambah 6 event (`legal_dokumen_updated`, `tagihan_signature_requested`, `tagihan_signature_responded`, `bpb_execution_requested`, `dajam_pengajuan_created`, `dajam_pencairan_completed`), sinkronisasi `fee_sales_pengajuan_rejected`, perapihan `relevant_groups`, upgrade `push_subscriptions` dual-provider, & menu `Permohonan & Approval` (`id=52`).
    - `app/Support/NotificationTaxonomy.php`: kelas baru `NotificationTaxonomy::resolve()`.
    - `app/Repositories/Notifikasi/NotificationRepository.php` & `app/Controllers/Api/Notifikasi/NotifikasiController.php`: filter `intent`/`category` & pelampiran taksonomi 2-dimensi.
    - `app/Services/Notifikasi/NotifikasiService.php` & `NotificationDispatchService.php`: `scheduleImmediatePushDispatch()` (post-response shutdown dispatch pasca-commit) & `dispatchForNotification()` + filter `provider = 'web_push'`.
    - `app/Models/PushSubscriptionModel.php` & `app/Controllers/Api/Notifikasi/NotifPushController.php`: dual-provider `web_push`/`fcm`, `deleteSubscription()` by endpoint/token, & cutoff sesi idle > 30 hari di `activeForUser()`.
    - Cross-module callers: `LegalService`, `PenagihanService`, `DireksiService`, `BpbService`, `DanaJaminanService`, dan `CashoutSubkonService`.
  - **View / CSS / JS:** `public/sw.js` (`BroadcastChannel('sigapp_notif_realtime')` + `client.postMessage`), `public/assets/js/scripts.js` (`initNotificationRealtimeListeners()`, `scheduleRealtimeNotifSync()`, `visibilitychange`, filter `intent`, badge `Perlu Tindakan`, deep-link event baru), `app/Views/template/generate_menu.php` (`#notif-permohonan-link`, `#notif-intent-filter`), & `public/assets/css/style.css`.
  - **Deprecations:** Menghapus pemanggilan shim `\App\Controllers\Notif` di `CashoutSubkonService` dan shim `App\Services\NotifikasiService` di `BpbService`.
- `2026-11-24` [`UI_REPORT`] — **Perbaikan Popup User Profile & Konversi Popup Notifikasi ke Side Modal Kanan Full-Height (`#list-notif`) (PASS QA)**:
  - **Routes & Public Contracts:** Tidak ada perubahan rute/API backend; tetap memakai kontrak `/notif/summary`, `/notif/center`, `/loadnotif`, `/notif/snooze`, `/notif/mark-as-read/{id}`, `/notif/mark-all-as-read`, dan `/notif/open/{id}`.
  - **Controller / Service / Repo / Model:** Tidak ada perubahan file backend (`N/A`).
  - **View / CSS / JS:**
    - `app/Views/template/generate_menu.php`: perbaikan markup `.dropdown-user` (span label & pertahankan `[data-push-label]`), perubahan `#header-notif` menjadi modal trigger (`data-toggle="modal" data-target="#list-notif"`), pemindahan `#list-notif` ke luar `<nav class="header-navbar">` menjadi `.modal.fade.notification-center-modal`, penambahan `.notification-close-btn`, penghapusan inline `<style>` `.notif-project-scroll`, serta penghapusan `.scrollable-container` dan `.media-list` dari `#notification-center-body`.
    - `public/assets/css/style.css`: perbaikan lebar/wrapping/mobile/dark-layout `.dropdown-user .dropdown-menu` (override `12rem` & `display-scale.css`), serta konversi styling `.notification-center-modal` menjadi drawer kanan full-height (`100vh`/`100dvh`, `min(440px, 100vw)` desktop, `100vw` mobile, flex header/tabs/filter, scrollable `#notification-center-body` `#f5f7fa` + `safe-area-inset-bottom`, `.notif-project-scroll`, dan `.dark-layout`).
    - `public/assets/js/scripts.js`: penambahan `window.openNotificationCenter()` dan `closeNotificationCenterModal()`, event `shown.bs.modal` pada `#list-notif`, auto-hide drawer saat aksi in-page, dan toleransi infinite scroll `scrollHeight - 24`.
    - `public/assets/js/dashboard.js`: pembaruan `#dashboard-view-all-activity` memanggil `window.openNotificationCenter()` (fallback `$('#list-notif').modal('show')`).
  - **Deprecations / Removed:** Dihapusnya selector `.notification-center-menu`, inline `<style>` `.notif-project-scroll`, class `.scrollable-container` & `.media-list` pada `#notification-center-body`, serta pemanggilan `$('#header-notif').dropdown('toggle')` dan listener `shown.bs.dropdown`.
- `2026-11-12` [`REFACTOR`] — **Refactoring Penuh Modul Notifikasi Menjadi Arsitektur Modular CI4 Web/Api & Services (Score 100/100, PASS QA)**:
  - **Routes & Public Contracts:**
    - Mendaftarkan rute Web eksplisit: `notif/open/(:num)` (redirect deep-link), `notif/icon/(:num)` (binary stream PNG), `google-calendar/*` (OAuth2 flow).
    - Mendaftarkan rute API JSON modern (BaseApiController, RAW_JSON_READY): `/api/notif/summary`, `/api/notif/center`, `/api/notif/snooze`, `/api/notif/load`, `/api/notif/mark-as-read/(:num)`, `/api/notif/mark-all-as-read`, `/api/notif/list`, `/api/notif/push/*`, `/api/notif/preferences/*`.
    - Mendaftarkan rute kompatibilitas legacy (Zero-Downtime): `/getnotif`, `/loadnotif`, `/notif/summary`, `/notif/center`, `/notif/snooze`, `/notif/mark-as-read/(:num)`, `/notif/mark-all-as-read`, `/notif/tambah_notif`, `/notif/getactivity`.
  - **Controllers:**
    - Memisahkan controller flat ke subfolder modular: `Web\Notifikasi\NotifikasiController`, `Web\Notifikasi\GoogleCalendarController`, `Api\Notifikasi\NotifikasiController`, `Api\Notifikasi\NotifPushController`, `Api\Notifikasi\NotificationPreferenceController`.
    - Implementasi Dual-Mode response pada `summary()` dan `center()`: otomatis mengembalikan struktur data raw untuk API consumer modern dan struktur envelope legacy untuk script navbar UI lama.
  - **Services & Repositories:**
    - Memindahkan 8 Services ke subfolder `App\Services\Notifikasi\*`.
    - Memindahkan 2 Repositories ke `App\Repositories\Notifikasi\*`.
    - Menambahkan method query Data Access bersih pada `NotificationRepository` (`getUserGroupId`, `getProyekName`, `getActorDepartmentContext`) untuk menghapus direct DB calls dari controller layer.
    - Menambahkan method `getActiveSubscriptionsForUser()` pada `WebPushService` dengan pengecekan integritas sesi perangkat `auth_device_sessions`.
  - **Zero-Downtime Shims:**
    - Membuat 14 class shim di root namespace `App\Controllers`, `App\Services`, dan `App\Repositories` dengan pewarisan penuh ke class modular baru.
  - **Frontend & Assets:**
    - Menyelaraskan `public/assets/js/push-subscription.js` terhadap envelope format BaseApiController dan auto-refresh token CSRF CI4.
    - Safeguard null-safety pada `public/sw.js` terhadap push data payload.
    - Verifikasi integritas view email digest `app/Views/emails/email_digest.php`.
- `2026-11-12` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular: status `COMPLETED (Catatan Minor)` (Score 100%); Controller/Shims/Routes bersih, namun 7 Service masih memanggil `$this->db->table()` langsung tanpa delegasi Repository.
