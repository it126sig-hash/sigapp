# Autentikasi dan Logout Perangkat

Dokumen ini menjelaskan registry sesi login SIGAPP dan mekanisme pencabutan per perangkat.

Terakhir dicek: 2026-10-01

## Ringkasan

Setiap login berhasil membuat satu sesi perangkat yang terpisah. Pemilik akun dapat melihat perangkat aktif, logout perangkat tertentu, atau logout perangkat lain sambil mempertahankan perangkat saat ini. Admin berizin dapat mencabut satu atau semua perangkat user dengan alasan audit.

Umur remember-me dan sesi tetap 365 hari. Pencabutan menghapus remember token terkait dan menandai registry perangkat sebagai dicabut; perangkat tersebut ditolak pada request berikutnya. Browser yang sedang terbuka tidak dapat dihentikan dari jarak jauh sebelum mengirim request berikutnya, tetapi tidak dapat memperpanjang atau memulihkan sesi yang dicabut.

## Komponen

| Area | File | Peran |
| --- | --- | --- |
| Migration | `app/Database/Migrations/2026-09-30-000001_CreateAuthDeviceSessions.php` | Registry perangkat, permission admin, dan link push subscription |
| Registry | `app/Models/DeviceSessionModel.php`, `app/Repositories/DeviceSessionRepository.php` | Hash token, status aktif, rotasi, audit pencabutan, hapus remember selector |
| Service | `app/Services/DeviceSessionService.php`, `app/Services/DeviceManagementService.php` | Validasi sesi dan kebijakan self-service/admin |
| Login/logout | `app/Controllers/Web/DeviceAuthController.php` | Mengganti endpoint Myth Auth agar login/logout mencatat perangkat |
| Filter | `app/Filters/DeviceLoginFilter.php` | Menegakkan keaktifan sesi perangkat pada request login-required |
| Self-service API | `app/Controllers/Api/AuthDeviceController.php` | Daftar, cabut satu perangkat, atau cabut perangkat lain |
| Admin API | `app/Controllers/Api/AdminAuthDeviceController.php` | Daftar dan pencabutan sesi user dengan audit reason |
| Parser | `app/Services/UserAgentDeviceParser.php` | Label, browser, dan platform dari user-agent |
| UI user | `app/Views/user/profil.php`, `public/assets/js/auth-devices.js` | Tab Perangkat Login pada profil |
| UI admin | `app/Views/user/setuser.php`, `public/assets/js/admin-auth-devices.js` | Dialog sesi perangkat pada Data Pengguna |

## Penyimpanan dan siklus hidup

`auth_device_sessions` menyimpan `token_hash` SHA-256, user, ringkasan user-agent, IP, waktu dibuat/terakhir aktif/kedaluwarsa, remember selector, serta metadata pencabutan. Cookie `sigapp_device` berisi token acak mentah; token mentah tidak disimpan di database. Cookie memakai HttpOnly dan SameSite=Lax serta mengikuti domain/path aplikasi. Atribut Secure aktif bila `Config\\App::$cookieSecure` aktif atau request HTTPS; CI4 menolak cookie Secure pada request HTTP.

Saat autentikasi remember-me Myth Auth merotasi selector, registry diperbarui bersamaan dan selector sebelumnya dihapus. Filter mengecek cookie, user login, registry yang belum dicabut/kedaluwarsa, dan remember selector. `last_seen_at` serta masa berlaku diperpanjang paling sering sekali tiap lima menit.

Logout satu perangkat menghapus remember token untuk selector perangkat tersebut. `POST /api/auth/devices/{id}/revoke` dan logout endpoint sendiri memerlukan password. `POST /api/auth/devices/revoke-others` mempertahankan perangkat aktif saat ini. Pada revoke perangkat aktif saat ini, sesi dan cookie login dibersihkan.

Pencabutan admin memerlukan permission `auth.devices.manage` dan alasan maksimal 255 karakter yang disimpan pada `revoke_reason` bersama `revoked_by`. Migration memberikan permission ini ke group admin ID 1 hanya jika grant belum tersedia; rollback hanya menghapus permission/grant yang dibuat migration.

## API

Semua endpoint berikut memerlukan login; mutasi juga dilindungi CSRF.

| Method | Endpoint | Kebijakan |
| --- | --- | --- |
| `GET` | `/api/auth/devices` | Daftar perangkat aktif user saat ini; menandai perangkat saat ini |
| `POST` | `/api/auth/devices/{id}/revoke` | Password wajib; dapat mencabut perangkat saat ini |
| `POST` | `/api/auth/devices/revoke-others` | Password wajib; mempertahankan perangkat saat ini |
| `GET` | `/api/admin/users/{userId}/devices` | Permission `auth.devices.manage` wajib |
| `POST` | `/api/admin/users/{userId}/devices/{id}/revoke` | Permission dan alasan audit wajib |
| `POST` | `/api/admin/users/{userId}/devices/revoke-all` | Permission dan alasan audit wajib |

## Web Push

`push_subscriptions.device_session_id` menghubungkan subscription dengan sesi browser yang membuatnya. Subscribe membaca ID sesi dari session login. Query pengiriman hanya mengembalikan subscription yang masih aktif dan mempunyai registry perangkat aktif yang sama user-nya. Row subscription lama yang `device_session_id`-nya kosong dikecualikan setelah migration.

Saat self-service/admin/logout mencabut sesi, subscription terkait di-set `disabled_at` pada transaksi pencabutan yang sama. Detail pipeline dan perilaku fallback didokumentasikan di [`docs/modul-notifikasi.md`](modul-notifikasi.md).

## Cutover dan batasan

- Sesi lama yang tidak memiliki cookie/registry perangkat tidak diterima oleh filter setelah deployment dan harus login kembali satu kali.
- Keep remember-me 365 hari dan masa sesi CI4 365 hari; fitur ini bukan pengurangan masa berlaku global.
- Revoke menutup akses pada request berikutnya; tidak ada kanal untuk menginterupsi proses atau halaman yang sudah berjalan di browser remote.
- Informasi device berasal dari user-agent dan dapat tidak akurat; IP adalah alamat yang terlihat oleh aplikasi/proxy.
- Pastikan proxy mengirim alamat client/protokol yang benar dan HTTPS aktif di produksi agar cookie device dikirim dengan atribut Secure.
- Verifikasi query locking transaksi pada database produksi MySQL/PostgreSQL dan verifikasi perilaku browser saat rollout sebelum menganggap deployment selesai.
