# Module: Keuangan

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

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

### Shared Tables (Dibaca dari modul lain)
- `mkdt`
- `kavling`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/list-tagihan` | `Explicit` | `Keuangan::list_tagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/jatuhtempo/get` | `Explicit` | `Keuangan::getJatuhTempo` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-bank/get` | `Explicit` | `Keuangan::getBankKprDisbursement` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-bank/save` | `Explicit` | `Keuangan::saveBankKprDisbursement` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-bank/void/(:num)` | `Explicit` | `Keuangan::voidBankKprDisbursement/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `submit-keuangan` | `Explicit` | `ReferralController::submitKeuangan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `mark-cair-keuangan` | `Explicit` | `ReferralController::markCairKeuangan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-tagihan` | `Explicit` | `Keuangan::list_tagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/ambilsatu` | `Explicit` | `Tagihan::getByID` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/riwayat/ambilsatu` | `Explicit` | `Tagihan::getRiwayatByID` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/turunkpr` | `Explicit` | `Tagihan::tambahTurunKPR` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/hapusturunkpr` | `Explicit` | `Tagihan::hapusTurunKPR` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/hapus` | `Explicit` | `Tagihan::hapus` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/void` | `Explicit` | `Tagihan::voidTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/unvoid` | `Explicit` | `Tagihan::unvoidTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/jatuhtempo` | `Explicit` | `Tagihan::getAllJatuhTempo` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/riwayat-bayar` | `Explicit` | `Tagihan::riwayatBayarIndex` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/riwayat-bayar` | `Explicit` | `Tagihan::riwayatBayarIndex` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/riwayat-bayar/ambil` | `Explicit` | `Tagihan::getRiwayatBayar` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/tagihan/list` | `Explicit` | `Tagihan::listTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/list/ambil` | `Explicit` | `Tagihan::getListTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/list/ambil-grouped` | `Explicit` | `Tagihan::getListTagihanGrouped` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/list/detail` | `Explicit` | `Tagihan::getListTagihanDetail` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/list/export-excel` | `Explicit` | `Tagihan::exportExcelTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/tagihan/list/ambil-riwayat-surat` | `Explicit` | `Tagihan::getListRiwayatSurat` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/danaakad/list/ambilsatu` | `Explicit` | `Tagihan::getListTagihan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `pembayaran/kuitansi/cetak` | `Explicit` | `Pembayaran::print` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `pembayaran/kuitansi-um/cetak` | `Explicit` | `Pembayaran::printUm` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/pembayaran/simpan` | `Explicit` | `Pembayaran::save` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/pembayaran/hapus` | `Explicit` | `Pembayaran::removeLP` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/pembayaran/hitungulang` | `Explicit` | `Pembayaran::recalculateSummary` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/pembayaran` | `Explicit` | `CashoutSubkon::pembayaran` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/cashout/ambil` | `Explicit` | `CashOut::getByIDKavling` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/cashout/listitem/ambil` | `Explicit` | `CashOut::getListItem` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/cashout/delete` | `Explicit` | `CashOut::delete` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/cashout/save` | `Explicit` | `CashOut::insert` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `kavling` | `Explicit` | `CashoutKavling::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `kavling/list` | `Explicit` | `CashoutKavling::getDataTables` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `kavling/detail-list` | `Explicit` | `CashoutKavling::getDetailList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `subkon` | `Explicit` | `CashoutSubkon::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/list` | `Explicit` | `CashoutSubkon::getDataTables` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/detail-list` | `Explicit` | `CashoutSubkon::getDetailList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/ambil` | `Explicit` | `CashoutSubkon::get` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/save` | `Explicit` | `CashoutSubkon::save` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/turun-jatuh-tempo` | `Explicit` | `CashoutSubkon::turunJatuhTempo` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/ajukan-spp` | `Explicit` | `CashoutSubkon::ajukanSPP` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/history` | `Explicit` | `CashoutSubkon::getHistory` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/ajukan-pencairan` | `Explicit` | `CashoutSubkon::ajukanPencairan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/pembayaran` | `Explicit` | `CashoutSubkon::pembayaran` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `toggle/(:num)` | `Explicit` | `CashoutSubkon::toggleStatus/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `download/(:num)` | `Explicit` | `CashoutSubkon::download/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `kavling` | `Explicit` | `CashoutKavling::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `kavling/list` | `Explicit` | `CashoutKavling::getDataTables` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `kavling/detail-list` | `Explicit` | `CashoutKavling::getDetailList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `subkon` | `Explicit` | `CashoutSubkon::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/list` | `Explicit` | `CashoutSubkon::getDataTables` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/detail-list` | `Explicit` | `CashoutSubkon::getDetailList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/ambil` | `Explicit` | `CashoutSubkon::get` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/save` | `Explicit` | `CashoutSubkon::save` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/turun-jatuh-tempo` | `Explicit` | `CashoutSubkon::turunJatuhTempo` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/ajukan-spp` | `Explicit` | `CashoutSubkon::ajukanSPP` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/history` | `Explicit` | `CashoutSubkon::getHistory` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/ajukan-pencairan` | `Explicit` | `CashoutSubkon::ajukanPencairan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/pembayaran` | `Explicit` | `CashoutSubkon::pembayaran` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `toggle/(:num)` | `Explicit` | `CashoutSubkon::toggleStatus/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `download/(:num)` | `Explicit` | `CashoutSubkon::download/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `bank` | `Explicit` | `ListBank::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `ambil` | `Explicit` | `BankController::ambil` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `list` | `Explicit` | `BankController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `ambilsatu` | `Explicit` | `BankController::ambilSatu` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `simpan` | `Explicit` | `BankController::simpan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `hapus` | `Explicit` | `BankController::hapus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/keuangan/pencairan-akad/get` | `Explicit` | `PencairanAkad::get` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/plan/save-retensi` | `Explicit` | `PencairanAkad::saveRetensi` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/plan/save-tenor` | `Explicit` | `PencairanAkad::saveTenor` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/pengajuan/store` | `Explicit` | `PencairanAkad::storePengajuan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/pencairan/store` | `Explicit` | `PencairanAkad::cairkan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/payment/list` | `Explicit` | `PencairanAkad::listPayment` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/payment/void` | `Explicit` | `PencairanAkad::voidPayment` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/pencairan-akad/void` | `Explicit` | `PencairanAkad::void` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/keuangan/pencairan-akad/history/(:num)` | `Explicit` | `PencairanAkad::history/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/keuangan/hasil-akad/list` | `Explicit` | `PencairanAkad::listHasilAkad` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/hasil-akad/list/ambil-grouped` | `Explicit` | `PencairanAkad::getListGrouped` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/hasil-akad/list/detail` | `Explicit` | `PencairanAkad::getListDetail` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/keuangan/hasil-akad/export-template` | `Explicit` | `PencairanAkad::exportTemplate` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/keuangan/hasil-akad/import` | `Explicit` | `PencairanAkad::importForm` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/keuangan/hasil-akad/import` | `Explicit` | `PencairanAkad::import` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `verifikasi` | `Explicit` | `BookingPaymentController::verify` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `cash-in` | `Explicit` | `CashInReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `summary` | `Explicit` | `CashInReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `detail` | `Explicit` | `CashInReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `cash-in` | `Explicit` | `CashInReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `summary` | `Explicit` | `CashInReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `detail` | `Explicit` | `CashInReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `hasil-akad` | `Explicit` | `HasilAkadReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `summary` | `Explicit` | `HasilAkadReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `detail` | `Explicit` | `HasilAkadReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `hasil-akad` | `Explicit` | `HasilAkadReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `summary` | `Explicit` | `HasilAkadReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `detail` | `Explicit` | `HasilAkadReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `spptb` | `Explicit` | `PrintController::printSpptb` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `kuitansi` | `Explicit` | `PrintController::printKuitansi` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/keuangan/getdanaakad` | `AutoRoute` | `Keuangan::getDanaAkad` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/getcashout` | `AutoRoute` | `Keuangan::getCashOut` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/savecashout` | `AutoRoute` | `Keuangan::saveCashOut` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/savedanaakad` | `AutoRoute` | `Keuangan::saveDanaAkad` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/get_riwayat_gantinama` | `AutoRoute` | `Keuangan::get_riwayat_gantinama` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/gettagihan` | `AutoRoute` | `Keuangan::getTagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/issudahbayar` | `AutoRoute` | `Keuangan::isSudahBayar` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/save_kons` | `AutoRoute` | `Keuangan::save_kons` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/isi_tagihan` | `AutoRoute` | `Keuangan::isi_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/save_sb` | `AutoRoute` | `Keuangan::save_sb` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/save` | `AutoRoute` | `Keuangan::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/get_kavling` | `AutoRoute` | `Keuangan::get_kavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/getlisttagihan` | `AutoRoute` | `Keuangan::getListTagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/riwayat_bayar` | `AutoRoute` | `Keuangan::riwayat_bayar` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/getriwayatbayar` | `AutoRoute` | `Keuangan::getRiwayatBayar` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/num` | `AutoRoute` | `Keuangan::num` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/get_tagihan` | `AutoRoute` | `Keuangan::get_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/removelp` | `AutoRoute` | `Keuangan::removeLP` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/doprint` | `AutoRoute` | `Keuangan::doPrint` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/printspptb` | `AutoRoute` | `Keuangan::printSPPTB` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/print_tagihan` | `AutoRoute` | `Keuangan::print_tagihan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/keuangan/print_kuitansi` | `AutoRoute` | `Keuangan::print_kuitansi` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
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
| `ANY` | `/tagihan/formatbiayamkdt` | `AutoRoute` | `Tagihan::formatBiayaMkdt` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tagihan/save` | `AutoRoute` | `Tagihan::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tagihan/num` | `AutoRoute` | `Tagihan::num` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pembayaran/num` | `AutoRoute` | `Pembayaran::num` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/index` | `AutoRoute` | `Pajak::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/getdatatable` | `AutoRoute` | `Pajak::getDataTable` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/getall` | `AutoRoute` | `Pajak::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/getone` | `AutoRoute` | `Pajak::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/save` | `AutoRoute` | `Pajak::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/storepajakfile` | `AutoRoute` | `Pajak::storePajakFile` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/edit` | `AutoRoute` | `Pajak::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/remove` | `AutoRoute` | `Pajak::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/if_where` | `AutoRoute` | `Pajak::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/is_active` | `AutoRoute` | `Pajak::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/format_tgl` | `AutoRoute` | `Pajak::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pajak/num` | `AutoRoute` | `Pajak::num` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/dana_akad/getbyid` | `AutoRoute` | `DanaAkad::getByID` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/dana_akad/num` | `AutoRoute` | `DanaAkad::num` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cash_out/konsumenexistsforkavling` | `AutoRoute` | `CashOut::konsumenExistsForKavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cash_out/formatbiayamkdt` | `AutoRoute` | `CashOut::formatBiayaMkdt` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cash_out/num` | `AutoRoute` | `CashOut::num` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cashout_kavling/json` | `AutoRoute` | `CashoutKavling::json` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cashout_subkon/json` | `AutoRoute` | `CashoutSubkon::json` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cash_in_report_controller/canaccessreport` | `AutoRoute` | `CashInReportController::canAccessReport` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/hasil_akad_report_controller/canaccessreport` | `AutoRoute` | `HasilAkadReportController::canAccessReport` | 🟢 MIGRATED | Rute method aktif |

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
- _dan 26 method lainnya..._
#### `PembayaranService.php`
- `simpan($data)`
- `recalculateSummary($id_mkdt)`
- `removeLP($request)`
- `num($d)`
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
- _dan 14 method lainnya..._
#### `BankKprDisbursementService.php`
- `getData(int $idMkdt, int $idKavling)`
- `save($request, int $actorId)`
- `void(int $id, $request, int $actorId)`
- `getRows(int $idMkdt, int $idKavling)`
- `getBanks()`
- `getAkadContext(int $idMkdt, int $idKavling, bool $akadOnly = true)`
- `buildSummary(array $rows, object $context, int $idKavling)`
- `emptySummary()`
- `getDanaJaminanTotal(int $idKavling)`
- `storeBukti($file)`
- `canManage()`
- `response(bool $success, string $message, array $extra = [])`
- `isValidDate(?string $value)`
- `num($value)`
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
- `assertCanonicalMatches(array $mkdt, array $link)`
- `historyRow(array $row, float $nominal, bool $installment)`
- `money(mixed $value)`
- `date(mixed $value)`
- `validDate(?string $value)`
#### `BookingFeeMigrationService.php`
- `run()`
- `snapshot()`
- `seedExistingLinks()`
- `linkApprovedReplacements()`
- `splitPayment1001()`
- `correctAllocation(int $paymentId, string $category, ?string $name, int $idMkdt)`
- `audit(string $key, ?int $idMkdt, ?int $paymentId, string $action, mixed $before, mixed $after)`
- `anomalies()`
- `sourceMkdtQuery()`
- `promoIds()`
#### `PencairanAkadService.php`
- `getData(int $idMkdt, int $idKavling)`
- `saveRetensi(array $post, int $actorId)`
- `saveTenor(array $post, int $actorId)`
- `storePengajuan($request, int $actorId)`
- `storePengajuanCore(array $data, ?string $lampiranSurat, int $actorId, bool $requireLampiran)`
- `cairkan($request, int $actorId)`
- `cairkanCore(int $idPengajuan, string $tanggalCair, array $details, ?string $catatan, int $actorId)`
- `listPayment(int $idPengajuan)`
- `voidPayment(int $idPayment, string $reason, int $actorId)`
- `void(int $idPengajuan, string $reason, int $actorId)`
- `exportTemplate(array $filter)`
- `templateColumns()`
- `importTanggalCair(string $csvPath, $lampiranFile, int $actorId)`
- `getListGrouped($request)`
- `listFilters($request)`
- _dan 24 method lainnya..._
#### `CashOutService.php`
- `insert($data)`
- `delete($id)`
- `num($v)`
- `format_tgl($tgl)`
- `is_active($id, $texts, $textf)`
#### `CashoutKavlingService.php`
- `getDataTables(array $var)`
- `getDetailList(int $idKavling)`
- `nominalButton(float $value, string $class, string $payload, bool $disabled = false)`
- `actionButton(string $class, string $payload, string $icon, string $color, string $title)`
- `formatNumber(float $value)`
- `escape(?string $value)`
#### `CashoutSubkonService.php`
- `getDataTables(array $var)`
- `getDetailList(int $id_cashout_subkon)`
- `getCashoutSubkon(array $id_kavlings, ?int $id_cashout_subkon = null)`
- `cashoutSubkonResponse(int $id_cashout_subkon)`
- `saveCashoutSubkon($post)`
- `turunJatuhTempo($id_detail, $tanggal_jatuh_tempo, $berita_acara)`
- `ajukanSPP($id_detail, $spp_no, $spp_tgl)`
- `ajukanPencairan($id_detail, $spp_no, $spp_tgl, $pencairan_tgl)`
- `pembayaran($id_detail, $cek_no, $cek_tgl)`
- `getHistory($id_cashout_subkon)`
- `allTerminPaid(int $id_cashout_subkon)`
- `notifyCashoutSubkon(int $id_cashout_subkon, array $targets, string $message)`
- `hasProgressedTermin(int $id_cashout_subkon)`
- `validateCashoutSubkonData(array $data, $file)`
- `validateRequired(array $fields)`
- _dan 12 method lainnya..._
#### `CashInReportService.php`
- `getAvailableYears(int $idProyek)`
- `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`
- `getDetail(int $idProyek, array $request)`
- `monthLabel(int $month)`
- `normalizeDataTableRequest(array $request)`
- `emptySummary(array $years)`
- `emptyAmounts(bool $includeTotal = true)`
- `assertYear(int $year)`
- `assertProject(int $idProyek)`
#### `HasilAkadReportService.php`
- `getAvailableYears(int $idProyek)`
- `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`
- `getDetail(int $idProyek, array $request)`
- `assignAkadRow(array &$summary, array $row)`
- `assignAmount(array &$summary, array $row, string $key)`
- `emptySummary(array $years)`
- `emptyAmounts()`
- `normalizeDataTableRequest(array $request)`
- `assertYear(int $year)`
- `assertProject(int $idProyek)`
#### `PrintService.php`
- `printSpptb(int $idKavling, int $idMkdt, int $idProyek)`
- `spptbConsumerFooter(object $consumer)`
- `printKuitansi($var)`
- `printKuitansiUm($var)`
- `assertPaymentOwnership(int $idPembayaran, int $idMkdt, mixed $payment)`
- `exportPBataloskonPdf($id_proyek, $id_cluster, $id_jalan)`
- `exportPoskonPdf($id_proyek, $id_cluster, $id_jalan, $status)`
- `exportBatalPoskonXlsx($id_proyek, $id_cluster, $id_jalan)`
- `exportPoskonXlsx($id_proyek, $id_cluster, $id_jalan, $status)`

## File Inventory
### Controllers (17)
- `app/Controllers/Keuangan.php` (1923 baris, 38 public methods)
- `app/Controllers/Tagihan.php` (521 baris, 19 public methods)
- `app/Controllers/Pembayaran.php` (89 baris, 6 public methods)
- `app/Controllers/Pajak.php` (589 baris, 12 public methods)
- `app/Controllers/DanaAkad.php` (92 baris, 2 public methods)
- `app/Controllers/CashOut.php` (242 baris, 7 public methods)
- `app/Controllers/Keuangan/Cashout/CashoutKavling.php` (85 baris, 4 public methods)
- `app/Controllers/Keuangan/Cashout/CashoutSubkon.php` (111 baris, 11 public methods)
- `app/Controllers/ListBank.php` (27 baris, 1 public methods)
- `app/Controllers/Api/BankController.php` (132 baris, 5 public methods)
- `app/Controllers/PencairanAkad.php` (154 baris, 15 public methods)
- `app/Controllers/Api/BookingPaymentController.php` (54 baris, 1 public methods)
- `app/Controllers/Web/CashInReportController.php` (51 baris, 1 public methods)
- `app/Controllers/Api/CashInReportController.php` (85 baris, 3 public methods)
- `app/Controllers/Web/HasilAkadReportController.php` (49 baris, 1 public methods)
- `app/Controllers/Api/HasilAkadReportController.php` (80 baris, 3 public methods)
- `app/Controllers/PrintController.php` (133 baris, 2 public methods)

### Services (15)
- `app/Services/KeuanganService.php` (1851 baris)
- `app/Services/PembayaranService.php` (256 baris)
- `app/Services/PajakService.php` (45 baris)
- `app/Services/FinanceLedgerService.php` (596 baris)
- `app/Services/BankKprDisbursementService.php` (407 baris)
- `app/Services/BankService.php` (37 baris)
- `app/Services/BookingPaymentService.php` (326 baris)
- `app/Services/BookingFeeMigrationService.php` (234 baris)
- `app/Services/PencairanAkadService.php` (1339 baris)
- `app/Services/CashOutService.php` (84 baris)
- `app/Services/Keuangan/Cashout/CashoutKavlingService.php` (132 baris)
- `app/Services/Keuangan/Cashout/CashoutSubkonService.php` (822 baris)
- `app/Services/CashInReportService.php` (212 baris)
- `app/Services/HasilAkadReportService.php` (187 baris)
- `app/Services/PrintService.php` (634 baris)

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

### Models (13)
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
- `public/assets/js/pencairan-akad-modal.js` (828 baris)
- `public/assets/js/keuangan/penagihan.js` (1041 baris)
- `public/assets/js/cashout_kavling/index.js` (128 baris)
- `public/assets/js/laporan-cash-in.js` (473 baris)
- `public/assets/js/laporan-hasil-akad.js` (201 baris)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Keuangan` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.
