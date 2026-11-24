# Module: Copilot

**Status:** `MAPPED`  
**Readiness Score:** `90/100`  
**Last Synced:** `2026-11-24 21:30:22` @ `3e64c8e`  

## Purpose
AI Copilot internal SIGAPP lintas channel (Tab Web & Bot Telegram) berbasis LLM Provider (Gemini & OpenAI-Compatible) dengan tool calling read-only, RBAC per tool, dan proteksi PII berlapis.

## Out of scope
- Modul `Copilot` bersifat **read-only** terhadap seluruh data bisnis SIGAPP (`keuangan`, `mkdt`, `konsumen`, `kavling`, `proyek`); tidak melakukan mutasi tagihan, pembayaran, booking, maupun data master proyek.
- Tidak mengekspose API key LLM (`GEMINI_API_KEY`, `OPENAI_API_KEY`, dll.) atau secret Telegram (`TELEGRAM_BOT_TOKEN`, `TELEGRAM_WEBHOOK_SECRET`) ke database maupun respons HTTP/frontend; seluruh kredensial tetap dikelola di `.env`.
- Tidak mengizinkan akses data konfidensial/PII sensitif (`nik`, `npwp`, `ktp`, `kk`, `hp_*`, `alamat_*`, `email`, `rekening`, `gaji`, `pekerjaan`, `instansi`, `file_*`, `keterangan`, `void_reason`) pada payload tool, prompt LLM, maupun jawaban Copilot.

## Invariants
- **Proteksi PII Berlapis:** `JatuhTempoRepository` menggunakan `SELECT` whitelist ketat tanpa kolom konfidensial; setiap output tool wajib melewati `PiiGuard::assertSafePayload()`, dan input/output chat disensor melalui `PiiGuard::redactText()`.
- **Session-Free RBAC:** `CopilotAuthorizer` memverifikasi izin menu (`copilot`), izin per-tool (`get_jatuh_tempo_cicilan` khusus grup `1, 3, 4, 8, 9`; `get_booking_per_bulan` & `get_konversi_booking_akad` semua grup aktif), serta cakupan proyek (`ProyekRepository::getAccessibleForUser()`) murni berbasis `userId` sehingga konsisten lintas Web, Telegram Webhook, dan CLI.
- **Anti-Injection & Boundary Tool:** `CopilotOrchestrator` membatasi panjang pesan maksimal 2000 karakter, rate limit 20 pesan per 10 menit lintas channel (`copilot_messages`), maksimal 4 iterasi tool calling per pesan, dan mencatat setiap eksekusi tool serta perubahan pengaturan ke `AuditTrail` (`HistoryService::log('copilot', ...)`).
- **Keamanan Webhook Telegram:** `CopilotTelegramController::webhook()` wajib memverifikasi header `X-Telegram-Bot-Api-Secret-Token` dengan `hash_equals()`, menolak chat non-`private`, melakukan deduplikasi berdasarkan `telegram_update_id`, dan membalas HTTP `200` untuk payload valid agar Telegram tidak melakukan retry berulang.
- **Zero Controller Layer Violations:** Seluruh Web & API Controller (`Web\Copilot\CopilotController`, `Api\Copilot\*`, `Api\Public\CopilotTelegramController`) tidak mengakses query DB langsung dan mendelegasikan seluruh logika ke Service layer.

## Database Tables
### Owner Table(s)
- `copilot_settings` *(akses tulis eksklusif hanya untuk modul Copilot)*
- `copilot_telegram_links` *(akses tulis eksklusif hanya untuk modul Copilot)*
- `copilot_messages` *(akses tulis eksklusif hanya untuk modul Copilot)*

### Shared Tables (Dibaca dari modul lain)
- `keuangan`
- `mkdt`
- `kavling`
- `jalan`
- `cluster`
- `proyek`
- `konsumen`
- `users`
- `auth_groups_users`
- `menus`
- `menu_roles`
- `menu_user_access`
- `history_log`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/copilot` | `Explicit` | `\App\Controllers\Web\Copilot\CopilotController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/copilot/meta` | `Explicit` | `ChatController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/copilot/history` | `Explicit` | `ChatController::history` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/copilot/chat` | `Explicit` | `ChatController::chat` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/copilot/settings` | `Explicit` | `SettingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/copilot/settings` | `Explicit` | `SettingController::update` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/copilot/telegram/link` | `Explicit` | `TelegramLinkController::create` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `DELETE` | `/api/copilot/telegram/link` | `Explicit` | `TelegramLinkController::delete` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/public/copilot/telegram/webhook` | `Explicit` | `CopilotTelegramController::webhook` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `CopilotOrchestrator.php`
- `handleUserMessage(int $userId, string $rawMessage, string $channel = 'web', ?int $telegramUpdateId = null, array $providerOverrides = [])`
- `getMetaForUser(int $userId)`
- `getChatHistory(int $userId, string $channel = 'web', int $limit = 30)`
- `getSettingsForAdmin(int $userId)`
- `getProviderConfigSummary()`
- `updateProviderSettings(int $userId, string $provider, string $model, ?string $baseUrl = null)`
#### `CopilotAuthorizer.php`
- `getUserGroupIds(int $userId)`
- `canAccessCopilot(int $userId)`
- `canUseTool(int $userId, string $toolName)`
- `getAccessibleProjects(int $userId)`
- `resolveTargetProject(int $userId, ?string $projectName = null, ?int $projectId = null, bool $allowMultiDefault = false)`
#### `TelegramService.php`
- `getBotToken()`
- `getWebhookSecret()`
- `generateLinkCode(int $userId)`
- `unlink(int $userId)`
- `handleWebhookUpdate(array $update)`
- `setWebhook(string $url, string $secretToken)`
- `deleteWebhook(bool $dropPending = false)`
- `getUpdates(int $offset = 0, int $timeout = 20)`
- `sendMessage(int $chatId, string $text)`
- `sendChatAction(int $chatId, string $action = 'typing')`
- `splitMessageChunks(string $text, int $maxLen = self::TELEGRAM_MAX_MESSAGE_LENGTH)`
- `hashCode(string $code)`
#### `ToolRegistry.php`
- `register(CopilotToolInterface $tool)`
- `getAllowedToolNamesForUser(int $userId)`
- `getDeclarationsForUser(int $userId)`
- `execute(int $userId, string $toolName, array $arguments = [])`
#### `PiiGuard.php`
- `assertSafePayload(array $payload)`
- `isForbiddenKey(string $key)`
- `redactText(string $text)`
#### `LlmProviderFactory.php`
- `make(?array $overrides = null)`
#### `LlmProviderInterface.php`
- `chat(array $messages, array $tools = [], array $options = [])`
- `getProviderName()`
- `getModelName()`
#### `GeminiProvider.php`
- `getProviderName()`
- `getModelName()`
- `chat(array $messages, array $tools = [], array $options = [])`
#### `OpenAiCompatibleProvider.php`
- `getProviderName()`
- `getModelName()`
- `chat(array $messages, array $tools = [], array $options = [])`
#### `LlmResponse.php`
- `hasToolCalls()`
#### `LlmProviderException.php`
- _Tidak ada public method terdeteksi._
#### `CopilotToolInterface.php`
- `getName()`
- `getDescription()`
- `getDeclaration()`
- `execute(int $userId, array $arguments)`
#### `JatuhTempoCicilanTool.php`
- `getName()`
- `getDescription()`
- `getDeclaration()`
- `execute(int $userId, array $arguments)`
#### `BookingPerBulanTool.php`
- `getName()`
- `getDescription()`
- `getDeclaration()`
- `execute(int $userId, array $arguments)`
#### `KonversiBookingAkadTool.php`
- `getName()`
- `getDescription()`
- `getDeclaration()`
- `execute(int $userId, array $arguments)`

## File Inventory
### Controllers (5)
- `app/Controllers/Web/Copilot/CopilotController.php` (41 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Copilot/ChatController.php` (117 baris, 3 public methods: `meta()`, `history()`, `chat()`)
- `app/Controllers/Api/Copilot/SettingController.php` (79 baris, 2 public methods: `index()`, `update($id = null)`)
- `app/Controllers/Api/Copilot/TelegramLinkController.php` (74 baris, 2 public methods: `create()`, `delete($id = null)`)
- `app/Controllers/Api/Public/CopilotTelegramController.php` (69 baris, 1 public methods: `webhook()`)

### Services (15)
- `app/Services/Copilot/CopilotOrchestrator.php` (514 baris, 6 public methods)
- `app/Services/Copilot/CopilotAuthorizer.php` (245 baris, 5 public methods)
- `app/Services/Copilot/TelegramService.php` (570 baris, 12 public methods)
- `app/Services/Copilot/ToolRegistry.php` (108 baris, 4 public methods)
- `app/Services/Copilot/PiiGuard.php` (189 baris, 3 public methods)
- `app/Services/Copilot/LlmProviderFactory.php` (126 baris, 1 public methods)
- `app/Services/Copilot/LlmProviderInterface.php` (22 baris, 3 public methods)
- `app/Services/Copilot/GeminiProvider.php` (294 baris, 3 public methods)
- `app/Services/Copilot/OpenAiCompatibleProvider.php` (248 baris, 3 public methods)
- `app/Services/Copilot/LlmResponse.php` (26 baris, 1 public methods)
- `app/Services/Copilot/LlmProviderException.php` (10 baris, 0 public methods)
- `app/Services/Copilot/Tools/CopilotToolInterface.php` (26 baris, 4 public methods)
- `app/Services/Copilot/Tools/JatuhTempoCicilanTool.php` (117 baris, 4 public methods)
- `app/Services/Copilot/Tools/BookingPerBulanTool.php` (121 baris, 4 public methods)
- `app/Services/Copilot/Tools/KonversiBookingAkadTool.php` (133 baris, 4 public methods)

### Repositories (4)
- `app/Repositories/Copilot/SettingRepository.php` (111 baris, 3 public methods: `getAll()`, `get(string $key, ?string $default = null)`, `upsertMany(array $pairs, ?int $userId = null)`)
- `app/Repositories/Copilot/JatuhTempoRepository.php` (194 baris, 1 public methods: `getJatuhTempo(array $allowedProjectIds, int $days = self::DEFAULT_DAYS, int $limit = self::DEFAULT_LIMIT, ?DateTimeImmutable $today = null, string $kategori = 'semua')`)
- `app/Repositories/Copilot/MessageRepository.php` (147 baris, 5 public methods: `insertMessage(int $userId, string $channel, string $role, string $content, ?int $telegramUpdateId = null, ?array $metadata = null)`, `getRecentHistory(int $userId, string $channel, int $limit = 10)`, `countRecentUserMessages(int $userId, int $windowMinutes = 10)`, `hasTelegramUpdateId(int $updateId)`, `pruneOlderThanDays(int $days = 90)`)
- `app/Repositories/Copilot/TelegramLinkRepository.php` (254 baris, 5 public methods: `findByUserId(int $userId)`, `findVerifiedByTelegramUserId(int $telegramUserId)`, `savePairingCode(int $userId, string $codeHash, string $expiresAt)`, `verifyAndBindByCodeHash(string $codeHash, int $telegramUserId, int $chatId, ?string $telegramUsername, string $now)`, `unlinkByUserId(int $userId)`)

### Models (0)

### Views (1 folder)
- `app/Views/copilot` (1 file: `index.php`)

### JavaScript (1 file)
- `public/assets/js/copilot.js` (1198 baris)

## Key Functions
- **Web Controller (`App\Controllers\Web\Copilot\CopilotController`):**
  - `index()`: Memverifikasi akses menu `copilot` via `MenuAccessService::canAccessUrl()` dan merender view `copilot/index` dalam layout `template`.
- **API Controllers (`App\Controllers\Api\Copilot\*` & `App\Controllers\Api\Public\CopilotTelegramController`):**
  - `ChatController::meta()`: Mengembalikan metadata akses user, daftar tool yang diizinkan, proyek yang dapat diakses, ringkasan konfigurasi provider aktif (tanpa API key), status penautan Telegram, dan kebijakan rate limit/retensi.
  - `ChatController::history()`: Mengambil riwayat percakapan channel `web` milik user login (maksimal 100 pesan).
  - `ChatController::chat()`: Meneruskan pesan user ke `CopilotOrchestrator::handleUserMessage($userId, $message, 'web')` dan mengembalikan jawaban LLM (`502` jika `LlmProviderException`).
  - `SettingController::index()` & `SettingController::update($id = null)`: Khusus Admin (grup `1`) untuk membaca dan memperbarui `provider`, `model`, dan `base_url` aktif di `copilot_settings`.
  - `TelegramLinkController::create()` & `delete($id = null)`: Membuat kode pairing Telegram 6 digit (TTL 10 menit) dan memutuskan tautan akun Telegram user login.
  - `CopilotTelegramController::webhook()`: Endpoint publik (`throttle:60,60`) yang memvalidasi `X-Telegram-Bot-Api-Secret-Token` (`hash_equals`) dan mendelegasikan object `Update` ke `TelegramService::handleWebhookUpdate()`.
- **Services (`App\Services\Copilot\*` & `App\Services\Copilot\Tools\*`):**
  - `CopilotOrchestrator::handleUserMessage(...)`: Orkestrasi pipeline lengkap (RBAC, validasi panjang 2000 karakter, rate limit 20 pesan/10 menit, redaksi PII input/output, history 10 pesan terakhir, loop tool calling maks 4 iterasi, audit `HistoryService::log('copilot', ...)`, dan persistensi ke `copilot_messages`).
  - `CopilotAuthorizer::canAccessCopilot()`, `canUseTool()`, `getAccessibleProjects()`, `resolveTargetProject()`: RBAC session-free (`get_jatuh_tempo_cicilan` khusus grup `1, 3, 4, 8, 9`; tool analitik MKDT semua grup aktif) dan resolusi proyek target.
  - `PiiGuard::assertSafePayload()`, `isForbiddenKey()`, `redactText()`: Blokir key PII (`nik`, `ktp`, `kk`, `npwp`, `hp_*`, `alamat`, `email`, `rekening`, `file_*`, `keterangan`, `void_reason`, dll.) dan sensor pola angka sensitif pada teks.
  - `LlmProviderFactory::make(?array $overrides = null)`: Resolusi provider aktif dari `Config\Copilot` + `copilot_settings` + API key `.env` (`GeminiProvider` atau `OpenAiCompatibleProvider`).
  - `GeminiProvider::chat()` & `OpenAiCompatibleProvider::chat()`: Adapter HTTP LLM dengan timeout <= 20 detik serta normalisasi respons & function/tool calls (`LlmResponse`).
  - `ToolRegistry::getDeclarationsForUser()`, `execute()`: Menyaring deklarasi tool sesuai RBAC dan memvalidasi hasil tool melalui `PiiGuard`.
  - `JatuhTempoCicilanTool::execute()`: Mengambil daftar cicilan terlambat dan akan jatuh tempo via `JatuhTempoRepository`.
  - `BookingPerBulanTool::execute()` & `KonversiBookingAkadTool::execute()`: Mengonsumsi `App\Services\Mkdt\BookingAkadReportService::getSummary()` secara agregat tanpa data personal.
  - `TelegramService::generateLinkCode()`, `handleWebhookUpdate()`, `setWebhook()`, `deleteWebhook()`, `getUpdates()`: Pengelolaan pairing 6 digit HMAC-SHA256, pemrosesan perintah `/start`, `/link`, `/unlink`, `/help`, deduplikasi `telegram_update_id`, guard chat `private`, dan pemecahan pesan panjang.
- **Repositories (`App\Repositories\Copilot\*`):**
  - `JatuhTempoRepository::getJatuhTempo(...)`: Query read-only `SELECT` whitelist atas `keuangan` join `mkdt`, `kavling`, `jalan`, `cluster`, `proyek`, `konsumen`; mengecualikan `is_batal = 1`, `status_mkdt = 'Batal'`, `is_void = 1`, `sudah_dibayar = 1`, `is_lunas = 1`; memisahkan kategori `terlambat` dan `akan_jatuh_tempo`.
  - `MessageRepository`: Penyimpanan riwayat chat (`insertMessage`), pembacaan riwayat (`getRecentHistory`), hitung kuota rate limit (`countRecentUserMessages`), cek deduplikasi (`hasTelegramUpdateId`), dan retensi (`pruneOlderThanDays`).
  - `TelegramLinkRepository`: Manajemen kode pairing dan tautan user (`findByUserId`, `findVerifiedByTelegramUserId`, `savePairingCode`, `verifyAndBindByCodeHash` dalam transaksi DB, `unlinkByUserId`).
  - `SettingRepository`: Pembacaan & upsert key whitelist (`provider`, `model`, `base_url`) di `copilot_settings`.
- **Frontend (`app/Views/copilot/index.php`, `public/assets/css/copilot.css`, `public/assets/js/copilot.js`):**
  - UI Pola A `.copilot-page` dengan tab mobile horizontal scrollable, bubble chat anti-XSS (DOM `createTextNode`), kartu status & pairing Telegram, panel Admin LLM provider settings, serta cache-busting `filemtime()`.

## Deprecated & Legacy Shims
- *(Tidak ada fungsi, route, atau class shim yang berstatus deprecated saat ini).*

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Copilot` untuk mengaudit dead code kandidat._

## Notes
- **File Pendukung di Luar Layer MVC Standar:**
  - **Config:** `app/Config/Copilot.php` (108 baris; **catatan `.gitignore`:** pola `/app/Config/*` di `.gitignore` mengabaikan file config kecuali `Filters.php` dan `Routes.php`, sehingga perlu pengecualian/force-add bila ikut di-commit).
  - **Migrations:** `app/Database/Migrations/2026-11-25-000002_CreateCopilotTables.php` (150 baris: `copilot_settings`, `copilot_telegram_links`, `copilot_messages`) dan `app/Database/Migrations/2026-11-25-000003_AddCopilotMenu.php` (94 baris: menu ID `53` `AI Copilot`, seluruh grup `auth_groups`).
  - **CLI Commands (`app/Commands/`):** `CopilotAsk.php` (`copilot:ask`), `CopilotTelegramWebhook.php` (`copilot:telegram-webhook`), `CopilotTelegramPoll.php` (`copilot:telegram-poll`), `CopilotPrune.php` (`copilot:prune`).
  - **CSS Asset:** `public/assets/css/copilot.css` (672 baris, scoped `.copilot-page`, dark mode & mobile responsive).
  - **Unit Tests (`tests/unit/Copilot/`):** `LlmProviderTest.php` (256 baris), `ToolsAndSecurityTest.php` (329 baris), `OrchestratorAndApiTest.php` (466 baris), `TelegramIntegrationTest.php` (513 baris) — total 17 test, 389 assertions PASS.
- **Cross-Module Dependencies (Layanan Modul Lain yang Dikonsumsi Copilot):**
  - `MKDT` (`App\Services\Mkdt\BookingAkadReportService::getSummary`): dikonsumsi oleh `BookingPerBulanTool` dan `KonversiBookingAkadTool`.
  - `Proyek` (`App\Repositories\Proyek\ProyekRepository::getAccessibleForUser`): dikonsumsi oleh `CopilotAuthorizer`.
  - `Settings` (`App\Services\Settings\MenuAccessService::getUserGroupIds`, `canAccessUrl`): dikonsumsi oleh `CopilotAuthorizer` dan `Web\Copilot\CopilotController`.
  - `AuditTrail` (`App\Services\AuditTrail\HistoryService::log`): dikonsumsi oleh `CopilotOrchestrator`.
- **Cross-Module Callers (Pemanggil Lintas Modul terhadap Copilot):**
  - `AuditTrail` (`app/Services/AuditTrail/HistoryService.php`): mendaftarkan `MODULE_LABELS['copilot'] = 'AI Copilot'`.
  - Sidebar Menu (`menus` / `menu_roles` -> `/copilot`) & CLI Spark Commands (`app/Commands/Copilot*.php`).
  - Webhook eksternal Telegram Bot API (`POST /api/public/copilot/telegram/webhook`).
- **Catatan `ModuleScanner`:** `app/Controllers/Api/Public/CopilotTelegramController.php` berada pada sub-namespace `Api\Public` sesuai konvensi endpoint publik ber-throttle SIGAPP, sehingga `isLayerModular('Controllers', 'Copilot', ...)` menandai checklist `Web & API Controllers Separated` sebagai belum penuh (`Status: MAPPED`, `Readiness Score: 90/100`).

## Change Log
- `2026-11-24` [`FEATURE`] — **Implementasi AI Copilot (Gemini & OpenAI-Compatible, Tab Web `/copilot`, dan Bot Telegram) (PASS QA Putaran 2, 17/17 Unit Test PASS)**:
  - **Routes & Public Contracts:** Menambahkan 9 rute eksplisit (`GET /copilot`, `GET /api/copilot/meta`, `GET /api/copilot/history`, `POST /api/copilot/chat`, `GET /api/copilot/settings`, `POST /api/copilot/settings`, `POST /api/copilot/telegram/link`, `DELETE /api/copilot/telegram/link`, `POST /api/public/copilot/telegram/webhook`).
  - **Controller / Service / Repo / Config / CLI:** Menambahkan `Config\Copilot`, migrasi `CreateCopilotTables` & `AddCopilotMenu`, 5 Controller (`Web\Copilot\CopilotController`, `Api\Copilot\{ChatController,SettingController,TelegramLinkController}`, `Api\Public\CopilotTelegramController`), 15 file Service & Tool (`CopilotOrchestrator`, `CopilotAuthorizer`, `TelegramService`, `ToolRegistry`, `PiiGuard`, `LlmProviderFactory`, `GeminiProvider`, `OpenAiCompatibleProvider`, `LlmResponse`, `LlmProviderException`, serta 3 tool read-only), 4 Repository (`SettingRepository`, `JatuhTempoRepository`, `MessageRepository`, `TelegramLinkRepository`), 4 Spark Command (`copilot:ask`, `copilot:telegram-webhook`, `copilot:telegram-poll`, `copilot:prune`), dan label audit `HistoryService::MODULE_LABELS['copilot']`.
  - **View / CSS / JS:** Menambahkan `app/Views/copilot/index.php`, `public/assets/css/copilot.css`, dan `public/assets/js/copilot.js` (Pola A, ikon `fas fa-paper-plane` tanpa `fa-brands`, render DOM anti-XSS, pairing Telegram, panel Admin settings).
  - **Deprecations:** Tidak ada.
- `2026-11-24` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.
