<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/bootstrap/extensions/fixed-columns/fixedColumns.bootstrap4.css">
<link rel="stylesheet" type="text/css"
  href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<script>
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
  const dt_proyek = {}
</script>
<style>
  .list-hasil-akad-page {
    text-transform: uppercase;
  }

  .list-hasil-akad-page .list-hasil-akad-filter {
    align-items: end;
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    padding: 1rem 1.25rem;
  }

  .list-hasil-akad-page .list-hasil-akad-title {
    color: #111827;
    font-size: 1rem;
    font-weight: 800;
    margin: 0;
    min-width: 160px;
  }

  .list-hasil-akad-page .filter-field {
    flex: 1 1 220px;
    min-width: 180px;
  }

  .list-hasil-akad-page .filter-action {
    flex: 0 0 auto;
  }

  .list-hasil-akad-page label {
    color: #6b7280;
    font-size: .72rem;
    font-weight: 700;
    margin-bottom: .25rem;
  }

  .list-hasil-akad-page .form-control,
  .list-hasil-akad-page .select2-selection {
    border-color: #d8dde3 !important;
    border-radius: 6px !important;
    min-height: 34px;
  }

  .list-hasil-akad-page .card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: none;
  }

  .list-hasil-akad-page .card-datatable {
    padding: .35rem;
  }

  #data_table_hasil_akad {
    border-collapse: separate !important;
    border-spacing: 0;
    font-size: 10px;
    vertical-align: middle !important;
    width: 100% !important;
  }

  #data_table_hasil_akad thead th {
    background: #eef5ff;
    border-bottom: 1px solid #c9ddf5;
    color: #2057a3;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: 0;
    padding: .55rem .5rem;
    white-space: nowrap;
  }

  #data_table_hasil_akad tbody td {
    border-color: #edf0f2;
    color: #111827;
    padding: .45rem .5rem;
    vertical-align: middle !important;
    white-space: nowrap;
  }

  #data_table_hasil_akad tbody tr:hover td {
    background: #f8fbff;
  }

  .hasil-akad-detail-toggle {
    align-items: center;
    border-radius: 6px;
    display: inline-flex;
    font-size: .72rem;
    font-weight: 800;
    height: 28px;
    justify-content: center;
    width: 28px;
  }

  tr.shown .hasil-akad-detail-toggle {
    background: #2057a3;
    border-color: #2057a3;
    color: #fff;
  }

  .hasil-akad-child-wrap {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin: .35rem 0;
    padding: .65rem;
    text-transform: uppercase;
  }

  .hasil-akad-child-table {
    font-size: .75rem;
    margin-bottom: .75rem;
  }

  .hasil-akad-child-table th {
    background: #fff;
    color: #2057a3;
    font-weight: 800;
    white-space: nowrap;
  }

  .hasil-akad-child-table td,
  .hasil-akad-child-table th {
    padding: .45rem .55rem;
    vertical-align: middle;
  }

  .hasil-akad-child-loading,
  .hasil-akad-child-error,
  .hasil-akad-child-empty {
    color: #6b7280;
    font-size: .78rem;
    font-weight: 700;
    padding: .55rem;
  }

  .hasil-akad-child-error {
    color: #b91c1c;
  }
</style>
<!-- /.card-header -->
<div class="app-content content list-hasil-akad-page">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="list-hasil-akad-title"><?= $data['title'] ?></h5>
          </div>
          <div class="list-hasil-akad-filter border-bottom">
            <div class="filter-field">
              <label>Cluster</label>
              <select disabled id="id_cluster" name="id_cluster" class="select2 form-control"></select>
            </div>
            <div class="filter-field">
              <label>Blok</label>
              <select disabled id="id_jalan" name="id_jalan" class="select2 form-control"></select>
            </div>
            <div class="filter-field">
              <label>Status Pencairan</label>
              <select id="status_cair" name="status_cair" class="form-control">
                <option value="">Semua</option>
                <option value="belum_cair">Belum Cair</option>
                <option value="sudah_cair">Sudah Cair</option>
              </select>
            </div>
            <div class="filter-action">
              <button type="button" id="btn_draw" class="btn btn-primary waves-effect btn-sm text-uppercase">
                <i class="fas fa-filter mr-25"></i> Filter Data
              </button>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-datatable">
            <table id="data_table_hasil_akad" class="datatables-basic table compact">
              <thead>
                <tr>
                  <th></th>
                  <th>No</th>
                  <th>Aksi</th>
                  <th>Tgl Akad</th>
                  <th>Nama Konsumen</th>
                  <th>Blok</th>
                  <th>No Kavling</th>
                  <th>Tipe Rumah</th>
                  <th>Harga Jual</th>
                  <th>KPR (ACC KPR)</th>
                  <th>Pengajuan Pencairan</th>
                  <th>Sudah Cair</th>
                  <th>Sisa</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?= view('keuangan/partials/modal_pencairan_akad') ?>

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

<script src="<?= base_url() ?>assets/js/pencairan-akad-modal.js?<?= filemtime(FCPATH . 'assets/js/pencairan-akad-modal.js') ?>"></script>
<script>
  let hasilAkadTable = null;
  let hasilAkadDetailCache = {};

  function paChildEscape(value) {
    return $("<div>").text(value === null || value === undefined ? "" : value).html();
  }

  function paChildStatusBadge(status) {
    const map = {
      active: '<span class="badge badge-secondary">Aktif</span>',
      partial: '<span class="badge badge-warning">Partial</span>',
      paid: '<span class="badge badge-success">Paid</span>',
      void: '<span class="badge badge-light-danger">Void</span>',
    };
    return map[status] || status;
  }

  function formatHasilAkadItems(items) {
    if (!Array.isArray(items) || items.length === 0) {
      return '<div class="hasil-akad-child-empty">Belum ada item retensi/tenor.</div>';
    }

    const rows = items.map(function(item) {
      const label = item.jenis === "retensi"
        ? "Retensi - " + paChildEscape(item.nama_jaminan || "-")
        : "Tenor #" + item.urutan_tenor;
      const locked = item.is_locked
        ? '<span class="badge badge-light-secondary">Terpakai di pengajuan</span>'
        : '<span class="badge badge-light-primary">Belum Diajukan</span>';

      return `
        <tr>
          <td>${item.jenis === "retensi" ? "Retensi" : "Tenor"}</td>
          <td>${label}</td>
          <td class="text-right">Rp ${num_format(parseFloat(item.nominal || 0))}</td>
          <td class="text-right">Rp ${num_format(parseFloat(item.sisa || 0))}</td>
          <td>${locked}</td>
        </tr>`;
    }).join("");

    return `
      <table class="table table-sm table-bordered hasil-akad-child-table mb-0">
        <thead>
          <tr>
            <th>Jenis</th>
            <th>Keterangan</th>
            <th class="text-right">Nominal</th>
            <th class="text-right">Sisa</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>`;
  }

  function formatHasilAkadPengajuan(pengajuan) {
    if (!Array.isArray(pengajuan) || pengajuan.length === 0) {
      return '<div class="hasil-akad-child-empty">Belum ada pengajuan pencairan.</div>';
    }

    const rows = pengajuan.map(function(row) {
      const lampiran = row.access_url
        ? `<a href="${row.access_url}" target="_blank">Lihat</a>`
        : "-";

      return `
        <tr>
          <td>${row.tanggal_pengajuan ? format_date(row.tanggal_pengajuan) : "-"}</td>
          <td>${row.tanggal_rencana_cair ? format_date(row.tanggal_rencana_cair) : "-"}</td>
          <td class="text-right">Rp ${num_format(parseFloat(row.total_pengajuan || 0))}</td>
          <td class="text-right">Rp ${num_format(parseFloat(row.total_cair || 0))}</td>
          <td>${paChildStatusBadge(row.status)}</td>
          <td>${lampiran}</td>
        </tr>`;
    }).join("");

    return `
      <table class="table table-sm table-bordered hasil-akad-child-table mb-0">
        <thead>
          <tr>
            <th>Tgl Pengajuan</th>
            <th>Rencana Cair</th>
            <th class="text-right">Total Pengajuan</th>
            <th class="text-right">Total Cair</th>
            <th>Status</th>
            <th>Lampiran</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>`;
  }

  function formatHasilAkadChild(data) {
    return `
      <div class="hasil-akad-child-wrap">
        <div class="font-weight-bold mb-50">Item Retensi &amp; Tenor</div>
        ${formatHasilAkadItems(data.items)}
        <div class="font-weight-bold mt-1 mb-50">Daftar Pengajuan</div>
        ${formatHasilAkadPengajuan(data.pengajuan)}
      </div>`;
  }

  function loadHasilAkadChild(row, rowData) {
    const idMkdt = rowData.id_mkdt;
    row.child('<div class="hasil-akad-child-wrap"><div class="hasil-akad-child-loading">Memuat detail...</div></div>').show();

    if (hasilAkadDetailCache[idMkdt]) {
      row.child(formatHasilAkadChild(hasilAkadDetailCache[idMkdt])).show();
      return;
    }

    $.ajax({
      url: base_url + "keuangan/hasil-akad/list/detail",
      type: "post",
      dataType: "json",
      data: {
        [csrfName]: csrfHash,
        id_mkdt: idMkdt,
      },
      success: function(r) {
        if (r.token) csrfHash = r.token;
        if (r.success === true) {
          hasilAkadDetailCache[idMkdt] = { items: r.items || [], pengajuan: r.pengajuan || [] };
          row.child(formatHasilAkadChild(hasilAkadDetailCache[idMkdt])).show();
        } else {
          row.child('<div class="hasil-akad-child-wrap"><div class="hasil-akad-child-error">' + paChildEscape(r.message || "Gagal memuat detail") + '</div></div>').show();
        }
      },
      error: function() {
        row.child('<div class="hasil-akad-child-wrap"><div class="hasil-akad-child-error">Terjadi kesalahan saat memuat detail.</div></div>').show();
      },
    });
  }

  $(function() {
    hasilAkadTable = $('#data_table_hasil_akad').DataTable({
      fnDrawCallback: function() {
        var api = this.api();
        setTimeout(function() {
          api.columns().adjust();
        }, 10);
      },
      scrollY: "50vh",
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
        [3, "asc"]
      ],
      columns: [{
          data: null,
          orderable: false,
          searchable: false,
          className: "text-center",
          defaultContent: '<button type="button" class="btn btn-outline-primary btn-sm hasil-akad-detail-toggle"><i class="fas fa-chevron-down"></i></button>'
        },
        {
          data: "no",
          orderable: false,
          searchable: false,
          className: "text-center"
        },
        {
          data: "Aksi",
          orderable: false,
          searchable: false,
          className: "text-center"
        },
        {
          data: "akad_tgl",
          name: "m.akad_tgl"
        },
        {
          data: "nama_konsumen",
          name: "c.nama_konsumen"
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
          data: "tipe_rumah",
          orderable: false
        },
        {
          data: "hargajual",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "harga_kpr_acc",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "pengajuan_outstanding",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "sudah_cair",
          orderable: false,
          searchable: false,
          className: "text-right"
        },
        {
          data: "sisa",
          orderable: false,
          searchable: false,
          className: "text-right font-weight-bold"
        }
      ],
      ajax: {
        url: base_url + 'keuangan/hasil-akad/list/ambil-grouped',
        type: "POST",
        dataType: "json",
        data: function(data) {
          data[csrfName] = csrfHash
          data.id_proyek = activeProyekId()
          data.id_cluster = $("#id_cluster").val()
          data.id_jalan = $("#id_jalan").val()
          data.status_cair = $("#status_cair").val()
        },
        dataSrc: function(r) {
          if (r.token) csrfHash = r.token
          return r.data;
        },
        async: "true"
      }
    });

    $('#data_table_hasil_akad tbody').on('click', '.hasil-akad-detail-toggle', function() {
      const tr = $(this).closest('tr');
      const row = hasilAkadTable.row(tr);

      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        $(this).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        return;
      }

      tr.addClass('shown');
      $(this).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
      loadHasilAkadChild(row, row.data());
    });

    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        hasilAkadTable.search(this.value).draw();
      });

    if (activeProyekId()) {
      $("#id_cluster").prop("disabled", false);
      hasilAkadTable.draw();
    }

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
    $("#id_cluster").on("change", function(e) {
      $('#id_jalan').val(null).trigger('change');
      if (this.value)
        $("#id_jalan").prop("disabled", false)
      else
        $("#id_jalan").prop("disabled", true)
    });

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

    $("#btn_draw").on("click", function(e) {
      hasilAkadDetailCache = {};
      hasilAkadTable.draw();
    })

    $("#status_cair").on("change", function(e) {
      hasilAkadDetailCache = {};
      hasilAkadTable.draw();
    })

    $(".select2-selection__arrow").css("pointer-events", "none")

  });
</script>
