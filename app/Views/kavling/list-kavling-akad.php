<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/fixed-columns/fixedColumns.bootstrap4.css">

<!--
<link rel="stylesheet" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/sticky-header/bootstrap-table-sticky-header.min.css">
<link rel="stylesheet" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/fixed-columns/bootstrap-table-fixed-columns.min.css"> -->

<script>
  // var csrfName = '<?= csrf_token() ?>';
  // var csrfHash = '<?= csrf_hash() ?>';
</script>

<style>
  table,
  tr {
    vertical-align: middle !important;
    text-align: center !important;
    font-size: 10px;
  }
</style>
<!-- /.card-header -->
<div class="app-content content ">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <h2 class="card-header">
            Posisi Konsumen Akad
          </h2>
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
            <hr class="col-12" hidden />
            <div class="col-md-4 mb-1" hidden>
              <label>Wawancara</label>
              <select id="wawancara" name="wawancara" class="select2 self form-control">
                <option value=""> Tanpa Filter </option>
                <option value="1"> Sudah </option>
                <option value="0"> Belum </option>
              </select>
            </div>
            <div class="col-md-4 mb-1" hidden>
              <label>SP3K</label>
              <select id="sp3k" name="sp3k" class="select2 self form-control">
                <option value=""> Tanpa Filter </option>
                <option value="1"> Sudah </option>
                <option value="0"> Belum </option>
              </select>
            </div>

            <div class="col-md-4 mb-1"></div>
            <div class="col-md-4 mb-1 hidden">
              <label>Akad</label>
              <select id="akad" name="akad" class="select2 self form-control">
                <option value=""> Tanpa Filter </option>
                <option value="1"> Sudah </option>
                <option value="0"> Belum </option>
              </select>
            </div>
            <hr class="col-12" />
            <button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect btn-sm">Filter Data</button>
            <div class="btn-group">
              <button type="button" id="btn_export_excel" class="btn btn-success waves-effect btn-sm"><i class="fa fa-file-excel"></i> Export Excel</button>
              <button type="button" id="btn_export_pdf" class="btn btn-danger waves-effect btn-sm"><i class="fa fa-file-pdf"></i> Export PDF</button>
            </div>

          </div>
        </div>
        <div class="card">
          <div class="card-body pb-0 pt-0">
            <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="list_poskon-tab"
                  data-toggle="tab" href="#list_poskon"
                  aria-controls="list_poskon" role="tab" aria-selected="true">List Posisi Konsumen Akad</a>
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
                      <th rowspan="3" id="tb-action"></th>
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
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->

<!-- <script src="<?= base_url() ?>app-assets/vendors/js/bootstrap/extensions/sticky-header/bootstrap-table-sticky-header.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/bootstrap/extensions/fixed-columns/bootstrap-table-fixed-columns.min.js"></script> -->

<script>
  $(function() {
    var table = $('#data_tables').DataTable({
      fnDrawCallback: function() {
        $('[data-toggle="popover"]').popover();
      },
      scrollY: "50vh",
      scrollX: true,
      scrollCollapse: true,
      fixedColumns: true,
      fixedColumns: {
        leftColumns: 5
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
        url: base_url + 'list-kavling/akad/ambil',
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

    //on chnage search
    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        table.search(this.value).draw();
      });

    //select filter for sp3k, wawancara, akad
    $(".self").select2();

    //select2 proyek
    $("#id_proyek").select2({
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
        url: base_url + "export/poskon/" + type + "/akad",
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
          $a.attr("download", "Konsumen Akad  Per " + d + ": " + $("#id_proyek").select2('data')[0].text + "." + type);
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
        url: base_url + "riwayat/poskon/akad",
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
    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none")

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