# Module: Keuangan

**Status:** `MAPPED`  
**Readiness Score:** `80/100`  
**Last Synced:** `2026-11-13 19:47:10` @ `8204898`  

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
| `GET` | `/list_bank/index` | `Explicit` | `\App\Controllers\Web\Keuangan\BankController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/list_bank` | `Explicit` | `\App\Controllers\Web\Keuangan\BankController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/listbank` | `Explicit` | `\App\Controllers\Web\Keuangan\BankController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/bank/meta` | `Explicit` | `BankController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
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
| `GET` | `/laporan/cash-in` | `Explicit` | `\App\Controllers\Web\Keuangan\CashInReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/laporan/cash-in/meta` | `Explicit` | `CashInReportController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/cash-in/summary` | `Explicit` | `CashInReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/cash-in/detail` | `Explicit` | `CashInReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/laporan/hasil-akad` | `Explicit` | `\App\Controllers\Web\Keuangan\HasilAkadReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/laporan/hasil-akad/meta` | `Explicit` | `HasilAkadReportController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
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
- `getRiwayatBayarWithDetailById($id_mkdt)`
- `getRiwayatBayar($request)`
- `getListRiwayatSurat($request)`
- `getAllJatuhTempo($id_proyek)`
- `deleteTagihan(int $idKeuangan, int $actorId)`
- `voidTagihan(int $idKeuangan, string $reason, int $actorId)`
- `unvoidTagihan(int $idKeuangan, int $actorId)`
- `insert($data)`
- `delete($id)`
- `hapusTurunKPR($id)`
- `tambahTurunKPR($data)`
- `hasTurunKPR(int $id_mkdt)`
- `format_tgl($tgl)`
- `is_active($id, $texts, $textf)`
- `exportExcelTagihan($request)`
- `getRiwayatTagihan($request)`
- `simpanPenagihan($request, int $actorId)`
- `updateStatusPenagihan($request, int $actorId)`
- `setTanggalKirim($no_inv)`
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
- `getTotalIncomeByDateRange(string $dateFrom, string $dateTo)`
#### `BankKprDisbursementService.php`
- `getData(int $idMkdt, int $idKavling)`
- `save($request, int $actorId)`
- `void(int $id, $request, int $actorId)`
#### `BankService.php`
- `getAllActive()`
- `getList(?string $search = null)`
- `getOne(int $id)`
- `simpan(array $data, ?int $id = null)`
- `hapus(int $id)`
#### `BookingPaymentService.php`
- `findProjectIdByMkdt(int $idMkdt)`
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
- `generateBatchNumber()`
- `syncBatchStatus(?int $idBatch, int $actorId)`
- `getEligibleBatchKavling(array $idKavlings = [], int $idProyek = 0)`
- `storeBatchPengajuan(array $data, $lampiranFile, int $actorId)`
- `getBatchList($request)`
- `getBatchDetail(int $idBatch)`
- `cairkanBatch(array $post, int $actorId)`
- `voidBatch(int $idBatch, string $reason, int $actorId)`
- `getBatchHistory(int $idBatch)`
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
- `app/Controllers/Keuangan.php` (1923 baris, 32 public methods: `getDanaAkad()`, `getBankKprDisbursement()`, `saveBankKprDisbursement()`, `voidBankKprDisbursement($id = null)`, `getJatuhTempo()`, `getCashOut()`, `saveCashOut()`, `saveDanaAkad()`, `get_riwayat_gantinama()`, `isSudahBayar($id)`, `isi_tagihan()`, `save_sb()`, `save()`, `list_tagihan()`, `getListTagihan()`, `riwayat_bayar()`, `getRiwayatBayar()`, `get_tagihan($id = null)`, `removeLP()`, `doPrint()`, `print_tagihan($id = null)`, `if_where($var, $column, $condition, $query)`, `format_tgl($tgl)`, `is_active($id, $texts, $textf)`, `get_riwayat_tagihan()`, `simpan_penagihan()`, `update_status_penagihan()`, `download_penagihan()`, `get_kopsurat_list()`, `get_kopsurat_detail()`, `simpan_kopsurat()`, `hapus_kopsurat()`)
- `app/Controllers/Tagihan.php` (521 baris, 17 public methods: `getByID()`, `getRiwayatByID()`, `hapusTurunKPR()`, `tambahTurunKPR()`, `hapus()`, `voidTagihan()`, `unvoidTagihan()`, `save()`, `listTagihan()`, `getListTagihan($status = null)`, `getListTagihanGrouped()`, `exportExcelTagihan()`, `getListTagihanDetail()`, `riwayatBayarIndex()`, `getRiwayatBayar()`, `getListRiwayatSurat()`, `getAllJatuhTempo()`)
- `app/Controllers/Pembayaran.php` (89 baris, 5 public methods: `save()`, `print()`, `removeLP()`, `printUm()`, `recalculateSummary()`)
- `app/Controllers/Pajak.php` (589 baris, 5 public methods: `getOne()`, `save()`, `if_where($var, $column, $condition, $query)`, `is_active($id, $texts, $textf)`, `format_tgl($tgl)`)
- `app/Controllers/DanaAkad.php` (92 baris, 1 public methods: `getByID()`)
- `app/Controllers/CashOut.php` (242 baris, 4 public methods: `getListItem()`, `getByIDKavling()`, `insert()`, `delete()`)
- `app/Controllers/Keuangan/Cashout/CashoutKavling.php` (85 baris, 3 public methods: `index()`, `getDataTables()`, `getDetailList()`)
- `app/Controllers/Keuangan/Cashout/CashoutSubkon.php` (111 baris, 10 public methods: `index()`, `getDataTables()`, `getDetailList()`, `get()`, `save()`, `turunJatuhTempo()`, `ajukanSPP()`, `ajukanPencairan()`, `pembayaran()`, `getHistory()`)
- `app/Controllers/Web/Keuangan/BankController.php` (28 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Keuangan/BankController.php` (165 baris, 6 public methods: `meta()`, `list()`, `ambil()`, `ambilSatu()`, `simpan()`, `hapus()`)
- `app/Controllers/PencairanAkad.php` (247 baris, 22 public methods: `get()`, `saveRetensi()`, `saveTenor()`, `storePengajuan()`, `cairkan()`, `listPayment()`, `voidPayment()`, `void()`, `history($id_kavling)`, `listHasilAkad()`, `getListGrouped()`, `getListDetail()`, `exportTemplate()`, `importForm()`, `import()`, `postBatchList()`, `postEligibleKavling()`, `postStoreBatchPengajuan()`, `postBatchDetail()`, `postCairkanBatch()`, `postVoidBatch()`, `getBatchHistory($id)`)
- `app/Controllers/Api/Keuangan/BookingPaymentController.php` (67 baris, 1 public methods: `verify()`)
- `app/Controllers/Web/Keuangan/CashInReportController.php` (51 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Keuangan/CashInReportController.php` (131 baris, 3 public methods: `meta()`, `summary()`, `detail()`)
- `app/Controllers/Web/Keuangan/HasilAkadReportController.php` (49 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Keuangan/HasilAkadReportController.php` (125 baris, 3 public methods: `meta()`, `summary()`, `detail()`)
- `app/Controllers/PrintController.php` (133 baris, 2 public methods: `printSpptb()`, `printKuitansi()`)
- `app/Controllers/PencairanJaminan.php` (75 baris, 7 public methods: `index()`, `list($id_kavling)`, `store()`, `toggleStatus($id)`, `cairkan($id)`, `history($id_kavling)`, `download($id)`)
- `app/Controllers/Direksi.php` (416 baris, 12 public methods: `get_data_by_id($st = null)`, `save()`, `if_where($var, $column, $condition, $query)`, `format_tgl($tgl)`, `is_active($id, $texts, $textf)`, `export_xlsx()`, `tagihan()`, `get_pending_tagihan()`, `get_my_signature()`, `sign_tagihan()`, `get_history_tagihan()`, `reject_tagihan()`)

### Services (16)
- `app/Services/Keuangan/KeuanganService.php` (1852 baris, 34 public methods)
- `app/Services/Keuangan/PembayaranService.php` (259 baris, 3 public methods)
- `app/Services/Keuangan/PajakService.php` (45 baris, 2 public methods)
- `app/Services/Keuangan/FinanceLedgerService.php` (596 baris, 16 public methods)
- `app/Services/Keuangan/BankKprDisbursementService.php` (409 baris, 3 public methods)
- `app/Services/Keuangan/BankService.php` (52 baris, 5 public methods)
- `app/Services/Keuangan/BookingPaymentService.php` (332 baris, 8 public methods)
- `app/Services/Keuangan/BookingFeeMigrationService.php` (234 baris, 2 public methods)
- `app/Services/Keuangan/PencairanAkadService.php` (2127 baris, 24 public methods)
- `app/Services/Keuangan/CashOutService.php` (84 baris, 4 public methods)
- `app/Services/Keuangan/Cashout/CashoutKavlingService.php` (132 baris, 2 public methods)
- `app/Services/Keuangan/Cashout/CashoutSubkonService.php` (822 baris, 10 public methods)
- `app/Services/Keuangan/CashInReportService.php` (212 baris, 4 public methods)
- `app/Services/Keuangan/HasilAkadReportService.php` (199 baris, 3 public methods)
- `app/Services/Keuangan/PrintService.php` (646 baris, 6 public methods)
- `app/Services/Keuangan/DanaJaminanService.php` (640 baris, 7 public methods)

### Repositories (14)
- `app/Repositories/Keuangan/KeuanganRepository.php` (495 baris, 11 public methods: `getTagihanById(int $idMkdt)`, `getTagihanTurunKPRById(int $idMkdt)`, `getAllJatuhTempo($id_proyek)`, `getLIKeu()`, `getBelumLunasQuery()`, `getBelumLunasGroupedQuery()`, `getAllGroupedQuery()`, `getLunasGroupedQuery()`, `getListTagihanJatuhTempoDetailById(int $idMkdt)`, `getListTagihanDetailById(int $idMkdt)`, `getTagihanOnlyByID(int $idMkdt)`)
- `app/Repositories/Keuangan/LogPembayaranRepository.php` (207 baris, 10 public methods: `getRiwayatBayarById(int $idMkdt)`, `getRiwayatBayarQuery($id_proyek, $id_cluster = null, $id_jalan = null)`, `softDeleteAndReturnIdMkdt($idPembayaran)`, `getRiwayatBayarByIdPembayran($id)`, `getDetailRiwayatBayarById(int $id_Pembayaran)`, `getTotalBayarByIdMkdt(int $id_Mkdt)`, `getPaidItemSummaryByIdMkdt(int $id_Mkdt)`, `getDetailRiwayatBayarByIdMkdt(int $id_Mkdt)`, `insertDetail($data)`, `hasRecentDuplicate(int $idMkdt, string $idKeuangan, $nominal, string $tanggalBayar, string $paymentType, int $seconds = 30)`)
- `app/Repositories/Keuangan/PaymentSummaryRepository.php` (74 baris, 3 public methods: `updateCategory(int $idMkdt, string $kategori, float $amount)`, `setToZero(int $id_mkdt)`, `setCategory(int $idMkdt, string $kategori, float $value)`)
- `app/Repositories/Keuangan/FinanceLedgerRepository.php` (77 baris, 4 public methods: `findBySource(string $sourceType, int $sourceId)`, `upsertBySource(array $payload)`, `voidBySource(string $sourceType, int $sourceId, ?int $actorId = null)`, `sumIncome(array $filters = [])`)
- `app/Repositories/Keuangan/PajakRepository.php` (41 baris, 1 public methods: `linkKavling(int $idKavling, int $idPajak)`)
- `app/Repositories/Keuangan/BookingPaymentRepository.php` (166 baris, 14 public methods: `findProjectIdByMkdt(int $idMkdt)`, `mkdt(int $id, bool $lock = false)`, `link(int $id, bool $lock = false)`, `owners(int $payment)`, `payment(int $id)`, `details(int $payment)`, `bookingRows(int $id, bool $deleted = false)`, `item(string $category, ?string $name = null)`, `write(string $table, array $data, array $where = [])`, `saveLink(int $id, ?int $payment, array $extra = [])`, `separateBookingSql(string $detail = 'lpd', string $item = 'kl')`, `effectiveCategorySql(string $detail = 'lpd', string $item = 'kl')`, `installmentSummary(int $id)`, `recalculate(int $id)`)
- `app/Repositories/Keuangan/PencairanAkadListRepository.php` (157 baris, 2 public methods: `buildListQuery(array $filters)`, `getSummary(array $filters, string $search = '')`)
- `app/Repositories/Keuangan/CashOutRepository.php` (61 baris, 3 public methods: `getListItem(string $search)`, `getRiwayatBayarCashOutByIDKavling(int $id_kavling)`, `softDelete($id)`)
- `app/Repositories/Keuangan/Cashout/CashoutKavlingRepo.php` (228 baris, 3 public methods: `getDataTables(array $var)`, `getDetailList(int $idKavling)`, `getCashoutRowsByKavling(int $idKavling)`)
- `app/Repositories/Keuangan/Cashout/CashoutSubkonRepo.php` (455 baris, 20 public methods: `getSubkonByID(int $id_subkon)`, `getDataTables(array $var)`, `getDetailsByCashoutSubkonIds(array $ids)`, `getListCashoutKavling(array $id_kavlings)`, `getCashoutSubkonByID(int $id_cashout_subkon)`, `getKavlingSubkonByID(int $id_cashout_subkon)`, `getListItemDetailByIDCashoutsubkon(int $id_cashout_subkon)`, `upsertSubkon($id_subkon, array $data)`, `saveCashoutSubkon(array $data)`, `saveCashoutSubkonDetail(array $data)`, `getKavlingBySubkonId(int $id_cashout_subkon)`, `getDetailByID(int $id_detail)`, `updateDetail(int $id_detail, array $data)`, `saveHistory(int $id_cashout_subkon, string $keterangan, int $status)`, `getDetailByCashoutSubkonID(int $id_cashout_subkon)`, `syncAutomaticDetailAllocations(int $id_cashout_subkon, array $id_kavlings)`, `getAllocationsByDetailID(int $id_cashout_subkon_detail)`, `updateCashoutSubkonStatus(int $id_cashout_subkon, int $status)`, `saveCashoutSubkonKavling(array $data)`, `getHistoryByIDCashoutSubkon($id_cashout_subkon)`)
- `app/Repositories/Keuangan/CashInReportRepository.php` (233 baris, 4 public methods: `getAvailableYearBounds(int $idProyek)`, `getMonthlyPaymentTotals(int $idProyek, array $years)`, `getMonthlyAkadTotals(int $idProyek, array $years)`, `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable)`)
- `app/Repositories/Keuangan/HasilAkadReportRepository.php` (363 baris, 5 public methods: `getAvailableYearBounds(int $idProyek)`, `getMonthlyAkadTotals(int $idProyek, array $years)`, `getMonthlyPengajuanTotals(int $idProyek, array $years)`, `getMonthlyPaymentTotals(int $idProyek, array $years)`, `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable)`)
- `app/Repositories/Keuangan/SpptbRepository.php` (23 baris, 1 public methods: `getLatestByMkdtId(int $idMkdt, int $limit = 3)`)
- `app/Repositories/Keuangan/BankRepository.php` (47 baris, 6 public methods: `getAllBanks(?string $search = null)`, `getActiveBanks()`, `getBankById(int $id)`, `insertBank(array $data)`, `updateBank(int $id, array $data)`, `deleteBank(int $id)`)

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
- `app/Views/keuangan` (4 file: `list-hasil-akad.php`, `list-tagihan.php`, `pencairan-akad-import.php`, `riwayat-bayar.php`)
- `app/Views/cashout_kavling` (1 file: `index.php`)
- `app/Views/cashout_subkon` (1 file: `index.php`)
- `app/Views/laporan` (3 file: `booking-akad.php`, `cash-in.php`, `hasil-akad.php`)
- `app/Views/pdf` (17 file: `invoice.php`, `kuitansi_pembayaran-uangmuka.php`, `kuitansi_pembayaran.bak.php`, `kuitansi_pembayaran.php`, `penagihan.php`, `poskon-batal.php`, `poskon.php`, `slf.php`, `slf_page3.php`, `spptb-memo.php`, `spptb-new-page2.php`, `spptb-new-page3.php`, `spptb-new.php`, `spptb-page-next.php`, `spptb-page1.php`, `tagihan_keuangan.php`, `testpdf.php`)

### JavaScript (9 file)
- `public/assets/js/siteplan/keuangan.js` (2192 baris)
- `public/assets/js/siteplan/pajak.js` (240 baris)
- `public/assets/js/siteplan/cashout_subkon.js` (1062 baris)
- `public/assets/js/tagihan-bayar-modal.js` (1407 baris)
- `public/assets/js/pencairan-akad-modal.js` (1266 baris)
- `public/assets/js/keuangan/penagihan.js` (1041 baris)
- `public/assets/js/cashout_kavling/index.js` (128 baris)
- `public/assets/js/laporan-cash-in.js` (473 baris)
- `public/assets/js/laporan-hasil-akad.js` (721 baris)

### Zero-Downtime Shims (32 file)
- `app/Controllers/ListBank.php`
- `app/Controllers/Api/BankController.php`
- `app/Controllers/Api/BookingPaymentController.php`
- `app/Controllers/Web/CashInReportController.php`
- `app/Controllers/Api/CashInReportController.php`
- `app/Controllers/Web/HasilAkadReportController.php`
- `app/Controllers/Api/HasilAkadReportController.php`
- `app/Services/KeuanganService.php`
- `app/Services/PembayaranService.php`
- `app/Services/PajakService.php`
- `app/Services/FinanceLedgerService.php`
- `app/Services/BankKprDisbursementService.php`
- `app/Services/BankService.php`
- `app/Services/BookingPaymentService.php`
- `app/Services/BookingFeeMigrationService.php`
- `app/Services/PencairanAkadService.php`
- `app/Services/CashOutService.php`
- `app/Services/CashInReportService.php`
- `app/Services/HasilAkadReportService.php`
- `app/Services/PrintService.php`
- `app/Services/DanaJaminanService.php`
- `app/Repositories/KeuanganRepository.php`
- `app/Repositories/LogPembayaranRepository.php`
- `app/Repositories/PaymentSummaryRepository.php`
- `app/Repositories/FinanceLedgerRepository.php`
- `app/Repositories/PajakRepository.php`
- `app/Repositories/BookingPaymentRepository.php`
- `app/Repositories/PencairanAkadListRepository.php`
- `app/Repositories/CashOutRepository.php`
- `app/Repositories/CashInReportRepository.php`
- `app/Repositories/HasilAkadReportRepository.php`
- `app/Repositories/SpptbRepository.php`

## Key Functions
- **Controller (`app/Controllers/Web/Keuangan/BankController.php`):** `index()` (Merender halaman master List Bank via `BankService::getList()`).
- **Controller (`app/Controllers/Api/Keuangan/BankController.php`):** `meta()` (Metadata daftar bank, bank aktif, dan izin akses), `list()` (Dual-mode JSON `?format=raw` dan DataTables HTML legacy), `ambil()`, `ambilSatu()` (Dual-mode `?format=raw`), `simpan()`, `hapus()`.
- **Controller (`app/Controllers/Web/Keuangan/CashInReportController.php`):** `index()` (Merender halaman Laporan Cash In dengan validasi akses menu & proyek aktif).
- **Controller (`app/Controllers/Api/Keuangan/CashInReportController.php`):** `meta()` (Metadata proyek aktif, tahun tersedia, kategori, dan permissions), `summary()`, `detail()` (Dual-mode `?format=raw` dan DataTables payload).
- **Controller (`app/Controllers/Web/Keuangan/HasilAkadReportController.php`):** `index()` (Merender halaman Laporan Hasil Akad dengan validasi akses menu & proyek aktif).
- **Controller (`app/Controllers/Api/Keuangan/HasilAkadReportController.php`):** `meta()` (Metadata proyek aktif, tahun tersedia, kategori, dan permissions), `summary()`, `detail()` (Dual-mode `?format=raw` dan DataTables payload).
- **Controller (`app/Controllers/Api/Keuangan/BookingPaymentController.php`):** `verify()` (Validasi role `[1, 3]`, validasi nominal/tanggal, cek kepemilikan proyek via `findProjectIdByMkdt()` + `ActiveProyekService::userCanAccess()`, dan verifikasi booking fee melalui `BookingPaymentService::verify()`).
- **Service (`app/Services/Keuangan/KeuanganService.php`):** `simpanIsiTagihan($request, int $actorId)`, `simpanDanaAkad($request, int $actorId)`, `simpanInvoice($request, int $actorId)`, `updateSudahDibayar($request)`, `syncTagihan(int $idMkdt, array $um, int $actorId)`, `simpanPenagihan($request, int $actorId)` (Validasi kop surat, item tagihan, nomor surat duplikat, dan persistensi invoice log berstatus draft/publish), `updateStatusPenagihan($request, int $actorId)` (Transisi status tagihan dan pencatatan audit trail status log), `setTanggalKirim($no_inv)`
- **Service (`app/Services/Keuangan/PembayaranService.php`):** `simpan($data)`, `recalculateSummary($id_mkdt)`, `removeLP($request)`
- **Service (`app/Services/Keuangan/PajakService.php`):** `insertPajak(array $fields, int $idKavling, int $actorId)`, `updatePajak(int $idPajak, array $fields, int $actorId)`
- **Service (`app/Services/Keuangan/FinanceLedgerService.php`):** `recordIncomeFromLogPembayaran(int $idPembayaran, ?int $actorId = null)`, `voidByLogPembayaran(int $idPembayaran, ?int $actorId = null)`, `recordIncomeFromDanaJaminan(int $idDanaAkad, int $idMkdt, ?int $actorId = null)`, `voidByDanaJaminan(int $idDanaAkad, ?int $actorId = null)`, `recordIncomeFromBankKprDisbursement(int $idDisbursement, ?int $actorId = null)`
- **Service (`app/Services/Keuangan/BankKprDisbursementService.php`):** `getData(int $idMkdt, int $idKavling)`, `save($request, int $actorId)`, `void(int $id, $request, int $actorId)`
- **Service (`app/Services/Keuangan/BankService.php`):** `getAllActive()`, `getList(?string $search = null)`, `getOne(int $id)`, `simpan(array $data, ?int $id = null)`, `hapus(int $id)`
- **Service (`app/Services/Keuangan/BookingPaymentService.php`):** `findProjectIdByMkdt(int $idMkdt)`, `assertEditable(int $idMkdt, float $nominal, ?string $tanggal)`, `synchronize(int $idMkdt, ?int $actorId = null)`, `verify(int $idMkdt, int $actorId, ?float $nominal = null, ?string $tanggal = null)`, `assertMayDelete(int $paymentId)`, `assertManualAllocationAllowed(int $idMkdt, array $allocations)`
- **Service (`app/Services/Keuangan/BookingFeeMigrationService.php`):** `run()`, `snapshot()`
- **Service (`app/Services/Keuangan/PencairanAkadService.php`):** `getData(int $idMkdt, int $idKavling)`, `saveRetensi(array $post, int $actorId)`, `saveTenor(array $post, int $actorId)`, `storePengajuan($request, int $actorId)`, `cairkan($request, int $actorId)`
- **Service (`app/Services/Keuangan/CashOutService.php`):** `insert($data)`, `delete($id)`, `format_tgl($tgl)`, `is_active($id, $texts, $textf)`
- **Service (`app/Services/Keuangan/Cashout/CashoutKavlingService.php`):** `getDataTables(array $var)`, `getDetailList(int $idKavling)`
- **Service (`app/Services/Keuangan/Cashout/CashoutSubkonService.php`):** `getDataTables(array $var)`, `getDetailList(int $id_cashout_subkon)`, `getCashoutSubkon(array $id_kavlings, ?int $id_cashout_subkon = null)`, `saveCashoutSubkon($post)`, `turunJatuhTempo($id_detail, $tanggal_jatuh_tempo, $berita_acara)`
- **Service (`app/Services/Keuangan/CashInReportService.php`):** `getAvailableYears(int $idProyek)`, `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`, `getDetail(int $idProyek, array $request)`, `monthLabel(int $month)`
- **Service (`app/Services/Keuangan/HasilAkadReportService.php`):** `getAvailableYears(int $idProyek)`, `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`, `getDetail(int $idProyek, array $request)`. Mendukung kategori aktif `'akad'` berdampingan dengan backward-compatibility `'penjualan'`, `'pengajuan'`, dan `'cair'`; kalkulasi key finansial `total_hasil_akad` (akumulasi `m.harga_kpr_acc` dari `mkdt`), `total_akad`, dan `total_penjualan` pada payload summary bulanan & total tahunan via helper `assignAkadRow()` dan `emptyAmounts()`; normalisasi parameter request dan whitelist ordering column DataTables detail (`konsumen`, `kavling`, `tipe_rumah`, `total_hasil_akad`, `nominal`, `booking_tgl`, `akad_tgl`, `harga_jual`, `total_sudah_cair`, `tanggal_pengajuan`, `tanggal_cair`) via `normalizeDataTableRequest()`.
- **Service (`app/Services/Keuangan/PrintService.php`):** `printSpptb(int $idKavling, int $idMkdt, int $idProyek)`, `printKuitansi($var)`, `printKuitansiUm($var)`, `exportPBataloskonPdf($id_proyek, $id_cluster, $id_jalan)`, `exportPoskonPdf($id_proyek, $id_cluster, $id_jalan, $status)`
- **Service (`app/Services/Keuangan/DanaJaminanService.php`):** `getData(int $idMkdt, int $idKavling)`, `saveDanaAkad($request, int $actorId)`, `listPengajuan(int $idKavling)`, `storePengajuan($request, int $actorId)`, `cairkanPengajuan(int $idPengajuan, $request, int $actorId)`
- **Repository (`app/Repositories/Keuangan/BankRepository.php`):** `getAllBanks(?string $search = null)`, `getActiveBanks()`, `getBankById(int $id)`, `insertBank(array $data)`, `updateBank(int $id, array $data)`, `deleteBank(int $id)`
- **Repository (`app/Repositories/Keuangan/KeuanganRepository.php`):** `getTagihanById(int $idMkdt)`, `getTagihanTurunKPRById(int $idMkdt)`, `getAllJatuhTempo($id_proyek)`, `getLIKeu()`, `getBelumLunasQuery()`
- **Repository (`app/Repositories/Keuangan/LogPembayaranRepository.php`):** `getRiwayatBayarById(int $idMkdt)`, `getRiwayatBayarQuery($id_proyek, $id_cluster = null, $id_jalan = null)`, `softDeleteAndReturnIdMkdt($idPembayaran)`, `getRiwayatBayarByIdPembayran($id)`, `getDetailRiwayatBayarById(int $id_Pembayaran)`
- **Repository (`app/Repositories/Keuangan/PaymentSummaryRepository.php`):** `updateCategory(int $idMkdt, string $kategori, float $amount)`, `setToZero(int $id_mkdt)`, `setCategory(int $idMkdt, string $kategori, float $value)`
- **Repository (`app/Repositories/Keuangan/FinanceLedgerRepository.php`):** `findBySource(string $sourceType, int $sourceId)`, `upsertBySource(array $payload)`, `voidBySource(string $sourceType, int $sourceId, ?int $actorId = null)`, `sumIncome(array $filters = [])`
- **Repository (`app/Repositories/Keuangan/PajakRepository.php`):** `linkKavling(int $idKavling, int $idPajak)`
- **Repository (`app/Repositories/Keuangan/BookingPaymentRepository.php`):** `findProjectIdByMkdt(int $idMkdt)`, `mkdt(int $id, bool $lock = false)`, `link(int $id, bool $lock = false)`, `owners(int $payment)`, `payment(int $id)`, `details(int $payment)`
- **Repository (`app/Repositories/Keuangan/PencairanAkadListRepository.php`):** `buildListQuery(array $filters)`, `getSummary(array $filters, string $search = '')`
- **Repository (`app/Repositories/Keuangan/CashOutRepository.php`):** `getListItem(string $search)`, `getRiwayatBayarCashOutByIDKavling(int $id_kavling)`, `softDelete($id)`
- **Repository (`app/Repositories/Keuangan/Cashout/CashoutKavlingRepo.php`):** `getDataTables(array $var)`, `getDetailList(int $idKavling)`, `getCashoutRowsByKavling(int $idKavling)`
- **Repository (`app/Repositories/Keuangan/Cashout/CashoutSubkonRepo.php`):** `getSubkonByID(int $id_subkon)`, `getDataTables(array $var)`, `getDetailsByCashoutSubkonIds(array $ids)`, `getListCashoutKavling(array $id_kavlings)`, `getCashoutSubkonByID(int $id_cashout_subkon)`
- **Repository (`app/Repositories/Keuangan/CashInReportRepository.php`):** `getAvailableYearBounds(int $idProyek)`, `getMonthlyPaymentTotals(int $idProyek, array $years)`, `getMonthlyAkadTotals(int $idProyek, array $years)`, `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable)`
- **Repository (`app/Repositories/Keuangan/HasilAkadReportRepository.php`):** `getAvailableYearBounds(int $idProyek)`, `getMonthlyAkadTotals(int $idProyek, array $years)` (mengakumulasikan `m.harga_kpr_acc` sebagai `total_hasil_akad`), `getMonthlyPengajuanTotals(int $idProyek, array $years)`, `getMonthlyPaymentTotals(int $idProyek, array $years)`, `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable)`. Dilengkapi builder kueri detail modular `compileDetailQuery()` dengan helper SQL builder: `tipeRumahSql()`, `isSubsidiSql()`, dan `totalSudahCairSql()` untuk join ke tabel `tipe` dan `hargajual` serta subquery agregasi pencairan; perluasan multi-column search builder untuk DataTables server-side (`tipe_rumah`, `alamat_kavling`, `nama_konsumen`, `jenis_laporan`, `tanggal_transaksi`).
- **Repository (`app/Repositories/Keuangan/SpptbRepository.php`):** `getLatestByMkdtId(int $idMkdt, int $limit = 3)`
- **Frontend Assets (`public/assets/js/laporan-hasil-akad.js`):** Menangani rendering matriks komparasi 3 kolom (`Total Hasil Akad`, `Total Pengajuan`, `Total Cair`), chart bulanan berlabel `Total Hasil Akad <Tahun>`, dan modal detail dinamis server-side DataTables:
  - Detail `Total Hasil Akad` (6 kolom): `Nama Konsumen / Kavling` (gabungan nama konsumen, alamat kavling, tipe rumah + badge Subsidi/Komersil rata kiri), `Tgl Booking`, `Tgl Akad`, `Total Hasil Akad` (nominal `m.harga_kpr_acc` tebal), `Total Sudah Cair` (nominal pencairan hijau tebal + pill badge persentase ketercairan), dan `Aksi` (Detail Kavling Siteplan & Kelola Pencairan Akad).
  - Detail `Total Pengajuan` & `Total Cair` (5 kolom): `Nama Konsumen / Kavling` (gabungan rata kiri), `Tgl Pengajuan`, `Status & Rincian` (teks rincian item termin/retensi di baris 1, horizontal flexbox badge status di kiri dan tanggal pencairan di kanan pada baris 2), `Nominal` (tebal rata kanan), dan `Aksi`.
  - Helper rendering modular: `renderKonsumenKavling(row)`, `renderStatusRincian(row, category)`, dan `renderActionButtons(row)`.
- **View & Modal Detail Integration (`app/Views/laporan/hasil-akad.php`):** Mengintegrasikan partial stylesheet `<style><?= view('siteplan/partials/modal_detail_styles') ?></style>`, vendor assets Flatpickr (CSS/JS) & Select2 (CSS/JS), serta inisialisasi lingkungan JS yang tahan redeklarasi (`window.base_url` dan `window.loaded = window.loaded || []`) untuk menjamin render modal detail kavling (`siteplan-detail-modal.js`) berjalan mulus tanpa error JS/CSS.
- **Service Dependency (`app/Services/FileAccessService.php`):** Otorisasi whitelist kategori berkas `'gambar_kerja'` dibuka untuk role Keuangan (`3`) bersama seluruh peran operasional (`[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]`), memastikan denah arsitektur kavling dapat diakses dan ditampilkan di modal detail kavling tanpa HTTP 403 Forbidden.

## Deprecated & Legacy Shims
### Zero-Downtime Class Shims (32 file — Dibuat pada Fase 1 `2026-11-13`)
Seluruh kelas shim di bawah ini dipertahankan sementara di namespace/lokasi asal sebagai *Zero-Downtime Shims* (`extends` kelas modular di `*\Keuangan\*`) dan berstatus `@deprecated`:
- **Controller Shims (7 file):**
  - `app/Controllers/ListBank.php` (`App\Controllers\ListBank`) -> Gunakan `\App\Controllers\Web\Keuangan\BankController`
  - `app/Controllers/Api/BankController.php` (`App\Controllers\Api\BankController`) -> Gunakan `\App\Controllers\Api\Keuangan\BankController`
  - `app/Controllers/Api/BookingPaymentController.php` (`App\Controllers\Api\BookingPaymentController`) -> Gunakan `\App\Controllers\Api\Keuangan\BookingPaymentController`
  - `app/Controllers/Web/CashInReportController.php` (`App\Controllers\Web\CashInReportController`) -> Gunakan `\App\Controllers\Web\Keuangan\CashInReportController`
  - `app/Controllers/Api/CashInReportController.php` (`App\Controllers\Api\CashInReportController`) -> Gunakan `\App\Controllers\Api\Keuangan\CashInReportController`
  - `app/Controllers/Web/HasilAkadReportController.php` (`App\Controllers\Web\HasilAkadReportController`) -> Gunakan `\App\Controllers\Web\Keuangan\HasilAkadReportController`
  - `app/Controllers/Api/HasilAkadReportController.php` (`App\Controllers\Api\HasilAkadReportController`) -> Gunakan `\App\Controllers\Api\Keuangan\HasilAkadReportController`
- **Service Shims (14 file):**
  - `app/Services/KeuanganService.php` -> Gunakan `\App\Services\Keuangan\KeuanganService`
  - `app/Services/PembayaranService.php` -> Gunakan `\App\Services\Keuangan\PembayaranService`
  - `app/Services/PajakService.php` -> Gunakan `\App\Services\Keuangan\PajakService`
  - `app/Services/FinanceLedgerService.php` -> Gunakan `\App\Services\Keuangan\FinanceLedgerService`
  - `app/Services/BankKprDisbursementService.php` -> Gunakan `\App\Services\Keuangan\BankKprDisbursementService`
  - `app/Services/BankService.php` -> Gunakan `\App\Services\Keuangan\BankService`
  - `app/Services/BookingPaymentService.php` -> Gunakan `\App\Services\Keuangan\BookingPaymentService`
  - `app/Services/BookingFeeMigrationService.php` -> Gunakan `\App\Services\Keuangan\BookingFeeMigrationService`
  - `app/Services/PencairanAkadService.php` -> Gunakan `\App\Services\Keuangan\PencairanAkadService`
  - `app/Services/CashOutService.php` -> Gunakan `\App\Services\Keuangan\CashOutService`
  - `app/Services/CashInReportService.php` -> Gunakan `\App\Services\Keuangan\CashInReportService`
  - `app/Services/HasilAkadReportService.php` -> Gunakan `\App\Services\Keuangan\HasilAkadReportService`
  - `app/Services/PrintService.php` -> Gunakan `\App\Services\Keuangan\PrintService`
  - `app/Services/DanaJaminanService.php` -> Gunakan `\App\Services\Keuangan\DanaJaminanService`
- **Repository Shims (11 file):**
  - `app/Repositories/KeuanganRepository.php` -> Gunakan `\App\Repositories\Keuangan\KeuanganRepository`
  - `app/Repositories/LogPembayaranRepository.php` -> Gunakan `\App\Repositories\Keuangan\LogPembayaranRepository`
  - `app/Repositories/PaymentSummaryRepository.php` -> Gunakan `\App\Repositories\Keuangan\PaymentSummaryRepository`
  - `app/Repositories/FinanceLedgerRepository.php` -> Gunakan `\App\Repositories\Keuangan\FinanceLedgerRepository`
  - `app/Repositories/PajakRepository.php` -> Gunakan `\App\Repositories\Keuangan\PajakRepository`
  - `app/Repositories/BookingPaymentRepository.php` -> Gunakan `\App\Repositories\Keuangan\BookingPaymentRepository`
  - `app/Repositories/PencairanAkadListRepository.php` -> Gunakan `\App\Repositories\Keuangan\PencairanAkadListRepository`
  - `app/Repositories/CashOutRepository.php` -> Gunakan `\App\Repositories\Keuangan\CashOutRepository`
  - `app/Repositories/CashInReportRepository.php` -> Gunakan `\App\Repositories\Keuangan\CashInReportRepository`
  - `app/Repositories/HasilAkadReportRepository.php` -> Gunakan `\App\Repositories\Keuangan\HasilAkadReportRepository`
  - `app/Repositories/SpptbRepository.php` -> Gunakan `\App\Repositories\Keuangan\SpptbRepository`

### Legacy Routes (Menunggu Dekomposisi Fase 2–5)
- Route `POST /keuangan/jatuhtempo/get` (`Keuangan::getJatuhTempo`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-bank/get` (`Keuangan::getBankKprDisbursement`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-bank/save` (`Keuangan::saveBankKprDisbursement`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-bank/void/(:num)` (`Keuangan::voidBankKprDisbursement/$1`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/ambilsatu` (`Tagihan::getByID`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/riwayat/ambilsatu` (`Tagihan::getRiwayatByID`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/turunkpr` (`Tagihan::tambahTurunKPR`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/hapusturunkpr` (`Tagihan::hapusTurunKPR`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/hapus` (`Tagihan::hapus`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/void` (`Tagihan::voidTagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/unvoid` (`Tagihan::unvoidTagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/jatuhtempo` (`Tagihan::getAllJatuhTempo`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /riwayat-bayar` (`Tagihan::riwayatBayarIndex`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/riwayat-bayar/ambil` (`Tagihan::getRiwayatBayar`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /tagihan/list` (`Tagihan::listTagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/list/ambil` (`Tagihan::getListTagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/list/ambil-grouped` (`Tagihan::getListTagihanGrouped`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/list/detail` (`Tagihan::getListTagihanDetail`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/list/export-excel` (`Tagihan::exportExcelTagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /tagihan/list/ambil-riwayat-surat` (`Tagihan::getListRiwayatSurat`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /danaakad/list/ambilsatu` (`Tagihan::getListTagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /pembayaran/kuitansi/cetak` (`Pembayaran::print`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /pembayaran/kuitansi-um/cetak` (`Pembayaran::printUm`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /pembayaran/simpan` (`Pembayaran::save`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /pembayaran/hapus` (`Pembayaran::removeLP`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /pembayaran/hitungulang` (`Pembayaran::recalculateSummary`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/cashout/ambil` (`CashOut::getByIDKavling`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/cashout/listitem/ambil` (`CashOut::getListItem`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/cashout/delete` (`CashOut::delete`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/cashout/save` (`CashOut::insert`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /cashout/kavling` (`CashoutKavling::index`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/kavling/list` (`CashoutKavling::getDataTables`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/kavling/detail-list` (`CashoutKavling::getDetailList`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /cashout/subkon` (`CashoutSubkon::index`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/subkon/list` (`CashoutSubkon::getDataTables`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/subkon/detail-list` (`CashoutSubkon::getDetailList`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/subkon/ambil` (`CashoutSubkon::get`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/subkon/save` (`CashoutSubkon::save`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/subkon/turun-jatuh-tempo` (`CashoutSubkon::turunJatuhTempo`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/subkon/ajukan-spp` (`CashoutSubkon::ajukanSPP`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/subkon/history` (`CashoutSubkon::getHistory`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/subkon/ajukan-pencairan` (`CashoutSubkon::ajukanPencairan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /cashout/subkon/pembayaran` (`CashoutSubkon::pembayaran`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/get` (`PencairanAkad::get`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/plan/save-retensi` (`PencairanAkad::saveRetensi`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/plan/save-tenor` (`PencairanAkad::saveTenor`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/pengajuan/store` (`PencairanAkad::storePengajuan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/pencairan/store` (`PencairanAkad::cairkan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/payment/list` (`PencairanAkad::listPayment`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/payment/void` (`PencairanAkad::voidPayment`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/void` (`PencairanAkad::void`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /keuangan/pencairan-akad/history/(:num)` (`PencairanAkad::history/$1`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/batch/list` (`PencairanAkad::postBatchList`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/batch/eligible-kavling` (`PencairanAkad::postEligibleKavling`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/batch/pengajuan/store` (`PencairanAkad::postStoreBatchPengajuan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/batch/detail` (`PencairanAkad::postBatchDetail`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/batch/pencairan/store` (`PencairanAkad::postCairkanBatch`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/pencairan-akad/batch/void` (`PencairanAkad::postVoidBatch`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /keuangan/pencairan-akad/batch/history/(:num)` (`PencairanAkad::getBatchHistory/$1`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /keuangan/hasil-akad/list` (`PencairanAkad::listHasilAkad`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/hasil-akad/list/ambil-grouped` (`PencairanAkad::getListGrouped`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/hasil-akad/list/detail` (`PencairanAkad::getListDetail`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /keuangan/hasil-akad/export-template` (`PencairanAkad::exportTemplate`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /keuangan/hasil-akad/import` (`PencairanAkad::importForm`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /keuangan/hasil-akad/import` (`PencairanAkad::import`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /print/spptb` (`PrintController::printSpptb`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /pencairan` (`PencairanJaminan::index`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /pencairan/list/(:num)` (`PencairanJaminan::list/$1`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /pencairan/store` (`PencairanJaminan::store`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /pencairan/cairkan/(:num)` (`PencairanJaminan::cairkan/$1`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /pencairan/history/(:num)` (`PencairanJaminan::history/$1`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /pencairan/toggle/(:num)` (`PencairanJaminan::toggleStatus/$1`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /pencairan/download/(:num)` (`PencairanJaminan::download/$1`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/getdanaakad` (`Keuangan::getDanaAkad`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/getcashout` (`Keuangan::getCashOut`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/savecashout` (`Keuangan::saveCashOut`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/savedanaakad` (`Keuangan::saveDanaAkad`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/get_riwayat_gantinama` (`Keuangan::get_riwayat_gantinama`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/issudahbayar` (`Keuangan::isSudahBayar`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/isi_tagihan` (`Keuangan::isi_tagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/save_sb` (`Keuangan::save_sb`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/save` (`Keuangan::save`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/list_tagihan` (`Keuangan::list_tagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/getlisttagihan` (`Keuangan::getListTagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/riwayat_bayar` (`Keuangan::riwayat_bayar`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/getriwayatbayar` (`Keuangan::getRiwayatBayar`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/get_tagihan` (`Keuangan::get_tagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/removelp` (`Keuangan::removeLP`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/doprint` (`Keuangan::doPrint`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/print_tagihan` (`Keuangan::print_tagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/if_where` (`Keuangan::if_where`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/format_tgl` (`Keuangan::format_tgl`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/is_active` (`Keuangan::is_active`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/get_riwayat_tagihan` (`Keuangan::get_riwayat_tagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/simpan_penagihan` (`Keuangan::simpan_penagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/update_status_penagihan` (`Keuangan::update_status_penagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/download_penagihan` (`Keuangan::download_penagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/get_kopsurat_list` (`Keuangan::get_kopsurat_list`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/get_kopsurat_detail` (`Keuangan::get_kopsurat_detail`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/simpan_kopsurat` (`Keuangan::simpan_kopsurat`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /keuangan/hapus_kopsurat` (`Keuangan::hapus_kopsurat`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /tagihan/save` (`Tagihan::save`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /pajak/getone` (`Pajak::getOne`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /pajak/save` (`Pajak::save`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /pajak/if_where` (`Pajak::if_where`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /pajak/is_active` (`Pajak::is_active`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /pajak/format_tgl` (`Pajak::format_tgl`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /dana_akad/getbyid` (`DanaAkad::getByID`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /list_bank/index` (`ListBank::index`) — **LEGACY / DEPRECATED (AutoRoute fallback)**: `GET /list_bank/index` telah dimigrasikan ke `\App\Controllers\Web\Keuangan\BankController::index`; `ListBank` dipertahankan hanya sebagai Zero-Downtime Shim.
- Route `ANY /print_controller/printkuitansi` (`PrintController::printKuitansi`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/get_data_by_id` (`Direksi::get_data_by_id`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/save` (`Direksi::save`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/if_where` (`Direksi::if_where`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/format_tgl` (`Direksi::format_tgl`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/is_active` (`Direksi::is_active`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/export_xlsx` (`Direksi::export_xlsx`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/tagihan` (`Direksi::tagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/get_pending_tagihan` (`Direksi::get_pending_tagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/get_my_signature` (`Direksi::get_my_signature`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/sign_tagihan` (`Direksi::sign_tagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/get_history_tagihan` (`Direksi::get_history_tagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /direksi/reject_tagihan` (`Direksi::reject_tagihan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
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
> - `app/Controllers/PrintController.php`: db_connect(), $this->db->table(), new ProfilePerusahaanModel(), new ProyekModel(), new KeuanganModel()
> - `app/Controllers/PencairanJaminan.php`: new RiwayatPencairanJaminanModel()
> - `app/Controllers/Direksi.php`: db_connect(), $this->db->table()

## Dead Code Candidates
_Jalankan `php spark module:deadcode Keuangan` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.
- **Cross-Module Callers (Pemanggil Lintas Modul terhadap Public PHP Services & Repositories):**
  - `KeuanganService`: `App\Services\Transaksi\TransaksiService` (via shim `App\Services\KeuanganService`).
  - `BookingPaymentService`: `App\Controllers\Mkdt`, `App\Services\Mkdt\NupService`, `App\Services\Transaksi\TransaksiService` (via shim `App\Services\BookingPaymentService`).
  - `FinanceLedgerService`: `App\Services\Produksi\ProduksiService` (via shim), serta CLI command `App\Commands\FinanceLedgerBackfillIncome`.
  - `PencairanAkadService`: `App\Services\Siteplan\SiteplanDetailService` (via shim).
  - `PrintService`: `App\Services\Mkdt\PosisiKonsumenService` (via shim).
  - `BookingFeeMigrationService`: CLI command `App\Commands\MigrateBookingFee`.
  - **Repository Shims Lintas Modul:** `App\Services\Mkdt\MkdtFinancialBreakdownService`, `App\Services\Mkdt\MkdtFinancialReconciliationService`, `App\Services\Mkdt\MkdtSettlementService` (`BookingPaymentRepository`); `App\Services\Siteplan\SiteplanDetailService` (`CashOutRepository`); `App\Services\Siteplan\SiteplanService` (`KeuanganRepository`); `App\Services\Transaksi\TransaksiService` (`KeuanganRepository`, `LogPembayaranRepository`, `SpptbRepository`).
- **Kontrak Dual-Mode & Metadata API (Fase 1 `2026-11-13`):**
  - Endpoint `GET /api/bank/list`, `POST /api/bank/ambilsatu`, `POST /api/laporan/cash-in/detail`, dan `POST /api/laporan/hasil-akad/detail` mendukung parameter `?format=raw` untuk mengembalikan payload JSON standar `{ success, message, data: { items, meta } }` tanpa membongkar respons DataTables HTML/legacy bagi klien eksisting.
  - Ditambahkan endpoint metadata baru: `GET /api/bank/meta`, `GET /api/laporan/cash-in/meta`, dan `GET /api/laporan/hasil-akad/meta`.
- **Catatan Skema & Integrasi `invoice_log` (Pembaruan Migrasi 2026-11-04):**
  - Tabel `invoice_log` diperkaya dengan kolom `status_tagihan` (`VARCHAR(50)`, NOT NULL, DEFAULT `'draft'`), `tanggal_ubah_status` (`DATE`, NULL), `keterangan_status` (`TEXT`, NULL), dan `tanggal_kirim` (`DATETIME`, NULL).
  - Index `idx_invoice_log_status_tagihan` (`status_tagihan`) ditambahkan untuk mempercepat kueri filter tagihan aktif dan riwayat status.
  - Seluruh data legacy yang belum memiliki status tagihan telah di-backfill menjadi `'draft'`.
- **Deprecation Notice (PencairanJaminan & DanaJaminanService):**
  - Komponen legacy `PencairanJaminan.php`, `DanaJaminanService.php`, dan rute `/pencairan/*` berstatus **DEPRECATED** dan dijadwalkan untuk dihapus tuntas saat sesi refaktor Modul Keuangan.
  - Seluruh fungsionalitas retensi jaminan perbankan telah digantikan sepenuhnya oleh sub-modul terpadu `PencairanAkad` (`pencairan_akad_*`) dengan rute `/keuangan/pencairan-akad/*`.
  - Tidak ada modul lain (MKDT, Legal, Produksi, Promosi, dsb.) yang mengakses controller maupun service ini.
- **Integrasi Modal Detail Kavling & Autorisasi File Gambar Kerja (Pembaruan 2026-11-09):**
  - Tampilan laporan Hasil Akad (`app/Views/laporan/hasil-akad.php`) mengintegrasikan modal detail kavling lintas modul (`siteplan/partials/modal_detail` & `siteplan-detail-modal.js`).
  - Dependensi visual modal (Flatpickr CSS/JS, Select2 CSS/JS, dan `modal_detail_styles`) diinisialisasi secara eksplisit pada view laporan bersama runtime environment tahan redeklarasi (`window.base_url` dan `window.loaded = window.loaded || []`).
  - Whitelist otorisasi kategori berkas `'gambar_kerja'` pada `FileAccessService` mencakup role Keuangan (3), memastikan gambar denah arsitektur kavling tampil dengan aman saat dibuka oleh staf Keuangan tanpa HTTP 403 Forbidden.

## Change Log
- **2026-11-13 [`REFACTOR` — Fase 1] — Migrasi Layer Service & Repository ke Subfolder `Keuangan/` + Subdomain Laporan, Bank & BookingPayment:**
  - **Routes & Public Contracts:**
    - `app/Config/Routes.php`: Mengarahkan rute `/list_bank/index`, `/list_bank`, `/listbank` ke `\App\Controllers\Web\Keuangan\BankController::index`; grup `/api/bank/*` ke `App\Controllers\Api\Keuangan\BankController` (plus endpoint baru `GET /api/bank/meta` dan dukungan dual-mode `?format=raw` pada `list` & `ambilSatu`); `/laporan/cash-in` & `/laporan/hasil-akad` ke `Web\Keuangan\*`; grup `/api/laporan/cash-in/*` & `/api/laporan/hasil-akad/*` ke `Api\Keuangan\*` (plus `GET */meta` dan dual-mode `?format=raw` pada `detail`); serta `/api/tagihan/booking/verifikasi` ke `App\Controllers\Api\Keuangan\BookingPaymentController::verify`.
  - **Controller / Service / Repo / Model:**
    - **7 Modular Controllers Baru:** `Web/Keuangan/BankController` (`index`), `Api/Keuangan/BankController` (`meta`, `list`, `ambil`, `ambilSatu`, `simpan`, `hapus`), `Web/Keuangan/CashInReportController` (`index`), `Api/Keuangan/CashInReportController` (`meta`, `summary`, `detail`), `Web/Keuangan/HasilAkadReportController` (`index`), `Api/Keuangan/HasilAkadReportController` (`meta`, `summary`, `detail`), dan `Api/Keuangan/BookingPaymentController` (`verify`).
    - **14 Modular Services (`app/Services/Keuangan/`):** Memindahkan `KeuanganService`, `PembayaranService`, `PajakService`, `FinanceLedgerService`, `BankKprDisbursementService`, `BankService` (+ `getAllActive()`, `getList()`, `getOne()`), `BookingPaymentService` (+ `findProjectIdByMkdt()`), `BookingFeeMigrationService`, `PencairanAkadService`, `CashOutService`, `CashInReportService`, `HasilAkadReportService`, `PrintService`, dan `DanaJaminanService` ke namespace `App\Services\Keuangan`.
    - **12 Modular Repositories (`app/Repositories/Keuangan/`):** Membuat `BankRepository.php` baru dan memindahkan `KeuanganRepository`, `LogPembayaranRepository`, `PaymentSummaryRepository`, `FinanceLedgerRepository`, `PajakRepository`, `BookingPaymentRepository` (+ `findProjectIdByMkdt()`), `PencairanAkadListRepository`, `CashOutRepository`, `CashInReportRepository`, `HasilAkadReportRepository`, serta `SpptbRepository` ke namespace `App\Repositories\Keuangan`.
    - **32 Zero-Downtime Shims:** Mempertahankan 7 Controller shims, 14 Service shims (`App\Services\*`), dan 11 Repository shims (`App\Repositories\*`) bertanda `@deprecated` di lokasi asal.
    - `app/Libraries/ModuleManager/ModuleCatalog.php`: Mendaftarkan seluruh path modular baru dan 32 shims modul Keuangan (Readiness Score meningkat `60%` -> `80%`).
  - **View / CSS / JS:** Tidak ada perubahan (minimum diff; seluruh UI eksisting dan 9 file JS tetap berfungsi tanpa regresi).
  - **Deprecations:** 32 file kelas shim pada lokasi root `app/Controllers/`, `app/Controllers/Api/`, `app/Controllers/Web/`, `app/Services/`, dan `app/Repositories/` ditandai `@deprecated` dan digantikan oleh namespace `*\Keuangan\*`.
- **2026-11-09 [`BUGFIX`] — Penyempurnaan Asset & Autorisasi Render Modal Detail Kavling:**
  - **Routes & Public Contracts:**
    - `app/Services/FileAccessService.php`: Memperbarui whitelist role kategori `'gambar_kerja'` menjadi `[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]` sehingga role Keuangan (3) dan seluruh peran operasional lainnya dapat memuat denah dan gambar arsitektural kavling tanpa terkena HTTP 403 Forbidden.
  - **Controller / Service / Repo / Model:**
    - Otorisasi akses berkas pada layer `FileAccessService` diperluas untuk kategori `'gambar_kerja'` mencakup role Keuangan.
  - **View / CSS / JS:**
    - `app/Views/laporan/hasil-akad.php`:
      - Menyertakan partial stylesheet `<style><?= view('siteplan/partials/modal_detail_styles') ?></style>` agar styling modal detail kavling seragam dan utuh.
      - Memuat vendor assets: Flatpickr CSS/JS dan Select2 CSS/JS untuk mendukung elemen form dan filter di dalam modal detail kavling.
      - Mengintegrasikan inisialisasi runtime `window.base_url = '<?= base_url() ?>/'` dan `window.loaded = window.loaded || []` dengan pola aman non-redeklarasi untuk mencegah `SyntaxError: Identifier has already been declared`.
  - **Deprecations:** Tidak ada.
- **2026-11-09 [`UI_REPORT` / `FEATURE`] — Refinement V2 Matriks Total Hasil Akad & Perampingan Modal Detail:**
  - **Routes & Public Contracts:**
    - `POST /api/laporan/hasil-akad/summary`: Mengembalikan key `total_hasil_akad` pada bulan dan total tahunan (mengakumulasikan `m.harga_kpr_acc` dari `mkdt`), tetap mempertahankan key `total_akad` dan `total_penjualan` untuk backward-compatibility.
    - `POST /api/laporan/hasil-akad/detail`: Whitelist parameter ordering diperkaya dengan `total_hasil_akad`. Payload baris kategori `akad` menyertakan `total_hasil_akad` (`m.harga_kpr_acc`) sebagai nominal utama.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/HasilAkadReportRepository.php`:
      - `getMonthlyAkadTotals()` menambahkan akumulasi `SUM(m.harga_kpr_acc)` dialiaskan sebagai `total_hasil_akad`.
      - `compileDetailQuery()` menyertakan kolom `m.harga_kpr_acc AS total_hasil_akad`, `tipe_rumah`, `is_subsidi`, `booking_tgl`, `akad_tgl`, `total_sudah_cair`, dan pencarian DataTables di kolom `tipe_rumah`.
    - `app/Services/HasilAkadReportService.php`:
      - `assignAkadRow()` dan `emptyAmounts()` memetakan `total_hasil_akad` secara konsisten pada summary payload.
      - `normalizeDataTableRequest()` mendaftarkan `total_hasil_akad` ke dalam whitelist kolom pengurutan orderable.
    - `tests/unit/HasilAkadReportServiceTest.php`: Memperbarui dan memverifikasi 6 test cases (38 assertions) lulus 100% untuk struktur summary V2, alias kategori akad/penjualan, dan format respon detail.
  - **View / CSS / JS:**
    - `app/Views/laporan/hasil-akad.php`: Pembaruan version query string asset JS modal dan laporan.
    - `public/assets/js/laporan-hasil-akad.js`:
      - Matriks & Summary: Kolom tabel dan series chart dari "Total Akad" diubah menjadi "Total Hasil Akad" yang membaca data dari `total_hasil_akad`.
      - Modal Detail "Total Hasil Akad" dirampingkan menjadi 6 kolom: Menggabungkan info konsumen, alamat kavling, tipe rumah + badge Subsidi/Komersil ke kolom "Nama Konsumen / Kavling" (rata kiri); Tgl Booking; Tgl Akad; Total Hasil Akad (tebal rata kanan); Total Sudah Cair (nominal hijau tebal + pill badge persentase cair); Aksi (Detail Siteplan & Kelola Modal).
      - Modal Detail "Total Pengajuan" & "Total Cair" dirampingkan menjadi 5 kolom: "Nama Konsumen / Kavling" gabungan rata kiri; Tgl Pengajuan; Status & Rincian (teks rincian baris 1, flexbox badge status di kiri dan tgl cair di kanan pada baris 2); Nominal (tebal rata kanan); Aksi.
    - `public/assets/css/laporan-hasil-akad.css`:
      - Penataan layout sel `.hasil-akad-consumer-cell` menyatu rata kiri.
      - Styling flexbox dua baris `.hasil-akad-rincian-status` dan `.hasil-akad-status-row` yang rapi dan ramping untuk menjaga kerapian vertikal baris tabel.
      - Styling pill badge persentase `.hasil-akad-percent-pill` (hijau/kuning) pada kolom pencairan.
  - **Deprecations:** Tidak ada. Zero-downtime kompatibel dengan seluruh parameter sebelumnya.
- **2026-11-09 [`UI_REPORT` / `FEATURE`] — Redesain Matriks, Modal Detail Dinamis, dan Standarisasi Hasil Akad:**
  - **Routes & Public Contracts:**
    - `POST /api/laporan/hasil-akad/summary`: Menambahkan key `total_akad` berdampingan dengan `total_penjualan` (backward-compatible).
    - `POST /api/laporan/hasil-akad/detail`: Mendukung category `'akad'` di samping `'penjualan'`, `'pengajuan'`, dan `'cair'`. Response detail menyertakan kolom `tipe_rumah`, `is_subsidi`, `booking_tgl`, `akad_tgl`, `harga_jual`, `total_sudah_cair`, `tanggal_pengajuan`, `tanggal_cair`.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/HasilAkadReportRepository.php`: Menambahkan query join ke tabel `tipe` dan `hargajual`; helper SQL `tipeRumahSql()`, `isSubsidiSql()`, `totalSudahCairSql()`; penanganan category `'akad'`; serta perluasan pencarian multi-kolom di `getDetailRows()`.
    - `app/Services/HasilAkadReportService.php`: Mendaftarkan `'akad'` pada `CATEGORIES`, mapping `total_akad` di `assignAkadRow` dan `emptyAmounts()`, serta menambahkan whitelist sorting/ordering columns baru pada DataTables request.
    - `tests/unit/HasilAkadReportServiceTest.php`: Unit test untuk validasi category baru `'akad'`, backward-compatibility `'penjualan'`, dan format response summary/detail.
  - **View / CSS / JS:**
    - `app/Views/laporan/hasil-akad.php`: Thead dinamis pada modal detail yang menyesuaikan struktur kolom berdasarkan kategori yang dipilih.
    - `public/assets/js/laporan-hasil-akad.js`: Mengeliminasi kolom redundant 'Total acc KPR' dari matriks dan chart; mengganti label 'Total Penjualan' menjadi 'Total Akad'; restrukturisasi modal detail dinamis (7 kolom untuk Total Akad, 6 kolom untuk Pengajuan & Pencairan); menambahkan helper modular `renderKonsumenKavling()`, `renderTipeRumah()`, `renderStatusRincian()`, dan `renderActionButtons()` (tombol aksi Detail Konsumen & Kelola Pencairan).
    - `public/assets/css/laporan-hasil-akad.css`: Penyesuaian layout responsive 3-kolom matriks per tahun, soft-tint badge status subsidi/komersil, rincian tanggal/nominal status, dan button group aksi.
  - **Deprecations:** Tidak ada (Zero-Downtime: parameter `penjualan` tetap didukung sebagai alias).
- **2026-11-04 [`BUGFIX`] — Penambahan Kolom status_tagihan dan Metadata Surat pada invoice_log:**
  - **Routes & Public Contracts:** Tidak berubah (endpoint `/keuangan/simpan_penagihan` dan `/keuangan/update_status_penagihan`).
  - **Controller / Service / Repo / Model:**
    - Pembuatan dan eksekusi migrasi database `app/Database/Migrations/2026-11-04-000001_AddStatusTagihanAndFieldsToInvoiceLog.php`.
    - Skema tabel `invoice_log` diperkaya dengan kolom `status_tagihan` (`VARCHAR(50)`, NOT NULL, DEFAULT `'draft'`), `tanggal_ubah_status` (`DATE`, NULL), `keterangan_status` (`TEXT`, NULL), dan `tanggal_kirim` (`DATETIME`, NULL) beserta index `idx_invoice_log_status_tagihan` (`status_tagihan`).
    - Backfill data legacy `invoice_log` ke status default `'draft'`.
    - Menyelesaikan runtime database error "Unknown column 'status_tagihan' in 'field list'" saat submit draft surat penagihan via `KeuanganService::simpanPenagihan`.
  - **View / CSS / JS:** Tidak berubah.
  - **Deprecations:** Tidak ada.
- `2026-11-04` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.
