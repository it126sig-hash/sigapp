<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/fixed-columns/fixedColumns.bootstrap4.css">
<link rel="stylesheet" type="text/css"
  href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<?php $canKeuangan = !function_exists('in_groups') || in_groups(['1', '3']); ?>
<script>
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
  var canKeuangan = <?= $canKeuangan ? 'true' : 'false' ?>;
  const dt_proyek = {}
</script>
<style>
  .list-hasil-akad-page {
    text-transform: uppercase;
  }

  .list-hasil-akad-page .card-header {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
    padding: .6rem .85rem;
  }

  .list-hasil-akad-page .list-hasil-akad-title {
    color: #111827;
    font-size: 1rem;
    font-weight: 800;
    margin: 0;
    white-space: nowrap;
  }

  .list-hasil-akad-page .list-hasil-akad-divider {
    align-self: stretch;
    background: #e5e7eb;
    flex: 0 0 1px;
    width: 1px;
  }

  .list-hasil-akad-page .list-hasil-akad-filter {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .list-hasil-akad-page .filter-field {
    flex: 0 0 150px;
  }

  .list-hasil-akad-page .filter-field-sm {
    flex: 0 0 120px;
  }

  .list-hasil-akad-page .filter-action {
    flex: 0 0 auto;
  }

  .list-hasil-akad-page .form-control,
  .list-hasil-akad-page .select2-selection {
    border-color: #d8dde3 !important;
    border-radius: 6px !important;
    font-size: .78rem;
    min-height: 30px;
  }

  .list-hasil-akad-page select.form-control {
    padding: .25rem .5rem;
  }

  .list-hasil-akad-page .select2-selection__rendered {
    line-height: 28px !important;
  }

  .list-hasil-akad-page .select2-selection__arrow {
    height: 28px !important;
  }

  .list-hasil-akad-page .card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: none;
  }

  .list-hasil-akad-page .card-datatable {
    padding: .35rem;
  }

  #data_table_hasil_akad {
    border-collapse: separate !important;
    border-spacing: 0;
    font-size: 10px;
    vertical-align: middle !important;
    width: 100% !important;
  }

  #data_table_hasil_akad thead th {
    background: #eef5ff;
    border-bottom: 1px solid #c9ddf5;
    color: #2057a3;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: 0;
    padding: .55rem .5rem;
    white-space: nowrap;
  }

  #data_table_hasil_akad tbody td {
    border-color: #edf0f2;
    color: #111827;
    padding: .45rem .5rem;
    vertical-align: middle !important;
    white-space: nowrap;
  }

  #data_table_hasil_akad tbody tr:hover td {
    background: #f8fbff;
  }

  .hasil-akad-detail-toggle {
    align-items: center;
    border-radius: 6px;
    display: inline-flex;
    font-size: .72rem;
    font-weight: 800;
    height: 28px;
    justify-content: center;
    width: 28px;
  }

  tr.shown .hasil-akad-detail-toggle {
    background: #2057a3;
    border-color: #2057a3;
    color: #fff;
  }

  .hasil-akad-child-wrap {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin: .35rem 0;
    padding: .65rem;
    text-transform: uppercase;
  }

  .hasil-akad-child-table {
    font-size: .75rem;
    margin-bottom: .75rem;
  }

  .hasil-akad-child-table th {
    background: #fff;
    color: #2057a3;
    font-weight: 800;
    white-space: nowrap;
  }

  .hasil-akad-child-table td,
  .hasil-akad-child-table th {
    padding: .45rem .55rem;
    vertical-align: middle;
  }

  .hasil-akad-child-loading,
  .hasil-akad-child-error,
  .hasil-akad-child-empty {
    color: #6b7280;
    font-size: .78rem;
    font-weight: 700;
    padding: .55rem;
  }

  .hasil-akad-child-error {
    color: #b91c1c;
  }

  #modal-filter .flatpickr-wrapper {
    width: 100%;
  }

  .hasil-akad-summary {
    border-top: 1px solid #e5e7eb;
    padding: .5rem .75rem .8rem;
  }

  .hasil-akad-summary .divider {
    margin: .25rem 0 .65rem;
  }

  .hasil-akad-summary-grid {
    display: grid;
    gap: .65rem;
    grid-template-columns: repeat(6, minmax(0, 1fr));
  }

  .hasil-akad-summary-card {
    align-items: center;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    display: flex;
    gap: .65rem;
    min-width: 0;
    padding: .7rem;
  }

  .hasil-akad-summary-card>span:last-child {
    min-width: 0;
  }

  .hasil-akad-summary-icon {
    align-items: center;
    background: #eef5ff;
    border-radius: 7px;
    color: #2057a3;
    display: inline-flex;
    flex: 0 0 34px;
    height: 34px;
    justify-content: center;
  }

  .hasil-akad-summary-label {
    color: #6b7280;
    display: block;
    font-size: .65rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: .2rem;
  }

  .hasil-akad-summary-value {
    color: #111827;
    display: block;
    font-size: .8rem;
    font-weight: 800;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  @media (max-width: 1199.98px) {
    .hasil-akad-summary-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
  }

  @media (max-width: 767.98px) {
    .hasil-akad-summary-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .flatpickr-calendar.multiMonth {
      background: #fff;
      max-width: 100%;
      width: 100% !important;
    }

    .flatpickr-calendar.multiMonth .flatpickr-months,
    .flatpickr-calendar.multiMonth .flatpickr-innerContainer,
    .flatpickr-calendar.multiMonth .flatpickr-rContainer,
    .flatpickr-calendar.multiMonth .flatpickr-weekdays,
    .flatpickr-calendar.multiMonth .flatpickr-days,
    .flatpickr-calendar.multiMonth .dayContainer {
      background: #fff;
    }

    .flatpickr-calendar.multiMonth .flatpickr-rContainer,
    .flatpickr-calendar.multiMonth .flatpickr-days,
    .flatpickr-calendar.multiMonth .flatpickr-weekdays {
      max-width: 100%;
      width: 100%;
    }

    .flatpickr-calendar.multiMonth .flatpickr-month,
    .flatpickr-calendar.multiMonth .flatpickr-weekdaycontainer,
    .flatpickr-calendar.multiMonth .dayContainer {
      flex: 0 0 50%;
      max-width: 50% !important;
      min-width: 0 !important;
      width: 50% !important;
    }

    .flatpickr-calendar.multiMonth .flatpickr-weekday {
      font-size: .65rem;
    }

    .flatpickr-calendar.multiMonth .flatpickr-day {
      font-size: .7rem;
      height: 30px;
      line-height: 30px;
      max-width: none;
      width: 14.2857143%;
    }
  }

  @media (max-width: 479.98px) {
    .hasil-akad-summary-grid {
      grid-template-columns: 1fr;
    }
  }

  #data_table_pengajuan_batch {
    border-collapse: separate !important;
    border-spacing: 0;
    font-size: 11px;
    vertical-align: middle !important;
    width: 100% !important;
  }

  #data_table_pengajuan_batch thead th {
    background: #eef5ff;
    border-bottom: 1px solid #c9ddf5;
    color: #2057a3;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: 0;
    padding: .55rem .5rem;
    white-space: nowrap;
  }

  #data_table_pengajuan_batch tbody td {
    border-color: #edf0f2;
    color: #111827;
    padding: .45rem .5rem;
    vertical-align: middle !important;
    white-space: nowrap;
  }

  #data_table_pengajuan_batch tbody tr:hover td {
    background: #f8fbff;
  }

  #hasil-akad-main-tabs .nav-link {
    font-size: 0.85rem;
    padding: 0.55rem 1.1rem;
    border-radius: 6px 6px 0 0;
  }

  #hasil-akad-main-tabs .nav-link.active {
    background-color: #fff;
    border-color: #d8dde3 #d8dde3 #fff;
    color: #2057a3;
  }
</style>
<!-- /.card-header -->
<div class="app-content content list-hasil-akad-page">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <ul class="nav nav-tabs mb-1" id="hasil-akad-main-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active font-weight-bold" id="tab-nav-kavling" data-toggle="tab" href="#tab-pane-kavling" role="tab">
              <i class="fas fa-home mr-25"></i> List Kavling (Hasil Akad)
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link font-weight-bold" id="tab-nav-pengajuan" data-toggle="tab" href="#tab-pane-pengajuan" role="tab">
              <i class="fas fa-file-invoice-dollar mr-25"></i> Daftar Pengajuan &amp; Riwayat
            </a>
          </li>
        </ul>

        <div class="tab-content">
          <!-- TAB 1: LIST KAVLING -->
          <div class="tab-pane show active" id="tab-pane-kavling" role="tabpanel">
            <div class="card">
              <div class="card-header">
                <h5 class="list-hasil-akad-title"><?= $data['title'] ?></h5>
                <div class="list-hasil-akad-divider"></div>
                <div class="list-hasil-akad-filter">
                  <?php if ($canKeuangan): ?>
                  <div class="filter-action">
                    <button type="button" class="btn btn-success waves-effect btn-sm text-uppercase" id="btn-batch-pengajuan">
                      <i class="fas fa-layer-group mr-25"></i> Buat Pengajuan
                    </button>
                  </div>
                  <?php endif; ?>
                  <div class="filter-action">
                    <button type="button" class="btn btn-primary waves-effect btn-sm text-uppercase" data-toggle="modal" data-target="#modal-filter">
                      <i class="fas fa-filter mr-25"></i> Filter Data
                    </button>
                  </div>
                  <div class="filter-action">
                    <button type="button" class="btn btn-outline-secondary waves-effect btn-sm text-uppercase btn-clear-filter">
                      <i class="fas fa-times mr-25"></i> Clear Filter
                    </button>
                  </div>
                  <?php if ($canKeuangan): ?>
                  <div class="filter-action">
                    <a href="<?= base_url('keuangan/hasil-akad/import') ?>" class="btn btn-outline-primary waves-effect btn-sm text-uppercase">
                      <i class="fas fa-upload mr-25"></i> Import Tanggal Cair
                    </a>
                  </div>
                  <?php endif; ?>
                </div>
                <div class="w-100 mt-50" id="active_filter_container" style="display: none;">
                  <div class="text-muted" style="font-size: 0.8rem;">
                    <i class="fas fa-info-circle mr-25"></i> <strong>Filter Aktif:</strong> <span id="active_filter_text"></span>
                  </div>
                </div>
              </div>
            </div>
            <div class="card">
              <div class="card-datatable">
                <table id="data_table_hasil_akad" class="datatables-basic table compact">
                  <thead>
                    <tr>
                      <th></th>
                      <th>No</th>
                      <th>Aksi</th>
                      <th>Tgl Akad</th>
                      <th>Nama Konsumen</th>
                      <th>Blok</th>
                      <th>No Kavling</th>
                      <th>Tipe Rumah</th>
                      <th>Harga Jual</th>
                      <th>KPR (ACC KPR)</th>
                      <th>Pengajuan Pencairan</th>
                      <th>Sudah Cair</th>
                      <th>Sisa</th>
                    </tr>
                  </thead>
                </table>
              </div>
              <div class="hasil-akad-summary" id="hasil_akad_summary" aria-live="polite">
                <div class="divider divider-left">
                  <div class="divider-text">Ringkasan Hasil Filter</div>
                </div>
                <div class="hasil-akad-summary-grid">
                  <div class="hasil-akad-summary-card">
                    <span class="hasil-akad-summary-icon"><i class="fas fa-home"></i></span>
                    <span class="min-width-0">
                      <span class="hasil-akad-summary-label">Jumlah Kavling</span>
                      <span class="hasil-akad-summary-value" id="summary_jumlah_kavling">0</span>
                    </span>
                  </div>
                  <div class="hasil-akad-summary-card">
                    <span class="hasil-akad-summary-icon"><i class="fas fa-tag"></i></span>
                    <span class="min-width-0">
                      <span class="hasil-akad-summary-label">Total Harga Jual</span>
                      <span class="hasil-akad-summary-value" id="summary_total_harga_jual">Rp 0</span>
                    </span>
                  </div>
                  <div class="hasil-akad-summary-card">
                    <span class="hasil-akad-summary-icon"><i class="fas fa-university"></i></span>
                    <span class="min-width-0">
                      <span class="hasil-akad-summary-label">Total ACC KPR</span>
                      <span class="hasil-akad-summary-value" id="summary_total_acc_kpr">Rp 0</span>
                    </span>
                  </div>
                  <div class="hasil-akad-summary-card">
                    <span class="hasil-akad-summary-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                    <span class="min-width-0">
                      <span class="hasil-akad-summary-label">Pengajuan Outstanding</span>
                      <span class="hasil-akad-summary-value text-warning" id="summary_total_pengajuan_outstanding">Rp 0</span>
                    </span>
                  </div>
                  <div class="hasil-akad-summary-card">
                    <span class="hasil-akad-summary-icon"><i class="fas fa-check-circle"></i></span>
                    <span class="min-width-0">
                      <span class="hasil-akad-summary-label">Sudah Cair</span>
                      <span class="hasil-akad-summary-value text-success" id="summary_total_sudah_cair">Rp 0</span>
                    </span>
                  </div>
                  <div class="hasil-akad-summary-card">
                    <span class="hasil-akad-summary-icon"><i class="fas fa-wallet"></i></span>
                    <span class="min-width-0">
                      <span class="hasil-akad-summary-label">Sisa</span>
                      <span class="hasil-akad-summary-value text-danger" id="summary_total_sisa">Rp 0</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: DAFTAR PENGAJUAN BATCH -->
          <div class="tab-pane" id="tab-pane-pengajuan" role="tabpanel">
            <div class="card mb-1">
              <div class="card-header">
                <h5 class="list-hasil-akad-title">Daftar Pengajuan Pencairan Akad &amp; Retensi</h5>
                <div class="list-hasil-akad-divider"></div>
                <div class="list-hasil-akad-filter">
                  <div class="filter-field-sm">
                    <select id="batch_status_filter" class="form-control form-control-sm">
                      <option value="">Semua Status</option>
                      <option value="active">Aktif / Belum Cair</option>
                      <option value="partial">Partial</option>
                      <option value="paid">Lunas</option>
                      <option value="void">Void</option>
                    </select>
                  </div>
                  <div class="filter-field">
                    <input type="text" id="batch_periode_tanggal" class="form-control form-control-sm" placeholder="Filter Tanggal Pengajuan" autocomplete="off">
                  </div>
                  <div class="filter-action">
                    <button type="button" class="btn btn-outline-secondary btn-sm text-uppercase" id="btn-clear-filter-batch">
                      <i class="fas fa-times mr-25"></i> Reset
                    </button>
                  </div>
                  <div class="filter-action">
                    <button type="button" class="btn btn-primary btn-sm text-uppercase" id="btn-refresh-batch">
                      <i class="fas fa-sync-alt mr-25"></i> Refresh
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="card">
              <div class="card-datatable">
                <table id="data_table_pengajuan_batch" class="datatables-basic table compact w-100">
                  <thead>
                    <tr>
                      <th class="text-center" style="width: 4%;">No</th>
                      <th class="text-left" style="width: 18%;">No. Pengajuan</th>
                      <th class="text-left" style="width: 30%;">Kavling &amp; Item Diajukan</th>
                      <th class="text-right" style="width: 10%;">Total Pengajuan</th>
                      <th class="text-right" style="width: 9%;">Sudah Cair</th>
                      <th class="text-right" style="width: 9%;">Sisa</th>
                      <th class="text-center" style="width: 8%;">Status</th>
                      <th class="text-center" style="min-width: 160px; width: 12%;">Aksi</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Modal Filter -->
<div class="modal modal-slide-in fade" id="modal-filter">
  <div class="modal-dialog sidebar-sm">
    <form class="add-new-record modal-content pt-0">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
      <div class="modal-header mb-1">
        <h5 class="modal-title" id="exampleModalLabel">Filter Data</h5>
      </div>
      <div class="modal-body flex-grow-1">
        <div class="form-group">
          <label class="form-label">Jenis Tanggal</label>
          <select id="jenis_tanggal" name="jenis_tanggal" class="form-control">
            <option value="tanggal_pengajuan">Tanggal Pengajuan Pencairan</option>
            <option value="tanggal_akad" selected>Tanggal Akad</option>
            <option value="tanggal_pencairan">Tanggal Pencairan Hasil Akad &amp; Retensi</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Periode Tanggal</label>
          <input type="text" id="periode_tanggal" class="form-control" placeholder="Pilih Periode" autocomplete="off">
        </div>
        <div class="form-group">
          <label class="form-label">Cluster</label>
          <select disabled id="id_cluster" name="id_cluster" class="select2 form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label">Blok / Jalan</label>
          <select disabled id="id_jalan" name="id_jalan" class="select2 form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label">Status Pencairan</label>
          <select id="status_cair" name="status_cair" class="form-control">
            <option value="belum_cair" selected>Belum Cair</option>
            <option value="sudah_cair">Sudah Cair</option>
            <option value="">Semua</option>
          </select>
        </div>
        <button type="button" id="btn_draw" class="btn btn-primary btn-block waves-effect text-uppercase mt-2" data-dismiss="modal">
          <i class="fas fa-filter mr-25"></i> Terapkan Filter
        </button>
        <button type="button" class="btn btn-outline-secondary btn-block waves-effect text-uppercase mt-1 btn-clear-filter" data-dismiss="modal">
          <i class="fas fa-times mr-25"></i> Clear Filter
        </button>
      </div>
    </form>
  </div>
</div>

<?= view('keuangan/partials/modal_pencairan_akad') ?>
<?= view('keuangan/partials/modal_batch_pengajuan') ?>
<?= view('keuangan/partials/modal_batch_pencairan') ?>
<?= view('keuangan/partials/modal_batch_history') ?>

<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/forms/validation/jquery.validate.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/polyfill.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/bootstrap/extensions/fixed-columns/dataTables.fixedColumns.js"></script>

<script src="<?= base_url() ?>assets/js/pencairan-akad-modal.js?<?= filemtime(FCPATH . 'assets/js/pencairan-akad-modal.js') ?>"></script>
<script>
  let hasilAkadTable = null;
  let hasilAkadDetailCache = {};
  let periodeTanggalPicker = null;
  let batchPengajuanTable = null;
  let batchPeriodePicker = null;

  function hasilAkadSummaryMoney(value) {
    const nominal = parseFloat(value || 0);
    return "Rp " + num_format(Number.isFinite(nominal) ? nominal : 0);
  }

  function renderHasilAkadSummary(summary) {
    const data = summary || {};
    const jumlahKavling = parseInt(data.jumlah_kavling || 0, 10);

    $("#summary_jumlah_kavling").text(num_format(Number.isFinite(jumlahKavling) ? jumlahKavling : 0));
    $("#summary_total_harga_jual").text(hasilAkadSummaryMoney(data.total_harga_jual));
    $("#summary_total_acc_kpr").text(hasilAkadSummaryMoney(data.total_acc_kpr));
    $("#summary_total_pengajuan_outstanding").text(hasilAkadSummaryMoney(data.total_pengajuan_outstanding));
    $("#summary_total_sudah_cair").text(hasilAkadSummaryMoney(data.total_sudah_cair));
    $("#summary_total_sisa").text(hasilAkadSummaryMoney(data.total_sisa));
    $("#hasil_akad_summary").attr("aria-busy", "false");
  }

  function paChildEscape(value) {
    return $("<div>").text(value === null || value === undefined ? "" : value).html();
  }

  function paChildStatusBadge(status) {
    const map = {
      active: '<span class="badge badge-secondary">Aktif</span>',
      partial: '<span class="badge badge-warning">Partial</span>',
      paid: '<span class="badge badge-success">Paid</span>',
      void: '<span class="badge badge-light-danger">Void</span>',
    };
    return map[status] || status;
  }

  function formatHasilAkadItems(items) {
    if (!Array.isArray(items) || items.length === 0) {
      return '<div class="hasil-akad-child-empty">Belum ada item retensi/hasil akad.</div>';
    }

    const rows = items.map(function(item) {
      const label = item.jenis === "retensi" ?
        "Retensi - " + paChildEscape(item.nama_jaminan || "-") :
        "Termin #" + item.urutan_tenor;
      const locked = item.is_locked ?
        '<span class="badge badge-light-secondary">Sudah diajukan pencairan</span>' :
        '<span class="badge badge-light-primary">Belum Diajukan</span>';

      return `
        <tr>
          <td>${item.jenis === "retensi" ? "Retensi" : "Termin"}</td>
          <td>${label}</td>
          <td class="text-right">Rp ${num_format(parseFloat(item.nominal || 0))}</td>
          <td class="text-right">Rp ${num_format(parseFloat(item.sisa || 0))}</td>
          <td>${locked}</td>
        </tr>`;
    }).join("");

    return `
      <table class="table table-sm table-bordered hasil-akad-child-table mb-0">
        <thead>
          <tr>
            <th>Jenis</th>
            <th>Keterangan</th>
            <th class="text-right">Nominal</th>
            <th class="text-right">Sisa</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>`;
  }

  function formatHasilAkadPengajuan(pengajuan) {
    if (!Array.isArray(pengajuan) || pengajuan.length === 0) {
      return '<div class="hasil-akad-child-empty">Belum ada pengajuan pencairan.</div>';
    }

    const rows = pengajuan.map(function(row) {
      const lampiran = row.access_url ?
        `<a href="${row.access_url}" target="_blank">Lihat</a>` :
        "-";

      return `
        <tr>
          <td>${row.tanggal_pengajuan ? format_date(row.tanggal_pengajuan) : "-"}</td>
          <td class="text-right">Rp ${num_format(parseFloat(row.total_pengajuan || 0))}</td>
          <td class="text-right">Rp ${num_format(parseFloat(row.total_cair || 0))}</td>
          <td>${paChildStatusBadge(row.status)}</td>
        </tr>`;
    }).join("");

    return `
      <table class="table table-sm table-bordered hasil-akad-child-table mb-0">
        <thead>
          <tr>
            <th>Tgl Pengajuan</th>
            <th class="text-right">Total Pengajuan</th>
            <th class="text-right">Total Cair</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>`;
  }

  function formatHasilAkadChild(data) {
    return `
      <div class="hasil-akad-child-wrap">
        <div class="font-weight-bold mb-50">Item Retensi &amp; Termin</div>
        ${formatHasilAkadItems(data.items)}
        <div class="font-weight-bold mt-1 mb-50">Daftar Pengajuan</div>
        ${formatHasilAkadPengajuan(data.pengajuan)}
      </div>`;
  }

  function loadHasilAkadChild(row, rowData) {
    const idMkdt = rowData.id_mkdt;
    row.child('<div class="hasil-akad-child-wrap"><div class="hasil-akad-child-loading">Memuat detail...</div></div>').show();

    if (hasilAkadDetailCache[idMkdt]) {
      row.child(formatHasilAkadChild(hasilAkadDetailCache[idMkdt])).show();
      return;
    }

    $.ajax({
      url: base_url + "keuangan/hasil-akad/list/detail",
      type: "post",
      dataType: "json",
      data: {
        [csrfName]: csrfHash,
        id_mkdt: idMkdt,
      },
      success: function(r) {
        if (r.token) csrfHash = r.token;
        if (r.success === true) {
          hasilAkadDetailCache[idMkdt] = {
            items: r.items || [],
            pengajuan: r.pengajuan || []
          };
          row.child(formatHasilAkadChild(hasilAkadDetailCache[idMkdt])).show();
        } else {
          row.child('<div class="hasil-akad-child-wrap"><div class="hasil-akad-child-error">' + paChildEscape(r.message || "Gagal memuat detail") + '</div></div>').show();
        }
      },
      error: function() {
        row.child('<div class="hasil-akad-child-wrap"><div class="hasil-akad-child-error">Terjadi kesalahan saat memuat detail.</div></div>').show();
      },
    });
  }

  $(function() {
    hasilAkadTable = $('#data_table_hasil_akad').DataTable({
      fnDrawCallback: function() {
        var api = this.api();
        setTimeout(function() {
          api.columns.adjust();
        }, 10);
      },
      scrollY: "50vh",
      scrollX: true,
      scrollCollapse: true,
      autoWidth: false,
      processing: true,
      serverSide: true,
      lengthChange: true,
      pageLength: 25,
      searching: true,
      ordering: true,
      paging: true,
      order: [
        [3, "asc"]
      ],
      columns: [
        {
          data: null,
          orderable: false,
          searchable: false,
          className: "text-center",
          defaultContent: '<button type="button" class="btn btn-outline-primary btn-sm hasil-akad-detail-toggle"><i class="fas fa-chevron-down"></i></button>'
        },
        {
          data: "no",
          orderable: false,
          searchable: false,
          className: "text-center"
        },
        {
          data: "Aksi",
          orderable: false,
          searchable: false,
          className: "text-center"
        },
        {
          data: "akad_tgl",
          name: "m.akad_tgl"
        },
        {
          data: "nama_konsumen",
          name: "c.nama_konsumen"
        },
        {
          data: "nama_jalan",
          name: "j.nama_jalan"
        },
        {
          data: "no_kavling",
          name: "k.no_kavling",
          className: "text-center"
        },
        {
          data: "tipe_rumah",
          orderable: false
        },
        {
          data: "hargajual",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "harga_kpr_acc",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "pengajuan_outstanding",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "sudah_cair",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "sisa",
          orderable: false,
          searchable: false,
          className: "text-right font-weight-bold"
        }
      ],
      ajax: {
        url: base_url + 'keuangan/hasil-akad/list/ambil-grouped',
        type: "POST",
        dataType: "json",
        data: function(data) {
          data[csrfName] = csrfHash
          data.id_proyek = activeProyekId()
          data.id_cluster = $("#id_cluster").val()
          data.id_jalan = $("#id_jalan").val()
          data.status_cair = $("#status_cair").val()
          data.jenis_tanggal = $("#jenis_tanggal").val()
          data.periode_tanggal = $("#periode_tanggal").val()
        },
        dataSrc: function(r) {
          if (r.token) csrfHash = r.token
          renderHasilAkadSummary(r.summary)
          return r.data;
        },
        async: "true"
      }
    });

    $('#data_table_hasil_akad')
      .on('preXhr.dt', function() {
        $("#hasil_akad_summary").attr("aria-busy", "true");
      })
      .on('error.dt', function() {
        renderHasilAkadSummary({});
      });

    $('#data_table_hasil_akad tbody').on('click', '.hasil-akad-detail-toggle', function() {
      const tr = $(this).closest('tr');
      const row = hasilAkadTable.row(tr);

      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        $(this).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        return;
      }

      tr.addClass('shown');
      $(this).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
      loadHasilAkadChild(row, row.data());
    });

    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        hasilAkadTable.search(this.value).draw();
      });

    if (activeProyekId()) {
      $("#id_cluster").prop("disabled", false);
      updateActiveFilterText();
      hasilAkadTable.draw();
    }

    $("#id_cluster").select2({
      placeholder: "Pilih Cluster",
      allowClear: true,
      ajax: {
        url: base_url + "/cluster/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return {
            [csrfName]: csrfHash,
            search: params.term,
            id_proyek: activeProyekId()
          };
        },
        processResults: function(r) {
          csrfHash = r.token

          let results = [];
          $.each(r.data, function(index, item) {
            results.push({
              id: item[0],
              text: item[3]
            });
          });

          return {
            results: results
          };
        },
        cache: true
      },
    })
    $("#id_cluster").on("change", function(e) {
      $('#id_jalan').val(null).trigger('change');
      if (this.value)
        $("#id_jalan").prop("disabled", false)
      else
        $("#id_jalan").prop("disabled", true)
    });

    $("#id_jalan").select2({
      placeholder: "Pilih Blok",
      allowClear: true,
      ajax: {
        url: base_url + "/jalan/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return {
            [csrfName]: csrfHash,
            search: params.term,
            id_cluster: $("#id_cluster").val(),
            id_proyek: activeProyekId()
          };
        },
        processResults: function(r) {
          csrfHash = r.token

          let results = [];
          $.each(r.data, function(index, item) {
            results.push({
              id: item[0],
              text: item[3]
            });
          });

          return {
            results: results
          };
        },
        cache: true
      },
    })

    $("#modal-filter").on("shown.bs.modal", function() {
      if (!periodeTanggalPicker) {
        periodeTanggalPicker = $("#periode_tanggal").flatpickr({
          mode: "range",
          dateFormat: "Y-m-d",
          altInput: true,
          altFormat: "d-m-Y",
          showMonths: 2,
          static: window.matchMedia("(max-width: 767.98px)").matches,
        });
      }
    });

    function updateActiveFilterText() {
      let activeFilters = [];

      let tgl = $("#periode_tanggal").val();
      let jenisTanggal = $("#jenis_tanggal").find("option:selected").text();
      if (tgl) activeFilters.push(jenisTanggal + ": " + tgl.replace(" to ", " s/d "));

      let cluster = $("#id_cluster").find("option:selected").text();
      if ($("#id_cluster").val() && cluster) activeFilters.push("Cluster: " + cluster);

      let jalan = $("#id_jalan").find("option:selected").text();
      if ($("#id_jalan").val() && jalan) activeFilters.push("Blok/Jalan: " + jalan);

      let status = $("#status_cair").find("option:selected").text();
      if ($("#status_cair").val() && status && status !== "Semua") activeFilters.push("Status Pencairan: " + status);

      if (activeFilters.length > 0) {
        $("#active_filter_text").text(activeFilters.join(" | "));
        $("#active_filter_container").show();
      } else {
        $("#active_filter_container").hide();
        $("#active_filter_text").text("");
      }
    }

    $(".btn-clear-filter").on("click", function(e) {
      if (periodeTanggalPicker) periodeTanggalPicker.clear();
      $("#jenis_tanggal").val("tanggal_akad");
      $("#id_cluster").val(null).trigger("change");
      $("#id_jalan").val(null).trigger("change");
      $("#status_cair").val("belum_cair");

      updateActiveFilterText();
      hasilAkadDetailCache = {};
      renderHasilAkadSummary({});
      hasilAkadTable.draw();
    });

    $("#btn_draw").on("click", function(e) {
      updateActiveFilterText();
      hasilAkadDetailCache = {};
      hasilAkadTable.draw();
    })

    $("#status_cair").on("change", function(e) {
      updateActiveFilterText();
      hasilAkadDetailCache = {};
      hasilAkadTable.draw();
    })

    /* =========================================================================
     * TAB SWITCHING & BATCH TABLE
     * ========================================================================= */
    function initBatchPengajuanTable() {
      if (batchPengajuanTable) {
        batchPengajuanTable.columns.adjust().draw();
        return;
      }

      if (!batchPeriodePicker && $('#batch_periode_tanggal').length) {
        batchPeriodePicker = $('#batch_periode_tanggal').flatpickr({
          mode: "range",
          dateFormat: "Y-m-d",
          altInput: true,
          altFormat: "d-m-Y",
          showMonths: 2,
          static: window.matchMedia("(max-width: 767.98px)").matches,
          onClose: function() {
            if (batchPengajuanTable) batchPengajuanTable.ajax.reload();
          }
        });
      }

      batchPengajuanTable = $('#data_table_pengajuan_batch').DataTable({
        scrollY: "50vh",
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        lengthChange: true,
        pageLength: 25,
        searching: true,
        ordering: true,
        paging: true,
        order: [
          [1, "desc"]
        ],
        columns: [
          { data: "no", orderable: false, searchable: false, className: "text-center align-middle" },
          { data: "nomor_batch", name: "b.nomor_batch", className: "text-left align-middle" },
          { data: "jumlah_kavling", orderable: false, searchable: false, className: "text-left align-middle" },
          { data: "total_pengajuan", name: "b.total_pengajuan", className: "text-right align-middle" },
          { data: "total_cair", name: "b.total_cair", className: "text-right align-middle" },
          { data: "sisa", orderable: false, searchable: false, className: "text-right align-middle" },
          { data: "status", name: "b.status", className: "text-center align-middle" },
          { data: "Aksi", orderable: false, searchable: false, className: "text-center align-middle text-nowrap", width: "160px" }
        ],
        ajax: {
          url: base_url + 'keuangan/pencairan-akad/batch/list',
          type: "POST",
          dataType: "json",
          data: function(d) {
            d[csrfName] = csrfHash;
            d.id_proyek = activeProyekId();
            d.status = $('#batch_status_filter').val();
            d.periode_tanggal = $('#batch_periode_tanggal').val();
          },
          dataSrc: function(r) {
            if (r.token) csrfHash = r.token;
            return r.data;
          }
        }
      });

      $('#data_table_pengajuan_batch_filter input').off().on('change', function() {
        batchPengajuanTable.search(this.value).draw();
      });
    }

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
      var target = $(e.target).attr("href");
      if (target === "#tab-pane-pengajuan") {
        initBatchPengajuanTable();
      } else if (target === "#tab-pane-kavling") {
        if (hasilAkadTable) {
          hasilAkadTable.columns.adjust().draw(false);
        }
      }
    });

    $('#batch_status_filter').on('change', function() {
      if (batchPengajuanTable) batchPengajuanTable.ajax.reload();
    });

    $('#btn-refresh-batch').on('click', function() {
      if (batchPengajuanTable) batchPengajuanTable.ajax.reload();
    });

    $('#btn-clear-filter-batch').on('click', function() {
      $('#batch_status_filter').val('');
      if (batchPeriodePicker) batchPeriodePicker.clear();
      $('#batch_periode_tanggal').val('');
      if (batchPengajuanTable) batchPengajuanTable.ajax.reload();
    });

    /* =========================================================================
     * BATCH PENGAJUAN MODAL
     * ========================================================================= */
    let availableEligibleKavlings = [];
    let currentBatchKavlings = [];

    $('#btn-batch-pengajuan').on('click', function() {
      openBatchPengajuanModal();
    });

    function openBatchPengajuanModal() {
      if (!canKeuangan) {
        Swal.fire('Akses Ditolak', 'Hanya user Keuangan yang berhak membuat pengajuan pencairan.', 'error');
        return;
      }

      currentBatchKavlings = [];
      availableEligibleKavlings = [];
      $('#batch-hidden-inputs').empty();
      $('#batch-picker-items-list').empty();
      $('#batch-item-picker-wrap').hide();
      renderBatchKavlingDraftTable();

      Swal.fire({
        title: 'Memuat Data...',
        text: 'Menyiapkan daftar kavling yang eligible untuk diajukan...',
        allowOutsideClick: false,
        didOpen: function() {
          Swal.showLoading();
        }
      });

      $.ajax({
        url: base_url + 'keuangan/pencairan-akad/batch/eligible-kavling',
        type: 'POST',
        dataType: 'json',
        data: {
          [csrfName]: csrfHash,
          id_kavlings: [],
          id_proyek: activeProyekId()
        },
        success: function(res) {
          Swal.close();
          if (res.token) csrfHash = res.token;
          if (!res.success) {
            Swal.fire('Gagal', res.message || 'Tidak dapat memuat daftar kavling', 'error');
            return;
          }

          initBatchPengajuanModal(res);
        },
        error: function() {
          Swal.fire('Error', 'Terjadi kesalahan pada server saat memuat kavling', 'error');
        }
      });
    }

    function initBatchPengajuanModal(data) {
      data = (data && data.data) ? data.data : (data || {});
      availableEligibleKavlings = data.kavlings || [];

      $('#batch-input-nomor').val(data.nomor_pengajuan || data.nomor_batch || 'Otomatis');

      if (!$('#batch-input-tgl-pengajuan').val()) {
        const today = new Date().toISOString().split('T')[0];
        $('#batch-input-tgl-pengajuan').val(today);
      }
      $('#batch-input-tgl-rencana').val('');
      $('#batch-input-catatan').val('');
      $('#batch-input-lampiran').val('');
      $('#batch-lampiran-label').text('Pilih file...');

      let optHtml = '<option value="">-- Pilih Kavling untuk Diajukan --</option>';
      availableEligibleKavlings.forEach(function(kav) {
        optHtml += `<option value="${kav.id_plan}">${paChildEscape(kav.nama_jalan)} No. ${paChildEscape(kav.no_kavling)} - ${paChildEscape(kav.nama_konsumen)} (${paChildEscape(kav.bank)})</option>`;
      });
      $('#batch-select-kavling').html(optHtml);

      if ($('#batch-select-kavling').data('select2')) {
        $('#batch-select-kavling').select2('destroy');
      }
      $('#batch-select-kavling').select2({
        dropdownParent: $('#modal_batch_pengajuan'),
        width: '100%'
      });

      $('#batch-item-picker-wrap').hide();
      renderBatchKavlingDraftTable();
      $('#modal_batch_pengajuan').modal('show');
    }

    $('#batch-select-kavling').on('change', function() {
      const selectedPlanId = $(this).val();
      if (!selectedPlanId) {
        $('#batch-item-picker-wrap').hide();
        return;
      }

      const kav = availableEligibleKavlings.find(k => String(k.id_plan) === String(selectedPlanId));
      if (!kav) {
        $('#batch-item-picker-wrap').hide();
        return;
      }

      const alreadyAdded = currentBatchKavlings.find(k => String(k.id_plan) === String(selectedPlanId));
      const alreadyItemIds = alreadyAdded ? alreadyAdded.items.map(it => String(it.id)) : [];

      $('#batch-picker-kavling-label').text(kav.nama_jalan + ' No. ' + kav.no_kavling + ' (' + kav.nama_konsumen + ')');

      let itemsHtml = '';
      (kav.items || []).forEach(function(it) {
        const itemId = it.id || it.id_item;
        const isChecked = alreadyItemIds.length > 0 ? alreadyItemIds.includes(String(itemId)) : true;
        const isRetensi = it.jenis === 'retensi';
        const badgeType = isRetensi
          ? '<span class="badge badge-light-warning mr-50"><i class="fas fa-shield-alt mr-25"></i>Retensi</span>'
          : '<span class="badge badge-light-primary mr-50"><i class="fas fa-file-invoice-dollar mr-25"></i>Hasil Akad</span>';
        const catatanHtml = it.catatan
          ? `<div class="small text-muted font-italic mt-25"><i class="far fa-comment-dots mr-25"></i>Catatan: ${paChildEscape(it.catatan)}</div>`
          : '';

        itemsHtml += `
          <div class="custom-control custom-checkbox my-50 p-50 border rounded bg-white">
            <input type="checkbox" class="custom-control-input batch-picker-item-chk" 
              id="picker_item_${itemId}" 
              value="${itemId}" 
              data-label="${paChildEscape(it.label)}" 
              data-jenis="${it.jenis}"
              data-catatan="${paChildEscape(it.catatan || '')}"
              data-sisa="${it.sisa}"
              ${isChecked ? 'checked' : ''}>
            <label class="custom-control-label d-flex justify-content-between align-items-center w-100 pl-50" for="picker_item_${itemId}" style="cursor: pointer;">
              <div>
                <div>${badgeType} <strong>${paChildEscape(it.label)}</strong></div>
                ${catatanHtml}
              </div>
              <span class="text-right font-weight-bold ml-1 text-primary" style="font-size: 0.9rem;">Rp ${num_format(it.sisa)}</span>
            </label>
          </div>
        `;
      });

      $('#batch-picker-items-list').html(itemsHtml);
      $('#batch-item-picker-wrap').show();
    });

    $('#btn-batch-picker-check-all').on('click', function() {
      const anyUnchecked = $('.batch-picker-item-chk:not(:checked)').length > 0;
      $('.batch-picker-item-chk').prop('checked', anyUnchecked);
    });

    $('#btn-add-kavling-to-batch').on('click', function() {
      const selectedPlanId = $('#batch-select-kavling').val();
      if (!selectedPlanId) return;

      const kav = availableEligibleKavlings.find(k => String(k.id_plan) === String(selectedPlanId));
      if (!kav) return;

      const selectedItems = [];
      $('#batch-picker-items-list .batch-picker-item-chk:checked').each(function() {
        selectedItems.push({
          id: $(this).val(),
          label: $(this).data('label'),
          jenis: $(this).data('jenis'),
          catatan: $(this).data('catatan'),
          sisa: parseFloat($(this).data('sisa')) || 0
        });
      });

      if (selectedItems.length === 0) {
        Swal.fire('Perhatian', 'Pilih minimal satu item untuk kavling ini.', 'warning');
        return;
      }

      const existingIndex = currentBatchKavlings.findIndex(k => String(k.id_plan) === String(kav.id_plan));
      if (existingIndex >= 0) {
        currentBatchKavlings[existingIndex].items = selectedItems;
      } else {
        currentBatchKavlings.push({
          ...kav,
          items: selectedItems
        });
      }

      renderBatchKavlingDraftTable();

      // Reset dropdown kavling
      $('#batch-select-kavling').val('').trigger('change');
      $('#batch-item-picker-wrap').hide();
    });

    function renderBatchKavlingDraftTable() {
      let tbodyHtml = '';
      let hiddenHtml = '';
      let grandTotal = 0;

      if (currentBatchKavlings.length === 0) {
        tbodyHtml = '<tr><td colspan="5" class="text-center text-muted py-2">Belum ada kavling yang ditambahkan. Silakan pilih kavling di atas.</td></tr>';
      } else {
        currentBatchKavlings.forEach(function(kav, idx) {
          let kavSubtotal = 0;
          let itemsBadgeHtml = '';

          hiddenHtml += `<input type="hidden" name="kavlings[${idx}][id_plan]" value="${kav.id_plan}">`;

          kav.items.forEach(function(it) {
            kavSubtotal += parseFloat(it.sisa) || 0;
            const isRetensi = it.jenis === 'retensi';
            const badgeClass = isRetensi ? 'badge-light-warning' : 'badge-light-primary';
            const catatanHtml = it.catatan
              ? `<div class="small text-muted font-italic"><i class="far fa-comment-dots mr-25"></i>Catatan: ${paChildEscape(it.catatan)}</div>`
              : '';

            itemsBadgeHtml += `
              <div class="mb-50 p-25 border rounded bg-white w-100">
                <span class="badge ${badgeClass} mr-25" style="font-size: 0.8rem;">
                  ${paChildEscape(it.label)}: <strong class="text-primary">Rp ${num_format(it.sisa)}</strong>
                </span>
                ${catatanHtml}
              </div>
            `;
            hiddenHtml += `<input type="hidden" name="kavlings[${idx}][items][]" value="${it.id}">`;
          });

          grandTotal += kavSubtotal;

          tbodyHtml += `
            <tr data-kav-idx="${idx}">
              <td class="text-center align-middle">${idx + 1}</td>
              <td class="align-middle">
                <span class="badge badge-light-primary font-weight-bold" style="font-size: 0.85rem;">${paChildEscape(kav.no_kavling)}</span>
                <div class="small text-muted">${paChildEscape(kav.nama_jalan)}</div>
                <div class="font-weight-bold mt-25">${paChildEscape(kav.nama_konsumen)}</div>
                <div class="small text-muted"><i class="fas fa-university mr-25"></i> ${paChildEscape(kav.bank)}</div>
              </td>
              <td class="align-middle">
                <div class="d-flex flex-wrap" style="gap: 4px;">
                  ${itemsBadgeHtml}
                </div>
              </td>
              <td class="text-right align-middle font-weight-bold text-primary">
                Rp ${num_format(kavSubtotal)}
              </td>
              <td class="text-center align-middle">
                <button type="button" class="btn btn-outline-danger btn-sm p-25 btn-remove-batch-kav" data-id-plan="${kav.id_plan}" title="Hapus dari batch">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </td>
            </tr>
          `;
        });
      }

      $('#batch-kavling-tbody').html(tbodyHtml);
      $('#batch-hidden-inputs').html(hiddenHtml);
      $('#batch-modal-kavling-count').text(currentBatchKavlings.length);
      $('#batch-modal-grand-total').text('Rp ' + num_format(grandTotal));
      $('#batch-footer-total').text('Rp ' + num_format(grandTotal));

      if (currentBatchKavlings.length === 0 || grandTotal <= 0) {
        $('#btn-submit-batch-pengajuan').prop('disabled', true);
      } else {
        $('#btn-submit-batch-pengajuan').prop('disabled', false);
      }
    }

    $(document).on('click', '.btn-remove-batch-kav', function() {
      const planId = $(this).data('id-plan');
      currentBatchKavlings = currentBatchKavlings.filter(k => String(k.id_plan) !== String(planId));
      renderBatchKavlingDraftTable();
    });

    $('#batch-input-lampiran').on('change', function() {
      const fileName = $(this).val().split('\\').pop() || 'Pilih file...';
      $('#batch-lampiran-label').text(fileName);
    });

    $('#form-batch-pengajuan').on('submit', function(e) {
      e.preventDefault();

      if (currentBatchKavlings.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu kavling dan item untuk diajukan.', 'warning');
        return;
      }

      Swal.fire({
        title: 'Konfirmasi Pengajuan Batch',
        text: 'Simpan pengajuan pencairan untuk ' + currentBatchKavlings.length + ' kavling terpilih?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal'
      }).then(function(result) {
        if (!result.isConfirmed) return;

        const formData = new FormData($('#form-batch-pengajuan')[0]);
        formData.set(csrfName, csrfHash);

        $('#btn-submit-batch-pengajuan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-25"></i> Menyimpan...');

        $.ajax({
          url: base_url + 'keuangan/pencairan-akad/batch/pengajuan/store',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(res) {
            $('#btn-submit-batch-pengajuan').prop('disabled', false).html('<i class="fas fa-paper-plane mr-25"></i> Simpan Pengajuan Batch');
            if (res.token) csrfHash = res.token;

            if (res.success) {
              $('#modal_batch_pengajuan').modal('hide');
              Swal.fire('Berhasil', res.message || 'Pengajuan batch berhasil dibuat.', 'success');
              hasilAkadDetailCache = {};
              hasilAkadTable.draw();
              
              $('a[href="#tab-pane-pengajuan"]').tab('show');
              if (batchPengajuanTable) {
                batchPengajuanTable.ajax.reload();
              }
            } else {
              Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat menyimpan', 'error');
            }
          },
          error: function() {
            $('#btn-submit-batch-pengajuan').prop('disabled', false).html('<i class="fas fa-paper-plane mr-25"></i> Simpan Pengajuan Batch');
            Swal.fire('Error', 'Terjadi kesalahan pada server saat menyimpan pengajuan batch', 'error');
          }
        });
      });
    });

    /* =========================================================================
     * BATCH PENCAIRAN MODAL
     * ========================================================================= */
    window.openBatchPencairanModal = function(idBatch) {
      if (!canKeuangan) {
        Swal.fire('Akses Ditolak', 'Hanya user Keuangan yang berhak memproses pencairan dana.', 'error');
        return;
      }

      Swal.fire({
        title: 'Memuat Data...',
        allowOutsideClick: false,
        didOpen: function() {
          Swal.showLoading();
        }
      });

      $.ajax({
        url: base_url + 'keuangan/pencairan-akad/batch/detail',
        type: 'POST',
        dataType: 'json',
        data: {
          [csrfName]: csrfHash,
          id_batch: idBatch
        },
        success: function(res) {
          Swal.close();
          if (res.token) csrfHash = res.token;
          if (!res.success) {
            Swal.fire('Gagal', res.message || 'Data tidak ditemukan', 'error');
            return;
          }

          renderBatchPencairanModal(res);
        },
        error: function() {
          Swal.fire('Error', 'Terjadi kesalahan server saat memuat detail batch', 'error');
        }
      });
    };

    function renderBatchPencairanModal(data) {
      data = (data && data.data) ? data.data : (data || {});
      const batch = data.batch || {};
      const kavlings = data.kavlings || [];

      $('#cair-batch-id').val(batch.id);
      $('#cair-batch-mode').val('partial');
      $('#cair-batch-nomor').text(batch.nomor_batch || '-');
      $('#cair-batch-total-pengajuan').text('Rp ' + num_format(batch.total_pengajuan || 0));
      $('#cair-batch-total-cair').text('Rp ' + num_format(batch.total_cair || 0));
      $('#cair-batch-total-sisa').text('Rp ' + num_format(batch.sisa || 0));

      $('#cair-input-tanggal').val('');
      $('#cair-input-catatan').val('');
      $('#cair-check-all-box').prop('checked', false);

      renderCairKavlingTbody(kavlings);
      $('#modal_batch_pencairan').modal('show');
    }

    function renderCairKavlingTbody(kavlings) {
      let tbodyHtml = '';
      let rowNumber = 1;

      kavlings.forEach(function(kav) {
        if (kav.items && kav.items.length > 0) {
          kav.items.forEach(function(it) {
            const itemSisa = parseFloat(it.sisa) || 0;
            if (itemSisa <= 0.01) return;

            tbodyHtml += `
              <tr>
                <td class="text-center align-middle">
                  <input type="checkbox" class="cair-item-check" 
                    id="cair_chk_${it.id_pengajuan_detail}" 
                    data-id="${it.id_pengajuan_detail}" 
                    data-sisa="${itemSisa}" 
                    style="cursor: pointer;">
                </td>
                <td class="text-center align-middle">${rowNumber++}</td>
                <td class="align-middle">
                  <span class="badge badge-light-primary font-weight-bold">${paChildEscape(kav.no_kavling)}</span>
                  <div class="small text-muted">${paChildEscape(kav.nama_jalan)}</div>
                </td>
                <td class="align-middle">
                  <div class="font-weight-bold">${paChildEscape(kav.nama_konsumen)}</div>
                  <div class="small text-muted">${paChildEscape(kav.bank)}</div>
                </td>
                <td class="align-middle">
                  <span class="badge badge-light-info">${paChildEscape(it.label)}</span>
                </td>
                <td class="text-right align-middle text-danger font-weight-bold">
                  Rp ${num_format(itemSisa)}
                </td>
                <td class="text-right align-middle">
                  <input type="number" 
                    class="form-control form-control-sm text-right cair-partial-nominal" 
                    name="details[${it.id_pengajuan_detail}][nominal_cair]" 
                    value="${itemSisa}" 
                    min="0" 
                    max="${itemSisa}" 
                    data-max="${itemSisa}" 
                    step="1000"
                    disabled>
                </td>
              </tr>
            `;
          });
        }
      });

      if (!tbodyHtml) {
        tbodyHtml = '<tr><td colspan="7" class="text-center text-muted py-2">Semua item pengajuan telah cair penuh.</td></tr>';
      }

      $('#cair-kavling-tbody').html(tbodyHtml);
      updateCairCurrentTotal();
    }

    function updateCairCurrentTotal() {
      let total = 0;
      let totalChecked = 0;
      let totalCheckboxes = $('.cair-item-check').length;

      $('.cair-item-check').each(function() {
        const isChecked = $(this).is(':checked');
        const inputNominal = $(this).closest('tr').find('.cair-partial-nominal');

        if (isChecked) {
          totalChecked++;
          inputNominal.prop('disabled', false);
          total += parseFloat(inputNominal.val()) || 0;
        } else {
          inputNominal.prop('disabled', true);
        }
      });

      $('#cair-current-total').text('Rp ' + num_format(total));
      $('#cair-check-all-box').prop('checked', totalCheckboxes > 0 && totalChecked === totalCheckboxes);
    }

    $(document).on('change', '.cair-item-check', function() {
      const isChecked = $(this).is(':checked');
      const inputNominal = $(this).closest('tr').find('.cair-partial-nominal');
      if (isChecked && (!inputNominal.val() || parseFloat(inputNominal.val()) <= 0)) {
        inputNominal.val(inputNominal.data('max'));
      }
      updateCairCurrentTotal();
    });

    $('#cair-check-all-box').on('change', function() {
      const isChecked = $(this).is(':checked');
      $('.cair-item-check').each(function() {
        $(this).prop('checked', isChecked);
        const inputNominal = $(this).closest('tr').find('.cair-partial-nominal');
        if (isChecked) {
          inputNominal.prop('disabled', false);
          if (!inputNominal.val() || parseFloat(inputNominal.val()) <= 0) {
            inputNominal.val(inputNominal.data('max'));
          }
        } else {
          inputNominal.prop('disabled', true);
        }
      });
      updateCairCurrentTotal();
    });

    $('#btn-check-all-cair').on('click', function() {
      $('#cair-check-all-box').prop('checked', true).trigger('change');
    });

    $('#btn-uncheck-all-cair').on('click', function() {
      $('#cair-check-all-box').prop('checked', false).trigger('change');
    });

    $(document).on('input change', '.cair-partial-nominal', function() {
      const max = parseFloat($(this).data('max')) || 0;
      let val = parseFloat($(this).val()) || 0;
      if (val > max) {
        val = max;
        $(this).val(val);
      }
      if (val < 0) {
        val = 0;
        $(this).val(val);
      }
      updateCairCurrentTotal();
    });

    $('#form-batch-pencairan').on('submit', function(e) {
      e.preventDefault();

      const tgl = $('#cair-input-tanggal').val();
      if (!tgl) {
        Swal.fire('Peringatan', 'Tanggal pencairan wajib diisi.', 'warning');
        return;
      }

      const checkedCount = $('.cair-item-check:checked').length;
      let totalCair = 0;
      $('.cair-item-check:checked').each(function() {
        const row = $(this).closest('tr');
        totalCair += parseFloat(row.find('.cair-partial-nominal').val()) || 0;
      });

      if (checkedCount === 0 || totalCair <= 0) {
        Swal.fire('Peringatan', 'Silakan pilih minimal satu item untuk dicairkan.', 'warning');
        return;
      }

      Swal.fire({
        title: 'Konfirmasi Pencairan Dana',
        text: 'Simpan pencairan sebesar ' + $('#cair-current-total').text() + ' untuk ' + checkedCount + ' item terpilih?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan Pencairan',
        cancelButtonText: 'Batal'
      }).then(function(result) {
        if (!result.isConfirmed) return;

        const postData = $('#form-batch-pencairan').serialize() + '&' + csrfName + '=' + encodeURIComponent(csrfHash);

        $('#btn-submit-cair-batch').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-25"></i> Menyimpan...');

        $.ajax({
          url: base_url + 'keuangan/pencairan-akad/batch/pencairan/store',
          type: 'POST',
          data: postData,
          dataType: 'json',
          success: function(res) {
            $('#btn-submit-cair-batch').prop('disabled', false).html('<i class="fas fa-save mr-25"></i> Simpan Pencairan');
            if (res.token) csrfHash = res.token;

            if (res.success) {
              $('#modal_batch_pencairan').modal('hide');
              Swal.fire('Berhasil', res.message || 'Pencairan batch berhasil disimpan.', 'success');
              if (batchPengajuanTable) batchPengajuanTable.ajax.reload();
              hasilAkadDetailCache = {};
              hasilAkadTable.draw();
            } else {
              Swal.fire('Gagal', res.message || 'Gagal menyimpan pencairan', 'error');
            }
          },
          error: function() {
            $('#btn-submit-cair-batch').prop('disabled', false).html('<i class="fas fa-save mr-25"></i> Simpan Pencairan');
            Swal.fire('Error', 'Terjadi kesalahan server saat menyimpan pencairan', 'error');
          }
        });
      });
    });

    /* =========================================================================
     * BATCH DETAIL, HISTORY & VOID
     * ========================================================================= */
    window.openBatchDetailModal = function(idBatch, targetTab) {
      Swal.fire({
        title: 'Memuat Detail...',
        allowOutsideClick: false,
        didOpen: function() { Swal.showLoading(); }
      });

      const pDetail = $.ajax({
        url: base_url + 'keuangan/pencairan-akad/batch/detail',
        type: 'POST',
        dataType: 'json',
        data: {
          [csrfName]: csrfHash,
          id_batch: idBatch
        }
      });

      const pHistory = $.ajax({
        url: base_url + 'keuangan/pencairan-akad/batch/history/' + idBatch,
        type: 'GET',
        dataType: 'json'
      });

      Promise.all([pDetail, pHistory]).then(function(results) {
        Swal.close();
        const resDetail = results[0];
        const resHistory = results[1];

        if (resDetail && resDetail.token) csrfHash = resDetail.token;
        if (!resDetail || !resDetail.success) {
          Swal.fire('Gagal', (resDetail && resDetail.message) || 'Data tidak ditemukan', 'error');
          return;
        }

        const data = (resDetail && resDetail.data) ? resDetail.data : (resDetail || {});
        const b = data.batch || {};
        const kavs = data.kavlings || [];

        // Header
        $('#detail-batch-header-nomor').text(b.nomor_batch || ('#' + idBatch));
        $('#detail-batch-header-status').html(paChildStatusBadge(b.status));

        // Tab 1: Rincian
        $('#detail-batch-nomor').text(b.nomor_batch || '-');
        $('#detail-batch-tgl').text(b.tanggal_pengajuan ? format_date(b.tanggal_pengajuan) : '-');
        $('#detail-batch-tgl-rencana').text(b.tanggal_rencana_cair ? format_date(b.tanggal_rencana_cair) : '-');
        $('#detail-batch-pic').text(b.pic_nama || '-');
        $('#detail-batch-total').text('Rp ' + num_format(b.total_pengajuan || 0));
        $('#detail-batch-cair').text('Rp ' + num_format(b.total_cair || 0));
        $('#detail-batch-sisa').text('Rp ' + num_format(b.sisa || 0));

        if (b.lampiran_url) {
          $('#detail-batch-lampiran').html('<a href="' + b.lampiran_url + '" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-paperclip mr-25"></i> Lihat Surat</a>');
        } else {
          $('#detail-batch-lampiran').text('-');
        }

        if (b.catatan) {
          $('#detail-batch-catatan').text(b.catatan);
          $('#detail-batch-catatan-box').show();
        } else {
          $('#detail-batch-catatan-box').hide();
        }

        let kavRows = '';
        kavs.forEach(function(k, idx) {
          kavRows += `
            <tr>
              <td class="text-center">${idx + 1}</td>
              <td>
                <span class="badge badge-light-primary font-weight-bold">${paChildEscape(k.no_kavling)}</span>
                <div class="small text-muted">${paChildEscape(k.nama_jalan)}</div>
              </td>
              <td>
                <div class="font-weight-bold">${paChildEscape(k.nama_konsumen)}</div>
                <div class="small text-muted">${paChildEscape(k.bank)}</div>
              </td>
              <td class="text-right">Rp ${num_format(k.total_pengajuan)}</td>
              <td class="text-right text-success font-weight-bold">Rp ${num_format(k.total_cair)}</td>
              <td class="text-right text-danger font-weight-bold">Rp ${num_format(k.sisa)}</td>
              <td class="text-center">${paChildStatusBadge(k.status)}</td>
            </tr>
          `;
        });
        $('#detail-batch-tbody').html(kavRows || '<tr><td colspan="7" class="text-center text-muted">Tidak ada data</td></tr>');

        // Tab 2: Riwayat
        const logs = (resHistory && resHistory.data) ? resHistory.data : [];
        if (logs.length === 0) {
          $('#detail-batch-history-timeline').html('<div class="text-center text-muted py-2">Belum ada catatan riwayat untuk batch ini.</div>');
        } else {
          let timelineHtml = '';
          logs.forEach(function(row) {
            const actionIcons = {
              'pengajuan_batch': ['bg-primary', 'fas fa-file-invoice'],
              'pencairan_batch': ['bg-success', 'fas fa-hand-holding-usd'],
              'void_batch': ['bg-danger', 'fas fa-ban'],
            };
            const iconConf = actionIcons[row.action] || ['bg-secondary', 'fas fa-info-circle'];

            timelineHtml += `
              <div class="pa-history-item d-flex mb-1 pb-1 border-bottom">
                <div class="pa-history-icon ${iconConf[0]} text-white rounded-circle d-flex align-items-center justify-content-center mr-1" style="width: 36px; height: 36px; min-width: 36px;">
                  <i class="${iconConf[1]}" style="font-size: 0.9rem;"></i>
                </div>
                <div class="pa-history-content flex-grow-1">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark">${paChildEscape(row.description || row.action)}</span>
                    <span class="badge badge-light-secondary font-weight-normal text-muted">${row.created_at || '-'}</span>
                  </div>
                  <div class="small text-muted mt-25">
                    <i class="far fa-user mr-25"></i> Oleh: <strong>${paChildEscape(row.username || row.add_by_username || '-')}</strong>
                  </div>
                </div>
              </div>
            `;
          });
          $('#detail-batch-history-timeline').html(timelineHtml);
        }

        // Footer buttons
        const totalCair = parseFloat(b.total_cair) || 0;
        const sisa = parseFloat(b.sisa) || 0;
        const canVoid = canKeuangan && totalCair <= 0 && b.status !== 'void';
        const canCair = canKeuangan && ['active', 'partial'].includes(b.status) && sisa > 0;

        if (canVoid) {
          $('#btn-detail-void-batch').show().data('id', b.id);
        } else {
          $('#btn-detail-void-batch').hide();
        }

        if (canCair) {
          $('#btn-detail-cair-batch').show().data('id', b.id);
        } else {
          $('#btn-detail-cair-batch').hide();
        }

        // Select tab
        if (targetTab === 'history') {
          $('#tab-batch-detail-history').tab('show');
        } else {
          $('#tab-batch-detail-rincian').tab('show');
        }

        $('#modal_batch_detail').modal('show');
      }).catch(function() {
        Swal.fire('Error', 'Terjadi kesalahan saat memuat detail batch', 'error');
      });
    };

    window.openBatchHistoryModal = function(idBatch) {
      openBatchDetailModal(idBatch, 'history');
    };

    $('#btn-detail-cair-batch').on('click', function() {
      const idBatch = $(this).data('id');
      $('#modal_batch_detail').modal('hide');
      if (idBatch) {
        openBatchPencairanModal(idBatch);
      }
    });

    $('#btn-detail-void-batch').on('click', function() {
      const idBatch = $(this).data('id');
      if (idBatch) {
        voidBatchPengajuan(idBatch);
      }
    });

    window.voidBatchPengajuan = function(idBatch) {
      if (!canKeuangan) {
        Swal.fire('Akses Ditolak', 'Hanya user Keuangan yang berhak membatalkan pengajuan pencairan.', 'error');
        return;
      }

      Swal.fire({
        title: 'Batalkan Pengajuan Batch?',
        text: 'Pengajuan yang dibatalkan tidak dapat dikembalikan. Masukkan alasan pembatalan:',
        input: 'textarea',
        inputPlaceholder: 'Tuliskan alasan pembatalan batch di sini...',
        inputAttributes: {
          'aria-label': 'Alasan pembatalan'
        },
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan Batch',
        confirmButtonColor: '#ea5455',
        cancelButtonText: 'Batal',
        inputValidator: function(value) {
          if (!value || !value.trim()) {
            return 'Alasan pembatalan wajib diisi!';
          }
        }
      }).then(function(result) {
        if (!result.isConfirmed) return;

        const reason = result.value;
        Swal.fire({
          title: 'Membatalkan...',
          allowOutsideClick: false,
          didOpen: function() { Swal.showLoading(); }
        });

        $.ajax({
          url: base_url + 'keuangan/pencairan-akad/batch/void',
          type: 'POST',
          dataType: 'json',
          data: {
            [csrfName]: csrfHash,
            id_batch: idBatch,
            reason: reason
          },
          success: function(res) {
            if (res.token) csrfHash = res.token;
            if (res.success) {
              $('#modal_batch_detail').modal('hide');
              Swal.fire('Dibatalkan', res.message || 'Pengajuan batch berhasil dibatalkan.', 'success');
              if (batchPengajuanTable) batchPengajuanTable.ajax.reload();
              hasilAkadDetailCache = {};
              hasilAkadTable.draw();
            } else {
              Swal.fire('Gagal', res.message || 'Gagal membatalkan batch', 'error');
            }
          },
          error: function() {
            Swal.fire('Error', 'Terjadi kesalahan saat membatalkan batch', 'error');
          }
        });
      });
    };

    $(".select2-selection__arrow").css("pointer-events", "none")

  });
</script>