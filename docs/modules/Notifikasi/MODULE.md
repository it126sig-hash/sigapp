# Module: Notifikasi

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

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
- `notifications` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `notification_email_queue` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `user_notification_preferences` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `notification_event_types` *(akses tulis eksklusif hanya untuk modul Notifikasi)*
- `notification_snoozes` *(akses tulis eksklusif hanya untuk modul Notifikasi)*

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
| `GET` | `push/status` | `Explicit` | `NotifPushController::status` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `push/subscribe` | `Explicit` | `NotifPushController::subscribe` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `push/unsubscribe` | `Explicit` | `NotifPushController::unsubscribe` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `push/test` | `Explicit` | `NotifPushController::test` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `preferences` | `Explicit` | `NotificationPreferenceController::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `preferences` | `Explicit` | `NotificationPreferenceController::save` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `preferences/reset` | `Explicit` | `NotificationPreferenceController::reset` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `push/status` | `Explicit` | `NotifPushController::status` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `push/subscribe` | `Explicit` | `NotifPushController::subscribe` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `push/unsubscribe` | `Explicit` | `NotifPushController::unsubscribe` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `push/test` | `Explicit` | `NotifPushController::test` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `preferences` | `Explicit` | `NotificationPreferenceController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `preferences` | `Explicit` | `NotificationPreferenceController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `preferences/reset` | `Explicit` | `NotificationPreferenceController::reset` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/notif/tambah_notif` | `AutoRoute` | `Notif::tambah_notif` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/notif/getactivity` | `AutoRoute` | `Notif::getActivity` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/notif/getcurrentgroupid` | `AutoRoute` | `Notif::getCurrentGroupId` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/notif/getunreadactivitycount` | `AutoRoute` | `Notif::getUnreadActivityCount` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/notif/sanitizeactivityitems` | `AutoRoute` | `Notif::sanitizeActivityItems` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/notif/plainnotificationtext` | `AutoRoute` | `Notif::plainNotificationText` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/notif/applygrouptargetfilter` | `AutoRoute` | `Notif::applyGroupTargetFilter` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/notif_push_controller/validsubscriptionpayload` | `AutoRoute` | `NotifPushController::validSubscriptionPayload` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/notif_push_controller/allowpushtest` | `AutoRoute` | `NotifPushController::allowPushTest` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/notif_push_controller/currentactorcontext` | `AutoRoute` | `NotifPushController::currentActorContext` | 🟢 MIGRATED | Rute method aktif |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `NotifikasiService.php`
- `create(NotificationData $data, NotificationAudience $audience)`
- `tambah_notif($target, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null, ?string $actionUrl = null, ?string $eventType = null)`
- `tambah_notif_user($user_id, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null, ?string $actionUrl = null, ?string $eventType = null)`
- `getActivity($all = false, $offset = null, $id_proyek = null, $limit = 10)`
- `resolveRecipientIds(NotificationAudience $audience, int $actorUserId, ?string $eventType = null)`
- `storeRecipients(int $notificationId, array $userIds, int $actorUserId, ?string $eventType, string $now)`
- `storeDeliveries(int $notificationId, array $recipientRows, int $actorUserId, ?string $eventType, string $now)`
- `insertDeliveryIfMissing(int $recipientId, int $notificationId, int $userId, string $channel, string $status, ?string $lastError, string $now)`
- `hasRecipientVisibilityColumn()`
- `storeLegacyEmailQueue(int $notificationId, NotificationAudience $audience, int $actorUserId, string $now)`
- `activeUsersQuery()`
- `isActiveUser(int $userId)`
- `adminUserIds()`
- `currentGroupId(int $userId)`
- `featureEnabled(string $key, bool $default)`
#### `NotificationDispatchService.php`
- `dispatch(string $channel = 'web_push', int $limit = 100)`
- `claim(string $channel, int $limit)`
- `dispatchWebPush(object $delivery)`
- `retryDelivery(int $deliveryId, int $currentAttempts, string $error)`
- `finishDelivery(int $deliveryId, string $status, int $currentAttempts, ?string $error)`
- `retryDelayMinutes(int $attempts)`
- `urlForNotification(object $delivery)`
- `claimToken()`
- `getNamaProyek(int $idProyek)`
- `resolveProyekIdFromKavling($idKavling)`
#### `NotificationPreferenceService.php`
- `isAllowed(int $userId, string $eventType, string $channel)`
- `getUserPreferences(int $userId, ?int $groupId = null)`
- `savePreferences(int $userId, array $preferences)`
- `resetToDefaults(int $userId)`
- `syncInAppVisibility(int $userId, ?string $now = null)`
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
- `limitText(string $value, int $limit)`
- `resolveGroupUserIds(string $groupId, int $excludeUserId = 0)`
#### `EmailDigestService.php`
- `processQueue()`
- `processDeliveryQueue()`
- `processLegacyQueue()`
- `skipLegacyRowsCoveredByOutbox(string $batchId)`
- `unreadLegacyItemsForUser(array $items, int $userId)`
- `markLegacyQueues(array $ids, string $status, string $batchId)`
- `legacyQueueIds(array $items)`
- `getTargetUsers($groupId, $userId)`
- `sendDigestEmail($user, $items)`
- `syncGoogleCalendarForUser($user, array $items, string $sentAt)`
- `groupItemsByProyek(array $items)`
- `markDeliveries(array $ids, string $status, ?string $error = null)`
- `retryOrFailDeliveries(array $rows, string $error)`
- `deliveryIds(array $rows)`
- `retryDelayMinutes(int $attempts)`
- _dan 2 method lainnya..._

## File Inventory
### Controllers (3)
- `app/Controllers/Notif.php` (271 baris, 16 public methods)
- `app/Controllers/Api/NotifPushController.php` (150 baris, 7 public methods)
- `app/Controllers/Api/NotificationPreferenceController.php` (95 baris, 3 public methods)

### Services (7)
- `app/Services/NotifikasiService.php` (431 baris)
- `app/Services/NotificationDispatchService.php` (262 baris)
- `app/Services/NotificationPreferenceService.php` (212 baris)
- `app/Services/NotificationNavigationService.php` (93 baris)
- `app/Services/NotificationIconService.php` (41 baris)
- `app/Services/WebPushService.php` (241 baris)
- `app/Services/EmailDigestService.php` (538 baris)

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
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Notifikasi` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.
