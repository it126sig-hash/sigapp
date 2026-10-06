<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/fixed-columns/fixedColumns.bootstrap4.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/richtext.min.css">
<link rel="stylesheet" type="text/css"
  href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<script>
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
  const state = {
    id_kavling: null,
    id_hargajual: null,
    id_mkdt: null,
    data_um: {},
    data_bb: {},
    sisa_cicilan: 0,
    sudah_bayar: 0,
    total_cicilan: 0,
    status: {
      tab: {
        isClosed: false
      }
    }

  };
  const li_keu = JSON.parse('<?= $li_keu ?>')
  const dt_proyek = {}
</script>
<style>
  .list-tagihan-page {
    text-transform: uppercase;
  }

  .list-tagihan-page .card-header {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
    padding: .6rem .85rem;
  }

  .list-tagihan-page .list-tagihan-title {
    color: #111827;
    font-size: 1rem;
    font-weight: 800;
    margin: 0;
    white-space: nowrap;
  }

  .list-tagihan-page .list-tagihan-divider {
    align-self: stretch;
    background: #e5e7eb;
    flex: 0 0 1px;
    width: 1px;
  }

  .list-tagihan-page .list-tagihan-filter {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .list-tagihan-page .filter-field {
    flex: 0 0 150px;
  }

  .list-tagihan-page .filter-field-sm {
    flex: 0 0 120px;
  }

  .list-tagihan-page .filter-action {
    flex: 0 0 auto;
  }

  .list-tagihan-page .form-control,
  .list-tagihan-page .select2-selection {
    border-color: #d8dde3 !important;
    border-radius: 6px !important;
    font-size: .78rem;
    min-height: 30px;
  }

  .list-tagihan-page select.form-control {
    padding: .25rem .5rem;
  }

  .list-tagihan-page .select2-selection__rendered {
    line-height: 28px !important;
  }

  .list-tagihan-page .select2-selection__arrow {
    height: 28px !important;
  }

  .list-tagihan-page .card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: none;
  }

  .list-tagihan-page .card-datatable {
    padding: .35rem;
  }

  #data_table {
    border-collapse: separate !important;
    border-spacing: 0;
    font-size: 10px;
    vertical-align: middle !important;
    width: 100% !important;
  }

  #data_table thead th {
    background: #eef5ff;
    border-bottom: 1px solid #c9ddf5;
    color: #2057a3;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: 0;
    padding: .55rem .5rem;
    white-space: nowrap;
  }

    #data_table tbody td:nth-child(1),
  #data_table thead th:nth-child(1),
  #data_table tbody td:nth-child(3),
  #data_table thead th:nth-child(3),
  #data_table tbody td:nth-child(4),
  #data_table thead th:nth-child(4),
  #data_table tbody td:nth-child(5),
  #data_table thead th:nth-child(5),
  #data_table tbody td:nth-child(9),
  #data_table thead th:nth-child(9) {
    text-align: center !important;
  }

  #data_table tbody td:nth-child(6),
  #data_table thead th:nth-child(6),
  #data_table tbody td:nth-child(7),
  #data_table thead th:nth-child(7),
  #data_table tbody td:nth-child(8),
  #data_table thead th:nth-child(8) {
    text-align: right !important;
  }

  #data_table tbody td:nth-child(2),
  #data_table thead th:nth-child(2) {
    text-align: left !important;
  }

  #data_table tbody td {
    border-color: #edf0f2;
    color: #111827;
    padding: .45rem .5rem;
    vertical-align: middle !important;
    white-space: nowrap;
  }

  #data_table tbody tr:hover td {
    background: #f8fbff;
  }

  .tagihan-detail-toggle {
    border-radius: 6px;
    font-size: .72rem;
    font-weight: 800;
    padding: .28rem .55rem;
  }

  .tagihan-detail-toggle {
    align-items: center;
    display: inline-flex;
    height: 28px;
    justify-content: center;
    width: 28px;
  }

  tr.shown .tagihan-detail-toggle {
    background: #2057a3;
    border-color: #2057a3;
    color: #fff;
  }

  .tagihan-child-wrap {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin: .35rem 0;
    padding: .65rem;
    text-transform: uppercase;
  }

  .tagihan-child-table {
    font-size: .75rem;
    margin-bottom: 0;
  }

  .tagihan-child-table th {
    background: #fff;
    color: #2057a3;
    font-weight: 800;
    white-space: nowrap;
  }

  .tagihan-child-table td,
  .tagihan-child-table th {
    padding: .45rem .55rem;
    vertical-align: middle;
  }

  .tagihan-child-loading,
  .tagihan-child-error,
  .tagihan-child-empty {
    color: #6b7280;
    font-size: .78rem;
    font-weight: 700;
    padding: .55rem;
  }

  .tagihan-child-error {
    color: #b91c1c;
  }

  #data_table tbody tr {
    cursor: pointer;
  }

  #data_table tbody tr.selected td {
    background-color: #e8f0fe !important;
    border-color: #c9ddf5 !important;
  }

  #detailDrawerModal .modal-dialog .modal-content {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    border-top-left-radius: 14px !important;
    border-bottom-left-radius: 14px !important;
  }

  #detailDrawerModal .modal-header {
    flex-shrink: 0;
    position: sticky;
    top: 0;
    z-index: 10;
    background: #ffffff;
    border-bottom: 1px solid #ebe9f1;
    padding: 1.1rem 1.25rem;
    margin-bottom: 0 !important;
    border-top-left-radius: 14px !important;
  }

  #detailDrawerModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto !important;
    padding: 1.25rem !important;
    margin: 0 !important;
  }

  #detailDrawerModal .modal-footer {
    flex-shrink: 0;
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: #ffffff;
    border-top: 1px solid #ebe9f1;
    padding: .85rem 1.25rem;
    border-bottom-left-radius: 14px !important;
  }

  #detailDrawerFooter .btn {
    border-radius: 8px !important;
  }

  .tagihan-action-dropdown .dropdown-toggle {
    border-radius: 50% !important;
    width: 28px;
    height: 28px;
    padding: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .tagihan-action-dropdown .dropdown-menu {
    border-radius: 8px !important;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(15, 23, 42, .12);
  }

  .list-tagihan-nav-tabs .nav-link {
    border-radius: 8px !important;
    font-size: .82rem;
    font-weight: 700;
    padding: .6rem 1.1rem;
    color: #4b5563;
    transition: all .2s ease;
  }
  .list-tagihan-nav-tabs .nav-link.active {
    background-color: #2057a3 !important;
    color: #ffffff !important;
    border-color: #2057a3 !important;
    box-shadow: 0 4px 12px rgba(32, 87, 163, 0.25);
  }
  #table_riwayat_pembayaran thead th,
  #table_riwayat_surat_tagihan thead th {
    background: #eef5ff;
    border-bottom: 1px solid #c9ddf5;
    color: #2057a3;
    font-size: .68rem;
    font-weight: 800;
    padding: .55rem .5rem;
    white-space: nowrap;
  }
  #table_riwayat_pembayaran tbody td,
  #table_riwayat_surat_tagihan tbody td {
    border-color: #edf0f2;
    color: #111827;
    padding: .5rem .5rem;
    vertical-align: middle !important;
    font-size: .78rem;
  }
  #table_riwayat_pembayaran tbody tr:hover td,
  #table_riwayat_surat_tagihan tbody tr:hover td {
    background: #f8fbff;
  }
  #table_riwayat_surat_tagihan tbody tr {
    cursor: pointer;
  }
  #table_riwayat_surat_tagihan tbody tr.selected td {
    background: #e8f0fe !important;
    border-color: #c9ddf5 !important;
  }
</style>
<!-- /.card-header -->
<div class="app-content content list-tagihan-page">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <!-- Navigation Tabs -->
        <div class="card mb-1">
          <div class="card-body p-50">
            <ul class="nav nav-tabs list-tagihan-nav-tabs mb-0 border-bottom-0" role="tablist">
              <li class="nav-item">
                <a class="nav-link active font-weight-bold" id="tab_tagihan-tab" data-toggle="tab" href="#tab_tagihan" role="tab" aria-selected="true">
                  <i class="fas fa-file-invoice mr-50"></i> DAFTAR TAGIHAN
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link font-weight-bold" id="tab_riwayat_bayar-tab" data-toggle="tab" href="#tab_riwayat_bayar" role="tab" aria-selected="false">
                  <i class="fas fa-receipt mr-50"></i> RIWAYAT PEMBAYARAN
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link font-weight-bold" id="tab_riwayat_surat-tab" data-toggle="tab" href="#tab_riwayat_surat" role="tab" aria-selected="false">
                  <i class="fas fa-envelope-open-text mr-50"></i> RIWAYAT SURAT TAGIHAN
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div class="tab-content">
          <!-- TAB 1: DAFTAR TAGIHAN -->
          <div class="tab-pane active" id="tab_tagihan" role="tabpanel" aria-labelledby="tab_tagihan-tab">
        <div class="card">
          <div class="card-header">
            <h5 class="list-tagihan-title"><?= $data['title'] ?></h5>
            <div class="list-tagihan-divider"></div>
            <div class="list-tagihan-filter">
              <div class="filter-action">
                <button type="button" class="btn btn-outline-secondary waves-effect btn-sm text-uppercase mr-50" id="btn_clear_filter_main">
                  <i class="fas fa-times mr-25"></i> Clear
                </button>
                <button type="button" class="btn btn-success waves-effect btn-sm text-uppercase mr-50" id="btn_export_excel" data-old-text="<i class='fa fa-file-excel mr-25'></i> Export Excel">
                  <i class="fas fa-file-excel mr-25"></i> Export Excel
                </button>
                <button type="button" class="btn btn-primary waves-effect btn-sm text-uppercase" data-toggle="modal" data-target="#filterModal">
                  <i class="fas fa-filter mr-25"></i> Filter Data
                </button>
              </div>
            </div>
          </div>
          <div class="card-body py-1 border-top" id="active_filter_container" style="display: none;">
            <div class="d-flex flex-wrap align-items-center" style="gap: .5rem;">
                <span class="font-weight-bold text-muted font-small-3 mr-50">Filter Aktif:</span>
                <div id="active_filter_tags" class="d-flex flex-wrap" style="gap: .5rem;"></div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-datatable">
            <table id="data_table" class="datatables-basic table compact">
              <thead>
                <tr>
                  <th class="text-center" id="tb-NO" width="5%">No</th>
                  <th class="text-left" style="text-align: left !important;" width="20%">KONSUMEN</th>
                  <th class="text-center" id="tb-TYPE" width="10%">TYPE</th>
                  <th class="text-center" width="10%">SKEMA</th>
                  <th class="text-center" id="tb-JATUH_TEMPO" width="15%">JATUH TEMPO</th>
                  <th class="text-right" id="tb-TOTAL_TAGIHAN" width="10%">TOTAL</th>
                  <th class="text-right" id="tb-SUDAH_BAYAR" width="10%">SUDAH BAYAR</th>
                  <th class="text-right font-weight-bolder" style="font-weight: 800 !important; color: #000;" id="tb-SISA_TAGIHAN" width="10%">SISA</th>
                  <th class="text-center" id="tb-AKSI" width="10%">AKSI</th>
                </tr>
                            </thead>
              <tfoot>
                <tr>
                  <th colspan="5" class="text-right align-middle" style="text-align: right !important; font-weight: bold;">Total (Halaman Ini):</th>
                  <th class="text-right align-middle" style="text-align: right !important; font-weight: bold;" id="footer-total">0</th>
                  <th class="text-right align-middle" style="text-align: right !important; font-weight: bold;" id="footer-sudah-bayar">0</th>
                  <th class="text-right align-middle" style="text-align: right !important; font-weight: bold;" id="footer-sisa">0</th>
                  <th></th>
                </tr>
              </tfoot>
            </table>

          </div>
        </div>
          </div>
          <!-- END TAB 1 -->

          <!-- TAB 2: RIWAYAT PEMBAYARAN -->
          <div class="tab-pane" id="tab_riwayat_bayar" role="tabpanel" aria-labelledby="tab_riwayat_bayar-tab">
            <div class="card mb-1">
              <div class="card-header d-flex justify-content-between align-items-center py-75 px-1">
                <h5 class="list-tagihan-title mb-0"><i class="fas fa-history text-primary mr-50"></i> Riwayat Pembayaran</h5>
                <button type="button" class="btn btn-outline-primary btn-sm waves-effect" id="btn_refresh_riwayat_bayar" title="Refresh Data">
                  <i class="fas fa-sync-alt mr-25"></i> Refresh
                </button>
              </div>
            </div>
            <div class="card">
              <div class="card-datatable p-50">
                <table id="table_riwayat_pembayaran" class="table compact w-100">
                  <thead>
                    <tr>
                      <th class="text-center" width="5%">No</th>
                      <th class="text-left" width="23%">Konsumen &amp; Kavling</th>
                      <th class="text-center" width="12%">Tanggal Pembayaran</th>
                      <th class="text-left" width="28%">Untuk Pembayaran</th>
                      <th class="text-left" width="17%">Tanggal Input + User</th>
                      <th class="text-center" width="15%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
          <!-- END TAB 2 -->

          <!-- TAB 3: RIWAYAT SURAT TAGIHAN -->
          <div class="tab-pane" id="tab_riwayat_surat" role="tabpanel" aria-labelledby="tab_riwayat_surat-tab">
            <div class="card mb-1">
              <div class="card-header d-flex justify-content-between align-items-center py-75 px-1">
                <h5 class="list-tagihan-title mb-0"><i class="fas fa-envelope-open-text text-primary mr-50"></i> Riwayat Surat Tagihan</h5>
                <button type="button" class="btn btn-outline-primary btn-sm waves-effect" id="btn_refresh_riwayat_surat" title="Refresh Data">
                  <i class="fas fa-sync-alt mr-25"></i> Refresh
                </button>
              </div>
            </div>
            <div class="row m-0">
              <!-- Left side: Table Surat Tagihan -->
              <div class="col-lg-7 p-0 pr-lg-50 mb-1">
                <div class="card h-100 mb-0">
                  <div class="card-datatable p-50">
                    <table id="table_riwayat_surat_tagihan" class="table compact w-100">
                      <thead>
                        <tr>
                          <th class="text-left" width="35%">No Surat &amp; Kavling</th>
                          <th class="text-left" width="25%">Tanggal &amp; Pembuat</th>
                          <th class="text-center" width="15%">TTD Direksi</th>
                          <th class="text-left" width="25%">Update Terakhir</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Right side: Detail Surat Panel -->
              <div class="col-lg-5 p-0 pl-lg-50 mb-1">
                <div class="card h-100 mb-0 shadow-none border" style="background: #fafbfe;">
                  <div class="card-body p-1" id="tab_surat_right_pane">
                    <!-- Empty state -->
                    <div id="tab_surat_detail_empty" class="text-center text-muted py-4">
                      <div class="mb-1"><i class="fas fa-file-invoice text-muted" style="font-size: 2.5rem; opacity: 0.5;"></i></div>
                      <h6 class="font-weight-bold text-muted">Belum Ada Surat Dipilih</h6>
                      <small>Pilih salah satu surat pada tabel di samping untuk melihat detail dan riwayat status surat.</small>
                    </div>

                    <!-- Detail content -->
                    <div id="tab_surat_detail_content" class="d-none">
                      <!-- Detail Box -->
                      <div class="card shadow-sm border mb-1 bg-white">
                        <div class="card-body p-1">
                          <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                              <small class="text-muted text-uppercase d-block mb-25 font-weight-bold" style="letter-spacing: 0.5px;">Detail Surat</small>
                              <h5 class="font-weight-bolder text-dark mb-0" id="tab_dtl_no_inv">-</h5>
                              <div class="font-weight-bold text-primary font-small-3 mt-25" id="tab_dtl_konsumen">-</div>
                              <div class="small text-muted" id="tab_dtl_kavling">-</div>
                            </div>
                            <div id="tab_dtl_status_badge"></div>
                          </div>

                          <div class="d-flex justify-content-between mb-50 border-top pt-50">
                            <small class="text-muted">Nominal tagihan</small>
                            <small class="font-weight-bolder text-primary font-medium-1" id="tab_dtl_nominal">Rp 0</small>
                          </div>
                          <div class="d-flex justify-content-between mb-50">
                            <small class="text-muted">Tanggal terbit</small>
                            <small class="font-weight-bold text-dark" id="tab_dtl_tgl_terbit">-</small>
                          </div>
                          <div class="d-flex justify-content-between mb-50">
                            <small class="text-muted">Jatuh tempo</small>
                            <small class="font-weight-bold text-dark" id="tab_dtl_jatuh_tempo">-</small>
                          </div>
                          <div class="d-flex justify-content-between mb-50">
                            <small class="text-muted">TTD Direksi</small>
                            <small class="font-weight-bold" id="tab_dtl_ttd_direksi">-</small>
                          </div>
                          <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Dibuat oleh</small>
                            <small class="font-weight-bold text-uppercase text-dark" id="tab_dtl_pembuat">-</small>
                          </div>

                          <div class="d-flex flex-wrap mt-1 border-top pt-1" style="gap: .5rem;" id="tab_dtl_actions">
                            <!-- Action buttons: Download, Ubah Status, Edit Surat -->
                          </div>
                        </div>
                      </div>

                      <h6 class="font-weight-bolder text-dark mb-1"><i class="fas fa-stream mr-50 text-primary"></i> Riwayat Surat</h6>
                      <ul class="timeline mb-0 pl-1" id="tab_dtl_riwayat_timeline">
                        <!-- Timeline items loaded dynamically -->
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- END TAB 3 -->
        </div>
      </div>
    </div>
  </section>

  <section>
    <!-- Modal Filter -->
    <div class="modal modal-slide-in fade" id="filterModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog sidebar-sm" role="document">
        <div class="modal-content pt-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
          <div class="modal-header mb-1">
            <h5 class="modal-title"><span class="align-middle"><i class="fas fa-filter text-primary mr-50"></i> Filter Tagihan</span></h5>
          </div>
          <div class="modal-body flex-grow-1">
            <div class="form-group">
              <label><i class="fas fa-map-marker-alt text-muted mr-50"></i> Cluster</label>
              <select disabled id="id_cluster" name="id_cluster" class="select2 form-control w-100"></select>
            </div>
            <div class="form-group">
              <label><i class="fas fa-home text-muted mr-50"></i> Blok</label>
              <select disabled id="id_jalan" name="id_jalan" class="select2 form-control w-100"></select>
            </div>
            <div class="form-group">
              <label><i class="fas fa-check-circle text-muted mr-50"></i> Status Tagihan</label>
              <select id="status_lunas" name="status_lunas" class="form-control">
                <option value="0">Belum Lunas</option>
                <option value="all">Semua</option>
                <option value="jatuh_tempo">Jatuh Tempo</option>
              </select>
            </div>
            <div class="form-group">
              <label><i class="fas fa-money-bill-wave text-muted mr-50"></i> Tunai / KPR</label>
              <select id="is_kpr" name="is_kpr" class="form-control">
                <option value="">Semua</option>
                <option value="0">Tunai</option>
                <option value="1">KPR</option>
              </select>
            </div>
            <div class="form-group">
              <label><i class="fas fa-calendar-plus text-muted mr-50"></i> Periode Tanggal Booking</label>
              <input type="text" id="booking_tgl_range" class="form-control flatpickr-range bg-white" placeholder="Pilih Range Tanggal">
            </div>
            <div class="form-group">
              <label><i class="fas fa-calendar-times text-muted mr-50"></i> Periode Jatuh Tempo</label>
              <input type="text" id="jatuh_tempo_tgl_range" class="form-control flatpickr-range bg-white" placeholder="Pilih Range Tanggal">
            </div>
            <div class="d-flex justify-content-between mt-2 border-top pt-2">
              <button type="button" class="btn btn-outline-secondary" id="btn_clear_filter_modal">
                 <i class="fas fa-times mr-25"></i> Clear Filter
              </button>
              <button type="button" class="btn btn-primary" id="btn_terapkan_filter">
                 <i class="fas fa-check mr-25"></i> Terapkan Filter
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section>
    <?= view('keuangan/partials/modal_bayar_tagihan') ?>
    <?= view('keuangan/partials/modal_penagihan') ?>
  
    <!-- Modal Detail Drawer -->
    <div class="modal modal-slide-in fade" id="detailDrawerModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog sidebar-sm" role="document">
        <div class="modal-content pt-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
          <div class="modal-header mb-1">
            <h5 class="modal-title"><span class="align-middle"><i class="fas fa-file-invoice-dollar text-primary mr-50"></i> Detail Jatuh Tempo</span></h5>
          </div>
          <div class="modal-body flex-grow-1" id="detailDrawerBody">
            <div class="text-center text-muted py-2">Memuat data...</div>
          </div>
          <div class="modal-footer p-1" id="detailDrawerFooter">
            <!-- Button will be injected here -->
          </div>
        </div>
      </div>
    </div>
  </section>
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
<script src="<?= base_url() ?>assets/js/tagihan-bayar-modal.js?v=<?= filemtime(FCPATH.'assets/js/tagihan-bayar-modal.js') ?>"></script>
<script src="<?= base_url() ?>assets/js/keuangan/penagihan.js?v=<?= time() ?>"></script>
<script>
  let fp = flatpickr(".flatpickr-human-friendly", {
    altInput: true,
    altFormat: 'F j, Y',
    dateFormat: 'Y-m-d'
  });

  let fpRange = flatpickr(".flatpickr-range", {
    mode: "range",
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "j F Y",
  });

  let listTagihanTable = null;
  let tagihanDetailCache = {};

  function isi_data(keepModalOpen = false) {
    if (!keepModalOpen) {
      $("#modal_divisi3").modal("hide");
    }
    tagihanDetailCache = {};
    if (listTagihanTable) listTagihanTable.ajax.reload(null, false);
  }

  function formatTagihanChild(data) {
    if (!Array.isArray(data) || data.length === 0) {
      return '<div class="tagihan-child-wrap"><div class="tagihan-child-empty">Tidak ada detail tagihan.</div></div>';
    }

    let total = 0;
    const rows = data.map((item, index) => {
      const nominal = keuToNumber(item.nominal);
      const isVoid = parseInt(item.is_void || 0) === 1;
      if (!isVoid) total += nominal;
      const isPaid = parseInt(item.sudah_dibayar || 0) === 1;
      const badge = isVoid
        ? `<span class="badge badge-secondary" title="${keuEscapeAttribute(item.void_reason || "")}">VOID</span>`
        : (isPaid ? '<span class="badge badge-success">LUNAS</span>' : '<span class="badge badge-warning">BELUM LUNAS</span>');
      const voidReason = isVoid && item.void_reason
        ? `<br><small class="text-danger">Alasan void: ${keuEscapeHtml(item.void_reason)}</small>`
        : "";

      return `
        <tr${isVoid ? ' class="text-muted"' : ""}>
          <td>${index + 1}</td>
          <td>${keuEscapeHtml(item.berita_acara || "-")}${voidReason}</td>
          <td>${format_date(item.jatuh_tempo_tgl) || "-"}</td>
          <td>${keuEscapeHtml(item.status || "-")}</td>
          <td class="text-right">Rp ${num_format(nominal)}</td>
          <td>${badge}</td>
        </tr>`;
    }).join("");

    return `
      <div class="tagihan-child-wrap">
        <div class="table-responsive">
          <table class="table table-sm table-bordered tagihan-child-table">
            <thead>
                <tr>
                  <th class="text-center" id="tb-NO" width="5%">No</th>
                  <th class="text-left" style="text-align: left !important;" width="20%">KONSUMEN</th>
                  <th class="text-center" id="tb-TYPE" width="10%">TYPE</th>
                  <th class="text-center" width="10%">SKEMA</th>
                  <th class="text-center" id="tb-JATUH_TEMPO" width="15%">JATUH TEMPO</th>
                  <th class="text-right" id="tb-TOTAL_TAGIHAN" width="10%">TOTAL</th>
                  <th class="text-right" id="tb-SUDAH_BAYAR" width="10%">SUDAH BAYAR</th>
                  <th class="text-right font-weight-bolder" style="font-weight: 800 !important; color: #000;" id="tb-SISA_TAGIHAN" width="10%">SISA</th>
                  <th class="text-center" id="tb-AKSI" width="10%">AKSI</th>
                </tr>
              </thead>
            <tbody>${rows}</tbody>
            <tfoot>
              <tr>
                <th colspan="4">Total</th>
                <th class="text-right">Rp ${num_format(total)}</th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>`;
  }

  function loadChildTagihan(row, rowData) {
    const idMkdt = rowData.id_mkdt;
    row.child('<div class="tagihan-child-wrap"><div class="tagihan-child-loading">Memuat detail tagihan...</div></div>').show();

    if (tagihanDetailCache[idMkdt]) {
      row.child(formatTagihanChild(tagihanDetailCache[idMkdt])).show();
      return;
    }

    $.ajax({
      url: base_url + "tagihan/list/detail",
      type: "post",
      dataType: "json",
      data: {
        [csrfName]: csrfHash,
        id_mkdt: idMkdt,
        status_lunas: $("#status_lunas").val(),
      },
      success: function(r) {
        if (r.token) csrfHash = r.token;
        if (r.success === true) {
          tagihanDetailCache[idMkdt] = Array.isArray(r.data) ? r.data : [];
          row.child(formatTagihanChild(tagihanDetailCache[idMkdt])).show();
        } else {
          row.child('<div class="tagihan-child-wrap"><div class="tagihan-child-error">' + keuEscapeHtml(r.message || "Gagal memuat detail tagihan") + '</div></div>').show();
        }
      },
      error: function() {
        row.child('<div class="tagihan-child-wrap"><div class="tagihan-child-error">Terjadi kesalahan saat memuat detail tagihan.</div></div>').show();
      },
    });
  }

  function fitTagihanTableHeight() {
    var $scrollBody = $('#data_table_wrapper .dataTables_scrollBody');
    if (!$scrollBody.length) return;
    var height = $(window).height() - $scrollBody.offset().top - 16;
    $scrollBody.css({
      'max-height': Math.max(200, height) + 'px',
      height: Math.max(200, height) + 'px'
    });
  }

  $(function() {
    listTagihanTable = $('#data_table').DataTable({
      fnDrawCallback: function() {
        $('[data-toggle="popover"]').popover();
        var api = this.api();
        setTimeout(function() {
          api.columns.adjust();
          fitTagihanTableHeight();
        }, 10);
      },
      scrollY: "60vh",
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
      footerCallback: function (row, data, start, end, display) {
          var api = this.api();
          var intVal = function (i) {
              if (typeof i === 'string') {
                  // extract numeric part, which might be in HTML or just a number string
                  // Sisa is rendered as: '<span class="font-weight-bolder text-dark" style="font-size: 1.05rem;">' + data + '</span>'
                  var stripped = i.replace(/(<([^>]+)>)/gi, ""); 
                  return stripped.replace(/[^\d]/g, '') * 1;
              }
              return typeof i === 'number' ? i : 0;
          };

          var total_tagihan = api.column(5, { page: 'current' }).data().reduce(function (a, b) {
              return intVal(a) + intVal(b);
          }, 0);
          
          var sudah_bayar = api.column(6, { page: 'current' }).data().reduce(function (a, b) {
              return intVal(a) + intVal(b);
          }, 0);
          
          var sisa = api.column(7, { page: 'current' }).data().reduce(function (a, b) {
              return intVal(a) + intVal(b);
          }, 0);

          $(api.column(5).footer()).html('Rp ' + num_format(total_tagihan));
          $(api.column(6).footer()).html('Rp ' + num_format(sudah_bayar));
          $(api.column(7).footer()).html('Rp ' + num_format(sisa));
      },
            order: [
        [4, "asc"]
      ],
      columns: [
        {
          data: "no",
          orderable: false,
          searchable: false,
          className: "text-center"
        },
        {
          data: "nama_konsumen",
          name: "c.nama_konsumen",
          className: "text-left",
          render: function(data, type, row) {
            return '<div class="font-weight-bold text-dark" style="text-align: left !important;">' + data + '</div>' +
                   '<div class="small text-muted mt-25" style="text-align: left !important;">' + row.nama_jalan + ' &bull; No. ' + row.no_kavling + '</div>';
          }
        },
        {
          data: "tipe_pricelist",
          name: "hj.id_tipe",
          className: "text-center",
          orderable: false
        },
        {
          data: "is_kpr",
          name: "m.is_kpr",
          className: "text-center",
          orderable: false,
                              render: function(data) {
            let isKpr = false;
            if (data == 1 || data === '1' || data === 'KPR' || data === true) {
                isKpr = true;
            } else if (typeof data === 'string' && data.indexOf('KPR') !== -1) {
                isKpr = true;
            }
            return isKpr ? '<span class="badge badge-success">KPR</span>' : '<span class="badge badge-primary">TUNAI</span>';
          }
        },
        {
          data: "jatuh_tempo_tgl",
          name: "keu_agg.jatuh_tempo_tgl",
          className: "text-center",
                    render: function(data, type, row) {
            if (type === 'display') {
              let rawData = row.jatuh_tempo_tgl_raw || data;
              if (!rawData) return '-';
              if (typeof rawData === 'string' && rawData.indexOf('<') !== -1) return data;
              
              let jtParts = rawData.split('-');
              let jtDate = new Date(jtParts[0], jtParts[1] - 1, jtParts[2]);
              let today = new Date();
              today.setHours(0,0,0,0);
              jtDate.setHours(0,0,0,0);
              
              let diffTime = today.getTime() - jtDate.getTime();
              let diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
              
              let html = '<div class="font-weight-bold">' + format_date(rawData) + '</div>';
              if (diffDays > 0) {
                html += '<div class="mt-25"><span class="badge badge-light-danger">' + diffDays + ' HARI TERLAMBAT</span></div>';
              } else if (diffDays === 0) {
                html += '<div class="mt-25"><span class="badge badge-light-warning">JATUH TEMPO HARI INI</span></div>';
              }
              
              html += '<div class="small text-muted mt-25">' + (row.jumlah_tagihan || 0) + ' item tagihan</div>';
              return html;
            }
            return data;
          }
        },
        {
          data: "total_tagihan",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "sudah_bayar",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "sisa_tagihan",
          orderable: false,
          searchable: false,
          className: "text-right",
          render: function(data) {
            return '<span class="font-weight-bolder text-dark" style="font-size: 1.05rem;">' + data + '</span>';
          }
        },
        {
          data: "Aksi",
          orderable: false,
          searchable: false,
          className: "text-center",
          render: function(data, type, row) {
            let rowJson = JSON.stringify(row).replace(/"/g, '&quot;');
            let bayarBtn = (data !== '-' ? data : '');

            let detailItem = `
              <a class="dropdown-item tagihan-action-detail" href="javascript:void(0);">
                <i class="fas fa-info-circle mr-50 text-info"></i> Lihat Detail
              </a>`;

            let buatTagihanItem = `
              <a class="dropdown-item tagihan-action-penagihan" href="javascript:void(0);">
                <i class="fas fa-file-invoice mr-50 text-primary"></i> Buat Tagihan
              </a>`;

            let dropdownHtml = `
              <div class="dropdown d-inline-block tagihan-action-dropdown">
                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle hide-arrow rounded-circle" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false" title="Menu Lainnya">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  ${detailItem}
                  ${buatTagihanItem}
                </div>
              </div>
            `;

            return '<div class="d-flex align-items-center justify-content-center" style="gap: .35rem;">' +
                      bayarBtn +
                      dropdownHtml +
                   '</div>';
          }
        }
      ],
      ajax: {
        url: base_url + 'tagihan/list/ambil-grouped',
        type: "POST",
        dataType: "json",
        data: function(data) {
          data[csrfName] = csrfHash
          data.id_proyek = activeProyekId()
          data.id_cluster = $("#id_cluster").val()
          data.id_jalan = $("#id_jalan").val()
          data.status_lunas = $("#status_lunas").val()
          data.is_kpr = $("#is_kpr").val()
          data.booking_tgl_range = $("#booking_tgl_range").val()
          data.jatuh_tempo_tgl_range = $("#jatuh_tempo_tgl_range").val()
        },
        dataSrc: function(r) {
          if (r.token) csrfHash = r.token
          return r.data;
        },
        async: "true"
      }
    });

    var resizeTimer;
    $(window).on('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(fitTagihanTableHeight, 150);
    });
    $(window).on('load', fitTagihanTableHeight);

    function openDetailDrawer(row, $tr) {
      if (!row) return;
      window.currentTagihanRow = row;

      if ($tr && $tr.length) {
        $('#data_table tbody tr.selected').removeClass('selected');
        $tr.addClass('selected');
      }

      const idMkdt = row.id_mkdt;
      
      $('#detailDrawerModal').modal('show');
      $('#detailDrawerBody').html('<div class="text-center text-muted py-2"><i class="fas fa-spinner fa-spin mr-50"></i> Memuat detail...</div>');
      
      // Footer buttons: Buat Tagihan + Bayar
      let sh = {
        data: {
          id_mkdt: row.id_mkdt,
          nama_proyek: row.nama_proyek,
          nama_jalan: row.nama_jalan,
          no_kavling: row.no_kavling
        },
        data2: {
          no_tipe_rumah: row.no_tipe_rumah,
          tipe_rumah: row.tipe_pricelist
        }
      };
      let shStr = JSON.stringify(sh).replace(/"/g, '&quot;');
      
      let bayarBtn = (row.Aksi !== '-') ? `
        <button type="button" class="btn btn-primary flex-fill text-uppercase font-small-3 font-weight-bold btn-drawer-bayar" onclick="open_keuangan(${shStr}, 3, 0); $('#detailDrawerModal').modal('hide');">
          <i class="fas fa-receipt mr-25"></i> Bayar
        </button>` : '';

      let buatTagihanBtn = `
        <button type="button" class="btn btn-outline-primary flex-fill text-uppercase font-small-3 font-weight-bold btn-drawer-buat-tagihan" data-id="${row.id_mkdt}">
          <i class="fas fa-file-invoice mr-25"></i> Buat Tagihan
        </button>
      `;

      $('#detailDrawerFooter').html(`
        <div class="d-flex w-100" style="gap: .5rem;">
          ${buatTagihanBtn}
          ${bayarBtn}
        </div>
      `);

      // Hitung selisih hari terdekat
      let badgeHtml = '';
      let rawDate = row.jatuh_tempo_tgl_raw || row.jatuh_tempo_tgl;
      if (rawDate && typeof rawDate === 'string' && rawDate.indexOf('<') === -1) {
          let jtParts = rawDate.split('-');
          let jtDate = new Date(jtParts[0], jtParts[1] - 1, jtParts[2]);
          let today = new Date();
          today.setHours(0,0,0,0);
          jtDate.setHours(0,0,0,0);
          let diffTime = today.getTime() - jtDate.getTime();
          let diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
          
          if (diffDays > 0) {
            badgeHtml = `<span class="badge badge-light-danger mt-50">${diffDays} HARI TERLAMBAT</span>`;
          } else if (diffDays === 0) {
            badgeHtml = `<span class="badge badge-light-warning mt-50">JATUH TEMPO HARI INI</span>`;
          }
      }

      let headerHtml = `
        <div class="mb-2 pb-1 border-bottom">
          <h6 class="font-weight-bolder mb-25 text-dark">${keuEscapeHtml(row.nama_konsumen || '-')}</h6>
          <div class="small text-muted">${keuEscapeHtml(row.nama_jalan || '-')} &bull; No. ${keuEscapeHtml(row.no_kavling || '-')} &bull; Type ${keuEscapeHtml(row.tipe_pricelist || '-')}</div>
        </div>
        
        <div class="mb-2 pb-1 border-bottom">
          <div class="text-uppercase font-weight-bold text-muted font-small-2 mb-1">Summary</div>
          <div class="mb-50">
            <div class="font-small-2 text-muted">Sisa Tagihan</div>
            <div class="font-weight-bolder text-danger" style="font-size: 1.1rem;">Rp ${row.sisa_tagihan}</div>
            <div>${badgeHtml}</div>
          </div>
          
          <div class="d-flex justify-content-between font-small-3 mt-1">
            <span class="text-muted">Total Tagihan</span>
            <span class="font-weight-bold">Rp ${row.total_tagihan}</span>
          </div>
          <div class="d-flex justify-content-between font-small-3 mt-25">
            <span class="text-muted">Sudah Bayar</span>
            <span class="font-weight-bold">Rp ${row.sudah_bayar}</span>
          </div>
          <div class="d-flex justify-content-between font-small-3 mt-25">
            <span class="text-muted">Skema</span>
            <span class="font-weight-bold">${(row.is_kpr == 1 || row.is_kpr === '1' || row.is_kpr === 'KPR' || row.is_kpr === true || (typeof row.is_kpr === 'string' && row.is_kpr.indexOf('KPR') !== -1)) ? '<span class="badge badge-success">KPR</span>' : '<span class="badge badge-primary">TUNAI</span>'}</span>
          </div>
        </div>
        
        <div class="text-uppercase font-weight-bold text-muted font-small-2 mb-75">Detail Item</div>
      `;
      
      $.ajax({
        url: base_url + "tagihan/list/detail",
        type: "post",
        dataType: "json",
        data: {
          [csrfName]: csrfHash,
          id_mkdt: idMkdt,
          status_lunas: $("#status_lunas").val(),
        },
        success: function(r) {
          if (r.token) csrfHash = r.token;
          
          let itemsHtml = '';
          if (r.success === true && Array.isArray(r.data) && r.data.length > 0) {
            r.data.forEach(function(item) {
              const isVoid = parseInt(item.is_void || 0) === 1;
              const isPaid = parseInt(item.sudah_dibayar || 0) === 1;
              
              let statusBadge = '';
              if (isVoid) {
                statusBadge = '<span class="badge badge-light-secondary" title="' + keuEscapeAttribute(item.void_reason || "") + '">VOID</span>';
              } else if (isPaid) {
                statusBadge = '<span class="badge badge-light-success">LUNAS</span>';
              } else {
                statusBadge = '<span class="badge badge-light-warning">BELUM LUNAS</span>';
              }
              
              let voidReasonHtml = (isVoid && item.void_reason)
                ? `<div class="small text-danger mt-25">Alasan void: ${keuEscapeHtml(item.void_reason)}</div>`
                : '';

              itemsHtml += `
                <div class="card border shadow-none mb-1">
                  <div class="card-body p-1">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="mr-1">
                        <div class="font-weight-bold text-dark font-small-3 mb-25">${keuEscapeHtml(item.berita_acara || '-')}</div>
                        <div class="text-muted font-small-2"><i class="fas fa-calendar-alt mr-25"></i> ${format_date(item.jatuh_tempo_tgl) || '-'}</div>
                        ${voidReasonHtml}
                      </div>
                      <div>
                        ${statusBadge}
                      </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-75 pt-50 border-top">
                      <span class="text-muted font-small-2">Nominal</span>
                      <span class="font-weight-bolder text-dark font-small-3">Rp ${num_format(item.nominal)}</span>
                    </div>
                  </div>
                </div>
              `;
            });
          } else {
            itemsHtml = '<div class="text-center text-muted font-small-3 py-1 bg-light rounded">Tidak ada detail tagihan yang belum lunas.</div>';
          }
          
          $('#detailDrawerBody').html(headerHtml + '<div class="detail-items-container">' + itemsHtml + '</div>');
        },
        error: function() {
          $('#detailDrawerBody').html('<div class="alert alert-danger p-1 font-small-3">Gagal memuat detail tagihan. Terjadi kesalahan jaringan.</div>');
        }
      });
    }

    // Row click to select and open detail drawer
    $('#data_table tbody').on('click', 'tr', function(e) {
      if ($(e.target).closest('.dropdown, .dropdown-menu, button, a, input, select, .custom-control').length) {
        return;
      }
      const row = listTagihanTable.row(this).data();
      if (!row) return;

      openDetailDrawer(row, $(this));
    });

    // Action Dropdown: Lihat Detail
    $('#data_table tbody').on('click', '.tagihan-action-detail', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const $tr = $(this).closest('tr');
      const row = listTagihanTable.row($tr).data();
      if (!row) return;
      openDetailDrawer(row, $tr);
    });

    // Action Dropdown: Buat Tagihan
    $('#data_table tbody').on('click', '.tagihan-action-penagihan', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const $tr = $(this).closest('tr');
      const row = listTagihanTable.row($tr).data();
      if (!row) return;
      if (typeof window.openModalPenagihan === 'function') {
        window.openModalPenagihan(row, 'tab_riwayat_tagihan');
      }
    });

    // Drawer Footer: Buat Tagihan
    $('#detailDrawerFooter').on('click', '.btn-drawer-buat-tagihan', function() {
      const row = window.currentTagihanRow;
      $('#detailDrawerModal').modal('hide');
      $('#detailDrawerModal').one('hidden.bs.modal', function() {
        if (row && typeof window.openModalPenagihan === 'function') {
          window.openModalPenagihan(row, 'tab_riwayat_tagihan');
        }
      });
    });

    //on chnage search
    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        listTagihanTable.search(this.value).draw();
      });

    if (activeProyekId()) {
      $("#id_cluster").prop("disabled", false);
      listTagihanTable.draw();
    }

    //select2 cluster
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
    // on select cluster
    $("#id_cluster").on("change", function(e) {
      $('#id_jalan').val(null).trigger('change');
      if (this.value)
        $("#id_jalan").prop("disabled", false)
      else
        $("#id_jalan").prop("disabled", true)
    });

    //select jalan
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

    $("#btn_terapkan_filter").on("click", function(e) {
      tagihanDetailCache = {};
      listTagihanTable.draw();
      $("#filterModal").modal("hide");
    });

    function updateActiveFilterTags() {
        let tags = [];
        
        let cluster = $("#id_cluster").select2('data');
        if (cluster && cluster.length > 0 && cluster[0].id) {
            tags.push(`<span class="badge badge-light-primary">Cluster: ${cluster[0].text}</span>`);
        }
        let jalan = $("#id_jalan").select2('data');
        if (jalan && jalan.length > 0 && jalan[0].id) {
            tags.push(`<span class="badge badge-light-primary">Blok: ${jalan[0].text}</span>`);
        }
        let lunas = $("#status_lunas option:selected").text();
        if ($("#status_lunas").val() != "0") {
            tags.push(`<span class="badge badge-light-info">Status: ${lunas}</span>`);
        }
        let isKpr = $("#is_kpr").val();
        if (isKpr !== "") {
            tags.push(`<span class="badge badge-light-warning">Tipe: ${isKpr == '1' ? 'KPR' : 'Tunai'}</span>`);
        }
        let booking = $("#booking_tgl_range").val();
        if (booking) {
            tags.push(`<span class="badge badge-light-success">Booking: ${booking}</span>`);
        }
        let jt = $("#jatuh_tempo_tgl_range").val();
        if (jt) {
            tags.push(`<span class="badge badge-light-danger">Jatuh Tempo: ${jt}</span>`);
        }

        if (tags.length > 0) {
            $("#active_filter_container").show();
            $("#active_filter_tags").html(tags.join(""));
        } else {
            $("#active_filter_container").hide();
            $("#active_filter_tags").html("");
        }
    }

    listTagihanTable.on('draw', function () {
        updateActiveFilterTags();
    });

    $("#btn_clear_filter_main, #btn_clear_filter_modal").on("click", function() {
        $("#id_cluster").val(null).trigger("change");
        $("#status_lunas").val("0");
        $("#is_kpr").val("");
        if (document.querySelector("#booking_tgl_range")._flatpickr) {
            document.querySelector("#booking_tgl_range")._flatpickr.clear();
        }
        if (document.querySelector("#jatuh_tempo_tgl_range")._flatpickr) {
            document.querySelector("#jatuh_tempo_tgl_range")._flatpickr.clear();
        }
        
        tagihanDetailCache = {};
        listTagihanTable.draw();
        $("#filterModal").modal("hide");
    });

    $("#btn_export_excel").on('click', function(e) {
      let $btn = $(this);
      $.ajax({
        type: "post",
        url: base_url + "tagihan/list/export-excel",
        data: {
          [csrfName]: csrfHash,
          id_proyek: activeProyekId(),
          id_cluster: $("#id_cluster").val(),
          id_jalan: $("#id_jalan").val(),
          status_lunas: $("#status_lunas").val(),
          is_kpr: $("#is_kpr").val(),
          booking_tgl_range: $("#booking_tgl_range").val(),
          jatuh_tempo_tgl_range: $("#jatuh_tempo_tgl_range").val(),
          search_value: $('.dataTables_filter input').val()
        },
        dataType: "json",
        beforeSend: function() {
          $btn.html("<i class='fa fa-spinner fa-spin mr-25'></i> Mengeksport...");
          $btn.prop("disabled", true);
        },
        success: function(data) {
          if (data.status) {
              var d = new Date()
              d = format_date(d.getFullYear() + "-" + (parseInt(d.getMonth()) + 1) + "-" + d.getDate());

              var $a = $("<a>");
              $a.attr("href", data.file);
              $("body").append($a);
              $a.attr("download", "Laporan_Tagihan_" + d + ".xlsx");
              $a[0].click();
              $a.remove();
          } else {
              alert("Gagal mengeksport data");
          }
          $btn.html($btn.data("old-text"));
          $btn.prop("disabled", false);
        },
        error: function() {
          alert("Terjadi kesalahan sistem saat eksport");
          $btn.html($btn.data("old-text"));
          $btn.prop("disabled", false);
        }
      });
    });

    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none");

    // ========================================================
    // TABS LAZY LOADING & RIWAYAT PEMBAYARAN / SURAT HANDLERS
    // ========================================================
    let riwayatBayarTableLoaded = false;
    let riwayatSuratTableLoaded = false;
    let riwayatBayarTable = null;
    let riwayatSuratTable = null;

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
      let target = $(e.target).attr('href');
      if (target === '#tab_riwayat_bayar') {
        if (!riwayatBayarTableLoaded) {
          initRiwayatBayarTable();
          riwayatBayarTableLoaded = true;
        } else if (riwayatBayarTable) {
          riwayatBayarTable.columns.adjust();
        }
      } else if (target === '#tab_riwayat_surat') {
        if (!riwayatSuratTableLoaded) {
          initRiwayatSuratTable();
          riwayatSuratTableLoaded = true;
        } else if (riwayatSuratTable) {
          riwayatSuratTable.columns.adjust();
        }
      } else if (target === '#tab_tagihan') {
        if (listTagihanTable) {
          listTagihanTable.columns.adjust();
        }
      }
    });

    function initRiwayatBayarTable() {
      riwayatBayarTable = $('#table_riwayat_pembayaran').DataTable({
        scrollY: "55vh",
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        lengthChange: true,
        pageLength: 25,
        searching: true,
        ordering: false,
        paging: true,
        ajax: {
          url: base_url + "tagihan/riwayat-bayar/ambil",
          type: "POST",
          dataType: "json",
          data: function(d) {
            d[csrfName] = csrfHash;
            d.id_proyek = activeProyekId();
            d.id_cluster = $("#id_cluster").val();
            d.id_jalan = $("#id_jalan").val();
          },
          dataSrc: function(r) {
            if (r.token) csrfHash = r.token;
            return r.data;
          }
        },
        columns: [
          {
            data: "no",
            className: "text-center",
            orderable: false,
            searchable: false,
            render: function(data, type, row, meta) {
              return meta.row + meta.settings._iDisplayStart + 1;
            }
          },
          {
            data: "nama_konsumen",
            className: "text-left",
            render: function(data, type, row) {
              return '<div class="font-weight-bold text-dark">' + keuEscapeHtml(data || '-') + '</div>' +
                     '<div class="small text-muted mt-25">' + keuEscapeHtml(row.nama_jalan || '-') + ' &bull; No. ' + keuEscapeHtml(row.no_kavling || '-') + '</div>';
            }
          },
          {
            data: "tanggal_bayar",
            className: "text-center",
            render: function(data) {
              return '<div class="font-weight-bold">' + (data ? format_date(data.split(' ')[0]) : '-') + '</div>';
            }
          },
          {
            data: "nominal",
            className: "text-left",
            render: function(data, type, row) {
              let nomHtml = '<div class="font-weight-bolder text-primary font-small-3">Rp ' + num_format(data) + '</div>';
              let typeBadge = row.payment_type ? '<span class="badge badge-light-secondary font-weight-bold mt-25 mr-25">' + keuEscapeHtml(row.payment_type) + '</span>' : '';
              let detail = row.detail_items ? '<div class="small text-muted mt-25">' + row.detail_items + '</div>' : '';
              let ket = (row.keterangan && row.keterangan !== row.payment_type) ? '<div class="small text-muted font-italic mt-25">' + keuEscapeHtml(row.keterangan) + '</div>' : '';
              return nomHtml + typeBadge + detail + ket;
            }
          },
          {
            data: "created_at",
            className: "text-left",
            render: function(data, type, row) {
              let tglStr = data ? format_datetime(data) : '-';
              let userStr = row.username ? '<div class="small text-muted font-weight-bold text-uppercase mt-25"><i class="fas fa-user mr-25"></i> ' + keuEscapeHtml(row.username) + '</div>' : '';
              return '<div>' + tglStr + '</div>' + userStr;
            }
          },
          {
            data: null,
            className: "text-center",
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
              let proyId = row.id_proyek || activeProyekId() || '';
              let btnPrint = '<button type="button" class="btn btn-outline-primary btn-sm mr-50" title="Cetak Kuitansi" onclick="printRiwayatBayar(\'' + row.id_pembayaran + '\', \'' + row.id_mkdt + '\', \'' + proyId + '\')"><i class="fas fa-print"></i></button>';
              let btnHapus = '<button type="button" class="btn btn-outline-danger btn-sm" title="Hapus Pembayaran" onclick="hapusRiwayatBayarCustom(\'' + row.id_pembayaran + '\')"><i class="fas fa-trash"></i></button>';
              return '<div class="btn-group">' + btnPrint + btnHapus + '</div>';
            }
          }
        ]
      });
    }

    window.hapusRiwayatBayarCustom = function(id) {
      Swal.fire({
        title: "Hapus Data?",
        text: "Apakah anda yakin akan menghapus data pembayaran ini?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
        confirmButtonClass: "btn btn-primary",
        cancelButtonClass: "btn btn-danger ml-1",
        buttonsStyling: false
      }).then(function(t) {
        if (t.value) {
          $.ajax({
            url: base_url + "pembayaran/hapus",
            type: "post",
            data: {
              [csrfName]: csrfHash,
              id_pembayaran: id
            },
            dataType: "json",
            success: function(r) {
              if (r.token) csrfHash = r.token;
              if (r.success) {
                Swal.fire({
                  icon: "success",
                  title: r.messages,
                  showConfirmButton: false,
                  timer: 1500
                });
                if (riwayatBayarTable) riwayatBayarTable.ajax.reload(null, false);
                if (listTagihanTable) listTagihanTable.ajax.reload(null, false);
              } else {
                Swal.fire({
                  icon: "error",
                  title: r.messages || "Gagal menghapus data"
                });
              }
            },
            error: function() {
              Swal.fire({
                icon: "error",
                title: "Terjadi kesalahan sistem saat menghapus data"
              });
            }
          });
        }
      });
    };

    function initRiwayatSuratTable() {
      riwayatSuratTable = $('#table_riwayat_surat_tagihan').DataTable({
        scrollY: "55vh",
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        lengthChange: true,
        pageLength: 25,
        searching: true,
        ordering: false,
        paging: true,
        ajax: {
          url: base_url + "tagihan/list/ambil-riwayat-surat",
          type: "POST",
          dataType: "json",
          data: function(d) {
            d[csrfName] = csrfHash;
            d.id_proyek = activeProyekId();
            d.id_cluster = $("#id_cluster").val();
            d.id_jalan = $("#id_jalan").val();
          },
          dataSrc: function(r) {
            if (r.token) csrfHash = r.token;
            return r.data;
          }
        },
        columns: [
          {
            data: "nomor_surat",
            className: "text-left",
            render: function(data, type, row) {
              let st = (row.status_tagihan || '').toLowerCase();
              let nomorTampil = (st === 'draft') ? (row.no_inv || '-') : (data || row.no_inv || '-');
              return '<div class="font-weight-bolder text-dark font-small-3">' + keuEscapeHtml(nomorTampil) + '</div>' +
                     '<div class="font-weight-bold text-primary font-small-2">' + keuEscapeHtml(row.nama_konsumen || '-') + '</div>' +
                     '<div class="small text-muted">' + keuEscapeHtml(row.nama_jalan || '-') + ' &bull; No. ' + keuEscapeHtml(row.no_kavling || '-') + '</div>';
            }
          },
          {
            data: "tanggal_invoice",
            className: "text-left",
            render: function(data, type, row) {
              let tgl = data ? format_date(data.split(' ')[0]) : '-';
              let pembuat = row.pembuat ? '<div class="small text-muted font-weight-bold text-uppercase mt-25"><i class="fas fa-user-edit mr-25"></i> ' + keuEscapeHtml(row.pembuat) + '</div>' : '';
              return '<div class="font-weight-bold">' + tgl + '</div>' + pembuat;
            }
          },
          {
            data: "is_signed_direktur",
            className: "text-center",
            render: function(data, type, row) {
              let isSigned = parseInt(data || 0) === 1;
              if (isSigned) {
                let tglSign = row.signed_at ? format_date(row.signed_at.split(' ')[0]) : '';
                return '<span class="badge badge-light-success font-weight-bold"><i class="fas fa-check-circle mr-25"></i> Sudah' + (tglSign ? '<br><small>' + tglSign + '</small>' : '') + '</span>';
              }
              return '<span class="badge badge-light-secondary font-weight-bold"><i class="fas fa-clock mr-25"></i> Belum</span>';
            }
          },
          {
            data: "status_tagihan",
            className: "text-left",
            render: function(data, type, row) {
              let st = (data || '').toLowerCase();
              let statusBadge = '';
              if (st === 'draft') statusBadge = '<span class="badge badge-light-secondary font-weight-bold">DRAFT</span>';
              else if (st === 'publish') statusBadge = '<span class="badge badge-light-primary font-weight-bold">PUBLISH</span>';
              else if (st === 'dibuat') statusBadge = '<span class="badge badge-light-secondary font-weight-bold">DIBUAT</span>';
              else if (st === 'dikirim') statusBadge = '<span class="badge badge-light-info font-weight-bold">DIKIRIM</span>';
              else if (st === 'respon') statusBadge = '<span class="badge badge-light-success font-weight-bold">RESPON</span>';
              else if (st === 'tidak respon') statusBadge = '<span class="badge badge-light-danger font-weight-bold">TIDAK RESPON</span>';
              else if (st === 'batal') statusBadge = '<span class="badge badge-light-dark font-weight-bold">BATAL</span>';
              else statusBadge = '<span class="badge badge-light-primary font-weight-bold text-uppercase">' + keuEscapeHtml(data || '-') + '</span>';

              let updateTgl = row.tanggal_ubah_status ? format_date(row.tanggal_ubah_status.split(' ')[0]) : (row.date_edit ? format_date(row.date_edit.split(' ')[0]) : (row.date_add ? format_date(row.date_add.split(' ')[0]) : '-'));
              let rawKet = (row.keterangan_status || '').trim();
              let ketSnippet = '';
              if (rawKet) {
                let truncated = rawKet.length > 30 ? rawKet.substring(0, 30) + '...' : rawKet;
                ketSnippet = '<div class="small text-muted mt-25" title="' + keuEscapeAttribute(rawKet) + '">' + keuEscapeHtml(truncated) + '</div>';
              }
              return '<div>' + statusBadge + ' <small class="text-muted font-weight-bold ml-25">' + updateTgl + '</small></div>' + ketSnippet;
            }
          }
        ]
      });

      $('#table_riwayat_surat_tagihan tbody').on('click', 'tr', function(e) {
        if ($(e.target).closest('button, a, input').length) return;
        $('#table_riwayat_surat_tagihan tbody tr').removeClass('selected');
        $(this).addClass('selected');
        let rowData = riwayatSuratTable.row(this).data();
        if (rowData) {
          showTabSuratDetail(rowData);
        }
      });
    }

    function showTabSuratDetail(v) {
      if (!v) return;
      $('#tab_surat_detail_empty').addClass('d-none');
      $('#tab_surat_detail_content').removeClass('d-none');

      let st = (v.status_tagihan || '').toLowerCase();
      let nomorTampilDtl = (st === 'draft') ? (v.no_inv || '-') : (v.nomor_surat || v.no_inv || '-');
      $('#tab_dtl_no_inv').text(nomorTampilDtl);
      $('#tab_dtl_konsumen').text(v.nama_konsumen || '-');
      $('#tab_dtl_kavling').text((v.nama_jalan || '-') + ' • No. ' + (v.no_kavling || '-'));

      let totalNominalSurat = 0;
      try {
        let rawTg = v.tagihan;
        if (typeof rawTg === 'string') {
          rawTg = rawTg.replace(/&quot;/g, '"');
          rawTg = JSON.parse(rawTg);
        }
        if (Array.isArray(rawTg)) {
          rawTg.forEach(function(item) {
            totalNominalSurat += parseFloat(item.nominal || 0);
          });
        }
      } catch(e) {}
      $('#tab_dtl_nominal').text('Rp ' + num_format(totalNominalSurat));
      $('#tab_dtl_tgl_terbit').text(v.tanggal_invoice ? format_date(v.tanggal_invoice.split(' ')[0]) : '-');
      $('#tab_dtl_jatuh_tempo').text(v.tanggal_jatuh_tempo ? format_date(v.tanggal_jatuh_tempo.split(' ')[0]) : '-');

      let isSignedDtl = parseInt(v.is_signed_direktur || 0) === 1;
      let ttdDtlText = isSignedDtl
        ? '<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-25"></i> Sudah ' + (v.signed_at ? '(' + format_date(v.signed_at.split(' ')[0]) + ')' : '') + '</span>'
        : '<span class="text-secondary font-weight-bold"><i class="fas fa-clock mr-25"></i> Belum</span>';
      $('#tab_dtl_ttd_direksi').html(ttdDtlText);
      $('#tab_dtl_pembuat').text(v.pembuat || '-');

      let statusBadge = '';
      if (st === 'draft') statusBadge = '<span class="badge badge-secondary font-weight-bold">DRAFT</span>';
      else if (st === 'publish') statusBadge = '<span class="badge badge-primary font-weight-bold">PUBLISH</span>';
      else if (st === 'dibuat') statusBadge = '<span class="badge badge-secondary font-weight-bold">DIBUAT</span>';
      else if (st === 'dikirim') statusBadge = '<span class="badge badge-info font-weight-bold">DIKIRIM</span>';
      else if (st === 'respon') statusBadge = '<span class="badge badge-success font-weight-bold">RESPON</span>';
      else if (st === 'tidak respon') statusBadge = '<span class="badge badge-danger font-weight-bold">TIDAK RESPON</span>';
      else if (st === 'batal') statusBadge = '<span class="badge badge-dark font-weight-bold">BATAL</span>';
      else statusBadge = '<span class="badge badge-light-primary font-weight-bold text-uppercase">' + keuEscapeHtml(v.status_tagihan || '-') + '</span>';
      $('#tab_dtl_status_badge').html(statusBadge);

      let actionsHtml = '';
      if (st === 'draft') {
        actionsHtml += '<button type="button" class="btn btn-sm btn-outline-warning mr-50 btn-edit-tagihan" data-inv=\'' + JSON.stringify(v).replace(/'/g, "&#39;") + '\'><i class="fas fa-edit mr-25"></i> Edit Surat</button>';
      }
      actionsHtml += '<a href="' + base_url + 'keuangan/download_penagihan?id=' + encodeURIComponent(v.no_inv) + '" target="_blank" class="btn btn-sm btn-outline-primary mr-50"><i class="fas fa-download mr-25"></i> Download</a>';
      actionsHtml += '<button type="button" class="btn btn-sm btn-outline-info btn-ubah-status-tagihan" data-no="' + v.no_inv + '" data-status="' + (v.status_tagihan || '') + '" data-tgl="' + (v.tanggal_ubah_status || '') + '" data-ket="' + (v.keterangan_status || '') + '" data-nomorsurat="' + (v.nomor_surat || '') + '"><i class="fas fa-exchange-alt mr-25"></i> Ubah Status</button>';
      $('#tab_dtl_actions').html(actionsHtml);

      loadTabSuratTimeline(v);
    }

    function loadTabSuratTimeline(v) {
      $('#tab_dtl_riwayat_timeline').html('<li class="text-muted small py-1"><i class="fas fa-spinner fa-spin mr-50"></i> Memuat riwayat status...</li>');
      $.ajax({
        url: base_url + "keuangan/get_riwayat_tagihan",
        type: "POST",
        dataType: "json",
        data: {
          [csrfName]: csrfHash,
          id_mkdt: v.id_mkdt,
          no_inv: v.no_inv
        },
        success: function(r) {
          if (r.token) csrfHash = r.token;
          if (r.success && r.data && r.data.length > 0) {
            let invData = r.data[0];
            let timelineList = Array.isArray(invData.lifecycle) ? [...invData.lifecycle] : [];

            timelineList.sort(function(a, b) {
              let timeA = new Date(a.date_add || a.tanggal || 0).getTime();
              let timeB = new Date(b.date_add || b.tanggal || 0).getTime();
              if (timeB !== timeA) return timeB - timeA;
              return (parseInt(b.id) || 0) - (parseInt(a.id) || 0);
            });

            let timelineHtml = '';
            $.each(timelineList, function(idx, log) {
              let logStatus = (log.status || '').toLowerCase();
              let pointColor = 'timeline-point-primary';
              let titleText = 'Surat dibuat';

              if (logStatus === 'dibuat') {
                pointColor = 'timeline-point-primary';
                titleText = 'Surat dibuat';
              } else if (logStatus === 'dikirim') {
                pointColor = 'timeline-point-info';
                titleText = 'Surat dikirim';
              } else if (logStatus === 'respon') {
                pointColor = 'timeline-point-success';
                titleText = 'Surat direspon';
              } else if (logStatus === 'tidak respon') {
                pointColor = 'timeline-point-danger';
                titleText = 'Tidak ada respon';
              } else {
                pointColor = 'timeline-point-secondary';
                titleText = 'Status: ' + (log.status || '-');
              }

              let dateOnly = (log.tanggal || log.date_add || '').split(' ')[0];
              let timeStr = format_date(dateOnly);
              if (log.date_add && log.date_add.indexOf(' ') !== -1) {
                let timePart = log.date_add.split(' ')[1];
                if (timePart) timeStr += ' - ' + timePart.substring(0, 5);
              }
              if (log.pembuat) timeStr += ' - ' + log.pembuat;

              let ketText = log.keterangan || (logStatus === 'dibuat' ? 'Surat penagihan berhasil dibuat.' : '-');
              let safeKet = $('<div>').text(ketText).html();

              timelineHtml += `
                <li class="timeline-item">
                  <span class="timeline-point timeline-point-indicator ${pointColor}"></span>
                  <div class="timeline-event">
                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-25">
                      <h6 class="font-weight-bolder text-dark mb-0">${titleText}</h6>
                    </div>
                    <span class="timeline-event-time small text-muted d-block mb-50">${timeStr}</span>
                    <div class="card shadow-none border bg-white mb-0">
                      <div class="card-body p-75 small text-dark">
                        ${safeKet}
                      </div>
                    </div>
                  </div>
                </li>
              `;
            });
            $('#tab_dtl_riwayat_timeline').html(timelineHtml || '<li class="text-muted small py-1">Tidak ada riwayat.</li>');
          } else {
            $('#tab_dtl_riwayat_timeline').html('<li class="text-muted small py-1">Tidak ada riwayat.</li>');
          }
        },
        error: function() {
          $('#tab_dtl_riwayat_timeline').html('<li class="text-danger small py-1">Gagal memuat riwayat status.</li>');
        }
      });
    }

    $('#btn_refresh_riwayat_bayar').on('click', function() {
      if (riwayatBayarTable) riwayatBayarTable.ajax.reload(null, false);
    });

    $('#btn_refresh_riwayat_surat').on('click', function() {
      if (riwayatSuratTable) riwayatSuratTable.ajax.reload(null, false);
    });

    $('#modal_ubah_status_tagihan').on('hidden.bs.modal', function() {
      if (riwayatSuratTable) riwayatSuratTable.ajax.reload(null, false);
      let selectedRow = $('#table_riwayat_surat_tagihan tbody tr.selected');
      if (selectedRow.length && riwayatSuratTable) {
        let rowData = riwayatSuratTable.row(selectedRow).data();
        if (rowData) showTabSuratDetail(rowData);
      }
    });

  });
</script>














