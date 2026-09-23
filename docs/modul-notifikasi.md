# Modul Notifikasi SIGAPP

Dokumen ini adalah acuan teknis modul notifikasi SIGAPP. Update file ini setiap ada perubahan alur notifikasi, endpoint, tabel, service, command, konfigurasi delivery, atau side effect.

Terakhir dicek: 2026-09-09

## Ringkasan

Modul notifikasi sekarang memakai pola bertahap:

1. `notification` tetap menjadi tabel event dan payload utama.
2. `notification_recipients` menyimpan status baca per user dan flag visibility In-App.
3. `notification_deliveries` menjadi outbox delivery per recipient dan channel, termasuk status `preference_blocked`.
4. Navbar/halaman aktif tetap polling `/notif/summary` setiap 30 detik.
5. Browser/PWA background memakai Web Push, tanpa self-hosted WebSocket/SSE.
6. Email digest membaca delivery recipient-specific yang masih unread jika tabel baru tersedia, lalu fallback ke `notification_email_queue` lama selama masa transisi.

## File Utama

| Area | File | Peran |
| --- | --- | --- |
| Controller navbar | `app/Controllers/Notif.php` | Summary, center, load more, snooze, dan mark read |
| Service utama | `app/Services/NotifikasiService.php` | Insert event, resolve recipient, buat delivery outbox, dan queue legacy |
| DTO | `app/Data/NotificationData.php`, `app/Data/NotificationAudience.php` | Input service baru |
| Repository list | `app/Repositories/NotificationRepository.php` | Query list, unread count, dan mark read per user |
| Repository lama | `app/Repositories/NotifRepository.php` | Legacy insert-only; jangan dipakai untuk notification baru |
| Email digest | `app/Services/EmailDigestService.php` | Kirim digest dan update delivery/queue sesuai hasil nyata |
| Formatter teks | `app/Support/NotificationTextFormatter.php` | Normalisasi isi notifikasi dan prefix lokasi kavling untuk activity, email, dan Web Push |
| Command email | `app/Commands/SendEmailDigest.php` | `php spark notif:send-digest` |
| Web Push | `app/Services/WebPushService.php` | PSR-18 client, validasi VAPID, subscribe, dan kirim push |
| Push dispatcher | `app/Services/NotificationDispatchService.php` | Proses delivery `web_push` dari cron |
| Command push | `app/Commands/DispatchNotifications.php` | `php spark notif:dispatch --channel=web_push --limit=100` |
| API push | `app/Controllers/Api/NotifPushController.php` | Status, subscribe, unsubscribe, test push |
| Frontend polling | `public/assets/js/scripts.js` | Render badge/center dan mark-read |
| Frontend push | `public/assets/js/push-subscription.js` | Explicit opt-in push, sync subscription, unsubscribe saat logout, cek HTTP status |
| Service worker | `public/sw.js` | Tampilkan push dan handle click |
| Menu user | `app/Views/template/generate_menu.php` | Tombol `Aktifkan Notifikasi` |
| Footer | `app/Views/template/footer.php` | Inject `SIGAPP_PWA` dan `VAPID_PUBLIC_KEY` |

## Routes

Routes utama:

```php
$routes->get('/getnotif', 'Notif::getNotif');
$routes->get('/loadnotif', 'Notif::loadNotif');
$routes->get('/notif/summary', 'Notif::getSummary');
$routes->get('/notif/center', 'Notif::getCenter');
$routes->get('/notif/open/(:num)', 'Notif::open/$1');
$routes->post('/notif/snooze', 'Notif::snooze');
$routes->post('/notif/mark-as-read/(:num)', 'Notif::markAsRead/$1');

$routes->group('api/notif', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function($routes) {
    $routes->get('push/status', 'NotifPushController::status');
    $routes->post('push/subscribe', 'NotifPushController::subscribe');
    $routes->post('push/unsubscribe', 'NotifPushController::unsubscribe');
    $routes->post('push/test', 'NotifPushController::test', ['filter' => 'throttle:5,60']);
});
```

Route SSE `/api/notif/stream` dan controller Ratchet lama sudah dihapus. Shared hosting memakai polling foreground dan Web Push background.

## Alur Tambah Notifikasi

API utama service:

```php
use App\Data\NotificationAudience;
use App\Data\NotificationData;
use App\Services\NotifikasiService;

$id = (new NotifikasiService())->create(
    new NotificationData($message, $actorUserId, $idKavling, $idKonsumen, $type, $idProyek),
    NotificationAudience::forGroups([3, 4, 9])
);
```

Wrapper legacy tetap tersedia supaya caller lama tidak putus:

```php
$service->tambah_notif('3;4;9', $message, $actorUserId, $idKavling, $idKonsumen, $type, $idProyek);
$service->tambah_notif_user($targetUserId, $message, $actorUserId, $idKavling, $idKonsumen, $type, $idProyek);
```

Behavior service:

- Semua operasi event, recipient, delivery, dan queue legacy dilakukan dalam satu transaksi.
- Target multi-group seperti `3;4;9` diurai menjadi recipient user unik.
- Target global `0` menjadi semua user aktif.
- Admin group `1` ditambahkan sebagai recipient semua event.
- Actor tetap menjadi recipient in-app agar melihat aktivitasnya sendiri.
- Delivery `email` dan `web_push` untuk actor ditandai `skipped`.
- Recipient menyimpan `in_app_visible`: jika channel In-App dimatikan untuk event tersebut, notifikasi tidak muncul di badge, dropdown, notification center, atau load-more akun itu.
- Preferensi Email dan Web Push dihitung per user per channel; channel yang dimatikan tidak menjadi delivery `pending`, tetapi dicatat sebagai `preference_blocked`.
- `notification_email_queue` lama tetap ditulis untuk rollback/transisi satu rilis.

Feature flag `.env`:

```dotenv
NOTIF_DUAL_WRITE_RECIPIENTS=true
NOTIF_WRITE_DELIVERY_OUTBOX=true
NOTIF_WRITE_LEGACY_EMAIL_QUEUE=true
NOTIF_USE_RECIPIENT_READ_PATH=true
NOTIF_EMAIL_USE_DELIVERY_OUTBOX=true
```

Semua flag default `true` jika tidak diisi. Saat rollback sementara, matikan read path baru dulu dengan `NOTIF_USE_RECIPIENT_READ_PATH=false`; legacy column tetap tersedia satu rilis.

## Read Path

`Notif.php` memakai `NotificationRepository`:

- Jika `notification_recipients` ada, list dan unread count dibaca dari recipient milik user login.
- Row recipient dengan `in_app_visible = 0` tidak dibaca oleh `/notif/summary`, `/notif/center`, `/loadnotif`, dan badge dropdown.
- `POST /notif/mark-as-read/{notificationId}` hanya update row recipient milik user login.
- Jika tabel baru belum ada, query fallback ke filter legacy `group_target`, `user_id`, dan global `0`.
- Bentuk response `/notif/summary`, `/notif/center`, dan `/loadnotif` tetap kompatibel dengan frontend lama.

## Web Push

Dependency wajib:

```json
"php-http/guzzle7-adapter": "^1.1"
```

`WebPushService` memakai `GuzzleHttp\Client` sebagai PSR-18 client eksplisit untuk `minishlink/web-push` v11. VAPID divalidasi saat service dibuat dan command dispatch dimulai. Key tidak dicetak ke log.

Format payload push dari delivery outbox:

- `title`: nama pembuat notifikasi dari `users.name`, fallback `users.username`, fallback `SIGAPP`.
- `body`: `[Departemen] Isi notifikasi`, dengan departemen dari `auth_groups.name` dan fallback `Umum`.
- Isi notifikasi memakai `NotificationTextFormatter`: HTML entity didecode, tag HTML dihapus, whitespace dinormalisasi, dan jika tersedia diprefix dengan `{Nama Jalan} No. {No Kavling} - Isi notifikasi`. Hasilnya lalu dibatasi sekitar 180 karakter agar tetap rapi di notification tray.
- Jika actor punya lebih dari satu group, departemen yang dipakai adalah nilai agregasi stabil `MIN(auth_groups.name)` agar query aman pada MySQL `ONLY_FULL_GROUP_BY`.
- Test push memakai format yang sama dengan user login sebagai actor.

Konfigurasi `.env`:

```dotenv
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:admin@sigapp.dev
```

Frontend:

- Tidak ada auto-request permission saat page load.
- Page load hanya sync subscription jika `Notification.permission === "granted"`.
- Prompt browser hanya muncul setelah user klik `Aktifkan Notifikasi`.
- Response endpoint subscribe/unsubscribe wajib dicek `response.ok`; error 400/500 tidak boleh dilaporkan sukses.
- Endpoint yang sama dipindahkan ke user login terbaru lewat `endpoint_hash`, sehingga browser yang berganti akun tidak menerima push akun lama.
- Saat user menekan `Logout`, frontend mencoba memanggil `/api/notif/push/unsubscribe` untuk endpoint browser saat ini sebelum berpindah ke route logout. Logout tetap dilanjutkan walau unsubscribe gagal/timeout.
- Login SIGAPP dibuat panjang: form login mengirim `remember=1`, `Auth::$allowRemembering = true`, `Auth::$rememberLength = 365 * DAY`, dan `Session::$expiration = 31536000`.

## Navbar Notification Center

Dropdown lonceng memakai `/notif/summary` untuk badge dan `/notif/center` untuk isi lengkap. Saat dropdown dibuka, frontend me-refresh isi notifikasi agar user tidak melihat cache lama.

Behavior UI:

- Tab `Jatuh Tempo` tetap diprioritaskan saat ada urgent item.
- Jika urgent kosong dan activity tersedia, dropdown otomatis membuka tab `Aktivitas` selama user belum memilih tab secara manual.
- Tombol footer `Aktivitas Lagi` dihapus; activity tambahan dimuat lewat infinite scroll saat tab `Aktivitas` aktif.
- Tombol `Perbarui` menjadi icon button kecil di header dropdown.
- Isi activity memakai field `notif_text` dari backend. Field ini dibuat oleh `NotificationTextFormatter` supaya notif yang berisi tag HTML tidak tampil raw di dropdown, menampilkan `nama_jalan` + `no_kavling` jika tersedia, dan formatnya sama dengan email/Web Push.
- Frontend tetap punya fallback plain-text sanitizer untuk response lama yang belum memiliki `notif_text`.

## Endpoint Push

- `GET /api/notif/push/status`
- `POST /api/notif/push/subscribe`
- `POST /api/notif/push/unsubscribe`
- `POST /api/notif/push/test` dengan throttle `5/60`

## Delivery Outbox

Command shared hosting:

```bash
php spark notif:dispatch --channel=web_push --limit=100
```

Rekomendasi cron:

```cron
* * * * * cd /path/to/sigapp && php spark notif:dispatch --channel=web_push --limit=100 >> writable/logs/notif-dispatch.log 2>&1
```

Policy:

- Claim pending/failed delivery dengan `claim_token`.
- Channel awal yang didukung dispatcher: `web_push`.
- Endpoint expired `404/410` dinonaktifkan.
- Transport error, `429`, dan `5xx` dijadwalkan retry.
- Maksimum 5 attempt.
- Delay retry: 1, 5, 15, lalu 60 menit.
- Delivery tanpa subscription aktif ditandai `skipped`.
- Delivery yang diblokir preferensi user ditandai `preference_blocked` dengan `processed_at` terisi, sehingga tidak pernah diklaim dispatcher.

## Email Digest

Command:

```bash
php spark notif:send-digest
```

Behavior:

- Sumber payload email tetap `notification.notif`; queue hanya menyimpan referensi dan status delivery.
- Isi email dinormalisasi dengan `NotificationTextFormatter`, diprefix lokasi `nama_jalan` + `no_kavling` jika tersedia, di-escape saat dirender, dan ditampilkan langsung tanpa label `Isi Notifikasi`.
- Jika `notification_deliveries` ada dan memiliki row email pending/failed, digest membaca delivery baru per user dan mengecek `notification_recipients.read_at` saat command dijalankan.
- Delivery yang notifikasinya sudah dibaca tidak dikirim dan ditandai `skipped`.
- Jika belum ada delivery baru, service fallback ke `notification_email_queue` dan tetap memfilter unread per user melalui `notification_recipients`; jika tabel recipient belum tersedia, fallback memakai `notification.is_read`.
- Queue legacy dengan `notification_id` yang sudah memiliki delivery email ditandai `skipped`, sehingga dual-write tidak mengirim notifikasi yang sama dua kali.
- Queue legacy yang tidak mempunyai penerima unread/eligible juga ditandai `skipped` agar tidak tertahan sebagai `pending`.
- Email user hanya ditandai `sent` setelah `send()` benar-benar sukses.
- Email gagal tidak dilaporkan sukses dan delivery/queue diberi status `failed`.
- User tanpa email atau `email_notif_enabled = 0` ditandai `skipped`.
- Google Calendar sync hanya dipanggil setelah email user sukses.
- Event Google Calendar untuk tiket masalah urgent memakai judul:
  - Kavling: `[SIGAPP] {Nama Jalan} No {No Kavling}/{Tipe Rumah}: {Keterangan Tiket}`
  - Others: `[SIGAPP] {Nama Jalan}/{TIPE AREA} {Nama Area}: {Keterangan Tiket}`
- Judul Calendar dibuat dari detail tiket terbaru, dinormalisasi menjadi plain text, dan tidak mengubah isi notifikasi asli di `notification.notif`.

## Tabel

### `notification`

Tetap menjadi event/payload:

- `id`
- `notif`
- `group_target`
- `user_id`
- `type`
- `is_read`
- `add_by`
- `id_kavling`
- `id_konsumen`
- `id_proyek`
- `seen_by`
- `created_at`

Kolom legacy `group_target`, `user_id`, `is_read`, dan `seen_by` tidak dihapus pada rilis pertama.

### `notification_recipients`

Kolom:

- `id`
- `notification_id`
- `user_id`
- `in_app_visible`: `1` tampil di UI In-App, `0` disembunyikan total dari badge/dropdown/center/load-more
- `read_at`
- `created_at`
- `updated_at`

Index:

- unique `uniq_notif_recip_user (notification_id, user_id)`
- `idx_notif_recip_user_read (user_id, read_at, notification_id)`
- `idx_notif_recip_user_visible_read (user_id, in_app_visible, read_at, notification_id)`

### `notification_deliveries`

Kolom:

- `id`
- `notification_recipient_id`
- `notification_id`
- `user_id`
- `channel`: `web_push`, `email`
- `status`: `pending`, `processing`, `sent`, `failed`, `skipped`, `preference_blocked`
- `attempts`
- `available_at`
- `processed_at`
- `claimed_at`
- `claim_token`
- `last_error`
- `created_at`
- `updated_at`

Index:

- unique `uniq_notif_delivery_recipient_channel (notification_recipient_id, channel)`
- `idx_notif_delivery_queue (channel, status, available_at, id)`
- `idx_notif_delivery_notif_user (notification_id, user_id)`

### `push_subscriptions`

Kolom baru:

- `endpoint_hash` unique, SHA-256 dari endpoint
- `last_seen_at`
- `disabled_at`
- `failure_count`

Endpoint duplikat lama dinonaktifkan saat migration. Row terbaru dipertahankan aktif.

### `notification_email_queue`

Masih dipertahankan untuk transisi:

- `id`
- `notification_id`
- `target_group`
- `target_user_id`
- `actor_user_id`
- `status`: `pending`, `sent`, `failed`, `skipped`
- `batch_id`
- `processed_at`
- `created_at`

## Migration dan Rollout

Migration baru:

```bash
php spark migrate
```

Migration `2026-09-09-000001_AddNotificationPreferenceVisibility` menambahkan `notification_recipients.in_app_visible`, index visibility, dan status `preference_blocked` pada enum `notification_deliveries.status`. Saat rollback, row `preference_blocked` diubah menjadi `skipped` sebelum enum lama dipulihkan.

Migration `2026-09-09-000002_SyncNotificationEventTypesFromSeeder` memanggil `NotificationEventTypeSeeder` agar event registry baru otomatis masuk saat `php spark migrate`, meskipun tabel `notification_event_types` sudah berisi event lama. Seeder memakai `INSERT IGNORE`, jadi event yang sudah ada tidak diduplikasi.

Migration `2026-09-02-000001_AddSkippedStatusToNotificationEmailQueue` menambahkan status `skipped` pada queue legacy. Saat rollback, row `skipped` diubah menjadi `sent` sebelum enum lama dipulihkan agar notifikasi yang sengaja dilewati tidak terkirim ulang.

Sebelum menjalankan di production/shared hosting, backup minimal tabel:

- `notification`
- `notification_email_queue`
- `push_subscriptions`
- `users`
- `auth_groups_users`

Backfill migration:

- membuat recipient dari actor, personal `user_id`, target global `0`, target group semicolon, dan admin group;
- notifikasi lama dengan `is_read = 1` dianggap sudah dibaca semua recipient hasil backfill;
- menambahkan `endpoint_hash` dan menonaktifkan endpoint push duplikat sebelum unique index dibuat.

Setelah deploy:

1. Jalankan migration.
2. Pastikan `/notif/summary` dan `/notif/center` tetap tampil.
3. Aktifkan cron `notif:dispatch`.
4. Uji `POST /api/notif/push/test` pada HTTPS.
5. Pantau delivery pending/failed minimal 48 jam.
6. Jadwalkan penghapusan legacy column/table pada rilis terpisah setelah stabil.

## Query Monitoring

Delivery:

```sql
SELECT channel, status, COUNT(*) AS total
FROM notification_deliveries
GROUP BY channel, status;
```

Recipient unread:

```sql
SELECT COUNT(*) AS unread
FROM notification_recipients
WHERE user_id = ?
  AND in_app_visible = 1
  AND read_at IS NULL;
```

Push subscription:

```sql
SELECT
  COUNT(*) AS total,
  COUNT(DISTINCT endpoint_hash) AS unique_endpoint,
  SUM(disabled_at IS NULL) AS active_endpoint
FROM push_subscriptions;
```

Email legacy:

```sql
SELECT status, COUNT(*) AS total
FROM notification_email_queue
GROUP BY status;
```

## Troubleshooting

### Web Push tidak terkirim

1. Pastikan `composer install --no-dev -o` sudah memasang `php-http/guzzle7-adapter`.
2. Pastikan tidak ada log `No PSR-18 clients found`.
3. Pastikan `VAPID_PUBLIC_KEY` dan `VAPID_PRIVATE_KEY` terisi.
4. Pastikan app dibuka via HTTPS.
5. Klik `Aktifkan Notifikasi`; jangan mengandalkan permission otomatis.
6. Cek `push_subscriptions.disabled_at`.
7. Jalankan `php spark notif:dispatch --channel=web_push --limit=100`.

### Badge tidak berubah

1. Cek response `/notif/summary`.
2. Cek row `notification_recipients` untuk user login.
3. Pastikan `read_at` user lain tidak ikut berubah saat user saat ini mark-read.
4. Jika tabel baru belum ada, pastikan fallback legacy masih cocok dengan `group_target`.

### Email digest aneh

1. Cek SMTP `.env`.
2. Jalankan `php spark notif:send-digest`.
3. Pastikan queue/delivery gagal tidak berubah menjadi `sent`.
4. Pastikan delivery yang `notification_recipients.read_at` sudah terisi berubah menjadi `skipped` dan tidak muncul di email.
5. Pastikan queue legacy yang sudah memiliki email delivery berubah menjadi `skipped`, bukan dikirim ulang.
6. Pastikan Calendar hanya sync setelah email sukses.

## Checklist Verifikasi

```bash
php -l app/Controllers/Notif.php
php -l app/Repositories/NotificationRepository.php
php -l app/Services/NotifikasiService.php
php -l app/Services/EmailDigestService.php
php -l app/Support/NotificationTextFormatter.php
php -l app/Services/WebPushService.php
php -l app/Services/NotificationDispatchService.php
php -l app/Controllers/Api/NotifPushController.php
php -l app/Commands/SendEmailDigest.php
php -l app/Commands/DispatchNotifications.php
php -l app/Database/Migrations/2026-09-01-000001_NormalizeNotificationDeliveryOutbox.php
php -l app/Database/Migrations/2026-09-02-000001_AddSkippedStatusToNotificationEmailQueue.php
php -l app/Database/Migrations/2026-09-09-000001_AddNotificationPreferenceVisibility.php
php -l app/Database/Migrations/2026-09-09-000002_SyncNotificationEventTypesFromSeeder.php
php spark routes
php spark list notif
php spark migrate:status
```

Acceptance:

- Tidak ada lagi log `No PSR-18 clients found`.
- Subscribe berulang menghasilkan satu endpoint aktif.
- Test push tampil sebagai notifikasi sistem.
- Badge halaman aktif diperbarui maksimal sekitar 30 detik.
- User A mark-read tidak mengubah unread user B.
- Toggle In-App off membuat event tersebut hilang dari badge, dropdown, center, dan load-more akun itu.
- Toggle Web Push off tidak menghasilkan delivery `web_push` pending untuk akun itu.
- Toggle Email off tidak menghasilkan delivery `email` pending untuk akun itu.
- Web Push tetap bisa terkirim saat In-App off jika channel Web Push event tersebut aktif.
- Logout menonaktifkan endpoint browser saat ini, lalu login akun lain memindahkan endpoint ke user baru melalui `endpoint_hash`.
- `/notif/open/{id}` mengarahkan aman ke home bila user login bukan recipient notifikasi.
- Email digest hanya memuat item yang masih unread untuk penerima saat command dijalankan.
- Isi activity, email, dan Web Push sama-sama plain text serta menampilkan `{Nama Jalan} No. {No Kavling}` jika notifikasi memiliki `id_kavling`; template email tidak menampilkan label `Isi Notifikasi`.
- Queue legacy yang tumpang tindih dengan outbox tidak menyebabkan email duplikat.
- Pending/failed delivery dapat dipantau dan diproses ulang dengan aman.


## Web Push Deep Linking & Routing Baru

Notifikasi push SIGAPP sekarang memuat ikon proyek (logo), nama proyek + pengirim pada judul, dan mendukung deep-linking spesifik per modul.

### Endpoint Pembuka: `/notif/open/{id}`
Saat notifikasi push diklik, user tidak langsung diarahkan ke hardcoded URL. Service worker akan mengarahkan ke endpoint pembuka `/notif/open/{id}` yang akan:
1. Memvalidasi bahwa `user_id` login cocok dengan penerima notifikasi.
2. Otomatis menandai notifikasi tersebut sebagai sudah dibaca (`read_at = NOW()`).
3. Mengecek `id_proyek` notifikasi. Jika berbeda dengan sesi proyek aktif, sistem akan **otomatis mengganti sesi proyek** (`ActiveProyekService::setActive`).
4. Mengarahkan pengguna ke `action_url` spesifik.

### Daftar Mapping Action URL & Tipe Notifikasi
Notifikasi disisipkan action URL agar saat diklik langsung membuka modal/tab terkait:
- **Produksi / Master Data / General**: `siteplan/view?id_kavling={id}` -> Membuka modal detail kavling default.
- **Konsumen & MKDT**: `siteplan/view?id_kavling={id}&tab=konsumen` -> Membuka modal detail kavling langsung pada tab Konsumen.
- **Keuangan & Tagihan**: `siteplan/view?id_kavling={id}&tab=keuangan` -> Membuka modal detail kavling pada tab Keuangan.
- **Legal & Pajak**: `siteplan/view?id_kavling={id}&tab=legal` -> Membuka modal detail kavling pada tab Legal.
- **Tiket Masalah**: `siteplan/view?id_kavling={id}&filter=Masalah&tiket_ref_type={ref_type}` -> Membuka modal detail kavling dengan filter tiket masalah.
- **Cashout Subkon**: `cashout/subkon?open_kavling={id_kavling}` -> Membuka daftar cashout dan otomatis menampilkan data kavling terkait.
- **Member Get Member**: `member-get-member?id_referral={id}` -> Membuka halaman referral dan menampilkan detailnya.

## Preferensi Notifikasi Dinamis (Opt-In / Opt-Out)

Mulai sekarang, notifikasi mendukung preferensi granular per user per channel (In-App, Email, Web Push) berdasarkan event type spesifik. Arsitektur terbaru telah diubah agar sistem menjadi **sepenuhnya dinamis**—setiap pengguna dari departemen manapun berhak (dan bisa) menerima notifikasi apapun jika mereka mengaktifkannya di pengaturan (berperilaku sebagai Opt-In).

**Arsitektur Preferensi & Dynamic Resolution:**
1. **Registry Event**: `app/Enums/NotificationEvent.php` mendaftarkan konstanta event, sedangkan tabel `notification_event_types` (di-seed lewat `NotificationEventTypeSeeder`) mendaftarkan metadata UI dan nilai *default* preferensi.
   - Event `is_mandatory = 1` selalu dikembalikan API sebagai channel aktif dan `is_locked = true`, sehingga UI tidak menampilkan opt-out palsu.
2. **Dynamic Recipient Resolution**: Pada saat `NotifikasiService::create` dipanggil, penerima **tidak lagi** dibatasi secara mutlak oleh `group_target` bawaan dari legacy caller (misal: `"3;4;9"`). Sistem akan membaca preferensi setiap pengguna aktif:
   - Jika pengguna menghidupkan minimal satu channel event tersebut di tabel `user_notification_preferences`, mereka akan di-inklusikan sebagai penerima.
   - Jika tidak ada preferensi eksplisit, sistem mengikuti gabungan default `default_in_app`, `default_email`, dan `default_web_push`.
   - Proses fallback ke `group_target` legacy tetap ada untuk memastikan _backward compatibility_ pada event yang belum terdaftar di registry.
3. **Override per User**: Pengaturan user disimpan di tabel `user_notification_preferences`. Terdapat kolom `is_locked` (TINYINT) yang jika bernilai 1 maka preferensi tidak bisa diubah (paksaan admin).
4. **Pemotongan Pengiriman (Delivery Gatekeeper)**:
   - Jika preferensi In-App aktif, row `notification_recipients.in_app_visible` bernilai `1`.
   - Jika preferensi In-App dimatikan, row recipient tetap boleh ada untuk kebutuhan Email/Web Push, tetapi `in_app_visible = 0` sehingga notifikasi hilang total dari UI akun tersebut.
   - Save/reset preferensi merekalkulasi `in_app_visible` untuk row recipient user tersebut yang sudah ada.
   - Jika preferensi Email / Web Push dimatikan: row `notification_deliveries` untuk channel tersebut ditandai `preference_blocked` dengan `processed_at` terisi, bukan `pending`.
5. **Caller Implementation**: Fungsi pembantu `tambah_notif` memilik signature argumen ke-9: `?string $eventType = null`. Pengembang wajib mengirim konstanta `NotificationEvent::NAMA_EVENT` setiap kali memanggil notifikasi dari Controller/Service agar Dynamic Resolution bisa berjalan.
