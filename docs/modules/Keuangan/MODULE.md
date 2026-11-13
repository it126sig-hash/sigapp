# Module: Keuangan

**Status:** `MAPPED`  
**Readiness Score:** `80/100`  
**Last Synced:** `2026-11-13 21:06:50` @ `2e2ade2`

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
| `GET` | `/riwayat-bayar` | `Explicit` | `\App\Controllers\Web\Keuangan\TagihanController::riwayatBayarIndex` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/tagihan/list` | `Explicit` | `\App\Controllers\Web\Keuangan\TagihanController::listTagihan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/ambilsatu` | `Explicit` | `TagihanController::getByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/riwayat/ambilsatu` | `Explicit` | `TagihanController::getRiwayatByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/turunkpr` | `Explicit` | `TagihanController::tambahTurunKPR` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/hapusturunkpr` | `Explicit` | `TagihanController::hapusTurunKPR` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/hapus` | `Explicit` | `TagihanController::hapus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/void` | `Explicit` | `TagihanController::voidTagihan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/unvoid` | `Explicit` | `TagihanController::unvoidTagihan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/save` | `Explicit` | `TagihanController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/jatuhtempo` | `Explicit` | `TagihanController::getAllJatuhTempo` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/riwayat-bayar/ambil` | `Explicit` | `TagihanController::getRiwayatBayar` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/list/ambil` | `Explicit` | `TagihanController::getListTagihan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/list/ambil-grouped` | `Explicit` | `TagihanController::getListTagihanGrouped` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/list/detail` | `Explicit` | `TagihanController::getListTagihanDetail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/list/export-excel` | `Explicit` | `TagihanController::exportExcelTagihan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/tagihan/list/ambil-riwayat-surat` | `Explicit` | `TagihanController::getListRiwayatSurat` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/danaakad/list/ambilsatu` | `Explicit` | `\App\Controllers\Api\Keuangan\TagihanController::getListTagihan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/keuangan/tagihan/meta` | `Explicit` | `TagihanController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pembayaran/simpan` | `Explicit` | `\App\Controllers\Api\Keuangan\PembayaranController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pembayaran/hapus` | `Explicit` | `\App\Controllers\Api\Keuangan\PembayaranController::removeLP` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/keuangan/pembayaran/meta` | `Explicit` | `PembayaranController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/pembayaran/hitungulang` | `Explicit` | `\App\Controllers\Api\Keuangan\PembayaranController::recalculateSummary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/print/spptb` | `Explicit` | `PrintController::printSpptb` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/print/kuitansi` | `Explicit` | `PrintController::printKuitansi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/pembayaran/kuitansi/cetak` | `Explicit` | `\App\Controllers\Web\Keuangan\PrintController::printPembayaran` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/pembayaran/kuitansi-um/cetak` | `Explicit` | `\App\Controllers\Web\Keuangan\PrintController::printPembayaranUm` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/keuangan/print_tagihan` | `Explicit` | `\App\Controllers\Web\Keuangan\PrintController::printTagihan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/jatuhtempo/get` | `Explicit` | `\App\Controllers\Api\Keuangan\BankKprDisbursementController::getJatuhTempo` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-bank/get` | `Explicit` | `\App\Controllers\Api\Keuangan\BankKprDisbursementController::getData` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-bank/save` | `Explicit` | `\App\Controllers\Api\Keuangan\BankKprDisbursementController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-bank/void/(:num)` | `Explicit` | `\App\Controllers\Api\Keuangan\BankKprDisbursementController::void/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/keuangan/pencairan-bank/meta` | `Explicit` | `BankKprDisbursementController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/keuangan/hasil-akad/list` | `Explicit` | `\App\Controllers\Web\Keuangan\PencairanAkadController::listHasilAkad` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/keuangan/hasil-akad/export-template` | `Explicit` | `\App\Controllers\Web\Keuangan\PencairanAkadController::exportTemplate` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/keuangan/hasil-akad/import` | `Explicit` | `\App\Controllers\Web\Keuangan\PencairanAkadController::importForm` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/keuangan/pencairan-akad/meta` | `Explicit` | `PencairanAkadController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/get` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::get` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/plan/save-retensi` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::saveRetensi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/plan/save-tenor` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::saveTenor` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/pengajuan/store` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::storePengajuan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/pencairan/store` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::cairkan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/payment/list` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::listPayment` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/payment/void` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::voidPayment` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/void` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::void` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/keuangan/pencairan-akad/history/(:num)` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::history/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/batch/list` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::postBatchList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/batch/eligible-kavling` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::postEligibleKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/batch/pengajuan/store` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::postStoreBatchPengajuan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/batch/detail` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::postBatchDetail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/batch/pencairan/store` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::postCairkanBatch` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/batch/void` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::postVoidBatch` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/keuangan/pencairan-akad/batch/history/(:num)` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::getBatchHistory/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/hasil-akad/list/ambil-grouped` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::getListGrouped` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/hasil-akad/list/detail` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::getListDetail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/hasil-akad/import` | `Explicit` | `\App\Controllers\Api\Keuangan\PencairanAkadController::import` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/danaakad/getbyid` | `Explicit` | `\App\Controllers\Api\Keuangan\DanaJaminanController::getByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/getDanaAkad` | `Explicit` | `\App\Controllers\Api\Keuangan\DanaJaminanController::getDanaAkad` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/saveDanaAkad` | `Explicit` | `\App\Controllers\Api\Keuangan\DanaJaminanController::saveDanaAkad` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/keuangan/dana-jaminan/meta` | `Explicit` | `DanaJaminanController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/pencairan` | `Explicit` | `DanaJaminanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/pencairan/list/(:num)` | `Explicit` | `DanaJaminanController::list/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pencairan/store` | `Explicit` | `DanaJaminanController::store` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pencairan/cairkan/(:num)` | `Explicit` | `DanaJaminanController::cairkan/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/pencairan/history/(:num)` | `Explicit` | `DanaJaminanController::history/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pencairan/toggle/(:num)` | `Explicit` | `DanaJaminanController::toggleStatus/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/pencairan/download/(:num)` | `Explicit` | `DanaJaminanController::download/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/cashout/ambil` | `Explicit` | `\App\Controllers\Api\Keuangan\CashOutController::getByIDKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/cashout/listitem/ambil` | `Explicit` | `\App\Controllers\Api\Keuangan\CashOutController::getListItem` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/cashout/delete` | `Explicit` | `\App\Controllers\Api\Keuangan\CashOutController::delete` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/cashout/save` | `Explicit` | `\App\Controllers\Api\Keuangan\CashOutController::insert` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/keuangan/cashout/meta` | `Explicit` | `CashOutController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/cashout/kavling` | `Explicit` | `\App\Controllers\Web\Keuangan\Cashout\CashoutKavlingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/keuangan/cashout-kavling/meta` | `Explicit` | `Cashout\CashoutKavlingController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/kavling/list` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutKavlingController::getDataTables` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/kavling/detail-list` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutKavlingController::getDetailList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/cashout/subkon` | `Explicit` | `\App\Controllers\Web\Keuangan\Cashout\CashoutSubkonController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/keuangan/cashout-subkon/meta` | `Explicit` | `Cashout\CashoutSubkonController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/subkon/list` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController::getDataTables` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/subkon/detail-list` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController::getDetailList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/subkon/ambil` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController::get` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/subkon/save` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/subkon/turun-jatuh-tempo` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController::turunJatuhTempo` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/subkon/ajukan-spp` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController::ajukanSPP` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/subkon/history` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController::getHistory` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/subkon/ajukan-pencairan` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController::ajukanPencairan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/cashout/subkon/pembayaran` | `Explicit` | `\App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController::pembayaran` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/list_bank/index` | `Explicit` | `\App\Controllers\Web\Keuangan\BankController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/list_bank` | `Explicit` | `\App\Controllers\Web\Keuangan\BankController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/listbank` | `Explicit` | `\App\Controllers\Web\Keuangan\BankController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/bank/meta` | `Explicit` | `BankController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/bank/ambil` | `Explicit` | `BankController::ambil` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/bank/list` | `Explicit` | `BankController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bank/ambilsatu` | `Explicit` | `BankController::ambilSatu` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bank/simpan` | `Explicit` | `BankController::simpan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bank/hapus` | `Explicit` | `BankController::hapus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tagihan/booking/verifikasi` | `Explicit` | `BookingPaymentController::verify` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/laporan/cash-in` | `Explicit` | `\App\Controllers\Web\Keuangan\CashInReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/laporan/cash-in/meta` | `Explicit` | `CashInReportController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/cash-in/summary` | `Explicit` | `CashInReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/cash-in/detail` | `Explicit` | `CashInReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/laporan/hasil-akad` | `Explicit` | `\App\Controllers\Web\Keuangan\HasilAkadReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/laporan/hasil-akad/meta` | `Explicit` | `HasilAkadReportController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/hasil-akad/summary` | `Explicit` | `HasilAkadReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/hasil-akad/detail` | `Explicit` | `HasilAkadReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/keuangan/getdanaakad` | `AutoRoute` | `Keuangan::getDanaAkad` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/getbankkprdisbursement` | `AutoRoute` | `Keuangan::getBankKprDisbursement` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/savebankkprdisbursement` | `AutoRoute` | `Keuangan::saveBankKprDisbursement` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/voidbankkprdisbursement` | `AutoRoute` | `Keuangan::voidBankKprDisbursement` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/getjatuhtempo` | `AutoRoute` | `Keuangan::getJatuhTempo` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
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
| `ANY` | `/pajak/getone` | `AutoRoute` | `Pajak::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/save` | `AutoRoute` | `Pajak::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/if_where` | `AutoRoute` | `Pajak::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/is_active` | `AutoRoute` | `Pajak::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/format_tgl` | `AutoRoute` | `Pajak::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
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
- `getListItemKeuangan()`
- `getTagihanBundle(int $idMkdt, bool $includeLog = true)`
- `formatBiayaMkdt($mkdt)`
- `saveTagihan($request, int $actorId)`
- `getJatuhTempoByProyek(?int $idProyek = null)`
- `getListTagihanRaw($request, $status = null)`
- `getListTagihanGroupedRaw($request)`
- `getRiwayatBayarRaw($request)`
- `getListRiwayatSuratRaw($request)`
- `setTanggalKirim($no_inv)`
#### `PembayaranService.php`
- `simpan($data)`
- `recalculateSummary($id_mkdt)`
- `recalculateAllSummaries()`
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
- `getBanksList()`
- `canUserManage()`
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
- `getListGroupedRaw($request)`
- `getListDetail(int $idMkdt)`
- `getHistory(int $idKavling)`
- `findLockedMismatch(array $row, object $item)`
- `getListDajam()`
- `getMetaPayload()`
- `generateBatchNumber()`
- `syncBatchStatus(?int $idBatch, int $actorId)`
- `getEligibleBatchKavling(array $idKavlings = [], int $idProyek = 0)`
- `storeBatchPengajuan(array $data, $lampiranFile, int $actorId)`
- `getBatchList($request)`
- `getBatchListRaw($request)`
- `getBatchDetail(int $idBatch)`
- `cairkanBatch(array $post, int $actorId)`
- `voidBatch(int $idBatch, string $reason, int $actorId)`
- `getBatchHistory(int $idBatch)`
#### `CashOutService.php`
- `getListItem(string $search = '')`
- `getListItemSelect2(string $search = '')`
- `getMetaPayload()`
- `getByIDKavling(int $idKavling)`
- `konsumenExistsForKavling(int|string $idKavling)`
- `formatBiayaMkdt(?object $mkdt)`
- `saveCashOut(array $input, ?int $userId = null)`
- `deleteCashOut(int $id)`
- `insert($data)`
- `delete($id)`
- `format_tgl($tgl)`
- `is_active($id, $texts, $textf)`
#### `CashoutKavlingService.php`
- `getIndexData(?int $idProyek)`
- `getMetaPayload(?int $idProyek)`
- `getDataTables(array $var)`
- `getDataTablesRaw(array $var)`
- `getDetailList(int $idKavling)`
#### `CashoutSubkonService.php`
- `getIndexData()`
- `getMetaPayload()`
- `getDataTables(array $var)`
- `getDataTablesRaw(array $var)`
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
- `printTagihan(string $noInv, int $actorId)`
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
- `getListDajam()`
- `getMetaPayload()`
- `getDownloadFile(int $idPengajuan)`
- `getDanaAkadDetail(int $idMkdt, int $idKavling = 0)`

## File Inventory
### Controllers (23)
- `app/Controllers/Keuangan.php` (1923 baris, 32 public methods: `getDanaAkad()`, `getBankKprDisbursement()`, `saveBankKprDisbursement()`, `voidBankKprDisbursement($id = null)`, `getJatuhTempo()`, `getCashOut()`, `saveCashOut()`, `saveDanaAkad()`, `get_riwayat_gantinama()`, `isSudahBayar($id)`, `isi_tagihan()`, `save_sb()`, `save()`, `list_tagihan()`, `getListTagihan()`, `riwayat_bayar()`, `getRiwayatBayar()`, `get_tagihan($id = null)`, `removeLP()`, `doPrint()`, `print_tagihan($id = null)`, `if_where($var, $column, $condition, $query)`, `format_tgl($tgl)`, `is_active($id, $texts, $textf)`, `get_riwayat_tagihan()`, `simpan_penagihan()`, `update_status_penagihan()`, `download_penagihan()`, `get_kopsurat_list()`, `get_kopsurat_detail()`, `simpan_kopsurat()`, `hapus_kopsurat()`)
- `app/Controllers/Web/Keuangan/TagihanController.php` (36 baris, 2 public methods: `listTagihan()`, `riwayatBayarIndex()`)
- `app/Controllers/Api/Keuangan/TagihanController.php` (380 baris, 16 public methods: `meta()`, `getByID()`, `getRiwayatByID()`, `hapusTurunKPR()`, `tambahTurunKPR()`, `hapus()`, `voidTagihan()`, `unvoidTagihan()`, `save()`, `getListTagihan($status = null)`, `getListTagihanGrouped()`, `exportExcelTagihan()`, `getListTagihanDetail()`, `getRiwayatBayar()`, `getListRiwayatSurat()`, `getAllJatuhTempo()`)
- `app/Controllers/Api/Keuangan/PembayaranController.php` (97 baris, 4 public methods: `meta()`, `save()`, `removeLP()`, `recalculateSummary()`)
- `app/Controllers/Web/Keuangan/PrintController.php` (124 baris, 5 public methods: `printSpptb()`, `printKuitansi()`, `printPembayaran()`, `printPembayaranUm()`, `printTagihan()`)
- `app/Controllers/Api/Keuangan/BankKprDisbursementController.php` (121 baris, 5 public methods: `meta()`, `getJatuhTempo()`, `getData()`, `save()`, `void($id = null)`)
- `app/Controllers/Web/Keuangan/PencairanAkadController.php` (60 baris, 3 public methods: `listHasilAkad()`, `importForm()`, `exportTemplate()`)
- `app/Controllers/Api/Keuangan/PencairanAkadController.php` (332 baris, 20 public methods: `meta()`, `get()`, `saveRetensi()`, `saveTenor()`, `storePengajuan()`, `cairkan()`, `listPayment()`, `voidPayment()`, `void()`, `history($id_kavling = null)`, `getListGrouped()`, `getListDetail()`, `import()`, `postBatchList()`, `postEligibleKavling()`, `postStoreBatchPengajuan()`, `postBatchDetail()`, `postCairkanBatch()`, `postVoidBatch()`, `getBatchHistory($id = null)`)
- `app/Controllers/Api/Keuangan/DanaJaminanController.php` (172 baris, 11 public methods: `meta()`, `index()`, `list($id_kavling = null)`, `store()`, `cairkan($id_pengajuan = null)`, `history($id_kavling = null)`, `toggleStatus($id_pengajuan = null)`, `download($id_pengajuan = null)`, `getByID()`, `getDanaAkad()`, `saveDanaAkad()`)
- `app/Controllers/Pajak.php` (589 baris, 5 public methods: `getOne()`, `save()`, `if_where($var, $column, $condition, $query)`, `is_active($id, $texts, $textf)`, `format_tgl($tgl)`)
- `app/Controllers/Api/Keuangan/CashOutController.php` (159 baris, 6 public methods: `initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)`, `meta()`, `getListItem()`, `getByIDKavling()`, `insert()`, `delete($id = null)`)
- `app/Controllers/Web/Keuangan/Cashout/CashoutKavlingController.php` (49 baris, 2 public methods: `initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)`, `index()`)
- `app/Controllers/Api/Keuangan/Cashout/CashoutKavlingController.php` (94 baris, 4 public methods: `initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)`, `meta()`, `getDataTables()`, `getDetailList()`)
- `app/Controllers/Web/Keuangan/Cashout/CashoutSubkonController.php` (36 baris, 2 public methods: `initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)`, `index()`)
- `app/Controllers/Api/Keuangan/Cashout/CashoutSubkonController.php` (177 baris, 11 public methods: `initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)`, `meta()`, `getDataTables()`, `getDetailList()`, `get()`, `save()`, `turunJatuhTempo()`, `ajukanSPP()`, `ajukanPencairan()`, `pembayaran()`, `getHistory()`)
- `app/Controllers/Web/Keuangan/BankController.php` (28 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Keuangan/BankController.php` (165 baris, 6 public methods: `meta()`, `list()`, `ambil()`, `ambilSatu()`, `simpan()`, `hapus()`)
- `app/Controllers/Api/Keuangan/BookingPaymentController.php` (67 baris, 1 public methods: `verify()`)
- `app/Controllers/Web/Keuangan/CashInReportController.php` (51 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Keuangan/CashInReportController.php` (131 baris, 3 public methods: `meta()`, `summary()`, `detail()`)
- `app/Controllers/Web/Keuangan/HasilAkadReportController.php` (49 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Keuangan/HasilAkadReportController.php` (125 baris, 3 public methods: `meta()`, `summary()`, `detail()`)
- `app/Controllers/Direksi.php` (416 baris, 12 public methods: `get_data_by_id($st = null)`, `save()`, `if_where($var, $column, $condition, $query)`, `format_tgl($tgl)`, `is_active($id, $texts, $textf)`, `export_xlsx()`, `tagihan()`, `get_pending_tagihan()`, `get_my_signature()`, `sign_tagihan()`, `get_history_tagihan()`, `reject_tagihan()`)

### Services (16)
- `app/Services/Keuangan/KeuanganService.php` (2470 baris, 43 public methods)
- `app/Services/Keuangan/PembayaranService.php` (294 baris, 4 public methods)
- `app/Services/Keuangan/PajakService.php` (45 baris, 2 public methods)
- `app/Services/Keuangan/FinanceLedgerService.php` (596 baris, 16 public methods)
- `app/Services/Keuangan/BankKprDisbursementService.php` (419 baris, 5 public methods)
- `app/Services/Keuangan/BankService.php` (52 baris, 5 public methods)
- `app/Services/Keuangan/BookingPaymentService.php` (332 baris, 8 public methods)
- `app/Services/Keuangan/BookingFeeMigrationService.php` (234 baris, 2 public methods)
- `app/Services/Keuangan/PencairanAkadService.php` (2315 baris, 28 public methods)
- `app/Services/Keuangan/CashOutService.php` (274 baris, 12 public methods)
- `app/Services/Keuangan/Cashout/CashoutKavlingService.php` (294 baris, 5 public methods)
- `app/Services/Keuangan/Cashout/CashoutSubkonService.php` (919 baris, 13 public methods)
- `app/Services/Keuangan/CashInReportService.php` (212 baris, 4 public methods)
- `app/Services/Keuangan/HasilAkadReportService.php` (199 baris, 3 public methods)
- `app/Services/Keuangan/PrintService.php` (720 baris, 7 public methods)
- `app/Services/Keuangan/DanaJaminanService.php` (739 baris, 11 public methods)

### Repositories (14)
- `app/Repositories/Keuangan/KeuanganRepository.php` (571 baris, 13 public methods: `getTagihanById(int $idMkdt)`, `getTagihanTurunKPRById(int $idMkdt)`, `getAllJatuhTempo($id_proyek)`, `getLIKeu()`, `getBelumLunasQuery()`, `getBelumLunasGroupedQuery()`, `getAllGroupedQuery()`, `getLunasGroupedQuery()`, `getListTagihanJatuhTempoDetailById(int $idMkdt)`, `getListTagihanDetailById(int $idMkdt)`, `getTagihanOnlyByID(int $idMkdt)`, `getJatuhTempoByProyek(?int $idProyek = null)`, `getRiwayatSuratQuery(int $idProyek, $idCluster = null, $idJalan = null)`)
- `app/Repositories/Keuangan/LogPembayaranRepository.php` (228 baris, 11 public methods: `getRiwayatBayarById(int $idMkdt)`, `getRiwayatBayarQuery($id_proyek, $id_cluster = null, $id_jalan = null)`, `softDeleteAndReturnIdMkdt($idPembayaran)`, `getRiwayatBayarByIdPembayran($id)`, `getDetailRiwayatBayarById(int $id_Pembayaran)`, `getTotalBayarByIdMkdt(int $id_Mkdt)`, `getPaidItemSummaryByIdMkdt(int $id_Mkdt)`, `getDetailRiwayatBayarByIdMkdt(int $id_Mkdt)`, `insertDetail($data)`, `hasRecentDuplicate(int $idMkdt, string $idKeuangan, $nominal, string $tanggalBayar, string $paymentType, int $seconds = 30)`, `getDistinctActiveMkdtIds()`)
- `app/Repositories/Keuangan/PaymentSummaryRepository.php` (74 baris, 3 public methods: `updateCategory(int $idMkdt, string $kategori, float $amount)`, `setToZero(int $id_mkdt)`, `setCategory(int $idMkdt, string $kategori, float $value)`)
- `app/Repositories/Keuangan/FinanceLedgerRepository.php` (77 baris, 4 public methods: `findBySource(string $sourceType, int $sourceId)`, `upsertBySource(array $payload)`, `voidBySource(string $sourceType, int $sourceId, ?int $actorId = null)`, `sumIncome(array $filters = [])`)
- `app/Repositories/Keuangan/PajakRepository.php` (41 baris, 1 public methods: `linkKavling(int $idKavling, int $idPajak)`)
- `app/Repositories/Keuangan/BookingPaymentRepository.php` (166 baris, 14 public methods: `findProjectIdByMkdt(int $idMkdt)`, `mkdt(int $id, bool $lock = false)`, `link(int $id, bool $lock = false)`, `owners(int $payment)`, `payment(int $id)`, `details(int $payment)`, `bookingRows(int $id, bool $deleted = false)`, `item(string $category, ?string $name = null)`, `write(string $table, array $data, array $where = [])`, `saveLink(int $id, ?int $payment, array $extra = [])`, `separateBookingSql(string $detail = 'lpd', string $item = 'kl')`, `effectiveCategorySql(string $detail = 'lpd', string $item = 'kl')`, `installmentSummary(int $id)`, `recalculate(int $id)`)
- `app/Repositories/Keuangan/PencairanAkadListRepository.php` (157 baris, 2 public methods: `buildListQuery(array $filters)`, `getSummary(array $filters, string $search = '')`)
- `app/Repositories/Keuangan/CashOutRepository.php` (88 baris, 5 public methods: `getListItem(string $search = '')`, `getListItemSelect2(string $search = '')`, `getKavlingDetail(int $idKavling)`, `getRiwayatBayarCashOutByIDKavling(int $id_kavling)`, `softDelete($id)`)
- `app/Repositories/Keuangan/Cashout/CashoutKavlingRepo.php` (228 baris, 3 public methods: `getDataTables(array $var)`, `getDetailList(int $idKavling)`, `getCashoutRowsByKavling(int $idKavling)`)
- `app/Repositories/Keuangan/Cashout/CashoutSubkonRepo.php` (455 baris, 20 public methods: `getSubkonByID(int $id_subkon)`, `getDataTables(array $var)`, `getDetailsByCashoutSubkonIds(array $ids)`, `getListCashoutKavling(array $id_kavlings)`, `getCashoutSubkonByID(int $id_cashout_subkon)`, `getKavlingSubkonByID(int $id_cashout_subkon)`, `getListItemDetailByIDCashoutsubkon(int $id_cashout_subkon)`, `upsertSubkon($id_subkon, array $data)`, `saveCashoutSubkon(array $data)`, `saveCashoutSubkonDetail(array $data)`, `getKavlingBySubkonId(int $id_cashout_subkon)`, `getDetailByID(int $id_detail)`, `updateDetail(int $id_detail, array $data)`, `saveHistory(int $id_cashout_subkon, string $keterangan, int $status)`, `getDetailByCashoutSubkonID(int $id_cashout_subkon)`, `syncAutomaticDetailAllocations(int $id_cashout_subkon, array $id_kavlings)`, `getAllocationsByDetailID(int $id_cashout_subkon_detail)`, `updateCashoutSubkonStatus(int $id_cashout_subkon, int $status)`, `saveCashoutSubkonKavling(array $data)`, `getHistoryByIDCashoutSubkon($id_cashout_subkon)`)
- `app/Repositories/Keuangan/CashInReportRepository.php` (233 baris, 4 public methods: `getAvailableYearBounds(int $idProyek)`, `getMonthlyPaymentTotals(int $idProyek, array $years)`, `getMonthlyAkadTotals(int $idProyek, array $years)`, `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable)`)
- `app/Repositories/Keuangan/HasilAkadReportRepository.php` (363 baris, 5 public methods: `getAvailableYearBounds(int $idProyek)`, `getMonthlyAkadTotals(int $idProyek, array $years)`, `getMonthlyPengajuanTotals(int $idProyek, array $years)`, `getMonthlyPaymentTotals(int $idProyek, array $years)`, `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable)`)
- `app/Repositories/Keuangan/SpptbRepository.php` (35 baris, 2 public methods: `getLatestByMkdtId(int $idMkdt, int $limit = 3)`, `insertFile(int $idMkdt, string $lokasi, int $actorId)`)
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
- `public/assets/js/siteplan/keuangan.js` (2194 baris)
- `public/assets/js/siteplan/pajak.js` (240 baris)
- `public/assets/js/siteplan/cashout_subkon.js` (1062 baris)
- `public/assets/js/tagihan-bayar-modal.js` (1407 baris)
- `public/assets/js/pencairan-akad-modal.js` (1266 baris)
- `public/assets/js/keuangan/penagihan.js` (1041 baris)
- `public/assets/js/cashout_kavling/index.js` (128 baris)
- `public/assets/js/laporan-cash-in.js` (473 baris)
- `public/assets/js/laporan-hasil-akad.js` (721 baris)

### Zero-Downtime Shims (41 file)
- `app/Controllers/Tagihan.php`
- `app/Controllers/Pembayaran.php`
- `app/Controllers/PrintController.php`
- `app/Controllers/PencairanAkad.php`
- `app/Controllers/PencairanJaminan.php`
- `app/Controllers/DanaAkad.php`
- `app/Controllers/CashOut.php`
- `app/Controllers/Keuangan/Cashout/CashoutKavling.php`
- `app/Controllers/Keuangan/Cashout/CashoutSubkon.php`
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
- **Controller (`app/Controllers/Web/Keuangan/TagihanController.php`):**
  - `listTagihan()`: Merender halaman utama daftar tagihan (`keuangan/list-tagihan`) dengan preload daftar item keuangan (`li_keu`) dari `KeuanganService::getListItemKeuangan()`.
  - `riwayatBayarIndex()`: Merender halaman riwayat pembayaran (`keuangan/riwayat-bayar`).
- **Controller (`app/Controllers/Api/Keuangan/TagihanController.php`):**
  - `meta()`: Mengembalikan metadata `active_project`, `list_item_keuangan`, dan `permissions` (`can_view`, `can_create_tagihan`, `can_pay`, `can_void`, `can_delete`).
  - `getByID()`, `getRiwayatByID()`: Mengambil bundle lengkap tagihan MKDT (`getTagihanBundle()`) dan detail log pembayaran (`getRiwayatBayarWithDetailById()`), mendukung dual-mode `?format=raw`.
  - `tambahTurunKPR()`, `hapusTurunKPR()`, `hapus()`, `voidTagihan()`, `unvoidTagihan()`, `save()`: Mutasi tagihan MKDT dan item Turun KPR yang didelegasikan ke `KeuanganService` (termasuk `saveTagihan()`), mendukung dual-mode `?format=raw`.
  - `getListTagihan()`, `getListTagihanGrouped()`, `getListTagihanDetail()`, `getRiwayatBayar()`, `getListRiwayatSurat()`, `exportExcelTagihan()`, `getAllJatuhTempo()`: Query DataTables/daftar tagihan, riwayat bayar, riwayat surat penagihan, export Excel, dan jatuh tempo proyek dengan dukungan dual-mode `?format=raw`.
- **Controller (`app/Controllers/Api/Keuangan/PembayaranController.php`):**
  - `meta()`: Mengembalikan metadata `active_project`, `list_item_keuangan`, dan `permissions` (`can_view`, `can_pay`, `can_delete`, `can_recalculate`).
  - `save()`, `removeLP()`, `recalculateSummary()`: Menyimpan pembayaran (`PembayaranService::simpan()`), menghapus/void pembayaran (`PembayaranService::removeLP()`), dan menghitung ulang seluruh summary pembayaran aktif (`PembayaranService::recalculateAllSummaries()`), dengan dukungan dual-mode `?format=raw`.
- **Controller (`app/Controllers/Web/Keuangan/PrintController.php`):**
  - `printSpptb()`, `printKuitansi()`, `printPembayaran()`, `printPembayaranUm()`, `printTagihan()`: Merender PDF SPPTB, kuitansi pembayaran, kuitansi uang muka, dan surat tagihan/invoice via `PrintService`, dengan penanganan `\DomainException` (`403`/`404`) pada `handleKuitansiPrint()` dan `printTagihan()`.
- **Controller (`app/Controllers/Api/Keuangan/BankKprDisbursementController.php`):**
  - `meta()`: Mengembalikan metadata `active_project`, daftar `banks`, `statuses` (`draft`, `cair`, `void`), dan `permissions.can_manage`.
  - `getJatuhTempo()`, `getData()`, `save()`, `void($id = null)`: Mengambil data jatuh tempo per proyek (`KeuanganService::getJatuhTempoByProyek()`) serta orkestrasi CRUD/void pencairan bank KPR via `BankKprDisbursementService` dengan dukungan dual-mode `?format=raw`.
- **Controller (`app/Controllers/Web/Keuangan/PencairanAkadController.php`):**
  - `listHasilAkad()`, `importForm()`, `exportTemplate()`: Merender halaman list hasil akad (`keuangan/list-hasil-akad`), form import pencairan (`keuangan/pencairan-akad-import` dengan pembatasan grup `[1, 3]`), serta mengunduh template CSV dari `PencairanAkadService::exportTemplate()`.
- **Controller (`app/Controllers/Api/Keuangan/PencairanAkadController.php`):**
  - `meta()`: Mengembalikan metadata `active_project`, `list_dajam`, `template_columns`, `statuses`, dan `permissions` (`can_view`, `can_manage`, `can_import`, `can_export`, `can_cairkan`, `can_void`).
  - `get()`, `saveRetensi()`, `saveTenor()`, `storePengajuan()`, `cairkan()`, `listPayment()`, `voidPayment()`, `void()`, `history($id_kavling = null)`: Orkestrasi pencairan akad per kavling dengan RBAC `checkKeuanganAccess()` dan dukungan dual-mode `?format=raw` via `respondDual()`.
  - `getListGrouped()`, `getListDetail()`, `import()`: Daftar grouped hasil akad (dual-mode `getListGroupedRaw()` vs DataTables), detail per `id_mkdt`, dan import tanggal cair CSV + lampiran surat.
  - `postBatchList()`, `postEligibleKavling()`, `postStoreBatchPengajuan()`, `postBatchDetail()`, `postCairkanBatch()`, `postVoidBatch()`, `getBatchHistory($id = null)`: Manajemen pencairan akad berbasis batch (dual-mode `getBatchListRaw()` vs DataTables).
- **Controller (`app/Controllers/Api/Keuangan/DanaJaminanController.php`):**
  - `meta()`: Mengembalikan metadata `active_project`, `list_dajam`, `statuses`, dan `permissions` (`can_view`, `can_manage`, `can_cairkan`).
  - `index()`, `list($id_kavling = null)`, `store()`, `cairkan($id_pengajuan = null)`, `history($id_kavling = null)`, `toggleStatus($id_pengajuan = null)`, `download($id_pengajuan = null)`: Endpoint konsolidasi dari legacy `PencairanJaminan` ke `DanaJaminanService` (termasuk `getDownloadFile()` pada mode `?format=raw` maupun redirect signed URL legacy).
  - `getByID()`, `getDanaAkad()`, `saveDanaAkad()`: Endpoint konsolidasi dari legacy `DanaAkad` dan `Keuangan` untuk detail (`getDanaAkadDetail()`) serta pembacaan/penyimpanan dana akad (`getData()`, `saveDanaAkad()`), mendukung dual-mode `?format=raw`.
- **Controller (`app/Controllers/Web/Keuangan/BankController.php`):** `index()` (Merender halaman master List Bank via `BankService::getList()`).
- **Controller (`app/Controllers/Api/Keuangan/BankController.php`):** `meta()` (Metadata daftar bank, bank aktif, dan izin akses), `list()` (Dual-mode JSON `?format=raw` dan DataTables HTML legacy), `ambil()`, `ambilSatu()` (Dual-mode `?format=raw`), `simpan()`, `hapus()`.
- **Controller (`app/Controllers/Web/Keuangan/CashInReportController.php`):** `index()` (Merender halaman Laporan Cash In dengan validasi akses menu & proyek aktif).
- **Controller (`app/Controllers/Api/Keuangan/CashInReportController.php`):** `meta()` (Metadata proyek aktif, tahun tersedia, kategori, dan permissions), `summary()`, `detail()` (Dual-mode `?format=raw` dan DataTables payload).
- **Controller (`app/Controllers/Web/Keuangan/HasilAkadReportController.php`):** `index()` (Merender halaman Laporan Hasil Akad dengan validasi akses menu & proyek aktif).
- **Controller (`app/Controllers/Api/Keuangan/HasilAkadReportController.php`):** `meta()` (Metadata proyek aktif, tahun tersedia, kategori, dan permissions), `summary()`, `detail()` (Dual-mode `?format=raw` dan DataTables payload).
- **Controller (`app/Controllers/Api/Keuangan/BookingPaymentController.php`):** `verify()` (Validasi role `[1, 3]`, validasi nominal/tanggal, cek kepemilikan proyek via `findProjectIdByMkdt()` + `ActiveProyekService::userCanAccess()`, dan verifikasi booking fee melalui `BookingPaymentService::verify()`).
- **Controller (`app/Controllers/Api/Keuangan/CashOutController.php`):**
  - `meta()`: Mengembalikan metadata `active_project`, `list_item` & `items` (dari `CashOutService::getMetaPayload()`), dan `permissions` (`can_view`, `can_manage`, `can_create`, `can_delete`).
  - `getListItem()`, `getByIDKavling()`, `insert()`, `delete($id = null)`: Endpoint daftar item cashout, bundel data kavling & riwayat bayar, simpan cashout, dan soft delete cashout via `CashOutService` dengan dukungan dual-mode `?format=raw` (`404`/`422` pada mode raw).
- **Controller (`app/Controllers/Web/Keuangan/Cashout/CashoutKavlingController.php`):** `index()` (Merender `cashout_kavling/index` dengan preload `CashoutKavlingService::getIndexData()`, redirect ke `/dashboard` jika proyek aktif belum dipilih).
- **Controller (`app/Controllers/Api/Keuangan/Cashout/CashoutKavlingController.php`):**
  - `meta()`: Mengembalikan metadata `active_project`, `pph`, `ppn`, `checklist_subitems`, dan `permissions` (`can_view`, `can_keuangan`, `can_produksi`, `can_pajak`).
  - `getDataTables()`, `getDetailList()`: Rekap cashout per kavling (dual-mode `getDataTablesRaw()` vs DataTables HTML) dan rincian per `id_kavling` (`422` pada mode raw jika tidak valid).
- **Controller (`app/Controllers/Web/Keuangan/Cashout/CashoutSubkonController.php`):** `index()` (Merender `cashout_subkon/index` dengan data dari `CashoutSubkonService::getIndexData()`).
- **Controller (`app/Controllers/Api/Keuangan/Cashout/CashoutSubkonController.php`):**
  - `meta()`: Mengembalikan metadata `active_project`, `statuses`, `termin_statuses`, dan `permissions` (`can_view`, `can_manage`, `can_create_spk`, `can_turun_jatuh_tempo`, `can_ajukan_spp`, `can_ajukan_pencairan`, `can_pembayaran`).
  - `getDataTables()`, `getDetailList()`, `get()`, `save()`, `turunJatuhTempo()`, `ajukanSPP()`, `ajukanPencairan()`, `pembayaran()`, `getHistory()`: Orkestrasi seluruh siklus SPK cashout subkon via `CashoutSubkonService`, mendukung dual-mode `?format=raw` (`getDataTablesRaw()` & helper `respondSubkonDual()`).
- **Service (`app/Services/Keuangan/KeuanganService.php`):**
  - `saveTagihan($request, int $actorId)`: Orkestrasi transaksional simpan/perbarui data konsumen & MKDT, sinkronisasi rincian tagihan (`syncTagihan()`), unggah berkas SPPTB & surat kuasa via `SpptbRepository::insertFile()`, serta pengiriman notifikasi booking/perubahan data konsumen.
  - `getTagihanBundle(int $idMkdt, bool $includeLog = true)` & `formatBiayaMkdt($mkdt)`: Menyusun bundel lengkap transaksi MKDT, komponen biaya, daftar tagihan, ringkasan angsuran/booking, serta riwayat pembayaran.
  - `getListItemKeuangan()` & `getJatuhTempoByProyek(?int $idProyek = null)`: Mengambil daftar item keuangan aktif (`KeuanganRepository::getLIKeu()`) dan daftar jatuh tempo konsumen per proyek (`KeuanganRepository::getJatuhTempoByProyek()`).
  - `getListTagihanRaw($request, $status = null)`, `getListTagihanGroupedRaw($request)`, `getRiwayatBayarRaw($request)`, `getListRiwayatSuratRaw($request)`: Varian JSON terstruktur (`items` + `meta` paginasi) untuk kontrak Dual-Mode `?format=raw`, dilengkapi guard internal `ensureDataTableRequestDefaults($request)` agar parameter `columns` & `search` DataTables aman ketika endpoint dipanggil tanpa payload DataTables standar.
  - `simpanIsiTagihan($request, int $actorId)`, `simpanDanaAkad($request, int $actorId)`, `simpanInvoice($request, int $actorId)`, `updateSudahDibayar($request)`, `syncTagihan(int $idMkdt, array $um, int $actorId)`, `simpanPenagihan($request, int $actorId)` (Validasi kop surat, item tagihan, nomor surat duplikat, dan persistensi invoice log berstatus draft/publish), `updateStatusPenagihan($request, int $actorId)` (Transisi status tagihan dan pencatatan audit trail status log), `setTanggalKirim($no_inv)`.
- **Service (`app/Services/Keuangan/PembayaranService.php`):**
  - `simpan($data)`: Validasi nominal pembayaran (`> 0`), validasi kecocokan total alokasi per item (`abs($total - $nominalBayar) <= 0.001`), deteksi duplikat terbaru, validasi alokasi booking/breakdown MKDT, persistensi `log_pembayaran` + detail, pencatatan income di `FinanceLedgerService`, hitung ulang summary, sinkronisasi lunas, serta notifikasi rekonsiliasi jika ditemukan selisih.
  - `recalculateAllSummaries()`: Menghitung ulang ringkasan pembayaran seluruh `id_mkdt` aktif dari `LogPembayaranRepository::getDistinctActiveMkdtIds()`.
  - `recalculateSummary($id_mkdt)`, `removeLP($request)`.
- **Service (`app/Services/Keuangan/PajakService.php`):** `insertPajak(array $fields, int $idKavling, int $actorId)`, `updatePajak(int $idPajak, array $fields, int $actorId)`
- **Service (`app/Services/Keuangan/FinanceLedgerService.php`):** `recordIncomeFromLogPembayaran(int $idPembayaran, ?int $actorId = null)`, `voidByLogPembayaran(int $idPembayaran, ?int $actorId = null)`, `recordIncomeFromDanaJaminan(int $idDanaAkad, int $idMkdt, ?int $actorId = null)`, `voidByDanaJaminan(int $idDanaAkad, ?int $actorId = null)`, `recordIncomeFromBankKprDisbursement(int $idDisbursement, ?int $actorId = null)`
- **Service (`app/Services/Keuangan/BankKprDisbursementService.php`):** `getData(int $idMkdt, int $idKavling)`, `save($request, int $actorId)`, `void(int $id, $request, int $actorId)`, `getBanksList()` (ekspos daftar bank untuk endpoint `/meta`), `canUserManage()` (validasi hak akses kelola pencairan bank untuk endpoint `/meta`).
- **Service (`app/Services/Keuangan/BankService.php`):** `getAllActive()`, `getList(?string $search = null)`, `getOne(int $id)`, `simpan(array $data, ?int $id = null)`, `hapus(int $id)`
- **Service (`app/Services/Keuangan/BookingPaymentService.php`):** `findProjectIdByMkdt(int $idMkdt)`, `assertEditable(int $idMkdt, float $nominal, ?string $tanggal)`, `synchronize(int $idMkdt, ?int $actorId = null)`, `verify(int $idMkdt, int $actorId, ?float $nominal = null, ?string $tanggal = null)`, `assertMayDelete(int $paymentId)`, `assertManualAllocationAllowed(int $idMkdt, array $allocations)`
- **Service (`app/Services/Keuangan/BookingFeeMigrationService.php`):** `run()`, `snapshot()`
- **Service (`app/Services/Keuangan/PencairanAkadService.php`):** `getData(int $idMkdt, int $idKavling)`, `saveRetensi(array $post, int $actorId)`, `saveTenor(array $post, int $actorId)`, `storePengajuan($request, int $actorId)`, `cairkan($request, int $actorId)`, `listPayment(int $idPengajuan)`, `voidPayment(int $idPayment, string $reason, int $actorId)`, `void(int $idPengajuan, string $reason, int $actorId)` (dibungkus DB transaction untuk pembaruan status void dan sinkronisasi status batch), `getListDajam()`, `getMetaPayload()`, `getListGrouped($request)`, `getListGroupedRaw($request)` (payload JSON terstruktur + paginasi untuk `?format=raw`), `buildBatchListQuery($request)`, `getBatchList($request)`, `getBatchListRaw($request)` (payload JSON terstruktur + paginasi batch untuk `?format=raw`), `exportTemplate(array $filter)`, `importTanggalCair(string $csvPath, $lampiranFile, int $actorId)`, `getEligibleBatchKavling(array $idKavlings = [], int $idProyek = 0)`, `storeBatchPengajuan(array $data, $lampiranFile, int $actorId)`, `getBatchDetail(int $idBatch)`, `cairkanBatch(array $post, int $actorId)`, `voidBatch(int $idBatch, string $reason, int $actorId)`, `getBatchHistory(int $idBatch)`.
- **Service (`app/Services/Keuangan/CashOutService.php`):** `getListItem(string $search = '')`, `getListItemSelect2(string $search = '')` (format item Select2), `getMetaPayload()`, `getByIDKavling(int $idKavling)` (bundel `konsumen`, `biaya_mkdt`, `riwayat_bayar` via `CashOutRepository::getKavlingDetail()` & `KonsumenService`), `konsumenExistsForKavling(int|string $idKavling)`, `formatBiayaMkdt(?object $mkdt)`, `saveCashOut(array $input, ?int $userId = null)` (validasi input, cek konsumen kavling, sanitasi nominal via `cleanNumeric()`), `deleteCashOut(int $id)`, `insert($data)`, `delete($id)`, `format_tgl($tgl)`, `is_active($id, $texts, $textf)`.
- **Service (`app/Services/Keuangan/Cashout/CashoutKavlingService.php`):** `getIndexData(?int $idProyek)` (data halaman rekap termasuk `proyek`, `pph`, `ppn`, dan checklist subitems terotorisasi), `getMetaPayload(?int $idProyek)`, `getDataTables(array $var)`, `getDataTablesRaw(array $var)` (payload JSON terstruktur + paginasi & permissions untuk `?format=raw`), `getDetailList(int $idKavling)`.
- **Service (`app/Services/Keuangan/Cashout/CashoutSubkonService.php`):** `getIndexData()`, `getMetaPayload()`, `getDataTables(array $var)`, `getDataTablesRaw(array $var)` (delegasi langsung ke `CashoutSubkonRepo::getDataTables()` untuk `?format=raw`), `getDetailList(int $id_cashout_subkon)`, `getCashoutSubkon(array $id_kavlings, ?int $id_cashout_subkon = null)`, `saveCashoutSubkon($post)`, `turunJatuhTempo($id_detail, $tanggal_jatuh_tempo, $berita_acara)`, `ajukanSPP($id_detail, $spp_no, $spp_tgl)`, `ajukanPencairan($id_detail, $spp_no, $spp_tgl, $pencairan_tgl)`, `pembayaran($id_detail, $cek_no, $cek_tgl)`, `getHistory($id_cashout_subkon)`, `num($number)`.
- **Service (`app/Services/Keuangan/CashInReportService.php`):** `getAvailableYears(int $idProyek)`, `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`, `getDetail(int $idProyek, array $request)`, `monthLabel(int $month)`
- **Service (`app/Services/Keuangan/HasilAkadReportService.php`):** `getAvailableYears(int $idProyek)`, `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`, `getDetail(int $idProyek, array $request)`. Mendukung kategori aktif `'akad'` berdampingan dengan backward-compatibility `'penjualan'`, `'pengajuan'`, dan `'cair'`; kalkulasi key finansial `total_hasil_akad` (akumulasi `m.harga_kpr_acc` dari `mkdt`), `total_akad`, dan `total_penjualan` pada payload summary bulanan & total tahunan via helper `assignAkadRow()` dan `emptyAmounts()`; normalisasi parameter request dan whitelist ordering column DataTables detail (`konsumen`, `kavling`, `tipe_rumah`, `total_hasil_akad`, `nominal`, `booking_tgl`, `akad_tgl`, `harga_jual`, `total_sudah_cair`, `tanggal_pengajuan`, `tanggal_cair`) via `normalizeDataTableRequest()`.
- **Service (`app/Services/Keuangan/PrintService.php`):** `printSpptb(int $idKavling, int $idMkdt, int $idProyek)`, `printKuitansi($var)`, `printKuitansiUm($var)`, `printTagihan(string $noInv, int $actorId)` (Render PDF invoice/surat penagihan via mPDF dengan validasi `invoice_log` dan `kopsurat`, melempar `\DomainException` jika data tidak ditemukan), `exportPBataloskonPdf($id_proyek, $id_cluster, $id_jalan)`, `exportPoskonPdf($id_proyek, $id_cluster, $id_jalan, $status)`
- **Service (`app/Services/Keuangan/DanaJaminanService.php`):** `getData(int $idMkdt, int $idKavling)`, `saveDanaAkad($request, int $actorId)`, `listPengajuan(int $idKavling)`, `storePengajuan($request, int $actorId)`, `cairkanPengajuan(int $idPengajuan, $request, int $actorId)`, `getHistory(int $idKavling)`, `rejectLegacyToggle()`, `getListDajam()`, `getMetaPayload()`, `getDownloadFile(int $idPengajuan)` (resolusi path lampiran dan signed download URL via `FileAccessService`), `getDanaAkadDetail(int $idMkdt, int $idKavling = 0)` (konsolidasi detail MKDT, konsumen, `list_dajam`, `dana_akad`, dan `list_pengajuan`).
- **Repository (`app/Repositories/Keuangan/BankRepository.php`):** `getAllBanks(?string $search = null)`, `getActiveBanks()`, `getBankById(int $id)`, `insertBank(array $data)`, `updateBank(int $id, array $data)`, `deleteBank(int $id)`
- **Repository (`app/Repositories/Keuangan/KeuanganRepository.php`):** `getTagihanById(int $idMkdt)`, `getTagihanTurunKPRById(int $idMkdt)`, `getAllJatuhTempo($id_proyek)`, `getLIKeu()`, `getBelumLunasQuery()`, `getJatuhTempoByProyek(?int $idProyek = null)` (query daftar konsumen/kavling belum lunas per proyek untuk jatuh tempo KPR), `getRiwayatSuratQuery(int $idProyek, $idCluster = null, $idJalan = null)` (builder query riwayat surat penagihan `invoice_log` per proyek/cluster/jalan)
- **Repository (`app/Repositories/Keuangan/LogPembayaranRepository.php`):** `getRiwayatBayarById(int $idMkdt)`, `getRiwayatBayarQuery($id_proyek, $id_cluster = null, $id_jalan = null)`, `softDeleteAndReturnIdMkdt($idPembayaran)`, `getRiwayatBayarByIdPembayran($id)`, `getDetailRiwayatBayarById(int $id_Pembayaran)`, `getDistinctActiveMkdtIds()` (daftar unik `id_mkdt` aktif dari `log_pembayaran` untuk batch recalculation summary)
- **Repository (`app/Repositories/Keuangan/PaymentSummaryRepository.php`):** `updateCategory(int $idMkdt, string $kategori, float $amount)`, `setToZero(int $id_mkdt)`, `setCategory(int $idMkdt, string $kategori, float $value)`
- **Repository (`app/Repositories/Keuangan/FinanceLedgerRepository.php`):** `findBySource(string $sourceType, int $sourceId)`, `upsertBySource(array $payload)`, `voidBySource(string $sourceType, int $sourceId, ?int $actorId = null)`, `sumIncome(array $filters = [])`
- **Repository (`app/Repositories/Keuangan/PajakRepository.php`):** `linkKavling(int $idKavling, int $idPajak)`
- **Repository (`app/Repositories/Keuangan/BookingPaymentRepository.php`):** `findProjectIdByMkdt(int $idMkdt)`, `mkdt(int $id, bool $lock = false)`, `link(int $id, bool $lock = false)`, `owners(int $payment)`, `payment(int $id)`, `details(int $payment)`
- **Repository (`app/Repositories/Keuangan/PencairanAkadListRepository.php`):** `buildListQuery(array $filters)`, `getSummary(array $filters, string $search = '')`
- **Repository (`app/Repositories/Keuangan/CashOutRepository.php`):** `getListItem(string $search = '')`, `getListItemSelect2(string $search = '')`, `getKavlingDetail(int $idKavling)` (mengambil `id_kavling` dan `id_mkdt` dari tabel `kavling`), `getRiwayatBayarCashOutByIDKavling(int $id_kavling)`, `softDelete($id)`
- **Repository (`app/Repositories/Keuangan/Cashout/CashoutKavlingRepo.php`):** `getDataTables(array $var)`, `getDetailList(int $idKavling)`, `getCashoutRowsByKavling(int $idKavling)`
- **Repository (`app/Repositories/Keuangan/Cashout/CashoutSubkonRepo.php`):** `getSubkonByID(int $id_subkon)`, `getDataTables(array $var)`, `getDetailsByCashoutSubkonIds(array $ids)`, `getListCashoutKavling(array $id_kavlings)`, `getCashoutSubkonByID(int $id_cashout_subkon)`
- **Repository (`app/Repositories/Keuangan/CashInReportRepository.php`):** `getAvailableYearBounds(int $idProyek)`, `getMonthlyPaymentTotals(int $idProyek, array $years)`, `getMonthlyAkadTotals(int $idProyek, array $years)`, `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable)`
- **Repository (`app/Repositories/Keuangan/HasilAkadReportRepository.php`):** `getAvailableYearBounds(int $idProyek)`, `getMonthlyAkadTotals(int $idProyek, array $years)` (mengakumulasikan `m.harga_kpr_acc` sebagai `total_hasil_akad`), `getMonthlyPengajuanTotals(int $idProyek, array $years)`, `getMonthlyPaymentTotals(int $idProyek, array $years)`, `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable)`. Dilengkapi builder kueri detail modular `compileDetailQuery()` dengan helper SQL builder: `tipeRumahSql()`, `isSubsidiSql()`, dan `totalSudahCairSql()` untuk join ke tabel `tipe` dan `hargajual` serta subquery agregasi pencairan; perluasan multi-column search builder untuk DataTables server-side (`tipe_rumah`, `alamat_kavling`, `nama_konsumen`, `jenis_laporan`, `tanggal_transaksi`).
- **Repository (`app/Repositories/Keuangan/SpptbRepository.php`):** `getLatestByMkdtId(int $idMkdt, int $limit = 3)`, `insertFile(int $idMkdt, string $lokasi, int $actorId)` (Menyimpan metadata unggahan file SPPTB dan surat kuasa ke tabel `file_spptb`)
- **Frontend Assets (`public/assets/js/laporan-hasil-akad.js`):** Menangani rendering matriks komparasi 3 kolom (`Total Hasil Akad`, `Total Pengajuan`, `Total Cair`), chart bulanan berlabel `Total Hasil Akad <Tahun>`, dan modal detail dinamis server-side DataTables:
  - Detail `Total Hasil Akad` (6 kolom): `Nama Konsumen / Kavling` (gabungan nama konsumen, alamat kavling, tipe rumah + badge Subsidi/Komersil rata kiri), `Tgl Booking`, `Tgl Akad`, `Total Hasil Akad` (nominal `m.harga_kpr_acc` tebal), `Total Sudah Cair` (nominal pencairan hijau tebal + pill badge persentase ketercairan), dan `Aksi` (Detail Kavling Siteplan & Kelola Pencairan Akad).
  - Detail `Total Pengajuan` & `Total Cair` (5 kolom): `Nama Konsumen / Kavling` (gabungan rata kiri), `Tgl Pengajuan`, `Status & Rincian` (teks rincian item termin/retensi di baris 1, horizontal flexbox badge status di kiri dan tanggal pencairan di kanan pada baris 2), `Nominal` (tebal rata kanan), dan `Aksi`.
  - Helper rendering modular: `renderKonsumenKavling(row)`, `renderStatusRincian(row, category)`, dan `renderActionButtons(row)`.
- **View & Modal Detail Integration (`app/Views/laporan/hasil-akad.php`):** Mengintegrasikan partial stylesheet `<style><?= view('siteplan/partials/modal_detail_styles') ?></style>`, vendor assets Flatpickr (CSS/JS) & Select2 (CSS/JS), serta inisialisasi lingkungan JS yang tahan redeklarasi (`window.base_url` dan `window.loaded = window.loaded || []`) untuk menjamin render modal detail kavling (`siteplan-detail-modal.js`) berjalan mulus tanpa error JS/CSS.
- **Service Dependency (`app/Services/FileAccessService.php`):** Otorisasi whitelist kategori berkas `'gambar_kerja'` dibuka untuk role Keuangan (`3`) bersama seluruh peran operasional (`[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]`), memastikan denah arsitektur kavling dapat diakses dan ditampilkan di modal detail kavling tanpa HTTP 403 Forbidden.
- **Frontend Assets (`public/assets/js/siteplan/keuangan.js`):** Guard inisialisasi plugin `.richText()` (`if ($.fn && typeof $.fn.richText === "function")`) pada baris 72–116 untuk elemen `#idk-rincian`, `#snk`, dan `#status_keterangan` (remediasi Fase 4B agar tidak melempar `TypeError` di halaman yang tidak memuat library richText seperti `/cashout/kavling`).

## Deprecated & Legacy Shims
### Zero-Downtime Class Shims (41 file — Fase 1 s/d Fase 4 `2026-11-13`)
Seluruh kelas shim di bawah ini dipertahankan sementara di namespace/lokasi asal sebagai *Zero-Downtime Shims* (`extends` / delegasi ke kelas modular di `*\Keuangan\*`) dan berstatus `@deprecated`:
- **Controller Shims — Fase 4 (3 file):**
  - `app/Controllers/CashOut.php` (`App\Controllers\CashOut`) -> Gunakan `\App\Controllers\Api\Keuangan\CashOutController` (mencatat `DEPRECATED_KEUANGAN_HIT` warning log pada `initController()`)
  - `app/Controllers/Keuangan/Cashout/CashoutKavling.php` (`App\Controllers\Keuangan\Cashout\CashoutKavling`) -> Gunakan `\App\Controllers\Web\Keuangan\Cashout\CashoutKavlingController` atau `\App\Controllers\Api\Keuangan\Cashout\CashoutKavlingController` (mencatat `DEPRECATED_KEUANGAN_HIT` warning log pada `initController()`)
  - `app/Controllers/Keuangan/Cashout/CashoutSubkon.php` (`App\Controllers\Keuangan\Cashout\CashoutSubkon`) -> Gunakan `\App\Controllers\Web\Keuangan\Cashout\CashoutSubkonController` atau `\App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController` (mencatat `DEPRECATED_KEUANGAN_HIT` warning log pada `initController()`)
- **Controller Shims — Fase 3 (3 file):**
  - `app/Controllers/PencairanAkad.php` (`App\Controllers\PencairanAkad`) -> Gunakan `\App\Controllers\Api\Keuangan\PencairanAkadController` (API) atau `\App\Controllers\Web\Keuangan\PencairanAkadController` (Web: `listHasilAkad()`, `importForm()`, `exportTemplate()`)
  - `app/Controllers/PencairanJaminan.php` (`App\Controllers\PencairanJaminan`) -> Gunakan `\App\Controllers\Api\Keuangan\DanaJaminanController` (mencatat `DEPRECATED_KEUANGAN_HIT` warning log pada `initController()`)
  - `app/Controllers/DanaAkad.php` (`App\Controllers\DanaAkad`) -> Gunakan `\App\Controllers\Api\Keuangan\DanaJaminanController` (mencatat `DEPRECATED_KEUANGAN_HIT` warning log pada `initController()`)
- **Controller Shims — Fase 2 (3 file):**
  - `app/Controllers/Tagihan.php` (`App\Controllers\Tagihan`) -> Gunakan `\App\Controllers\Api\Keuangan\TagihanController` (API) atau `\App\Controllers\Web\Keuangan\TagihanController` (Web: `listTagihan()`, `riwayatBayarIndex()`)
  - `app/Controllers/Pembayaran.php` (`App\Controllers\Pembayaran`) -> Gunakan `\App\Controllers\Api\Keuangan\PembayaranController` (API) atau `\App\Controllers\Web\Keuangan\PrintController` (Web: `printPembayaran()`, `printPembayaranUm()`)
  - `app/Controllers/PrintController.php` (`App\Controllers\PrintController`) -> Gunakan `\App\Controllers\Web\Keuangan\PrintController`
- **Controller Shims — Fase 1 (7 file):**
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

### Legacy Methods & Routes (Menunggu Dekomposisi Fase 5)
- **Legacy Methods pada `app/Controllers/Keuangan.php`:** Seluruh method legacy di `Keuangan.php` (sebagian sudah diekstrak ke controller modular pada Fase 2 untuk subdomain Tagihan, Pembayaran, Print, BankKprDisbursement, serta `getDanaAkad`/`saveDanaAkad` ke `Api\Keuangan\DanaJaminanController` pada Fase 3) akan diberi anotasi `@deprecated` secara in-place pada **Fase 5** (Opsi A — Deprecation In-Place) agar pemanggil AutoRoute `/keuangan/*` tetap zero-downtime selama masa transisi.
- **Rute CashOut, Cashout Kavling & Cashout Subkon (Dimigrasikan ke Controller Modular pada Fase 4):** Seluruh rute `/keuangan/cashout/{ambil,listitem/ambil,delete,save}`, `/cashout/kavling{,/list,/detail-list}`, dan `/cashout/subkon{,/list,/detail-list,/ambil,/save,/turun-jatuh-tempo,/ajukan-spp,/history,/ajukan-pencairan,/pembayaran}` telah diarahkan ke `\App\Controllers\Api\Keuangan\CashOutController`, `\App\Controllers\{Web,Api}\Keuangan\Cashout\CashoutKavlingController`, dan `\App\Controllers\{Web,Api}\Keuangan\Cashout\CashoutSubkonController`. Pemanggilan langsung melalui kelas shim lama `CashOut`, `CashoutKavling`, atau `CashoutSubkon` mencatat warning `DEPRECATED_KEUANGAN_HIT`.
- Route `GET /pencairan` s/d `GET /pencairan/download/(:num)` (sekarang ditangani `\App\Controllers\Api\Keuangan\DanaJaminanController` sejak Fase 3; shim `PencairanJaminan` mencatat `DEPRECATED_KEUANGAN_HIT`) — **LEGACY / DEPRECATED** secara fungsional: seluruh fitur pencairan jaminan diarahkan ke `/keuangan/pencairan-akad/*`.
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
> - `app/Controllers/Pajak.php`: db_connect(), $this->db->table(), new ClusterModel(), new ProyekModel()
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
- **Kontrak Dual-Mode & Metadata API (Fase 1 s/d Fase 4 `2026-11-13`):**
  - **Fase 1:** Endpoint `GET /api/bank/list`, `POST /api/bank/ambilsatu`, `POST /api/laporan/cash-in/detail`, dan `POST /api/laporan/hasil-akad/detail` mendukung `?format=raw` untuk payload JSON standar `{ success, message, data: { items, meta } }`. Ditambahkan endpoint metadata `GET /api/bank/meta`, `GET /api/laporan/cash-in/meta`, dan `GET /api/laporan/hasil-akad/meta`.
  - **Fase 2:** Ditambahkan endpoint metadata baru:
    - `GET /api/keuangan/tagihan/meta` (`Api\Keuangan\TagihanController::meta`) — mengembalikan `active_project`, `list_item_keuangan`, dan `permissions`.
    - `GET /api/keuangan/pembayaran/meta` (`Api\Keuangan\PembayaranController::meta`) — mengembalikan `active_project`, `list_item_keuangan`, dan `permissions`.
    - `GET /api/keuangan/pencairan-bank/meta` (`Api\Keuangan\BankKprDisbursementController::meta`) — mengembalikan `active_project`, `banks`, `statuses`, dan `permissions`.
  - **Kontrak Dual-Mode `?format=raw` (Fase 2):** Seluruh endpoint JSON pada `Api\Keuangan\TagihanController`, `Api\Keuangan\PembayaranController`, dan `Api\Keuangan\BankKprDisbursementController` mendukung parameter `?format=raw` untuk mengembalikan envelope standar `BaseApiController` (`{ success, message, data }`), sementara panggilan tanpa `?format=raw` mempertahankan format DataTables/legacy + `token` CSRF demi kompatibilitas penuh view CI4 eksisting.
  - **Fase 3:** Ditambahkan endpoint metadata baru:
    - `GET /api/keuangan/pencairan-akad/meta` (`Api\Keuangan\PencairanAkadController::meta`) — mengembalikan `active_project`, `list_dajam`, `template_columns`, `statuses`, dan `permissions` (`can_view`, `can_manage`, `can_import`, `can_export`, `can_cairkan`, `can_void`).
    - `GET /api/keuangan/dana-jaminan/meta` (`Api\Keuangan\DanaJaminanController::meta`) — mengembalikan `active_project`, `list_dajam`, `statuses`, dan `permissions` (`can_view`, `can_manage`, `can_cairkan`).
  - **Kontrak Dual-Mode `?format=raw` (Fase 3):** Seluruh endpoint JSON pada `Api\Keuangan\PencairanAkadController` (termasuk `getListGrouped` -> `getListGroupedRaw()`, `postBatchList` -> `getBatchListRaw()`, `import()`, dan endpoint mutasi/query via `respondDual()`) dan `Api\Keuangan\DanaJaminanController` (`index`, `list`, `store`, `cairkan`, `history`, `toggleStatus`, `download`, `getByID`, `getDanaAkad`, `saveDanaAkad`) mendukung `?format=raw` untuk mengembalikan envelope `BaseApiController` (`{ success, message, data }`), sedangkan panggilan tanpa `?format=raw` mempertahankan format DataTables/redirect/legacy + `token` CSRF.
  - **Fase 4:** Ditambahkan 3 endpoint metadata baru:
    - `GET /api/keuangan/cashout/meta` (`Api\Keuangan\CashOutController::meta`) — mengembalikan `active_project`, `list_item`, `items`, dan `permissions` (`can_view`, `can_manage`, `can_create`, `can_delete`).
    - `GET /api/keuangan/cashout-kavling/meta` (`Api\Keuangan\Cashout\CashoutKavlingController::meta`) — mengembalikan `active_project`, `pph`, `ppn`, `checklist_subitems`, dan `permissions` (`can_view`, `can_keuangan`, `can_produksi`, `can_pajak`).
    - `GET /api/keuangan/cashout-subkon/meta` (`Api\Keuangan\Cashout\CashoutSubkonController::meta`) — mengembalikan `active_project`, `statuses`, `termin_statuses`, dan `permissions` (`can_view`, `can_manage`, `can_create_spk`, `can_turun_jatuh_tempo`, `can_ajukan_spp`, `can_ajukan_pencairan`, `can_pembayaran`).
  - **Kontrak Dual-Mode `?format=raw` (Fase 4):** Seluruh endpoint JSON pada `Api\Keuangan\CashOutController` (`getListItem`, `getByIDKavling`, `insert`, `delete`), `Api\Keuangan\Cashout\CashoutKavlingController` (`getDataTables` -> `getDataTablesRaw()`, `getDetailList`), dan `Api\Keuangan\Cashout\CashoutSubkonController` (`getDataTables` -> `getDataTablesRaw()`, serta `getDetailList`, `get`, `save`, `turunJatuhTempo`, `ajukanSPP`, `ajukanPencairan`, `pembayaran`, `getHistory` via `respondSubkonDual()`) mendukung `?format=raw` untuk mengembalikan envelope standar `BaseApiController` (`{ success, message, data }`) dengan HTTP status `404`/`422` pada kegagalan validasi/data tidak ditemukan, sedangkan panggilan tanpa `?format=raw` tetap mempertahankan format DataTables/legacy + `token` CSRF.
- **Guard Inisialisasi Plugin `.richText()` di `public/assets/js/siteplan/keuangan.js` (Fase 4B `2026-11-13`):**
  - Pemanggilan `.richText()` pada `#idk-rincian`, `#snk`, dan `#status_keterangan` di `public/assets/js/siteplan/keuangan.js` (baris 72–116) dibungkus dengan guard `if ($.fn && typeof $.fn.richText === "function")` agar halaman yang memuat `keuangan.js` tanpa library richText (seperti `/cashout/kavling` saat membuka modal cashout `.ck-open-cashout`) tidak melempar `TypeError: $(...).richText is not a function`.
- **Perbaikan CSRF & DataTables pada `app/Views/keuangan/riwayat-bayar.php` (Fase 2B `2026-11-13`):**
  - Menambahkan definisi eksplisit `columns` DataTables (`No`, `nominal`, `tanggal_bayar`, `keterangan`, `username`, `created_at`) dengan rendering aman (`escapeHtml()`) agar kompatibel dengan format object rows dari `DataTable::of()->toJson(true)` di `KeuanganService::getRiwayatBayar()`.
  - Menambahkan rotasi token CSRF (`if (r && r.token) csrfHash = r.token;`) pada callback `ajax.dataSrc` DataTables dan `processResults` Select2 (`#id_cluster`, `#id_jalan`) untuk mencegah error `DataTables warning` dan penolakan token CSRF stale saat filter/pagination.
- **Catatan Skema & Integrasi `invoice_log` (Pembaruan Migrasi 2026-11-04):**
  - Tabel `invoice_log` diperkaya dengan kolom `status_tagihan` (`VARCHAR(50)`, NOT NULL, DEFAULT `'draft'`), `tanggal_ubah_status` (`DATE`, NULL), `keterangan_status` (`TEXT`, NULL), dan `tanggal_kirim` (`DATETIME`, NULL).
  - Index `idx_invoice_log_status_tagihan` (`status_tagihan`) ditambahkan untuk mempercepat kueri filter tagihan aktif dan riwayat status.
  - Seluruh data legacy yang belum memiliki status tagihan telah di-backfill menjadi `'draft'`.
- **Deprecation Notice (PencairanJaminan, DanaAkad & DanaJaminanService):**
  - Sejak Fase 3 (`2026-11-13`), rute `/pencairan/*`, `/danaakad/getbyid`, `/keuangan/getDanaAkad`, dan `/keuangan/saveDanaAkad` telah dikonsolidasikan ke `Api\Keuangan\DanaJaminanController`, sementara `PencairanJaminan.php` dan `DanaAkad.php` dipertahankan sebagai Zero-Downtime Shims `@deprecated` yang mencatat warning log `DEPRECATED_KEUANGAN_HIT` pada `initController()`.
  - Secara fungsional, rute `/pencairan/*` tetap berstatus **DEPRECATED**; seluruh fungsionalitas retensi jaminan perbankan telah digantikan oleh sub-modul terpadu `PencairanAkad` (`pencairan_akad_*`) dengan rute `/keuangan/pencairan-akad/*`.
  - Tidak ada modul lain (MKDT, Legal, Produksi, Promosi, dsb.) yang mengakses controller maupun service `DanaJaminan` ini.
- **Integrasi Modal Detail Kavling & Autorisasi File Gambar Kerja (Pembaruan 2026-11-09):**
  - Tampilan laporan Hasil Akad (`app/Views/laporan/hasil-akad.php`) mengintegrasikan modal detail kavling lintas modul (`siteplan/partials/modal_detail` & `siteplan-detail-modal.js`).
  - Dependensi visual modal (Flatpickr CSS/JS, Select2 CSS/JS, dan `modal_detail_styles`) diinisialisasi secara eksplisit pada view laporan bersama runtime environment tahan redeklarasi (`window.base_url` dan `window.loaded = window.loaded || []`).
  - Whitelist otorisasi kategori berkas `'gambar_kerja'` pada `FileAccessService` mencakup role Keuangan (3), memastikan gambar denah arsitektur kavling tampil dengan aman saat dibuka oleh staf Keuangan tanpa HTTP 403 Forbidden.

## Change Log
- **2026-11-13 [`REFACTOR` — Fase 4] — Subdomain CashOut Umum, Cashout Kavling & Cashout Subkon (+ Fase 4B Remediation):**
  - **Routes & Public Contracts:**
    - `app/Config/Routes.php` (`154` rute eksplisit Keuangan): Memigrasikan rute `/keuangan/cashout/{ambil,listitem/ambil,delete,save}` ke `Api\Keuangan\CashOutController`, `/cashout/kavling` ke `Web\Keuangan\Cashout\CashoutKavlingController`, `/cashout/kavling/{list,detail-list}` ke `Api\Keuangan\Cashout\CashoutKavlingController`, `/cashout/subkon` ke `Web\Keuangan\Cashout\CashoutSubkonController`, dan `/cashout/subkon/{list,detail-list,ambil,save,turun-jatuh-tempo,ajukan-spp,history,ajukan-pencairan,pembayaran}` ke `Api\Keuangan\Cashout\CashoutSubkonController`.
    - Menambahkan 3 endpoint metadata baru: `GET /api/keuangan/cashout/meta`, `GET /api/keuangan/cashout-kavling/meta`, dan `GET /api/keuangan/cashout-subkon/meta`, serta dukungan kontrak Dual-Mode `?format=raw` di seluruh endpoint API CashOut, Cashout Kavling, dan Cashout Subkon.
  - **Controller / Service / Repo / Model:**
    - **5 Modular Controllers Baru (0 direct DB/Model access):** `Api\Keuangan\CashOutController` (`meta`, `getListItem`, `getByIDKavling`, `insert`, `delete`), `Web\Keuangan\Cashout\CashoutKavlingController` (`index`), `Api\Keuangan\Cashout\CashoutKavlingController` (`meta`, `getDataTables`, `getDetailList`), `Web\Keuangan\Cashout\CashoutSubkonController` (`index`), dan `Api\Keuangan\Cashout\CashoutSubkonController` (`meta`, `getDataTables`, `getDetailList`, `get`, `save`, `turunJatuhTempo`, `ajukanSPP`, `ajukanPencairan`, `pembayaran`, `getHistory`).
    - **Service & Repository Enhancements:**
      - `CashOutRepository`: Menambahkan `getListItemSelect2()` dan `getKavlingDetail()`.
      - `CashOutService`: Menambahkan `getListItemSelect2()`, `getMetaPayload()`, `getByIDKavling()`, `konsumenExistsForKavling()`, `formatBiayaMkdt()`, `saveCashOut()`, `deleteCashOut()`, serta sanitasi `cleanNumeric()`.
      - `CashoutKavlingService`: Menambahkan `getIndexData()`, `getMetaPayload()`, dan `getDataTablesRaw()`.
      - `CashoutSubkonService`: Menambahkan `getIndexData()`, `getMetaPayload()`, dan `getDataTablesRaw()` (delegasi langsung ke `CashoutSubkonRepo::getDataTables()`).
    - **3 Zero-Downtime Shims Baru:** `app/Controllers/CashOut.php`, `app/Controllers/Keuangan/Cashout/CashoutKavling.php`, dan `app/Controllers/Keuangan/Cashout/CashoutSubkon.php` (`@deprecated` + `DEPRECATED_KEUANGAN_HIT` warning log pada `initController()`).
    - `app/Libraries/ModuleManager/ModuleCatalog.php`: Mendaftarkan 5 controller modular baru (`Ctrl: 23`) dan 3 shims baru (total `41` shims di modul Keuangan).
  - **View / CSS / JS:**
    - `public/assets/js/siteplan/keuangan.js` (Fase 4B Remediation): Menambahkan guard `if ($.fn && typeof $.fn.richText === "function")` di baris 72–116 untuk inisialisasi `.richText()` pada `#idk-rincian`, `#snk`, dan `#status_keterangan` agar aman dimuat pada halaman tanpa library richText (seperti `/cashout/kavling`).
  - **Deprecations:** 3 controller lama (`CashOut`, `Keuangan\Cashout\CashoutKavling`, `Keuangan\Cashout\CashoutSubkon`) diubah menjadi `@deprecated` Zero-Downtime Shims; legacy methods di `Keuangan.php` dijadwalkan untuk diberi `@deprecated` pada Fase 5 (Opsi A).
- **2026-11-13 [`REFACTOR` — Fase 3] — Subdomain Pencairan Akad, Dana Akad & Konsolidasi `PencairanJaminan` -> `DanaJaminanController`:**
  - **Routes & Public Contracts:**
    - `app/Config/Routes.php` (`151` rute eksplisit Keuangan): Memigrasikan seluruh rute `/keuangan/hasil-akad/*` (`list`, `export-template`, `import` -> `Web\Keuangan\PencairanAkadController`; `list/ambil-grouped`, `list/detail`, `import` -> `Api\Keuangan\PencairanAkadController`), `/keuangan/pencairan-akad/*` (`16` endpoint single & batch -> `Api\Keuangan\PencairanAkadController`), `/danaakad/getbyid`, `/keuangan/getDanaAkad`, `/keuangan/saveDanaAkad`, serta konsolidasi grup `/pencairan/*` (`7` rute -> `Api\Keuangan\DanaJaminanController`).
    - Menambahkan 2 endpoint metadata baru: `GET /api/keuangan/pencairan-akad/meta` dan `GET /api/keuangan/dana-jaminan/meta`, serta dukungan kontrak Dual-Mode `?format=raw` di seluruh endpoint `Api\Keuangan\PencairanAkadController` dan `Api\Keuangan\DanaJaminanController`.
  - **Controller / Service / Repo / Model:**
    - **3 Modular Controllers Baru:** `Web\Keuangan\PencairanAkadController` (`listHasilAkad`, `importForm`, `exportTemplate`), `Api\Keuangan\PencairanAkadController` (`meta` + `19` endpoint single/batch/hasil-akad dengan `checkKeuanganAccess()` & `respondDual()`), dan `Api\Keuangan\DanaJaminanController` (`meta`, `index`, `list`, `store`, `cairkan`, `history`, `toggleStatus`, `download`, `getByID`, `getDanaAkad`, `saveDanaAkad`).
    - **Service Enhancements:**
      - `PencairanAkadService`: Memperkuat `void()` dengan DB transaction, serta menambahkan `getListDajam()` (public), `getMetaPayload()`, `getListGroupedRaw()`, `buildBatchListQuery()`, dan `getBatchListRaw()`.
      - `DanaJaminanService`: Menambahkan `getListDajam()`, `getMetaPayload()`, `getDownloadFile()`, dan `getDanaAkadDetail()`.
    - **3 Zero-Downtime Shims Baru:** `app/Controllers/PencairanAkad.php` (`@deprecated`), `app/Controllers/PencairanJaminan.php` (`@deprecated` + `DEPRECATED_KEUANGAN_HIT` warning log), dan `app/Controllers/DanaAkad.php` (`@deprecated` + `DEPRECATED_KEUANGAN_HIT` warning log).
    - `app/Libraries/ModuleManager/ModuleCatalog.php`: Mendaftarkan 3 controller modular baru dan 3 shims baru (total `38` shims di modul Keuangan).
  - **View / CSS / JS:** Tidak berubah (minimum diff; kompatibilitas UI eksisting terjaga).
  - **Deprecations:** 3 controller lama (`PencairanAkad`, `PencairanJaminan`, `DanaAkad`) diubah menjadi `@deprecated` Zero-Downtime Shims; legacy methods di `Keuangan.php` dijadwalkan untuk diberi `@deprecated` pada Fase 5 (Opsi A).
- **2026-11-13 [`REFACTOR` — Fase 2 & 2B] — Dekomposisi Subdomain Tagihan, Pembayaran, Print & Bank KPR Disbursement + Remediasi QA:**
  - **Routes & Public Contracts:**
    - `app/Config/Routes.php`: Memigrasikan seluruh rute subdomain **Tagihan** (`/riwayat-bayar`, `/tagihan/list` -> `Web\Keuangan\TagihanController`; `/tagihan/*`, `/danaakad/list/ambilsatu` -> `Api\Keuangan\TagihanController`), **Pembayaran** (`/pembayaran/simpan`, `/pembayaran/hapus`, `/pembayaran/hitungulang` -> `Api\Keuangan\PembayaranController`), **Print** (`/print/spptb`, `/print/kuitansi`, `/pembayaran/kuitansi/cetak`, `/pembayaran/kuitansi-um/cetak`, `/keuangan/print_tagihan` -> `Web\Keuangan\PrintController`), dan **Bank KPR Disbursement** (`/keuangan/jatuhtempo/get`, `/keuangan/pencairan-bank/*` -> `Api\Keuangan\BankKprDisbursementController`).
    - Menambahkan 3 endpoint metadata baru: `GET /api/keuangan/tagihan/meta`, `GET /api/keuangan/pembayaran/meta`, dan `GET /api/keuangan/pencairan-bank/meta` serta kontrak Dual-Mode `?format=raw` di seluruh endpoint API baru tersebut.
  - **Controller / Service / Repo / Model:**
    - **5 Modular Controllers Baru:** `Web\Keuangan\TagihanController` (`listTagihan`, `riwayatBayarIndex`), `Api\Keuangan\TagihanController` (`meta` + 15 endpoint tagihan/surat/jatuh tempo), `Api\Keuangan\PembayaranController` (`meta`, `save`, `removeLP`, `recalculateSummary`), `Web\Keuangan\PrintController` (`printSpptb`, `printKuitansi`, `printPembayaran`, `printPembayaranUm`, `printTagihan` dengan try/catch `\DomainException` `403`/`404` pada `handleKuitansiPrint()` & `printTagihan()`), dan `Api\Keuangan\BankKprDisbursementController` (`meta`, `getJatuhTempo`, `getData`, `save`, `void`).
    - **Service & Repository Enhancements:**
      - `KeuanganService`: Menambah `getListItemKeuangan()`, `getTagihanBundle()`, `formatBiayaMkdt()`, `saveTagihan()`, `getJatuhTempoByProyek()`, `getListTagihanRaw()`, `getListTagihanGroupedRaw()`, `getRiwayatBayarRaw()`, `getListRiwayatSuratRaw()`, serta guard `ensureDataTableRequestDefaults()` untuk proteksi array `columns`/`search`.
      - `PembayaranService`: Menambah `recalculateAllSummaries()` dan memperkuat validasi nominal/alokasi pada `simpan()`.
      - `PrintService`: Menambah `printTagihan()` serta membersihkan trailing whitespace.
      - `BankKprDisbursementService`: Menambah `getBanksList()` dan `canUserManage()`.
      - `SpptbRepository::insertFile()`, `LogPembayaranRepository::getDistinctActiveMkdtIds()`, dan `KeuanganRepository::{getJatuhTempoByProyek, getRiwayatSuratQuery}`.
    - **3 Zero-Downtime Shims Baru:** `app/Controllers/Tagihan.php`, `app/Controllers/Pembayaran.php`, dan `app/Controllers/PrintController.php`.
    - `app/Libraries/ModuleManager/ModuleCatalog.php`: Mendaftarkan 5 controller modular baru dan 3 shims baru.
  - **View / CSS / JS:**
    - `app/Views/keuangan/riwayat-bayar.php` (Fase 2B): Menambahkan konfigurasi eksplisit `columns` DataTables untuk object rows hasil `DataTable::of()->toJson(true)`, rendering aman dengan `escapeHtml()`, dan sinkronisasi `csrfHash = r.token` pada callback DataTables & Select2.
  - **Deprecations:** 3 controller lama (`Tagihan`, `Pembayaran`, `PrintController`) diubah menjadi `@deprecated` Zero-Downtime Shims; legacy methods di `Keuangan.php` dijadwalkan untuk diberi `@deprecated` pada Fase 5 (Opsi A).
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
