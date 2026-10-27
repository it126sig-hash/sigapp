# Module: Keuangan

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-10-23 13:52:12` @ `ad0c900`  

## Purpose
Sentralisasi finansial: Cash-In penagihan & pembayaran, Cash-Out subkon & operasional, SPP, bank KPR, pajak, dan ledger kas.

## Out of scope
- Hal-hal di luar tanggung jawab domain Keuangan.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `keuangan` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `log_pembayaran` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `finance_ledger` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `bank_kpr_disbursement` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `list_bank` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `cashout` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `cashout_subkon` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `cashout_subkon_detail` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `cashout_subkon_detail_allocation` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `cashout_subkon_history` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `cashout_subkon_kavling` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `cashout_subkon_payment` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `list_cashout` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `riwayat_pencairan_jaminan` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `riwayat_pencairan_jaminan_detail` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `dana_jaminan_history` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `dana_akad` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `pencairan_akad_plan` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `pencairan_akad_item` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `pencairan_akad_pengajuan` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `pencairan_akad_pengajuan_detail` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `pencairan_akad_payment` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `pencairan_akad_payment_detail` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `invoice_log` *(akses tulis eksklusif hanya untuk modul Keuangan)*
- `invoice_status_log` *(akses tulis eksklusif hanya untuk modul Keuangan)*

### Shared Tables (Dibaca dari modul lain)
- `mkdt`
- `kavling`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `POST` | `/keuangan/jatuhtempo/get` | `Explicit` | `Keuangan::getJatuhTempo` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-bank/get` | `Explicit` | `Keuangan::getBankKprDisbursement` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-bank/save` | `Explicit` | `Keuangan::saveBankKprDisbursement` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-bank/void/(:num)` | `Explicit` | `Keuangan::voidBankKprDisbursement/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/ambilsatu` | `Explicit` | `Tagihan::getByID` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/riwayat/ambilsatu` | `Explicit` | `Tagihan::getRiwayatByID` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/turunkpr` | `Explicit` | `Tagihan::tambahTurunKPR` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/hapusturunkpr` | `Explicit` | `Tagihan::hapusTurunKPR` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/hapus` | `Explicit` | `Tagihan::hapus` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/void` | `Explicit` | `Tagihan::voidTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/unvoid` | `Explicit` | `Tagihan::unvoidTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/jatuhtempo` | `Explicit` | `Tagihan::getAllJatuhTempo` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/riwayat-bayar` | `Explicit` | `Tagihan::riwayatBayarIndex` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/riwayat-bayar/ambil` | `Explicit` | `Tagihan::getRiwayatBayar` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/tagihan/list` | `Explicit` | `Tagihan::listTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/list/ambil` | `Explicit` | `Tagihan::getListTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/list/ambil-grouped` | `Explicit` | `Tagihan::getListTagihanGrouped` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/list/detail` | `Explicit` | `Tagihan::getListTagihanDetail` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/list/export-excel` | `Explicit` | `Tagihan::exportExcelTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/list/ambil-riwayat-surat` | `Explicit` | `Tagihan::getListRiwayatSurat` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/danaakad/list/ambilsatu` | `Explicit` | `Tagihan::getListTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/pembayaran/kuitansi/cetak` | `Explicit` | `Pembayaran::print` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/pembayaran/kuitansi-um/cetak` | `Explicit` | `Pembayaran::printUm` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/pembayaran/simpan` | `Explicit` | `Pembayaran::save` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/pembayaran/hapus` | `Explicit` | `Pembayaran::removeLP` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/pembayaran/hitungulang` | `Explicit` | `Pembayaran::recalculateSummary` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/cashout/ambil` | `Explicit` | `CashOut::getByIDKavling` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/cashout/listitem/ambil` | `Explicit` | `CashOut::getListItem` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/cashout/delete` | `Explicit` | `CashOut::delete` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/cashout/save` | `Explicit` | `CashOut::insert` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/cashout/kavling` | `Explicit` | `CashoutKavling::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/kavling/list` | `Explicit` | `CashoutKavling::getDataTables` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/kavling/detail-list` | `Explicit` | `CashoutKavling::getDetailList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/cashout/subkon` | `Explicit` | `CashoutSubkon::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/subkon/list` | `Explicit` | `CashoutSubkon::getDataTables` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/subkon/detail-list` | `Explicit` | `CashoutSubkon::getDetailList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/subkon/ambil` | `Explicit` | `CashoutSubkon::get` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/subkon/save` | `Explicit` | `CashoutSubkon::save` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/subkon/turun-jatuh-tempo` | `Explicit` | `CashoutSubkon::turunJatuhTempo` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/subkon/ajukan-spp` | `Explicit` | `CashoutSubkon::ajukanSPP` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/subkon/history` | `Explicit` | `CashoutSubkon::getHistory` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/subkon/ajukan-pencairan` | `Explicit` | `CashoutSubkon::ajukanPencairan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/cashout/subkon/pembayaran` | `Explicit` | `CashoutSubkon::pembayaran` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/api/bank/ambil` | `Explicit` | `BankController::ambil` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/bank/list` | `Explicit` | `BankController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bank/ambilsatu` | `Explicit` | `BankController::ambilSatu` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bank/simpan` | `Explicit` | `BankController::simpan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bank/hapus` | `Explicit` | `BankController::hapus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/get` | `Explicit` | `PencairanAkad::get` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/plan/save-retensi` | `Explicit` | `PencairanAkad::saveRetensi` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/plan/save-tenor` | `Explicit` | `PencairanAkad::saveTenor` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/pengajuan/store` | `Explicit` | `PencairanAkad::storePengajuan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/pencairan/store` | `Explicit` | `PencairanAkad::cairkan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/payment/list` | `Explicit` | `PencairanAkad::listPayment` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/payment/void` | `Explicit` | `PencairanAkad::voidPayment` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/void` | `Explicit` | `PencairanAkad::void` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/keuangan/pencairan-akad/history/(:num)` | `Explicit` | `PencairanAkad::history/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/batch/list` | `Explicit` | `PencairanAkad::postBatchList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/batch/eligible-kavling` | `Explicit` | `PencairanAkad::postEligibleKavling` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/batch/pengajuan/store` | `Explicit` | `PencairanAkad::postStoreBatchPengajuan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/batch/detail` | `Explicit` | `PencairanAkad::postBatchDetail` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/batch/pencairan/store` | `Explicit` | `PencairanAkad::postCairkanBatch` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/batch/void` | `Explicit` | `PencairanAkad::postVoidBatch` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/keuangan/pencairan-akad/batch/history/(:num)` | `Explicit` | `PencairanAkad::getBatchHistory/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/keuangan/hasil-akad/list` | `Explicit` | `PencairanAkad::listHasilAkad` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/hasil-akad/list/ambil-grouped` | `Explicit` | `PencairanAkad::getListGrouped` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/hasil-akad/list/detail` | `Explicit` | `PencairanAkad::getListDetail` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/keuangan/hasil-akad/export-template` | `Explicit` | `PencairanAkad::exportTemplate` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/keuangan/hasil-akad/import` | `Explicit` | `PencairanAkad::importForm` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/hasil-akad/import` | `Explicit` | `PencairanAkad::import` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/api/tagihan/booking/verifikasi` | `Explicit` | `BookingPaymentController::verify` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/laporan/cash-in` | `Explicit` | `CashInReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/cash-in/summary` | `Explicit` | `CashInReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/cash-in/detail` | `Explicit` | `CashInReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/laporan/hasil-akad` | `Explicit` | `HasilAkadReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/hasil-akad/summary` | `Explicit` | `HasilAkadReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/hasil-akad/detail` | `Explicit` | `HasilAkadReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/print/spptb` | `Explicit` | `PrintController::printSpptb` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/pencairan` | `Explicit` | `PencairanJaminan::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/pencairan/list/(:num)` | `Explicit` | `PencairanJaminan::list/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/pencairan/store` | `Explicit` | `PencairanJaminan::store` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/pencairan/cairkan/(:num)` | `Explicit` | `PencairanJaminan::cairkan/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/pencairan/history/(:num)` | `Explicit` | `PencairanJaminan::history/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/pencairan/toggle/(:num)` | `Explicit` | `PencairanJaminan::toggleStatus/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/pencairan/download/(:num)` | `Explicit` | `PencairanJaminan::download/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/keuangan/getdanaakad` | `AutoRoute` | `Keuangan::getDanaAkad` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/getcashout` | `AutoRoute` | `Keuangan::getCashOut` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/savecashout` | `AutoRoute` | `Keuangan::saveCashOut` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/savedanaakad` | `AutoRoute` | `Keuangan::saveDanaAkad` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/get_riwayat_gantinama` | `AutoRoute` | `Keuangan::get_riwayat_gantinama` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/issudahbayar` | `AutoRoute` | `Keuangan::isSudahBayar` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/isi_tagihan` | `AutoRoute` | `Keuangan::isi_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/save_sb` | `AutoRoute` | `Keuangan::save_sb` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/save` | `AutoRoute` | `Keuangan::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/list_tagihan` | `AutoRoute` | `Keuangan::list_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/getlisttagihan` | `AutoRoute` | `Keuangan::getListTagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/riwayat_bayar` | `AutoRoute` | `Keuangan::riwayat_bayar` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/getriwayatbayar` | `AutoRoute` | `Keuangan::getRiwayatBayar` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/get_tagihan` | `AutoRoute` | `Keuangan::get_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/removelp` | `AutoRoute` | `Keuangan::removeLP` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/doprint` | `AutoRoute` | `Keuangan::doPrint` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/print_tagihan` | `AutoRoute` | `Keuangan::print_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/if_where` | `AutoRoute` | `Keuangan::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/format_tgl` | `AutoRoute` | `Keuangan::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/is_active` | `AutoRoute` | `Keuangan::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/get_riwayat_tagihan` | `AutoRoute` | `Keuangan::get_riwayat_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/simpan_penagihan` | `AutoRoute` | `Keuangan::simpan_penagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/update_status_penagihan` | `AutoRoute` | `Keuangan::update_status_penagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/download_penagihan` | `AutoRoute` | `Keuangan::download_penagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/get_kopsurat_list` | `AutoRoute` | `Keuangan::get_kopsurat_list` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/get_kopsurat_detail` | `AutoRoute` | `Keuangan::get_kopsurat_detail` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/simpan_kopsurat` | `AutoRoute` | `Keuangan::simpan_kopsurat` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/hapus_kopsurat` | `AutoRoute` | `Keuangan::hapus_kopsurat` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tagihan/save` | `AutoRoute` | `Tagihan::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/getone` | `AutoRoute` | `Pajak::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/save` | `AutoRoute` | `Pajak::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/if_where` | `AutoRoute` | `Pajak::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/is_active` | `AutoRoute` | `Pajak::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/format_tgl` | `AutoRoute` | `Pajak::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/dana_akad/getbyid` | `AutoRoute` | `DanaAkad::getByID` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/list_bank/index` | `AutoRoute` | `ListBank::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/print_controller/printkuitansi` | `AutoRoute` | `PrintController::printKuitansi` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/get_data_by_id` | `AutoRoute` | `Direksi::get_data_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/save` | `AutoRoute` | `Direksi::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/if_where` | `AutoRoute` | `Direksi::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/format_tgl` | `AutoRoute` | `Direksi::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/is_active` | `AutoRoute` | `Direksi::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/export_xlsx` | `AutoRoute` | `Direksi::export_xlsx` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/tagihan` | `AutoRoute` | `Direksi::tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/get_pending_tagihan` | `AutoRoute` | `Direksi::get_pending_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/get_my_signature` | `AutoRoute` | `Direksi::get_my_signature` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/sign_tagihan` | `AutoRoute` | `Direksi::sign_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/get_history_tagihan` | `AutoRoute` | `Direksi::get_history_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/direksi/reject_tagihan` | `AutoRoute` | `Direksi::reject_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `KeuanganService.php`
- `simpanIsiTagihan($request, int $actorId)`
- `simpanDanaAkad($request, int $actorId)`
- `simpanInvoice($request, int $actorId)`
- `updateSudahDibayar($request)`
- `syncTagihan(int $idMkdt, array $um, int $actorId)`
- `synchronizeLunasStatus(int $idMkdt)`
- `getListTagihan($request, $status = null)`
- `getListTagihanJatuhTempoGrouped($request)`
- `getListTagihanGrouped($request)`
- `getListTagihanJatuhTempoDetail(int $idMkdt)`
- `getListTagihanDetail(int $idMkdt, $statusLunas = null)`
- `getTagihanById($id_mkdt, $isTurunKPR = false)`
- `getRiwayatBayarById($id_mkdt)`
- `getTotalBayarById($id_mkdt)`
- `getPaidItemSummaryById($id_mkdt)`
- _dan 19 method lainnya..._
#### `PembayaranService.php`
- `simpan($data)`
- `recalculateSummary($id_mkdt)`
- `removeLP($request)`
#### `PajakService.php`
- `insertPajak(array $fields, int $idKavling, int $actorId)`
- `updatePajak(int $idPajak, array $fields, int $actorId)`
#### `FinanceLedgerService.php`
- `recordIncomeFromLogPembayaran(int $idPembayaran, ?int $actorId = null)`
- `voidByLogPembayaran(int $idPembayaran, ?int $actorId = null)`
- `recordIncomeFromDanaJaminan(int $idDanaAkad, int $idMkdt, ?int $actorId = null)`
- `voidByDanaJaminan(int $idDanaAkad, ?int $actorId = null)`
- `recordIncomeFromBankKprDisbursement(int $idDisbursement, ?int $actorId = null)`
- `voidByBankKprDisbursement(int $idDisbursement, ?int $actorId = null)`
- `recordIncomeFromPencairanAkadPaymentDetail(int $idPaymentDetail, ?int $actorId = null)`
- `voidByPencairanAkadPaymentDetail(int $idPaymentDetail, ?int $actorId = null)`
- `recordExpenseFromCashoutSubkonDetail(int $idCashoutSubkonDetail, ?int $actorId = null)`
- `recordExpenseFromBayarProduksi(int $idBayarProduksi, ?int $actorId = null)`
- `voidByBayarProduksi(int $idBayarProduksi, ?int $actorId = null)`
- `syncExpensesFromPajak(int $idPajak, ?int $actorId = null)`
- `getTotalIncome(array $filters = [])`
- `getTotalIncomeByMkdt(int $idMkdt)`
- `getTotalIncomeByKavling(int $idKavling)`
- _dan 1 method lainnya..._
#### `BankKprDisbursementService.php`
- `getData(int $idMkdt, int $idKavling)`
- `save($request, int $actorId)`
- `void(int $id, $request, int $actorId)`
#### `BankService.php`
- `simpan(array $data, int $id = null)`
- `hapus(int $id)`
#### `BookingPaymentService.php`
- `assertEditable(int $idMkdt, float $nominal, ?string $tanggal)`
- `synchronize(int $idMkdt, ?int $actorId = null)`
- `verify(int $idMkdt, int $actorId, ?float $nominal = null, ?string $tanggal = null)`
- `assertMayDelete(int $paymentId)`
- `assertManualAllocationAllowed(int $idMkdt, array $allocations)`
- `getBooking(int $idMkdt)`
- `getInstallment(int $idMkdt)`
#### `BookingFeeMigrationService.php`
- `run()`
- `snapshot()`
#### `PencairanAkadService.php`
- `getData(int $idMkdt, int $idKavling)`
- `saveRetensi(array $post, int $actorId)`
- `saveTenor(array $post, int $actorId)`
- `storePengajuan($request, int $actorId)`
- `cairkan($request, int $actorId)`
- `listPayment(int $idPengajuan)`
- `voidPayment(int $idPayment, string $reason, int $actorId)`
- `void(int $idPengajuan, string $reason, int $actorId)`
- `exportTemplate(array $filter)`
- `templateColumns()`
- `importTanggalCair(string $csvPath, $lampiranFile, int $actorId)`
- `getListGrouped($request)`
- `getListDetail(int $idMkdt)`
- `getHistory(int $idKavling)`
- `findLockedMismatch(array $row, object $item)`
- _dan 9 method lainnya..._
#### `CashOutService.php`
- `insert($data)`
- `delete($id)`
- `format_tgl($tgl)`
- `is_active($id, $texts, $textf)`
#### `CashoutKavlingService.php`
- `getDataTables(array $var)`
- `getDetailList(int $idKavling)`
#### `CashoutSubkonService.php`
- `getDataTables(array $var)`
- `getDetailList(int $id_cashout_subkon)`
- `getCashoutSubkon(array $id_kavlings, ?int $id_cashout_subkon = null)`
- `saveCashoutSubkon($post)`
- `turunJatuhTempo($id_detail, $tanggal_jatuh_tempo, $berita_acara)`
- `ajukanSPP($id_detail, $spp_no, $spp_tgl)`
- `ajukanPencairan($id_detail, $spp_no, $spp_tgl, $pencairan_tgl)`
- `pembayaran($id_detail, $cek_no, $cek_tgl)`
- `getHistory($id_cashout_subkon)`
- `num($number)`
#### `CashInReportService.php`
- `getAvailableYears(int $idProyek)`
- `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`
- `getDetail(int $idProyek, array $request)`
- `monthLabel(int $month)`
#### `HasilAkadReportService.php`
- `getAvailableYears(int $idProyek)`
- `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`
- `getDetail(int $idProyek, array $request)`
#### `PrintService.php`
- `printSpptb(int $idKavling, int $idMkdt, int $idProyek)`
- `printKuitansi($var)`
- `printKuitansiUm($var)`
- `exportPBataloskonPdf($id_proyek, $id_cluster, $id_jalan)`
- `exportPoskonPdf($id_proyek, $id_cluster, $id_jalan, $status)`
- `exportPoskonXlsx($id_proyek, $id_cluster, $id_jalan, $status)`
#### `DanaJaminanService.php`
- `getData(int $idMkdt, int $idKavling)`
- `saveDanaAkad($request, int $actorId)`
- `listPengajuan(int $idKavling)`
- `storePengajuan($request, int $actorId)`
- `cairkanPengajuan(int $idPengajuan, $request, int $actorId)`
- `getHistory(int $idKavling)`
- `rejectLegacyToggle()`

## File Inventory
### Controllers (19)
- `app/Controllers/Keuangan.php` (1923 baris, 32 public methods)
- `app/Controllers/Tagihan.php` (521 baris, 17 public methods)
- `app/Controllers/Pembayaran.php` (89 baris, 5 public methods)
- `app/Controllers/Pajak.php` (589 baris, 5 public methods)
- `app/Controllers/DanaAkad.php` (92 baris, 1 public methods)
- `app/Controllers/CashOut.php` (242 baris, 4 public methods)
- `app/Controllers/Keuangan/Cashout/CashoutKavling.php` (85 baris, 3 public methods)
- `app/Controllers/Keuangan/Cashout/CashoutSubkon.php` (111 baris, 10 public methods)
- `app/Controllers/ListBank.php` (27 baris, 1 public methods)
- `app/Controllers/Api/BankController.php` (132 baris, 5 public methods)
- `app/Controllers/PencairanAkad.php` (247 baris, 22 public methods)
- `app/Controllers/Api/BookingPaymentController.php` (54 baris, 1 public methods)
- `app/Controllers/Web/CashInReportController.php` (51 baris, 1 public methods)
- `app/Controllers/Api/CashInReportController.php` (85 baris, 2 public methods)
- `app/Controllers/Web/HasilAkadReportController.php` (49 baris, 1 public methods)
- `app/Controllers/Api/HasilAkadReportController.php` (80 baris, 2 public methods)
- `app/Controllers/PrintController.php` (133 baris, 2 public methods)
- `app/Controllers/PencairanJaminan.php` (75 baris, 7 public methods)
- `app/Controllers/Direksi.php` (416 baris, 12 public methods)

### Services (16)
- `app/Services/KeuanganService.php` (1851 baris)
- `app/Services/PembayaranService.php` (256 baris)
- `app/Services/PajakService.php` (45 baris)
- `app/Services/FinanceLedgerService.php` (596 baris)
- `app/Services/BankKprDisbursementService.php` (407 baris)
- `app/Services/BankService.php` (37 baris)
- `app/Services/BookingPaymentService.php` (326 baris)
- `app/Services/BookingFeeMigrationService.php` (234 baris)
- `app/Services/PencairanAkadService.php` (2124 baris)
- `app/Services/CashOutService.php` (84 baris)
- `app/Services/Keuangan/Cashout/CashoutKavlingService.php` (132 baris)
- `app/Services/Keuangan/Cashout/CashoutSubkonService.php` (822 baris)
- `app/Services/CashInReportService.php` (212 baris)
- `app/Services/HasilAkadReportService.php` (187 baris)
- `app/Services/PrintService.php` (634 baris)
- `app/Services/DanaJaminanService.php` (637 baris)

### Repositories (13)
- `app/Repositories/KeuanganRepository.php` (495 baris)
- `app/Repositories/LogPembayaranRepository.php` (207 baris)
- `app/Repositories/PaymentSummaryRepository.php` (74 baris)
- `app/Repositories/FinanceLedgerRepository.php` (77 baris)
- `app/Repositories/PajakRepository.php` (41 baris)
- `app/Repositories/BookingPaymentRepository.php` (152 baris)
- `app/Repositories/PencairanAkadListRepository.php` (157 baris)
- `app/Repositories/CashOutRepository.php` (61 baris)
- `app/Repositories/Keuangan/Cashout/CashoutKavlingRepo.php` (228 baris)
- `app/Repositories/Keuangan/Cashout/CashoutSubkonRepo.php` (455 baris)
- `app/Repositories/CashInReportRepository.php` (233 baris)
- `app/Repositories/HasilAkadReportRepository.php` (310 baris)
- `app/Repositories/SpptbRepository.php` (23 baris)

### Models (16)
- `app/Models/KeuanganModel.php`
- `app/Models/LogPembayaranModel.php`
- `app/Models/FinanceLedgerModel.php`
- `app/Models/BankKprDisbursementModel.php`
- `app/Models/ListBankModel.php`
- `app/Models/CashoutModel.php`
- `app/Models/CashoutSubkonModel.php`
- `app/Models/CashoutSubkonDetailModel.php`
- `app/Models/CashoutSubkonDetailAllocationModel.php`
- `app/Models/CashoutSubkonHistoryModel.php`
- `app/Models/CashoutSubkonKavlingModel.php`
- `app/Models/CashoutSubkonPaymentModel.php`
- `app/Models/ListCashoutModel.php`
- `app/Models/RiwayatPencairanJaminanModel.php`
- `app/Models/RiwayatPencairanJaminanDetailModel.php`
- `app/Models/DanaJaminanHistoryModel.php`

### Views (5 folder)
- `app/Views/keuangan` (4 file)
- `app/Views/cashout_kavling` (1 file)
- `app/Views/cashout_subkon` (1 file)
- `app/Views/laporan` (2 file)
- `app/Views/pdf` (17 file)

### JavaScript (9 file)
- `public/assets/js/siteplan/keuangan.js` (2192 baris)
- `public/assets/js/siteplan/pajak.js` (240 baris)
- `public/assets/js/siteplan/cashout_subkon.js` (1062 baris)
- `public/assets/js/tagihan-bayar-modal.js` (1407 baris)
- `public/assets/js/pencairan-akad-modal.js` (1266 baris)
- `public/assets/js/keuangan/penagihan.js` (1041 baris)
- `public/assets/js/cashout_kavling/index.js` (128 baris)
- `public/assets/js/laporan-cash-in.js` (473 baris)
- `public/assets/js/laporan-hasil-akad.js` (228 baris)

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [ ] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Keuangan.php`: db_connect(), $this->db->table(), new KeuanganModel(), new KavlingModel(), new MkdtModel(), new KonsumenModel(), new LogPembayaranModel(), new ProfilePerusahaanModel(), new RiwayatPencairanJaminanModel()
> - `app/Controllers/Tagihan.php`: db_connect(), Database::connect(), new ProfilePerusahaanModel()
> - `app/Controllers/Pembayaran.php`: Database::connect()
> - `app/Controllers/Pajak.php`: db_connect(), $this->db->table(), new ClusterModel(), new ProyekModel()
> - `app/Controllers/DanaAkad.php`: db_connect(), $this->db->table(), new ProfilePerusahaanModel()
> - `app/Controllers/CashOut.php`: db_connect(), $this->db->table()
> - `app/Controllers/Keuangan/Cashout/CashoutKavling.php`: Database::connect(), $this->db->table(), new ProyekModel(), new ChecklistSubItemModel()
> - `app/Controllers/ListBank.php`: new ListBankModel()
> - `app/Controllers/Api/BankController.php`: new ListBankModel()
> - `app/Controllers/Api/BookingPaymentController.php`: Database::connect()
> - `app/Controllers/PrintController.php`: db_connect(), $this->db->table(), new ProfilePerusahaanModel(), new ProyekModel(), new KeuanganModel()
> - `app/Controllers/PencairanJaminan.php`: new RiwayatPencairanJaminanModel()
> - `app/Controllers/Direksi.php`: db_connect(), $this->db->table()

## Dead Code Candidates
_Jalankan `php spark module:deadcode Keuangan` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.
- **Deprecation Notice (PencairanJaminan & DanaJaminanService):**
  - Komponen legacy `PencairanJaminan.php`, `DanaJaminanService.php`, dan rute `/pencairan/*` berstatus **DEPRECATED** dan dijadwalkan untuk dihapus tuntas saat sesi refaktor Modul Keuangan.
  - Seluruh fungsionalitas retensi jaminan perbankan telah digantikan sepenuhnya oleh sub-modul terpadu `PencairanAkad` (`pencairan_akad_*`) dengan rute `/keuangan/pencairan-akad/*`.
  - Tidak ada modul lain (MKDT, Legal, Produksi, Promosi, dsb.) yang mengakses controller maupun service ini.
