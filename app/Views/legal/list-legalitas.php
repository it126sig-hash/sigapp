<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/legal.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/legal.css') ?>">

<script>
  window.LEGAL_CONFIG = {
    baseUrl: '<?= rtrim(base_url(), '/') ?>',
    csrfName: '<?= csrf_token() ?>',
    csrfHash: '<?= csrf_hash() ?>'
  };
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
</script>

<div class="app-content content legal-page">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <div class="content-wrapper">
    <div class="content-header row mb-1">
      <div class="content-header-left col-md-7 col-12">
        <h2 class="content-header-title mb-0"><?= esc($data['title'] ?? 'Legalitas') ?></h2>
      </div>
    </div>
    <div class="content-body">
      <section id="basic-datatable">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header border-bottom">
                <h4 class="card-title"><?= esc($data['title'] ?? 'Legalitas') ?></h4>
              </div>
              <div class="card-body pt-2">
                <div class="row">
                  <div class="col-md-4 col-12 mb-1">
                    <label for="id_cluster" class="font-weight-bold">Cluster</label>
                    <select disabled id="id_cluster" name="id_cluster" class="select2 form-control"></select>
                  </div>
                  <div class="col-md-4 col-12 mb-1">
                    <label for="id_jalan" class="font-weight-bold">Blok</label>
                    <select disabled id="id_jalan" name="id_jalan" class="select2 form-control"></select>
                  </div>
                  <div class="col-md-4 col-12 mb-1 d-flex align-items-end" style="gap: .5rem;">
                    <button type="button" id="btn_draw" class="btn btn-primary waves-effect">
                      <i class="fa fa-filter mr-50"></i>Filter Data
                    </button>
                    <button type="button" id="btn_reset_filter" class="btn btn-outline-secondary waves-effect">
                      <i class="fa fa-refresh mr-50"></i>Reset
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="card">
              <div class="card-datatable table-responsive">
                <table id="data_table" class="datatables-basic table compact table-hover w-100">
                  <thead>
                    <tr>
                      <th id="no">No</th>
                      <th id="blok">Blok</th>
                      <th id="no_kavling">No</th>
                      <th id="tipe_rumah">Tipe</th>
                      <th id="no_hgb_induk">No HGB Induk</th>
                      <th id="no_hgb">No HGB</th>
                      <th id="nib">NIB</th>
                      <th id="balik_nama">Balik Nama</th>
                      <th id="nop_pbb">NOP PBB</th>
                      <th id="verifikasi_pbb">Verifikasi PBB</th>
                      <th id="validasi_bphtb">Validasi BPHTB</th>
                      <th id="no_imb_pbg">No IMB/PBG</th>
                      <th id="tanggal_validasi_pph">Tanggal Validasi PPH</th>
                      <th id="no_ajb">No AJB</th>
                      <th id="no_ppjb">No PPJB</th>
                      <th id="nama_konsumen">Nama Konsumen</th>
                      <th class="created" id="created_at">Diinput Oleh</th>
                      <th class="created" id="created_by">Diubah Oleh</th>
                      <th class="text-center" id="aksi">Aksi</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>

<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/validation/jquery.validate.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/polyfill.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script src="<?= base_url('assets/js/legal.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/legal.js') ?>"></script>
