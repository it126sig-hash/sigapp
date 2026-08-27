# Modul Notifikasi SIGAPP

Dokumen ini menjadi acuan modul notifikasi SIGAPP. Update dokumen ini setiap ada perubahan pada alur notifikasi, endpoint, tabel, service, atau konfigurasi pengiriman.

Terakhir dicek: 2026-08-27

## Rekomendasi Penyimpanan

Dokumen ini disimpan di `docs/` karena isinya adalah referensi teknis proyek yang perlu dibaca dan diubah bersama kode. Gunakan skill Codex hanya jika aturan modul ini perlu menjadi instruksi otomatis untuk agent setiap kali mengubah notifikasi.

Jika nanti modul notifikasi makin sering dikerjakan, isi dokumen ini bisa diringkas menjadi skill terpisah, misalnya `skills/sigapp-notifikasi/SKILL.md`, dengan aturan kerja yang lebih prosedural.

## Ringkasan Modul

Modul notifikasi terdiri dari beberapa jalur:

1. Notifikasi aplikasi di navbar bell.
2. Notifikasi urgent berbasis jatuh tempo/proyek aktif.
3. Browser Web Push melalui service worker dan VAPID.
4. Email digest melalui queue `notification_email_queue`.
5. SSE endpoint yang tersedia, tetapi frontend saat ini memakai polling.

Sumber data utama berada di tabel `notification`. Side effect untuk email dan push dibuat oleh `App\Services\NotifikasiService`.

## File Utama

| Area | File | Peran |
| --- | --- | --- |
| Controller notifikasi aplikasi | `app/Controllers/Notif.php` | Endpoint summary, center, load more, snooze, mark as read, dan facade `tambah_notif()` |
| Service notifikasi baru | `app/Services/NotifikasiService.php` | Insert notification, queue email, trigger web push |
| Repository lama | `app/Repositories/NotifRepository.php` | Insert notification saja; tidak membuat email queue dan tidak trigger web push |
| Email digest | `app/Services/EmailDigestService.php` | Ambil queue pending, kelompokkan target, kirim email HTML |
| Command digest | `app/Commands/SendEmailDigest.php` | Command `php spark notif:send-digest` |
| Template email | `app/Views/emails/email_digest.php` | HTML email digest |
| Web push | `app/Services/WebPushService.php` | Simpan subscription dan kirim push ke user/group |
| API push | `app/Controllers/Api/NotifPushController.php` | Subscribe/unsubscribe push |
| SSE | `app/Controllers/Api/SseController.php` | Stream badge dan notification event |
| Service worker | `public/sw.js` | Handle push event dan notification click |
| Frontend navbar | `public/assets/js/scripts.js` | Polling summary/center, render notification center, mark read |
| Push subscription frontend | `public/assets/js/push-subscription.js` | Request permission, subscribe PushManager, kirim subscription ke backend |
| Footer template | `app/Views/template/footer.php` | Inject service worker URL dan `VAPID_PUBLIC_KEY` |
| Preferensi email user | `app/Controllers/Profil.php`, `app/Views/user/profil.php` | Toggle `email_notif_enabled` |

## Routes

Routes utama berada di `app/Config/Routes.php`.

```php
$routes->get('/getnotif', 'Notif::getNotif');
$routes->get('/loadnotif', 'Notif::loadNotif');
$routes->get('/notif/summary', 'Notif::getSummary');
$routes->get('/notif/center', 'Notif::getCenter');
$routes->post('/notif/snooze', 'Notif::snooze');
$routes->post('/notif/mark-as-read/(:num)', 'Notif::markAsRead/$1');

$routes->group('api/notif', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->post('push/subscribe', 'NotifPushController::subscribe');
    $routes->post('push/unsubscribe', 'NotifPushController::unsubscribe');
    $routes->get('stream', 'SseController::stream');
});
```

## Alur Notifikasi Aplikasi

Frontend navbar memakai polling, bukan SSE.

1. `public/assets/js/scripts.js` memanggil `/notif/summary` tiap 30 detik.
2. Saat dropdown notifikasi dibuka, frontend memanggil `/notif/center`.
3. Tombol atau scroll load more memanggil `/loadnotif`.
4. Klik item aktivitas memanggil `/notif/mark-as-read/{id}` lalu membuka modal/halaman terkait sesuai `type`.

`Notif::getCenter()` menggabungkan:

- urgent summary dari `SiteplanUrgentService`;
- activity list dari tabel `notification`;
- unread count activity;
- badge total.

Filter target group ada di `Notif::applyGroupTargetFilter()`. Admin group `1` melihat semua. Group lain melihat target group-nya, target global `0`, dan notifikasi user personal.

## Alur Tambah Notifikasi

Jalur baru yang direkomendasikan:

```php
$notifikasiService = new \App\Services\NotifikasiService();
$notifikasiService->tambah_notif($target, $message, $actorUserId, $idKavling, $idKonsumen, $type, $idProyek);
```

`NotifikasiService::tambah_notif()` akan:

1. insert row ke tabel `notification`;
2. insert row ke `notification_email_queue`;
3. trigger web push melalui `WebPushService`.

Untuk notifikasi personal gunakan:

```php
$notifikasiService->tambah_notif_user($targetUserId, $message, $actorUserId, $idKavling, $idKonsumen, $type, $idProyek);
```

## Email Digest

Email digest memakai command:

```bash
php spark notif:send-digest
```

Alur saat ini:

1. `EmailDigestService::processQueue()` mengambil semua queue dengan `status = pending`.
2. Queue dikelompokkan berdasarkan `target_group` dan `target_user_id`.
3. User target dicari dari `users`, `auth_groups_users`, dan preferensi `email_notif_enabled`.
4. Actor user tidak dikirimi notifikasi miliknya sendiri.
5. Email dikirim dengan template `app/Views/emails/email_digest.php`.
6. Queue ditandai `sent`.

Preferensi email user:

- kolom `users.email_notif_enabled`;
- default `1`;
- diubah dari halaman profil.

Konfigurasi email saat dicek:

- `app/Config/Email.php` masih `protocol = mail`;
- `SMTPHost` kosong;
- `SMTPPort = 25`;
- `.env` belum berisi konfigurasi SMTP email;
- uji lokal `php spark notif:send-digest` menghasilkan error koneksi ke `::1:1025`.

## Browser Push

Komponen push:

1. `app/Views/template/footer.php` inject `VAPID_PUBLIC_KEY`.
2. `public/assets/js/pwa-install.js` register service worker `public/sw.js`.
3. `public/assets/js/push-subscription.js` request permission dan subscribe PushManager.
4. Subscription dikirim ke `/api/notif/push/subscribe`.
5. Backend menyimpan subscription di `push_subscriptions`.
6. `NotifikasiService` memanggil `WebPushService` saat notifikasi dibuat.
7. `public/sw.js` menampilkan notification dari push event.

Saat dicek, `.env` sudah berisi:

- `VAPID_PUBLIC_KEY`;
- `VAPID_PRIVATE_KEY`;
- `VAPID_SUBJECT`.

## Tabel Terkait

### `notification`

Kolom penting:

- `id`
- `notif`
- `group_target`
- `type`
- `is_read`
- `id_proyek`
- `id_kavling`
- `id_konsumen`
- `created_at`
- `seen_by`
- `add_by`
- `user_id`

### `notification_email_queue`

Kolom penting:

- `id`
- `notification_id`
- `target_group`
- `target_user_id`
- `actor_user_id`
- `status`: `pending`, `sent`, `failed`
- `batch_id`
- `processed_at`
- `created_at`

### `push_subscriptions`

Kolom penting:

- `id`
- `user_id`
- `endpoint`
- `p256dh_key`
- `auth_token`
- `user_agent`
- `created_at`
- `updated_at`

## Status Terakhir Saat Dicek

Hasil cek lokal pada 2026-08-27:

- Syntax check file utama notifikasi: OK.
- `notification_email_queue`: 6 `pending`, 19 `sent`.
- `push_subscriptions`: 14 row.
- User aktif dengan email dan `email_notif_enabled = 1`: 18.
- Unread notification: 1661.
- `php spark routes` gagal saat scan controller `App\Controllers\Pusher`, bukan karena file notifikasi utama.

Catatan: uji `php spark notif:send-digest` sempat memproses 6 queue terbaru ke `sent` walau email gagal. Queue tersebut sudah dikembalikan ke `pending`.

## Known Issues

### 1. Email digest bisa menandai queue `sent` walau pengiriman gagal

Lokasi:

- `app/Services/EmailDigestService.php`

Masalah:

- Queue ditandai `sent` setelah loop target selesai.
- Tidak ada pencatatan `failed` per queue/user jika `send()` gagal.
- Output command bisa melaporkan email terkirim walau mailer error.

Rekomendasi:

- Mark `sent` hanya untuk queue yang benar-benar berhasil dikirim.
- Gunakan status `failed` dan simpan error/debug message jika memungkinkan.
- Tambahkan counter `sent`, `failed`, `skipped`.
- Jangan jadikan queue `sent` jika semua target user gagal.

### 2. Web push group multi-target belum aman

Lokasi:

- `app/Services/WebPushService.php`
- `app/Services/NotifikasiService.php`

Masalah:

- Banyak pemanggil memakai target seperti `"3;4;9"`.
- `WebPushService::sendToGroup()` melakukan `where('group_id', $groupId)`, sehingga string `"3;4;9"` tidak match.
- Target `"0"` atau global juga tidak terkirim karena `empty($groupId)` dianggap false condition untuk pengiriman.

Rekomendasi:

- Parse group target dengan `explode(';', $targetGroup)`.
- Untuk target `0`, kirim ke semua user aktif kecuali actor, atau definisikan perilaku global secara eksplisit.
- Deduplicate user sebelum kirim push.

### 3. Masih ada jalur yang memakai `NotifRepository`

Lokasi:

- `app/Repositories/NotifRepository.php`
- `app/Services/TransaksiService.php`
- `app/Services/KeuanganService.php`
- `app/Services/PembayaranService.php`
- `app/Services/CashOutService.php`

Masalah:

- `NotifRepository::tambah_notif()` hanya insert tabel `notification`.
- Email queue dan web push tidak dibuat dari jalur ini.

Rekomendasi:

- Migrasikan pemanggil ke `NotifikasiService`.
- Jika repository tetap dipertahankan, repository sebaiknya hanya query/insert data, sedangkan side effect tetap dikelola service.

### 4. Konfigurasi email belum siap

Lokasi:

- `app/Config/Email.php`
- `.env`

Masalah:

- `protocol = mail`.
- `SMTPHost` kosong.
- Tidak ada konfigurasi SMTP di `.env`.
- Uji lokal mengarah ke `::1:1025` dan ditolak.

Rekomendasi:

- Tentukan mailer dev/production.
- Tambahkan konfigurasi email via `.env`.
- Hindari hardcode kredensial di `app/Config/Email.php`.
- Tambahkan logging error pengiriman email.

### 5. SSE tersedia tetapi tidak dipakai frontend

Lokasi:

- `app/Controllers/Api/SseController.php`
- `public/assets/js/scripts.js`

Status:

- Route `/api/notif/stream` tersedia.
- Frontend memakai polling 30 detik, dengan catatan di JS bahwa SSE diganti polling karena freeze.

Rekomendasi:

- Pertahankan polling jika stabil.
- Jika SSE ingin dipakai lagi, uji session lock, timeout, proxy buffering, dan beban koneksi.

## Aturan Saat Mengubah Modul Ini

1. Update dokumen ini saat menambah route, tabel, kolom, notification type, atau side effect baru.
2. Pakai `NotifikasiService` untuk notifikasi yang perlu email queue dan push.
3. Hindari membuat notifikasi langsung via `NotifRepository` kecuali memang hanya butuh activity in-app.
4. Pastikan target group multi-value seperti `"3;4;9"` diuji untuk app, email, dan push.
5. Pastikan notifikasi personal memakai `user_id`, bukan hanya `group_target`.
6. Jangan mark queue email sebagai `sent` sebelum pengiriman benar-benar sukses.
7. Untuk fitur baru, uji minimal:
   - insert notification;
   - muncul di `/notif/summary`;
   - muncul di `/notif/center`;
   - mark as read;
   - queue email dibuat;
   - command digest menangani sukses/gagal;
   - push tidak error jika VAPID/subscription tersedia.

## Checklist Verifikasi Cepat

```bash
php -l app/Controllers/Notif.php
php -l app/Services/NotifikasiService.php
php -l app/Services/EmailDigestService.php
php -l app/Services/WebPushService.php
php -l app/Controllers/Api/NotifPushController.php
php -l app/Controllers/Api/SseController.php
php -l app/Commands/SendEmailDigest.php
```

Query status queue:

```sql
SELECT status, COUNT(*) AS total
FROM notification_email_queue
GROUP BY status;
```

Query subscription push:

```sql
SELECT COUNT(*) AS push_subscriptions
FROM push_subscriptions;
```

Query user email aktif:

```sql
SELECT COUNT(*) AS active_email_enabled
FROM users
WHERE active = 1
  AND deleted_at IS NULL
  AND email IS NOT NULL
  AND email <> ''
  AND email_notif_enabled = 1;
```

## Rekomendasi Refactor Berikutnya

Prioritas paling masuk akal:

1. Perbaiki `EmailDigestService` agar queue tidak salah `sent`.
2. Perbaiki `WebPushService::sendToGroup()` untuk target multi-group dan global.
3. Migrasikan pemanggil `NotifRepository` ke `NotifikasiService`.
4. Pindahkan konfigurasi email ke `.env`.
5. Tambahkan test kecil untuk target group parser dan status email queue.
