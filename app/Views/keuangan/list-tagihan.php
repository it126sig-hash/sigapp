<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/fixed-columns/fixedColumns.bootstrap4.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/richtext.min.css">
<link rel="stylesheet" type="text/css"
  href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<script>
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
  const state = {
    id_kavling: null,
    id_hargajual: null,
    id_mkdt: null,
    data_um: {},
    data_bb: {},
    sisa_cicilan: 0,
    sudah_bayar: 0,
    total_cicilan: 0,
    status: {
      tab: {
        isClosed: false
      }
    }

  };
  const li_keu = JSON.parse('<?= $li_keu ?>')
  const dt_proyek = {}
</script>
<style>
  .list-tagihan-page {
    text-transform: uppercase;
  }

  .list-tagihan-page .card-header {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
    padding: .6rem .85rem;
  }

  .list-tagihan-page .list-tagihan-title {
    color: #111827;
    font-size: 1rem;
    font-weight: 800;
    margin: 0;
    white-space: nowrap;
  }

  .list-tagihan-page .list-tagihan-divider {
    align-self: stretch;
    background: #e5e7eb;
    flex: 0 0 1px;
    width: 1px;
  }

  .list-tagihan-page .list-tagihan-filter {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .list-tagihan-page .filter-field {
    flex: 0 0 150px;
  }

  .list-tagihan-page .filter-field-sm {
    flex: 0 0 120px;
  }

  .list-tagihan-page .filter-action {
    flex: 0 0 auto;
  }

  .list-tagihan-page .form-control,
  .list-tagihan-page .select2-selection {
    border-color: #d8dde3 !important;
    border-radius: 6px !important;
    font-size: .78rem;
    min-height: 30px;
  }

  .list-tagihan-page select.form-control {
    padding: .25rem .5rem;
  }

  .list-tagihan-page .select2-selection__rendered {
    line-height: 28px !important;
  }

  .list-tagihan-page .select2-selection__arrow {
    height: 28px !important;
  }

  .list-tagihan-page .card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: none;
  }

  .list-tagihan-page .card-datatable {
    padding: .35rem;
  }

  #data_table {
    border-collapse: separate !important;
    border-spacing: 0;
    font-size: 10px;
    vertical-align: middle !important;
    width: 100% !important;
  }

  #data_table thead th {
    background: #eef5ff;
    border-bottom: 1px solid #c9ddf5;
    color: #2057a3;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: 0;
    padding: .55rem .5rem;
    white-space: nowrap;
  }

  #data_table tbody td {
    border-color: #edf0f2;
    color: #111827;
    padding: .45rem .5rem;
    vertical-align: middle !important;
    white-space: nowrap;
  }

  #data_table tbody tr:hover td {
    background: #f8fbff;
  }

  .tagihan-pay-btn,
  .tagihan-detail-toggle {
    border-radius: 6px;
    font-size: .72rem;
    font-weight: 800;
    padding: .28rem .55rem;
  }

  .tagihan-detail-toggle {
    align-items: center;
    display: inline-flex;
    height: 28px;
    justify-content: center;
    width: 28px;
  }

  tr.shown .tagihan-detail-toggle {
    background: #2057a3;
    border-color: #2057a3;
    color: #fff;
  }

  .tagihan-child-wrap {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin: .35rem 0;
    padding: .65rem;
    text-transform: uppercase;
  }

  .tagihan-child-table {
    font-size: .75rem;
    margin-bottom: 0;
  }

  .tagihan-child-table th {
    background: #fff;
    color: #2057a3;
    font-weight: 800;
    white-space: nowrap;
  }

  .tagihan-child-table td,
  .tagihan-child-table th {
    padding: .45rem .55rem;
    vertical-align: middle;
  }

  .tagihan-child-loading,
  .tagihan-child-error,
  .tagihan-child-empty {
    color: #6b7280;
    font-size: .78rem;
    font-weight: 700;
    padding: .55rem;
  }

  .tagihan-child-error {
    color: #b91c1c;
  }

</style>
<!-- /.card-header -->
<div class="app-content content list-tagihan-page">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="list-tagihan-title"><?= $data['title'] ?></h5>
            <div class="list-tagihan-divider"></div>
            <div class="list-tagihan-filter">
              <div class="filter-field">
                <select disabled id="id_cluster" name="id_cluster" class="select2 form-control"></select>
              </div>
              <div class="filter-field">
                <select disabled id="id_jalan" name="id_jalan" class="select2 form-control"></select>
              </div>
              <div class="filter-field filter-field-sm">
                <select id="status_lunas" name="status_lunas" class="form-control">
                  <option value="0">Belum Lunas</option>
                  <option value="1">Sudah Lunas</option>
                </select>
              </div>
              <div class="filter-action">
                <button type="button" id="btn_draw" class="btn btn-primary waves-effect btn-sm text-uppercase">
                  <i class="fas fa-filter mr-25"></i> Filter Data
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-datatable">
            <table id="data_table" class="datatables-basic table compact">
              <thead>
                <tr>
                  <th></th>
                  <th id="tb-AKSI">Aksi</th>
                  <th id="tb-NO">No</th>
                  <th id="tb-BLOK">BLOK</th>
                  <th id="tb-NO_KAVLING">NO KAVLING</th>
                  <th id="tb-TYPE">TYPE</th>
                  <th>NAMA KONSUMEN</th>
                  <th>TANGGAL BOOKING</th>
                  <th>TUNAI/KPR</th>
                  <th id="tb-JATUH_TEMPO">JATUH TEMPO TERDEKAT</th>
                  <th id="tb-TOTAL_TAGIHAN">TOTAL TAGIHAN</th>
                  <th id="tb-SUDAH_BAYAR">SUDAH BAYAR</th>
                  <th id="tb-SISA_TAGIHAN">SISA TAGIHAN</th>
                </tr>
              </thead>
            </table>

          </div>
        </div>
      </div>
    </div>
  </section>

  <section>
    <?= view('keuangan/partials/modal_bayar_tagihan') ?>
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
<script src="<?= base_url() ?>assets/js/tagihan-bayar-modal.js?v=<?= filemtime(FCPATH.'assets/js/tagihan-bayar-modal.js') ?>"></script>
<script>
  let fp = flatpickr(".flatpickr-human-friendly", {
    altInput: true,
    altFormat: 'F j, Y',
    dateFormat: 'Y-m-d'
  });

  let listTagihanTable = null;
  let tagihanDetailCache = {};

  function isi_data(keepModalOpen = false) {
    if (!keepModalOpen) {
      $("#modal_divisi3").modal("hide");
    }
    tagihanDetailCache = {};
    if (listTagihanTable) listTagihanTable.ajax.reload(null, false);
  }

  function formatTagihanChild(data) {
    if (!Array.isArray(data) || data.length === 0) {
      return '<div class="tagihan-child-wrap"><div class="tagihan-child-empty">Tidak ada detail tagihan.</div></div>';
    }

    let total = 0;
    const rows = data.map((item, index) => {
      const nominal = keuToNumber(item.nominal);
      total += nominal;
      const isPaid = parseInt(item.sudah_dibayar || 0) === 1;
      const badge = isPaid ? '<span class="badge badge-success">LUNAS</span>' : '<span class="badge badge-warning">BELUM LUNAS</span>';

      return `
        <tr>
          <td>${index + 1}</td>
          <td>${keuEscapeHtml(item.berita_acara || "-")}</td>
          <td>${format_date(item.jatuh_tempo_tgl) || "-"}</td>
          <td>${keuEscapeHtml(item.status || "-")}</td>
          <td class="text-right">Rp ${num_format(nominal)}</td>
          <td>${badge}</td>
        </tr>`;
    }).join("");

    return `
      <div class="tagihan-child-wrap">
        <div class="table-responsive">
          <table class="table table-sm table-bordered tagihan-child-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Berita Acara</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
                <th class="text-right">Nominal</th>
                <th>Pembayaran</th>
              </tr>
            </thead>
            <tbody>${rows}</tbody>
            <tfoot>
              <tr>
                <th colspan="4">Total</th>
                <th class="text-right">Rp ${num_format(total)}</th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>`;
  }

  function loadChildTagihan(row, rowData) {
    const idMkdt = rowData.id_mkdt;
    row.child('<div class="tagihan-child-wrap"><div class="tagihan-child-loading">Memuat detail tagihan...</div></div>').show();

    if (tagihanDetailCache[idMkdt]) {
      row.child(formatTagihanChild(tagihanDetailCache[idMkdt])).show();
      return;
    }

    $.ajax({
      url: base_url + "tagihan/list/detail",
      type: "post",
      dataType: "json",
      data: {
        [csrfName]: csrfHash,
        id_mkdt: idMkdt,
      },
      success: function(r) {
        if (r.token) csrfHash = r.token;
        if (r.success === true) {
          tagihanDetailCache[idMkdt] = Array.isArray(r.data) ? r.data : [];
          row.child(formatTagihanChild(tagihanDetailCache[idMkdt])).show();
        } else {
          row.child('<div class="tagihan-child-wrap"><div class="tagihan-child-error">' + keuEscapeHtml(r.message || "Gagal memuat detail tagihan") + '</div></div>').show();
        }
      },
      error: function() {
        row.child('<div class="tagihan-child-wrap"><div class="tagihan-child-error">Terjadi kesalahan saat memuat detail tagihan.</div></div>').show();
      },
    });
  }

  function fitTagihanTableHeight() {
    var $scrollBody = $('#data_table_wrapper .dataTables_scrollBody');
    if (!$scrollBody.length) return;
    var height = $(window).height() - $scrollBody.offset().top - 16;
    $scrollBody.css({
      'max-height': Math.max(200, height) + 'px',
      height: Math.max(200, height) + 'px'
    });
  }

  $(function() {
    listTagihanTable = $('#data_table').DataTable({
      fnDrawCallback: function() {
        $('[data-toggle="popover"]').popover();
        var api = this.api();
        setTimeout(function() {
          api.columns().adjust();
          fitTagihanTableHeight();
        }, 10);
      },
      scrollY: "60vh",
      scrollX: true,
      scrollCollapse: true,
      autoWidth: false,
      processing: true,
      serverSide: true,
      lengthChange: true,
      searching: true,
      ordering: true,
      paging: true,
      order: [
        [9, "asc"]
      ],
      columns: [{
          data: null,
          orderable: false,
          searchable: false,
          className: "text-center",
          defaultContent: '<button type="button" class="btn btn-outline-primary btn-sm tagihan-detail-toggle"><i class="fas fa-chevron-down"></i></button>'
        },
        {
          data: "Aksi",
          orderable: false,
          searchable: false,
          className: "text-center"
        },
        {
          data: "no",
          orderable: false,
          searchable: false,
          className: "text-center"
        },
        {
          data: "nama_jalan",
          name: "j.nama_jalan"
        },
        {
          data: "no_kavling",
          name: "k.no_kavling",
          className: "text-center"
        },
        {
          data: "tipe_pricelist",
          name: "hj.id_tipe",
          orderable: false
        },
        {
          data: "nama_konsumen",
          name: "c.nama_konsumen"
        },
        {
          data: "booking_tgl",
          name: "m.booking_tgl",
          orderable: false
        },
        {
          data: "is_kpr",
          name: "m.is_kpr",
          orderable: false,
          className: "text-center"
        },
        {
          data: "jatuh_tempo_tgl",
          name: "keu_agg.jatuh_tempo_tgl"
        },
        {
          data: "total_tagihan",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "sudah_bayar",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "sisa_tagihan",
          orderable: false,
          searchable: false,
          className: "text-right font-weight-bold"
        }
      ],
      ajax: {
        url: base_url + 'tagihan/list/ambil-grouped',
        type: "POST",
        dataType: "json",
        data: function(data) {
          data[csrfName] = csrfHash
          data.id_proyek = activeProyekId()
          data.id_cluster = $("#id_cluster").val()
          data.id_jalan = $("#id_jalan").val()
          data.status_lunas = $("#status_lunas").val()
        },
        dataSrc: function(r) {
          if (r.token) csrfHash = r.token
          return r.data;
        },
        async: "true"
      }
    });

    var resizeTimer;
    $(window).on('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(fitTagihanTableHeight, 150);
    });

    $('#data_table tbody').on('click', '.tagihan-detail-toggle', function() {
      const tr = $(this).closest('tr');
      const row = listTagihanTable.row(tr);

      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        $(this).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        return;
      }

      tr.addClass('shown');
      $(this).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
      loadChildTagihan(row, row.data());
    });

    //on chnage search
    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        listTagihanTable.search(this.value).draw();
      });

    if (activeProyekId()) {
      $("#id_cluster").prop("disabled", false);
      listTagihanTable.draw();
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
      tagihanDetailCache = {};
      listTagihanTable.draw();
    })

    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none")

  });
</script>
