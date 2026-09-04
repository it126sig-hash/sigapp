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
            Posisi Konsumen Batal
          </h2>
          <div class="card-header border-bottom">
            <div class="d-flex align-items-center">
              <button type="button" class="btn btn-outline-primary waves-effect btn-sm" data-toggle="modal" data-target="#filterModal">
                <i class="fa fa-filter"></i> Filter
              </button>
              <button type="button" id="btn_clear_filter" class="btn btn-outline-danger waves-effect btn-sm ml-1">
                <i class="fa fa-times"></i> Clear Filter
              </button>
              <span id="active_filter_text" class="ml-1 text-muted">Tanpa Filter</span>
            </div>
            <div class="btn-group">
              <button type="button" id="btn_export_pdf" class="btn btn-danger waves-effect btn-sm"><i class="fa fa-file-pdf"></i> Export PDF</button>
            </div>
          </div>

          <!-- Modal Slide In -->
          <div class="modal modal-slide-in fade" id="filterModal">
            <div class="modal-dialog sidebar-sm">
              <form class="modal-content pt-0">
                <div class="modal-header mb-1">
                  <h5 class="modal-title" id="exampleModalLabel"><i class="fa fa-filter mr-1"></i>Filter Data</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                  <div class="form-group">
                    <label>Cluster</label>
                    <select disabled id="id_cluster" name="id_cluster" class="select2 form-control"></select>
                  </div>
                  <div class="form-group">
                    <label>Blok</label>
                    <select disabled id="id_jalan" name="id_jalan" class="select2 form-control"></select>
                  </div>
                  <div class="form-group">
                    <label>Periode Tanggal Booking</label>
                    <input type="text" id="periode_booking" class="form-control flatpickr-range" placeholder="Pilih Periode">
                  </div>
                  <div class="form-group">
                    <label>Periode Tanggal Batal</label>
                    <input type="text" id="periode_batal" class="form-control flatpickr-range" placeholder="Pilih Periode">
                  </div>
                  <button type="button" id="btn_draw_modal" class="btn btn-primary mb-1 d-grid w-100">Apply Filter</button>
                  <button type="button" id="btn_clear_filter_modal" class="btn btn-outline-danger d-grid w-100">Clear Filter</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-body pb-0 pt-0">
            <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="list_poskon-tab"
                  data-toggle="tab" href="#list_poskon"
                  aria-controls="list_poskon" role="tab" aria-selected="true">List Posisi Konsumen Batal</a>
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
                      <th rowspan="2" id="tb-NO">NO</th>
                      <th colspan="2" id="tb-KAVLING">KAVLING</th>
                      <th rowspan="2" id="tb-TYPE">TYPE</th>
                      <th rowspan="2" id="tb-KET_BATAL">Keterangan Batal</th>
                      <th rowspan="2" id="tb-TGL_BATAL">Tanggal Batal & Oleh</th>
                      <th rowspan="2" id="tb-STATUS_REFUND">Status Refund</th>
                      <th rowspan="2">Nama Konsumen</th>
                      <th rowspan="2">Tanggal Booking</th>
                      <th rowspan="2">TUNAI/KPR</th>
                      <th rowspan="2" id="tb-TOTAL_TAGIHAN">TOTAL TAGIHAN</th>
                      <th rowspan="2" id="tb-SUDAH_BAYAR">SUDAH BAYAR</th>
                      <th rowspan="2" id="tb-SISA_TAGIHAN">SISA TAGIHAN</th>
                    </tr>
                    <tr>
                      <th id="tb-BLOK">BLOK</th>
                      <th id="tb-NO_KAVLING">NO</th>
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
        leftColumns: 6
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
        url: base_url + 'list-kavling/batal/ambil',
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
          data.periode_booking = $("#periode_booking").val()
          data.periode_batal = $("#periode_batal").val()
        },
        dataSrc: function(r) {
          csrfHash = r.token
          return r.data;
        },
        async: "true"
      }
    });

    // Init flatpickr
    $('.flatpickr-range').flatpickr({
      mode: 'range',
      dateFormat: 'Y-m-d'
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

    function updateFilterText() {
      let text = [];
      if ($('#id_cluster').val()) text.push('Cluster: ' + $('#id_cluster option:selected').text());
      if ($('#id_jalan').val()) text.push('Blok: ' + $('#id_jalan option:selected').text());
      if ($('#periode_booking').val()) text.push('Booking: ' + $('#periode_booking').val());
      if ($('#periode_batal').val()) text.push('Batal: ' + $('#periode_batal').val());
      
      if (text.length > 0) {
        $('#active_filter_text').html('Filter aktif: ' + text.join(' | '));
      } else {
        $('#active_filter_text').html('Tanpa Filter');
      }
    }

    //on click apply filter
    $("#btn_draw_modal").on("click", function(e) {
      table.draw();
      load_riwayat();
      updateFilterText();
      $('#filterModal').modal('hide');
    })

    // clear filter
    $("#btn_clear_filter, #btn_clear_filter_modal").on("click", function(e) {
      $('#id_cluster').val(null).trigger('change');
      $('#id_jalan').val(null).trigger('change');
      $('#periode_booking').val('');
      $('#periode_batal').val('');
      
      // Update text and table
      updateFilterText();
      table.draw();
      load_riwayat();
      
      // If triggered from modal
      $('#filterModal').modal('hide');
    })

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

    function export_file(type, $btn) {
      $.ajax({
        type: "post",
        url: base_url + "export/poskon/" + type + "/batal",
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
          $a.attr("download", "Konsumen Batal Per " + d + ": " + (window.SIGAPP.activeProyekName || "Proyek") + "." + type);
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
        url: base_url + "riwayat/poskon/batal",
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
  $('#tb-KET_BATAL').css({
    'min-width': '250px',
    'max-width': '250px'
  });
</script>
