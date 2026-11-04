# Handoff Report: Milestone 1 — Backend Service Implementation Plan (`BookingAkadReportService`)

**Agent**: Explorer M1_2 (`teamwork_explorer`)  
**Working Directory**: `c:\Users\Salivs\Data\laragon\www\sigapp.dev\.agents\teamwork\explorer_m1_2\`  
**Date**: 2026-11-04  
**Handoff Type**: Hard (Task Complete)  

---

## 1. Observation

1. **Existing Service Implementation (`app/Services/BookingAkadReportService.php`)**:
   - Line 10: `public const CATEGORIES = ['booking', 'booking_aktif', 'booking_batal', 'akad'];`  
     *Defisiensi*: Hanya mengagregasi 4 status lama, belum memuat 6 status resmi (`booking`, `wawancara`, `sp3k`, `batal`, `akad_indent`, `akad_ajb`) sesuai R1.
   - Lines 60–80: Menggunakan pemanggilan terpisah `getMonthlyBookingTotals` dan `getMonthlyAkadTotals`.
   - Line 94: `item['rasio_akad'] = $booking > 0 ? round(($akad / $booking) * 100, 1) : 0.0;`  
     *Defisiensi*: Menghitung rasio kuantitas akad bulanan kalender berjalan dibagi booking baru di bulan yang sama. Ini memicu anomali rasio hingga 1.400% (misal 1 booking, 14 akad dari masa lalu) atau 0% saat tidak ada booking baru meski ada akad.
   - Line 114: Menghitung total rasio tahunan dari total akad kalender dibagi total booking kalender.
   - Lines 141–148: Method `getDetail()` mengembalikan array data mentah (`$result['data']`) dari repositori tanpa standardisasi format 7 kolom atau struktur objek bersarang.
   - Lines 181–191: Pemetaan kolom `order_by` di `normalizeDataTableRequest()` hanya mencakup kolom lama (`jenis_laporan`, `alamat_kavling`, `tanggal_transaksi`, `status_mkdt`, `nominal`), belum mengenali kolom-kolom baru spesifikasi R3.

2. **Kontrak Antarmuka (`PROJECT.md § Interface Contracts`)**:
   - Lines 36–43: Repository `getSummary(int $idProyek, int $year): array` mengembalikan array bulanan (1..12) dengan metrik:
     `['booking' => int, 'wawancara' => int, 'sp3k' => int, 'batal' => int, 'akad_indent' => int, 'akad_ajb' => int, 'booking_converted_akad' => int, 'total_booking_cohort' => int]`.
   - Lines 45–62: Respon JSON Summary wajib menyertakan:
     `{"status": "success", "data": {"years": [...], "summary": {"2026": {"1": {"booking": 0, ..., "konversi": 0.0}, ..., "total": {"booking": ..., "konversi": ...}}}}}`.
   - Lines 63–91: Respon JSON Detail wajib menyertakan struktur 7 kolom:
     `id_mkdt`, `id_kavling`, `kavling` (`nama_jalan`, `no_kavling`, `sales`), `konsumen` (`nama_konsumen`, `tipe_rumah`, `is_subsidi`), `tanggal_booking` (`d-m-Y`), `status_terakhir`, `status_terakhir_badge`, `tanggal_status_terakhir` (`d-m-Y` atau `'-'`), `catatan_mkdt`.

3. **Spesifikasi & Suite Pengujian (`tests/unit/BookingAkadReportServiceOverhaulTest.php`)**:
   - Lines 29–75: Memvalidasi agregasi ke-6 status (`booking`, `wawancara`, `sp3k`, `batal`, `akad_indent`, `akad_ajb`) dalam nilai bulan.
   - Lines 78–159: Memvalidasi formula konversi riil cohort berbasis transaksi unik yang dibatasi strictly pada $[0.0\%, 100.0\%]$.
   - Lines 163–193: Memvalidasi payload modal detail 7 kolom.
   - Lines 323–330: Memvalidasi format tanggal safe zero-date `'0000-00-00'` $\rightarrow$ `'-'`.
   - Lines 335–343: Memvalidasi fallback sales NULL/kosong $\rightarrow$ `'-'`.
   - Lines 359–428: Memvalidasi hierarki prioritas status: Batal > Akad AJB > Akad Indent > SP3K > Wawancara > Booking.

---

## 2. Logic Chain

1. **Dari Observasi 1 (Defisiensi Kategori) ke Solusi R1**:
   Konstanta `CATEGORIES` di `BookingAkadReportService` wajib diperbarui menjadi:
   `['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb']`. Ini menjadi whitelist resmi untuk validasi drilldown detail dan sinkronisasi enumerasi MKDT.
2. **Dari Observasi 1 & 2 (Anomali Rasio Konvensional) ke Solusi Konversi Real Cohort R2**:
   - Denominator adalah `total_booking_cohort`: transaksi unik (`mkdt.uniq_id`) yang melakukan booking pada bulan $m$.
   - Numerator adalah `booking_converted_akad`: dari transaksi unik booking bulan $m$, berapa yang akhirnya mencapai akad (`akad_indent` atau `akad_ajb`).
   - Formula bulanan:  
     `$totalCohortBooking > 0 ? round(($cohortAkad / $totalCohortBooking) * 100, 1) : 0.0`.
   - Jika bulan bersangkutan 0 booking, konversi otomatis bernilai `0.0%`, mencegah fatal error *Division by zero*.
   - Formula tahunan: Akumulasi total transaksi booking yang mencapai akad sepanjang tahun dibagi total booking tahun tersebut:  
     `$annualCohortBooking > 0 ? round(($annualCohortAkad / $annualCohortBooking) * 100, 1) : 0.0`. Ini menjamin akurasi matematis tanpa distorsi rerata persentase.
3. **Dari Observasi 2 & 3 ke Kontrak Modal Detail R3**:
   - `formatDetailRow()` mentransformasikan setiap record repositori menjadi objek terstruktur dengan key `kavling` (`nama_jalan`, `no_kavling`, `sales`), `konsumen` (`nama_konsumen`, `tipe_rumah`, `is_subsidi`), `tanggal_booking`, `status_terakhir`, `status_terakhir_badge`, `catatan_mkdt`, dan `tanggal_status_terakhir`.
   - Menyediakan flat aliases pada baris data untuk menjamin DataTables di frontend Milestone 2 dapat membaca baik format nested maupun flat tanpa runtime error.
4. **Dari Observasi 3 (Dirty Data DB) ke Defensive Sanitization Helpers**:
   - Helper `formatDate()` memfilter `0000-00-00`, `0000-00-00 00:00:00`, dan $\le$ `1970-01-01` menjadi `'-'`, dan memformat tanggal valid ke format `d-m-Y`.
   - Helper `resolveCatatanMkdt()` melakukan resolusi bertingkat: `COALESCE(keterangan_batal, keterangan, keterangan_status, '-')`.
   - Helper `resolveStatusTerakhir()` menerapkan prioritas: Batal > Akad AJB > Akad Indent > SP3K > Wawancara > Booking.
   - Helper `resolveStatusBadge()` memetakan status ke semantik Vuexy/Bootstrap: `'success'`, `'danger'`, `'warning'`, `'info'`, `'secondary'`, `'primary'`.

---

## 3. Caveats

1. **Ketergantungan Repositori (M1_1)**:
   - Implementasi service dirancang fleksibel: dapat memanggil `BookingAkadReportRepository::getSummary(int $idProyek, int $year)` atau `getMonthlySummaryTotals(int $idProyek, array $years)` via helper `fetchYearSummaryFromRepository()`. Repositori yang diimplementasikan oleh M1_1 cukup menyediakan salah satu kontrak tersebut.
2. **Ketergantungan Pengujian Unit (M1_3)**:
   - Unit test eksisting `tests/unit/BookingAkadReportServiceTest.php` menguji mock repositori lama (`getMonthlyBookingTotals`, `booking_aktif`, `booking_batal`). Saat implementasi service diaktifkan, unit test tersebut wajib diperbarui ke 6 status baru atau dialihkan ke `BookingAkadReportServiceOverhaulTest.php`.

---

## 4. Conclusion

Rencana implementasi dan rekomendasi kode lengkap untuk `app/Services/BookingAkadReportService.php` telah dirumuskan secara tuntas pada `c:\Users\Salivs\Data\laragon\www\sigapp.dev\.agents\teamwork\explorer_m1_2\analysis.md`.

Rekomendasi kode:
- Mengimplementasikan seluruh 6 status transaksi (R1).
- Menerapkan kalkulasi konversi cohort riil per bulan dan total tahunan berbasis kode transaksi unik (R2).
- Menghasilkan respon JSON 7 kolom bersih sesuai `PROJECT.md § Interface Contracts` dan spesifikasi modal detail (R3).
- Kebal terhadap pembagian nol, tanggal `0000-00-00`, sales null, dan catatan kosong.
- Siap digunakan langsung (*drop-in replacement*) oleh tim implementasi backend (`sigapp-engineer`).

---

## 5. Verification Method

1. **Inspeksi Dokumen**:
   - Buka dan baca dokumen analisis lengkap:
     `c:\Users\Salivs\Data\laragon\www\sigapp.dev\.agents\teamwork\explorer_m1_2\analysis.md`
2. **Pengujian Unit Test PHPUnit**:
   Jalankan perintah pengujian pada terminal:
   ```bash
   vendor/bin/phpunit tests/unit/BookingAkadReportServiceOverhaulTest.php
   ```
3. **Kondisi Invalidasi (Fail Conditions)**:
   - Nilai konversi bulanan atau tahunan melebihi 100.0% atau kurang dari 0.0%.
   - Terjadi PHP warning/notice saat tanggal bernilai `'0000-00-00'` atau sales bernilai null.
   - Terjadi PHP *DivisionByZeroError* saat bulan memiliki 0 booking.
   - Respon modal detail tidak memuat nested key `kavling` atau `konsumen`.
