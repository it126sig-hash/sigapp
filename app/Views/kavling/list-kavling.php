<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/fixed-columns/fixedColumns.bootstrap4.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/richtext.min.css">
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
          <h2 class="card-header">
            Posisi Konsumen Aktif
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

  window.editdtt = [];

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

    // Construct fake Konva shape object
    window.editdtt = [{
      id: "kav" + row.id_kavling,
      data: {
        tipe: "kavling", // required by mkdt validator
        id_mkdt: row.id_mkdt,
        id_keuangan: row.id_keuangan,
        id_legal: row.id_legal,
        id_produksi: row.id_produksi,
        nama_jalan: row.nama_jalan,
        no_kavling: row.no_kavling
      },
      data2: {
        harga_akhir: row.harga_akhir ?? "-",
        id_hargajual: row.id_hargajual ?? "-",
        id_komplain: row.id_komplain ?? null,
        no_tipe_rumah: row.no_tipe_rumah,
        tipe_rumah: row.tipe_rumah
      }
    }];

    let sh = window.editdtt[0];

    // Debug:
    console.log("Membuka Modal dengan Mock editdtt:", window.editdtt);

    // Triggers based on role
    switch (parseInt(roleid)) {
      case 6: // Planning
      case 1:
        if (typeof open_planning === 'function') open_planning(sh, roleid, row.id_kavling);
        break;
      case 7: // Produksi
        if (typeof open_produksi === 'function') open_produksi(sh, roleid, row.id_kavling);
        break;
      case 8: // Sales
        if (typeof open_sales === 'function') open_sales(sh, roleid, row.id_kavling);
        break;
      case 5: // Legal
        if (typeof open_legal === 'function') open_legal(sh, roleid, row.id_kavling);
        break;
      case 4: // MKDT
        if (typeof isi_data_konsumen === 'function') isi_data_konsumen();
        break;
      case 9: // Direksi
        if (typeof open_direksi === 'function') open_direksi(sh, roleid, row.id_kavling);
        break;
      case 3: // Keuangan
        if (typeof open_keuangan === 'function') open_keuangan(sh, roleid, row.id_kavling);
        break;
      case 10: // Pajak
        if (typeof open_pajak === 'function') open_pajak(sh, roleid, row.id_kavling);
        break;
      default:
        console.error("[INFO] :: Modul untuk role ini tidak tersedia atau komponen form belum dimuat.");
    }
  };

  $(document).ajaxSuccess(function(event, xhr, settings) {
    if (settings.url.includes('simpan')) {
      if ($.fn.DataTable.isDataTable('#data_tables')) {
        $('#data_tables').DataTable().draw(false);
      }
    }
  });

  $(function() {
    var table = $('#data_tables').DataTable({
      fnDrawCallback: function() {
        $('[data-toggle="popover"]').popover();
      },
      scrollY: "50vh",
      scrollX: true,
      scrollCollapse: true,
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
              id: item['id_proyek'],
              text: item[1] + ' (' + item[2] + ')',
              data: item
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
    $("#id_proyek").on("select2:select", function(e) {
      $('#id_cluster').val(null).trigger('change');

      dt_proyek = e.params.data;

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
    //remove bug arrow select2
    $(".select2-selection__arrow").removeClass("select2-selection__arrow")

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
  echo '<script src="' . base_url() . 'assets/js/planning.js?' . filemtime(FCPATH . 'assets/js/planning.js') . '"></script>';
}
if (in_array($role_id, [7, 1])) {
  echo view('siteplan/produksi');
  echo '<script src="' . base_url() . 'assets/js/produksi.js?' . filemtime(FCPATH . 'assets/js/produksi.js') . '"></script>';
}
if (in_array($role_id, [8, 1])) {
  echo view('siteplan/sales');
  echo '<script src="' . base_url() . 'assets/js/sales.js?' . filemtime(FCPATH . 'assets/js/sales.js') . '"></script>';
}
if (in_array($role_id, [5, 1])) {
  echo view('siteplan/legal');
  echo '<script src="' . base_url() . 'assets/js/legal.js?' . filemtime(FCPATH . 'assets/js/legal.js') . '"></script>';
}
if (in_array($role_id, [4, 1])) {
  echo view('siteplan/mkdt');
  echo '<script src="' . base_url() . 'assets/js/mkdt.js?' . filemtime(FCPATH . 'assets/js/mkdt.js') . '"></script>';
}
if (in_array($role_id, [9, 1])) {
  echo view('siteplan/direksi');
  echo '<script src="' . base_url() . 'assets/js/direksi.js?' . filemtime(FCPATH . 'assets/js/direksi.js') . '"></script>';
}
if (in_array($role_id, [3, 1])) {
  echo view('siteplan/keuangan');
  echo '<script src="' . base_url() . 'assets/js/keuangan.js?' . filemtime(FCPATH . 'assets/js/keuangan.js') . '"></script>';
}
if (in_array($role_id, [10, 1])) {
  echo view('siteplan/pajak');
  echo '<script src="' . base_url() . 'assets/js/pajak.js?' . filemtime(FCPATH . 'assets/js/pajak.js') . '"></script>';
}
?>