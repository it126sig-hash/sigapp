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
  table,
  tr {
    vertical-align: middle !important;
    /* text-align: center !important; */
    font-size: 10px;
  }

  tr.bbb {
    text-align: right !important;

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
                  <th rowspan="2" id="tb-NO">No</th>
                  <th rowspan="2" id=""></th>
                  <th colspan="2" id="tb-KAVLING">KAVLING</th>
                  <th rowspan="2" id="tb-TYPE">TYPE</th>
                  <th rowspan="2">Nama Konsumen</th>
                  <th rowspan="2">Tanggal Booking</th>
                  <th rowspan="2">TUNAI/KPR</th>
                  <th rowspan="2" id="tb-JML_TAGIHAN">TAGIHAN BELUM LUNAS</th>
                  <th rowspan="2" id="tb-JATUH_TEMPO">JATUH TEMPO TERDEKAT</th>
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
        </div>
      </div>
    </div>
  </section>

  <section>
    <!--#################################### Modal Keuangan #########################################-->
    <div class="modal fade text-left" id="modal_divisi3">
      <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form id="fm-keuangan" class="add-new-record modal-content pt-0" autocomplete="off">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Bayar Tagihan</h5>
            <button type="button" class="close" data-dismiss="modal" id="close_modal_divisi3" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body flex-grow-1" style="background-color:#eee">
            <div class="row">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-body bg-primary text-light">
                    <div class="row">
                      <div class="col-8">
                        <p class="modal-title label_alamat"></p>

                      </div>
                      <div class="col-4">
                        <div class="card">
                          <div class="card-body">
                            <h5><i class="fas fa-users"></i> <span>Konsumen</span></h5>
                            <h5><strong><span id="fm-bayar-label_konsumen"></span></strong></h5>
                            <h5><i class="fas fa-calendar"></i> <span>Tanggal Booking</span></h5>
                            <h5><strong><span id="fm-bayar-label_tgl"></span>(Rp. <span id="fm-bayar-label_bookingfee"></span>)</strong></h5>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-12">
                <div class="card">
                  <div class="card-body pb-0 pt-0">
                    <input type="hidden" class="form-control" name="status_mkdt" id="status_mkdt" value="" />
                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                    <input type="hidden" class="form-control" id="nama_konsumen" name="nama_konsumen" value="" />


                    <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
                      <li class="nav-item active">
                        <a class="nav-link" id="tagihan-tab" data-toggle="tab" href="#tagihan" aria-controls="home"
                          role="tab" aria-selected="true">Tagihan</a>
                      </li>

                      <li class="nav-item">
                        <a class="nav-link" id="log_pembayaran-tab" data-toggle="tab" href="#log_pembayaran"
                          aria-controls="log_pembayaran" role="tab" aria-selected="false">Riwayat Pembayaran</a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="tab-content">
                  <div class="tab-pane active" id="tagihan" aria-labelledby="tagihan-tab" role="tabpanel">
                    <div class="row">
                      <div class="col-md-4 col-sm-12 col-lg-4">
                        <div class="card">
                          <div class="card-body">
                            <div class="divider divider-left">
                              <div class="divider-text font-weight-bold">Status Konsumen</div>
                            </div>
                            <div class="row">
                              <div class="col-9">
                                <h5 class="text-primary">Tandai Sebagai Sudah Lunas</h5>
                              </div>
                              <div class="col-3">
                                <div class="custom-control custom-switch custom-control-inline">
                                  <input type="checkbox" class="custom-control-input cbp" id="is_lunas" name="is_lunas"
                                    value="1" />
                                  <label class="custom-control-label" for="is_lunas"></label>
                                </div>
                              </div>
                            </div>


                            <div class="divider divider-left">
                              <div class="divider-text font-weight-bold">List Tagihan</div>
                            </div>

                            <div id="tb-data-tagihan"></div>

                          </div>
                        </div>


                      </div>
                      <div class="col-md-3 col-sm-12 col-lg-3" hidden>
                        <div class="divider">
                          <div class="divider-text">Total Uang Muka</div>
                        </div>
                        <div class="form-group">
                          <label for="bt-total_biaya_um">Total Tagihan</label>
                          <input readonly type="text" class="form-control num" id="bt-total_biaya_um"
                            name="bt-total_biaya_um">
                        </div>

                        <hr>
                        <div class="form-group">
                          <label for="bt-sudah_bayar_um">Sudah Bayar</label>
                          <input type="text" class="form-control num" readonly id="bt-sudah_bayar_um"
                            name="bt-sudah_bayar_um">
                        </div>
                        <div class="form-group">
                          <label for="bt-sisa_tagihan_um">Sisa Tagihan</label>
                          <input type="text" class="form-control num" readonly id="bt-sisa_tagihan_um"
                            name="bt-sisa_tagihan_um">
                        </div>
                        <div class="form-group">
                          <label for="bt-persentase_bayar_tagihan_um">Persentase</label>
                          <input type="text" class="form-control" style="text-align:right" readonly
                            id="bt-persentase_bayar_tagihan_um" name="bt-persentase_bayar_tagihan_um">
                        </div>
                        <div id="hide_refund">
                          <div class="divider">
                            <div class="divider-text">Refund</div>
                          </div>
                          <div class="form-group">
                            <div class="custom-control custom-switch custom-control-inline">
                              <input type="checkbox" class="custom-control-input cbp" id="refund_paid"
                                name="refund_paid" value="1" />
                              <label class="custom-control-label" for="refund_paid">Pembayaran
                                Selesai</label>
                            </div>
                          </div>
                          <div class="form-group">
                            <label for="keterangan_refund">Keterangan</label>
                            <textarea class="form-control" id="keterangan_refund" name="keterangan_refund"
                              rows="3" placeholder="Keterangan"></textarea>
                          </div>
                          <div class="form-group">
                            <label for="nominal_refund">Nominal</label>
                            <input type="text" class="form-control num" id="nominal_refund"
                              name="nominal_refund">
                          </div>
                          <div class="form-group">
                            <label for="tanggal_refund">Tanggal Refund</label>
                            <input type="text" id="tanggal_refund" name="tanggal_refund"
                              class="form-control flatpickr-human-friendly" placeholder="-" />
                          </div>
                        </div>

                      </div>
                      <div class="col-md-8 col-sm-12 col-lg-8">
                        <div class="row">
                          <div class="col-12">
                            <div class="card">
                              <div class="card-body">
                                <div class="divider divider-left">
                                  <div class="divider-text font-weight-bold">Form Bayar</div>
                                </div>
                                <div class="row">
                                  <div class="col-md-4 col-sm-12 col-lg-4">
                                    <div class="form-group">
                                      <label for="bt-for">Pembayaran Angsuran Ke</label>
                                      <select multiple="multiple" name="bt-for[]" id="bt-for"
                                        class="form-control form-select"></select>
                                    </div>
                                  </div>
                                  <div class="col-md-4 col-sm-12 col-lg-4">
                                    <div class="form-group">
                                      <label for="tanggal_bayar">Tanggal Pembayaran</label>
                                      <input type="text" id="bt-tanggal_bayar_um" name="bt-tanggal_bayar_um"
                                        class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                  </div>
                                  <div class="col-md-4 col-sm-12 col-lg-4">
                                    <div class="form-group">
                                      <label for="sisa_tagihan">Nominal Pembayaran</label>
                                      <input type="text" class="form-control num" id="bt-bayar_tagihan_um"
                                        name="bt-bayar_tagihan_um">
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-12">
                            <div class="card">
                              <div class="card-body">
                                <div class="divider divider-left">
                                  <div class="divider-text font-weight-bold">Alokasi Dana</div>
                                </div>
                                <div class="row">
                                  <div class="col-12">
                                    <div class="p-1 mb-1 rounded border" style="background-color: #f1f1f1ff;">
                                      <div class="row">
                                        <div class="col-4">
                                          <h5>Total harus Dialokasikan</h5>
                                        </div>
                                        <div class="col-8 text-right">
                                          <h5 class="text-success text-right"><strong id="fm-keu-total_dialokasi"></strong></h5>
                                        </div>
                                        <div class="col-4">
                                          <h5>Sisa Belum Dialokasi</h5>
                                        </div>
                                        <div class="col-8 text-right">
                                          <h5 class="text-danger text-right"><strong id="fm-keu-sisa_belum_dialokasi"></strong></h5>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-12">
                                    <button class="btn btn-sm btn-outline-primary mb-1" id="btn-add-item-alokasi" type="button">
                                      <i class="fas fa-plus"></i> Tambah Item
                                    </button>
                                    <div class="table-responsive">
                                      <table class="table table-sm table-bordered">
                                        <thead>
                                          <tr>
                                            <th></th>
                                            <th>Item</th>
                                            <th>Nominal</th>
                                          </tr>
                                        </thead>
                                        <tbody id="tb-alokasi-dana">
                                        </tbody>
                                      </table>
                                    </div>
                                  </div>
                                </div>


                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="card">
                          <div class="card-body">
                            <div class="hide_lunas">
                              <div class="form-group">
                                <label for="berita_acara">Catatan</label>
                                <textarea class="form-control" id="bt-berita_acara_um" name="bt-berita_acara_um"
                                  rows="3" placeholder="Keterangan"></textarea>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                    </div>

                  </div>

                  <div class="tab-pane" id="log_pembayaran" aria-labelledby="log_pembayaran-tab" role="tabpanel">
                    <div class="row">

                      <div class="col-md-12 col-sm-12 col-lg-12">
                        <div class="card">
                          <div class="card-body">
                            <div class="divider divider-left">
                              <div class="divider-text font-weight-bold">Riwayat Pembayaran</div>
                            </div>
                            <div class="table-responsive">
                              <table class="table mb-0">
                                <thead>
                                  <tr>
                                    <th scope="col" class="text-nowrap">No</th>
                                    <th scope="col" class="text-nowrap">Tanggal Bayar</th>
                                    <th scope="col" class="text-nowrap">Nominal</th>
                                    <th scope="col" class="text-nowrap">Berita Acara</th>
                                    <th scope="col" class="text-nowrap">Oleh</th>
                                    <th scope="col" class="text-nowrap"></th>
                                  </tr>
                                </thead>
                                <tbody id="tb-data-log_pembayaran">
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            <button class="add-form-btn-keuangan btn btn-primary data-submit mr-1"
              onclick="save_keuangan(); return false;" href="javascript:void(0)">Simpan</button>
          </div>
        </form>
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
<script src="<?= base_url() ?>assets/js/keuangan.js"></script>
<script>
  let fp = flatpickr(".flatpickr-human-friendly", {
    altInput: true,
    altFormat: 'F j, Y',
    dateFormat: 'Y-m-d'
  })
  $(function() {
    let table = $('#data_table').DataTable({
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
        url: base_url + 'tagihan/list/ambil-grouped',
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

  $('#no').css({
    'min-width': '50px',
    'max-width': '50px'
  });
  $('.bbb').css({
    'min-width': '80px',
    'max-width': '80px'
  });
  $('#blok').css({
    'min-width': '170px',
    'max-width': '170px'
  });
</script>