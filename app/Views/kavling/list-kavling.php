<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/fixed-columns/fixedColumns.bootstrap4.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/richtext.min.css">
<style>
  #poskon-filter {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: none;
  }

  #poskon-filter .poskon-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem .65rem;
    padding: .6rem .85rem;
    border-bottom: 1px solid #edf0f4;
    background: #fff;
  }

  #poskon-filter .poskon-toolbar-title h2 {
    margin: 0;
    color: #111827;
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.3;
    white-space: nowrap;
  }

  #modal-filter-lanjutan .poskon-filter-grid {
    display: flex;
    flex-direction: column;
    gap: .85rem;
  }

  #modal-filter-lanjutan .poskon-filter-field {
    width: 100%;
    margin-bottom: 0 !important;
  }

  #modal-filter-lanjutan .poskon-filter-field label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: .3rem;
  }

  #poskon-filter .form-control,
  #modal-filter-lanjutan .form-control,
  #poskon-filter .select2-container--default .select2-selection--single,
  #modal-filter-lanjutan .select2-container--default .select2-selection--single {
    min-height: 30px;
    border-color: #d8dee8;
    border-radius: 6px;
  }

  #poskon-filter .select2-container--default .select2-selection--single .select2-selection__rendered,
  #modal-filter-lanjutan .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
    font-size: .78rem;
  }

  #poskon-filter .select2-container--default .select2-selection--single .select2-selection__arrow,
  #modal-filter-lanjutan .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 28px;
  }

  .poskon-tabs-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .poskon-tabs-bar .poskon-tabs {
    margin-bottom: 0;
    flex: 1 1 auto;
    min-width: 0;
  }

  .poskon-filter-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    align-items: center;
    justify-content: flex-end;
    margin-left: auto;
  }

  .poskon-filter-actions .btn,
  .poskon-filter-actions .btn-group .btn {
    min-height: 30px;
    padding: .3rem .6rem;
    border-radius: 6px;
    font-size: .78rem;
    font-weight: 600;
    line-height: 1.2;
  }

  .poskon-filter-actions .btn i {
    margin-right: .25rem;
  }

  #poskon-filter .btn-primary,
  #modal-filter-lanjutan .btn-primary {
    border-color: #2057a3;
    background-color: #2057a3;
  }

  #poskon-filter .btn-primary:hover,
  #poskon-filter .btn-primary:focus,
  #modal-filter-lanjutan .btn-primary:hover,
  #modal-filter-lanjutan .btn-primary:focus {
    border-color: #184783;
    background-color: #184783;
  }

  #poskon-filter .btn-outline-primary {
    border-color: #2057a3;
    color: #2057a3;
  }

  #poskon-filter .btn-outline-primary:hover,
  #poskon-filter .btn-outline-primary:focus {
    border-color: #2057a3;
    background-color: #2057a3;
    color: #fff;
  }

  .poskon-active-filters {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem;
    padding: .6rem .85rem;
    margin-bottom: 1rem;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
  }

  .poskon-active-filters-label {
    font-size: .78rem;
    font-weight: 700;
    color: #4b5563;
    white-space: nowrap;
  }

  .poskon-active-filters-chips {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
  }

  .poskon-filter-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .3rem .6rem;
    border-radius: 999px;
    background: #eaf1fb;
    color: #2057a3;
    font-size: .75rem;
    font-weight: 600;
  }

  .poskon-filter-chip i {
    cursor: pointer;
    font-size: .7rem;
  }

  .poskon-active-filters-clear {
    margin-left: auto;
    font-size: .78rem;
    font-weight: 600;
    color: #2057a3;
    white-space: nowrap;
    cursor: pointer;
  }

  .poskon-table-card .card-body {
    padding: 1rem;
  }

  .poskon-datatable-card .card-body {
    padding: .5rem;
  }

  #data_tables {
    width: 100% !important;
  }

  #data_tables tbody td {
    text-transform: uppercase;
  }

  #data_tables tbody td .poskon-action-cell,
  #data_tables tbody td .poskon-action-cell * {
    text-transform: none;
  }

  #poskon-filter .select2-container,
  #modal-tambah-poskon .select2-container,
  #modal-filter-lanjutan .select2-container {
    width: 100% !important;
  }

  .select2-dropdown {
    z-index: 1060;
  }

  @media (max-width: 767.98px) {
    .app-content.content {
      padding-left: .75rem;
      padding-right: .75rem;
    }

    #poskon-filter .poskon-toolbar {
      align-items: flex-start;
      padding: .85rem;
    }

    #poskon-filter .poskon-toolbar-title {
      width: 100%;
    }

    #poskon-filter .poskon-toolbar-title h2 {
      font-size: 1rem;
    }

    .poskon-filter-actions {
      display: none;
    }

    .poskon-tabs {
      flex-wrap: nowrap;
      overflow-x: auto;
      overflow-y: hidden;
      -webkit-overflow-scrolling: touch;
    }

    .poskon-tabs .nav-link {
      white-space: nowrap;
    }

    .poskon-active-filters-clear {
      margin-left: 0;
      width: 100%;
      text-align: right;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
      width: 100%;
      text-align: left;
      margin-bottom: .75rem;
    }

    .dataTables_wrapper .dataTables_filter input {
      width: 100%;
      margin-left: 0;
      margin-top: .35rem;
    }

    .dataTables_scrollBody {
      max-height: 62vh !important;
    }

    #data_tables,
    #riwayat_export {
      font-size: .82rem;
    }
  }
</style>
<style>
  <?= view('siteplan/partials/modal_detail_styles') ?>

  @media (max-width: 1199.98px) {
    #modal_detail .detail-kavling-layout {
      flex-wrap: wrap;
    }

    #modal_detail .detail-kavling-sidebar,
    #modal_detail .detail-kavling-content {
      flex: 0 0 100%;
      max-width: 100%;
    }

    #modal_detail .detail-kavling-sidebar {
      position: static;
    }
  }

  @media (max-width: 767.98px) {
    #modal_detail .modal-dialog {
      max-width: calc(100vw - 12px);
      margin: .5rem auto;
    }

    #modal_detail .modal-body {
      max-height: calc(100vh - 5.5rem);
      padding: .75rem;
    }

    #modal_detail .nav-pills {
      flex-direction: row !important;
      flex-wrap: nowrap;
      overflow-x: auto;
      padding-bottom: .25rem;
    }

    #modal_detail .detail-dashboard-grid,
    #modal_detail .detail-card-grid,
    #modal_detail .detail-production-dashboard {
      grid-template-columns: 1fr;
    }

    #modal_detail .card-body {
      padding: .85rem;
    }
  }

  .poskon-action-cell .dropdown-menu {
    max-height: 280px;
    overflow-y: auto;
  }
</style>
<!--
<link rel="stylesheet" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/sticky-header/bootstrap-table-sticky-header.min.css">
<link rel="stylesheet" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/fixed-columns/bootstrap-table-fixed-columns.min.css"> -->

<script>
  // var csrfName = '<?= csrf_token() ?>';
  // var csrfHash = '<?= csrf_hash() ?>';
  const state = {
    status: {
      tab: {
        isClosed: false
      }
    }
  };

  let dt_proyek = [];

  document.addEventListener("DOMContentLoaded", function() {
    var fp = flatpickr(".flatpickr-human-friendly", {
      altInput: true,
      altFormat: 'F j, Y',
      dateFormat: 'Y-m-d'
    })
  });
  const not_found = "images/not_found.png"
</script>


<!-- /.card-header -->
<div class="app-content content ">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card hidden" id="poskon-filter">
          <div class="poskon-toolbar">
            <div class="poskon-toolbar-title">
              <h2>Posisi Konsumen Aktif</h2>
            </div>
          </div>
        </div>
        <div class="card poskon-table-card">
          <div class="card-body pb-0 pt-0">
            <div class="poskon-tabs-bar">
              <ul class="nav nav-tabs poskon-tabs mb-1 mt-1" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="list_poskon-tab"
                    data-toggle="tab" href="#list_poskon"
                    aria-controls="list_poskon" role="tab" aria-selected="true">List Posisi Konsumen Aktif</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="riwayat_eksport-tab" data-toggle="tab"
                    href="#riwayat_eksport" aria-controls="riwayat_eksport" role="tab"
                    aria-selected="true">Riwayat Eksport</a>
                </li>
              </ul>
              <div class="poskon-filter-actions">
                <button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect btn-sm" title="Filter Data"><i class="fa fa-filter"></i> Filter</button>
                <button type="button" id="btn_open_add_modal" class="btn btn-primary waves-effect btn-sm" title="Tambah Data"><i class="fa fa-plus"></i> Tambah</button>
                <div class="btn-group">
                  <button type="button" id="btn_export_toggle" class="btn btn-outline-primary waves-effect btn-sm dropdown-toggle" data-toggle="dropdown" title="Export Data"><i class="fa fa-file-export"></i> Export</button>
                  <div class="dropdown-menu dropdown-menu-right">
                    <button type="button" id="btn_export_excel" class="dropdown-item"><i class="fa fa-file-excel text-success mr-50"></i> Export Excel</button>
                    <button type="button" id="btn_export_pdf" class="dropdown-item"><i class="fa fa-file-pdf text-danger mr-50"></i> Export PDF</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="poskon-active-filters" id="poskon-active-filters" hidden>
          <span class="poskon-active-filters-label">Filter Aktif:</span>
          <div class="poskon-active-filters-chips" id="poskon-active-filters-chips"></div>
          <a href="javascript:void(0)" id="btn_filter_clear_all" class="poskon-active-filters-clear">Bersihkan</a>
        </div>
        <div class="card poskon-datatable-card">
          <div class="card-body">
            <div class="tab-content">
              <div class="tab-pane show active" id="list_poskon"
                aria-labelledby="list_poskon-tab" role="tabpanel">
                <table id="data_tables" class="datatables-basic table compact table-hover table-bordered">
                  <thead>
                    <tr>
                      <th rowspan="3" id="tb-action">AKSI</th>
                      <th rowspan="3" id="tb-NO">NO</th>
                      <th colspan="2" id="tb-KAVLING">KAVLING</th>
                      <th rowspan="3" id="tb-TYPE">TYPE</th>
                      <th rowspan="3" id="tb-NAMA_KONSUMEN">NAMA KONSUMEN</th>
                      <th rowspan="3" id="tb-SALES">SALES</th>
                      <th rowspan="3" id="tb-TGL_BOOKING">TGL BOOKING</th>
                      <th rowspan="3" id="tb-TGL_WAWANCARA">TGL WAWANCARA</th>
                      <th rowspan="3" id="tb-TGL_AKAD">TGL AKAD</th>
                      <th colspan="6" id="tb-MARKETING_DATA">MARKETING DATA</th>
                      <th colspan="4" id="tb-KEUANGAN">KEUANGAN</th>
                      <th colspan="4" id="tb-PRODUKSI">PRODUKSI</th>
                      <th colspan="3" id="tb-LEGAL">LEGAL</th>
                      <th id="tb-GA">GA</th>
                      <th rowspan="3" id="tb-KETERANGAN_STATUS">KETERANGAN STATUS</th>
                    </tr>

                    <tr>
                      <th rowspan="2" id="tb-BLOK">BLOK</th>
                      <th rowspan="2" id="tb-NO_KAVLING">NO</th>

                      <th colspan="2" id="tb-PENGAJUAN">PENGAJUAN</th>
                      <th rowspan="2" id="tb-STATUS">STATUS</th>
                      <th colspan="2" id="tb-SP3K">SP3K</th>
                      <th rowspan="2" id="tb-SIKASEP">SIKASEP</th>

                      <th rowspan="2" id="tb-TUNAI">TUNAI</th>
                      <th rowspan="2" id="tb-UM">UM</th>
                      <th rowspan="2" id="tb-B_ADM">B. ADM</th>
                      <th rowspan="2" id="tb-BIAYA_BIAYA">BIAYA-BIAYA</th>

                      <th colspan="2" id="tb-BANGUNAN">BANGUNAN</th>
                      <th rowspan="2" id="tb-LISTRIK">LISTRIK</th>
                      <th rowspan="2" id="tb-JALAN">JALAN</th>

                      <th rowspan="2" id="tb-HGB">HGB</th>
                      <th rowspan="2" id="tb-IMB">IMB</th>
                      <th rowspan="2" id="tb-PBB">PBB</th>

                      <th rowspan="2" id="tb-SIKUMBANG">SIKUMBANG</th>
                    </tr>

                    <tr>
                      <th id="tb-TUNAI_KPR">TUNAI/KPR</th>
                      <th id="tb-BANK">BANK</th>
                      <th id="tb-TERBIT">TERBIT</th>
                      <th id="tb-EXPIRED">EXPIRED</th>

                      <th id="tb-%">%</th>
                      <th id="tb-LPA">LPA</th>
                    </tr>
                  </thead>
                </table>
              </div>
              <div class="tab-pane show" id="riwayat_eksport"
                aria-labelledby="riwayat_eksport-tab" role="tabpanel">
                <table id="riwayat_export" class=" table compact table-hover table-bordered">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Proyek</th>
                      <th>Tanggal Eksport</th>
                      <th>Dieksport Oleh</th>
                      <th>Tipe</th>
                      <th>File</th>
                    </tr>
                  </thead>
                  <tbody id="riwayat-here">
                  </tbody>
                </table>
              </div>
            </div>


          </div>
        </div>
      </div>
    </div>

  </section>
</div>

<div class="modal modal-slide-in fade" id="modal-filter-lanjutan">
  <div class="modal-dialog sidebar-sm">
    <div class="add-new-record modal-content pt-0">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
      <div class="modal-header mb-1">
        <h5 class="modal-title">Filter Lanjutan</h5>
      </div>
      <div class="modal-body flex-grow-1">
        <div class="poskon-filter-grid">
          <div class="poskon-filter-field">
            <label>Cluster</label>
            <select disabled id="id_cluster" name="id_cluster" class="select2  form-control"></select>
          </div>
          <div class="poskon-filter-field">
            <label>Blok / Jalan</label>
            <select disabled id="id_jalan" name="id_jalan" class="select2 form-control"></select>
          </div>
          <div class="poskon-filter-field" hidden>
            <select id="poskon_filter_wawancara" name="wawancara" class="select2 self form-control">
              <option value=""> Tanpa Filter </option>
              <option value="1"> Sudah </option>
              <option value="0"> Belum </option>
            </select>
          </div>
          <div class="poskon-filter-field" hidden>
            <select id="poskon_filter_sp3k" name="sp3k" class="select2 self form-control">
              <option value=""> Tanpa Filter </option>
              <option value="1"> Sudah </option>
              <option value="0"> Belum </option>
            </select>
          </div>

          <div class="poskon-filter-field">
            <label>Status</label>
            <select id="filter_status_kavling" class="select2 self form-control">
              <option value="booking" selected>Belum Akad (Booking)</option>
              <option value="akad">Sudah Akad</option>
              <option value="indent">Akad Indent</option>
            </select>
          </div>

          <div class="poskon-filter-field">
            <label>Tanggal Acuan Bulan/Tahun</label>
            <select id="filter_bulan_field" class="select2 self form-control">
              <option value="booking" selected>Tgl. Booking</option>
              <option value="akad">Tgl. Akad</option>
            </select>
          </div>
          <div class="poskon-filter-field">
            <label>Bulan</label>
            <select id="filter_bulan" class="select2 self form-control">
              <option value="">Semua Bulan</option>
              <option value="1">Januari</option>
              <option value="2">Februari</option>
              <option value="3">Maret</option>
              <option value="4">April</option>
              <option value="5">Mei</option>
              <option value="6">Juni</option>
              <option value="7">Juli</option>
              <option value="8">Agustus</option>
              <option value="9">September</option>
              <option value="10">Oktober</option>
              <option value="11">November</option>
              <option value="12">Desember</option>
            </select>
          </div>
          <div class="poskon-filter-field">
            <label>Tahun</label>
            <select id="filter_tahun" class="select2 self form-control">
              <option value="">Semua Tahun</option>
              <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 5; $y--): ?>
                <option value="<?= $y ?>"><?= $y ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btn_filter_reset" class="btn btn-outline-secondary">Reset</button>
        <button type="button" id="btn_filter_apply" class="btn btn-primary">Terapkan</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal-tambah-poskon" tabindex="-1" role="dialog" aria-labelledby="modalTambahPoskonLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahPoskonLabel">Tambah Data Kavling</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 mb-1">
            <label>Kavling</label>
            <select id="add_id_kavling" class="select2 form-control"></select>
          </div>
          <div class="col-12" id="add_menu_wrapper" hidden>
            <label>Pilih Aksi</label>
            <div id="add_menu_items" class="list-group"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

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

<script src="<?= base_url() ?>assets/js/jquery.richtext.min.js"></script>

<?php
$k = null;
$v = null;
$roles = user()->getRoles();
if (!empty($roles)) {
  foreach ($roles as $key => $val) {
    $k = $key;
    $v = $val;
    break; // Just need the first one if multiple
  }
}
?>
<script>
  var roleid = "<?= $k; ?>";
  var rolename = "<?= $v; ?>";
  const has_akses = JSON.parse('<?= json_encode($data['data']['has_akses'] ?? ['proyek' => false, 'legal' => false]) ?>');
  const pph = JSON.parse('<?= json_encode($data['data']['pph'] ?? []) ?>');
  const ppn = JSON.parse('<?= json_encode($data['data']['ppn'] ?? []) ?>');
  const li_keu = JSON.parse('<?= $data['data']['li_keu'] ?? '[]' ?>');
  var conf = JSON.parse('<?= $data['data']['conf'] ?? '{}' ?>');
  const proyekContext = <?= json_encode($data['data']['proyek'] ?? null) ?>;
  window.siteplanMenuItems = [];

  window.editdtt = [];

  function getKavlingIdFromShape(sh, fallbackId) {
    if (!sh) return fallbackId || '';
    return (sh.data && sh.data.id_kavling) || fallbackId || String(sh.id || '').replace(/^kav/, '');
  }

  function buildKavlingShape(row) {
    const idKavling = row.id_kavling || row.id || '';

    return {
      id: "kav" + idKavling,
      data: {
        tipe: "kavling",
        id_kavling: idKavling,
        id_mkdt: row.id_mkdt || null,
        id_keuangan: row.id_keuangan || null,
        id_legal: row.id_legal || null,
        id_produksi: row.id_produksi || null,
        nama_jalan: row.nama_jalan || '',
        no_kavling: row.no_kavling || ''
      },
      data2: {
        harga_akhir: row.harga_akhir || row.id_hargajual || "-",
        id_hargajual: row.id_hargajual || row.harga_akhir || "-",
        id_komplain: row.id_komplain || null,
        no_tipe_rumah: row.no_tipe_rumah || '',
        tipe_rumah: row.tipe_rumah || '',
        harga_akhir_tgl: row.harga_akhir_tgl || '',
        harga_akhir_oleh: row.harga_akhir_oleh || row.uadd_by || ''
      }
    };
  }

  function syncProjectContextFromRow(row) {
    dt_proyek = $.extend({}, proyekContext || {}, dt_proyek || {}, {
      id_proyek: activeProyekId() || (proyekContext && proyekContext.id_proyek) || '',
      nama_proyek: window.SIGAPP.activeProyekName || (proyekContext && proyekContext.nama_proyek) || '',
      id_perumahan_sikumbang: (proyekContext && proyekContext.id_perumahan_sikumbang) || (dt_proyek && dt_proyek.id_perumahan_sikumbang) || ''
    });
    if (row && row.nama_proyek) {
      dt_proyek.nama_proyek = row.nama_proyek;
    }
    return dt_proyek;
  }

  function encodePoskonRow(row) {
    return encodeURIComponent(JSON.stringify(row || {}));
  }

  function decodePoskonRow(encoded) {
    if (!encoded) return {};
    try {
      return JSON.parse(decodeURIComponent(encoded));
    } catch (error) {
      return {};
    }
  }

  function loadSiteplanMenuItems() {
    return $.ajax({
      url: base_url + 'home/getMenuItemsJson',
      type: 'post',
      data: {
        [csrfName]: csrfHash
      },
      dataType: 'json'
    }).done(function(response) {
      csrfHash = response.token;
      window.siteplanMenuItems = response.items || [];
    }).fail(function() {
      window.siteplanMenuItems = [];
    });
  }

  function buildSiteplanMenuItemsHtml(row, itemClass) {
    const rowEncoded = encodePoskonRow(row);
    let menuHtml = '';
    let lastGroup = null;

    (window.siteplanMenuItems || []).forEach(function(item) {
      if (parseInt(roleid, 10) === 1 && item.group_label && item.group_label !== lastGroup) {
        lastGroup = item.group_label;
        menuHtml += '<div class="dropdown-header">' + $('<div>').text(item.group_label).html() + '</div>';
      }

      const icon = item.icon ? '<i class="' + item.icon + '"></i> ' : '';
      menuHtml += '<button type="button" class="' + itemClass + ' poskon-menu-action" data-onclick="' +
        encodeURIComponent(item.onclick || '') + '" data-row="' + rowEncoded + '" data-group="' + (item.id_group || '') + '">' +
        icon + $('<div>').text(item.label || '').html() + '</button>';
    });

    return menuHtml;
  }

  function renderPoskonActionCell(row) {
    const idKavling = row.id_kavling;
    if (!idKavling) return '';

    const rowEncoded = encodePoskonRow(row);
    const menuHtml = buildSiteplanMenuItemsHtml(row, 'dropdown-item');

    const dropdown = menuHtml ?
      '<div class="btn-group ml-50">' +
      '<button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</button>' +
      '<div class="dropdown-menu dropdown-menu-right">' + menuHtml + '</div>' +
      '</div>' :
      '';

    return '<div class="btn-group poskon-action-cell" style="white-space:nowrap">' +
      '<button type="button" class="btn btn-info btn-sm poskon-detail-btn" data-row="' + rowEncoded + '" title="Lihat Detail">' +
      '<i class="fa fa-eye"></i></button>' + dropdown + '</div>';
  }

  function runSiteplanMenuAction(onclick, row, menuItem) {
    if (!onclick) return;

    if ($('#modal-tambah-poskon').hasClass('show')) {
      $('#modal-tambah-poskon').modal('hide');
    }

    syncProjectContextFromRow(row);
    const sh = buildKavlingShape(row);
    window.editdtt = [sh];
    $('.id_kavling').val(row.id_kavling || '');

    if (String(onclick).trim() === 'isi_data()') {
      const itemGroup = menuItem && menuItem.id_group ? parseInt(menuItem.id_group, 10) : 0;
      const targetRole = itemGroup > 0 ? itemGroup : parseInt(roleid, 10);
      return openDepartmentModal(targetRole, sh, 'edit');
    }

    try {
      const fn = new Function(onclick);
      fn.call(window);
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Aksi gagal dijalankan',
        text: error.message || 'Fungsi aksi tidak tersedia pada halaman ini.'
      });
    }
  }

  window.openDetailModal = function(id_kavling, rowData) {
    const row = rowData || {};
    const idKav = id_kavling || row.id_kavling;
    if (!idKav) return;

    syncProjectContextFromRow(row);
    const sh = buildKavlingShape($.extend({}, row, {
      id_kavling: idKav
    }));
    window.editdtt = [sh];

    if (typeof detail_kavling === 'function') {
      return detail_kavling(sh, idKav);
    }

    Swal.fire({
      icon: 'error',
      title: 'Modal detail tidak tersedia',
      text: 'Komponen detail kavling belum dimuat pada halaman.'
    });
  };

  function openDepartmentModal(roleToOpen, sh, source = 'edit') {
    const targetRole = parseInt(roleToOpen, 10);
    const idKavling = getKavlingIdFromShape(sh);

    window.editdtt = [sh];

    if ([3, 9, 10].includes(targetRole) && !sh.data.id_mkdt) {
      return Swal.fire({
        icon: 'warning',
        title: 'Data konsumen belum ada',
        text: 'Isi data konsumen dari MKDT terlebih dahulu sebelum membuka departemen ini.',
        showConfirmButton: true
      });
    }

    if (targetRole === 6) {
      if (!$('#modals-slide-in-edit').length) {
        return Swal.fire({
          icon: 'info',
          title: 'Planning dibuka dari Siteplan',
          text: 'Tambah atau ubah data planning kavling masih membutuhkan seleksi area di Siteplan.',
          showConfirmButton: true
        });
      }
      if (typeof open_planning === 'function') return open_planning(sh, targetRole, idKavling);
    }
    if (targetRole === 7 && typeof open_produksi === 'function') return open_produksi(sh, targetRole, idKavling);
    if (targetRole === 8) {
      if (typeof open_sales === 'function') return open_sales(sh, targetRole, idKavling);
      if (typeof open_checklist_sales === 'function') return open_checklist_sales();
    }
    if (targetRole === 5 && typeof open_legal === 'function') return open_legal(sh, targetRole, idKavling);
    if (targetRole === 4) {
      if (sh.data.id_mkdt && typeof open_mkdt === 'function') return open_mkdt(sh, targetRole, idKavling);
      if (typeof isi_data_konsumen === 'function') return isi_data_konsumen();
      if (typeof open_mkdt === 'function') return open_mkdt(sh, targetRole, idKavling);
    }
    if (targetRole === 9) {
      if (typeof open_direksi === 'function') return open_direksi(sh, targetRole, idKavling);
      if (typeof open_diskresi === 'function') return open_diskresi();
    }
    if (targetRole === 3 && typeof open_keuangan === 'function') return open_keuangan(sh, targetRole, idKavling);
    if (targetRole === 10 && typeof open_pajak === 'function') return open_pajak(sh, targetRole, idKavling);

    return Swal.fire({
      icon: 'error',
      title: 'Modal tidak tersedia',
      text: 'Komponen form departemen ini belum dimuat pada halaman.',
      showConfirmButton: true
    });
  }

  window.load_kavling = function() {
    if ($.fn.DataTable.isDataTable('#data_tables')) {
      $('#data_tables').DataTable().draw(false);
    }
  };

  window.hapus_seleksi = function() {
    window.editdtt = [];
  };

  window.openEdit = function(btn) {
    let rowData = $(btn).attr('data-kavling');

    if (!rowData) {
      console.error("Data baris tidak ditemukan pada atribut data-kavling");
      return;
    }

    let row = JSON.parse(rowData);
    console.log("Extracted row data:", row);

    let sh = buildKavlingShape(row);

    // Debug:
    console.log("Membuka Modal dengan Mock editdtt:", [sh]);
    openDepartmentModal(roleid == 1 ? 6 : roleid, sh, 'edit');
  };

  $(document).ajaxSuccess(function(event, xhr, settings) {
    if (settings.url.includes('simpan')) {
      if ($.fn.DataTable.isDataTable('#data_tables')) {
        $('#data_tables').DataTable().draw(false);
      }
    }
  });

  $(function() {
    if ($.fn.modal && $.fn.modal.Constructor && $.fn.modal.Constructor.prototype) {
      $.fn.modal.Constructor.prototype.enforceFocus = function() {};
      $.fn.modal.Constructor.prototype._enforceFocus = function() {};
    }

    var isMobileTable = window.matchMedia("(max-width: 767.98px)").matches;
    var table = null;

    initPoskonTable(isMobileTable);

    function applyPoskonTableHeight() {
      if (isMobileTable) return;
      var $wrapper = $('#data_tables_wrapper');
      var $scrollBody = $wrapper.find('.dataTables_scrollBody').first();
      if (!$scrollBody.length) return;

      // ponytail: pakai tinggi baris info/pagination langsung (bukan "semua children minus scroll"),
      // karena fixedColumns membungkus .dataTables_scroll di dalam .DTFC_ScrollWrapper sehingga
      // pendekatan "children().not(...)" ikut menghitung tabel itu sendiri sebagai "sisa di bawah".
      var $bottomBar = $wrapper.find('.dataTables_info, .dataTables_paginate').first().closest('.row');
      var bottomHeight = $bottomBar.length ? $bottomBar.outerHeight(true) : 50;

      var top = $scrollBody.offset().top;
      var $card = $scrollBody.closest('.card');
      var $cardBody = $card.find('.card-body').first();
      var cardBottomOverhead = (parseFloat($cardBody.css('padding-bottom')) || 0) +
        (parseFloat($card.css('margin-bottom')) || 0) + 8; // 8px jarak aman
      var available = Math.max(200, $(window).height() - top - bottomHeight - cardBottomOverhead);

      $scrollBody.css({
        height: available + 'px',
        maxHeight: available + 'px'
      });
      if ($.fn.dataTable.isDataTable('#data_tables')) {
        var api = $('#data_tables').DataTable();
        if (api.fixedColumns) api.fixedColumns().relayout();
      }
    }

    $(window).on('resize', function() {
      clearTimeout(window._poskonResizeTimer);
      window._poskonResizeTimer = setTimeout(applyPoskonTableHeight, 150);
    });
    $(window).on('load', applyPoskonTableHeight);

    function initPoskonTable(isMobileTable) {
    try {
      table = $('#data_tables').DataTable({
        fnDrawCallback: function() {
          $('[data-toggle="popover"]').popover();
          setTimeout(applyPoskonTableHeight, 10);
        },
        initComplete: function() {
          applyPoskonTableHeight();
        },
        scrollY: isMobileTable ? "60vh" : "50vh",
        scrollX: true,
        scrollCollapse: true,
        fixedColumns: {
          leftColumns: isMobileTable ? 0 : 6
        },
        processing: true,
        serverSide: true,
        lengthChange: true,
        pageLength: 25,
        searching: true,
        ordering: true,
        order: [
          [2, 'asc'],
          [3, 'asc']
        ],
        columnDefs: [{
            targets: '_all',
            orderable: false
          },
          {
            targets: [7, 9],
            orderable: true
          },
          {
            targets: [9, 15],
            visible: false
          }
        ],
        paging: true,
        ajax: {
          url: base_url + 'list-kavling/ambil',
          type: "POST",
          dataType: "json",
          data: {
            [csrfName]: csrfHash
          },
          data: function(data) {
            data[csrfName] = csrfHash
            data.id_proyek = activeProyekId()
            data.id_cluster = $("#id_cluster").val()
            data.id_jalan = $("#id_jalan").val()
            data.sp3k = $("#poskon_filter_sp3k").val()
            data.wawancara = $("#poskon_filter_wawancara").val()
            data.akad_indent = ($("#filter_status_kavling").val() === 'indent') ? 1 : ''
            data.bulan_field = $("#filter_bulan_field").val()
            data.bulan = $("#filter_bulan").val()
            data.tahun = $("#filter_tahun").val()
          },
          dataSrc: function(r) {
            csrfHash = r.token
            return r.data;
          },
          async: "true"
        }
      });
    } catch (error) {
      console.error('Gagal memuat DataTables list kavling:', error);
    }
    }

    $(document).on('click', '.poskon-detail-btn', function() {
      const row = decodePoskonRow($(this).attr('data-row'));
      openDetailModal(row.id_kavling, row);
    });

    $(document).on('click', '.poskon-menu-action', function(e) {
      e.preventDefault();
      const $btn = $(this);
      const row = decodePoskonRow($btn.attr('data-row'));
      const onclick = decodeURIComponent($btn.attr('data-onclick') || '');
      const menuItem = {
        id_group: parseInt($btn.attr('data-group') || '0', 10)
      };
      runSiteplanMenuAction(onclick, row, menuItem);
    });

    //on chnage search
    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        if (!table) return;
        table.search(this.value).draw();
      });

    //select filter for sp3k, wawancara, akad
    $(".self").select2();

    loadSiteplanMenuItems();

    if (activeProyekId()) {
      $("#id_cluster").prop("disabled", false);
      dt_proyek = {
        id_proyek: activeProyekId(),
        nama_proyek: window.SIGAPP.activeProyekName || ""
      };
    }

    function initFilterSelect2($el, options) {
      if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
      }
      $el.select2(options);
    }

    //select2 cluster
    initFilterSelect2($("#id_cluster"), {
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
    });
    // on select cluster
    $("#id_cluster").on("change", function(e) {
      $('#id_jalan').val(null).trigger('change');
      if (this.value)
        $("#id_jalan").prop("disabled", false)
      else
        $("#id_jalan").prop("disabled", true)
    });

    //select jalan
    initFilterSelect2($("#id_jalan"), {
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
    });

    $("#btn_open_add_modal").on("click", function() {
      $("#add_id_kavling").val(null).trigger('change');
      $("#add_menu_wrapper").prop('hidden', true);
      $("#add_menu_items").empty();
      $("#modal-tambah-poskon").modal({
        backdrop: "static",
        keyboard: false
      });
    });

    $("#add_id_kavling").select2({
      dropdownParent: $("#modal-tambah-poskon"),
      placeholder: "Cari Blok / No. Kavling",
      allowClear: true,
      width: '100%',
      ajax: {
        url: base_url + "kavling/list/ambil",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return {
            [csrfName]: csrfHash,
            search: params.term,
            id_proyek: activeProyekId(),
            only_available: 1,
            limit: 25
          };
        },
        processResults: function(r) {
          let results = [];
          $.each(r, function(index, item) {
            results.push({
              id: item.id_kavling,
              text: item.nama_jalan + ' No. ' + item.no_kavling,
              id_kavling: item.id_kavling,
              nama_jalan: item.nama_jalan,
              no_kavling: item.no_kavling
            });
          });

          return {
            results: results
          };
        },
        cache: true
      },
    });

    $("#add_id_kavling").on("select2:select", function() {
      const selectedKavling = $(this).select2('data')[0] || {};
      loadAddKavlingMenu(selectedKavling);
    });

    $("#add_id_kavling").on("select2:clear", function() {
      $("#add_menu_wrapper").prop('hidden', true);
      $("#add_menu_items").empty();
    });

    function loadAddKavlingMenu(selectedKavling) {
      $("#add_menu_wrapper").prop('hidden', false);
      $("#add_menu_items").html("<div class='text-center p-2'><i class='fa fa-spinner fa-spin'></i></div>");

      $.ajax({
        url: base_url + "siteplan/get_kavling_by_id",
        type: "post",
        dataType: "json",
        data: {
          [csrfName]: csrfHash,
          id_kavling: selectedKavling.id_kavling
        },
        success: function(res) {
          csrfHash = res.token;
          const row = $.extend({}, selectedKavling, res.data || {});
          const menuHtml = buildSiteplanMenuItemsHtml(row, 'list-group-item list-group-item-action');
          $("#add_menu_items").html(menuHtml || "<div class='text-muted p-2'>Tidak ada aksi tersedia</div>");
        },
        error: function() {
          $("#add_menu_items").html("<div class='text-danger p-2'>Gagal memuat menu aksi</div>");
        }
      });
    }

    //buka panel filter
    $("#btn_draw").on("click", function(e) {
      $("#modal-filter-lanjutan").modal("show");
    })

    function applyPoskonFilters() {
      if (table) {
        var filterVal = $("#filter_status_kavling").val();
        table.columns([9, 15]).visible(filterVal !== 'booking');
        var url = (filterVal === 'akad' || filterVal === 'indent') ?
          base_url + 'list-kavling/akad/ambil' :
          base_url + 'list-kavling/ambil';
        table.ajax.url(url).load();
      }
      load_riwayat();
      renderActiveFilterChips();
    }

    function resetPoskonFilterFields() {
      $("#id_cluster").val(null).trigger('change');
      $("#filter_status_kavling").val('booking').trigger('change');
      $("#filter_bulan_field").val('booking').trigger('change');
      $("#filter_bulan").val('').trigger('change');
      $("#filter_tahun").val('').trigger('change');
    }

    function removeFilterByKey(key) {
      if (key === 'id_cluster') {
        $("#id_cluster").val(null).trigger('change');
      } else if (key === 'id_jalan') {
        $("#id_jalan").val(null).trigger('change');
      } else if (key === 'filter_status_kavling') {
        $("#filter_status_kavling").val('booking').trigger('change');
      } else if (key === 'bulan_tahun') {
        $("#filter_bulan").val('').trigger('change');
        $("#filter_tahun").val('').trigger('change');
      }
      applyPoskonFilters();
    }

    function renderActiveFilterChips() {
      var chips = [];

      function addChip(key, label) {
        chips.push('<span class="poskon-filter-chip" data-filter-key="' + key + '">' + label +
          ' <i class="fa fa-times" data-remove-filter="' + key + '"></i></span>');
      }

      var clusterData = $("#id_cluster").select2('data')[0];
      if (clusterData && clusterData.id) addChip('id_cluster', 'Cluster: ' + $('<div>').text(clusterData.text).html());

      var jalanData = $("#id_jalan").select2('data')[0];
      if (jalanData && jalanData.id) addChip('id_jalan', 'Blok: ' + $('<div>').text(jalanData.text).html());

      var status = $("#filter_status_kavling").val();
      if (status !== 'booking') addChip('filter_status_kavling', 'Status: ' + $("#filter_status_kavling option:selected").text());

      var bulan = $("#filter_bulan").val();
      var tahun = $("#filter_tahun").val();
      if (bulan || tahun) {
        var fieldLabel = $("#filter_bulan_field option:selected").text();
        var bulanLabel = bulan ? $("#filter_bulan option:selected").text() : '';
        var parts = [bulanLabel, tahun].filter(Boolean).join(' ');
        addChip('bulan_tahun', fieldLabel + ': ' + parts);
      }

      $("#poskon-active-filters-chips").html(chips.join(''));
      $("#poskon-active-filters").prop('hidden', chips.length === 0);
    }

    $("#btn_filter_apply").on("click", function() {
      applyPoskonFilters();
      $("#modal-filter-lanjutan").modal("hide");
    });

    $("#btn_filter_reset").on("click", function() {
      resetPoskonFilterFields();
    });

    $(document).on('click', '[data-remove-filter]', function() {
      removeFilterByKey($(this).data('remove-filter'));
    });

    $("#btn_filter_clear_all").on('click', function() {
      resetPoskonFilterFields();
      applyPoskonFilters();
    });

    $("#btn_export_excel").on('click', function(e) {
      if (!activeProyekId()) {
        return Swal.fire({
          icon: 'error',
          title: "Proyek belum dipilih",
          showConfirmButton: false
        })
      }
      e.preventDefault();

      const $btn = $(this);

      // cegah klik berulang
      $btn.prop("disabled", true);
      $btn.data("old-text", $btn.text());
      export_file("xlsx", $btn)
    })
    $("#btn_export_pdf").on('click', function(e) {
      if (!activeProyekId()) {
        return Swal.fire({
          icon: 'error',
          title: "Proyek belum dipilih",
          showConfirmButton: false
        })
      }
      e.preventDefault();

      const $btn = $(this);

      // cegah klik berulang
      $btn.prop("disabled", true);
      $btn.data("old-text", $btn.text());
      export_file("pdf", $btn)
    })

    registerPoskonMobileBottomNav();

    function export_file(type, $btn) {
      $.ajax({
        type: "post",
        url: base_url + "export/poskon/" + type + "/aktif",
        data: {
          [csrfName]: csrfHash,
          id_proyek: activeProyekId(),
          id_cluster: $("#id_cluster").val(),
          id_jalan: $("#id_jalan").val(),
        },
        dataType: "json",
        beforeSend: function() {
          $btn.html("<i class='fa fa-spinner fa-spin'></i> Sedang Mengeksport")
          $btn.prop("disabled", true)
        },
        success: function(data) {
          var d = new Date()
          d = format_date(d.getFullYear() + "-" + (parseInt(d.getMonth()) + 1) + "-" + d.getDate());

          var $a = $("<a>");
          $a.attr("href", data.file);
          $("body").append($a);
          $a.attr("download", "Konsumen Aktif  Per " + d + ": " + (window.SIGAPP.activeProyekName || "Proyek") + "." + type);
          $a[0].click();
          $a.remove();
          $btn.html($btn.data("old-text"))
          $btn.prop("disabled", false)
        },
        error: function() {
          $btn.html($btn.data("old-text"))
          $btn.prop("disabled", false)
        }
      });
    }

    function registerMobileBottomNav(config) {
      window.SIGAPPMobileBottomNavQueue = window.SIGAPPMobileBottomNavQueue || [];
      if (window.SIGAPPMobileBottomNav && typeof window.SIGAPPMobileBottomNav.register === 'function') {
        window.SIGAPPMobileBottomNav.register(config);
      } else {
        window.SIGAPPMobileBottomNavQueue.push(config);
      }
    }

    function registerPoskonMobileBottomNav() {
      registerMobileBottomNav({
        // ponytail: filter sekarang pakai modal slide-in bersama (#modal-filter-lanjutan),
        // jadi tombol "Filter" di bawah cukup buka modal itu (lewat #btn_draw), tidak perlu
        // sheet filter terpisah yang memindahkan node grid keluar dari modal.
        actions: [{
            label: 'Filter',
            icon: 'fa fa-filter',
            className: 'btn btn-outline-primary',
            sourceSelector: '#btn_draw'
          },
          {
            label: 'Tambah',
            icon: 'fa fa-plus',
            className: 'btn btn-primary',
            sourceSelector: '#btn_open_add_modal'
          },
          {
            label: 'Excel',
            icon: 'fa fa-file-excel',
            className: 'btn btn-success',
            sourceSelector: '#btn_export_excel'
          },
          {
            label: 'PDF',
            icon: 'fa fa-file-pdf',
            className: 'btn btn-danger',
            sourceSelector: '#btn_export_pdf'
          }
        ],
        showBack: true,
        showMenu: true
      });
    }

    function load_riwayat() {
      $div = $("#riwayat-here")
      $div.empty()
      $.ajax({
        type: "post",
        url: base_url + "riwayat/poskon/aktif",
        data: {
          [csrfName]: csrfHash,
          id_proyek: activeProyekId(),
          id_cluster: $("#id_cluster").val(),
          id_jalan: $("#id_jalan").val(),
        },
        dataType: "json",
        beforeSend: function() {
          $div.append("<tr  ><td class='text-center' colspan=6><i class='fa fa-spinner fa-spin'></i> Memuat Data</td></tr>")
        },
        success: function(data) {
          $div.empty()
          let no = 1;
          if (data.length == 0) {
            $div.append("<tr  ><td class='text-center' colspan=6>Data Tidak Ditemukan</td></tr>")
          }
          $.each(data, function(index, item) {
            let icon = "PDF <i class='fa fa-file-pdf text-danger'></i>";
            if (item.tipe_file == "xlsx") {
              icon = "Excel <i class='fa fa-file-excel text-success'></i>";
            }
            $div.append("<tr><td>" + no++ + "</td><td>" + item.nama_proyek + "</td><td>" + format_datetime(item.export_tgl) + "</td><td>" + item.export_by + "</td><td>" + icon + "</td><td>" + (item.download_url ? "<a href='" + item.download_url + "' target='_blank' rel='noopener'>Download</a>" : "-") + "</td></tr>")
          })
        },
        error: function() {
          $div.empty()
          $div.append("<tr  ><td class='text-center' colspan=6>Data Tidak Ditemukan</td></tr>")
        }
      });
    }
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
      // e.target adalah tab yang baru saja aktif
      var target = $(e.target).attr("href");

      if (target === '#riwayat_eksport') {
        load_riwayat();
      }
    });
    // Hindari arrow select2 menutupi area klik (regresi dari style.css global)
    $("#poskon-filter .select2-selection__arrow, #modal-tambah-poskon .select2-selection__arrow, #modal-filter-lanjutan .select2-selection__arrow")
      .css("pointer-events", "none");

  });

  $('#tb-BLOK').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('#tb_nama_konsumen').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('#tb_tgl_booking, #tb_tgl_wwc, #tb_terbit, #tb_expired, #tb_pricelist').css({
    'min-width': '100px',
    'max-width': '100px'
  });
</script>

<?php
// Dapatkan role_id dari variable $k yang sudah di-set di atas
$role_id = $k;

// Include Modal View Sesuai Role
if (in_array($role_id, [6, 1])) {
  echo view('siteplan/planning');
}
if (in_array($role_id, [7, 1])) {
  echo view('siteplan/produksi');
}
if (in_array($role_id, [8, 1])) {
  echo view('siteplan/sales');
}
if (in_array($role_id, [5, 1])) {
  echo view('siteplan/legal');
}
if (in_array($role_id, [4, 1])) {
  echo view('siteplan/mkdt');
}
if (in_array($role_id, [9, 1])) {
  echo view('siteplan/direksi');
}
if (in_array($role_id, [3, 1])) {
  echo view('siteplan/keuangan');
}
if (in_array($role_id, [10, 1])) {
  echo view('siteplan/pajak');
}
if (in_array($role_id, [1, 7, 3])) {
  echo view('siteplan/cashout_subkon');
}
?>
<?= view('siteplan/partials/modal_detail', ['data' => $data['data'] ?? []]) ?>
<script src="<?= base_url() ?>assets/js/siteplan-detail-modal.js?<?= filemtime(FCPATH . 'assets/js/siteplan-detail-modal.js') ?>"></script>
