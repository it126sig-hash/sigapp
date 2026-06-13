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
<div class="app-content content ">
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
              <label>Proyek</label>
              <select id="id_proyek" name="id_proyek" class="select2 form-control"></select>
            </div>
            <div class="col-md-4 mb-1">
              <label>Cluster</label>
              <select disabled id="id_cluster" name="id_cluster" class="select2  form-control"></select>
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
          <div class="card-datatable">
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
  $(function() {
    var table = $('#data_table').DataTable({
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
        url: base_url + '/tagihan/riwayat-bayar/ambil',
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

    //select2 proyek
    $("#id_proyek").select2({
      placeholder: "Pilih Proyek",
      allowClear: true,
      ajax: {
        url: base_url + "/proyek/getAll",
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
            results.push({
              id: item[0],
              text: item[1] + ' (' + item[2] + ')'
            });
          });

          return {
            results: results
          };
        },
        cache: true
      },
    })

    //on select proyek
    $("#id_proyek").on("change", function(e) {
      $('#id_cluster').val(null).trigger('change');

      if (this.value)
        $("#id_cluster").prop("disabled", false)
      else
        $("#id_cluster").prop("disabled", true)
    });

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
    })

    //on click btn filter
    $("#btn_draw").on("click", function(e) {
      table.draw();
    })

    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none")

  });

  $('thead > tr> th:nth-child(1)').css({
    'min-width': '50px',
    'max-width': '50px'
  });
  $('thead > tr> th:nth-child(2)').css({
    'min-width': '100px',
    'max-width': '100px'
  });
  $('thead > tr> th:nth-child(3)').css({
    'min-width': '125px',
    'max-width': '125px'
  });
  $('thead > tr> th:nth-child(4)').css({
    'min-width': '250px',
    'max-width': '250px'
  });
  $('thead > tr> th:nth-child(5)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(6)').css({
    'min-width': '125px',
    'max-width': '125px'
  });
</script>