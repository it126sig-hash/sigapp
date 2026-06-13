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
    justify-content: space-between;
    gap: .75rem 1rem;
    padding: .85rem 1rem .7rem;
    border-bottom: 1px solid #edf0f4;
    background: #fff;
  }

  #poskon-filter .poskon-toolbar-title h2 {
    margin: 0;
    color: #1f2937;
    font-size: 1.12rem;
    font-weight: 600;
    line-height: 1.3;
  }

  #poskon-filter .poskon-filter-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(170px, 1fr));
    gap: .75rem;
    padding: .85rem 1rem 1rem;
    background: #f8fafc;
  }

  #poskon-filter .poskon-filter-field {
    min-width: 0;
    margin-bottom: 0 !important;
  }

  #poskon-filter .poskon-filter-field label {
    display: block;
    margin-bottom: .25rem;
    color: #6b7280;
    font-size: .72rem;
    font-weight: 600;
    line-height: 1.2;
  }

  #poskon-filter .form-control,
  #poskon-filter .select2-container--default .select2-selection--single {
    min-height: 34px;
    border-color: #d8dee8;
    border-radius: 6px;
  }

  #poskon-filter .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 32px;
    font-size: .86rem;
  }

  #poskon-filter .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 32px;
  }

  .poskon-filter-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    align-items: center;
    justify-content: flex-end;
  }

  .poskon-filter-actions .btn,
  .poskon-filter-actions .btn-group .btn {
    min-height: 32px;
    padding: .32rem .65rem;
    border-radius: 6px;
    font-size: .82rem;
    font-weight: 600;
    line-height: 1.2;
  }

  .poskon-filter-actions .btn i {
    margin-right: .25rem;
  }

  #poskon-filter .btn-primary {
    border-color: #2057a3;
    background-color: #2057a3;
  }

  #poskon-filter .btn-primary:hover,
  #poskon-filter .btn-primary:focus {
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

  .poskon-table-card .card-body {
    padding: 1rem;
  }

  #data_tables {
    width: 100% !important;
  }

  #poskon-filter .select2-container,
  #modal-tambah-poskon .select2-container {
    width: 100% !important;
  }

  .project-select-option {
    display: flex;
    align-items: center;
    gap: .5rem;
    min-width: 0;
  }

  .project-select-option img {
    width: 26px;
    height: 26px;
    flex: 0 0 26px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    object-fit: contain;
    background: #fff;
  }

  .project-select-option span {
    min-width: 0;
    overflow: hidden;
    color: #1f2937;
    font-size: .86rem;
    line-height: 1.25;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  #poskon-filter .select2-selection__rendered .project-select-option,
  #modal-tambah-poskon .select2-selection__rendered .project-select-option {
    height: 32px;
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

    #poskon-filter .poskon-filter-grid {
      grid-template-columns: 1fr;
      gap: .65rem;
      padding: .85rem;
    }

    #poskon-filter .poskon-filter-field {
      width: 100%;
      max-width: 100%;
    }

    .poskon-filter-actions {
      width: 100%;
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .5rem;
    }

    .poskon-filter-actions .btn-group {
      width: 100%;
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      grid-column: 1 / -1;
      gap: .5rem;
    }

    .poskon-filter-actions .btn,
    .poskon-filter-actions .btn-group .btn {
      width: 100%;
      margin-left: 0 !important;
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
        <div class="card" id="poskon-filter">
          <div class="poskon-toolbar">
            <div class="poskon-toolbar-title">
              <h2>Posisi Konsumen Aktif</h2>
            </div>
            <div class="poskon-filter-actions">
              <button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect btn-sm" title="Filter Data"><i class="fa fa-filter"></i> Filter</button>
              <button type="button" id="btn_open_add_modal" class="btn btn-primary waves-effect btn-sm" title="Tambah Data"><i class="fa fa-plus"></i> Tambah</button>
              <div class="btn-group">
                <button type="button" id="btn_export_excel" class="btn btn-success waves-effect btn-sm" title="Export Excel"><i class="fa fa-file-excel"></i> Excel</button>
                <button type="button" id="btn_export_pdf" class="btn btn-danger waves-effect btn-sm" title="Export PDF"><i class="fa fa-file-pdf"></i> PDF</button>
              </div>
            </div>
          </div>
          <div class="poskon-filter-grid">
            <div class="poskon-filter-field">
              <label>Proyek</label>
              <select id="id_proyek" name="id_proyek" class="select2 form-control"></select>
            </div>
            <div class="poskon-filter-field">
              <label>Cluster</label>
              <select disabled id="id_cluster" name="id_cluster" class="select2  form-control"></select>
            </div>
            <div class="poskon-filter-field">
              <label>Blok</label>
              <select disabled id="id_jalan" name="id_jalan" class="select2 form-control"></select>
            </div>
            <div class="poskon-filter-field" hidden>
              <label>Wawancara</label>
              <select id="wawancara" name="wawancara" class="select2 self form-control">
                <option value=""> Tanpa Filter </option>
                <option value="1"> Sudah </option>
                <option value="0"> Belum </option>
              </select>
            </div>
            <div class="poskon-filter-field" hidden>
              <label>SP3K</label>
              <select id="sp3k" name="sp3k" class="select2 self form-control">
                <option value=""> Tanpa Filter </option>
                <option value="1"> Sudah </option>
                <option value="0"> Belum </option>
              </select>
            </div>

            <div class="poskon-filter-field hidden" hidden>
              <label>Akad</label>
              <select id="akad" name="akad" class="select2 self form-control">
                <option value=""> Tanpa Filter </option>
                <option value="1"> Sudah </option>
                <option value="0"> Belum </option>
              </select>
            </div>
          </div>
        </div>
        <div class="card poskon-table-card">
          <div class="card-body pb-0 pt-0">
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
          </div>
        </div>
        <div class="card">
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
                      <th colspan="6" id="tb-MARKETING_DATA">MARKETING DATA</th>
                      <th colspan="4" id="tb-KEUANGAN">KEUANGAN</th>
                      <th colspan="4" id="tb-PRODUKSI">PRODUKSI</th>
                      <th colspan="3" id="tb-LEGAL">LEGAL</th>
                      <th id="tb-GA">GA</th>
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
                      <th id="tb-TERBIT">BANK</th>
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
          <div class="col-md-6 mb-1">
            <label>Proyek</label>
            <select id="add_id_proyek" class="select2 form-control"></select>
          </div>
          <div class="col-md-6 mb-1">
            <label>Cluster</label>
            <select disabled id="add_id_cluster" class="select2 form-control"></select>
          </div>
          <div class="col-md-6 mb-1">
            <label>Blok</label>
            <select disabled id="add_id_jalan" class="select2 form-control"></select>
          </div>
          <div class="col-md-6 mb-1">
            <label>Kavling</label>
            <select disabled id="add_id_kavling" class="select2 form-control"></select>
          </div>
          <div class="col-md-6 mb-1">
            <label>Departemen</label>
            <select id="add_id_role" class="form-control"></select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btn_add_open_department" class="btn btn-primary"><i class="fa fa-plus"></i> Lanjut Tambah Data</button>
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
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->

<!-- <script src="<?= base_url() ?>app-assets/vendors/js/bootstrap/extensions/sticky-header/bootstrap-table-sticky-header.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/bootstrap/extensions/fixed-columns/bootstrap-table-fixed-columns.min.js"></script> -->

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
  var has_akses = true;
  var pph = 0;
  var ppn = 0;
  var li_keu = [];
  const roleOptions = {
    4: 'MKDT',
    3: 'Keuangan',
    6: 'Planning',
    7: 'Produksi',
    8: 'Sales',
    5: 'Legal',
    9: 'Direksi',
    10: 'Pajak'
  };

  window.editdtt = [];

  function normalizeProjectContext(item) {
    if (!item) return {};

    return {
      id_proyek: item.id_proyek || item.id || item[0] || '',
      nama_proyek: item.nama_proyek || item.text || item[1] || ''
    };
  }

  function getProjectLogoUrl(item) {
    if (!item) return '';
    if (item.logo_url) return item.logo_url;

    const rawLogo = item.logo_html || item[3] || (item.data && item.data[3]) || '';
    if (!rawLogo) return '';

    return $('<div>').html(rawLogo).find('img').attr('src') || '';
  }

  function createProjectSelectOption(item) {
    return {
      id: item.id_proyek || item.id || item[0] || '',
      text: item.nama_proyek || item.text || item[1] || '',
      id_proyek: item.id_proyek || item.id || item[0] || '',
      nama_proyek: item.nama_proyek || item.text || item[1] || '',
      logo_url: getProjectLogoUrl(item),
      data: item
    };
  }

  function renderProjectSelectOption(item) {
    if (!item.id) return item.text;

    const $option = $('<span class="project-select-option"></span>');
    const logoUrl = getProjectLogoUrl(item);

    if (logoUrl) {
      $('<img>', {
        src: logoUrl,
        alt: ''
      }).appendTo($option);
    }

    $('<span></span>').text(item.nama_proyek || item.text).appendTo($option);

    return $option;
  }

  function setProjectContextFromSelect($select) {
    const selected = $select.select2('data')[0] || {};
    dt_proyek = normalizeProjectContext(selected);
    return dt_proyek;
  }

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
        tipe_rumah: row.tipe_rumah || ''
      }
    };
  }

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

    try {
      table = $('#data_tables').DataTable({
        fnDrawCallback: function() {
          $('[data-toggle="popover"]').popover();
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
        searching: true,
        ordering: false,
        paging: true,
        // "info": true,
        // "autoWidth": false,
        // "responsive": true,
        ajax: {
          url: base_url + 'list-kavling/ambil',
          type: "POST",
          dataType: "json",
          data: {
            [csrfName]: csrfHash
          },
          data: function(data) {
            data[csrfName] = csrfHash
            data.id_proyek = $("#id_proyek").val()
            data.id_cluster = $("#id_cluster").val()
            data.id_jalan = $("#id_jalan").val()
            data.sp3k = $("#sp3k").val()
            data.wawancara = $("#wawancara").val()
            data.akad = $("#akad").val()
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

    //on chnage search
    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        if (!table) return;
        table.search(this.value).draw();
      });

    //select filter for sp3k, wawancara, akad
    $(".self").select2();

    function fillRoleOptions() {
      const $role = $("#add_id_role");
      $role.empty();

      if (roleid == 1) {
        $.each(roleOptions, function(id, label) {
          $role.append(new Option(label, id));
        });
      } else {
        $role.append(new Option(roleOptions[roleid] || rolename || 'Departemen aktif', roleid));
      }

      $role.select2({
        dropdownParent: $("#modal-tambah-poskon"),
        width: '100%'
      });
    }

    function resetAddSelect($select, disabled = true) {
      $select.prop('disabled', disabled);
      $select.val(null).trigger('change.select2');
    }

    fillRoleOptions();

    function initFilterSelect2($el, options) {
      if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
      }
      $el.select2(options);
    }

    //select2 proyek
    initFilterSelect2($("#id_proyek"), {
      placeholder: "Pilih Proyek",
      allowClear: true,
      ajax: {
        url: base_url + "proyek/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return {
            [csrfName]: csrfHash,
            search: params.term
          };
        },
        processResults: function(r) {
          csrfHash = r.token

          let results = [];
          $.each(r.data, function(index, item) {
            results.push(createProjectSelectOption(item));
          });

          return {
            results: results
          };
        },
        cache: true
      },
      templateResult: renderProjectSelectOption,
      templateSelection: renderProjectSelectOption
    });

    //on select proyek
    $("#id_proyek").on("select2:select", function(e) {
      $('#id_cluster').val(null).trigger('change');

      dt_proyek = normalizeProjectContext(e.params.data);

      if (this.value)
        $("#id_cluster").prop("disabled", false)
      else
        $("#id_cluster").prop("disabled", true)
    });

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
            id_proyek: $("#id_proyek").val()
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
            id_proyek: $("#id_proyek").val()
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
      $("#modal-tambah-poskon").modal({
        backdrop: "static",
        keyboard: false
      });
    });

    $("#add_id_proyek").select2({
      dropdownParent: $("#modal-tambah-poskon"),
      placeholder: "Pilih Proyek",
      allowClear: true,
      width: '100%',
      ajax: {
        url: base_url + "proyek/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return {
            [csrfName]: csrfHash,
            search: params.term
          };
        },
        processResults: function(r) {
          csrfHash = r.token

          let results = [];
          $.each(r.data, function(index, item) {
            results.push(createProjectSelectOption(item));
          });

          return {
            results: results
          };
        },
        cache: true
      },
      templateResult: renderProjectSelectOption,
      templateSelection: renderProjectSelectOption
    });

    $("#add_id_proyek").on("select2:select", function() {
      setProjectContextFromSelect($(this));
      resetAddSelect($("#add_id_cluster"), false);
      resetAddSelect($("#add_id_jalan"));
      resetAddSelect($("#add_id_kavling"));
    });

    $("#add_id_proyek").on("select2:clear", function() {
      dt_proyek = {};
      resetAddSelect($("#add_id_cluster"));
      resetAddSelect($("#add_id_jalan"));
      resetAddSelect($("#add_id_kavling"));
    });

    $("#add_id_cluster").select2({
      dropdownParent: $("#modal-tambah-poskon"),
      placeholder: "Pilih Cluster",
      allowClear: true,
      width: '100%',
      ajax: {
        url: base_url + "/cluster/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return {
            [csrfName]: csrfHash,
            search: params.term,
            id_proyek: $("#add_id_proyek").val()
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

    $("#add_id_cluster").on("select2:select", function() {
      resetAddSelect($("#add_id_jalan"), false);
      resetAddSelect($("#add_id_kavling"));
    });

    $("#add_id_cluster").on("select2:clear", function() {
      resetAddSelect($("#add_id_jalan"), !this.value);
      resetAddSelect($("#add_id_kavling"));
    });

    $("#add_id_jalan").select2({
      dropdownParent: $("#modal-tambah-poskon"),
      placeholder: "Pilih Blok",
      allowClear: true,
      width: '100%',
      ajax: {
        url: base_url + "/jalan/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return {
            [csrfName]: csrfHash,
            search: params.term,
            id_cluster: $("#add_id_cluster").val(),
            id_proyek: $("#add_id_proyek").val()
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

    $("#add_id_jalan").on("select2:select", function() {
      resetAddSelect($("#add_id_kavling"), false);
    });

    $("#add_id_jalan").on("select2:clear", function() {
      resetAddSelect($("#add_id_kavling"), !this.value);
    });

    $("#add_id_kavling").select2({
      dropdownParent: $("#modal-tambah-poskon"),
      placeholder: "Pilih Kavling",
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
            id_proyek: $("#add_id_proyek").val(),
            id_cluster: $("#add_id_cluster").val(),
            id_jalan: $("#add_id_jalan").val(),
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

    $("#btn_add_open_department").on("click", function() {
      if (!$("#add_id_proyek").val()) return swal('error', 'Pilih proyek terlebih dahulu');
      if (!$("#add_id_cluster").val()) return swal('error', 'Pilih cluster terlebih dahulu');
      if (!$("#add_id_jalan").val()) return swal('error', 'Pilih blok terlebih dahulu');
      if (!$("#add_id_kavling").val()) return swal('error', 'Pilih kavling terlebih dahulu');

      const selectedRole = $("#add_id_role").val();
      const selectedKavling = $("#add_id_kavling").select2('data')[0] || {};
      setProjectContextFromSelect($("#add_id_proyek"));

      $.ajax({
        url: base_url + "siteplan/get_kavling_by_id",
        type: "post",
        dataType: "json",
        data: {
          [csrfName]: csrfHash,
          id_kavling: $("#add_id_kavling").val()
        },
        beforeSend: function() {
          $("#btn_add_open_department").prop("disabled", true).html("<i class='fa fa-spinner fa-spin'></i> Memuat");
        },
        success: function(res) {
          csrfHash = res.token;
          const row = $.extend({}, selectedKavling, res.data || {});
          const sh = buildKavlingShape(row);
          $("#modal-tambah-poskon").modal("hide");
          openDepartmentModal(selectedRole, sh, 'add');
        },
        error: function() {
          swal('error', 'Terjadi kesalahan saat memuat kavling');
        },
        complete: function() {
          $("#btn_add_open_department").prop("disabled", false).html("<i class='fa fa-plus'></i> Lanjut Tambah Data");
        }
      });
    });

    //on click btn filter
    $("#btn_draw").on("click", function(e) {
      if (table) table.draw();
      load_riwayat();
    })

    $("#btn_export_excel").on('click', function(e) {
      if (!$("#id_proyek").val()) {
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
      if (!$("#id_proyek").val()) {
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

    function export_file(type, $btn) {
      $.ajax({
        type: "post",
        url: base_url + "export/poskon/" + type + "/aktif",
        data: {
          [csrfName]: csrfHash,
          id_proyek: $("#id_proyek").val(),
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
          $a.attr("download", "Konsumen Aktif  Per " + d + ": " + $("#id_proyek").select2('data')[0].text + "." + type);
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

    function load_riwayat() {
      $div = $("#riwayat-here")
      $div.empty()
      $.ajax({
        type: "post",
        url: base_url + "riwayat/poskon/aktif",
        data: {
          [csrfName]: csrfHash,
          id_proyek: $("#id_proyek").val(),
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
            $div.append("<tr><td>" + no++ + "</td><td>" + item.nama_proyek + "</td><td>" + format_datetime(item.export_tgl) + "</td><td>" + item.export_by + "</td><td>" + icon + "</td><td><a href='" + base_url + item.path + item.randomname + "' target='_blank'>Download</a></td></tr>")
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
    $("#poskon-filter .select2-selection__arrow, #modal-tambah-poskon .select2-selection__arrow")
      .css("pointer-events", "none");

  });

  function sum_mktotal() {
    let hj_net = parseFloat(removeComma($("#mk-hargajual_net").val()) || 0)
    let tot = hitung_total()

    $("#mk-hargajual_net").val(hj_net).keyup()

    $("#mk-tgt").val(tot.total_keseluruhan).keyup(); //grand total keseluruhan
    $("#mk-total_tot").val(tot.harus_dibayar).keyup(); //total yang harus dibayar konsumen

  }

  function hitung_total(isForm = false, mkdt = []) {
    let totalum = 0,
      totalbb = 0,
      pengurangan = 0,
      hj = parseFloat(removeComma($("#mk-hargajual").val()) || 0), // 
      diskon_hj = parseFloat(removeComma($("#mk-diskon_harga_jual").val()) || 0),
      hj_net = parseFloat(removeComma($("#mk-hargajual_net").val()) || 0),
      kpr = parseFloat(removeComma($("#mk-kpr").val()) || 0),
      um = parseFloat(removeComma($("#mk-uang_muka").val()) || 0),
      diskon_um = parseFloat(removeComma($("#mk-diskon_uang_muka").val()) || 0),
      badm = parseFloat(removeComma($("#mk-biaya_adm").val()) || 0),
      ppn = parseFloat(removeComma($("#mk-ppn").val()) || 0),
      bphtb = parseFloat(removeComma($("#mk-bphtb").val()) || 0),
      bproses = parseFloat(removeComma($("#mk-biaya_proses").val()) || 0),
      sbum = parseFloat(removeComma($("#mk-harga_sbum").val()) || 0),

      hj_real = 0,
      persentase_kpr = ($("#idk-is_subsidi").val() == 1) ? 0.05 : 0.1, //persentase kpr
      penambahan_biaya = parseFloat(removeComma($("#mk-harga_penambahan").val()) || 0),
      penambahan_biaya_tanah = parseFloat(removeComma($("#mk-harga_penambahan_tanah").val()) || 0),
      is_allin = $("#idk-is_allin").val(),
      harga_allin = parseFloat(removeComma($("#mk-harga_allin").val() || 0))
    if (isForm) {
      if (mkdt.length == 0)
        return showToast('tidak ada data tersedia', 'warning')

      um = parseFloat(mkdt.harga_uang_muka || 0)
      diskon_um = parseFloat(mkdt.harga_diskon_uang_muka || 0)
      badm = parseFloat(mkdt.harga_administrasi || 0)
      ppn = parseFloat(mkdt.harga_ppn || 0)
      bphtb = parseFloat(mkdt.harga_bphtb || 0)
      bproses = parseFloat(mkdt.harga_biaya_proses || 0)
      sbum = parseFloat(mkdt.harga_sbum || 0)
      penambahan_biaya = parseFloat(mkdt.harga_penambahan || 0)
      penambahan_biaya_tanah = parseFloat(mkdt.harga_penambahan_tanah || 0)
      is_allin = parseFloat(mkdt.is_allin || 0)
      harga_allin = parseFloat(mkdt.harga_allin || 0)
    }

    pengurangan = diskon_um + sbum

    totalum = um + badm + penambahan_biaya + penambahan_biaya_tanah
    totalbb = ppn + bphtb + bproses

    let tottot = totalum + totalbb - pengurangan;

    let grandtotal = tottot;
    if (is_allin == "1")
      grandtotal = harga_allin

    return {
      'total_keseluruhan': tottot,
      'harus_dibayar': grandtotal
    }
  }

  function lihat_total() {
    var harga_jual = removeComma(($("#detail_harga_jual").val() == '') ? 0 : $("#detail_harga_jual").val()),
      harga_diskon = removeComma(($("#detail_harga_diskon").val() == '') ? 0 : $("#detail_harga_diskon").val()),
      harga_penambahan = removeComma(($("#detail_harga_penambahan").val() == '') ? 0 : $("#detail_harga_penambahan").val()),
      harga_administrasi = removeComma(($("#detail_harga_administrasi").val() == '') ? 0 : $("#detail_harga_administrasi").val()),
      harga_ppn = removeComma(($("#detail_harga_ppn").val() == '') ? 0 : $("#detail_harga_ppn").val()),
      harga_bphtb = removeComma(($("#detail_harga_bphtb").val() == '') ? 0 : $("#detail_harga_bphtb").val()),
      harga_biaya_proses = removeComma(($("#detail_harga_biaya_proses").val() == '') ? 0 : $("#detail_harga_biaya_proses").val()),
      harga_kpr = removeComma(($("#detail_harga_kpr").val() == '') ? 0 : $("#detail_harga_kpr").val()),
      total_biaya = 0;

    total_biaya = (harga_jual - harga_kpr) - harga_diskon + harga_penambahan + harga_ppn + harga_bphtb + harga_biaya_proses;

    $("#detail_total_biaya").val(total_biaya).keyup();

  }
  //sum tagihan

  function sum_tg(e = 0, bb = '') {
    e = parseFloat(removeComma(e))

    let total_keu = parseFloat(removeComma($("#mk-total_tot").val()) || 0)
    let cicilan_keu = parseFloat(removeComma($("#mk-total_cicilan_um").val()) || 0)

    if (cicilan_keu + e > total_keu)
      $("#nominal").val(total_keu - cicilan_keu).keyup()
  }
  var it = 0;
  /***************** list tagihan ****************/
  function tambah_(e = '') {
    let a = (e == '_bb') ? e : '_um'
    if ($("#mk-total_cicilan_um").val() == $("#mk-total_tot").val()) {
      swal('error', "Tidak bisa menambahkan tagihan", "Total tagihan tidak bisa melebeihi total harus dibayar", false);
      return false;
    } else {
      if (!$("#berita_acara" + e).val() || !$("#nominal" + e).val() || !$("#jatuh_tempo_tgl" + e).val()) {
        swal('error', "Nominal dan jatuh tempo tidak boleh kosong", null, false);
        return false;
      }
      Swal.fire({
        title: 'Simpan data?',
        text: "Pastikan data sudah terisi dengan benar!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya!',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: !1
      }).then(function(t) {
        if (t.value) {
          tambah(e)
        }
      })
    }
  }

  function tambah(e = '') {
    let i = 'lk' + it

    if (state.data_um[$("#id_list_keu" + e).val()])
      i = $("#id_list_keu" + e).val()

    state.data_um[i] = ({
      id_list_keu: i,
      id_keuangan: $("#id_keuangan").val(),
      berita_acara: $("#berita_acara").val(),
      nominal: $("#nominal").val(),
      jatuh_tempo_tgl: $("#jatuh_tempo_tgl").val(),
    })

    tambah_ketagihan(e)

    fp = flatpickr("#jatuh_tempo_tgl", {
      altInput: true,
      altFormat: 'F j, Y',
      dateFormat: 'Y-m-d'
    })

    var d = new Date(
      $("#jatuh_tempo_tgl").val()
    ).fp_incr(30);

    fp.setDate(d);

    it += 1;
  }

  function removeFromTable(x, y = null) {
    Swal.fire({
      title: 'Hapus Data?',
      text: "Data tidak bisa dipulihkan!",
      type: 'danger',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya!',
      confirmButtonClass: 'btn btn-primary',
      cancelButtonClass: 'btn btn-danger ml-1',
      buttonsStyling: !1
    }).then(function(t) {
      if (t.value) {
        $.ajax({
          url: base_url + 'Keuangan/isSudahBayar/' + editdtt[0].data.id_mkdt,
          type: 'get',
          dataType: 'json',
          success: function(r) {
            csrfHash = r.token;

            if (r.success === false) {
              return swal('error', r.messages)
            }

            if (y == '_bb') delete state.data_bb[x];
            else delete state.data_um[x];
            tambah_ketagihan();
          },
          error: function() {
            return swal('error', 'Terjadi kesalahan')
          }
        });

      }
    })

  }

  function editFromTable(x) {
    var d = state.data_um[x]

    $("#id_list_keu").val(x);
    $("#berita_acara").val(d.berita_acara);
    $("#nominal").val(d.nominal).keyup();
    $("#jatuh_tempo_tgl").val(d.jatuh_tempo_tgl);
    $("#tambah_list").html("Simpan Perubahan")
  }


  function rowHTML({
    title,
    date,
    amount,
    key,
    suffix = ''
  }) {
    return `
            <tr data-key="${key}" data-suffix="${suffix}">
            <td>${title}</td>
            <td>${format_date(date)}</td>
            <td>${num_format(amount)}</td>
            <td>
                <div class="btn-group">
                <button type="button" class="btn btn-outline-danger waves-effect btn-sm js-remove">
                    <i class="fa fa-trash"></i>
                </button>
                </div>
            </td>
            </tr>`;
  }

  function sectionHTML({
    rows,
    label,
    suffix = ''
  }) {
    let total = 0;
    const body = rows.map(r => {
      total += Number(removeComma(r.amount));
      return rowHTML({
        ...r,
        suffix
      });
    }).join('');
    const foot = `
                    <tr class="table-secondary">
                        <td colspan="2">Total Tagihan </td>
                        <td>${num_format(total)}</td>
                        <td></td>
                    </tr>`;
    return {
      html: body + foot,
      total
    };
  }

  function tambah_ketagihan() {
    const umRows = Object.keys(state.data_um || {}).map(k => ({
      key: k,
      title: state.data_um[k].berita_acara,
      date: state.data_um[k].jatuh_tempo_tgl,
      amount: state.data_um[k].nominal
    }));

    // const bbRows = Object.keys(state.data_bb || {}).map(k => ({
    //     key: k,
    //     title: state.data_bb[k].berita_acara_bb,
    //     date: state.data_bb[k].jatuh_tempo_tgl_bb,
    //     amount: state.data_bb[k].nominal_bb
    // }));

    const um = sectionHTML({
      rows: umRows,
      label: 'Tagihan Uang Muka',
      suffix: ''
    });

    // const bb = sectionHTML({
    //     rows: bbRows,
    //     label: 'Tagihan Biaya Biaya',
    //     suffix: '_bb'
    // });

    // 1x write ke DOM
    $("#list_cicilan_here").html(um.html);

    // update total & UI state
    $("#mk-total_cicilan_um").val(um.total).trigger('change');
    // $("#total_cicilan_bb").val(bb.total).trigger('change');
    $("#id_list_keu").val('');
    $("#id_list_keu_bb").val('');
    $("#nominal, #nominal_bb").trigger('change');
    // $("#tambah_list").text("+ Cicilan UM");
    // $("#tambah_list_bb").text("+ Cicilan BB");
  }



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
?>
