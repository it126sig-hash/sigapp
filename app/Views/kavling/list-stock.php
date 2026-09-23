<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/3.2.1/css/fixedColumns.dataTables.min.css"/> -->

<script>
  // var csrfName = '<?= csrf_token() ?>';
  // var csrfHash = '<?= csrf_hash() ?>';
</script>
<!-- /.card-header -->
<div class="app-content content ">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <h5 class="card-header">
            <?=$data['title']?>
          </h5>
          <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <button type="button" id="btn_open_filter" class="btn btn-outline-primary waves-effect btn-sm">
              <i class="fa fa-filter"></i> Filter Data
            </button>
          </div>
          <div class="poskon-active-filters" id="poskon-active-filters" hidden>
            <span class="poskon-active-filters-label">Filter Aktif:</span>
            <div class="poskon-active-filters-chips" id="poskon-active-filters-chips"></div>
            <a href="javascript:void(0)" id="btn_filter_clear_all" class="poskon-active-filters-clear">Bersihkan</a>
          </div>
        </div>
        <div class="card">
          <div class="card-datatable">
            <table id="data_table" class="datatables-basic table compact">
              <thead>
                <tr>
                  <th>Aksi</th>
                  <th>Nama Jalan</th>
                  <th>No Kavling</th>
                  <th>Tipe Rumah</th>
                  <th>Tanggal Pembangunan</th>
                  <th>Tanggal Selesai Pembangunan</th>
                  <th>Terakhir Diperbarui</th>
                </tr>
              </thead>
            </table>

          </div>
        </div>
      </div>
    </div>
    <!-- Add modal content -->
    <div class="modal modal-slide-in fade" id="add-modal">
      <div class="modal-dialog sidebar-sm">
        <form id="add-form" class="add-new-record modal-content pt-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
          <div class="modal-header mb-1">
            <h5 class="modal-title" id="exampleModalLabel">Tambah Data</h5>
          </div>
          <div class="modal-body flex-grow-1">
            <div class="row">
              <input type="hidden" id="idGroup" name="idGroup" class="form-control" placeholder="Id group" maxlength="11" required>
            </div>
            <div class="form-group">
              <label for="namaGroup"> Nama group: <span class="text-danger">*</span> </label>
              <input type="text" id="namaGroup" name="namaGroup" class="form-control" placeholder="Nama group" maxlength="50" required>
            </div>
            <button type="submit" class="btn btn-primary data-submit mr-1" id="add-form-btn">Simpan</button>
            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Add modal content -->
    <div class="modal modal-slide-in fade" id="edit-modal">
      <div class="modal-dialog sidebar-sm">
        <form id="edit-form" class="add-new-record modal-content pt-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
          <div class="modal-header mb-1">
            <h5 class="modal-title" id="exampleModalLabel">Tambah Data</h5>
          </div>
          <div class="modal-body flex-grow-1">
            <div class="row">
              <input type="hidden" id="idGroup" name="idGroup" class="form-control" placeholder="Id group" maxlength="11" required>
            </div>
            <div>
              <div class="form-group">
                <label for="namaGroup"> Nama group: <span class="text-danger">*</span> </label>
                <input type="text" id="namaGroup" name="namaGroup" class="form-control" placeholder="Nama group" maxlength="50" required>
              </div>
            </div>
            <button type="submit" class="btn btn-primary data-submit mr-1" id="add-form-btn">Simpan</button>
            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
          </div>
        </form>
      </div>
    </div>
    <div class="modal modal-slide-in fade" id="modal-filter-lanjutan">
      <div class="modal-dialog sidebar-sm">
        <div class="add-new-record modal-content pt-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
          <div class="modal-header mb-1">
            <h5 class="modal-title"><i class="fa fa-filter"></i> Filter Lanjutan</h5>
          </div>
          <div class="modal-body flex-grow-1">
            <div class="form-group">
              <label><i class="fa fa-map-marker-alt"></i> Cluster</label>
              <select disabled id="id_cluster" name="id_cluster" class="select2 form-control"></select>
            </div>
            <div class="form-group">
              <label><i class="fa fa-road"></i> Blok / Jalan</label>
              <select disabled id="id_jalan" name="id_jalan" class="select2 form-control"></select>
            </div>
            <div class="form-group">
              <label><i class="fa fa-calendar-alt"></i> Periode Tgl Pembangunan</label>
              <input type="text" id="filter_tgl_pembangunan" class="form-control flatpickr-range" placeholder="Pilih rentang tanggal" />
            </div>
            <div class="form-group">
              <label><i class="fa fa-calendar-check"></i> Periode Tgl Selesai</label>
              <input type="text" id="filter_tgl_selesai" class="form-control flatpickr-range" placeholder="Pilih rentang tanggal" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="btn_filter_reset" class="btn btn-outline-secondary"><i class="fa fa-undo"></i> Reset</button>
            <button type="button" id="btn_filter_apply" class="btn btn-primary"><i class="fa fa-check"></i> Terapkan</button>
          </div>
        </div>
      </div>
    </div>
  </section>
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
<!-- <script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.1/js/dataTables.fixedColumns.min.js"></script> -->
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->
<style>
  .poskon-action-cell .dropdown-menu {
    max-height: 280px;
    overflow-y: auto;
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
</style>

<?php
$k = null;
$v = null;
$roles = user()->getRoles();
if (!empty($roles)) {
  foreach ($roles as $key => $val) {
    $k = $key;
    $v = $val;
    break;
  }
}
?>
<script>
  var roleid = "<?= $k; ?>";
  var rolename = "<?= $v; ?>";
  let dt_proyek = [];
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
    dt_proyek = $.extend({}, window.SIGAPP?.activeProyekName || {}, dt_proyek || {}, {
      id_proyek: typeof activeProyekId === 'function' ? activeProyekId() : '',
      nama_proyek: window.SIGAPP?.activeProyekName || '',
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

    syncProjectContextFromRow(row);
    const sh = buildKavlingShape(row);
    window.editdtt = [sh];
    $('.id_kavling').val(row.id_kavling || '');

    if (String(onclick).trim() === 'isi_data()') {
      const itemGroup = menuItem && menuItem.id_group ? parseInt(menuItem.id_group, 10) : 0;
      const targetRole = itemGroup > 0 ? itemGroup : parseInt(roleid, 10);
      if (typeof openDepartmentModal === 'function') return openDepartmentModal(targetRole, sh, 'edit');
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


  $(function() {
    loadSiteplanMenuItems();
    var table = $('#data_table').DataTable({
      fnDrawCallback: function() {
        $('[data-toggle="popover"]').popover();
      },
      columns: [
        { data: null, render: function(data, type, row) { return renderPoskonActionCell(row); } },
        { data: 'nama_jalan' },
        { data: 'no_kavling' },
        { data: 'tipe_rumah' },
        { data: 'tanggal_pembangunan_formatted' },
        { data: 'tanggal_selesai_pembangunan_formatted' },
        { data: 'terakhir_diperbarui' }
      ],
      columnDefs: [{
        'targets': [1,2,3],
        'createdCell': function(td, cellData, rowData, row, col) {
          $(td).attr('data-toggle', 'popover');
          $(td).attr('data-placement', 'top');
          $(td).attr('data-content', rowData.nama_jalan + " No. " + rowData.no_kavling);
          $(td).attr('data-trigger', 'hover');
        }
      }],
      scrollY: "50vh",
      scrollX: true,
      scrollCollapse: true,
      fixedColumns: true,
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
        url: base_url + '/mkdt/getListStock',
        type: "POST",
        dataType: "json",
        data: {
          [csrfName]: csrfHash
        },
        data: function(data) {
          data[csrfName] = csrfHash;
          data.id_proyek = activeProyekId();
          data.id_cluster = $("#id_cluster").val();
          data.id_jalan = $("#id_jalan").val();
          data.tgl_pembangunan = $("#filter_tgl_pembangunan").val();
          data.tgl_selesai = $("#filter_tgl_selesai").val();
        },
        dataSrc: function(r) {
          csrfHash = r.token
          return r.data;
        },
        async: "true"
      }
    });

    //on chnage search
    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        table.search(this.value).draw();
      });

    //select filter for sp3k, wawancara, akad
    $(".self").select2();

    if (activeProyekId()) {
      $("#id_cluster").prop("disabled", false);
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

    // Initialize flatpickr range
    $(".flatpickr-range").flatpickr({
      mode: "range",
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "j M Y"
    });

    // Handle filter modal open
    $("#btn_open_filter").on("click", function() {
      $("#modal-filter-lanjutan").modal("show");
    });

    function applyFilters() {
      table.draw();
      renderActiveFilterChips();
    }

    function resetFilterFields() {
      $("#id_cluster").val(null).trigger('change');
      $("#filter_tgl_pembangunan").val('');
      $("#filter_tgl_selesai").val('');
      if ($("#filter_tgl_pembangunan")[0]._flatpickr) {
        $("#filter_tgl_pembangunan")[0]._flatpickr.clear();
      }
      if ($("#filter_tgl_selesai")[0]._flatpickr) {
        $("#filter_tgl_selesai")[0]._flatpickr.clear();
      }
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

      var tglPembangunan = $("#filter_tgl_pembangunan").val();
      if (tglPembangunan) addChip('tgl_pembangunan', 'Tgl Pembangunan: ' + tglPembangunan);

      var tglSelesai = $("#filter_tgl_selesai").val();
      if (tglSelesai) addChip('tgl_selesai', 'Tgl Selesai: ' + tglSelesai);

      $("#poskon-active-filters-chips").html(chips.join(''));
      $("#poskon-active-filters").prop('hidden', chips.length === 0);
    }

    $("#btn_filter_apply").on("click", function() {
      applyFilters();
      $("#modal-filter-lanjutan").modal("hide");
    });

    $("#btn_filter_reset").on("click", function() {
      resetFilterFields();
    });

    $(document).on('click', '[data-remove-filter]', function() {
      var key = $(this).data('remove-filter');
      if (key === 'id_cluster') {
        $("#id_cluster").val(null).trigger('change');
      } else if (key === 'id_jalan') {
        $("#id_jalan").val(null).trigger('change');
      } else if (key === 'tgl_pembangunan') {
        $("#filter_tgl_pembangunan").val('');
        if ($("#filter_tgl_pembangunan")[0]._flatpickr) {
          $("#filter_tgl_pembangunan")[0]._flatpickr.clear();
        }
      } else if (key === 'tgl_selesai') {
        $("#filter_tgl_selesai").val('');
        if ($("#filter_tgl_selesai")[0]._flatpickr) {
          $("#filter_tgl_selesai")[0]._flatpickr.clear();
        }
      }
      applyFilters();
    });

    $("#btn_filter_clear_all").on('click', function() {
      resetFilterFields();
      applyFilters();
    });

    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none")

  });
 
  $('thead > tr> th:nth-child(1)').css({ 'min-width': '120px' });
  $('thead > tr> th:nth-child(2)').css({ 'min-width': '200px' });
  $('thead > tr> th:nth-child(3)').css({ 'min-width': '80px' });
  $('thead > tr> th:nth-child(4)').css({ 'min-width': '80px' });
  $('thead > tr> th:nth-child(5)').css({ 'min-width': '120px' });
  $('thead > tr> th:nth-child(6)').css({ 'min-width': '120px' });
  $('thead > tr> th:nth-child(7)').css({ 'min-width': '150px' });
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
