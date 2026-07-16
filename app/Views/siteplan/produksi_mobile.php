<?php
$idProyek = (int) ($proyek->id_proyek ?? 0);
$initialIdKavling = (int) ($initial_id_kavling ?? 0);
$canUpdateTanggal = !empty($has_akses['update_tanggal_pembangunan']);
?>
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<style>
    .produksi-mobile-page {
        background: #f3f5f7;
        min-height: calc(100vh - 90px);
    }

    .produksi-mobile-shell {
        margin: 0 auto;
        max-width: 860px;
        padding: .75rem;
    }

    .produksi-mobile-toolbar {
        align-items: center;
        display: flex;
        gap: .75rem;
        justify-content: space-between;
        margin-bottom: .75rem;
    }

    .produksi-mobile-toolbar h4 {
        color: #111827;
        font-size: 1.08rem;
        font-weight: 700;
        line-height: 1.25;
        margin: 0;
    }

    .produksi-mobile-toolbar .btn {
        border-radius: 6px;
        white-space: nowrap;
    }

    .produksi-mobile-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: .75rem;
        overflow: hidden;
    }

    .produksi-mobile-card .card-body {
        padding: .9rem;
    }

    .produksi-mobile-summary {
        position: sticky;
        top: 82px;
        z-index: 4;
    }

    .produksi-mobile-summary-title {
        color: #111827;
        font-size: .98rem;
        font-weight: 800;
        line-height: 1.3;
        margin-bottom: .25rem;
        overflow-wrap: anywhere;
    }

    .produksi-mobile-summary-meta {
        color: #667085;
        font-size: .78rem;
        line-height: 1.4;
    }

    .produksi-mobile-metric-grid {
        display: grid;
        gap: .5rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: .75rem;
    }

    .produksi-mobile-metric {
        background: #f8fafc;
        border: 1px solid #e5eaf2;
        border-radius: 8px;
        padding: .65rem;
    }

    .produksi-mobile-metric span {
        color: #6b7280;
        display: block;
        font-size: .72rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: .2rem;
    }

    .produksi-mobile-metric strong {
        color: #111827;
        display: block;
        font-size: .9rem;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .produksi-mobile-page label,
    .produksi-mobile-page .form-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    .produksi-mobile-page .form-control,
    .produksi-mobile-page .select2-container--default .select2-selection--single {
        border-color: #d8dde3;
        border-radius: 6px;
        min-height: 38px;
    }

    .produksi-mobile-page .btn {
        border-radius: 6px;
        font-weight: 700;
    }

    .produksi-mobile-page .btn-primary {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    .produksi-mobile-page .btn-primary:hover,
    .produksi-mobile-page .btn-primary:focus {
        background-color: #174b8f !important;
        border-color: #174b8f !important;
    }

    .produksi-mobile-page .divider {
        margin: .65rem 0 .85rem;
    }

    .produksi-mobile-page .divider-left {
        border-left-color: #2057a3;
        padding-left: .75rem;
    }

    .produksi-mobile-page .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    .produksi-mobile-upload-grid,
    .produksi-mobile-file-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .produksi-mobile-upload-card {
        background: #fff;
        border: 1px dashed #9db5d8;
        border-radius: 8px;
        padding: .75rem;
    }

    .produksi-mobile-upload-title {
        color: #111827;
        display: block;
        font-size: .86rem;
        font-weight: 800;
        line-height: 1.3;
        margin-bottom: .2rem;
    }

    .produksi-mobile-upload-card small {
        color: #667085;
        display: block;
        font-size: .76rem;
        line-height: 1.35;
        margin-bottom: .65rem;
    }

    .produksi-mobile-upload-card .custom-file {
        margin-bottom: .65rem;
    }

    .produksi-mobile-page [id^="list_"] {
        display: grid !important;
        gap: .65rem;
        grid-template-columns: 1fr;
    }

    .produksi-mobile-page .input-foto-container,
    .produksi-mobile-file-tile {
        background: #fff;
        border: 1px solid #d7deea;
        border-radius: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
        width: 100%;
    }

    .produksi-mobile-page .input-foto,
    .produksi-mobile-file-preview {
        background: #f3f6fb;
        border-bottom: 1px solid #e5eaf2;
        min-height: 130px;
        overflow: hidden;
    }

    .produksi-mobile-page .input-foto img,
    .produksi-mobile-file-preview img {
        display: block;
        height: 130px;
        object-fit: cover;
        width: 100%;
    }

    .produksi-mobile-page .input-foto-meta,
    .produksi-mobile-file-body {
        padding: .7rem;
    }

    .produksi-mobile-page .input-foto-meta strong,
    .produksi-mobile-file-title {
        color: #374151;
        display: block;
        font-size: .78rem;
        font-weight: 800;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .produksi-mobile-file-meta,
    .produksi-mobile-page .foto-coordinate-status {
        color: #667085;
        font-size: .76rem;
        line-height: 1.35;
        margin-top: .25rem;
    }

    .produksi-mobile-history-list {
        display: flex;
        flex-direction: column;
        gap: .65rem;
    }

    .produksi-mobile-history-item {
        background: #f8fafc;
        border: 1px solid #e5eaf2;
        border-radius: 8px;
        padding: .75rem;
    }

    .produksi-mobile-history-title {
        color: #111827;
        font-size: .84rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .produksi-mobile-history-meta {
        color: #667085;
        font-size: .76rem;
        line-height: 1.35;
        margin-top: .25rem;
    }

    @media (max-width: 767.98px) {
        .produksi-mobile-shell {
            padding: .5rem;
        }

        .produksi-mobile-summary {
            top: 76px;
        }

        .produksi-mobile-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .produksi-mobile-toolbar .btn {
            width: 100%;
        }

        .produksi-mobile-card .card-body {
            padding: .8rem;
        }
    }
</style>

<div class="app-content content produksi-mobile-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-body">
            <div class="produksi-mobile-shell">
                <div class="produksi-mobile-toolbar">
                    <div>
                        <h4>Produksi Mobile</h4>
                        <div class="text-muted small"><?= esc($proyek->nama_proyek ?? '-') ?></div>
                    </div>
                    <a href="<?= base_url('siteplan/view') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-map-marked-alt mr-50"></i>Siteplan
                    </a>
                </div>

                <div class="produksi-mobile-card">
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label for="pm-id-kavling">Pilih Kavling</label>
                            <select id="pm-id-kavling" class="form-control"></select>
                        </div>
                    </div>
                </div>

                <div id="pm-empty-state" class="produksi-mobile-card">
                    <div class="card-body text-center">
                        <strong class="d-block mb-25">Belum ada kavling dipilih</strong>
                        <span class="text-muted">Cari kavling untuk mulai update progres dan upload foto.</span>
                    </div>
                </div>

                <div id="pm-workspace" class="d-none">
                    <div class="produksi-mobile-card produksi-mobile-summary">
                        <div class="card-body">
                            <div class="produksi-mobile-summary-title" id="pm-summary-title">-</div>
                            <div class="produksi-mobile-summary-meta" id="pm-summary-meta">-</div>
                            <div class="produksi-mobile-metric-grid">
                                <div class="produksi-mobile-metric">
                                    <span>Progres</span>
                                    <strong><span id="pm-summary-progress">0</span>%</strong>
                                </div>
                                <div class="produksi-mobile-metric">
                                    <span>Konsumen</span>
                                    <strong id="pm-summary-konsumen">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="produksi-mobile-card">
                        <div class="card-body">
                            <div class="divider divider-left">
                                <div class="divider-text">Progres & Tanggal</div>
                            </div>
                            <form id="pm-progress-form">
                                <input type="hidden" id="pm-form-id-kavling" name="id_kavling">
                                <input type="hidden" id="pm-form-id-produksi" name="id_produksi">
                                <input type="hidden" id="pm-tanggal-pembangunan-old" name="tanggal_pembangunan_old">
                                <input type="hidden" id="pm-tanggal-rencana-old" name="tanggal_rencana_selesai_pembangunan_old">
                                <input type="hidden" id="pm-tanggal-selesai-old" name="tanggal_selesai_pembangunan_old">
                                <input type="hidden" id="pm-preserve-bp" name="bp">
                                <input type="hidden" id="pm-preserve-st-jalan" name="st_jalan">
                                <input type="hidden" id="pm-preserve-st-saluran" name="st_saluran">
                                <input type="hidden" id="pm-preserve-st-air" name="st_air">
                                <input type="hidden" id="pm-preserve-lpa-tanggal" name="lpa_tanggal">
                                <input type="hidden" id="pm-preserve-listrik-disediakan-no" name="listrik_disediakan_no">
                                <input type="hidden" id="pm-preserve-listrik-disediakan-tanggal" name="listrik_disediakan_tanggal">
                                <input type="hidden" id="pm-preserve-air-deskripsi-unit" name="air_deskripsi_unit">
                                <input type="hidden" id="pm-preserve-sumurbor" name="sumurbor">
                                <input type="hidden" id="pm-preserve-sumurbor-keterangan" name="sumurbor_keterangan">
                                <input type="hidden" id="pm-preserve-sumurbor-tanggal" name="sumurbor_tanggal">

                                <div class="form-group">
                                    <label for="pm-progres-bangunan">Progres Bangunan</label>
                                    <input type="range" class="form-control-range" id="pm-progres-bangunan" name="progres_bangunan" min="0" max="100" step="1" value="0">
                                    <strong><span id="pm-progres-label">0</span>%</strong>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pm-tanggal-pembangunan">Tanggal Pembangunan</label>
                                            <input type="text" class="form-control flatpickr-human-friendly pm-tanggal-field" id="pm-tanggal-pembangunan" name="tanggal_pembangunan" <?= $canUpdateTanggal ? '' : 'disabled' ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pm-tanggal-rencana">Rencana Selesai Pembangunan</label>
                                            <input type="text" class="form-control flatpickr-human-friendly pm-tanggal-field" id="pm-tanggal-rencana" name="tanggal_rencana_selesai_pembangunan" <?= $canUpdateTanggal ? '' : 'disabled' ?>>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="pm-tanggal-selesai">Tanggal Selesai Pembangunan</label>
                                    <input type="text" class="form-control flatpickr-human-friendly" id="pm-tanggal-selesai" name="tanggal_selesai_pembangunan">
                                </div>

                                <div class="form-group">
                                    <label for="pm-keterangan">Keterangan Pembangunan</label>
                                    <textarea class="form-control" id="pm-keterangan" name="produksi_keterangan" rows="3"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-6 col-md-3">
                                        <div class="custom-control custom-switch mb-1">
                                            <input type="checkbox" value="1" class="custom-control-input" id="pm-st-0" name="st_0">
                                            <label class="custom-control-label" for="pm-st-0">sd Sloof</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="custom-control custom-switch mb-1">
                                            <input type="checkbox" value="1" class="custom-control-input" id="pm-st-25" name="st_25">
                                            <label class="custom-control-label" for="pm-st-25">25%</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="custom-control custom-switch mb-1">
                                            <input type="checkbox" value="1" class="custom-control-input" id="pm-st-50" name="st_50">
                                            <label class="custom-control-label" for="pm-st-50">50%</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="custom-control custom-switch mb-1">
                                            <input type="checkbox" value="1" class="custom-control-input" id="pm-st-75" name="st_75">
                                            <label class="custom-control-label" for="pm-st-75">75%</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="custom-control custom-switch mb-1">
                                            <input type="checkbox" value="1" class="custom-control-input" id="pm-st-100" name="st_100">
                                            <label class="custom-control-label" for="pm-st-100">100%</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="custom-control custom-switch mb-1">
                                            <input type="checkbox" value="1" class="custom-control-input" id="pm-lpa" name="lpa">
                                            <label class="custom-control-label" for="pm-lpa">LPA</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="custom-control custom-switch mb-1">
                                            <input type="checkbox" value="1" class="custom-control-input" id="pm-slo" name="slo">
                                            <label class="custom-control-label" for="pm-slo">SLO/NIDI</label>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="pm-save-progress" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-50"></i>Simpan Progres
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="produksi-mobile-card">
                        <div class="card-body">
                            <div class="divider divider-left">
                                <div class="divider-text">Dokumentasi Bangunan</div>
                            </div>
                            <div class="produksi-mobile-upload-grid">
                                <?= view('siteplan/partials/produksi_mobile_upload_card', [
                                    'id' => 'prod_foto_konstruksi',
                                    'title' => 'Konstruksi',
                                    'help' => 'Pembesian, pondasi, sloof, ringbalok, dinding, atap, plafon.',
                                ]) ?>
                                <?= view('siteplan/partials/produksi_mobile_upload_card', [
                                    'id' => 'prod_foto_exterior',
                                    'title' => 'Exterior',
                                    'help' => 'Tampak depan/belakang dan kondisi luar unit.',
                                ]) ?>
                                <?= view('siteplan/partials/produksi_mobile_upload_card', [
                                    'id' => 'prod_foto_interior',
                                    'title' => 'Interior',
                                    'help' => 'Kamar, dapur, toilet, ruang tengah, finishing.',
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="produksi-mobile-card">
                        <div class="card-body">
                            <div class="divider divider-left">
                                <div class="divider-text">Jalan, Listrik, Air</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pm-listrik-jenis">Jenis Sumber Listrik</label>
                                        <select id="pm-listrik-jenis" name="listrik_jenis" class="form-control pm-progress-field" form="pm-progress-form">
                                            <option value="PLN">PLN</option>
                                            <option value="Disendiakan Pengembang">Disendiakan Pengembang (Dalam Pengajuan)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="pm-listrik-pln">No ID Pelanggan/Nomor Meteran</label>
                                        <input type="text" id="pm-listrik-pln" name="listrik_pln" class="form-control pm-progress-field" form="pm-progress-form">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pm-air-jenis">Jenis Sumber Air</label>
                                        <select id="pm-air-jenis" name="air_jenis" class="form-control pm-progress-field" form="pm-progress-form">
                                            <option value="Air Tanah">Air Tanah</option>
                                            <option value="Komunal Warga">Komunal Warga</option>
                                            <option value="PDAM">PDAM</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="pm-air-pdam-no">No Meteran Air PDAM</label>
                                        <input type="text" id="pm-air-pdam-no" name="air_pdam_no" class="form-control pm-progress-field" form="pm-progress-form">
                                    </div>
                                </div>
                            </div>
                            <div class="produksi-mobile-upload-grid">
                                <?= view('siteplan/partials/produksi_mobile_upload_card', [
                                    'id' => 'jalan_foto',
                                    'title' => 'Foto Jalan',
                                    'help' => 'Kondisi jalan awal/eksisting.',
                                ]) ?>
                                <?= view('siteplan/partials/produksi_mobile_upload_card', [
                                    'id' => 'jalan_foto_update',
                                    'title' => 'Jalan Update',
                                    'help' => 'Update setelah akad/paving.',
                                ]) ?>
                                <?= view('siteplan/partials/produksi_mobile_upload_card', [
                                    'id' => 'listrik_pln_foto',
                                    'title' => 'Listrik PLN',
                                    'help' => 'Lampu menyala, meteran, atau bukti ketersediaan.',
                                ]) ?>
                                <?= view('siteplan/partials/produksi_mobile_upload_card', [
                                    'id' => 'air_tanah',
                                    'title' => 'Air Tanah',
                                    'help' => 'Air mengalir dan sumber air tanah.',
                                ]) ?>
                                <?= view('siteplan/partials/produksi_mobile_upload_card', [
                                    'id' => 'air_komunal',
                                    'title' => 'Air Komunal',
                                    'help' => 'Air mengalir dari sumber komunal.',
                                ]) ?>
                                <?= view('siteplan/partials/produksi_mobile_upload_card', [
                                    'id' => 'air_pdam',
                                    'title' => 'Air PDAM',
                                    'help' => 'Air mengalir dan meteran PDAM.',
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="produksi-mobile-card">
                        <div class="card-body">
                            <div class="divider divider-left">
                                <div class="divider-text">Foto Tersimpan</div>
                            </div>
                            <div id="pm-file-list" class="produksi-mobile-file-grid"></div>
                        </div>
                    </div>

                    <div class="produksi-mobile-card">
                        <div class="card-body">
                            <div class="divider divider-left">
                                <div class="divider-text">Riwayat</div>
                            </div>
                            <div id="pm-history-list" class="produksi-mobile-history-list"></div>
                            <button type="button" id="pm-history-more" class="btn btn-outline-primary btn-sm mt-1 d-none">Muat lagi</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script>
    const pmConfig = {
        idProyek: <?= json_encode($idProyek) ?>,
        initialIdKavling: <?= json_encode($initialIdKavling) ?>,
        historyLimit: 8
    };
</script>
<script src="<?= base_url() ?>assets/js/siteplan/produksi_mobile.js?v=<?= filemtime(FCPATH.'assets/js/siteplan/produksi_mobile.js') ?>"></script>
</script>
