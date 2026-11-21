<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">
<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/3.2.1/css/fixedColumns.dataTables.min.css"/> -->

<script>
  var csrfName = '<?= csrf_token() ?>'
  var csrfHash = '<?= csrf_hash() ?>'
</script>
<!-- /.card-header -->
<div class="app-content content riwayat-bayar-page">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <h5 class="card-header">
            <?= $data['title'] ?>
          </h5>
          <div class="card-header border-bottom">

            <div class="col-md-4 mb-1">
              <label>Cluster</label>
              <select disabled id="id_cluster" name="id_cluster" class="select2 form-control"></select>
            </div>
            <div class="col-md-4 mb-1">
              <label>Blok</label>
              <select disabled id="id_jalan" name="id_jalan" class="select2 form-control"></select>
            </div>
            <hr class="col-12" />
            <button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect btn-sm">Filter Data</button>

          </div>
        </div>
        <div class="card">
          <div class="card-datatable table-responsive">
            <table id="data_table" class="datatables-basic table compact">
              <thead>
                <tr>
                  <th>No</th>

                  <th>Nominal</th>
                  <th>Tanggal Bayar</th>
                  <th>Keterangan</th>

                  <th>Oleh</th>
                  <th>Pada</th>
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
<script>
  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  $(function() {
    var table = $('#data_table').DataTable({
      scrollY: "50vh",
      scrollX: true,
      scrollCollapse: true,
      autoWidth: false,
      processing: true,
      serverSide: true,
      lengthChange: true,
      searching: true,
      ordering: false,
      paging: true,
      ajax: {
        url: base_url + '/tagihan/riwayat-bayar/ambil',
        type: "POST",
        dataType: "json",
        data: function(data) {
          data[csrfName] = csrfHash;
          data.id_proyek = activeProyekId();
          data.id_cluster = $("#id_cluster").val();
          data.id_jalan = $("#id_jalan").val();
        },
        dataSrc: function(r) {
          if (r && r.token) {
            csrfHash = r.token;
          }
          return (r && Array.isArray(r.data)) ? r.data : [];
        }
      },
      columns: [
        {
          data: null,
          className: "text-center",
          width: "50px",
          orderable: false,
          searchable: false,
          render: function(data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          }
        },
        {
          data: "nominal",
          className: "text-right",
          width: "140px",
          render: function(data) {
            var val = (data !== null && data !== undefined && data !== '') ? Number(data) : 0;
            var formatted = !isNaN(val) ? num_format(val) : escapeHtml(data);
            return '<span class="font-weight-bolder text-primary text-nowrap">Rp ' + escapeHtml(formatted) + '</span>';
          }
        },
        {
          data: "tanggal_bayar",
          className: "text-center",
          width: "130px",
          render: function(data) {
            if (!data) return '-';
            var datePart = String(data).split(' ')[0];
            return '<span class="font-weight-bold text-nowrap">' + escapeHtml(format_date(datePart) || datePart) + '</span>';
          }
        },
        {
          data: "keterangan",
          className: "text-left",
          width: "280px",
          render: function(data, type, row) {
            var parts = [];
            if (row.nama_konsumen && row.nama_konsumen !== '-') {
              parts.push('<div class="font-weight-bold text-dark">' + escapeHtml(row.nama_konsumen) + '</div>');
            }
            if ((row.nama_jalan && row.nama_jalan !== '-') || (row.no_kavling && row.no_kavling !== '-')) {
              parts.push('<div class="small text-muted">' + escapeHtml(row.nama_jalan || '-') + ' &bull; No. ' + escapeHtml(row.no_kavling || '-') + '</div>');
            }
            if (row.payment_type) {
              parts.push('<span class="badge badge-light-secondary font-weight-bold mt-25 mr-25">' + escapeHtml(row.payment_type) + '</span>');
            }
            if (row.detail_items) {
              parts.push('<div class="small text-muted mt-25">' + escapeHtml(row.detail_items) + '</div>');
            }
            if (data && data !== row.payment_type) {
              parts.push('<div class="small text-muted font-italic mt-25">' + escapeHtml(data) + '</div>');
            }
            return parts.length ? parts.join('') : '-';
          }
        },
        {
          data: "username",
          className: "text-left",
          width: "130px",
          render: function(data) {
            return data ? '<span class="font-weight-bold text-uppercase">' + escapeHtml(data) + '</span>' : '-';
          }
        },
        {
          data: "created_at",
          className: "text-center",
          width: "150px",
          render: function(data) {
            if (!data) return '-';
            var str = String(data);
            var formatted = str.indexOf(' ') !== -1 ? format_datetime(str) : format_date(str);
            return '<span class="text-nowrap">' + escapeHtml(formatted || str) + '</span>';
          }
        }
      ]
    });

    //on chnage search
    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        table.search(this.value).draw();
      });

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
          if (r && r.token) {
            csrfHash = r.token;
          }

          let results = [];
          $.each((r && r.data) || [], function(index, item) {
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
        $("#id_jalan").prop("disabled", false);
      else
        $("#id_jalan").prop("disabled", true);
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
          if (r && r.token) {
            csrfHash = r.token;
          }

          let results = [];
          $.each((r && r.data) || [], function(index, item) {
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

    //on click btn filter
    $("#btn_draw").on("click", function(e) {
      table.draw();
    });

    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none");
  });
</script>