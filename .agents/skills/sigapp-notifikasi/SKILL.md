---
name: sigapp-notifikasi
description: >
  Use this skill when changing the SIGAPP notification module, including in-app navbar notifications,
  urgent notification center, email digest queue, browser web push, notification routes, notification tables,
  or any code that calls tambah_notif. Always update docs/modul-notifikasi.md when the module behavior changes.
---

# SIGAPP Modul Notifikasi

Gunakan skill ini setiap kali ada perubahan pada modul notifikasi SIGAPP: notifikasi aplikasi, urgent center, email digest, web push, SSE/polling, tabel notifikasi, atau pemanggil `tambah_notif`.

## Referensi Wajib

Sebelum mengubah kode notifikasi, baca `docs/modul-notifikasi.md`. Dokumen itu adalah acuan utama arsitektur, file terkait, known issues, dan checklist verifikasi. Jika perubahan mengubah perilaku, route, tabel, konfigurasi, notification type, atau side effect, update dokumen itu dalam perubahan yang sama.

Jika perubahan menyentuh controller, service, repository, model, migration, validation, atau endpoint CodeIgniter 4, gunakan juga skill `codeigniter4` dan ikuti pola Thin Controller -> Service -> Repository.

## File yang Biasanya Terkait

- `app/Controllers/Notif.php`
- `app/Services/NotifikasiService.php`
- `app/Repositories/NotifRepository.php`
- `app/Services/EmailDigestService.php`
- `app/Commands/SendEmailDigest.php`
- `app/Services/WebPushService.php`
- `app/Controllers/Api/NotifPushController.php`
- `app/Controllers/Api/SseController.php`
- `app/Services/SiteplanUrgentService.php`
- `app/Config/Routes.php`
- `app/Config/Email.php`
- `app/Views/emails/email_digest.php`
- `app/Views/template/footer.php`
- `app/Views/template/generate_menu.php`
- `public/assets/js/scripts.js`
- `public/assets/js/push-subscription.js`
- `public/assets/js/pwa-install.js`
- `public/sw.js`
- migrations terkait `notification`, `notification_email_queue`, `notification_snoozes`, `push_subscriptions`, dan `users.email_notif_enabled`

## Aturan Implementasi

- Jadikan `App\Services\NotifikasiService` sebagai jalur utama untuk notifikasi yang harus membuat in-app notification, email queue, dan web push.
- Jangan menambah pemanggilan baru ke `NotifRepository::tambah_notif()` kecuali memang sengaja hanya membuat activity in-app tanpa email/push. Jelaskan alasan itu di kode atau final response jika relevan.
- Untuk notification personal, gunakan jalur `user_id` atau `tambah_notif_user()`, bukan memaksa target group.
- Perlakukan target group multi-value seperti `"3;4;9"` sebagai daftar group, bukan satu group literal.
- Definisikan perilaku target global `"0"` secara eksplisit untuk app, email, dan push.
- Jangan menandai email queue sebagai `sent` sebelum pengiriman benar-benar berhasil. Untuk kegagalan, gunakan status `failed` atau biarkan `pending` sesuai desain retry.
- Jangan membuat command digest melaporkan sukses jika mailer gagal. Laporkan jumlah `sent`, `failed`, dan `skipped` jika mengubah area email.
- Jangan menyimpan kredensial email di kode. Konfigurasi SMTP sebaiknya dari `.env`.
- Jika mengubah polling/SSE, perhatikan session lock, timeout request, proxy buffering, dan dampak freeze pada UI.

## Checklist Saat Ada Perubahan

Pastikan perubahan yang relevan diuji di semua jalur yang terdampak:

- notification row dibuat di tabel `notification`;
- badge `/notif/summary` berubah sesuai ekspektasi;
- `/notif/center` menampilkan item yang benar;
- load more `/loadnotif` tetap jalan;
- mark as read `/notif/mark-as-read/{id}` mengubah `is_read`;
- target group dan target user difilter dengan benar;
- `notification_email_queue` dibuat jika notifikasi perlu email;
- `php spark notif:send-digest` menangani sukses dan gagal dengan benar;
- web push tidak error saat VAPID key dan subscription tersedia;
- service worker tetap menampilkan notification payload yang benar;
- `docs/modul-notifikasi.md` sudah diperbarui jika perilaku berubah.

## Verifikasi Cepat

Jalankan syntax check minimal untuk file yang disentuh. Untuk perubahan luas, mulai dari:

```bash
php -l app/Controllers/Notif.php
php -l app/Services/NotifikasiService.php
php -l app/Services/EmailDigestService.php
php -l app/Services/WebPushService.php
php -l app/Controllers/Api/NotifPushController.php
php -l app/Controllers/Api/SseController.php
php -l app/Commands/SendEmailDigest.php
```

Gunakan query status queue saat area email berubah:

```sql
SELECT status, COUNT(*) AS total
FROM notification_email_queue
GROUP BY status;
```

Gunakan query subscription saat area push berubah:

```sql
SELECT COUNT(*) AS push_subscriptions
FROM push_subscriptions;
```

## Prioritas Refactor yang Perlu Diingat

Known issues dari audit 2026-08-27:

- `EmailDigestService` masih bisa menandai queue `sent` walau pengiriman gagal.
- `WebPushService::sendToGroup()` belum aman untuk target multi-group seperti `"3;4;9"` dan target global `"0"`.
- Beberapa service masih memakai `NotifRepository`, sehingga notifikasi dari jalur itu tidak membuat email queue dan tidak trigger web push.
- Konfigurasi email belum siap produksi dan belum berbasis `.env`.
- SSE endpoint tersedia, tetapi frontend sengaja memakai polling karena SSE pernah menyebabkan freeze.
