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

    #data_table tbody td:nth-child(1),
  #data_table thead th:nth-child(1),
  #data_table tbody td:nth-child(3),
  #data_table thead th:nth-child(3),
  #data_table tbody td:nth-child(4),
  #data_table thead th:nth-child(4),
  #data_table tbody td:nth-child(5),
  #data_table thead th:nth-child(5),
  #data_table tbody td:nth-child(9),
  #data_table thead th:nth-child(9) {
    text-align: center !important;
  }

  #data_table tbody td:nth-child(6),
  #data_table thead th:nth-child(6),
  #data_table tbody td:nth-child(7),
  #data_table thead th:nth-child(7),
  #data_table tbody td:nth-child(8),
  #data_table thead th:nth-child(8) {
    text-align: right !important;
  }

  #data_table tbody td:nth-child(2),
  #data_table thead th:nth-child(2) {
    text-align: left !important;
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
              <div class="filter-action">
                <button type="button" class="btn btn-outline-secondary waves-effect btn-sm text-uppercase mr-50" id="btn_clear_filter_main">
                  <i class="fas fa-times mr-25"></i> Clear
                </button>
                <button type="button" class="btn btn-success waves-effect btn-sm text-uppercase mr-50" id="btn_export_excel" data-old-text="<i class='fa fa-file-excel mr-25'></i> Export Excel">
                  <i class="fas fa-file-excel mr-25"></i> Export Excel
                </button>
                <button type="button" class="btn btn-primary waves-effect btn-sm text-uppercase" data-toggle="modal" data-target="#filterModal">
                  <i class="fas fa-filter mr-25"></i> Filter Data
                </button>
              </div>
            </div>
          </div>
          <div class="card-body py-1 border-top" id="active_filter_container" style="display: none;">
            <div class="d-flex flex-wrap align-items-center" style="gap: .5rem;">
                <span class="font-weight-bold text-muted font-small-3 mr-50">Filter Aktif:</span>
                <div id="active_filter_tags" class="d-flex flex-wrap" style="gap: .5rem;"></div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-datatable">
            <table id="data_table" class="datatables-basic table compact">
              <thead>
                <tr>
                  <th class="text-center" id="tb-NO" width="5%">No</th>
                  <th class="text-left" style="text-align: left !important;" width="20%">KONSUMEN</th>
                  <th class="text-center" id="tb-TYPE" width="10%">TYPE</th>
                  <th class="text-center" width="10%">SKEMA</th>
                  <th class="text-center" id="tb-JATUH_TEMPO" width="15%">JATUH TEMPO</th>
                  <th class="text-right" id="tb-TOTAL_TAGIHAN" width="10%">TOTAL</th>
                  <th class="text-right" id="tb-SUDAH_BAYAR" width="10%">SUDAH BAYAR</th>
                  <th class="text-right font-weight-bolder" style="font-weight: 800 !important; color: #000;" id="tb-SISA_TAGIHAN" width="10%">SISA</th>
                  <th class="text-center" id="tb-AKSI" width="10%">AKSI</th>
                </tr>
              </thead>
            </table>

          </div>
        </div>
      </div>
    </div>
  </section>

  <section>
    <!-- Modal Filter -->
    <div class="modal modal-slide-in fade" id="filterModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog sidebar-sm" role="document">
        <div class="modal-content pt-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">ÃƒÆ’Ã¢â‚¬â€</button>
          <div class="modal-header mb-1">
            <h5 class="modal-title"><span class="align-middle"><i class="fas fa-filter text-primary mr-50"></i> Filter Tagihan</span></h5>
          </div>
          <div class="modal-body flex-grow-1">
            <div class="form-group">
              <label><i class="fas fa-map-marker-alt text-muted mr-50"></i> Cluster</label>
              <select disabled id="id_cluster" name="id_cluster" class="select2 form-control w-100"></select>
            </div>
            <div class="form-group">
              <label><i class="fas fa-home text-muted mr-50"></i> Blok</label>
              <select disabled id="id_jalan" name="id_jalan" class="select2 form-control w-100"></select>
            </div>
            <div class="form-group">
              <label><i class="fas fa-check-circle text-muted mr-50"></i> Status Tagihan</label>
              <select id="status_lunas" name="status_lunas" class="form-control">
                <option value="0">Belum Lunas</option>
                <option value="1">Sudah Lunas</option>
              </select>
            </div>
            <div class="form-group">
              <label><i class="fas fa-money-bill-wave text-muted mr-50"></i> Tunai / KPR</label>
              <select id="is_kpr" name="is_kpr" class="form-control">
                <option value="">Semua</option>
                <option value="0">Tunai</option>
                <option value="1">KPR</option>
              </select>
            </div>
            <div class="form-group">
              <label><i class="fas fa-calendar-plus text-muted mr-50"></i> Periode Tanggal Booking</label>
              <input type="text" id="booking_tgl_range" class="form-control flatpickr-range bg-white" placeholder="Pilih Range Tanggal">
            </div>
            <div class="form-group">
              <label><i class="fas fa-calendar-times text-muted mr-50"></i> Periode Jatuh Tempo</label>
              <input type="text" id="jatuh_tempo_tgl_range" class="form-control flatpickr-range bg-white" placeholder="Pilih Range Tanggal">
            </div>
            <div class="d-flex justify-content-between mt-2 border-top pt-2">
              <button type="button" class="btn btn-outline-secondary" id="btn_clear_filter_modal">
                 <i class="fas fa-times mr-25"></i> Clear Filter
              </button>
              <button type="button" class="btn btn-primary" id="btn_terapkan_filter">
                 <i class="fas fa-check mr-25"></i> Terapkan Filter
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section>
    <?= view('keuangan/partials/modal_bayar_tagihan') ?>
  
    <!-- Modal Detail Drawer -->
    <div class="modal modal-slide-in fade" id="detailDrawerModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog sidebar-sm" role="document">
        <div class="modal-content pt-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">Ãƒâ€”</button>
          <div class="modal-header mb-1">
            <h5 class="modal-title"><span class="align-middle"><i class="fas fa-file-invoice-dollar text-primary mr-50"></i> Detail Jatuh Tempo</span></h5>
          </div>
          <div class="modal-body flex-grow-1" id="detailDrawerBody">
            <div class="text-center text-muted py-2">Memuat data...</div>
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
<script src="<?= base_url() ?>assets/js/tagihan-bayar-modal.js?v=<?= filemtime(FCPATH.'assets/js/tagihan-bayar-modal.js') ?>"></script>
<script>
  let fp = flatpickr(".flatpickr-human-friendly", {
    altInput: true,
    altFormat: 'F j, Y',
    dateFormat: 'Y-m-d'
  });

  let fpRange = flatpickr(".flatpickr-range", {
    mode: "range",
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "j F Y",
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
      const isVoid = parseInt(item.is_void || 0) === 1;
      if (!isVoid) total += nominal;
      const isPaid = parseInt(item.sudah_dibayar || 0) === 1;
      const badge = isVoid
        ? `<span class="badge badge-secondary" title="${keuEscapeAttribute(item.void_reason || "")}">VOID</span>`
        : (isPaid ? '<span class="badge badge-success">LUNAS</span>' : '<span class="badge badge-warning">BELUM LUNAS</span>');
      const voidReason = isVoid && item.void_reason
        ? `<br><small class="text-danger">Alasan void: ${keuEscapeHtml(item.void_reason)}</small>`
        : "";

      return `
        <tr${isVoid ? ' class="text-muted"' : ""}>
          <td>${index + 1}</td>
          <td>${keuEscapeHtml(item.berita_acara || "-")}${voidReason}</td>
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
                  <th class="text-center" id="tb-NO" width="5%">No</th>
                  <th class="text-left" style="text-align: left !important;" width="20%">KONSUMEN</th>
                  <th class="text-center" id="tb-TYPE" width="10%">TYPE</th>
                  <th class="text-center" width="10%">SKEMA</th>
                  <th class="text-center" id="tb-JATUH_TEMPO" width="15%">JATUH TEMPO</th>
                  <th class="text-right" id="tb-TOTAL_TAGIHAN" width="10%">TOTAL</th>
                  <th class="text-right" id="tb-SUDAH_BAYAR" width="10%">SUDAH BAYAR</th>
                  <th class="text-right font-weight-bolder" style="font-weight: 800 !important; color: #000;" id="tb-SISA_TAGIHAN" width="10%">SISA</th>
                  <th class="text-center" id="tb-AKSI" width="10%">AKSI</th>
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
      url: base_url + "tagihan/jatuh-tempo/detail",
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
      pageLength: 25,
      searching: true,
      ordering: true,
      paging: true,
            order: [
        [4, "asc"]
      ],
      columns: [
        {
          data: "no",
          orderable: false,
          searchable: false,
          className: "text-center"
        },
        {
          data: "nama_konsumen",
          name: "c.nama_konsumen",
          className: "text-left",
          render: function(data, type, row) {
            return '<div class="font-weight-bold text-dark" style="text-align: left !important;">' + data + '</div>' +
                   '<div class="small text-muted mt-25" style="text-align: left !important;">' + row.nama_jalan + ' &bull; No. ' + row.no_kavling + '</div>';
          }
        },
        {
          data: "tipe_pricelist",
          name: "hj.id_tipe",
          className: "text-center",
          orderable: false
        },
        {
          data: "is_kpr",
          name: "m.is_kpr",
          className: "text-center",
          orderable: false,
                              render: function(data) {
            let isKpr = false;
            if (data == 1 || data === '1' || data === 'KPR' || data === true) {
                isKpr = true;
            } else if (typeof data === 'string' && data.indexOf('KPR') !== -1) {
                isKpr = true;
            }
            return isKpr ? '<span class="badge badge-success">KPR</span>' : '<span class="badge badge-primary">TUNAI</span>';
          }
        },
        {
          data: "jatuh_tempo_tgl",
          name: "keu_agg.jatuh_tempo_tgl",
          className: "text-center",
          render: function(data, type, row) {
            if (type === 'display' && data) {
              let jtParts = data.split('-');
              let jtDate = new Date(jtParts[0], jtParts[1] - 1, jtParts[2]);
              let today = new Date();
              today.setHours(0,0,0,0);
              jtDate.setHours(0,0,0,0);
              
              let diffTime = today.getTime() - jtDate.getTime();
              let diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
              
              let html = '<div class="font-weight-bold">' + format_date(data) + '</div>';
              if (diffDays > 0) {
                html += '<div class="mt-25"><span class="badge badge-light-danger">' + diffDays + ' HARI TERLAMBAT</span></div>';
              } else if (diffDays === 0) {
                html += '<div class="mt-25"><span class="badge badge-light-warning">JATUH TEMPO HARI INI</span></div>';
              }
              
              html += '<div class="small text-muted mt-25">' + (row.jumlah_tagihan || 0) + ' item tagihan</div>';
              return html;
            }
            return data;
          }
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
          className: "text-right",
          render: function(data) {
            return '<span class="font-weight-bolder text-dark" style="font-size: 1.05rem;">' + data + '</span>';
          }
        },
        {
          data: "Aksi",
          orderable: false,
          searchable: false,
          className: "text-center",
          render: function(data, type, row) {
            return '<div class="d-flex align-items-center justify-content-center" style="gap: .35rem;">' +
                      (data !== '-' ? data : '') + 
                      '<button type="button" class="btn btn-outline-info btn-sm tagihan-detail-drawer" data-id="' + row.id_mkdt + '">' +
                        '<i class="fas fa-info-circle"></i> Detail' +
                      '</button>' +
                    '</div>';
          }
        }
      ],
      ajax: {
        url: base_url + 'tagihan/jatuh-tempo/ambil-grouped',
        type: "POST",
        dataType: "json",
        data: function(data) {
          data[csrfName] = csrfHash
          data.id_proyek = activeProyekId()
          data.id_cluster = $("#id_cluster").val()
          data.id_jalan = $("#id_jalan").val()
          data.status_lunas = $("#status_lunas").val()
          data.is_kpr = $("#is_kpr").val()
          data.booking_tgl_range = $("#booking_tgl_range").val()
          data.jatuh_tempo_tgl_range = $("#jatuh_tempo_tgl_range").val()
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
    $(window).on('load', fitTagihanTableHeight);

    // Open Detail Drawer
    $('#data_table tbody').on('click', '.tagihan-detail-drawer', function() {
      const tr = $(this).closest('tr');
      const row = listTagihanTable.row(tr).data();
      const idMkdt = row.id_mkdt || $(this).data('id');
      
      $('#detailDrawerModal').modal('show');
      $('#detailDrawerBody').html('<div class="text-center text-muted py-2"><i class="fas fa-spinner fa-spin mr-50"></i> Memuat detail...</div>');
      
      // Hitung selisih hari terdekat
      let jtParts = row.jatuh_tempo_tgl.split('-');
      let jtDate = new Date(jtParts[0], jtParts[1] - 1, jtParts[2]);
      let today = new Date();
      today.setHours(0,0,0,0);
      jtDate.setHours(0,0,0,0);
      let diffTime = today.getTime() - jtDate.getTime();
      let diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
      
      let badgeHtml = '';
      if (diffDays > 0) {
        badgeHtml = `<span class="badge badge-light-danger mt-50">${diffDays} HARI TERLAMBAT</span>`;
      } else if (diffDays === 0) {
        badgeHtml = `<span class="badge badge-light-warning mt-50">JATUH TEMPO HARI INI</span>`;
      }

      let headerHtml = `
        <div class="mb-2 pb-1 border-bottom">
          <h6 class="font-weight-bolder mb-25 text-dark">${row.nama_konsumen}</h6>
          <div class="small text-muted">${row.nama_jalan} &bull; No. ${row.no_kavling} &bull; Type ${row.tipe_pricelist}</div>
        </div>
        
        <div class="mb-2 pb-1 border-bottom">
          <div class="text-uppercase font-weight-bold text-muted font-small-2 mb-1">Summary</div>
          <div class="mb-50">
            <div class="font-small-2 text-muted">Sisa Tagihan</div>
            <div class="font-weight-bolder text-danger" style="font-size: 1.1rem;">Rp ${row.sisa_tagihan}</div>
            <div>${badgeHtml}</div>
          </div>
          
          <div class="d-flex justify-content-between font-small-3 mt-1">
            <span class="text-muted">Total Tagihan</span>
            <span class="font-weight-bold">Rp ${row.total_tagihan}</span>
          </div>
          <div class="d-flex justify-content-between font-small-3 mt-25">
            <span class="text-muted">Sudah Bayar</span>
            <span class="font-weight-bold">Rp ${row.sudah_bayar}</span>
          </div>
          <div class="d-flex justify-content-between font-small-3 mt-25">
            <span class="text-muted">Skema</span>
            <span class="font-weight-bold">${(row.is_kpr == 1 || row.is_kpr === '1' || row.is_kpr === 'KPR' || row.is_kpr === true || (typeof row.is_kpr === 'string' && row.is_kpr.indexOf('KPR') !== -1)) ? '<span class="badge badge-success">KPR</span>' : '<span class="badge badge-primary">TUNAI</span>'}</span>
          </div>
        </div>
        
        <div class="text-uppercase font-weight-bold text-muted font-small-2 mb-75">Detail Item</div>
      `;
      
      $.ajax({
        url: base_url + "tagihan/jatuh-tempo/detail",
        type: "post",
        dataType: "json",
        data: {
          [csrfName]: csrfHash,
          id_mkdt: idMkdt,
        },
        success: function(r) {
          if (r.token) csrfHash = r.token;
          
          let itemsHtml = '';
          if (r.success === true && Array.isArray(r.data) && r.data.length > 0) {
            r.data.forEach(function(item) {
              const isVoid = parseInt(item.is_void || 0) === 1;
              const isPaid = parseInt(item.sudah_dibayar || 0) === 1;
              
              let statusBadge = '';
              if (isVoid) {
                statusBadge = '<span class="badge badge-light-secondary">VOID</span>';
              } else if (isPaid) {
                statusBadge = '<span class="badge badge-light-success">LUNAS</span>';
              } else {
                statusBadge = '<span class="badge badge-light-warning">BELUM LUNAS</span>';
              }
              
              itemsHtml += `
                <div class="card border shadow-none mb-1">
                  <div class="card-body p-1">
                    <div class="d-flex justify-content-between align-items-start mb-50">
                      <div>
                        <div class="font-weight-bold text-dark font-small-3">${item.berita_acara || '-'}</div>
                        <div class="text-muted font-small-2 mt-25"><i class="fas fa-calendar-alt mr-25"></i> ${format_date(item.jatuh_tempo_tgl) || '-'}</div>
                      </div>
                      ${statusBadge}
                    </div>
                    
                    <div class="row mt-75 font-small-3">
                      <div class="col-6">
                        <div class="text-muted font-small-2">Status</div>
                        <div class="font-weight-bold mt-25">${item.status || '-'}</div>
                      </div>
                      <div class="col-6 text-right">
                        <div class="text-muted font-small-2">Nominal</div>
                        <div class="font-weight-bold mt-25">Rp ${num_format(item.nominal)}</div>
                      </div>
                    </div>
                  </div>
                </div>
              `;
            });
          } else {
            itemsHtml = '<div class="text-center text-muted font-small-3 py-1 bg-light rounded">Tidak ada detail tagihan yang belum lunas.</div>';
          }
          
          $('#detailDrawerBody').html(headerHtml + '<div class="detail-items-container">' + itemsHtml + '</div>');
        },
        error: function() {
          $('#detailDrawerBody').html('<div class="alert alert-danger p-1 font-small-3">Gagal memuat detail tagihan. Terjadi kesalahan jaringan.</div>');
        }
      });
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

    $("#btn_terapkan_filter").on("click", function(e) {
      tagihanDetailCache = {};
      listTagihanTable.draw();
      $("#filterModal").modal("hide");
    });

    function updateActiveFilterTags() {
        let tags = [];
        
        let cluster = $("#id_cluster").select2('data');
        if (cluster && cluster.length > 0 && cluster[0].id) {
            tags.push(`<span class="badge badge-light-primary">Cluster: ${cluster[0].text}</span>`);
        }
        let jalan = $("#id_jalan").select2('data');
        if (jalan && jalan.length > 0 && jalan[0].id) {
            tags.push(`<span class="badge badge-light-primary">Blok: ${jalan[0].text}</span>`);
        }
        let lunas = $("#status_lunas option:selected").text();
        if ($("#status_lunas").val() != "0") {
            tags.push(`<span class="badge badge-light-info">Status: ${lunas}</span>`);
        }
        let isKpr = $("#is_kpr").val();
        if (isKpr !== "") {
            tags.push(`<span class="badge badge-light-warning">Tipe: ${isKpr == '1' ? 'KPR' : 'Tunai'}</span>`);
        }
        let booking = $("#booking_tgl_range").val();
        if (booking) {
            tags.push(`<span class="badge badge-light-success">Booking: ${booking}</span>`);
        }
        let jt = $("#jatuh_tempo_tgl_range").val();
        if (jt) {
            tags.push(`<span class="badge badge-light-danger">Jatuh Tempo: ${jt}</span>`);
        }

        if (tags.length > 0) {
            $("#active_filter_container").show();
            $("#active_filter_tags").html(tags.join(""));
        } else {
            $("#active_filter_container").hide();
            $("#active_filter_tags").html("");
        }
    }

    listTagihanTable.on('draw', function () {
        updateActiveFilterTags();
    });

    $("#btn_clear_filter_main, #btn_clear_filter_modal").on("click", function() {
        $("#id_cluster").val(null).trigger("change");
        $("#status_lunas").val("0");
        $("#is_kpr").val("");
        if (document.querySelector("#booking_tgl_range")._flatpickr) {
            document.querySelector("#booking_tgl_range")._flatpickr.clear();
        }
        if (document.querySelector("#jatuh_tempo_tgl_range")._flatpickr) {
            document.querySelector("#jatuh_tempo_tgl_range")._flatpickr.clear();
        }
        
        tagihanDetailCache = {};
        listTagihanTable.draw();
        $("#filterModal").modal("hide");
    });

    $("#btn_export_excel").on('click', function(e) {
      let $btn = $(this);
      $.ajax({
        type: "post",
        url: base_url + "tagihan/jatuh-tempo/export-excel",
        data: {
          [csrfName]: csrfHash,
          id_proyek: activeProyekId(),
          id_cluster: $("#id_cluster").val(),
          id_jalan: $("#id_jalan").val(),
          status_lunas: $("#status_lunas").val(),
          is_kpr: $("#is_kpr").val(),
          booking_tgl_range: $("#booking_tgl_range").val(),
          jatuh_tempo_tgl_range: $("#jatuh_tempo_tgl_range").val(),
          search_value: $('.dataTables_filter input').val()
        },
        dataType: "json",
        beforeSend: function() {
          $btn.html("<i class='fa fa-spinner fa-spin mr-25'></i> Mengeksport...");
          $btn.prop("disabled", true);
        },
        success: function(data) {
          if (data.status) {
              var d = new Date()
              d = format_date(d.getFullYear() + "-" + (parseInt(d.getMonth()) + 1) + "-" + d.getDate());

              var $a = $("<a>");
              $a.attr("href", data.file);
              $("body").append($a);
              $a.attr("download", "Laporan_Jatuh_Tempo_" + d + ".xlsx");
              $a[0].click();
              $a.remove();
          } else {
              alert("Gagal mengeksport data");
          }
          $btn.html($btn.data("old-text"));
          $btn.prop("disabled", false);
        },
        error: function() {
          alert("Terjadi kesalahan sistem saat eksport");
          $btn.html($btn.data("old-text"));
          $btn.prop("disabled", false);
        }
      });
    });

    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none")

  });
</script>














