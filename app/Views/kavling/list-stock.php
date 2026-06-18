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
                  <th>No</th>
                  <th>Blok</th>
                  <th>No</th>
                  <th>Tipe</th>
                  <th>Progres Bangunan</th>
                  <th>Nama Konsumen</th>
                  <th>Kontak</th>
                  <th>Keterangan Batal</th>

                  <th>Created At</th>
                  <th>Created By</th>
                  <th>Updated At</th>
                  <th>Updated By</th>

                  <th></th>
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
      fnDrawCallback: function() {
        $('[data-toggle="popover"]').popover();
      },
      columnDefs: [{
        'targets': [2,3,4],
        'createdCell': function(td, cellData, rowData, row, col) {
          $(td).attr('data-toggle', 'popover');
          $(td).attr('data-placement', 'top');
          $(td).attr('data-content', rowData[1] + " No. " + rowData[2]);
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
 
  $('thead > tr> th:nth-child(1)').css({
    'min-width': '50px',
    'max-width': '50px'
  });
  $('thead > tr> th:nth-child(2)').css({
    'min-width': '200px',
    'max-width': '200px'
  });
  $('thead > tr> th:nth-child(3)').css({
    'min-width': '50px',
    'max-width': '50px'
  });
  $('thead > tr> th:nth-child(4)').css({
    'min-width': '50px',
    'max-width': '50px'
  });
  $('thead > tr> th:nth-child(5)').css({
    'min-width': '80px',
    'max-width': '80px'
  });
  $('thead > tr> th:nth-child(6)').css({
    'min-width': '250px',
    'max-width': '2500px'
  });
  $('thead > tr> th:nth-child(7)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(8)').css({
    'min-width': '80px',
    'max-width': '80px'
  });
  $('thead > tr> th:nth-child(9)').css({
    'min-width': '250px',
    'max-width': '250px'
  });
  $('thead > tr> th:nth-child(10)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(11)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(12)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(13)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(14)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(15)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(16)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(17)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(18)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(19)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(20)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(21)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(22)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(23)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(24)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
  $('thead > tr> th:nth-child(25)').css({
    'min-width': '150px',
    'max-width': '150px'
  });
</script>