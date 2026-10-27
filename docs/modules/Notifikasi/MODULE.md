# Module: Notifikasi

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-10-23 13:52:13` @ `ad0c900`  

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

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/getnotif` | `Explicit` | `Notif::getNotif` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/loadnotif` | `Explicit` | `Notif::loadNotif` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/notif/summary` | `Explicit` | `Notif::getSummary` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/notif/center` | `Explicit` | `Notif::getCenter` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/notif/snooze` | `Explicit` | `Notif::snooze` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/notif/mark-as-read/(:num)` | `Explicit` | `Notif::markAsRead/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/notif/mark-all-as-read` | `Explicit` | `Notif::markAllAsRead` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/notif/icon/(:num)` | `Explicit` | `Notif::icon/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/notif/open/(:num)` | `Explicit` | `Notif::open/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/api/notif/push/status` | `Explicit` | `NotifPushController::status` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/push/subscribe` | `Explicit` | `NotifPushController::subscribe` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/push/unsubscribe` | `Explicit` | `NotifPushController::unsubscribe` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/push/test` | `Explicit` | `NotifPushController::test` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/notif/preferences` | `Explicit` | `NotificationPreferenceController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/preferences` | `Explicit` | `NotificationPreferenceController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/notif/preferences/reset` | `Explicit` | `NotificationPreferenceController::reset` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/google-calendar/connect` | `Explicit` | `GoogleCalendar::connect` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/google-calendar/callback` | `Explicit` | `GoogleCalendar::callback` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/google-calendar/disconnect` | `Explicit` | `GoogleCalendar::disconnect` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/notif/tambah_notif` | `AutoRoute` | `Notif::tambah_notif` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/notif/getactivity` | `AutoRoute` | `Notif::getActivity` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `NotifikasiService.php`
- `create(NotificationData $data, NotificationAudience $audience)`
- `tambah_notif($target, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null, ?string $actionUrl = null, ?string $eventType = null)`
- `tambah_notif_user($user_id, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null, ?string $actionUrl = null, ?string $eventType = null)`
- `getActivity($all = false, $offset = null, $id_proyek = null, $limit = 10)`
#### `NotificationDispatchService.php`
- `dispatch(string $channel = 'web_push', int $limit = 100)`
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
### Controllers (4)
- `app/Controllers/Notif.php` (248 baris, 11 public methods)
- `app/Controllers/Api/NotifPushController.php` (150 baris, 4 public methods)
- `app/Controllers/Api/NotificationPreferenceController.php` (95 baris, 3 public methods)
- `app/Controllers/GoogleCalendar.php` (54 baris, 3 public methods)

### Services (8)
- `app/Services/NotifikasiService.php` (441 baris)
- `app/Services/NotificationDispatchService.php` (249 baris)
- `app/Services/NotificationPreferenceService.php` (318 baris)
- `app/Services/NotificationNavigationService.php` (93 baris)
- `app/Services/NotificationIconService.php` (41 baris)
- `app/Services/WebPushService.php` (241 baris)
- `app/Services/EmailDigestService.php` (538 baris)
- `app/Services/GoogleCalendarService.php` (611 baris)

### Repositories (2)
- `app/Repositories/NotifRepository.php` (30 baris)
- `app/Repositories/NotificationRepository.php` (290 baris)

### Models (1)
- `app/Models/PushSubscriptionModel.php`

### Views (1 folder)
- `app/Views/emails` (1 file)

### JavaScript (1 file)
- `public/assets/js/push-subscription.js` (234 baris)

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [ ] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Notif.php`: db_connect(), $this->db->table()
> - `app/Controllers/Api/NotifPushController.php`: db_connect()
> - `app/Controllers/Api/NotificationPreferenceController.php`: db_connect()

## Dead Code Candidates
_Jalankan `php spark module:deadcode Notifikasi` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.
- **Perubahan Penting (2026-10-23):**
  - **Registrasi 5 Event Kategori `Fee Sales`:** Menambahkan konstanta resmi di `App\Enums\NotificationEvent` dan `NotificationEventTypeSeeder` (`fee_sales_pengajuan_created`, `fee_sales_termin_generated`, `fee_sales_harga_net_updated`, `fee_sales_ttd_signed`, `fee_sales_pencairan`) dengan `relevant_groups = '3;4'`.
  - **Scoping Default Preferensi Berbasis `relevant_groups`:** `NotificationPreferenceService::getUserPreferences()` dan `syncInAppVisibility()` menggunakan `resolveUserGroups()` serta `isRelevantForGroups()` untuk mengevaluasi `e.relevant_groups` terhadap grup user (`auth_groups_users` + `$userGroupId`). User di luar grup terkait otomatis mendapatkan default `false` pada ketiga channel (`in_app`, `email`, `web_push`).
  - **Deep-Link Handler Frontend (`public/assets/js/scripts.js`):** `handleNotificationClick()` menangani `type && type.startsWith("fee_sales")` dengan mengarahkan ke `/notif/open/{id_notif}` menuju `action_url` (`fee-sales?open_mkdt={id_mkdt}` atau `fee-sales/pengajuan/{share_token}`).
  - **Deprekasi Alias Event Pendek `fee_sales_*`:** Kode pendek (`fee_sales_pengajuan`, `fee_sales_termin`, `fee_sales_harga_net`, `fee_sales_ttd`, `fee_sales_cair`) dinormalisasi via `NotificationEvent::fromLegacyType()`.
