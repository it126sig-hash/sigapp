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
          <div class="card-header border-bottom">
            <div class="col-md-4 mb-1">
              <label>Cluster</label>
              <select disabled id="id_cluster" name="id_cluster" class="select2  form-control"></select>
            </div>
            <div class="col-md-4 mb-1">
              <label>Blok</label>
              <select disabled id="id_jalan" name="id_jalan" class="select2 form-control"></select>
            </div>
            <!-- <div class="col-md-4 mb-1">
              <label>Wawancara</label>
              <select id="wawancara" name="wawancara" class="select2 self form-control">
                <option value=""> Tanpa Filter </option>
                <option value="1"> Sudah </option>
                <option value="0"> Belum </option>
              </select>
            </div>
            <div class="col-md-4 mb-1">
              <label>SP3K</label>
              <select id="sp3k" name="sp3k" class="select2 self form-control">
                <option value=""> Tanpa Filter </option>
                <option value="1"> Sudah </option>
                <option value="0"> Belum </option>
              </select>
            </div>

            <div class="col-md-4 mb-1">
              <label>Akad</label>
              <select id="akad" name="akad" class="select2 self form-control">
                <option value=""> Tanpa Filter </option>
                <option value="1"> Sudah </option>
                <option value="0"> Belum </option>
              </select>
            </div> -->
            <hr class="col-12" />
            <button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect btn-sm">Filter Data</button>

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
          data[csrfName] = csrfHash
          data.id_proyek = activeProyekId()
          data.id_cluster = $("#id_cluster").val()
          data.id_jalan = $("#id_jalan").val()
          data.sp3k = ""
          data.wawancara = ""
          data.akad = ""

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

    //on click btn filter
    $("#btn_draw").on("click", function(e) {
      table.draw();
    })

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