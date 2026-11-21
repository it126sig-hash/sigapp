# Modul Notifikasi SIGAPP

Dokumen ini adalah acuan teknis modul notifikasi SIGAPP. Update file ini setiap ada perubahan alur notifikasi, endpoint, tabel, service, command, konfigurasi delivery, atau side effect.

Terakhir dicek: 2026-11-21 (Penambahan alur notifikasi balik Request Kavling `NotificationEvent::REQUEST_KAVLING` dari Planning ke pengaju / MKDT)

## Ringkasan

Modul notifikasi sekarang memakai pola modular berlapis (*Thin Controller → Service → Repository Pattern*):

1. `notification` tetap menjadi tabel event dan payload utama.
2. `notification_recipients` menyimpan status baca per user dan flag visibility In-App.
3. `notification_deliveries` menjadi outbox delivery per recipient dan channel, termasuk status `preference_blocked`.
4. Navbar/halaman aktif tetap polling `/notif/summary` setiap 30 detik (didukung rute modern `/api/notif/summary` dan shim legacy `/notif/summary`).
5. Browser/PWA background memakai Web Push, tanpa self-hosted WebSocket/SSE.
6. Email digest membaca delivery recipient-specific yang masih unread jika tabel baru tersedia, lalu fallback ke `notification_email_queue` lama selama masa transisi.
7. Arsitektur backend telah dimigrasikan secara penuh ke subfolder `Web\Notifikasi`, `Api\Notifikasi`, `Services\Notifikasi`, dan `Repositories\Notifikasi` dengan 14 class Zero-Downtime Shim di root namespace untuk menjaga kompatibilitas ke belakang tanpa downtime.

## File Utama

| Area | File Modular Baru | Shim Legacy (Zero-Downtime) | Peran |
| --- | --- | --- | --- |
| Controller Web | `app/Controllers/Web/Notifikasi/NotifikasiController.php` | `app/Controllers/Notif.php` | Deep-link resolver (`open`), binary stream logo proyek (`icon`) |
| Controller Web Calendar | `app/Controllers/Web/Notifikasi/GoogleCalendarController.php` | `app/Controllers/GoogleCalendar.php` | Alur OAuth2 Google Calendar (`connect`, `callback`, `disconnect`) |
| Controller API Notif | `app/Controllers/Api/Notifikasi/NotifikasiController.php` | `app/Controllers/Notif.php` | REST API (`RAW_JSON_READY`) & Dual-Mode summary, center, load, snooze, mark-read |
| Controller API Push | `app/Controllers/Api/Notifikasi/NotifPushController.php` | `app/Controllers/Api/NotifPushController.php` | Endpoint Web Push (`status`, `subscribe`, `unsubscribe`, `test`) |
| Controller API Pref | `app/Controllers/Api/Notifikasi/NotificationPreferenceController.php` | `app/Controllers/Api/NotificationPreferenceController.php` | Endpoint preferensi user (`index`, `save`, `reset`) |
| Service Utama | `app/Services/Notifikasi/NotifikasiService.php` | `app/Services/NotifikasiService.php` | Insert event, resolve recipient, buat delivery outbox, dan queue legacy |
| Service Push Dispatcher | `app/Services/Notifikasi/NotificationDispatchService.php` | `app/Services/NotificationDispatchService.php` | Background dispatcher delivery channel `web_push` via cron |
| Service Preferensi | `app/Services/Notifikasi/NotificationPreferenceService.php` | `app/Services/NotificationPreferenceService.php` | Evaluasi opt-in/opt-out granular dan sinkronisasi visibilitas in-app |
| Service Navigasi | `app/Services/Notifikasi/NotificationNavigationService.php` | `app/Services/NotificationNavigationService.php` | Otorisasi penerima, auto-mark-read, dan switch sesi proyek aktif |
| Service Icon | `app/Services/Notifikasi/NotificationIconService.php` | `app/Services/NotificationIconService.php` | Resolve path avatar logo proyek fisik / fallback default |
| Service Web Push | `app/Services/Notifikasi/WebPushService.php` | `app/Services/WebPushService.php` | Client PSR-18 Guzzle, validasi VAPID, enkripsi payload, validasi sesi perangkat |
| Service Email Digest | `app/Services/Notifikasi/EmailDigestService.php` | `app/Services/EmailDigestService.php` | Pemrosesan batch digest dan pencegahan duplikasi pengiriman |
| Service Calendar | `app/Services/Notifikasi/GoogleCalendarService.php` | `app/Services/GoogleCalendarService.php` | Integrasi Google Calendar API untuk tiket masalah urgent |
| Repository List | `app/Repositories/Notifikasi/NotificationRepository.php` | `app/Repositories/NotificationRepository.php` | Query list, unread count, mark-read, user group ID, department context |
| Repository Legacy | `app/Repositories/Notifikasi/NotifRepository.php` | `app/Repositories/NotifRepository.php` | Method insert legacy; dipertahankan demi kompatibilitas mundur |
| DTO | `app/Data/NotificationData.php`, `app/Data/NotificationAudience.php` | - | Objek transfer data input service baru |
| Model Push Sub | `app/Models/PushSubscriptionModel.php` | - | Akses tabel `push_subscriptions` |
| View Email | `app/Views/emails/email_digest.php` | - | Template HTML email digest berkala |
| Formatter Teks | `app/Support/NotificationTextFormatter.php` | - | Normalisasi teks notifikasi dan prefix jalan/kavling |
| Command Email | `app/Commands/SendEmailDigest.php` | - | CLI command: `php spark notif:send-digest` |
| Command Push | `app/Commands/DispatchNotifications.php` | - | CLI command: `php spark notif:dispatch --channel=web_push --limit=100` |
| Frontend Push | `public/assets/js/push-subscription.js` | - | Script browser Web Push opt-in, CSRF handling, dan unregister saat logout |
| Service Worker | `public/sw.js` | - | Background service worker penampil push notification & handler klik |

## Routes

### 1. Modern Web Routes
```php
$routes->get('notif/open/(:num)', '\App\Controllers\Web\Notifikasi\NotifikasiController::open/$1');
$routes->get('notif/icon/(:num)', '\App\Controllers\Web\Notifikasi\NotifikasiController::icon/$1');
$routes->group('google-calendar', ['filter' => 'login'], function($routes) {
    $routes->get('connect', '\App\Controllers\Web\Notifikasi\GoogleCalendarController::connect');
    $routes->get('callback', '\App\Controllers\Web\Notifikasi\GoogleCalendarController::callback');
    $routes->post('disconnect', '\App\Controllers\Web\Notifikasi\GoogleCalendarController::disconnect');
});
```

### 2. Modern REST API Routes (BaseApiController, RAW_JSON_READY)
```php
$routes->group('api/notif', ['namespace' => 'App\Controllers\Api\Notifikasi', 'filter' => 'login'], function($routes) {
    $routes->get('summary', 'NotifikasiController::summary');
    $routes->get('center', 'NotifikasiController::center');
    $routes->post('snooze', 'NotifikasiController::snooze');
    $routes->get('load', 'NotifikasiController::load');
    $routes->post('mark-as-read/(:num)', 'NotifikasiController::markAsRead/$1');
    $routes->post('mark-all-as-read', 'NotifikasiController::markAllAsRead');
    $routes->get('list', 'NotifikasiController::list');

    // Web Push Subscriptions
    $routes->get('push/status', 'NotifPushController::status');
    $routes->post('push/subscribe', 'NotifPushController::subscribe');
    $routes->post('push/unsubscribe', 'NotifPushController::unsubscribe');
    $routes->post('push/test', 'NotifPushController::test', ['filter' => 'throttle:5,60']);

    // Notification Preferences
    $routes->get('preferences', 'NotificationPreferenceController::index');
    $routes->post('preferences', 'NotificationPreferenceController::save');
    $routes->post('preferences/reset', 'NotificationPreferenceController::reset');
});
```

### 3. Legacy Shim Routes (Zero-Downtime Backward Compatibility)
```php
// Rute flat legacy yang didelegasikan ke Api\Notifikasi\NotifikasiController
$routes->get('/getnotif', '\App\Controllers\Api\Notifikasi\NotifikasiController::getNotif');
$routes->get('/loadnotif', '\App\Controllers\Api\Notifikasi\NotifikasiController::loadNotif');
$routes->get('/notif/summary', '\App\Controllers\Api\Notifikasi\NotifikasiController::getSummary');
$routes->get('/notif/center', '\App\Controllers\Api\Notifikasi\NotifikasiController::getCenter');
$routes->post('/notif/snooze', '\App\Controllers\Api\Notifikasi\NotifikasiController::snooze');
$routes->post('/notif/mark-as-read/(:num)', '\App\Controllers\Api\Notifikasi\NotifikasiController::markAsRead/$1');
$routes->post('/notif/mark-all-as-read', '\App\Controllers\Api\Notifikasi\NotifikasiController::markAllAsRead');
$routes->match(['get', 'post'], '/notif/tambah_notif', '\App\Controllers\Api\Notifikasi\NotifikasiController::tambah_notif');
$routes->match(['get', 'post'], '/notif/getactivity', '\App\Controllers\Api\Notifikasi\NotifikasiController::getActivityRoute');
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
- Audience eksplisit `NotificationAudience::forUser()` / `forUsers()` tetap dibatasi ke user target meskipun event sudah terdaftar di registry; preferensi channel target tetap diterapkan.
- Admin group `1` dan actor ditambahkan untuk audience group/global. Keduanya tidak otomatis ditambahkan untuk audience user eksplisit agar notifikasi personal tidak bocor ke penerima lain.
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

## Event Bon Permintaan Barang

Modul BPB mengirim event personal dengan deep-link `/bpb?open={id}`:

| Event | Pemicu | Target | Kebijakan |
|---|---|---|---|
| `bpb_signature_requested` | Submit atau CC selesai | CC/Mengetahui aktif | Mandatory; In-App, Email, Web Push default aktif |
| `bpb_signature_completed` | Tahap tanda tangan selesai | Pemohon | Mengikuti preferensi |
| `bpb_rejected` | CC/Mengetahui menolak | Pemohon | Mengikuti preferensi |
| `bpb_approved` | Mengetahui selesai | Pemohon | Mengikuti preferensi |
| `bpb_status_changed` | Diproses/Cair/Pending/Dibeli | Pemohon | Mengikuti preferensi |

Definisi event berada di `NotificationEventTypeSeeder`, enum `NotificationEvent`, dan migration BPB. Pemanggilan memakai `NotifikasiService::tambah_notif_user()` sehingga recipient, delivery outbox, email queue, badge, dan web-push tetap melewati pipeline notifikasi utama.

## Event Fee Sales

Modul Fee Sales (`SalesExternalService`) mengirim event ke grup Keuangan (`3`) dan MKDT (`4`) menggunakan `NotifikasiService::tambah_notif('3;4', ...)` dengan deep-link `fee-sales?open_mkdt={id_mkdt}` (untuk alur per-unit/per-termin) atau `fee-sales/pengajuan/{share_token}` (fallback `fee-sales/pengajuan/{id_pengajuan}` untuk alur pengajuan grup):

| Event | Pemicu | Target Group | Default Preferensi |
|---|---|---|---|
| `fee_sales_pengajuan_created` | Pengajuan SPP per-termin dibuat (`submitPengajuanSpp`) atau pengajuan grup dibuat (`createPengajuanGroup`) | `3;4` (Keuangan, MKDT) | `default_in_app=1, default_email=1, default_web_push=1`, `relevant_groups='3;4'` (non-mandatory; otomatis nonaktif default-nya untuk grup selain `3` dan `4` di `NotificationPreferenceService`) |
| `fee_sales_pengajuan_rejected` | Pengajuan fee sales ditolak oleh pihak MKDT atau Keuangan dengan alasan/saran revisi harga (`rejectPengajuanGroup`) | `3;4` (Keuangan, MKDT) | `default_in_app=1, default_email=0, default_web_push=0`, `is_mandatory=1`, `relevant_groups='3;4'` |
| `fee_sales_termin_generated` | Termin fee sales baru di-generate (`saveFeeHeader` ketika `$generateTermin && $totalGeneratedTerminCount > 0` dan `$sendNotification = true`) | `3;4` (Keuangan, MKDT) | `default_in_app=1, default_email=1, default_web_push=1`, `relevant_groups='3;4'` |
| `fee_sales_harga_net_updated` | Harga Net AJB diperbarui (`saveFeeHeader` ketika `$isHargaChanged` bernilai `true` dan `$sendNotification = true`) | `3;4` (Keuangan, MKDT) | `default_in_app=1, default_email=1, default_web_push=1`, `relevant_groups='3;4'` |
| `fee_sales_ttd_signed` | Tanda tangan pengajuan grup disimpan (`signPengajuanGroup` untuk role `mkdt` atau `keuangan`) | `3;4` (Keuangan, MKDT) | `default_in_app=1, default_email=1, default_web_push=1`, `relevant_groups='3;4'` |
| `fee_sales_pencairan` | Pencairan fee sales dikonfirmasi (`confirmPencairan` per-termin atau `confirmPencairanGroup` grup) | `3;4` (Keuangan, MKDT) | `default_in_app=1, default_email=1, default_web_push=1`, `relevant_groups='3;4'` |

Catatan implementasi:
- Saat `createPengajuanGroup()` memanggil `saveFeeHeader(..., false)` untuk auto-inisialisasi fee header yang belum ada, parameter `$sendNotification = false` diteruskan agar hanya 1 notifikasi `fee_sales_pengajuan_created` yang dikirim (tidak menduplikasi `fee_sales_harga_net_updated` atau `fee_sales_termin_generated`).
- `NotificationPreferenceService::getUserPreferences()` dan `syncInAppVisibility()` mengevaluasi `relevant_groups` (`3;4`) terhadap seluruh grup user (`resolveUserGroups()` menggabungkan `$userGroupId` dan seluruh baris `auth_groups_users`) sehingga user di luar Keuangan/MKDT secara default mendapatkan `in_app = false`, `email = false`, `web_push = false` kecuali di-override secara eksplisit di `user_notification_preferences`.
- Klik notifikasi di dropdown navbar (`public/assets/js/scripts.js`) untuk `type && type.startsWith("fee_sales")` mengarahkan ke `/notif/open/{id_notif}` yang me-redirect ke `action_url` (`fee-sales?open_mkdt={id_mkdt}` atau `fee-sales/pengajuan/{share_token}`), dan `public/assets/js/sales/fee_sales.js` membaca query string `?open_mkdt=` saat halaman dimuat untuk otomatis membuka `openDetailModal(openMkdt)`.

## Event Request Kavling (`NotificationEvent::REQUEST_KAVLING`)

Modul `Transaksi` (`App\Services\Transaksi\KavlingRequestService`) mengirim event `request_kavling` (`NotificationEvent::REQUEST_KAVLING`) melalui `NotifikasiService::create()` dengan dua arah komunikasi: pengajuan dari MKDT/Admin ke Planning dan **notifikasi balik** dari Planning ke pengaju / MKDT. Event ini terdaftar di `NotificationEventTypeSeeder` dengan default `default_in_app = 1`, `default_email = 1`, `default_web_push = 1`, `is_mandatory = 0`, `relevant_groups = '6'`.

| Arah / Pemicu | Method Pemicu | Target Audience | Action URL | Template Pesan & Prioritas |
|---|---|---|---|---|
| **Pengajuan Tambah Kavling Baru (Multi-Jalan)** | `KavlingRequestService::submitTambahBaru()` | `NotificationAudience::forGroups([6])` (Planning + opt-in preferensi) | `siteplan/view` | `"[Request Siteplan] {pengaju} mengajukan penambahan kavling baru ({lokasiText}). Tipe: {tipeText} di {namaProyek}. Catatan: {keterangan}"` (normal; `default_in_app=1, default_email=1, default_web_push=1`) |
| **Pengajuan Ubah Tipe (Multi-Kavling / Single / Batch Target)** | `submitMultiUbahTipe()`, `submitLegacySingleUbahTipe()`, `submitBatchItemsUbahTipe()` | `NotificationAudience::forGroups([6])` (Planning + opt-in preferensi) | `siteplan/view?id_kavling={id}` (1 kavling) atau `siteplan/view` (multi/batch) | `"[Request Siteplan] {pengaju} mengajukan perubahan tipe {countKav} kavling ({previewText}) menjadi {tipeText} di {namaProyek}. Catatan: {keterangan}"` (atau format tunggal/batch) |
| **Notifikasi Balik Selesai / Ditolak oleh Planning** | `KavlingRequestService::updateStatus()` saat `status` = `'approved'` / `'rejected'` (memanggil `sendStatusFeedbackNotification()`) | `NotificationAudience::forUsers([$createdBy])` jika `created_by > 0`; fallback `NotificationAudience::forGroups([4])` (MKDT) | `siteplan/view?id_kavling={id}` atau `siteplan/view` | `"[Request Siteplan] Pengajuan {jenisLabel} (#{idRequest}) di {namaProyek} telah {statusText} oleh {pemroses}."` (`statusText`: `'disetujui / selesai dikerjakan'` \| `'ditolak'`) + `" Catatan Planning: {catatanPlanning}"` (bila diisi) |
| **Notifikasi Balik Terapkan Ubah Tipe Kavling** | `KavlingRequestService::applyUbahTipeKavling()` (memanggil `sendStatusFeedbackNotification()`) | `NotificationAudience::forUsers([$createdBy])` jika `created_by > 0`; fallback `NotificationAudience::forGroups([4])` (MKDT) | `siteplan/view?id_kavling={id}` (1 kavling terpengaruh) atau `siteplan/view` | `"[Request Siteplan] Pengajuan ubah tipe kavling (#{idRequest}) di {namaProyek} telah disetujui / selesai dikerjakan oleh {pemroses}. Tipe diterapkan menjadi {appliedTipeLabel} untuk {N} kavling."` + `" Catatan Planning: {catatanPlanning}"` (bila diisi) |

Catatan implementasi:
- Pemanggilan notifikasi balik pada `sendStatusFeedbackNotification()` menggunakan `NotificationAudience::forUsers([$createdBy])` bila `kavling_request.created_by > 0` agar notifikasi bersifat personal ke pengaju awal tanpa membocorkan ke seluruh user atau otomatis menambahkan admin/actor lain; bila `created_by` tidak tersedia, audiens dialihkan ke Group `4` (`NotificationAudience::forGroups([4])` — MKDT).
- Seluruh pemanggilan `NotifikasiService::create()` pada `KavlingRequestService` dibungkus di dalam transaksi DB (`transStart`/`transComplete`), dan `action_url` mendukung pembukaan proyek terkait melalui endpoint `/notif/open/{id}`.

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
- Subscription push ditautkan ke `auth_device_sessions.device_session_id`. Setelah registri sesi perangkat tersedia, hanya subscription dengan sesi perangkat yang masih aktif dan belum kedaluwarsa yang ikut pengiriman; subscription lama yang belum tertaut tidak lagi dipakai.
- Pencabutan/logout perangkat menonaktifkan subscription push terkait di transaksi yang sama, sehingga tidak menunggu browser mengirim unsubscribe.
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

### Event Keuangan Rekonsiliasi
- **`rekonsiliasi_keuangan`**: Dikirim ketika Keuangan menyimpan alokasi pembayaran yang melebihi tagihan item di MKDT atau item tersebut memiliki target Rp 0. Target default notifikasi ini adalah divisi **Marketing Data (group `4`)** dan `action_url` mengarahkan ke halaman `list-kavling`.

## Catatan Teknis, Debugging & Audit Pemeliharaan (Oktober 2026)

Bagian ini merekam riwayat perbaikan bug (RCA), pembersihan dead code, dan inventaris fungsi kompatibilitas ke belakang (*backward-compatibility shims*) untuk kebutuhan debugging dan panduan pembaruan/refactoring di kemudian hari.

### 1. Riwayat Perbaikan Bug & Debugging RCA (2026-10-22)

1. **Validasi Sesi Perangkat pada Web Push Dispatch (`NotificationDispatchService::dispatchWebPush`)**:
   - **Isu**: Pengiriman Web Push cron sebelumnya dapat mencoba mengirim ke langganan push yang tidak valid/stale jika tidak diverifikasi dengan status user aktif.
   - **Perbaikan**: Mengintegrasikan `PushSubscriptionModel::activeForUser($userId)` untuk memvalidasi perangkat aktif sebelum payload dikirimkan via `WebPushService`, mencegah pengiriman mubazir ke perangkat yang sudah tidak aktif atau logout.
2. **Normalisasi `notification.type` pada Sinkronisasi Visibilitas In-App (`NotificationPreferenceService::syncInAppVisibility`)**:
   - **Isu**: Event tiket masalah (`tiket_masalah_dibuat`, `tiket_masalah_dibalas`, `tiket_masalah_selesai`) dan pencairan hasil akad (`pencairan_hasil_akad`) memiliki format penamaan tipe/event yang bervariasi antara format legacy dan `NotificationEvent`. Pada `syncInAppVisibility()`, ketiadaan pemetaan yang tepat menyebabkan inkonsistensi penentuan `in_app_visible` saat pengguna menyimpan atau mereset preferensi notifikasi.
   - **Perbaikan**: Menambahkan normalisasi pemetaan tipe event (`mapNotificationTypeToEventType`) pada query pembaruan `notification_recipients` agar status `in_app_visible` terefleksi secara akurat sesuai preferensi pengguna untuk tiket masalah dan pencairan hasil akad.
3. **Resolusi Opt-In Lintas Grup (`NotifikasiService::resolveRecipientIds`)**:
   - **Isu**: Ketika user di luar grup target bawaan secara eksplisit melakukan *opt-in* pada event tertentu di pengaturan preferensinya, resolusi penerima sebelumnya berisiko mengabaikan user tersebut jika filter awal terlalu restriktif pada target grup legacy.
   - **Perbaikan**: `NotifikasiService::resolveRecipientIds()` kini memeriksa preferensi seluruh user aktif yang menyalakan channel terkait, sehingga mekanisme preferensi dinamis (*opt-in* lintas departemen/grup) berjalan konsisten.

### 2. Dead Code yang Telah Dibersihkan

Pembersihan kode mati yang tidak lagi memiliki pemanggil aktif atau redundan:
- **`NotificationDispatchService::urlForNotification()`**: Dihapus karena penentuan `action_url` sudah sepenuhnya dilakukan saat pembuatan notifikasi (`NotifikasiService::create` / `tambah_notif`), disimpan pada kolom `notification.action_url`, dan ditangani secara terpusat oleh endpoint deep-linking `/notif/open/{id}`.
- **`Notif::applyGroupTargetFilter()`**: Dihapus dari controller `Notif.php` karena filtering akses grup dan visibilitas pengguna kini sepenuhnya ditangani pada layer data (`NotificationRepository` dan `NotificationPreferenceService`) melalui relasi `notification_recipients`.

### 3. Inventaris Fungsi Kompatibilitas, Rute Pengganti, dan Deprekasi (Backward-Compatibility Shims)

Komponen berikut sengaja dipertahankan untuk menjamin *zero-breaking change* pada pemanggil legacy, namun **wajib diperbarui dan dibersihkan di kemudian hari** setelah seluruh modul pemanggil selesai dimigrasikan:

| Komponen / Method / Rute | Lokasi | Alasan Dipertahankan (Shim) | Rencana Pembaruan / Deprekasi |
| --- | --- | --- | --- |
| `Notif::tambah_notif()` | `app/Controllers/Notif.php` | Controller method shim yang membungkus `NotifikasiService::tambah_notif()`. Masih dapat diakses via rute legacy/AutoRoute. | Hapus method controller ini setelah semua pemanggil internal beralih langsung ke dependency injection `NotifikasiService`. |
| `Notif::getActivity()` | `app/Controllers/Notif.php` | Controller method shim untuk endpoint legacy `/notif/getactivity`. | Tandai deprecated; alihkan konsumsi activity feed sepenuhnya ke endpoint REST API terstruktur. |
| `NotifRepository::tambah_notif()` | `app/Repositories/NotifRepository.php` | Method insert langsung ke tabel `notification` model lama. Sudah tidak dipanggil oleh alur baru aktif. | Hapus method ini sepenuhnya saat `NotifRepository` didepresiasi dan digantikan penuh oleh `NotificationRepository`. |
| Legacy Target Format String `"3;4;9"` | Controller legacy (`Direksi`, `Mkdt`, `Pajak`, `Siteplan`, `Tagihan`) | Parameter target grup berupa string *semicolon-separated* (contoh: `"3;4;9"`) yang diteruskan ke `tambah_notif()`. | Refaktor caller di controller-controller lama tersebut agar mengadopsi DTO `NotificationData` + `NotificationAudience::forGroups(...)` dan konstanta resmi `NotificationEvent::*`. |
| Alias Event Legacy Fee Sales (`fee_sales_pengajuan`, `fee_sales_pengajuan_rejected`, `fee_sales_reject`, `fee_sales_rejection`, `fee_sales_termin`, `fee_sales_harga_net`, `fee_sales_ttd`, `fee_sales_cair`) | `NotificationEvent::fromLegacyType()` (`app/Enums/NotificationEvent.php`) | Pemetaan kompatibilitas mundur ke kode kanonik (`fee_sales_pengajuan_created`, `fee_sales_pengajuan_rejected`, `fee_sales_termin_generated`, `fee_sales_harga_net_updated`, `fee_sales_ttd_signed`, `fee_sales_pencairan`). | Gunakan hanya konstanta resmi `NotificationEvent::FEE_SALES_*` pada seluruh kode baru; hapus pemetaan alias pendek setelah dipastikan tidak ada baris historis yang memakainya. |
| Deep-Link Pengajuan Grup Berbasis ID (`fee-sales/pengajuan/{id}`) | `SalesExternalController::pengajuanDetail()`, `SalesExternalService` | Fallback untuk pengajuan grup lama yang belum memiliki `share_token`. | Seluruh notifikasi dan tautan baru wajib memakai `fee-sales/pengajuan/{share_token}`; fallback berbasis `{id}` numerik dipertahankan hanya untuk data historis. |
| Alur Pengajuan SPP Per-Termin (`POST /api/sales-external/fee/termin/(:num)/spp` / `submitPengajuanSpp`) | `SalesExternalApiController`, `SalesExternalService` | Alur legacy pengajuan SPP satu per satu per termin (`action_url => fee-sales?open_mkdt={id_mkdt}`). | Diarahkan secara bertahap ke alur Pengajuan Pencairan Grup (`POST /api/sales-external/fee/pengajuan` / `createPengajuanGroup`) dengan tanda tangan digital multi-pihak dan `share_token`. |




## Catatan Refaktor Arsitektur Modular CI4 Web/Api & Services (2026-11-12)

Pada tanggal 12 November 2026, Modul Notifikasi telah berhasil direfaktor penuh (*Score 100/100, PASS QA*) mengadopsi standar arsitektur modular SIGAPP (*Thin Controller → Service → Repository Pattern*).

### Rincian Peningkatan Arsitektur:
1. **Pemisahan Web dan API Controller:**
   - Web Controller (`Web\Notifikasi\NotifikasiController`, `Web\Notifikasi\GoogleCalendarController`) melayani permintaan berbasis browser (redirect deep link, stream biner icon PNG, dan alur OAuth2).
   - API Controller (`Api\Notifikasi\NotifikasiController`, `Api\Notifikasi\NotifPushController`, `Api\Notifikasi\NotificationPreferenceController`) mewarisi `BaseApiController` dengan envelope standar JSON (`success`, `data`, `message`) dan berstatus `RAW_JSON_READY` untuk persiapan migrasi ke Vue 3 / Capacitor Mobile.
2. **Dual-Mode Response (`summary()` & `center()`):**
   - Mendeteksi konteks pemanggil: jika dipanggil dari endpoint REST API modern (`/api/notif/summary`), mengembalikan format JSON murni (`BaseApiController::respond()`).
   - Jika dipanggil dari rute legacy navbar dropdown (`/notif/summary` atau `/getnotif`), mengembalikan format array JSON legacy yang diharapkan oleh `public/assets/js/scripts.js` (`status`, `unread`, `urgent_count`, dll.) sehingga UI navbar eksisting tidak terganggu.
3. **Eliminasi Total Direct DB Access (Zero Layer Violations):**
   - Menghapus semua penggunaan `db_connect()` dan `$this->db` dari Controller layer.
   - Menambahkan method query Data Access bersih pada `NotificationRepository`:
     - `getUserGroupId(int $userId): int`: Mengambil ID grup pengguna via `auth_groups_users`.
     - `getProyekName(int $idProyek): string`: Mengambil nama proyek.
     - `getActorDepartmentContext(int $userId): array`: Mengambil nama actor, username, dan nama grup/departemen dengan agregasi stabil `MIN(auth_groups.name)` yang patuh MySQL `ONLY_FULL_GROUP_BY`.
4. **Keamanan Sesi Perangkat Web Push:**
   - `WebPushService::getActiveSubscriptionsForUser(int $userId)` memvalidasi relasi subscription terhadap `auth_device_sessions`. Hanya perangkat dengan sesi aktif dan belum kedaluwarsa yang akan dikirimi Web Push.
5. **Penyelarasan Frontend & Aset:**
   - `public/assets/js/push-subscription.js` diselaraskan untuk membaca envelope data `res.data` atau fallback `res`, serta memperbarui token CSRF CI4 secara dinamis dari respons server.
   - `public/sw.js` diberikan pengaman ekstra (null-safety guard) pada `event.data.json()`.
6. **14 Zero-Downtime Shims:**
   - Seluruh nama class lama di root namespace `App\Controllers`, `App\Services`, dan `App\Repositories` dipertahankan sebagai shim class yang meng-extend class modular baru, menjamin kontinuitas total 100% bagi pemanggil legacy tanpa downtime.
