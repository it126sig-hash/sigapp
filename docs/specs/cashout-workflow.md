# Desain Alur Pencairan CashOut Multi-Departemen

## Konteks
Cashout saat ini hanya mencatat pembayaran biaya per kavling (PPH, BPHTB, dll) langsung — tidak ada alur *pengajuan → approval → pencairan*. Rencana ini menambahkan **lifecycle/workflow** pengajuan pencairan dana dari berbagai departemen (Pajak, Legal, Produksi, MKDT) ke departemen Keuangan.

## Persyaratan Utama
1. **Satu Level Proses**: Saat ini hanya ada 1 level di keuangan saja. Hanya perlu tahu kalau keuangan sudah mengajukan/memproses dana tersebut.
2. **Konteks Kavling**: Semua pengeluaran untuk saat ini terkait pada kavling (di luar biaya overhead).
3. **Nominal Terpisah**: Nominal yang diajukan dan nominal yang cair dipisah.
4. **Pengembalian Dana**: Alur tambahan untuk pengembalian dana jika ada dana sisa/harus dikembalikan ke keuangan. Departemen terkait mengirim dana dan keuangan mengiyakan/menolak.

---

## Skema Database

### 1. Tabel `pengajuan_pencairan`

Tabel terpusat untuk menampung pengajuan dari semua departemen.

```sql
CREATE TABLE pengajuan_pencairan (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_pengajuan      VARCHAR(20) NOT NULL UNIQUE,     -- e.g. PCR-2024-001

    -- Pengaju
    departemen_asal     ENUM('pajak','legal','produksi','mkdt') NOT NULL,
    id_user_pengaju     INT NOT NULL,

    -- Selalu terkait kavling
    id_kavling          VARCHAR(20) NOT NULL,
    jenis_biaya         VARCHAR(50) NOT NULL,            -- 'pph','ppn','bphtb', dll
    id_referensi        INT NULL,                        -- FK ke tabel pajak/legal/dll (opsional)

    -- Detail pengajuan
    keperluan           VARCHAR(255) NOT NULL,
    nominal_diajukan    DECIMAL(15,2) NOT NULL,          -- yang diminta dept
    tanggal_pengajuan   DATE NOT NULL,

    -- Status sederhana (1 level - keuangan saja)
    status              ENUM('diajukan','diproses','cair','ditolak') DEFAULT 'diajukan',
    -- 'diajukan'  = dept sudah submit
    -- 'diproses'  = keuangan sedang proses (sudah forward/urus)
    -- 'cair'      = dana sudah keluar
    -- 'ditolak'   = ditolak keuangan

    id_user_keuangan    INT NULL,                        -- siapa di keuangan yg handle
    tanggal_diproses    DATETIME NULL,
    catatan_keuangan    TEXT NULL,

    -- Pencairan (nominal bisa beda dari diajukan)
    nominal_cair        DECIMAL(15,2) NULL,              -- realisasi yang cair
    tanggal_cair        DATETIME NULL,

    -- Rekening tujuan
    tujuan_pencairan    ENUM('rekening_dept','langsung') NULL,
    nama_penerima       VARCHAR(100) NULL,
    no_rekening         VARCHAR(50) NULL,
    nama_bank           VARCHAR(50) NULL,
    bukti_pencairan     VARCHAR(255) NULL,

    catatan             TEXT NULL,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_kavling (id_kavling),
    INDEX idx_status (status),
    INDEX idx_dept (departemen_asal)
);
```

### 2. Tabel `pengembalian_dana`

Tabel untuk mencatat pengembalian dana (misal dana sisa pencairan) dari departemen kembali ke Keuangan.

```sql
CREATE TABLE pengembalian_dana (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_pengembalian   VARCHAR(20) NOT NULL UNIQUE,     -- e.g. RTN-2024-001

    -- Referensi ke pencairan asal (opsional, jika pengembalian terkait pencairan tertentu)
    id_pengajuan        INT UNSIGNED NULL,
    FOREIGN KEY (id_pengajuan) REFERENCES pengajuan_pencairan(id),

    -- Dari departemen mana
    departemen_asal     ENUM('pajak','legal','produksi','mkdt') NOT NULL,
    id_user_pengirim    INT NOT NULL,
    id_kavling          VARCHAR(20) NOT NULL,

    -- Detail pengembalian
    alasan              TEXT NOT NULL,                   -- kenapa dikembalikan
    nominal_dikembalikan DECIMAL(15,2) NOT NULL,
    tanggal_kirim       DATE NOT NULL,
    bukti_transfer      VARCHAR(255) NULL,               -- bukti dept sudah transfer

    -- Konfirmasi keuangan
    status              ENUM('menunggu','diterima','ditolak') DEFAULT 'menunggu',
    id_user_keuangan    INT NULL,
    tanggal_konfirmasi  DATETIME NULL,
    catatan_keuangan    TEXT NULL,

    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_kavling (id_kavling),
    INDEX idx_pengajuan (id_pengajuan)
);
```

---

## Ringkasan Alur Proses

### Alur Pencairan Dana
```
Dept ──(diajukan)──> Keuangan ──(diproses)──> [urus ke luar]
                                     └──────(cair)──> pilih tujuan
                                     └──────(ditolak)
```

### Alur Pengembalian Dana
```
Dept ──(transfer + upload bukti)──> Keuangan ──(diterima)
                                              └──(ditolak)
```

## Tugas Implementasi Mendatang
1. Buat migration database untuk tabel `pengajuan_pencairan` dan `pengembalian_dana`.
2. Buat Model CI4 untuk kedua tabel tersebut.
3. Buat Repository CI4 untuk menangani logika query pengajuan dan pengembalian.
4. Buat Service CI4 untuk menangani bisnis logic (validasi, ganti status, upload bukti transfer).
5. Buat Controller API (atau Web) untuk departemen agar bisa mengajukan dan memantau status.
6. Buat Controller Web Keuangan untuk list pengajuan, proses, cairkan, dan verifikasi pengembalian.
7. Buat UI (Views/JS) form pengajuan di masing-masing modul departemen, dan dashboard monitoring di Keuangan.
