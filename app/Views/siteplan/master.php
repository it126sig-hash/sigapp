<?php
$k;
$v;
foreach (user()->getRoles() as $key => $val) {
    $k = $key;
    $v = $val;
}

?>

<!-- BEGIN: Vendor CSS-->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/css/richtext.min.css">
<script>
    let sv_url,
        sv_fm,
        sv_btn,
        sv_par,
        wr_pembangunan = [],
        filter = {
            id_cluster: '',
            id_jalan: ''
        },
        filterwarna = {
            Status: null,
            Subsidi: null,
            Komersil: null,
            'Lain-lain': null
        };

    const not_found = "/images/not_found.png"


    // var browser = require("webextension-polyfill");

    // const  nama_perusahaan,
    //             alamat_perusahaan,
    //             kota_perusahaan,
    //             tel_perushaan,


    const rolename = '<?= $v ?>';
    const roleid = '<?= $k ?>';
    const has_akses = JSON.parse('<?= json_encode($has_akses) ?>');
    const pph = JSON.parse('<?= json_encode($pph) ?>')
    const ppn = JSON.parse('<?= json_encode($ppn) ?>')

    let dt_proyek = '<?php echo json_encode($data['proyek']) ?>';
    dt_proyek = JSON.parse(dt_proyek);

    let c_date = new Date();
    let c_date_m = (c_date.getMonth() + 1 > 10) ? c_date.getMonth() + 1 : "0" + (c_date.getMonth() + 1);
    let today_date = c_date.getFullYear() + '-' + c_date_m + '-' + c_date.getDate();

    //convert date to num
    function treatAsUTC(date) {
        let result = new Date(date);
        result.setMinutes(result.getMinutes() - result.getTimezoneOffset());
        return result;
    }

    //cari selisih hari
    function daysBetween(startDate, endDate) {
        let millisecondsPerDay = 24 * 60 * 60 * 1000;
        return (treatAsUTC(endDate) - treatAsUTC(startDate)) / millisecondsPerDay;
    }
    // palidasi manual
    function palid(id, val, msg) {
        if ($("#" + id).val() == val) {
            Swal.fire({
                // 
                icon: 'error',
                title: msg,
                showConfirmButton: false,
                //timer: 1500
            });
            return false;
        }
        return true;

    }
    Date.prototype.toDateInputValue = (function() {
        var local = new Date(this);
        local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
        return local.toJSON().slice(0, 10);
    });

    var conf = JSON.parse('<?= $conf ?>')
</script>
<style>
    @media screen and (max-width: 1366px) {
        html {
            font-size: 70%;
            /* Mengurangi ukuran font global menjadi 90% */
        }
    }

    /* bug drag kanvas malah select text */
    body {
        -webkit-user-select: none;
        /* Safari */
        -ms-user-select: none;
        /* IE 10 and IE 11 */
        user-select: none;
        /* Standard syntax */
    }

    .canvas {
        border: 1px solid black;
        background-color: #eee;
    }

    .float {
        position: fixed;
        width: '100%';
        /* height: 70px; */
        bottom: 40px;
        background-color: #fff;
        border: 1px solid;
        border-radius: 5px;
        text-align: center;
        box-shadow: 2px 2px 3px #999;
        z-index: 1040;
        padding: 5px;
        margin: 5px;
        max-width: 90vw;
        /* left: 50%;
        transform: translateX(-50%); */
    }

    #table-selesai th {
        text-align: center;
        vertical-align: middle;
    }

    .my-float {
        /* margin-top: 22px; */
    }

    .disabled {
        pointer-events: none;
        cursor: default;
    }

    /* div#mkdt,
    div#legal,
    div#lpt {   
        height: 50vh;
        overflow: auto;
        background: #fff;
    } */

    .capitalize {
        text-transform: capitalize;
    }

    .tab-pane.active {
        animation: slide-down 0.3s ease-out;
    }

    @keyframes slide-down {
        0% {
            opacity: 0.5;
            transform: translateX(20px);
        }

        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* The heart of the matter */
    .testimonial-group>.row {
        overflow-x: auto;
        white-space: nowrap;
    }

    .testimonial-group>.row>.col-sm-4 {
        display: inline-block;
        float: none;
    }

    /* Decorations */
    .col-sm-4 {
        color: #fff;
        font-size: 48px;
        padding-bottom: 20px;
        padding-top: 18px;
    }

    .col-sm-4:nth-child(3n+1) {
        background: #c69;
    }

    .col-sm-4:nth-child(3n+2) {
        background: #9c6;
    }

    .col-sm-4:nth-child(3n+3) {
        background: #69c;
    }

    /* Menyusun button dengan posisi menempel di kanan tengah */
    .center-right {
        position: fixed;
        top: 50%;
        right: 0;
        transform: translate(0, -50%);
    }

    #menu {
        display: none;
        position: absolute;
        width: 80px;
        background-color: white;
        box-shadow: 0 0 5px grey;
        border-radius: 3px;
    }

    #menu button {
        width: 100%;
        background-color: white;
        border: none;
        margin: 0;
        padding: 10px;
    }

    #menu button:hover {
        background-color: lightgray;
    }

    #div_filter {
        overflow-y: scroll;
    }

    /* untuk modal  */
    #modalEwe .modal-dialog {
        width: 100%;
        height: 50%;
        margin: 0;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
    }

    #modalEwe .modal-content {
        height: 100%;
    }

    #modalEwe .modal.fade .modal-dialog {
        transform: translateY(-100%);
        transition: transform 0.3s ease-out;
    }

    #modalEwe .modal.show .modal-dialog {
        transform: translateY(0);
    }

    .foto-container {
        min-height: 250px;
        height: auto;
    }

    .file-container {
        min-height: 150px;
        height: auto;
    }

    .input-foto-container {
        width: 350px;
        height: 150px;
        display: flex;
        flex-direction: row;
    }

    .input-foto {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .flatpickr-human-friendly:enabled {
        background-color: #fff !important;
    }


    .input-loading {
        filter: blur(3px);
        transition: filter 0.3s ease;
        /* untuk animasi */

        /* background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loadingShimmer 1.5s infinite;
        color: transparent !important;
        position: relative; */
    }

    .divider-left {
        border-left: 4px solid #007bff;
        padding-left: 15px;
        margin-bottom: 20px;
    }

    @keyframes loadingShimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    .guarantee-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
</style>
<!-- END: Vendor CSS-->

<!-- BEGIN: Content-->
<div class="app-content content ">
    <div id="menu_here"></div>

    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0">Siteplan</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url("siteplan") ?>">Siteplan</a>
                                </li>
                                <li class="breadcrumb-item active" id="br_siteplan">Index</li>
                            </ol>
                        </div>
                    </div>

                </div>
            </div>
            <!-- <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none">
                    <div class="form-group breadcrumb-right">
                        <div class="dropdown">
                            <button class="btn-icon btn btn-primary btn-round btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i data-feather="grid"></i></button>
                            <div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item" href="javascript:void(0);"><i class="mr-1" data-feather="check-square"></i><span class="align-middle">Todo</span></a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-1" data-feather="message-square"></i><span class="align-middle">Chat</span></a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-1" data-feather="mail"></i><span class="align-middle">Email</span></a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-1" data-feather="calendar"></i><span class="align-middle">Calendar</span></a></div>
                        </div>
                    </div>
                </div> -->
        </div>
        <div class="content-body">
            <!-- Kick start -->
            <div class="card">
                <!-- <div class="card-header">
                        <h4 class="card-title">ASI Ext</h4>
                    </div> -->
                <div class="card-body">
                    <div class="row">
                        <div style="overflow: auto" class="hidden">
                            <div style="float: left; margin-right: 10px;">Blur radius: </div>
                            <input id="blurRadius" type="text" onchange="onRadiusChange.apply(this, arguments)"
                                style="float: left; width: 20px; margin-right: 10px;" />
                            <div id="threshold"></div>
                        </div>
                        <div class="col-md-9">
                            <div id="stage-parent">
                                <div class="canvas" id="konva-holder"></div>
                            </div>
                            <div id="menu">
                                <div>
                                    <button id="menu-btn-lihat_detail">Detail</button>
                                    <!-- <button id="menu-btn-input">Isi/Ubah</button> -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 d-md-block" style="overflow-y:auto" id="filter-side">
                            <div class="form-group">
                                <select id="pilih-divisi" class="form-control-sm select2">
                                    <option value="0">Pilih Divisi</option>
                                    <!-- <option value="8" class="dropdown-item">Sales & Promotion</option> -->
                                    <option value="7" class="dropdown-item">Produksi</option>
                                    <option value="4" class="dropdown-item">Marketing Data</option>
                                    <option value="5" class="dropdown-item">Legal & Pertanahan</option>
                                    <option value="10" class="dropdown-item">Pajak</option>
                                    <option value="3" class="dropdown-item">Keuangan</option>
                                    <option value="6" class="dropdown-item">Planning</option>
                                    <!-- <option value="9" class="dropdown-item">Management</option> -->
                                </select>
                                <button onclick="load_kavling()" class="btn btn-sm btn-primary col-12 mt-1">
                                    Muat Ulang Kavling
                                </button>
                            </div>

                            <div class="divider">
                                <div class="divider-text">FilterCluster</div>
                            </div>

                            <div class="form-group">
                                <select id="filter-id_cluster" name="id_cluster"
                                    class="select2 select-sm form-control-sm"></select>
                            </div>
                            <div class="form-group">
                                <select disabled id="filter-id_jalan" name="id_jalan"
                                    class="select-sm form-control-sm select2 "></select>
                            </div>
                            <div class="form-group row">
                                <button class="btn btn-primary col-5 ml-1 mt-1 mb-1 btn-sm "
                                    onclick="filter_option()">Filter Data</button>
                                <button class="btn btn-outline-warning col-5 m-1 btn-sm "
                                    onclick="hapus_filter_option()">Hapus Filter</button>
                            </div>
                            <hr>
                            <div id="keterangan-warna-here"></div>
                            <hr>
                            <div class="form-group">
                                <!-- <button onclick="renderText()" class="btn btn-outline-warning  col-12" id="btn-renderText"> Tampilkan Keterangan Warna Di Siteplan</button> -->
                                <button onclick="export_siteplan()" class="btn btn-sm btn-outline-primary  col-12"
                                    id="btn-export-siteplan"> Export Siteplan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Kick start -->
    </div>
</div>
<div id="btn-filter" class="btn hidden btn-primary center-right p-1" onclick="open_setting()">
    <div class="spinner feather feather-settings"><i data-feather="settings"></i></div>
</div>
</div>

<!-- Modal to add new record -->
<?php if ($k == 1 || $k == 6): ?>
    <?php echo view('siteplan/planning'); ?>
<?php endif; ?>

<?php if ($k == 7 || $k == 1): ?>
    <!--#################################### Modal Produksi #########################################-->
    <?php echo view('siteplan/produksi'); ?>
<?php endif; ?>

<?php if ($k == 8 || $k == 1): ?>
    <?php echo view('siteplan/sales'); ?>
<?php endif; ?>

<div class="modal fade" id="modal_komplain_sales">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form id="fm-komplain-sales" enctype="multipart/form-data" class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Komplain Kavling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_alamat" id="label_alamat5"></p>
                <hr>

                <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                <input type="hidden" class="form-control" id="id_komplain" name="id_komplain" value="" />
                <small id="last_update_komplain_sales" class="text-muted"></small>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="fmks-komplain-tab" data-toggle="tab" href="#fmks-komplain"
                            aria-controls="fmks-komplain" role="tab" aria-selected="true">Komplain</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="fmks-ditangani-tab" data-toggle="tab" href="#fmks-ditangani"
                            aria-controls="fmks-ditangani" role="tab" aria-selected="true">Ditangani</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="fmks-selesai-tab" data-toggle="tab" href="#fmks-selesai"
                            aria-controls="fmks-ditangani" role="tab" aria-selected="true">Selesai</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="fmks-komplain" aria-labelledby="fmks-komplain-tab" role="tabpanel">
                        <!-------------------------------------- dikomplain  ------------------------------------->
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div id="div_upload_komplain_sales">
                                    <label for="label_upload_komplain_sales">Foto Komplain</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="image/*"
                                            name="upload_komplain_sales[]" id="upload_komplain_sales" multiple />
                                        <label class="custom-file-label" id="label_upload_komplain_sales"
                                            for="upload_komplain_sales">Bisa Lebih dari 1 foto</label>
                                        <div id="list_upload_komplain_sales"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="keterangan_komplain">Keterangan Komplain</label>
                                    <textarea class="form-control" id="keterangan_komplain" name="keterangan_komplain"
                                        rows="3" placeholder="Keterangan"></textarea>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div id="batal_komplain" class="hidden">
                                    <button id="batal_komplain_btn" type="button"
                                        class="btn btn-outline-danger btn-block waves-effect"
                                        onclick="batal_komplain()">Batalkan Komplain</button>
                                    <div class="form-group">
                                        <label for="username_komplain_oleh">Dikomplain Oleh</label>
                                        <input readonly type="text" class="form-control" id="username_komplain_oleh"
                                            name="username_komplain_oleh" />
                                    </div>
                                    <div class="form-group">
                                        <label for="komplain_tgl">Tanggal Komplain</label>
                                        <input disabled type="text" class="form-control flatpickr-human-friendly"
                                            id="komplain_tgl" name="komplain_tgl" />
                                    </div>
                                    <div id="control_sales_foto_komplain_sales" class="carousel slide">
                                        <div class="carousel-inner" id="foto_komplain_sales">
                                            <!-- Foto komplain belongs here -->
                                        </div>
                                        <a class="carousel-control-prev" href="#control_sales_foto_komplain_sales"
                                            role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#control_sales_foto_komplain_sales"
                                            role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="fmks-ditangani" aria-labelledby="fmks-ditangani-tab" role="tabpanel">
                        <!-------------------------------------- komplain ditangani  ------------------------------------->
                        <div class="divider">
                            <div class="divider-text">Komplain Ditangani</div>
                        </div>
                        <div id="komplain_ditangani_sales" class="hidden">
                            <div class="form-group">
                                <label for="keterangan_ditangani">Keterangan</label>
                                <textarea disabled class="form-control" id="keterangan_ditangani"
                                    name="keterangan_ditangani" rows="3" placeholder="Keterangan"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="username_ditangani_oleh">Komplain Diterima Oleh</label>
                                <input disabled type="text" class="form-control" id="username_ditangani_oleh"
                                    name="username_ditangani_oleh" />
                            </div>
                            <div class="form-group">
                                <label for="ditangani_tgl">Tanggal Komplain Diterima</label>
                                <input disabled type="text" class="form-control flatpickr-human-friendly"
                                    id="ditangani_tgl" name="ditangani_tgl" />
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="fmks-selesai" aria-labelledby="fmks-selesai-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <!-- ---------------------------------------- komplain diselesaikan ---------------------------->
                                <div id="selesaikan_komplain_div_sales" class="hidden">
                                    <div class="divider">
                                        <div class="divider-text">Komplain diselesaikan Produksi</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="selesai_keterangan_produksi">Keterangan </label>
                                        <textarea disabled class="form-control" id="selesai_keterangan_produksi"
                                            name="selesai_keterangan_produksi" rows="3"
                                            placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="username_selesai_oleh_produksi">Diselesakan Oleh</label>
                                        <input disabled type="text" class="form-control"
                                            id="username_selesai_oleh_produksi" name="username_selesai_oleh_produksi" />
                                    </div>
                                    <div class="form-group">
                                        <label for="selesai_tgl_produksi">Tanggal Diselesaikan</label>
                                        <input disabled type="text" class="form-control flatpickr-human-friendly"
                                            id="selesai_tgl_produksi" name="selesai_tgl_produksi" />
                                    </div>
                                    <div id="controls_sales_foto_komplain_produksi" class="carousel slide">
                                        <div class="carousel-inner" id="foto_komplain_produksi">
                                            <!-- Foto komplain belongs here -->
                                        </div>
                                        <a class="carousel-control-prev" href="#controls_sales_foto_komplain_produksi"
                                            role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#controls_sales_foto_komplain_produksi"
                                            role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <!-- ---------------------------------------- komplain diselesaikan ---------------------------->

                                <div class="divider">
                                    <div class="divider-text">Selesaikan Komplain</div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" value="1" class="custom-control-input"
                                            id="is_selesai_sales" name="is_selesai_sales" />
                                        <label class="custom-control-label" for="is_selesai_sales">Selesaikan
                                            Komplain</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="selesai_keterangan_sales">Keterangan </label>
                                    <textarea class="form-control" id="selesai_keterangan_sales"
                                        name="selesai_keterangan_sales" rows="3" placeholder="Keterangan"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="username_selesai_oleh_sales">Diselesakan Oleh</label>
                                    <input disabled type="text" class="form-control" id="username_selesai_oleh_sales"
                                        name="username_selesai_oleh_sales" />
                                </div>
                                <div class="form-group">
                                    <label for="selesai_tgl_sales">Tanggal Diselesaikan</label>
                                    <input disabled type="text" class="form-control flatpickr-human-friendly"
                                        id="selesai_tgl_sales" name="selesai_tgl_sales" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="komplain-sales-form-btn" class="btn btn-primary data-submit mr-1"
                    onclick="save_komplain_sales()" href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php if ($k == 5 || $k == 1): ?>
    <?php echo view('siteplan/legal'); ?>
<?php endif; ?>

<?php if ($k == 4 || $k == 1): ?>
    <?php echo view('siteplan/mkdt'); ?>
<?php endif; ?>

<?php if ($k == 9 || $k == 1): ?>
    <?php echo view('siteplan/direksi'); ?>
<?php endif; ?>

<?php if ($k == 3 || $k == 1): ?>
    <?php echo view('siteplan/keuangan'); ?>
<?php endif; ?>

<?php if ($k == 10 || $k == 1): ?>
    <?php echo view('siteplan/pajak'); ?>
<?php endif; ?>

<!-- modal detail kavling -->
<div class="modal fade" id="modal_detail">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="add-new-record modal-content pt-0">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Detail Kavling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1" style="background-color:#eee">
                <form action="" ></form>
                <div class="row" id="fm-detail">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Detail Kavling</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text label_alamat" id="detail_kavling_header"></p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"></h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Promo</h6>
                                            <span id="dt-promo">-</span>
                                        </div>
                                    </li>
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Tunai/KPR</h6>
                                            <span id="dt-is_kpr">-</span>
                                        </div>
                                    </li>
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Subsidi/Komersil</h6>
                                            <span id="dt-is_subsidi">-</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h5>Detail Konsumen</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">No SPPTB</h6>
                                            <span id="dt-no_spptb">-</span>
                                        </div>
                                    </li>
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Nama Konsumen</h6>
                                            <span id="dt-nama_konsumen">-</span>
                                        </div>
                                    </li>
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Alamat Konsumen</h6>
                                            <span id="dt-alamat_konsumen">-</span>
                                        </div>
                                    </li>
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">NIK</h6>
                                            <span id="dt-nik_konsumen">-</span>
                                        </div>
                                    </li>
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">NPWP</h6>
                                            <span id="dt-npwp_konsumen">-</span>
                                        </div>
                                    </li>
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Kontak Konsumen</h6>
                                            <span id="dt-hp_konsumen">-</span>
                                        </div>
                                    </li>
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Email Konsumen</h6>
                                            <span id="dt-email_konsumen">-</span>
                                        </div>
                                    </li>
                                    <li class="mb-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Sales</h6>
                                            <span id="dt-sales">-</span>
                                        </div>
                                    </li>

                                </ul>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-body pb-0 pt-0">
                                <ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="dtt-summary-tab" data-toggle="tab" href="#dtt-summary"
                                            aria-controls="summary" role="tab" aria-selected="true">Ringkasan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dtt-hj-tab" data-toggle="tab" href="#dtt-hj"
                                            aria-controls="dj" role="tab" aria-selected="true">Harga Jual</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-stdetail-tab" data-toggle="tab" href="#dt-stdetail"
                                            aria-controls="dt-stdetail-dt" role="tab" aria-selected="false">Status</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-tagihan-tab" data-toggle="tab" href="#dt-tagihan"
                                            aria-controls="tgt" role="tab" aria-selected="false">Tagihan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-cashout-tab" data-toggle="tab" href="#dt-cashout"
                                            aria-controls="cashout-tab" role="tab" aria-selected="false">Cashout</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-legal-tab" data-toggle="tab" href="#dt-legal"
                                            aria-controls="legal" role="tab" aria-selected="false">Legal</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-produksi-tab" data-toggle="tab" href="#dt-produksi"
                                            aria-controls="produksi" role="tab" aria-selected="false">Bangunan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="dt-pajak-tab" data-toggle="tab" href="#dt-pajak"
                                            aria-controls="pajak" role="tab" aria-selected="false">Bukti Bayar PPH/PPN</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card col-md-12">
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="dtt-summary" aria-labelledby="dtt-summary-tab"
                                        role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="divider divider-left">
                                                    <div class="divider-text font-weight-bold">Status</div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label mb-0">Status Kavling</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-status_mkdt"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Bank</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-st_bank"></span>
                                                    </div>

                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Notaris</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-notaris"></span>
                                                    </div>
                                                </div>
                                                <div class="divider divider-left">
                                                    <div class="divider-text font-weight-bold">SP3K</div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Tanggal terbit SP3K</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-st_sp3k_tgl"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Tanggal kadaluarsa SP3K</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-st_sp3k_tgl_exp"></span>
                                                    </div>
                                                </div>
                                                <div class="divider divider-left">
                                                    <div class="divider-text font-weight-bold">Standing Instruction</div>
                                                </div>
                                                <div id="s-si"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="divider divider-left">
                                                    <div class="divider-text font-weight-bold">Keuangan</div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Uang Muka</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-persentase_bayar_tagihan_um"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Biaya Adm + Turun KPR + Kav. Strageis + Kelebihan Tanah</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-persentase_bayar_tagihan_um_ll"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Biaya-biaya</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-persentase_bayar_tagihan_bb"></span>
                                                    </div>
                                                </div>
                                                <div class="divider divider-left">
                                                    <div class="divider-text font-weight-bold">Cashout</div>
                                                </div>
                                                <div id="s-co"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="divider divider-left">
                                                    <div class="divider-text font-weight-bold">Bangunan</div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Progres Bangunan</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-progress_bangunan"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Tanggal Turun Pembangunan</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-perintah_bangun_tgl"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Tanggal Pembangunan</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-tanggal_pembangunan"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Tanggal Selesai Pembangunan</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-tanggal_selesai_pembangunan"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Saluran Jalan</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-st_saluran"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Air</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-st_air"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">Listrik</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-st_jalan"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">SLO/NIDI</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-slo"></span>
                                                    </div>
                                                </div>
                                                <div class="info-row row no-gutters">
                                                    <div class="col-6">
                                                        <label class="info-label">LPA</label>
                                                    </div>
                                                    <div class="col-6">
                                                        : <span class="info-value" id="s-lpa"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane" id="dtt-hj" aria-labelledby="dtt-hj-tab"
                                        role="tabpanel">
                                        <h5>Harga Jual</h5>
                                        <small class="text-muted">Terakhir diperbaharui oleh</small>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="divider">
                                                    <div class="divider-text">Pricelist</div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Tanggal
                                                        PriceList</label>
                                                    <input type="text"
                                                        class="form-control text-right dt-pl_fm flatpickr-human-friendly"
                                                        id="dt-pl_tgl_harga" disabled name="dt-pl_tgl_harga" value=""
                                                        readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Harga
                                                        Jual</label>
                                                    <input type="text" class="form-control num dt-pl_fm"
                                                        id="dt-pl_hargajual" name="dt-pl_hargajual" value="" readonly />
                                                </div>
                                                <div class="form-group" id="hjdis">
                                                    <label class="form-label" for="basic-icon-default-fullname">Diskon
                                                        Harga
                                                        Jual</label>
                                                    <input type="text" class="form-control num dt-pl_fm"
                                                        id="dt-pl_harga_diskon_hargajual"
                                                        name="dt-pl_harga_diskon_hargajual" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Harga
                                                        Jual Net</label>
                                                    <input type="text" class="form-control num dt-pl_fm"
                                                        id="dt-pl_hargajual_net" name="dt-pl_hargajual_net" value=""
                                                        readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="basic-icon-default-fullname">KPR</label>
                                                    <input type="text" class="form-control num dt-pl_fm" id="dt-pl_kpr"
                                                        name="dt-pl_kpr" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Uang
                                                        Muka</label>
                                                    <input type="text" class="form-control num dt-pl_fm"
                                                        id="dt-pl_uang_muka" name="dt-pl_uang_muka" value="" readonly />
                                                </div>
                                                <div class="form-group" id="umdis">
                                                    <label class="form-label" for="basic-icon-default-fullname">Diskon
                                                        Uang Muka</label>
                                                    <input type="text" class="form-control num dt-pl_fm"
                                                        id="dt-pl_harga_diskon_uang_muka"
                                                        name="dt-pl_harga_diskon_uang_muka" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Biaya
                                                        Adm</label>
                                                    <input type="text" class="form-control num dt-pl_fm"
                                                        id="dt-pl_biaya_adm" name="dt-pl_biaya_adm" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label for="total_biaya2">PPN</label>
                                                    <input type="text" class="form-control num dt-pl_fm totalbb"
                                                        id="dt-pl_ppn" name="dt-pl_ppn" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="basic-icon-default-fullname">BPHTB</label>
                                                    <input type="text" class="form-control num dt-pl_fm totalbb"
                                                        id="dt-pl_bphtb" name="dt-pl_bphtb" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Biaya
                                                        Proses</label>
                                                    <input type="text" class="form-control num dt-pl_fm totalbb"
                                                        id="dt-pl_biaya_proses" name="dt-pl_biaya_proses" value=""
                                                        readonly />
                                                </div>



                                            </div>
                                            <div class="col-md-4">
                                                <div class="divider">
                                                    <div class="divider-text">Harga Jual (SPPTB)</div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Tanggal
                                                        PriceList</label>
                                                    <input type="text"
                                                        class="form-control text-right dt-fm flatpickr-human-friendly"
                                                        id="dt-tgl_harga" disabled name="dt-tgl_harga" value=""
                                                        readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Harga
                                                        Jual</label>
                                                    <input type="text" class="form-control num dt-fm" id="dt-hargajual"
                                                        name="dt-hargajual" value="" readonly />
                                                </div>
                                                <div class="form-group" id="hjdis">
                                                    <label class="form-label" for="basic-icon-default-fullname">Diskon
                                                        Harga
                                                        Jual</label>
                                                    <input type="text" class="form-control num dt-fm"
                                                        id="dt-harga_diskon_hargajual" name="dt-harga_diskon_hargajual"
                                                        value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Harga
                                                        Jual Net</label>
                                                    <input type="text" class="form-control num dt-fm"
                                                        id="dt-hargajual_net" name="dt-hargajual_net" value=""
                                                        readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="basic-icon-default-fullname">KPR</label>
                                                    <input type="text" class="form-control num dt-fm" id="dt-kpr"
                                                        name="dt-kpr" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Uang
                                                        Muka</label>
                                                    <input type="text" class="form-control num dt-fm" id="dt-uang_muka"
                                                        name="dt-uang_muka" value="" readonly />
                                                </div>
                                                <div class="form-group" id="umdis">
                                                    <label class="form-label" for="basic-icon-default-fullname">Diskon
                                                        Uang Muka</label>
                                                    <input type="text" class="form-control num dt-fm"
                                                        id="dt-harga_diskon_uang_muka" name="dt-harga_diskon_uang_muka"
                                                        value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Biaya
                                                        Adm</label>
                                                    <input type="text" class="form-control num dt-fm" id="dt-biaya_adm"
                                                        name="dt-biaya_adm" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label for="total_biaya2">PPN</label>
                                                    <input type="text" class="form-control num dt-fm totalbb"
                                                        id="dt-ppn" name="dt-ppn" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="basic-icon-default-fullname">BPHTB</label>
                                                    <input type="text" class="form-control num dt-fm totalbb"
                                                        id="dt-bphtb" name="dt-bphtb" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="basic-icon-default-fullname">Biaya
                                                        Proses</label>
                                                    <input type="text" class="form-control num dt-fm totalbb"
                                                        id="dt-biaya_proses" name="dt-biaya_proses" value="" readonly />
                                                </div>
                                                <!-- <div class="form-group">
                                                    <label class="form-label"
                                                        for="basic-icon-default-fullname">ROW</label>
                                                    <input type="text" class="form-control num dt-fm" id="dt-row"
                                                        name="dt-row" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="basic-icon-default-fullname">Tipe</label>
                                                    <input type="text" class="form-control dt-fm text-right"
                                                        id="dt-tipe" name="dt-tipe" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="basic-icon-default-fullname">LB</label>
                                                    <input type="text" class="form-control num dt-fm" id="dt-lb"
                                                        name="dt-lb" value="" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="basic-icon-default-fullname">LT</label>
                                                    <input type="text" class="form-control num dt-fm" id="dt-lt"
                                                        name="dt-lt" value="" readonly />
                                                </div> -->


                                            </div>

                                            <div class="col-md-4">
                                                <div class="divider">
                                                    <div class="divider-text">KPR</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="total_biaya2">KPR Disetujui</label>
                                                    <input readonly type="text" class="form-control num"
                                                        id="dt-st_harga_kpr_acc" name="dt-st_harga_kpr_acc">
                                                </div>
                                                <div class="form-group">
                                                    <label for="total_biaya2">Turun KPR</label>
                                                    <input readonly type="text" class="form-control num "
                                                        id="dt-st_harga_penambahan_um" name="dt-st_harga_penambahan_um">
                                                </div>
                                                <div class="divider">
                                                    <div class="divider-text">Penambahan Biaya</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="total_biaya2">Biaya Kavling Strategis</label>
                                                    <input type="text" readonly class="form-control num "
                                                        id="dt-st_harga_penambahan" name="dt-st_harga_penambahan">
                                                </div>
                                                <div class="form-group">
                                                    <label for="total_biaya2">Biaya Kelebihan Tanah</label>
                                                    <input type="text" readonly class="form-control num "
                                                        id="dt-st_harga_penambahan_tanah"
                                                        name="dt-st_harga_penambahan_tanah">
                                                </div>
                                                <div class="form-group hidden">
                                                    <label for="total_biaya2">Keterangan Penambahan Biaya</label>
                                                    <textarea readonly name="dt-st_keterangan_harga_penambahan"
                                                        id="dt-st_keterangan_harga_penambahan" class="form-control "
                                                        cols="30" rows="2"></textarea>
                                                </div>
                                                <div class="divider">
                                                    <div class="divider-text">Dokumen</div>
                                                </div>
                                                <div class="form-group">
                                                    <label>KTP</label>
                                                    <div>
                                                        <a href="#" class="btn btn-primary" id="dt-btn-ktp_here"
                                                            class="files-here dt-cl-ktp_here" target=_blank>
                                                            Klik untuk lihat file
                                                            <embed src="" style="width: 90%;"
                                                                class="files-here dt-cl-ktp_here">
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>NPWP</label>
                                                    <div>
                                                        <a href="#" class="btn btn-primary" id="dt-btn-npwp_here"
                                                            class="files-here dt-cl-npwp_here" target=_blank>
                                                            Klik untuk lihat file
                                                            <embed src="" style="width: 90%;"
                                                                class="files-here dt-cl-npwp_here">
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Berkas Lainnya</label>
                                                    <div>
                                                        <a href="#" class="btn btn-primary" id="dt-btn-bl_here"
                                                            class="files-here dt-cl-bl_here" target=_blank>
                                                            Klik untuk lihat file
                                                            <embed src="" style="width: 90%;"
                                                                class="files-here dt-cl-bl_here">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="tab-pane" id="dt-cashout" aria-labelledby="dt-cashout-tab"
                                        role="tabpanel">
                                        <div id="dt-cashout-here"></div>
                                    </div>
                                    <div class="tab-pane" id="dt-stdetail" aria-labelledby="dt-stdetail-tab"
                                        role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="divider">
                                                    <div class="divider-text">Status Kavling</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="status_kavling">Status</label>
                                                    <select required class="form-control" id="dt-status_mkdt"
                                                        name="dt-status_mkdt" disabled>
                                                        <option value="">-</option>
                                                        <option value="Booking">Booking</option>
                                                        <option value="Akad">Akad</option>
                                                        <option value="Batal">Batal</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_booking_fee">Notaris</label>
                                                    <input type="text" disabled class="form-control" id="dt-notaris"
                                                        name="dt-notaris">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_booking_fee">PPJB/AJB</label>
                                                    <input type="text" disabled class="form-control" id="dt-is_ajb"
                                                        name="dt-is_ajb">
                                                </div>
                                                <div class="divider">
                                                    <div class="divider-text">Booking</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_booking_tgl">Tanggal Booking</label>
                                                    <input type="text" id="dt-st_booking_tgl" name="dt-st_booking_tgl"
                                                        class="form-control flatpickr-human-friendly" placeholder="-"
                                                        disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_booking_fee">Booking Fee</label>
                                                    <input type="text" disabled class="form-control num"
                                                        id="dt-st_booking_fee" name="dt-st_booking_fee">
                                                </div>
                                                <div class="divider">
                                                    <div class="divider-text">Wawancara</div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="dt-st_wawancara" name="dt-st_wawancara" value="1"
                                                            disabled />
                                                        <label class="custom-control-label" for="dt-st_wawancara">Sudah
                                                            Wawancara</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_bank">Bank</label>
                                                    <input type="text" id="dt-st_bank" name="dt-st_bank"
                                                        class="form-control" placeholder="-" disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_wawancara_tgl">Tanggal Wawancara</label>
                                                    <input type="text" id="dt-st_wawancara_tgl"
                                                        name="dt-st_wawancara_tgl"
                                                        class="form-control flatpickr-human-friendly" placeholder="-"
                                                        disabled />
                                                </div>


                                            </div>

                                            <div class="col-md-4">
                                                <div class="divider">
                                                    <div class="divider-text">SP3K</div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select class="form-control" id="dt-st_mkdt_keterangan"
                                                        name="dt-st_mkdt_keterangan" disabled>
                                                        <option value="">-</option>
                                                        <option value="Disetujui">Disetujui</option>
                                                        <option value="Ditolak">Ditolak</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_harga_kpr">Pengajuan</label>
                                                    <input type="text" id="dt-st_harga_kpr" name="dt-st_harga_kpr"
                                                        class="form-control num" placeholder="-" disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_acc_harga_kpr">Disetujui</label>
                                                    <input type="text" id="dt-st_acc_harga_kpr"
                                                        name="dt-st_acc_harga_kpr" class="form-control num"
                                                        placeholder="-" disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_harga_turun_kpr">Turun KPR</label>
                                                    <input type="text" id="dt-st_harga_turun_kpr"
                                                        name="dt-st_harga_turun_kpr" class="form-control num"
                                                        placeholder="-" disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_sp3k_no">No SP3K</label>
                                                    <input type="text" id="dt-st_sp3k_no" name="dt-st_sp3k_no"
                                                        class="form-control" placeholder="-" disabled />
                                                </div>
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" id="dt-sp3k"
                                                            name="dt-sp3k" value="1" disabled />
                                                        <label class="custom-control-label" for="dt-sp3k">SP3K</label>
                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <label for="dt-st_sp3k_tgl">Tanggal Terbit</label>
                                                    <input type="text" id="dt-st_sp3k_tgl" name="dt-st_sp3k_tgl"
                                                        class="form-control flatpickr-human-friendly" placeholder="-"
                                                        disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_sp3k_tgl_exp">Tanggal Kadaluarsa</label>
                                                    <input type="text" id="dt-st_sp3k_tgl_exp" name="dt-st_sp3k_tgl_exp"
                                                        class="form-control flatpickr-human-friendly" placeholder="-"
                                                        disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label>SP3K</label>
                                                    <div class="custom-file">
                                                        <a href="" class="btn btn-primary" target=_blank
                                                            id="dt-st_list-upload_sp3k_file">Klik untuk lihat file
                                                            <!-- <embed src="" id="dt-st_list-upload_sp3k_file-here" class="files-here" width="80%"> -->
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="divider">
                                                    <div class="divider-text">Perintah Bangun</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_perintah_bangun_tgl">Tanggal Perintah
                                                        Bangun</label>
                                                    <input type="text" disabled="disabled"
                                                        id="dt-st_perintah_bangun_tgl" name="dt-st_perintah_bangun_tgl"
                                                        class="form-control flatpickr-human-friendly" placeholder="-"
                                                        disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_perintah_bangun_oleh">Oleh</label>
                                                    <input type="text" disabled="disabled"
                                                        id="dt-st_perintah_bangun_oleh"
                                                        name="dt-st_perintah_bangun_oleh" class="form-control"
                                                        placeholder="-" disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label>Perintah Bangun</label>
                                                    <div class="custom-file">
                                                        <a href="#" class="btn btn-primary" target=_blank
                                                            id="dt-st_list-upload_perintah_bangun_file">Klik untuk lihat
                                                            file
                                                            <!-- <embed src="" style="width: 80%;" class="files-here" id="dt-st_list-upload_perintah_bangun_file-here"> -->
                                                        </a>
                                                    </div>
                                                </div>

                                                <div class="divider">
                                                    <div class="divider-text">Akad</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-st_rencana_akad_tgl">Rencana Akad</label>
                                                    <input type="text" id="dt-st_rencana_akad_tgl"
                                                        name="dt-st_rencana_akad_tgl"
                                                        class="form-control flatpickr-human-friendly" placeholder="-"
                                                        disabled />
                                                </div>

                                                <div class="form-group">
                                                    <div class="custom-control custom-switch custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="dt-st_akad" name="dt-st_akad" value="1" disabled />
                                                        <label class="custom-control-label"
                                                            for="dt-st_akad">Akad</label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="dt-st_akad_tgl">Tanggal Akad</label>
                                                    <input type="text" id="dt-st_akad_tgl" name="dt-st_akad_tgl"
                                                        class="form-control flatpickr-human-friendly" placeholder="-"
                                                        disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label>No Debitur</label>
                                                    <input type="text" id="dt-st_debitur_no" name="dt-st_debitur_no"
                                                        class="form-control" placeholder="-" disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label>No BAST</label>
                                                    <input type="text" id="dt-st_bast_no" name="dt-st_bast_no"
                                                        class="form-control" placeholder="-" disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label>BAST</label>

                                                    <a href="" class="btn btn-primary" target=_blank
                                                        id="dt-st_list-upload_bast_file">Klik
                                                        untuk
                                                        lihat file</a>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dt-tagihan" aria-labelledby="dt-tagihan-tab"
                                        role="tabpanel">
                                        <small id="last_update_keuangan" class="text-muted"></small>
                                        <div class="row">
                                            <div class="col-md-4 col-sm-12 col-lg-4">
                                                <div class="divider">
                                                    <div class="divider-text">Total Uang Muka</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-total_biaya_um">Total Uang Muka</label>
                                                    <input readonly type="text" class="form-control num"
                                                        id="dt-total_biaya_um" name="dt-total_biaya_um">
                                                </div>

                                                <hr>
                                                <div class="form-group">
                                                    <label for="dt-sudah_bayar_um">Sudah Bayar Uang Muka</label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sudah_bayar_um" name="dt-sudah_bayar_um">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-sisa_tagihan_um">Sisa Tagihan Uang Muka</label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sisa_tagihan_um" name="dt-sisa_tagihan_um">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-persentase_bayar_tagihan_um">Persentase</label>
                                                    <input type="text" class="form-control" style="text-align:right"
                                                        readonly id="dt-persentase_bayar_tagihan_um"
                                                        name="dt-persentase_bayar_tagihan_um">
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-12 col-lg-4">
                                                <div class="divider">
                                                    <div class="divider-text">Total Biaya Adm + Turun KPR + Hook</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-total_biaya_um">Total Tagihan</label>
                                                    <input readonly type="text" class="form-control num"
                                                        id="dt-total_biaya_um_ll" name="dt-total_biaya_um_ll">
                                                </div>

                                                <hr>
                                                <div class="form-group">
                                                    <label for="dt-sudah_bayar_um">Sudah Bayar </label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sudah_bayar_um_ll" name="dt-sudah_bayar_um_ll">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-sisa_tagihan_um">Sisa Tagihan</label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sisa_tagihan_um_ll" name="dt-sisa_tagihan_um_ll">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-persentase_bayar_tagihan_um">Persentase</label>
                                                    <input type="text" class="form-control" style="text-align:right"
                                                        readonly id="dt-persentase_bayar_tagihan_um_ll"
                                                        name="dt-persentase_bayar_tagihan_um_ll">
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-12 col-lg-4">

                                                <div class="divider">
                                                    <div class="divider-text">Total Biaya-biaya</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-total_biaya_bb">Total Biaya-biaya</label>
                                                    <input readonly type="text" class="form-control num"
                                                        id="dt-total_biaya_bb" name="dt-total_biaya_bb">
                                                </div>

                                                <hr>
                                                <div class="form-group">
                                                    <label for="dt-sudah_bayar_bb">Sudah Bayar Biaya-biaya</label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sudah_bayar_bb" name="dt-sudah_bayar_bb">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-sisa_tagihan_um">Sisa Tagihan Biaya-biaya</label>
                                                    <input type="text" class="form-control num" readonly
                                                        id="dt-sisa_tagihan_bb" name="dt-sisa_tagihan_bb">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dt-persentase_bayar_tagihan_bb">Persentase</label>
                                                    <input type="text" class="form-control" style="text-align:right"
                                                        readonly id="dt-persentase_bayar_tagihan_bb"
                                                        name="dt-persentase_bayar_tagihan_bb">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dt-legal" aria-labelledby="dt-legal-tab" role="tabpanel">
                                        <small id="last_update_legal" class="text-muted"></small>
                                        <div>
                                            <div class="card">
                                                <ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2"
                                                    role="tablist">

                                                    <li class="nav-item">
                                                        <a class="nav-link active" id="dt-legal-sertifikat-tab"
                                                            data-toggle="tab" href="#dt-legal-sertifikat"
                                                            aria-controls="home" role="tab"
                                                            aria-selected="true">Sertipikat</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link " id="dt-legal-pbb-tab" data-toggle="tab"
                                                            href="#dt-legal-pbb" aria-controls="home" role="tab"
                                                            aria-selected="true">PBB</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link " id="dt-legal-bphtb-tab" data-toggle="tab"
                                                            href="#dt-legal-bphtb" aria-controls="home" role="tab"
                                                            aria-selected="true">BPHTB</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link " id="dt-legal-pbg-tab" data-toggle="tab"
                                                            href="#dt-legal-pbg" aria-controls="home" role="tab"
                                                            aria-selected="true">IMB/PBG</a>
                                                    </li>

                                                    <li class="nav-item">
                                                        <a class="nav-link " id="dt-legal-pph-tab" data-toggle="tab"
                                                            href="#dt-legal-pph" aria-controls="home" role="tab"
                                                            aria-selected="true">PPH</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link " id="dt-legal-ajb-tab" data-toggle="tab"
                                                            href="#dt-legal-ajb" aria-controls="home" role="tab"
                                                            aria-selected="true">AJB</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-content">

                                                <div class="tab-pane" id="dt-legal-pbb"
                                                    aria-labelledby="dt-legal-pbb-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Mutasi Pecah PBB</h5>
                                                                </div>
                                                                <div class="card-body">

                                                                    <div class="form-group">
                                                                        <label>NOP PBB</label>
                                                                        <input type="text"
                                                                            class="form-control" id="dt-pbb_pecah_nop"
                                                                            name="dt-pbb_pecah_nop" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Luas Bumi</label>
                                                                        <input type="text"
                                                                            class="form-control" id="dt-pbb_pecah_luas_bumi"
                                                                            name="dt-pbb_pecah_luas_bumi" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>NJOP Bumi</label>
                                                                        <input type="text"
                                                                            class="form-control num" id="dt-pbb_pecah_njop_bumi"
                                                                            name="dt-pbb_pecah_njop_bumi" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Luas Bangunan</label>
                                                                        <input type="text"
                                                                            class="form-control" id="dt-pbb_pecah_luas_bangunan"
                                                                            name="dt-pbb_pecah_luas_bangunan" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>NJOP Bangunan</label>
                                                                        <input type="text"
                                                                            class="form-control num" id="dt-pbb_pecah_njop_bangunan"
                                                                            name="dt-pbb_pecah_njop_bangunan" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Tanggal bayar</label>
                                                                        <input type="text"
                                                                            id="dt-pbb_pecah_tanggal_bayar"
                                                                            name="dt-pbb_pecah_tanggal_bayar"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            placeholder="-" disabled />
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Jumlah Tagihan</label>
                                                                        <input type="text"
                                                                            class="form-control num" id="dt-pbb_pecah_jumlah_tagihan"
                                                                            name="dt-pbb_pecah_jumlah_tagihan" disabled>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Pembetulan</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="form-group">
                                                                        <label>Pembetulan PBB</label>
                                                                        <select name="dt-pbb_is_pembetulan"
                                                                            id="dt-pbb_is_pembetulan"
                                                                            class="form-control"
                                                                            disabled>
                                                                            <option value="Tidak">Tidak</option>
                                                                            <option value="Iya">Iya</option>
                                                                        </select>
                                                                    </div>
                                                                    <div id="select-pbb_is_pembetulan">
                                                                        <div class="form-group">
                                                                            <label>Tanggal Pembetulan</label>
                                                                            <input type="text"
                                                                                id="dt-pbb_tgl_pembetulan"
                                                                                name="dt-pbb_tgl_pembetulan"
                                                                                class="form-control flatpickr-human-friendly"
                                                                                placeholder="-" disabled />
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Balik Nama PBB</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="form-group">
                                                                        <label>Balik Nama PBB</label>
                                                                        <select name="dt-pbb_is_balik_nama"
                                                                            id="dt-pbb_is_balik_nama"
                                                                            class="form-control"
                                                                            disabled>
                                                                            <option value="Belum">Belum</option>
                                                                            <option value="Sudah">Sudah</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="select-pbb_is_balik_nama">
                                                                        <div class="form-group">
                                                                            <label>Nama Konsumen</label>
                                                                            <input type="text" readonly
                                                                                id="dt-pbb_balik_nama"
                                                                                class="form-control"
                                                                                name="dt-pbb_balik_nama" disabled>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Tanggal Pengiriman</label>
                                                                            <input type="text"
                                                                                id="dt-pbb_balik_nama_tgl_pengiriman"
                                                                                name="dt-pbb_balik_nama_tgl_pengiriman"
                                                                                class="form-control flatpickr-human-friendly"
                                                                                placeholder="-" disabled />
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Dikirim Ke
                                                                                Bank/Konsumen/Notaris</label>
                                                                            <input type="text"
                                                                                class="form-control"
                                                                                id="dt-pbb_balik_nama_ke"
                                                                                name="dt-pbb_balik_nama_ke" disabled>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="tab-pane active" id="dt-legal-sertifikat"
                                                    aria-labelledby="dt-legal-sertifikat-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Sertipikat</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="form-group">
                                                                        <label>No HGB Induk</label>
                                                                        <input type="text"
                                                                            class="form-control "
                                                                            id="dt-sertifikat_split_no_hgb_induk"
                                                                            name="dt-sertifikat_split_no_hgb_induk"
                                                                            disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Split Sertifikat</label>
                                                                        <select name="dt-sertifikat_is_split"
                                                                            id="dt-sertifikat_is_split"
                                                                            class="form-control"
                                                                            disabled>
                                                                            <option value="0">Tidak</option>
                                                                            <option value="1">Ya</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Sertipikat Split</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="select-sertifikat_is_split">
                                                                        <div class="form-group">
                                                                            <label>No HGB</label>
                                                                            <input type="text"
                                                                                class="form-control"
                                                                                id="dt-sertifikat_split_no_hgb"
                                                                                name="dt-sertifikat_split_no_hgb"
                                                                                disabled>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Tanggal Terbit Sertipikat</label>
                                                                            <input type="text"
                                                                                id="dt-sertifikat_split_tanggal_terbit"
                                                                                name="dt-sertifikat_split_tanggal_terbit"
                                                                                class="form-control flatpickr-human-friendly"
                                                                                placeholder="-" disabled />
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Tanggal Terbit Berakhir</label>
                                                                            <input type="text"
                                                                                id="dt-sertifikat_split_tanggal_berakhir"
                                                                                name="dt-sertifikat_split_tanggal_berakhir"
                                                                                class="form-control flatpickr-human-friendly"
                                                                                placeholder="-" disabled />
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>NIB</label>
                                                                            <input type="text"
                                                                                class="form-control "
                                                                                id="dt-sertifikat_split_nib"
                                                                                name="dt-sertifikat_split_nib" disabled>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Tanggal Surat Ukur</label>
                                                                            <input type="text"
                                                                                id="dt-sertifikat_split_tanggal_surat_ukur"
                                                                                name="dt-sertifikat_split_tanggal_surat_ukur"
                                                                                class="form-control flatpickr-human-friendly"
                                                                                placeholder="-" disabled />
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>No Surat Ukur</label>
                                                                            <input type="text"
                                                                                id="dt-sertifikat_split_no_surat_ukur"
                                                                                name="dt-sertifikat_split_no_surat_ukur"
                                                                                class="form-control" placeholder="-"
                                                                                disabled />
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Luas Tanah (m2)</label>
                                                                            <input type="text"
                                                                                class="form-control "
                                                                                id="dt-sertifikat_split_luas_tanah"
                                                                                name="dt-sertifikat_split_luas_tanah"
                                                                                disabled>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Sertipikat Balik Nama</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="form-group">
                                                                        <label>Balik Nama Sertifikat</label>
                                                                        <select name="dt-sertifikat_is_balik_nama"
                                                                            class="form-control "
                                                                            id="dt-sertifikat_is_balik_nama"
                                                                            disabled>
                                                                            <option value="Belum">Belum</option>
                                                                            <option value="Sudah">Sudah</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="select-sertifikat_is_balik_nama">
                                                                        <div class="form-group">
                                                                            <label>Nama Konsumen</label>
                                                                            <input type="text" readonly
                                                                                class="form-control "
                                                                                id="dt-sertifikat_balik_nama"
                                                                                name="dt-sertifikat_balik_nama"
                                                                                disabled>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>NIB Elektronik</label>
                                                                            <input type="text"
                                                                                class="form-control "
                                                                                id="dt-sertifikat_nib_elektronik"
                                                                                name="dt-sertifikat_nib_elektronik"
                                                                                disabled>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Tanggal Pengiriman</label>
                                                                            <input type="text"
                                                                                id="dt-sertifikat_balik_nama_tgl_pengiriman"
                                                                                name="dt-sertifikat_balik_nama_tgl_pengiriman"
                                                                                class="form-control flatpickr-human-friendly"
                                                                                placeholder="-" disabled />
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Dikirim Ke
                                                                                Bank/Konsumen/Notaris</label>
                                                                            <input type="text"
                                                                                class="form-control "
                                                                                id="dt-sertifikat_balik_nama_ke"
                                                                                name="dt-sertifikat_balik_nama_ke"
                                                                                disabled>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="tab-pane " id="dt-legal-pbg"
                                                    aria-labelledby="dt-legal-pbg-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>IMB/PBG</h5>
                                                                </div>
                                                                <div class="card-body">

                                                                    <div class="form-group">
                                                                        <label>No IMB/PBG</label>
                                                                        <input type="text" class="form-control"
                                                                            id="dt-pbg_no" name="dt-pbg_no" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Tanggal terbit</label>
                                                                        <input type="text" id="dt-pbg_tanggal_terbit"
                                                                            name="dt-pbg_tanggal_terbit"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            placeholder="-" disabled />
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Tipe</label>
                                                                        <input type="text" id="dt-pbg_tipe"
                                                                            name="dt-pbg_tipe" class="form-control"
                                                                            placeholder="-" disabled />
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Status</label>
                                                                        <select id="dt-pbg_status" name="dt-pbg_status"
                                                                            class="form-control" disabled>
                                                                            <option value="">-</option>
                                                                            <option value="Proses">Proses</option>
                                                                            <option value="Selesai">Selesai</option>
                                                                            <option value="Terjadi Masalah">Terjadi
                                                                                Masalah</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="divider">
                                                                        <div class="divider-text">Pengiriman</div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Dikirim Ke Bank/Konsumen</label>
                                                                        <select name="dt-pbg_dikirim_ke"
                                                                            class="form-control" id="dt-pbg_dikirim_ke"
                                                                            disabled>
                                                                            <option value="null"></option>
                                                                            <option value="Bank BTN">Bank BTN</option>
                                                                            <option value="Konsumen">Konsumen</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Tanggal Kirim Ke Bank/Konsumen</label>
                                                                        <input type="text" id="dt-pbg_tanggal_kirim"
                                                                            name="dt-pbg_tanggal_kirim"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            placeholder="-" disabled />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Revisi IMB/PBG</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="form-group">
                                                                        <label>Revisi IMB/PBG</label>
                                                                        <select name="dt-pbg_is_revisi"
                                                                            class="form-control" id="dt-pbg_is_revisi"
                                                                            disabled>
                                                                            <option value="Tidak">Tidak</option>
                                                                            <option value="Ya">Ya</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="select-pbg_is_revisi">
                                                                        <div class="form-group">
                                                                            <label>No IMB/PBG</label>
                                                                            <input type="text" class="form-control"
                                                                                id="dt-pbg_no_revisi"
                                                                                name="dt-pbg_no_revisi" disabled>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Tanggal terbit</label>
                                                                            <input type="text"
                                                                                id="dt-pbg_tanggal_terbit_revisi"
                                                                                name="dt-pbg_tanggal_terbit_revisi"
                                                                                class="form-control flatpickr-human-friendly"
                                                                                placeholder="-" disabled />
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Tipe</label>
                                                                            <input type="text" id="dt-pbg_tipe_revisi"
                                                                                name="dt-pbg_tipe_revisi"
                                                                                class="form-control" placeholder="-"
                                                                                disabled />
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Status</label>
                                                                            <select id="dt-pbg_status_revisi"
                                                                                name="dt-pbg_status_revisi"
                                                                                class="form-control" disabled>
                                                                                <option value="">-</option>
                                                                                <option value="Proses">Proses</option>
                                                                                <option value="Selesai">Selesai</option>
                                                                                <option value="Terjadi Masalah">Terjadi
                                                                                    Masalah</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="tab-pane " id="dt-legal-bphtb"
                                                    aria-labelledby="dt-legal-bphtb-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Verifikasi BPHTB</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="form-group">
                                                                        <label>Tanggal Verifikasi</label>
                                                                        <input type="text"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            id="dt-bphtb_tanggal_verifikasi"
                                                                            name="dt-bphtb_tanggal_verifikasi" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Jatuh Tempo</label>
                                                                        <input type="text"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            id="dt-bphtb_jatuh_tempo"
                                                                            name="dt-bphtb_jatuh_tempo" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Tanggal Perpanjangan Jatuh Tempo</label>
                                                                        <input type="text"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            id="dt-bphtb_perpanjang_jatuh_tempo"
                                                                            name="dt-bphtb_perpanjang_jatuh_tempo"
                                                                            disabled>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Tanggal Pembayaran</label>
                                                                        <input type="text"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            id="dt-bphtb_tanggal_pembayaran"
                                                                            name="dt-bphtb_tanggal_pembayaran" disabled>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Nominal Yang Disetujui</label>
                                                                        <input type="text" readonly
                                                                            class="form-control num"
                                                                            id="dt-bphtb_nominal_disetujui"
                                                                            name="dt-bphtb_nominal_disetujui" disabled>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>Validasi BPHTB</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="form-group">
                                                                        <label>Tanggal Validasi</label>
                                                                        <input type="text"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            id="dt-bphtb_tanggal_validasi"
                                                                            name="dt-bphtb_tanggal_validasi" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>No NTPD</label>
                                                                        <input type="text" class="form-control"
                                                                            id="dt-bphtb_nominal_tervalidasi"
                                                                            name="dt-bphtb_nominal_tervalidasi"
                                                                            disabled>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane " id="dt-legal-pph"
                                                    aria-labelledby="dt-legal-pph-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                </div>
                                                                <div class="card-body">

                                                                    <div class="form-group">
                                                                        <label>Nominal Dibayar</label>
                                                                        <input type="text" class="form-control num"
                                                                            id="dt-pph_nominal_bayar"
                                                                            name="dt-pph_nominal_bayar" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Tanggal Bayar</label>
                                                                        <input type="text"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            id="dt-pph_tgl_bayar"
                                                                            name="dt-pph_tgl_bayar" disabled>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="form-group">
                                                                        <label>Jenis Validasi</label>
                                                                        <select class="form-control"
                                                                            id="dt-pph_jenis_validasi"
                                                                            name="dt-pph_jenis_validasi" disabled>
                                                                            <option value=""></option>
                                                                            <option value="Offline">Offline</option>
                                                                            <option value="Online">Online</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="select-pph-validasi-offline" class="hide">
                                                                        <div class="form-group">
                                                                            <label>Tanggal Validasi</label>
                                                                            <input type="text"
                                                                                class="form-control flatpickr-human-friendly"
                                                                                id="dt-pph_tanggal_validasi"
                                                                                name="dt-pph_tanggal_validasi" disabled>
                                                                        </div>
                                                                    </div>
                                                                    <div class="select-pph-validasi-online"
                                                                        class="hide">
                                                                        <div class="form-group">
                                                                            <label>Tanggal Permohonan</label>
                                                                            <input type="text"
                                                                                class="form-control flatpickr-human-friendly"
                                                                                id="dt-pph_tgl_permohonan"
                                                                                name="dt-pph_tgl_permohonan" disabled>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Tanggal Selesai</label>
                                                                            <input type="text"
                                                                                class="form-control flatpickr-human-friendly"
                                                                                id="dt-pph_tgl_selesai"
                                                                                name="dt-pph_tgl_selesai" disabled>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>NTPN</label>
                                                                        <input type="text" class="form-control"
                                                                            id="dt-pph_ntpn" name="dt-pph_ntpn"
                                                                            disabled>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>No SKET</label>
                                                                        <input type="text" class="form-control"
                                                                            id="dt-pph_no_sket" name="dt-pph_no_sket"
                                                                            disabled>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>

                                                </div>
                                                <div class="tab-pane " id="dt-legal-ajb"
                                                    aria-labelledby="dt-legal-ajb-tab" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>AJB</h5>
                                                                </div>
                                                                <div class="card-body">

                                                                    <div class="form-group">
                                                                        <label>No AJB</label>
                                                                        <input type="text" class="form-control "
                                                                            id="dt-ajb_no" name="dt-ajb_no" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Tanggal AJB</label>
                                                                        <input type="text"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            id="dt-ajb_tanggal" name="dt-ajb_tanggal"
                                                                            disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Notaris</label>
                                                                        <input type="text" class="form-control "
                                                                            id="dt-ajb_notaris" name="dt-ajb_notaris"
                                                                            disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Dikirim Ke Bank/Konsumen</label>
                                                                        <input type="text" class="form-control "
                                                                            id="dt-ajb_dikirim_ke"
                                                                            name="dt-ajb_dikirim_ke" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Tanggal Dikirim Ke Bank/Konsumen</label>
                                                                        <input type="text"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            id="dt-ajb_tanggal_dikirim"
                                                                            name="dt-ajb_tanggal_dikirim" disabled>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5>PPJB</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="form-group">
                                                                        <label>No PPJB</label>
                                                                        <input type="text" class="form-control "
                                                                            id="dt-ppjb_no" name="dt-ppjb_no" disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Tanggal PPJB</label>
                                                                        <input type="text"
                                                                            class="form-control flatpickr-human-friendly"
                                                                            id="dt-ppjb_tanggal" name="dt-ppjb_tanggal"
                                                                            disabled>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Notaris</label>
                                                                        <input type="text" class="form-control "
                                                                            id="dt-ppjb_notaris" name="dt-ppjb_notaris"
                                                                            disabled>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dt-produksi" aria-labelledby="dt-produksi-tab"
                                        role="tabpanel">
                                        <small id="last_update_produksi" class="text-muted"></small>
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="dt-fm-prod-progress-tab"
                                                    data-toggle="tab" href="#dt-fm-prod-progress" role="tab"
                                                    aria-selected="true">Proges</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="dt-fm-prod-bayar_produksi-tab"
                                                    data-toggle="tab" href="#dt-fm-prod-bayar_produksi" role="tab"
                                                    aria-selected="true">Pembayaran</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="dt-fm-prod-dokumentasi-tab" data-toggle="tab"
                                                    href="#dt-fm-prod-dokumentasi" role="tab"
                                                    aria-selected="true">Dokumentasi Bangunan</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="dt-fm-prod-jalan-tab" data-toggle="tab"
                                                    href="#dt-fm-prod-jalan" role="tab" aria-selected="true">Jalan</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="dt-fm-prod-listrik-tab" data-toggle="tab"
                                                    href="#dt-fm-prod-listrik" role="tab"
                                                    aria-selected="true">Listrik</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="dt-fm-prod-air-tab" data-toggle="tab"
                                                    href="#dt-fm-prod-air" role="tab" aria-selected="true">Air</a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="dt-fm-prod-progress"
                                                aria-labelledby="dt-fm-prod-progress-tab" role="tabpanel">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-st_0"
                                                                    name="dt-st_0" disabled />
                                                                <label class="custom-control-label" for="dt-st_0">sd Sloof</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-st_25"
                                                                    name="dt-st_25" disabled />
                                                                <label class="custom-control-label" for="dt-st_25">Dinding sd Ringbalok</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-st_50"
                                                                    name="dt-st_50" disabled />
                                                                <label class="custom-control-label" for="dt-st_50">Dinding Full, Atap, PLester
                                                                    dan Aci</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-st_75"
                                                                    name="dt-st_75" disabled />
                                                                <label class="custom-control-label" for="dt-st_75">Plafon, Keramik, Dapur,
                                                                    Kamar Mandi dan Cat</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1" class="custom-control-input cbp"
                                                                    id="dt-st_100" name="dt-st_100" disabled />
                                                                <label class="custom-control-label" for="dt-st_100">Kusen, Pintu, Jendela,
                                                                    Kaca, Halaman dan Finishing</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1" class="custom-control-input cbp"
                                                                    id="dt-st_saluran" name="dt-st_saluran" disabled />
                                                                <label class="custom-control-label" for="dt-st_saluran">Saluran Jalan</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1" class="custom-control-input cbp"
                                                                    id="dt-st_jalan" name="dt-st_jalan" disabled />
                                                                <label class="custom-control-label" for="dt-st_jalan">Listrik</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1" class="custom-control-input cbp"
                                                                    id="dt-st_air" name="dt-st_air" disabled />
                                                                <label class="custom-control-label" for="dt-st_air">Air</label>
                                                            </div>
                                                        </div>
                                                        <!-- <div class="af"> -->
                                                        <div class="">
                                                            <div class="form-group">
                                                                <div class="custom-control custom-switch custom-control-inline">
                                                                    <input type="checkbox" value="1" class="custom-control-input cbp"
                                                                        id="dt-slo" name="dt-slo" disabled />
                                                                    <label class="custom-control-label" for="dt-slo">SLO / NIDI</label>
                                                                </div>
                                                            </div>
                                                            <!-- <div class="form-group">
                                                                <div class="custom-control custom-switch custom-control-inline">
                                                                    <input type="checkbox" value="1" class="custom-control-input cbp"
                                                                        id="dt-bp" name="dt-bp" disabled />
                                                                    <label class="custom-control-label" for="dt-bp">BP</label>
                                                                </div>
                                                            </div> -->

                                                        </div>

                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="divider">
                                                            <div class="divider-text">LPA</div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-lpa"
                                                                    name="dt-lpa" disabled />
                                                                <label class="custom-control-label" for="dt-lpa">LPA</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal LPA</label>
                                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                                id="dt-lpa_tanggal" name="dt-lpa_tanggal" disabled>
                                                        </div>
                                                        <div class="divider">
                                                            <div class="divider-text">Sumur Bor</div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch custom-control-inline">
                                                                <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-sumurbor"
                                                                    name="dt-sumurbor" disabled />
                                                                <label class="custom-control-label" for="dt-sumurbor">Sumur Bor</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal Pemasangan Sumur Bor</label>
                                                            <input type="text" class="form-control flatpickr-human-friendly"
                                                                id="dt-sumurbor_tanggal" name="dt-sumurbor_tanggal" disabled>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="dt-sumurbor_keterangan">Keterangan Sumur Bor</label>
                                                            <textarea class="form-control" id="dt-sumurbor_keterangan"
                                                                name="dt-sumurbor_keterangan" rows="3" placeholder="Keterangan" disabled></textarea>

                                                            <small id="dt-last_update-sumurbor" class="text-muted"></small>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="dt-progres_bangunan">Progres Bangunan</label>
                                                            <input type="range" class="form-control-range" value="0" id="dt-progres_bangunan"
                                                                name="dt-progres_bangunan" step="1" disabled>
                                                            <span id="dt-t_progres_bangunan"></span>%
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="dt-produksi_keterangan">Keterangan Pembangunan</label>
                                                            <textarea class="form-control" id="dt-produksi_keterangan"
                                                                name="dt-produksi_keterangan" rows="3" placeholder="Keterangan" disabled></textarea>
                                                        </div>

                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="divider">
                                                            <div class="divider-text">Tanggal Pembangunan Rumah</div>
                                                        </div>
                                                        <div>

                                                            <div class="form-group">
                                                                <label>Tanggal Pembangunan</label>
                                                                <input type="text" class="form-control tanggal_pembangunan flatpickr-human-friendly tgl_bangun"
                                                                    id="dt-tanggal_pembangunan" name="dt-tanggal_pembangunan" disabled>
                                                                <input type="text" class="hidden" id="dt-tanggal_pembangunan_old"
                                                                    name="dt-tanggal_pembangunan_old" disabled>
                                                            </div>
                                                            <span class="text-muted" id="dt-lu-tanggal_pembangunan"></span>

                                                            <div class="form-group">
                                                                <label>Tanggal Rencana Selesai Pembangunan</label>
                                                                <input type="text" class="form-control tanggal_rencana_selesai_pembangunan flatpickr-human-friendly tgl_bangun"
                                                                    id="dt-tanggal_rencana_selesai_pembangunan"
                                                                    name="dt-tanggal_rencana_selesai_pembangunan" disabled>
                                                                <input type="text" class="hidden" id="dt-tanggal_rencana_selesai_pembangunan_old"
                                                                    name="dt-tanggal_rencana_selesai_pembangunan_old" disabled>
                                                            </div>
                                                            <span class="text-muted" id="dt-lu-tanggal_rencana_selesai_pembangunan"></span>


                                                            <div class="form-group">
                                                                <label>Tanggal Selesai Pembangunan</label>
                                                                <input type="text" class="form-control flatpickr-human-friendly "
                                                                    id="dt-tanggal_selesai_pembangunan" name="dt-tanggal_selesai_pembangunan" disabled>
                                                                <input type="text" class="hidden" id="dt-tanggal_selesai_pembangunan_old"
                                                                    name="dt-tanggal_selesai_pembangunan_old" disabled>
                                                            </div>
                                                            <span class="text-muted" id="dt-lu-tanggal_selesai_pembangunan"></span>


                                                            <div class="hidden">
                                                                <div class="form-group">
                                                                    <label>Diinput oleh</label>
                                                                    <input type="text" class="form-control" id="dt-tanggal_pembangunan_oleh" disabled
                                                                        name="dt-tanggal_pembangunan_oleh">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diinput Pada</label>
                                                                    <input type="text" class="form-control flatpickr-human-friendly"
                                                                        id="dt-tanggal_pembangunan_pada" disabled name="dt-tanggal_pembangunan_pada">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diubah oleh</label>
                                                                    <input type="text" class="form-control" id="dt-tanggal_pembangunan_diubah_oleh"
                                                                        disabled name="dt-tanggal_pembangunan_diubah_oleh">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diubah Pada</label>
                                                                    <input type="text" class="form-control flatpickr-human-friendly"
                                                                        id="dt-tanggal_pembangunan_diubah_pada" disabled
                                                                        name="dt-tanggal_pembangunan_diubah_pada">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diinput oleh</label>
                                                                    <input type="text" class="form-control" id="dt-tanggal_selesai_pembangunan_oleh"
                                                                        disabled name="dt-tanggal_selesai_pembangunan_oleh">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diinput Pada</label>
                                                                    <input type="text" class="form-control flatpickr-human-friendly"
                                                                        id="dt-tanggal_selesai_pembangunan_pada" disabled
                                                                        name="dt-tanggal_selesai_pembangunan_pada">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diubah oleh</label>
                                                                    <input type="text" class="form-control"
                                                                        id="dt-tanggal_selesai_pembangunan_diubah_oleh" disabled
                                                                        name="dt-tanggal_selesai_pembangunan_diubah_oleh">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Diubah Pada</label>
                                                                    <input type="text" class="form-control flatpickr-human-friendly"
                                                                        id="dt-tanggal_selesai_pembangunan_diubah_pada" disabled
                                                                        name="dt-tanggal_selesai_pembangunan_diubah_pada">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="form-group" style="min-height:100px; height: auto;">
                                                    <label>RAB</label>
                                                    <div id="list_rab_dokumen" style="display: flex; flex-wrap: wrap;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="dt-fm-prod-bayar_produksi" aria-labelledby="dt-fm-prod-bayar_produksi-tab" role="tabpanel">
                                                <div id="dt-div-bayar_produksi-here" class="row"></div>
                                            </div>
                                            <div class="tab-pane" id="dt-fm-prod-dokumentasi" aria-labelledby="dt-fm-prod-dokumentasi-tab" role="tabpanel">
                                                <div class="form-group foto-container">
                                                    <label>Foto Konstruksi(Jika Ada, Pembesian, Pondasi)</label>

                                                    <div id="dt-list_prod_foto_konstruksi"
                                                        style="display: flex; flex-wrap: wrap;"></div>
                                                </div>
                                                <hr>
                                                <div class="form-group foto-container">
                                                    <label for="upload_komplain_produksi">Foto Exterior(Depan dan
                                                        Belakang(min. 1 photo), foto memiliki titik koordinat)</label>

                                                    <div id="dt-list_prod_foto_exterior"
                                                        style="display: flex; flex-wrap: wrap;"></div>
                                                </div>
                                                <hr>
                                                <div class="form-group foto-container">
                                                    <label for="upload_komplain_produksi">Foto Interior(kamar, dapur,
                                                        toilet, dan ruang tengah (min. 1 photo), foto memiliki titik
                                                        koordinat)</label>

                                                    <div id="dt-list_prod_foto_interior"
                                                        style="display: flex; flex-wrap: wrap;"></div>
                                                </div>

                                            </div>

                                            <div class="tab-pane" id="dt-fm-prod-jalan"
                                                aria-labelledby="dt-fm-prod-jalan-tab" role="tabpanel">
                                                <div class="divider">
                                                    <div class="divider-text">Foto Jalan</div>
                                                </div>
                                                <div>
                                                    <div class="form-group foto-container">
                                                        <label for="jalan_foto">Foto Jalan</label>

                                                        <div id="dt-list_jalan_foto"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="form-group foto-container">
                                                        <label for="jalan_foto_update">Foto Jalan Update/Setelah
                                                            Akad(Paving)</label>

                                                        <div id="dt-list_jalan_foto_update"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="tab-pane" id="dt-fm-prod-listrik"
                                                aria-labelledby="dt-fm-prod-listrik-tab" role="tabpanel">
                                                <div class="divider">
                                                    <div class="divider-text">Ketersediaan Listrik</div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Jenis Sumber Listrik</label>
                                                    <select id="dt-listrik_jenis" name="dt-listrik_jenis"
                                                        class="form-control" disabled>
                                                        <option value="PLN">PLN</option>
                                                        <option value="Disendiakan Pengembang">Disendiakan Pengembang
                                                            (Dalam Pengajuan)</option>
                                                    </select>
                                                </div>
                                                <div id="dt-listrik-pln-input-form">
                                                    <div class="form-group">
                                                        <label>No ID Pelanggan/Nomor Meteran Listrik PLN</label>
                                                        <input type="text" class="form-control" id="dt-listrik_pln"
                                                            name="dt-listrik_pln" disabled>
                                                    </div>
                                                    <div class="form-group foto-container">
                                                        <label>Foto Ketersediaan Lampu
                                                            Menyala</label>

                                                        <div id="dt-list_listrik_pln_foto"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>
                                                <div id="listrik_disediakan" class="hidden">
                                                    <div class="form-group">
                                                        <label>No Pengajuan Listrik PLN</label>
                                                        <input type="text" class="form-control"
                                                            id="dt-listrik_disediakan_no"
                                                            name="dt-listrik_disediakan_no" disabled>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal Pengajuan Listrik PLN</label>
                                                        <input type="text" class="form-control flatpickr-human-friendly"
                                                            id="dt-listrik_disediakan_tanggal"
                                                            name="dt-listrik_disediakan_tanggal" disabled>
                                                    </div>
                                                    <div class="form-group foto-container">
                                                        <label for="label_listrik_disediakan_dokumen">Upload Bukti
                                                            Pengajuan</label>

                                                        <div id="dt-list_listrik_disediakan_dokumen"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                    <div class="form-group foto-container">
                                                        <label for="dt-listrik_disediakan_foto">Foto Ketersediaan Lampu
                                                            Menyala</label>

                                                        <div id="dt-list_listrik_disediakan_foto"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="dt-fm-prod-air"
                                                aria-labelledby="dt-fm-prod-air-tab" role="tabpanel">
                                                <div class="divider">
                                                    <div class="divider-text">Ketersediaan Air</div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Jenis Sumber Air</label>
                                                    <select id="dt-air_jenis" name="dt-air_jenis" class="form-control"
                                                        disabled>
                                                        <option value="Air Tanah">Air Tanah</option>
                                                        <option value="Komunal Warga">Komunal Warga</option>
                                                        <option value="PDAM">PDAM</option>
                                                    </select>
                                                </div>
                                                <div id="dt-air_tanah-input_form">
                                                    <div class="form-group foto-container">
                                                        <label for="dt-air_tanah">Foto ketersediaan air bersih dengan
                                                            air
                                                            mengalir & sumber air (min. 1 foto)</label>

                                                        <div id="dt-list_air_tanah"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>
                                                <div id="dt-air_komunal-input_form" class="hidden">
                                                    <div class="form-group foto-container">
                                                        <label for="dt-air_komunal">Foto ketersediaan air bersih dengan
                                                            air
                                                            mengalir & sumber air komunal bersama (min. 1 foto)</label>

                                                        <div id="dt-list_air_komunal"
                                                            style="display: flex; flex-wrap: wrap;"></div>
                                                    </div>
                                                </div>
                                                <div id="dt-air_pdam-input_form" class="hidden">
                                                    <div class="form-group">
                                                        <label>No Meteran Air PDAM</label>
                                                        <input type="text" class="form-control" id="dt-air_pdam_no"
                                                            name="dt-air_pdam_no" disabled>
                                                    </div>
                                                    <div class="form-group foto-container">
                                                        <label for="dt-air_pdam">Foto ketersediaan air bersih dengan air
                                                            mengalir & meteran air PDAM (min. 1 foto)</label>
                                                        <div id="dt-list_air_pdam"
                                                            style="display: flex; flex-wrap: wrap;">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Deskripsi Unit (informasi keunggulan unit)</label>
                                                    <input type="text" class="form-control" id="dt-air_deskripsi_unit"
                                                        name="dt-air_deskripsi_unit" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dt-pajak" aria-labelledby="dt-pajak-tab"
                                        role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="divider">
                                                    <div class="divider-text">Bukti Pembayaran PPH</div>
                                                </div>
                                                <div id="dt-file_pph42-here"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="divider">
                                                    <div class="divider-text">Bukti Pembayaran PPn</div>
                                                </div>
                                                <div id="dt-file_ppn-here"></div>
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
            </div>
        </div>
    </div>
</div>
<!-- modal detail kavling -->
<?php
/*<div class="modal fade" id="modal_detail">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Detail Kavling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="card">
                    <form action="" id="fm-detail">
                        <div class="card-header">
                            <p class="modal-title label_alamat" id="detail_kavling_header"></p>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="dt-mkdt-tab" data-toggle="tab" href="#dt-mkdt" aria-controls="mkdt" role="tab" aria-selected="true">Konsumen</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="dt-stdetail-tab" data-toggle="tab" href="#dt-stdetail" aria-controls="dt-stdetail-dt" role="tab" aria-selected="false">Status</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="dt-tagihan-tab" data-toggle="tab" href="#dt-tagihan" aria-controls="tgt" role="tab" aria-selected="false">Tagihan</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="dt-legal-tab" data-toggle="tab" href="#dt-legal" aria-controls="legal" role="tab" aria-selected="false">Legal</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="dt-produksi-tab" data-toggle="tab" href="#dt-produksi" aria-controls="produksi" role="tab" aria-selected="false">Bangunan</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <!-- tab mkdt -->
                                <div class="tab-pane active" id="dt-mkdt" aria-labelledby="dt-mkdt-tab" role="tabpanel">
                                    <small id="last_update_mkdt" class="text-muted"></small>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-3 col-lg-3">
                                            <div class="divider">
                                                <div class="divider-text">Data Konsumen</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="nama_konsumen">No SPPTB</label>
                                                <input type="text" class="form-control" id="dt-no_spptb" name="dt-no_spptb">
                                            </div>
                                            <div class="form-group">
                                                <label for="nama_konsumen">Nama Konsumen</label>
                                                <input type="text" class="form-control" id="dt-nama_konsumen" required name="dt-nama_konsumen">
                                            </div>
                                            <div class="form-group">
                                                <label for="alamat_konsumen">Alamat Konsumen</label>
                                                <input type="text" class="form-control" id="dt-alamat_konsumen" name="dt-alamat_konsumen">
                                            </div>
                                            <div class="form-group">
                                                <label for="nik_konsumen">NIK</label>
                                                <input type="text" class="form-control" id="dt-nik_konsumen" name="dt-nik_konsumen">
                                            </div>
                                            <div class="form-group">
                                                <label for="npwp_konsumen">NPWP</label>
                                                <input type="text" class="form-control" id="dt-npwp_konsumen" name="dt-npwp_konsumen">
                                            </div>
                                            <div class="form-group">
                                                <label for="hp_konsumen">Kontak Konsumen</label>
                                                <input type="text" class="form-control" id="dt-hp_konsumen" name="dt-hp_konsumen">
                                            </div>
                                            <div class="form-group">
                                                <label for="hp_konsumen">Email Konsumen</label>
                                                <input type="text" class="form-control" id="dt-email_konsumen" name="dt-email_konsumen">
                                            </div>
                                            <div class="form-group hidden">
                                                <label for="status_kavling">Status Konsumen</label>
                                                <select class="form-control" id="dt-status_konsumen" name="dt-status_konsumen">
                                                    <option value="">-</option>
                                                    <option value="Umum">Umum</option>
                                                    <option value="TWP">TWP</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="sales">Sales</label>
                                                <input type="text" class="form-control" id="dt-sales" required name="dt-sales">
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-3 col-lg-3">
                                            <input readonly type="hidden" class="form-control num" id="id_mkdt" name="id_mkdt">
                                            <div class="divider">
                                                <div class="divider-text">TUNAI/KPR</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="is_kpr">Tunai/KPR</label>
                                                <select required class="form-control" id="dt-is_kpr" name="dt-is_kpr">
                                                    <option value="">-</option>
                                                    <option value="0">TUNAI/CASH KERAS</option>
                                                    <option value="2">TUNAI/CASH BERTAHAP</option>
                                                    <option value="1">KPR</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="is_subsidi">Subsidi/Non-Subsidi</label>
                                                <select required class="form-control" id="dt-is_subsidi" name="dt-is_subsidi">
                                                    <option value="">-</option>
                                                    <option value="0">Non-Subsidi</option>
                                                    <option value="1">Subsidi</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="total_biaya2">ACC KPR</label>
                                                <input readonly type="text" class="form-control num" id="dt-harga_kpr_acc" name="dt-harga_kpr_acc">
                                            </div>
                                            <div class="form-group">
                                                <label for="total_biaya2">Turun KPR</label>
                                                <input readonly type="text" class="form-control num" id="dt-harga_penambahan_um" name="dt-harga_penambahan_um">
                                            </div>
                                            <div class="divider">
                                                <div class="divider-text">Penambahan Biaya</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="total_biaya2">Penambahan Biaya</label>
                                                <input type="text" class="form-control num totalbb" id="dt-harga_penambahan" name="dt-harga_penambahan">
                                            </div>
                                            <div class="form-group">
                                                <label for="total_biaya2">Keterangan Penambahan Biaya</label>
                                                <textarea name="dt-keterangan_penambahan_biaya" id="dt-keterangan_penambahan_biaya" class="form-control dt-fm" cols="30" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3 col-lg-3">
                                            <div class="divider">
                                                <div class="divider-text">Harga Jual</div>
                                            </div>
                                            <input type="hidden" name="dt-id_mkdt" id="dt-id_mkdt">
                                            <!-- <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Tanggal PriceList</label>
                                                <input type="text" class="form-control text-right dt-fm" id="dt-tgl_harga" name="dt-tgl_harga" value="" readonly />
                                            </div> -->
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Harga Jual</label>
                                                <input type="text" class="form-control num dt-fm" id="dt-harga_jual" name="dt-harga_jual" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label for="total_biaya2">Diskon Harga Jual</label>
                                                <input type="text" class="form-control num" id="dt-harga_diskon_hargajual" name="dt-harga_diskon_hargajual">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Harga Jual Net</label>
                                                <input type="text" class="form-control num dt-fm" id="dt-harga_jual_net" name="dt-harga_jual_net" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">KPR</label>
                                                <input type="text" class="form-control num dt-fm" id="dt-harga_kpr" name="dt-harga_kpr" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Uang Muka</label>
                                                <input type="text" class="form-control num dt-fm totalbb" id="dt-harga_uang_muka" name="dt-harga_uang_muka" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label for="total_biaya2">Diskon Uang Muka</label>
                                                <input type="text" class="form-control num" id="dt-harga_diskon_uang_muka" name="dt-harga_diskon_uang_muka">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Biaya Adm</label>
                                                <input type="text" class="form-control num dt-fm totalbb" id="dt-harga_administrasi" name="dt-harga_administrasi" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label for="total_biaya2">PPN</label>
                                                <input type="text" class="form-control num totalbb" id="dt-harga_ppn" name="dt-harga_ppn">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                                                <input type="text" class="form-control num dt-fm totalbb" id="dt-harga_bphtb" name="dt-harga_bphtb" value="" readonly />
                                            </div>


                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Biaya Proses</label>
                                                <input type="text" class="form-control num dt-fm totalbb" id="dt-harga_biaya_proses" name="dt-harga_biaya_proses" value="" readonly />
                                            </div>
                                            <!-- <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">ROW</label>
                                                <input type="text" class="form-control num dt-fm" id="dt-row" name="dt-row" value="" readonly />
                                            </div> -->
                                        </div>
                                        <div class="col-sm-12 col-md-3 col-lg-3">
                                            <div class="divider">
                                                <div class="divider-text">Harga di Price List</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Tanggal PriceList</label>
                                                <input type="text" class="form-control text-right dt-fm" id="pl-tgl_harga" name="pl-tgl_harga" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Harga Jual</label>
                                                <input type="text" class="form-control num dt-fm" id="pl-hargajual" name="pl-hargajual" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Harga Jual Net</label>
                                                <input type="text" class="form-control num dt-fm" id="pl-hargajual_net" name="pl-hargajual_net" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">KPR</label>
                                                <input type="text" class="form-control num dt-fm" id="pl-kpr" name="pl-kpr" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Uang Muka</label>
                                                <input type="text" class="form-control num dt-fm totalbb" id="pl-uang_muka" name="pl-uang_muka" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Biaya Adm</label>
                                                <input type="text" class="form-control num dt-fm totalbb" id="pl-biaya_adm" name="pl-biaya_adm" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label for="total_biaya2">PPN</label>
                                                <input type="text" class="form-control num totalbb" id="pl-ppn" name="pl-ppn">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                                                <input type="text" class="form-control num dt-fm totalbb" id="pl-bphtb" name="pl-bphtb" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">Biaya Proses</label>
                                                <input type="text" class="form-control num pl-fm totalbb" id="pl-biaya_proses" name="pl-biaya_proses" value="" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="basic-icon-default-fullname">ROW</label>
                                                <input type="text" class="form-control num dt-fm" id="pl-row" name="pl-row" value="" readonly />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="dt-stdetail" aria-labelledby="dt-stdetail-tab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-4 col-lg-4">
                                            <div class="divider">
                                                <div class="divider-text">Status</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="status_kavling">Status Kavling</label>
                                                <select required class="form-control" id="dt-status_mkdt" name="dt-status_mkdt">
                                                    <option value="">-</option>
                                                    <option value="Booking">Booking</option>
                                                    <option value="Akad">Akad</option>
                                                    <option value="Batal">Batal</option>
                                                </select>
                                            </div>
                                            <div id="dt-show_keterangan_batal" class="hidden">
                                                <div class="form-group">
                                                    <label for="keterangan_batal">Keterangan Batal</label>
                                                    <textarea class="form-control" id="dt-keterangan_batal" name="dt-keterangan_batal" rows="3" placeholder="Keterangan"></textarea>
                                                </div>

                                            </div>
                                            <div class="divider">
                                                <div class="divider-text">Booking</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="booking_tgl">Tanggal Booking</label>
                                                <input type="text" id="dt-booking_tgl" name="dt-booking_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="harga_jual">Booking Fee</label>
                                                <input type="text" class="form-control num" id="dt-booking_fee" name="dt-booking_fee">
                                            </div>
                                            <div class="divider">
                                                <div class="divider-text">Wawancara</div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" id="dt-wawancara" name="dt-wawancara" value="1" />
                                                    <label class="custom-control-label" for="dt-wawancara">Sudah Wawancara</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="bank">Bank</label>
                                                <input type="text" id="dt-bank" name="dt-bank" class="form-control" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-wawancara_tgl">Tanggal Wawancara</label>
                                                <input type="text" id="dt-wawancara_tgl" name="dt-wawancara_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>


                                        </div>
                                        <div class="col-sm-12 col-md-4 col-lg-4">
                                            <div class="divider">
                                                <div class="divider-text">SP3K</div>
                                            </div>

                                            <div class="form-group">
                                                <label for="ewe_keterangan">Status</label>
                                                <input type="text" id="dt-mkdt_keterangan" name="dt-mkdt_keterangan" class="form-control" placeholder="TUNAI/AAC SP3K/LAIN-LAIN" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-harga_kpr">Pengajuan</label>
                                                <input type="text" id="dt-harga_kpr" name="dt-harga_kpr" class="form-control num" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="bank">ACC</label>
                                                <input type="text" id="dt-acc_harga_kpr" name="dt-acc_harga_kpr" class="form-control num" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="bank">Turun KPR</label>
                                                <input type="text" id="dt-harga_turun_kpr" name="dt-harga_turun_kpr" class="form-control num" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-sp3k_tgl">Tanggal Terbit</label>
                                                <input type="text" id="dt-sp3k_tgl" name="dt-sp3k_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-sp3k_tgl_exp">Tanggal Expire</label>
                                                <input type="text" id="dt-sp3k_tgl_exp" name="dt-sp3k_tgl_exp" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <a href="" class="btn btn-primary" target=_blank id="dt-sp3k_file">Klik untuk lihat file SP3K</a>
                                        </div>
                                        <div class="col-sm-12 col-md-4 col-lg-4">
                                            <div class="divider">
                                                <div class="divider-text">Perintah Bangun</div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" id="dt-perintah_bangun" name="dt-perintah_bangun" value="1" />
                                                    <label class="custom-control-label" for="dt-perintah_bangun">Perintah Bangun</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-perintah_bangun_tgl">Tanggal Perintah Bangun</label>
                                                <input type="text" readonly="readonly" id="dt-perintah_bangun_tgl" name="dt-perintah_bangun_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-perintah_bangun_oleh">Oleh</label>
                                                <input type="text" readonly="readonly" id="dt-perintah_bangun_oleh" name="dt-perintah_bangun_oleh" class="form-control" placeholder="-" />
                                            </div>
                                            <a href="" class="btn btn-primary" target=_blank id="dt-perintah_bangun_file">Klik untuk lihat file Perintah Bangun</a>
                                            <div class="divider">
                                                <div class="divider-text">Akad</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-rencana_akad_tgl">Rencana Akad</label>
                                                <input type="text" id="dt-rencana_akad_tgl" name="dt-rencana_akad_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>

                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" id="dt-akad" name="dt-akad" value="1" />
                                                    <label class="custom-control-label" for="dt-akad">Akad</label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="dt-akad_tgl">Tanggal Akad</label>
                                                <input type="text" id="dt-akad_tgl" name="dt-akad_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-perintah_bangun_oleh">No Debitur</label>
                                                <input type="text" readonly="readonly" id="dt-debitur_no" name="dt-debitur_no" class="form-control" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-perintah_bangun_oleh">No BAST</label>
                                                <input type="text" readonly="readonly" id="dt-bast_no" name="dt-bast_no" class="form-control" placeholder="-" />
                                            </div>
                                            <a href="" class="btn btn-primary" target=_blank id="dt-bast_file">Klik untuk lihat file BAST</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- tab tagihan -->
                                <div class="tab-pane" id="dt-tagihan" aria-labelledby="dt-tagihan-tab" role="tabpanel">
                                    <small id="last_update_keuangan" class="text-muted"></small>
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12 col-lg-6">
                                            <div class="divider">
                                                <div class="divider-text">Total Uang Muka</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-total_biaya_um">Total Uang Muka</label>
                                                <input readonly type="text" class="form-control num" id="dt-total_biaya_um" name="dt-total_biaya_um">
                                            </div>

                                            <hr>
                                            <div class="form-group">
                                                <label for="dt-sudah_bayar_um">Sudah Bayar Uang Muka</label>
                                                <input type="text" class="form-control num" readonly id="dt-sudah_bayar_um" name="dt-sudah_bayar_um">
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-sisa_tagihan_um">Sisa Tagihan Uang Muka</label>
                                                <input type="text" class="form-control num" readonly id="dt-sisa_tagihan_um" name="dt-sisa_tagihan_um">
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-persentase_bayar_tagihan_um">Persentase</label>
                                                <input type="text" class="form-control" style="text-align:right" readonly id="dt-persentase_bayar_tagihan_um" name="dt-persentase_bayar_tagihan_um">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12 col-lg-6">

                                            <div class="divider">
                                                <div class="divider-text">Total Biaya-biaya</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-total_biaya_bb">Total Biaya-biaya</label>
                                                <input readonly type="text" class="form-control num" id="dt-total_biaya_bb" name="dt-total_biaya_bb">
                                            </div>

                                            <hr>
                                            <div class="form-group">
                                                <label for="dt-sudah_bayar_bb">Sudah Bayar Biaya-biaya</label>
                                                <input type="text" class="form-control num" readonly id="dt-sudah_bayar_bb" name="dt-sudah_bayar_bb">
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-sisa_tagihan_um">Sisa Tagihan Biaya-biaya</label>
                                                <input type="text" class="form-control num" readonly id="dt-sisa_tagihan_bb" name="dt-sisa_tagihan_bb">
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-persentase_bayar_tagihan_bb">Persentase</label>
                                                <input type="text" class="form-control" style="text-align:right" readonly id="dt-persentase_bayar_tagihan_bb" name="dt-persentase_bayar_tagihan_bb">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="dt-legal" aria-labelledby="dt-legal-tab" role="tabpanel">
                                    <small id="last_update_legal" class="text-muted"></small>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-4 col-lg-4">
                                            <div class="divider">
                                                <div class="divider-text">PBB</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-sertifikat_no_hgb">PBB</label>
                                                <input type="text" class="form-control" id="dt-pbb" name="dt-pbb">
                                            </div>
                                            <h5 class="modal-title" id="exampleModalLabel">Sertifikat</h5>
                                            <div class="form-group">
                                                <label for="dt-sertifikat_tgl">Tanggal Sertifikat</label>
                                                <input type="text" id="dt-sertifikat_tgl" name="dt-sertifikat_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-sertifikat_luas">Luas Tanah</label>
                                                <input type="text" class="form-control" id="dt-sertifikat_luas" name="dt-sertifikat_luas">
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-sertifikat_no_hgb">No HGB</label>
                                                <input type="text" class="form-control" id="dt-sertifikat_no_hgb" name="dt-sertifikat_no_hgb">
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-sertifikat_no_split">No Split</label>
                                                <input type="text" class="form-control" id="dt-sertifikat_no_split" name="dt-sertifikat_no_split">
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-sertifikat_masa_berlaku">Masa Berlaku</label>
                                                <input type="text" id="dt-sertifikat_masa_berlaku" name="dt-sertifikat_masa_berlaku" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-4 col-lg-4">
                                            <div class="divider">
                                                <div class="divider-text">IMB</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-imb_tgl">Tanggal IMB</label>
                                                <input type="text" id="dt-imb_tgl" name="dt-imb_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-imb_no_induk">No Induk</label>
                                                <input type="text" class="form-control" id="dt-imb_no_induk" name="dt-imb_no_induk">
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-imb_no_split">No Split</label>
                                                <input type="text" class="form-control" id="dt-imb_no_split" name="dt-imb_no_split">
                                            </div>
                                            <div class="divider">
                                                <div class="divider-text">BPHTB</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-bphtb_tgl">Tanggal BPHTB</label>
                                                <input type="text" id="dt-bphtb_tgl" name="dt-bphtb_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-bphtb_masa_berlaku">Masa Berlaku</label>
                                                <input type="text" id="dt-bphtb_masa_berlaku" name="dt-bphtb_masa_berlaku" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-bphtb_validasi">Validasi</label>
                                                <input type="text" id="dt-bphtb_validasi" name="dt-bphtb_validasi" class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-4 col-lg-4">
                                            <div class="divider">
                                                <div class="divider-text">NOP</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-nop_pbb">NOP</label>
                                                <input type="text" class="form-control" id="dt-nop_pbb" name="dt-nop_pbb">
                                            </div>
                                            <div class="form-group">
                                                <label for="dt-pph">PPh</label>
                                                <input type="text" class="form-control" id="dt-pph" name="dt-pph">
                                            </div>
                                            <!-- <div class="form-group">
                                            <label for="legal_akad_tgl">Tanggal Akad</label>
                                            <input type="text" id="legal_akad_tgl" name="legal_akad_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div> -->
                                            <div class="form-group">
                                                <label for="dt-legal_keterangan">Keterangan</label>
                                                <textarea class="form-control" id="dt-legal_keterangan" name="dt-legal_keterangan" rows="3" placeholder="Keterangan"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- tab produksi  -->
                                <div class="tab-pane" id="dt-produksi" aria-labelledby="dt-produksi-tab" role="tabpanel">
                                    <small id="last_update_produksi" class="text-muted"></small>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-pondasi" name="dt-pondasi" disabled />
                                            <label class="custom-control-label" for="dt-pondasi">Pondasi</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-naik_dinding" name="dt-naik_dinding" disabled />
                                            <label class="custom-control-label" for="dt-naik_dinding">Naik Dinding</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-topping_off" name="dt-topping_off" disabled />
                                            <label class="custom-control-label" for="dt-topping_off">Topping Off</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-finishing" name="dt-finishing" disabled />
                                            <label class="custom-control-label" for="dt-finishing">Finishing</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-saluran" name="dt-saluran" disabled />
                                            <label class="custom-control-label" for="dt-saluran">Saluran</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-jalan" name="dt-jalan" disabled />
                                            <label class="custom-control-label" for="dt-jalan">Jalan</label>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="af">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch custom-control-inline">
                                                <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-slo" name="dt-slo" disabled />
                                                <label class="custom-control-label" for="dt-slo">SLO</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-switch custom-control-inline">
                                                <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-bp" name="dt-bp" disabled />
                                                <label class="custom-control-label" for="dt-bp">BP</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-switch custom-control-inline">
                                                <input type="checkbox" value="1" class="custom-control-input cbp" id="dt-lpa" name="dt-lpa" disabled />
                                                <label class="custom-control-label" for="dt-lpa">LPA</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="dt-progres_bangunan">Progres Bangunan</label>
                                        <input type="range" class="form-control-range" disabled value="0" id="dt-progres_bangunan" name="dt-progres_bangunan" disabled>
                                        <span id="dt-t_progres_bangunan"></span>%
                                    </div>
                                    <div class="form-group">
                                        <label for="dt-produksi_keterangan">Keterangan</label>
                                        <textarea class="form-control" id="dt-produksi_keterangan" name="dt-produksi_keterangan" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>*/
?>

<!-- END: Content-->
<div class="modal fade text-left" id="modal-batal" tabindex="-1" role="dialog" aria-labelledby="modal-batal"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Batal Booking</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="fm-batal_booking" enctype="multipart/form-data" class="add-new-record modal-content pt-0">
                <div class="modal-body">
                    <p class="modal-title label_alamat" id="label-batal_booking"></p>
                    <hr>
                    <div class="row">
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="divider">
                                <div class="divider-text">Data Konsumen</div>
                            </div>
                            <div class="form-group">
                                <label>No SPPTB</label>
                                <input disabled type="text" class="form-control" id="batal-no_spptb"
                                    name="batal-no_spptb">
                            </div>
                            <div class="form-group">
                                <label>Nama Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-nama_konsumen" required
                                    name="batal-nama_konsumen">
                            </div>
                            <div class="form-group">
                                <label>Alamat Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-alamat_konsumen"
                                    name="batal-alamat_konsumen">
                            </div>
                            <div class="form-group">
                                <label>NIK</label>
                                <input disabled type="text" class="form-control" id="batal-nik_konsumen"
                                    name="batal-nik_konsumen">
                            </div>
                            <div class="form-group">
                                <label>NPWP</label>
                                <input disabled type="text" class="form-control" id="batal-npwp_konsumen"
                                    name="batal-npwp_konsumen">
                            </div>
                            <div class="form-group">
                                <label>Kontak Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-hp_konsumen"
                                    name="batal-hp_konsumen">
                            </div>
                            <div class="form-group">
                                <label>Email Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-email_konsumen"
                                    name="batal-email_konsumen">
                            </div>
                            <div class="form-group hidden">
                                <label>Status Konsumen</label>
                                <select class="form-control" id="batal-status_konsumen" name="batal-status_konsumen">
                                    <option value="">-</option>
                                    <option value="Umum">Umum</option>
                                    <option value="TWP">TWP</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sales</label>
                                <input disabled type="text" class="form-control" id="batal-sales" required
                                    name="batal-sales">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <input readonly type="hidden" class="form-control" id="batal-id_konsumen"
                                name="batal-id_konsumen">
                            <input readonly type="hidden" class="form-control" id="batal-id_mkdt" name="batal-id_mkdt">
                            <input readonly type="hidden" class="form-control" id="batal-id_kavling"
                                name="batal-id_kavling">
                            <div class="divider">
                                <div class="divider-text">TUNAI/KPR</div>
                            </div>
                            <div class="form-group">
                                <label>Tunai/KPR</label>
                                <select disabled class="form-control" id="batal-is_kpr" name="batal-is_kpr">
                                    <option value="">-</option>
                                    <option value="0">TUNAI/CASH KERAS</option>
                                    <option value="2">TUNAI/CASH BERTAHAP</option>
                                    <option value="1">KPR</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Subsidi/Non-Subsidi</label>
                                <select disabled class="form-control" id="batal-is_subsidi" name="batal-is_subsidi">
                                    <option value="">-</option>
                                    <option value="0">Non-Subsidi</option>
                                    <option value="1">Subsidi</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">ACC KPR</label>
                                <input readonly type="text" class="form-control num" id="batal-harga_kpr_acc"
                                    name="batal-harga_kpr_acc">
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">Turun KPR</label>
                                <input readonly type="text" class="form-control num" id="batal-harga_penambahan_um"
                                    name="batal-harga_penambahan_um">
                            </div>
                            <div class="divider">
                                <div class="divider-text">Penambahan Biaya</div>
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">Penambahan Biaya</label>
                                <input disabled type="text" class="form-control num totalbb" id="batal-harga_penambahan"
                                    name="batal-harga_penambahan">
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">Keterangan Penambahan Biaya</label>
                                <textarea disabled name="batal-keterangan_penambahan_biaya"
                                    id="batal-keterangan_penambahan_biaya" class="form-control batal-fm" cols="30"
                                    rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="divider">
                                <div class="divider-text">Total Uang Muka</div>
                            </div>
                            <div class="form-group">
                                <label for="batal-total_biaya_um">Total Uang Muka</label>
                                <input readonly type="text" class="form-control num" id="batal-total_biaya_um"
                                    name="batal-total_biaya_um">
                            </div>

                            <hr>
                            <div class="form-group">
                                <label for="batal-sudah_bayar_um">Sudah Bayar Uang Muka</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sudah_bayar_um"
                                    name="batal-sudah_bayar_um">
                            </div>
                            <div class="form-group">
                                <label for="batal-sisa_tagihan_um">Sisa Tagihan Uang Muka</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sisa_tagihan_um"
                                    name="batal-sisa_tagihan_um">
                            </div>
                            <!-- <div class="form-group">
                                <label for="batal-persentase_bayar_tagihan_um">Persentase</label>
                                <input disabled type="text" class="form-control" style="text-align:right" readonly id="batal-persentase_bayar_tagihan_um" name="batal-persentase_bayar_tagihan_um">
                            </div> -->
                            <div class="divider">
                                <div class="divider-text">Total Biaya-biaya</div>
                            </div>
                            <div class="form-group">
                                <label for="batal-total_biaya_bb">Total Biaya-biaya</label>
                                <input readonly type="text" class="form-control num" id="batal-total_biaya_bb"
                                    name="batal-total_biaya_bb">
                            </div>

                            <hr>
                            <div class="form-group">
                                <label for="batal-sudah_bayar_bb">Sudah Bayar Biaya-biaya</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sudah_bayar_bb"
                                    name="batal-sudah_bayar_bb">
                            </div>
                            <div class="form-group">
                                <label for="batal-sisa_tagihan_um">Sisa Tagihan Biaya-biaya</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sisa_tagihan_bb"
                                    name="batal-sisa_tagihan_bb">
                            </div>
                            <!-- <div class="form-group">
                                <label for="batal-persentase_bayar_tagihan_bb">Persentase</label>
                                <input disabled type="text" class="form-control" style="text-align:right" readonly id="batal-persentase_bayar_tagihan_bb" name="batal-persentase_bayar_tagihan_bb">
                            </div> -->
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="divider">
                                <div class="divider-text">Batal</div>
                            </div>
                            <div class="form-group">
                                <label for="keterangan_batal">Keterangan Batal</label>
                                <textarea class="form-control" id="batal-keterangan_batal" name="batal-keterangan_batal"
                                    rows="3" placeholder="Keterangan"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Surat Batal</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" accept="application/pdf"
                                        name="file_surat_batal" id="file_surat_batal" />
                                    <label class="custom-file-label" id="label-file_surat_batal"
                                        for="label-file_surat_batal">Upload Surat Batal</label>
                                    <a href="" target=_blank id="list-file_surat_batal">klik untuk melihat surat
                                        batal</a>
                                </div>
                            </div>
                            <small id="last_update-batal_mkdt" class="text-muted"></small>
                            <div class="divider">
                                <div class="divider-text">Pengembalian Dana ke Konsumen</div>
                            </div>
                            <div class="form-group">
                                <label for="batal-sertifikat_luas">Nominal</label>
                                <input type="text" class="form-control num" id="batal-refund" name="batal-refund">
                            </div>
                            <small id="last_update-batal_keuangan" class="text-muted"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="btn-simpan_batal_mkdt" class="btn btn-primary" onclick="simpan_batal()">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!--#################################### Modal Filter/Setting #########################################-->
<div class="modal modal-slide-in fade" id="modal-setting-filter">
    <div class="modal-dialog sidebar-sm">
        <div class="add-new-record modal-content pt-0">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Filter</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <!-- <div id="modal-filter"></div> -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-list-rumah-belum-selesai">
    <div class="modal-dialog modal-lg">
        <div class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h1 class="modal-title" id="exampleModalLabel">Kavling Belum Selesai di Bangun</h1>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="table-responsive">
                    <table class="table" id="table-selesai">
                        <thead>
                            <tr>
                                <th rowspan=2>No</th>
                                <th rowspan=2>Kavling</t>
                                <th rowspan="2">Progress</th>
                                <th colspan="2">Tanggal</th>
                                <th rowspan="2">Keterangan</th>
                            </tr>
                            <tr>
                                <th>Pembangunan</th>
                                <th>Rencana Selesai</th>
                            </tr>
                        </thead>
                        <tbody id="list-rumah-belum-selesai-here"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalEwe" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Filter & Keterangan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-filter"></div>
            </div>
        </div>
    </div>
</div>
<div class="fade modal text-left" id="modal_othersdetail" aria-labelledby="modal_othersdetail" role="dialog" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Detail</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <form class="modal-content pt-0 add-new-record" id="fm-fotherproduksi">
                <div class="modal-body">
                    <p class="modal-title label_alamat" id="label_fothersproduksi"></p><br><span><strong>Luas di Siteplan :</strong><br><span class="t_luas_planning"></span></span><br><span><strong>Keterangan Planning :</strong><br><span class="t_keterangan_planning"></span></span><br><br><span><strong>Luas di Sertifikat :</strong><br><span class="t_luas_legal"></span></span><br><span><strong>Keterangan Legal :</strong><br><span class="t_keterangan_legal"></span></span><br><br><span><strong>Luas di Lapangan :</strong><br><span class="t_luas_produksi"></span></span><br><span><strong>Keterangan Produksi :</strong><br><span class="t_keterangan_keterangan"></span></span>
                    <hr>
                    <div class="form-group"><label for="f_progres_jalan">Progres</label> <input name="f_detail_progres_jalan" class="form-control-range" id="f_detail_progres_jalan" disabled type="range" max="100" min="0" oninput='$(".r_progres").html($(this).val())' step="5"> <span class="r_progres"></span><span>%</span></div>
                </div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Tutup</button></div>
            </form>
        </div>
    </div>
</div>
<!-- BEGIN: Vendor JS-->
<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<!-- BEGIN Vendor JS-->

<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url() ?>/assets/js/magic-wand.min.js"></script>
<script src="<?= base_url() ?>/assets/js/konva.min.js"></script>
<script src="<?= base_url() ?>/assets/js/jquery.richtext.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/html2canvas/html2canvas.min.js"></script>
<!-- <script src="<?= base_url() ?>/assets/js/scripts.js"></script> -->
<!-- END: Page Vendor JS-->
<script>
    function applyLoadingEffect(selector) {
        $(selector).addClass("input-loading");
    }

    function removeLoadingEffect(selector) {
        $(selector).removeClass("input-loading")
    }
    let data_um = [],
        data_bb = []
    //sewwtalert2 fix error cant type after open modal
    $.fn.modal.Constructor.prototype._enforceFocus = function() {};

    const list_pekerjaan = {
        "Pekerjaan Persiapan": ["Persiapan Pembersihan lokasi", "Pemasangan bouplank"],
        "Pekerjaan Pondasi": ["Galian tanah pondasi", "Pasangan Pondasi Batu kali", "Pasangan Pondasi plat Setempat", "Instalasi Pipa Air Kotor pendam 3 inch", "Instalasi Pipa Air Kotor Pendam 4 inch", "Instalasi Pipa Air Kotor Pendam lebih dari 4 inch", "Urugan tanah pondasi tinggi 0-20 cm dari jalan lingkungan", "Urugan tanah pondasi tinggi 20-50 cm dari jalan lingkungan", "Urugan tanah pondasi tinggi lebih dari 50 cm dari jalan lingkungan"],
        "Pekerjaan Pembesian/Kontruksi": ["Rangkaian pemasangan besi/tulangan pada sloof Bawah", "Rangkaian pemasangan Pasang besi/tulangan pada Ring Balok/balok atas", "Rangkaian pemasangan besi/tulangan pada kolom", "Rangkaian pemasangan besi/tulangan pada sopi-sopi/Gewel/gunungan", "Rangkaian pemasangan pertemuan Besi Tulangan antara Kolom dan Sloof Bawah", "Rangkaian pemasangan pertemuan Tulangan antara Kolom dan Ring Balk/balok atas", "Rangkaian pemasangan pertemuan Tulangan antara Kolom, Ring Balk/balok atas dan sopi-sopi/gunungan", ],
        "Pekerjaan Cor Beton": ["Cor Beton sloof bawah", "Cor Beton Ring Balk/balok atas", "Tangga Beton (jika rumah 2 lantai)", "Plat Lantai Beton lantai 2", "Plat Lantai Beton lantai"],
        "Pekerjaan Pasangan Dinding": ["Pasang Bata/Batako/Bata Ringan/Jenis lain", "Plesteran tebal", "Acian semen", "Pasangan keramik dinding kamar mandi", "Pasangan keramik dinding dapur"],
        "Pekerjaan Atap": ["Rangka atap & Kuda Kuda", "Tutup atap/genting", "Genting bubung", "Listplang kayu", "Talang sudut"],
        "Pekerjaan Instalasi": ["Instalasi Air bersih", "Instalasi/Jaringan Listrik"],
        "Pekerjaan Langit-langit (Plapond)": ["Rangka plapond", "Penutup plapond"],
        "Pekerjaan Lantai": ["Lantai kerja (pasir lantai)", "Penutup lantai"],
        "Pekerjaan kusen": ["Kusen/pintu Utama", "Jendela Utama/jendela teras", "Kusen/pintu Kamar", "Jendela Kamar"],
        "Pekerjaan Kaca dan Kunci": ["Pasang kaca jendela", "Pasang kunci tanam pintu", "Pasang kunci kamar mandi", "Pasang engsel pintu", "Pasang engsel jendela", "Pasang tulak angin/Hak angin /windows stay"],
        "Pekerjaan Sanitasi": ["Pasang closet", "Pasang washtafel", "Pasang bak mandi", "Pasang bak cuci piring", "Septictank"],
        "Pekerjaan Finishing & Pegecatan": ["Pengecatan kusen", "Pengecatan pintu dan jendela", "Pengecatan Plapond", "Pengecatan tembok"]
    };

    //carousel
    $('.carousel').carousel('pause')

    //datepicker
    var fp = flatpickr(".flatpickr-human-friendly", {
            altInput: true,
            altFormat: 'F j, Y',
            dateFormat: 'Y-m-d'
        }),
        sp,
        dtt = [], //data point from selection
        batchdtt = [], // multiple data point from selection
        kav, //kavling poly
        imageInfo,
        bml_old = 0, //batch mask old
        batchMask = [], //for multiple selection
        dtt_first = [], //for multiple selection
        sceneWidth = screen.width,
        sceneHeight = Math.min(window.innerHeight, window.innerHeight * 0.7);
    //declare stage
    var stage = new Konva.Stage({
        height: sceneHeight,
        width: sceneWidth,
        container: "konva-holder",
        draggable: true
    });

    //declare layer
    var siteplan = new Konva.Layer(), //siteplan layer
        masked = new Konva.Layer(), //selection layer
        // datal = new Konva.Layer(), //kavling shape layer
        group = new Konva.Group({ //group for tooltip
            visible: false,
        }),
        shape_ket = new Konva.Image({
            x: 10,
            y: 10,
            draggable: true,
            stroke: 'red',
            scaleX: 2,
            scaleY: 2,
        }),

        manual_selection = new Konva.Group(),

        maskedGroup = new Konva.Group(), //group for selection line and number
        tooltip = new Konva.Text({ //tooltip text on hovering at kavling
            text: '',
            fontFamily: 'Calibri',
            fontSize: 12,
            padding: 5,
            textFill: 'white',
            fill: 'black',
            text: 'vertical align',
            alpha: 0.75,
        }),
        tooltipbg = new Konva.Rect({ //tooltip bg on hovering at kavling
            width: 270,
            height: 57,
            stroke: "black",
            strokeWidth: 1,
            fill: "#f2ff7d",
        })

    Konva.hitOnDragEnabled = true; //for zoom on touchscreen

    group.add(tooltipbg, tooltip) //grouping tooltip bg & text
    siteplan.add(group);
    masked.add(shape_ket);
    masked.add(group);

    // siteplan img object :
    var imageObj = new Image();
    imageObj.onload = function() {

        sp = new Konva.Image({
            x: 0,
            y: 0,
            image: imageObj,
            width: imageObj.width,
            height: imageObj.height,
            globalCompositeOperation: 'overlay'
        });

        // add image to the layer
        siteplan.add(sp);
    };


    //siteplan src
    imageObj.src = base_url + '' + dt_proyek.siteplan;

    //deklarasi kanvas untuk kavling
    window.onload = function() {
        colorThreshold = 15;
        blurRadius = 1;
        simplifyTolerant = 0;
        simplifyCount = 30;
        hatchLength = 4;
        hatchOffset = 0;

        imageInfo = null;
        cacheInd = null;
        mask = null;
        oldMask = null;
        downPoint = null;
        allowDraw = false;
        addMode = false;
        currentThreshold = colorThreshold;

        showThreshold();

        //imginfo
        var img = imageObj;
        var cvs = masked;
        cvs.width = img.width;
        cvs.height = img.height;
        imageInfo = {
            width: img.width,
            height: img.height,
            context: cvs.getContext("2d", {
                willReadFrequently: true
            })._context
        };
        mask = null;

        var tempCtx = document.createElement("canvas").getContext("2d", {
            willReadFrequently: true
        });
        tempCtx.canvas.width = imageInfo.width;
        tempCtx.canvas.height = imageInfo.height;
        tempCtx.drawImage(img, 0, 0);
        imageInfo.data = tempCtx.getImageData(0, 0, imageInfo.width, imageInfo.height);

        //load kavling dari database
        load_kavling(roleid == 1 || roleid == 7);

        // $("#pilih-divisi").select2("val", roleid)
        // change_div();
        load_menu();


        // scaling layer to fit stage
        let konva_w = parseFloat($("#konva-holder").width())
        let konva_h = parseFloat($("#konva-holder").height())
        let l
        if (konva_w > konva_h)
            l = parseFloat($("#konva-holder").width()) / imageObj.width;
        else
            l = parseFloat($("#konva-holder").height()) / imageObj.height;

        stage.scale({
            x: l,
            y: l
        });

        group.scale({
            x: 1 / l,
            y: 1 / l
        })

        $('#filter-side').css('height', konva_h)

        if ($(window).width() < 768) {
            $('#filter-side').children().appendTo('#modal-filter');
            $('#filter-side').remove();
            $("#btn-filter").removeClass("hidden")
        }



    }

    var line_ms = new Konva.Line({
        points: [0, 0],
        stroke: "red",
        strokeWidth: 2,
        dash: [5, 5],
        opacity: 1,
        closed: !0,
        id: "line_sel"
    });

    //refresh kavling setelah ganti divisi
    $("#pilih-divisi").change(function() {
        change_div()
    });

    function change_div() {
        $("#tambah_jalan").prop("checked", 0)
        hapus_seleksi(); //hapus seleksi kavling

        //tampilkan menu sesuai divisi jika login sebagai admin
        if (roleid == 1) {
            let va = $("#pilih-divisi option:selected").val();
            $(".div_menu").addClass("hidden");
            if (va == 6) {
                $("#planning_menu").removeClass("hidden")
            } else if (va == 4) {
                $("#mkdt_menu").removeClass("hidden")
            } else if (va == 7) {
                $("#produksi_menu").removeClass("hidden")
            } else if (va == 8) {
                $("#sales_menu").removeClass("hidden")
            } else if (va == 3) {
                $("#keuangan_menu").removeClass("hidden")
            } else if (va == 9) {
                $("#direksi_menu").removeClass("hidden")
            } else if (va == 0) {
                $("#others_menu").addClass("hidden")
            } else {
                $("#others_menu").removeClass("hidden")
            }
        }

        //load ulang kavling
        load_kavling();
    }

    function load_menu() {
        // let va = $("#pilih-divisi option:selected").val();
        $.ajax({
            url: base_url + 'home/getMenuBtn',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                // id_role: roleid
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(r) {
                $("#loading").addClass("hidden");
                const menu = r.menu;
                $("#menu_here").html(menu);
                if (roleid == 7 && has_akses.proyek == false) {
                    $(".btn-prod").hide()
                    swal('warning', 'Peringatan!', "Kamu tidak bisa melakukan perubahan untuk proyek ini")
                } else {
                    $(".btn-prod").show()
                }
                if (roleid == 5 && has_akses.legal == false) {
                    $("#edit_kavling_batch").hide()
                    swal('warning', 'Peringatan!', "Kamu tidak bisa melakukan perubahan untuk proyek ini")
                } else {
                    $("#edit_kavling_batch").show()
                }
            },
            error: function() {
                $("#loading").addClass("hidden");

            }
        })
    }

    function buat_nominatif() {

    }

    var stroke, fill, strokeWidth, dashed;
    let filterwarnahitung = {};

    function set_fill2(e) { //test set fill dengan config dari db
        // console.log(conf[e])
        if (!conf[e])
            e = "Warna Tidak Ditemukan"
        set_fill(conf[e].fill, conf[e].stroke, conf[e].strokeWidth, conf[e].dashed)
        return e;
    }

    function hitung_kavling(fill) {
        let e = fill.fill
        let p = filterwarnahitung[e] ? filterwarnahitung[e] : 0;
        filterwarnahitung[e] = p + 1;

    }

    function set_keterangan_warna() {
        $("#keterangan-warna-here").html(" ")
        let div = "",
            kv
        // console.log(filterwarna)
        $.each(filterwarna, function(i, v) {
            if (v) {
                div += `
                <div class="divider">
                    <div class="divider-text">${i} ${i == 'Subsidi' || i == 'Komersil' ? 'Dipasarkan' : ''}</div>
                </div>`;
                const sortedKeys = Object.keys(v).sort();

                // Step 2: Create a new object with sorted keys
                const sortedObj = {};
                sortedKeys.forEach(key => {
                    sortedObj[key] = v[key];
                });

                $.each(sortedObj, function(x, y) {

                    kv = (x == "Def") ? "Data yang bisa diolah" : x;

                    div += `<div class="form-group row">
                                <div class="btn col-2 ml-1" style="background-color:${y}"></div>
                                <div class="col-9"> ${kv} (${filterwarnahitung[x]})</div>
                            </div>`;
                })
            }
        })
        $("#keterangan-warna-here").html(div)
    }
    //load shape kavling
    function load_kavling(refresh = false) {
        hapus_seleksi();
        filterwarna = {
            Status: null,
            Subsidi: null,
            Komersil: null,
            'Lain-lain': null
        };
        filterwarnahitung = {};

        siteplan.find('Line').forEach(line => line.destroy());

        let va = $("#pilih-divisi option:selected").val();
        wr_pembangunan = [];
        $.ajax({
            url: base_url + 'siteplan/get_kavling_all',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek,
                id_cluster: filter.id_cluster,
                id_jalan: filter.id_jalan,
                id_role: va
            },
            dataType: 'json',
            beforeSend: () => $("#loading").removeClass("hidden"),
            success: function(result) {
                $("#loading").addClass("hidden");
                csrfHash = result.token;
                stroke = fill = strokeWidth = dashed = "";

                let r = result['data'],
                    data2,
                    hit,
                    tp_rumah,
                    subsidi;



                for (var p = 0; p < r.length; p++) {
                    tp_rumah = r[p].tipe_rumah
                    no_tp_rumah = r[p].no_tipe_rumah

                    subsidi = (r[p].is_subsidi == 1) ? "Subsidi" : "Komersil"

                    //set default shape color 
                    // set_fill("#fff67a", "#000000", 0, null)
                    hit = {
                        fill: set_fill2("Def"),
                        tipe: 'Lain-lain'
                    }

                    if (r[p].tanggal_rencana_selesai_pembangunan != null) {
                        if (r[p].tanggal_selesai_pembangunan == null) {
                            if (daysBetween(today_date, r[p].tanggal_rencana_selesai_pembangunan) < 3) {
                                wr_pembangunan.push({
                                    progres: r[p].progres_bangunan ? r[p].progres_bangunan : 0,
                                    nama_jalan: r[p].nama_jalan,
                                    no_kavling: r[p].no_kavling,
                                    tipe: r[p].tipe_rumah,
                                    keterangan: r[p].keterangan_produksi,
                                    tanggal_pembangunan: r[p].tanggal_pembangunan,
                                    tanggal_rencana_selesai_pembangunan: r[p].tanggal_rencana_selesai_pembangunan,
                                    tanggal_selesai_pembangunan: r[p].tanggal_selesai_pembangunan
                                })
                            }
                        }
                    }


                    // if (r[p].tanggal_selesai_pembangunan != '0000-00-00' || r[p].tanggal_selesai_pembangunan != '' || r[p].tanggal_selesai_pembangunan != null) {

                    // }

                    if (r[p].harga_akhir) {
                        tp_rumah = r[p].hj_tipe_rumah
                        no_tp_rumah = r[p].hj_no_tipe_rumah
                        hit = set_fill2(r[p].hj_tipe_rumah)
                        hit = {
                            fill: hit,
                            tipe: subsidi
                        }
                        // if (r[p].is_subsidi == 1)
                        //     hit = set_fill2("Subsidi")
                        // else
                        //     hit = set_fill2("Non-Subsidi")
                    }


                    if (r[p].status_mkdt) {
                        hit = set_fill2(r[p].status_mkdt)
                        if (hit == "Akad" || hit == "Booking") {
                            hit = hit + " " + subsidi
                        }
                    } else {
                        if (r[p].progres_bangunan == "100") {
                            // jika sudah selesai
                            hit = set_fill2("Ready Stock")
                        }
                    }


                    if (va == 3) { //keuangan
                        if (r[p].is_lunas == 0 || r[p].is_lunas == null || r[p].is_lunas == "undefined") {

                            if (r[p].jatuh_tempo_tgl != null && r[p].jatuh_tempo_tgl != "0000-00-00") {
                                if (daysBetween(today_date, r[p].jatuh_tempo_tgl) < 7)
                                    hit = set_fill2('Jatuh Tempo')
                            }
                        } else if (r[p].is_lunas == 1) {
                            hit = set_fill2('Lunas')
                            if (r[p].status_mkdt == 'Akad')
                                hit = set_fill2('Akad')
                        }
                        if (r[p].is_batal == 1)
                            hit = set_fill2("Batal")
                    } else if (va == 4) { //mkdt
                        if (r[p].perintah_bangun == 1)
                            hit = set_fill2("Perintah Bangun")

                        //status kavling
                        if (r[p].status_mkdt == "Batal") { //jika batal
                            if (r[p].is_batal == 1)
                                hit = set_fill2("Batal")
                            else
                                hit = set_fill2(r[p].status_mkdt)
                        } else if (r[p].status_mkdt == "Akad") { //jika akad
                            hit = set_fill2('Akad')
                        } else if (r[p].status_mkdt == "Booking") {
                            //jika booking
                            if (r[p].booking_tgl != null && r[p].booking_tgl != "0000-00-00") {
                                // if (r[p].is_kpr == 1)
                                //     hit = set_fill2('KPR')
                                // else if (r[p].is_kpr == 0)
                                //     hit = set_fill2('Tunai')
                                // if (r[p].mkdt_is_subsidi == 1) {
                                //     hit = set_fill2('Subsidi')
                                // } else if (r[p].mkdt_is_subsidi == 0) {
                                //     hit = set_fill2('Non-Subsidi')
                                // }
                            }
                            //jika turun sp3k
                            if (r[p].sp3k_tgl != null && r[p].sp3k_tgl != "0000-00-00") {
                                hit = set_fill2('SP3K')
                            }
                        }
                    } else if (va == 5) { //legal
                        if (r[p].id_legal)
                            hit = set_fill2("Sudah Diisi")
                        // if (
                        //     r[p].sertifikat_tgl != null && r[p].sertifikat_tgl != "0000-00-00" &&
                        //     r[p].sertifikat_masa_berlaku != null && r[p].sertifikat_masa_berlaku != "0000-00-00" &&
                        //     r[p].bphtb_masa_berlaku != null && r[p].bphtb_masa_berlaku != "0000-00-00" &&
                        //     r[p].imb_tgl != null && r[p].imb_tgl != "0000-00-00" &&
                        //     r[p].bphtb_tgl != null && r[p].bphtb_tgl != "0000-00-00" &&
                        //     r[p].sertifikat_no_hgb != null &&
                        //     r[p].sertifikat_no_split != null &&
                        //     r[p].imb_no_induk != null &&
                        //     r[p].imb_no_split != null &&
                        //     r[p].nop_pbb != null &&
                        //     r[p].pph != null
                        // )
                        //     hit = set_fill2("Sudah Diisi")
                        // else {
                        //     if (
                        //         r[p].sertifikat_tgl != null && r[p].sertifikat_tgl != "0000-00-00" ||
                        //         r[p].sertifikat_masa_berlaku != null && r[p].sertifikat_masa_berlaku != "0000-00-00" ||
                        //         r[p].bphtb_masa_berlaku != null && r[p].bphtb_masa_berlaku != "0000-00-00" ||
                        //         r[p].imb_tgl != null && r[p].imb_tgl != "0000-00-00" ||
                        //         r[p].bphtb_tgl != null && r[p].bphtb_tgl != "0000-00-00" ||
                        //         r[p].sertifikat_no_hgb != null ||
                        //         r[p].sertifikat_no_split != null ||
                        //         r[p].imb_no_induk != null ||
                        //         r[p].imb_no_split != null ||
                        //         r[p].nop_pbb != null ||
                        //         r[p].pph != null
                        //     )
                        //         hit = set_fill2("Sebagian Diisi")
                        // }
                        // if (r[p].sertifikat_masa_berlaku != null && r[p].sertifikat_masa_berlaku != "0000-00-00") {
                        //     if (daysBetween(today_date, r[p].sertifikat_masa_berlaku) < 30)
                        //         hit = set_fill2("h-30 Kadaluarsa") //warna merah
                        //     else if (daysBetween(today_date, r[p].sertifikat_masa_berlaku) < 60)
                        //         hit = set_fill2("h-60 Kadaluarsa") // warna orange
                        // }

                        // if (r[p].bphtb_masa_berlaku != null && r[p].bphtb_masa_berlaku != "0000-00-00") {
                        //     if (daysBetween(today_date, r[p].bphtb_masa_berlaku) < 30)
                        //         hit = set_fill2("h-30 Kadaluarsa") //warna merah
                        //     else if (daysBetween(today_date, r[p].bphtb_masa_berlaku) < 60)
                        //         hit = set_fill2("h-60 Kadaluarsa") // warna orange
                        // }

                    } else if (va == 7) { //produksi
                        // if (r[p].status_mkdt)
                        //     hit = set_fill2(r[p].status_mkdt)


                        if (r[p].status_mkdt == "Akad") {
                            hit = set_fill2("Akad " + subsidi)


                            // if (r[p].perintah_bangun == 1)
                            //     hit = set_fill2("Perintah Bangun")
                            // if (parseInt(r[p].progres_bangunan) > 0 && parseInt(r[p].progres_bangunan) < 100) {
                            //     hit = set_fill2("Pembangunan") // warna merah
                            // }else if (parseInt(r[p].progres_bangunan) == 100) {
                            //     // jika sudah selesai
                            //     hit = set_fill2("Akad "+ subsidi)
                            // }
                        } else if (r[p].status_mkdt == "Booking") {
                            hit = set_fill2("Booking " + subsidi)

                            if (r[p].perintah_bangun == 1)
                                hit = set_fill2("Perintah Bangun")

                            // if (r[p].perintah_bangun == 1)
                            //     hit = set_fill2("Perintah Bangun")

                            if (parseInt(r[p].progres_bangunan) > 0 && parseInt(r[p].progres_bangunan) < 100) {
                                hit = set_fill2("Pembangunan") // warna merah
                            } else if (parseInt(r[p].progres_bangunan) == 100) {
                                // jika sudah selesai
                                hit = set_fill2("Bangunan 100%")
                            }
                        } else {
                            if (r[p].perintah_bangun == 1)
                                hit = set_fill2("Perintah Bangun")
                            if (parseInt(r[p].progres_bangunan) > 0 && parseInt(r[p].progres_bangunan) < 100) {
                                hit = set_fill2("Pembangunan") // warna merah
                            } else if (parseInt(r[p].progres_bangunan) == 100) {
                                // jika sudah selesai
                                hit = set_fill2("Ready Stock") // warna merah
                            }
                        }

                        //jika ada komplain (dari sales)
                        if (r[p].status_komplain == 1 || r[p].status_komplain == 2 || r[p].status_komplain == 3)
                            hit = set_fill2("Komplain")

                    } else if (va == 8) { //sales
                        //jika bangunan sudah 100%
                        if (r[p].progres_bangunan == "100") {
                            hit = set_fill2("Pembangunan Selesai") // warna biru
                            if (r[p].id_mkdt == null)
                                hit = set_fill2("Ready Stock") // warna biru
                        }

                        //jika sudah akad
                        if (r[p].status_mkdt == "Akad")
                            hit = set_fill2("Akad") //warna ungu
                        //jika ada komplain
                        if (r[p].status_komplain == 1 || r[p].status_komplain == 2 || r[p].status_komplain == 3)
                            hit = set_fill2("Komplain") //warna merah
                        // jika sudah dicek
                        if (r[p].is_checked == 1)
                            hit = set_fill2("Sudah dicek") // warna orange
                        // jika sudah dicek
                        if (r[p].is_serah_terima == 1)
                            hit = set_fill2("Serah Terima") // warna hijau

                    } else if (va == 9) {
                        if (r[p].harga_akhir) {
                            tp_rumah = r[p].hj_tipe_rumah
                            no_tp_rumah = r[p].hj_no_tipe_rumah
                            hit = set_fill2(r[p].hj_tipe_rumah)

                            //ubah var hit ke object
                            hit = {
                                fill: hit,
                                tipe: subsidi
                            }

                        }
                    }


                    // }


                    //harga jual
                    let id_hargajual = r[p].harga_akhir;
                    r[p].harga_akhir = (r[p].hargajual) ? num_format(r[p].hargajual) + "(" + format_date(r[p].tgl_harga) + ")" : '-';



                    if (typeof hit !== 'object') {
                        hit = {
                            fill: hit,
                            tipe: 'Status'
                        }
                    }

                    // return;

                    //set untuk filter warna
                    filterwarna[hit.tipe] = {
                        ...filterwarna[hit.tipe],
                        [hit.fill]: conf[hit.fill].fill
                    }


                    // console.log(hit.fill, conf[hit.fill].fill);

                    hitung_kavling(hit)
                    //data di tiap kavling harus disesuaikan dengan divisi yang dipilih
                    kav = new Konva.Line({
                        points: JSON.parse("[" + r[p].points + "]"),
                        // lineCap: 'round',
                        // lineJoin: 'round',
                        // stroke: stroke,
                        fill: conf[hit.fill].fill,
                        // strokeWidth: strokeWidth,
                        dash: dashed,
                        opacity: 1,
                        closed: true,
                        globalCompositeOperation: 'multiply',
                        data: {
                            nama_jalan: r[p].nama_jalan,
                            no_kavling: r[p].no_kavling,
                            id_produksi: r[p].id_produksi,
                            id_legal: r[p].id_legal,
                            id_keuangan: r[p].id_keuangan,
                            id_sales: r[p].id_sales,
                            id_planning: r[p].id_planning,
                            id_mkdt: r[p].id_mkdt,
                            id_umum: r[p].id_umum,
                            id_direksi: r[p].id_direksi,
                            tipe: 'kavling',
                            status_tanah: r[p].status_tanah,
                            luas_tanah: r[p].luas_tanah,
                            is_batal: r[p].is_batal,
                            // total_biaya: ktotal_biaya,
                            // sudah_bayar: ksudah_bayar
                        },
                        data2: {
                            id_hargajual: id_hargajual,
                            status_mkdt: r[p].status_mkdt,
                            id_tipe: r[p].id_tipe,
                            tipe_rumah: tp_rumah,
                            no_tipe_rumah: no_tp_rumah,
                            id_gambar_kerja: r[p].id_gambar_kerja,
                            harga_akhir: r[p].harga_akhir,
                            harga_akhir_tgl: r[p].harga_akhir_tgl,
                            harga_akhir_oleh: r[p].harga_akhir_oleh_username,
                            id_serah_terima: r[p].id_serah_terima,
                            id_komplain: r[p].id_komplain,
                        },
                        id: 'kav' + r[p].id_kavling
                    });

                    siteplan.add(kav);
                }
                set_keterangan_warna()
                cek_tanggal_pembangunan(refresh)
            },
            error: function(xhr, st, err) {
                $("#loading").addClass("hidden")
                return swal("error", err);
            },
        });

        //load jalan fasos rth
        $.ajax({
            url: base_url + 'siteplan/get_others',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek,
                id_role: va
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(result) {
                stroke = ""
                fill = ""
                strokeWidth = ""
                dashed = ""

                var r = result.data

                for (var p = 0; p < r.length; p++) {
                    if (r[p].tipe == "jalan")
                        set_fill("#ccc", "#000", "0", null) // warna abu
                    else if (r[p].tipe == "fasos")
                        set_fill("#9000ff", "#000", "0", null) // warna ungu
                    else if (r[p].tipe == "rth")
                        set_fill("#0f0", "#000", "0", null) // warna merah
                    kav = new Konva.Line({
                        points: JSON.parse("[" + r[p].points + "]"),
                        fill: fill,
                        dash: dashed,
                        opacity: 1,
                        closed: true,
                        globalCompositeOperation: 'multiply',
                        data: {
                            tipe: r[p].tipe,
                            nama_jalan: r[p].nama_jalan,
                        },
                        data2: {},
                        id: 'others' + r[p].id
                    });
                    siteplan.add(kav);
                }
            },
            error: function() {
                Swal.fire({

                    icon: 'error',
                    title: "Terjadi kesalahan",
                    showConfirmButton: false,
                    //timer: 1500
                })
                return;
            }
        });
        group.hide();
    }

    //zoom
    var scaleBy = 1.1;
    stage.on('wheel', (e) => {
        // stop default scrolling
        menuNode.style.display = 'none';
        e.evt.preventDefault();

        var oldScale = stage.scaleX();
        var pointer = stage.getPointerPosition();

        var mousePointTo = {
            x: (pointer.x - stage.x()) / oldScale,
            y: (pointer.y - stage.y()) / oldScale,
        };

        // how to scale? Zoom in? Or zoom out?
        let direction = e.evt.deltaY > 0 ? -1 : 1;

        // when we zoom on trackpad, e.evt.ctrlKey is true
        // in that case lets revert direction
        if (e.evt.ctrlKey) {
            direction = -direction;
        }

        var newScale = direction > 0 ? oldScale * scaleBy : oldScale / scaleBy;

        stage.scale({
            x: newScale,
            y: newScale
        });

        group.scale({
            x: 1 / newScale,
            y: 1 / newScale
        })
        var newPos = {
            x: pointer.x - mousePointTo.x * newScale,
            y: pointer.y - mousePointTo.y * newScale,
        };
        stage.position(newPos);
    });

    //clear selction
    var idss, idsb, idst, idstb, ajal, seljal;

    function hapus_seleksi() {
        line_ms.points([0, 0])

        bml_old = 0;
        dtt_first = [];

        idss = stage.find('#sel')[0]; //find selection line
        idst = stage.find('#tsel')[0]; //find selection text


        // ajal = stage.find('#ajal')[0];
        // if(ajal) ajal.destroy();

        //remove point select jalan
        seljal = stage.find('#seljal');
        for (let p = 0; p <= seljal.length; p++) {
            if (seljal[p])
                seljal[p].destroy();
        }
        dtt = [];



        if (idss)
            idss.destroy(); //destroy shape
        if (idst)
            idst.destroy(); //destroy shape

        hapus_seleksi_batch() //destroy multiple selection
    }
    //destroy multiple selection
    function hapus_seleksi_batch() {
        idsb = stage.find('#sel'); //find selection line
        idstb = stage.find('#tsel'); //find selection text

        for (let p = 0; p <= idsb.length; p++) {
            if (idsb[p])
                idsb[p].destroy();

            if (idstb[p])
                idstb[p].destroy();
        }

        batchMask = [];
        batchdtt = [];
        editdtt = [];
        siteplan.draw();
    }

    var editdtt = [];

    //event klik kavling
    masked.on('dblclick', function(e) {
        if (!addMode) {
            if (e.evt.button === 0 && e.target.attrs.id) {
                //open detail modal
                lihat_detail();
            }
        }
    })

    // siteplan.on('dbltap', function(e) {
    //     //open detail modal
    //     lihat_detail();
    // })

    //hide tooltip on tap at siteplan
    siteplan.on('tap', function() {
        group.hide();
    })

    //panggil tooltip saat di tap di ponsel
    siteplan.on('tap', function(e) {
        var data = e.target.attrs;

        //posisi tooltip
        var mousePos = stage.getRelativePointerPosition();
        group.position({
            x: mousePos.x + 20,
            y: mousePos.y + 5,
        });

        //text tooltip
        if (data.data) {
            if (!data.data.nama_jalan || !data.data.no_kavling)
                return;
            tooltip.text(
                data.data.nama_jalan +
                " No. " + data.data.no_kavling + "\n" +
                data.data2.no_tipe_rumah + "\n" +
                data.data2.tipe_rumah + " ( " + data.data.luas_tanah + " / " + data.data.status_tanah + ") \n" +
                "HJ: Rp. " + data.data2.harga_akhir +
                ""
            );
            group.moveToTop();
            group.show(); //show tooltip
        }
    })

    siteplan.on('click tap', function(e) {
        var k = e.target, //get shape
            sh = k.attrs, //get attribut shape
            role = $('#pilih-divisi option:selected').val(),
            id_kavling = ''

        if (!sh.id) return false;
        id_kavling = sh.id.substr(3)

        if (!$("#tambah_jalan").prop('checked')) {
            addMode = e.evt.ctrlKey;

            //jika hak akses = planning dan pilihan data yang ditampilkan = planning
            if (roleid == 1 || roleid == 4 || roleid == 6 || roleid == 7) {
                if (addMode) {
                    if (sh.data.tipe != "kavling") {
                        return swal('error', 'Terjadi kesalahan', 'Multiple Selection hanya untuk data kavling ')
                    }
                    // if (editdtt && editdtt[0].data.tipe != 'kavling') {
                    //     return swal('error', 'Terjadi kesalahan', 'Multiple Selection hanya untuk data kavling ')
                    // }
                    editdtt.push(sh)
                    drawBorderEdit(sh)
                } else {
                    hapus_seleksi();
                    editdtt.push(sh)
                    drawBorderEdit(sh)
                }
            } else {
                hapus_seleksi();
                editdtt.push(sh)
                drawBorderEdit(sh)
            }
        }
    })

    stage.on('click tap', function(e) {
        if ($("#tambah_jalan").prop('checked')) {
            dtt = [];
            var pos = this.getRelativePointerPosition();

            var dot = new Konva.Circle({
                x: pos.x,
                y: pos.y,
                fill: 'red',
                radius: 5,
                id: "seljal",
                draggable: true
            })
            manual_selection.add(dot);

            var sj = stage.find("#seljal");
            for (let u = 0; u < sj.length; u++) {
                dtt.push(Math.trunc(sj[u].attrs.x), Math.trunc(sj[u].attrs.y))
            }
            line_ms.points(dtt)
        }
    })
    masked.add(line_ms)
    masked.add(manual_selection)

    manual_selection.on('dragend', function(e) {
        dtt = []
        var sj = stage.find("#seljal");
        for (let u = 0; u < sj.length; u++) {
            dtt.push(Math.trunc(sj[u].attrs.x), Math.trunc(sj[u].attrs.y))
        }

        line_ms.points(dtt)
    })

    //even mouse move data kavling
    var data, mousePos, persentase;
    siteplan.on('mousemove', function(e) {
        data = e.target.attrs;
        // console.log(data);

        //posisi tooltip
        mousePos = stage.getRelativePointerPosition();
        group.position({
            x: mousePos.x + 20,
            y: mousePos.y + 5,
        });
        //text tooltip
        if (data.data) {
            if (!data.data.nama_jalan || !data.data.no_kavling)
                return;
            // if(roleid == 3 || roleid == 1){
            //     console.log(data.data.sudah_bayar)
            //     persentase = (parseInt(data.data.sudah_bayar) > 0)?parseInt(data.data.sudah_bayar)/parseInt(data.data.total_biaya)*100:0;
            //     tooltip.text(
            //         data.data.nama_jalan +
            //         " No. " + data.data.no_kavling + "\n" +
            //         data.data2.no_tipe_rumah + "\n" +
            //         data.data2.tipe_rumah + " ( " + data.data.status_tanah + ")\n" +
            //         "Uang Muka: " + t
            //     );
            // }else{
            tooltip.text(
                data.data.nama_jalan +
                " No. " + data.data.no_kavling + "\n" +
                data.data2.no_tipe_rumah + "\n" +
                data.data2.tipe_rumah + " ( " + data.data.luas_tanah + " / " + data.data.status_tanah + ") \n" +
                "HJ: Rp. " + data.data2.harga_akhir
            );
            // }
            group.moveToTop();
            group.show(); //show tooltip

        }

    })
    //even mouse move data kavling
    var data, mousePos, persentase;
    siteplan.on('mousemove', function(e) {
        data = e.target.attrs;
        // console.log(data);

        //posisi tooltip
        mousePos = stage.getRelativePointerPosition();
        group.position({
            x: mousePos.x + 20,
            y: mousePos.y + 5,
        });
        //text tooltip
        if (data.data) {
            if (!data.data.nama_jalan || !data.data.no_kavling)
                return;
            // if(roleid == 3 || roleid == 1){
            //     console.log(data.data.sudah_bayar)
            //     persentase = (parseInt(data.data.sudah_bayar) > 0)?parseInt(data.data.sudah_bayar)/parseInt(data.data.total_biaya)*100:0;
            //     tooltip.text(
            //         data.data.nama_jalan +
            //         " No. " + data.data.no_kavling + "\n" +
            //         data.data2.no_tipe_rumah + "\n" +
            //         data.data2.tipe_rumah + " ( " + data.data.status_tanah + ")\n" +
            //         "Uang Muka: " + t
            //     );
            // }else{
            tooltip.text(
                data.data.nama_jalan +
                " No. " + data.data.no_kavling + "\n" +
                data.data2.no_tipe_rumah + "\n" +
                data.data2.tipe_rumah + " ( " + data.data.luas_tanah + " / " + data.data.status_tanah + ") \n" +
                "HJ: Rp. " + data.data2.harga_akhir
            );
            // }
            group.moveToTop();
            group.show(); //show tooltip

        }

    })

    //highligh kavling 
    siteplan.on('mouseover', function(e) {
        var sh = e.target;
        sh.setAttr("strokeWidth", 4);
        sh.setAttr("stroke", "black");
    })

    //hide tooltip
    siteplan.on('mouseout', function(e) {
        var sh = e.target;
        sh.setAttr("strokeWidth", 0);
        group.hide();
    })

    function getDistance(p1, p2) {
        return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
    }

    function getCenter(p1, p2) {
        return {
            x: (p1.x + p2.x) / 2,
            y: (p1.y + p2.y) / 2,
        };
    }

    var lastCenter = null;
    var lastDist = 0;
    stage.on('touchmove', function(e) {
        e.evt.preventDefault();
        var touch1 = e.evt.touches[0];
        var touch2 = e.evt.touches[1];

        if (touch1 && touch2) {
            // if the stage was under Konva's drag&drop
            // we need to stop it, and implement our own pan logic with two pointers
            if (stage.isDragging()) {
                stage.stopDrag();
            }

            var p1 = {
                x: touch1.clientX,
                y: touch1.clientY,
            };
            var p2 = {
                x: touch2.clientX,
                y: touch2.clientY,
            };

            if (!lastCenter) {
                lastCenter = getCenter(p1, p2);
                return;
            }
            var newCenter = getCenter(p1, p2);

            var dist = getDistance(p1, p2);

            if (!lastDist) {
                lastDist = dist;
            }

            // local coordinates of center point
            var pointTo = {
                x: (newCenter.x - stage.x()) / stage.scaleX(),
                y: (newCenter.y - stage.y()) / stage.scaleX(),
            };

            var scale = stage.scaleX() * (dist / lastDist);

            stage.scaleX(scale);
            stage.scaleY(scale);

            // calculate new position of the stage
            var dx = newCenter.x - lastCenter.x;
            var dy = newCenter.y - lastCenter.y;

            var newPos = {
                x: newCenter.x - pointTo.x * scale + dx,
                y: newCenter.y - pointTo.y * scale + dy,
            };

            group.scale({
                x: 1 / scale,
                y: 1 / scale
            })

            stage.position(newPos);

            lastDist = dist;
            lastCenter = newCenter;
        }
    });

    stage.on('touchend', function() {
        lastDist = 0;
        lastCenter = null;
    });

    function lihat_detail() {
        if (editdtt.length == 0) {
            Swal.fire({

                icon: 'error',
                title: "Terjadi Kesalahan.",
                text: "Tidak ada kavling yang dipilih",
                showConfirmButton: false,
            });
            return;
        }
        last_update("#last_update_legal, #last_update_mkdt, #last_update_keuangan, #last_update_prod")
        var sh = editdtt[0],
            id_kavling = sh.id.substr(3);

        // $("#fm-detail")[0].reset();
        $('#fm-detail input,#fm-detail select').val('');
        $("#tb-data-tagihan-detail").html("");

        if (sh.data.tipe == 'kavling') {
            return detail_kavling(sh, id_kavling)
        } else {
            return detail_others(sh)
        }
    }

    function detail_others(sh) {
        // alert(sh.data.tipe)
        $("#f_detail_progres_jalan").val(0)
        $(".t_luas_planning, .t_keterangan_planning, .t_luas_legal, .t_keterangan_legal, .t_luas_produksi, .t_keterangan_produksi, .r_progres").html("-")
        $.ajax({
            url: base_url + 'siteplan/get_others',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_kavling: editdtt[0].id.substr(6)
            },
            dataType: 'json',
            success: function(r) {
                csrfHash = r.token;

                if (r.data) {
                    let d = r.data[0],
                        progres = (d.progres) ? d.progres : 0;
                    // $(".id_kavling").val(d.id)
                    // $(".t_luas_legal, .t_luas_produksi").html("-")

                    if (d.planning_luas) {
                        $(".t_luas_planning").html(d.planning_luas + "  m&sup2  (" + d.planning_edit + ": " + format_datetime(d.planning_updated_at) + ")")
                        $(".t_keterangan_planning").html(d.planning_keterangan)
                    }

                    if (d.legal_luas) {
                        $(".t_luas_legal").html(d.legal_luas + "  m&sup2  (" + d.legal_edit + ": " + format_datetime(d.legal_updated_at) + ")")
                        $(".t_keterangan_legal").html(d.legal_keterangan)
                    }

                    if (d.produksi_luas) {
                        $(".t_luas_produksi").html(d.produksi_luas + "  m&sup2  (" + d.produksi_edit + ": " + format_datetime(d.produksi_updated_at) + ")")
                        $(".t_keterangan_produksi").html(d.produksi_keterangan)
                    }
                    $("#f_detail_progres_jalan").val(progres)
                    $(".r_progres").html(progres)

                }

            },
            error: function() {
                Swal.fire({

                    icon: 'error',
                    title: "Terjadi kesalahan",
                    showConfirmButton: false,
                    //timer: 1500
                })
                return;
            }
        });

        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/> <span class='capitalize'>" + sh.data.tipe + "<span>: " + sh.data.nama_jalan + "");
        $('#modal_othersdetail').modal({
            backdrop: 'static',
            keyboard: false
        });
    }
    const isSubsidi = ['Non-Subsidi', 'Subsidi'],
        isKPR = ['Tunai/Cash Keras', 'KPR', 'Tunai/Bertahap']

    let dmkdt,
        dpl,
        dpr,
        dlg,
        dlc,
        dbprod,
        dr;

    function detail_kavling(sh, id_kavling) {
        let src = not_found
        $("#fm-detail :input").prop("disabled", true)
        $('#dtt-summary-tab').tab('show');

        $("#dt-promo").text('-')
        $("#dt-is_kpr").text('-')
        $("#dt-is_subsidi").text('-')
        $("#dt-no_spptb").text('-')
        $("#dt-nama_konsumen").text('-')
        $("#dt-alamat_konsumen").text('-')
        $("#dt-nik_konsumen").text('-')
        $("#dt-npwp_konsumen").text('-')
        $("#dt-hp_konsumen").text('-')
        $("#dt-email_konsumen").text('-')
        $("#dt-sales").text('-')

        let categories = [
            "rab_dokumen",
            "prod_foto_konstruksi",
            "prod_foto_exterior",
            "prod_foto_interior",
            "jalan_foto",
            "jalan_foto_update",
            "listrik_pln_foto",
            "listrik_disediakan_dokumen",
            "air_komunal",
            "air_tanah",
            "air_pdam",
        ];

        categories.forEach((cat) => {
            $("#dt-list_" + cat).html("");
            $("#dt-label_" + cat).html("Upload file/Foto");
        });

        $(".files-here").prop('src', src)

        $.ajax({
            url: base_url + 'siteplan/get_detail',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_kavling: id_kavling,
                id_legal: sh.data.id_legal,
                id_produksi: sh.data.id_produksi,
                id_keuangan: sh.data.id_keuangan,
                id_mkdt: sh.data.id_mkdt,
                id_hargajual: sh.data2.id_hargajual
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(r) {
                $("#loading").addClass("hidden");



                csrfHash = r.token;
                // dmkdt = r.mkdt
                // dpl = r.pricelist
                // dpr = r.produksi
                // dlg = r.legal
                // dlc = r.cashout
                // dbprod = r.bayar_produksi
                dr = r

                loadSummary(dr)
                loaded['sm'] = true

                // loadPL(pl)
                // loadKavling(r)
                // loadMKDT(mkdt)
                // loadTagihan(r)
                // loadLegal(lg)
                // loadProduksi(pr)
                // loadBuktiBayarPajak(r)
                // loadCashOut(lc)
                // loadBayarProduksi(bprod)


                /************************ load bayar produksi  ***************************/

                $(".label_alamat").html("<?= $data['proyek']->nama_proyek ?> <br/>" +
                    sh.data.nama_jalan +
                    ", No." + sh.data.no_kavling +
                    "<br/>" + sh.data2.no_tipe_rumah +
                    " (" + sh.data2.tipe_rumah + ")<br/>" +
                    " Harga Jual: Rp. " + sh.data2.harga_akhir + "<br/>" +
                    "(" + format_date(sh.data2.harga_akhir_tgl) + " - " + sh.data2.harga_akhir_oleh + ")");

                $("#modal_detail").modal('show');
            },
            error: function(xhr, st, err) {
                $("#loading").addClass("hidden")
                return swal("error", err);
            },
        });
    }
    let loaded = [];
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        const targetId = $(e.target).attr('href'); // ex: #profile
        if (targetId === '#dtt-summary' && !loaded['sm']) {
            applyLoadingEffect(targetId)
            setTimeout(() => {
                loadSummary(dr)
                loaded['sm'] = true
                removeLoadingEffect(targetId);
            }, 200);
        }
        if (targetId === '#dtt-hj' && !loaded['pl']) {
            applyLoadingEffect(targetId)
            setTimeout(() => {
                loadPL(dr.pricelist);
                loadMKDT(dr.mkdt)
                removeLoadingEffect(targetId);
            }, 200);
            loaded['pl'] = true
        }
        if (targetId === '#dt-stdetail' && !loaded['mkdt']) {
            applyLoadingEffect(targetId)
            setTimeout(() => {
                loadMKDT(dr.mkdt)
                loadKavling(dr)
                loaded['mkdt'] = true
                removeLoadingEffect(targetId);
            }, 200);
        }
        if (targetId === '#dt-cashout' && !loaded['co']) {
            applyLoadingEffect(targetId)
            setTimeout(() => {
                loadCashOut(dr.cashout)
                removeLoadingEffect(targetId);
            }, 200);
            loaded['co'] = true
        }
        if (targetId === '#dt-tagihan' && !loaded['tg']) {
            applyLoadingEffect(targetId)
            setTimeout(() => {
                loadTagihan(dr)
                removeLoadingEffect(targetId);
            }, 200);
            loaded['tg'] = true
        }
        if (targetId === '#dt-legal' && !loaded['lg']) {
            applyLoadingEffect(targetId)
            setTimeout(() => {
                loadLegal(dr.legal)
                removeLoadingEffect(targetId);
            }, 200);
            loaded['lg'] = true
        }
        if (targetId === '#dt-produksi' && !loaded['pr']) {
            applyLoadingEffect(targetId)
            setTimeout(() => {
                loadProduksi(dr.produksi)
                loadBayarProduksi(dr.bayar_produksi)
                removeLoadingEffect(targetId);
            }, 200);
            loaded['pr'] = true
        }
        if (targetId === '#dt-pajak' && !loaded['pj']) {
            loadBuktiBayarPajak(dr)
            loaded['pj'] = true
        }
        if (targetId === '#log_pembayaran' && !loaded['keu_lp']) {
            applyLoadingEffect(targetId)
            setTimeout(() => {
                loadLogPembayaran(keu_lp)
                removeLoadingEffect(targetId);
            }, 200);
            loaded['keu_lp'] = true
        }
        if (targetId === '#tagihan' && !loaded['keu_tg'] || targetId === '#bb' && !loaded['keu_tg']) {
            applyLoadingEffect(targetId)
            setTimeout(() => {
                loadTableTagihan(keu_tg)
                removeLoadingEffect(targetId);
            }, 200);
            loaded['keu_tg'] = true            
        }
    });
    $('#modal_detail').on('hidden.bs.modal', function() {
        // alert()
        loaded = [];
    });

    //modal keuangan
    $('#modal_divisi3').on('hidden.bs.modal', function() {
        // alert()
        load_kavling()
        loaded = [];
        keu_lp = []
        keu_tg = []
    });

    function loadSummary(r) {
        //load data konsumen
        if (r.mkdt) {
            let mkdt = r.mkdt
            setText("#dt-promo", mkdt.promo)
            setText("#dt-is_kpr", isKPR[mkdt.is_kpr])
            setText("#dt-is_subsidi", isSubsidi[mkdt.is_subsidi])
            setText("#dt-no_spptb", mkdt.no_spptb)
            setText("#dt-nama_konsumen", mkdt.nama_konsumen)
            setText("#dt-alamat_konsumen", mkdt.alamat_konsumen)
            setText("#dt-nik_konsumen", mkdt.nik_konsumen)
            setText("#dt-npwp_konsumen", mkdt.npwp)
            setText("#dt-hp_konsumen", mkdt.hp_konsumen)
            setText("#dt-email_konsumen", mkdt.email_konsumen)
            setText("#dt-sales", mkdt.sales)

            // console.log()

            setText("#s-status_mkdt", mkdt.status_mkdt)
            setText("#s-st_bank", mkdt.st_bank)
            setText("#s-notaris", mkdt.notaris)
            setText("#s-st_sp3k_tgl", format_date(mkdt.sp3k_tgl))
            setText("#s-st_sp3k_tgl_exp", format_date(mkdt.sp3k_tgl_exp))
        }else{
            setText("#dt-promo", '-')
            setText("#dt-is_kpr", '-')
            setText("#dt-is_subsidi", '-')
            setText("#dt-no_spptb", '-')
            setText("#dt-nama_konsumen", '-')
            setText("#dt-alamat_konsumen", '-')
            setText("#dt-nik_konsumen", '-')
            setText("#dt-npwp_konsumen", '-')
            setText("#dt-hp_konsumen", '-')
            setText("#dt-email_konsumen", '-')
            setText("#dt-sales", '-')

            // console.log()

            setText("#s-status_mkdt", '-')
            setText("#s-st_bank", '-')
            setText("#s-notaris", '-')
            setText("#s-st_sp3k_tgl", '-')
            setText("#s-st_sp3k_tgl_exp", '-') 
        }

        if(r.kavling){
            setText("#s-perintah_bangun_tgl", format_date(r.kavling.perintah_bangun_tgl))
        }else{
            setText("#s-perintah_bangun_tgl", "-")
        }
        
        const tg = hitungTagihan(r)
        setText("#s-persentase_bayar_tagihan_um", tg.ldp)
        setText("#s-persentase_bayar_tagihan_um_ll", tg.ldp_ll)
        setText("#s-persentase_bayar_tagihan_bb", tg.ldp_bb)

        if (r.produksi) {
            let pr = r.produksi
            setText("#s-progress_bangunan", pr.progres_bangunan + '%')
            setText("#s-tanggal_pembangunan", format_date(pr.tanggal_pembangunan))
            setText("#s-tanggal_selesai_pembangunan", format_date(pr.tanggal_selesai_pembangunan))

            setText("#s-st_saluran", isSudah(pr.st_saluran))
            setText("#s-st_air", isSudah(pr.st_air))
            setText("#s-st_jalan", isSudah(pr.st_jalan))
            setText("#s-slo", isSudah(pr.slo))
            setText("#s-lpa", isSudah(pr.lpa))
        }else{
            setText("#s-progress_bangunan", "-")
            setText("#s-tanggal_pembangunan", "-")
            setText("#s-tanggal_selesai_pembangunan", "-")

            setText("#s-st_saluran", "-")
            setText("#s-st_air", "-")
            setText("#s-st_jalan", "-")
            setText("#s-slo", "-")
            setText("#s-lpa", "-")
        }

        if (r.si) {
            let si = ''
            $.each(r.si, function(i, v) {
                si += `
                <div class="info-row row no-gutters">
                    <div class="col-6">
                        <label class="info-label mb-0">${v.nama}</label>
                    </div>
                    <div class="col-6">
                        : <span class="info-value">${isSudah(v.id_kavling)} ${v.tanggal_si ? format_date(v.tanggal_si): '-'}</span>
                    </div>
                </div>
                `
            });
            applyLoadingEffect("#s-si")
            setTimeout(() => {
                setText("#s-si", si)
                removeLoadingEffect("#s-si");
            }, 500);
        }

        if (r.cashout) {
            let cashout = ''
            // let nom 
            $.each(r.cashout, function(i, v) {
                // nom = v.nominal ? num_format(v.nominal):''
                cashout += ` 
                <div class="info-row row no-gutters">
                    <div class="col-6">
                        <label class="info-label mb-0">${v.item}</label>
                    </div>
                    <div class="col-6">
                        : <span class="info-value">${isSudah(v.id)} ${v.tanggal_bayar ? format_date(v.tanggal_bayar): '-'}</span>
                    </div>
                </div>`
            });

            applyLoadingEffect("#s-co")
            setTimeout(() => {
                setText("#s-co", cashout)
                removeLoadingEffect("#s-co");
            }, 500);
        }
    }


    function isSudah(e) {
        if (e)
            return `<i class="fa fa-solid fa-check"></i> Sudah`
        return `-`
    }

    function last_update(id, username = null, date = null) {
        let u = '-',
            t = '-'
        if (username) {
            u = username
            t = format_datetime(date)
        }
        $(id).html(`Terakhir diubah oleh: <b>${u}</b>, pada: <b>${t}</b> `);
    }

    function loadPL(pl) {
        //load harga pricelist
        if (pl) {
            $.each(pl, function(i, v) {
                applyLoadingEffect("#dt-pl_" + i, v)
                setTimeout(() => {
                    changeVal("#dt-pl_" + i, v)
                    removeLoadingEffect("#dt-pl_" + i, v);
                }, 500);

            });
            setDatePicker(pl.tgl_harga, "#dt-pl_tgl_harga")
        }

    }

    function loadKavling(r) {
        if (r.kavling) {
            setDatePicker(r.kavling.perintah_bangun_tgl, '#dt-st_perintah_bangun_tgl')
            changeVal("#dt-st_perintah_bangun_oleh", r.kavling.username)
            src = not_found
            if (r.kavling.perintah_bangun_file) {
                src = r.kavling.perintah_bangun_file
            }

            $("#dt-pph_ntpn").val(r.kavling.pph42_ntpn)
            $("#dt-pph_nominal_bayar").val(r.kavling.pph42_nilai).keyup()
            setDatePicker(r.kavling.pph42_tgl_bayar, "#dt-pph_tgl_bayar")

            //  $("#dt-st_list-upload_perintah_bangun_file-here").prop('src', base_url + src)
            
        }
        $("#dt-st_list-upload_perintah_bangun_file").prop('href', base_url + src)
    }

    function loadMKDT(mkdt) {
        src = not_found

        $("#dt-st_list-upload_sp3k_file").prop('href', base_url + src)

        $("#dt-st_list-upload_bast_file").prop('href', base_url + src)
        
        $("#dt-btn-bl_here").prop('href', base_url + src)
        $(".dt-cl-bl_here").prop('src', base_url + src)
        
        $("#dt-btn-npwp_here").prop('href', base_url + src)
        $(".dt-cl-npwp_here").prop('src', base_url + src)
        
        $("#dt-btn-ktp_here").prop('href', base_url + src)
        $(".dt-cl-ktp_here").prop('src', base_url + src)

        if (mkdt) {
            //load price list dari keuangan
            setDatePicker(mkdt.tgl_harga, "#dt-tgl_harga")
            changeVal("#dt-hargajual", mkdt.harga_jual)
            changeVal("#dt-harga_diskon_hargajual", mkdt.harga_diskon_hargajual)
            changeVal("#dt-hargajual_net", mkdt.harga_jual_net)
            changeVal("#dt-kpr", mkdt.harga_kpr)
            changeVal("#dt-uang_muka", mkdt.harga_uang_muka)
            changeVal("#dt-harga_diskon_uang_muka", mkdt.harga_diskon_uang_muka)
            changeVal("#dt-biaya_adm", mkdt.harga_administrasi)
            changeVal("#dt-bphtb", mkdt.harga_bphtb)
            changeVal("#dt-ppn", mkdt.harga_ppn)
            changeVal("#dt-biaya_proses", mkdt.harga_biaya_proses)
            changeVal("#dt-row", mkdt.row)
            changeVal("#dt-tipe", mkdt.tipe)
            changeVal("#dt-lt", mkdt.lb)
            changeVal("#dt-lb", mkdt.lt)
            changeVal("#dt-is_ajb", mkdt.is_ajb)
            changeVal("#dt-notaris", mkdt.notaris)



            //kpr disetujui
            changeVal("#dt-st_harga_kpr_acc", mkdt.harga_kpr_acc)
            changeVal("#dt-st_harga_penambahan_um", mkdt.harga_penambahan_um)
            changeVal("#dt-st_harga_penambahan", mkdt.harga_penambahan)
            changeVal("#dt-st_harga_penambahan_tanah", mkdt.harga_penambahan_tanah)
            changeVal("#dt-st_keterangan_harga_penambahan", mkdt.keterangan_harga_penambahan)

            //status
            changeVal('#dt-status_mkdt', mkdt.status_mkdt)
            setDatePicker(mkdt.booking_tgl, '#dt-st_booking_tgl')
            changeVal('#dt-st_bank', mkdt.bank)

            changeVal('#dt-st_booking_fee', mkdt.booking_fee)

            $("#dt-st_wawancara").prop('checked', mkdt.wawancara)
            setDatePicker(mkdt.wawancara_tgl, '#dt-st_wawancara_tgl')

            changeVal('#dt-st_harga_kpr', mkdt.harga_kpr)
            changeVal('#dt-st_acc_harga_kpr', mkdt.harga_kpr_acc)
            changeVal('#dt-st_harga_turun_kpr', parseFloat(mkdt.harga_kpr) - parseFloat(mkdt.harga_kpr_acc))
            changeVal('#dt-st_sp3k_no', mkdt.sp3k_no)
            setDatePicker(mkdt.sp3k_tgl, "#dt-st_sp3k_tgl")
            setDatePicker(mkdt.sp3k_tgl_exp, "#dt-st_sp3k_tgl_exp")

            src = not_found

            if (mkdt.sp3k_file) {
                src = mkdt.sp3k_file
            }
            $("#dt-st_list-upload_sp3k_file").prop('href', base_url + src)
            // $("#dt-st_list-upload_sp3k_file-here").prop('src', base_url + src)


            src = not_found
            if (mkdt.file_ktp) {
                src = mkdt.file_ktp
            }
            $("#dt-btn-ktp_here").prop('href', base_url + src)
            $(".dt-cl-ktp_here").prop('src', base_url + src)

            src = not_found
            if (mkdt.file_npwp) {
                src = mkdt.file_npwp
            }
            $("#dt-btn-npwp_here").prop('href', base_url + src)
            $(".dt-cl-npwp_here").prop('src', base_url + src)

            src = not_found
            if (mkdt.file_data_diri) {
                src = mkdt.file_data_diri
            }
            $("#dt-btn-bl_here").prop('href', base_url + src)
            $(".dt-cl-bl_here").prop('src', base_url + src)





            setDatePicker(mkdt.rencana_akad_tgl, '#dt-st_rencana_akad_tgl')
            $("#dt-st_akad").prop('checked', mkdt.akad == "0" ? 0 : 1)
            setDatePicker(mkdt.akad_tgl, '#dt-st_akad_tgl')

            changeVal("#dt-st_debitur_no", mkdt.debitur_no)
            changeVal("#dt-st_bast_no", mkdt.bast_no)

            src = not_found
            if (mkdt.bast_file) {
                src = mkdt.bast_file
            }
            $("#dt-st_list-upload_bast_file").prop('href', base_url + src)

            // $("#last_update_legal").html(`Terakhir dipudate oleh: ${lg.uadd_by}, pada: ${format_datetime(lg.created_at)} `);
            // if(lg.uedit_by){
            //     $("#last_update_legal").html(`Terakhir dipudate oleh: -${lg.uedit_by}, pada: ${format_datetime(lg.updated_at)} `);
            // }
        }
    }


    function loadLegal(lg) {
        /************************ load table legal ***************************/
        if (lg) {
            for (let i in lg) {
                $("#dt-" + i).val(lg[i]).change();
            }

            if (lg.data) {
                $("#dt-sertifikat_balik_nama").val(lg.data.nama_konsumen ? lg.data.nama_konsumen : '')
                $("#dt-bphtb_nominal_disetujui").val(lg.data.harga_bphtb ? lg.data.harga_bphtb : '').change().keyup()
            }

            setDatePicker(lg.sertifikat_split_tanggal_terbit, "#dt-sertifikat_split_tanggal_terbit")
            setDatePicker(lg.sertifikat_split_tanggal_berakhir, "#dt-sertifikat_split_tanggal_berakhir")
            setDatePicker(lg.sertifikat_split_tanggal_surat_ukur, "#dt-sertifikat_split_tanggal_surat_ukur")
            setDatePicker(lg.sertifikat_balik_nama_tgl_pengiriman, "#dt-sertifikat_balik_nama_tgl_pengiriman")
            setDatePicker(lg.pbb_pecah_tanggal_bayar, "#dt-pbb_pecah_tanggal_bayar")

            setDatePicker(lg.bphtb_tanggal_verifikasi, "#dt-bphtb_tanggal_verifikasi")
            setDatePicker(lg.bphtb_jatuh_tempo, "#dt-bphtb_jatuh_tempo")
            setDatePicker(lg.bphtb_perpanjang_jatuh_tempo, "#dt-bphtb_perpanjang_jatuh_tempo")
            setDatePicker(lg.bphtb_tanggal_pembayaran, "#dt-bphtb_tanggal_pembayaran")
            setDatePicker(lg.bphtb_tanggal_validasi, "#dt-bphtb_tanggal_validasi")

            setDatePicker(lg.pph_tgl_permohonan, "#dt-pph_tgl_permohonan")
            setDatePicker(lg.pph_tanggal_validasi, "#dt-pph_tanggal_validasi")
            setDatePicker(lg.pph_tgl_bayar, "#dt-pph_tgl_bayar")
            setDatePicker(lg.ajb_tanggal, "#dt-ajb_tanggal")
            setDatePicker(lg.ajb_tanggal_dikirim, "#dt-ajb_tanggal_dikirim")
            setDatePicker(lg.ppjb_tanggal, "#dt-ppjb_tanggal")

            setDatePicker(lg.pbb_balik_nama_tgl_pengiriman, "#dt-pbb_balik_nama_tgl_pengiriman")
            setDatePicker(lg.pbb_tgl_pembetulan, "#dt-pbb_tgl_pembetulan")
            setDatePicker(lg.pbg_tanggal_kirim, "#dt-pbg_tanggal_kirim")
            setDatePicker(lg.pph_tgl_selesai, "#dt-pph_tgl_selesai")

            $("#dt-legal_keterangan").val(lg.keterangan);

            last_update("#last_update_legal", lg.uadd_by, lg.created_at)
            if (lg.uedit_by) {
                last_update("#last_update_legal", lg.uedit_by, lg.updated_at)
            }

        }

        /************************ end of table legal ***************************/
    }

    function hitungTagihan(r) {
        //um
        let total_um = parseFloat(r.total_um)
        let sb_um = parseFloat(r.sb_um)
        let sisa_um = r.total_um - r.sb_um,
            ldp = (sisa_um == 0) ? 100 : r.sb_um / r.total_um * 100;
        ldp = (r.sb_um > 0) ? ~~ldp + "%" : "0%";

        //um dan ll
        let sisa_um_ll = r.total_um_ll - r.sb_um_ll,
            ldp_ll = (sisa_um_ll == 0) ? 100 : r.sb_um_ll / r.total_um_ll * 100;
        ldp_ll = (r.sb_um_ll > 0) ? ~~ldp_ll + "%" : "0%";

        let total_bb = parseFloat(r.total_bb)
        let sb_bb = parseFloat(r.sb_bb)
        let sisa_bb = r.total_bb - r.sb_bb,
            ldp_bb = (sisa_bb == 0) ? 100 : r.sb_bb / r.total_bb * 100;
        ldp_bb = (r.sb_bb > 0) ? ~~ldp_bb + "%" : "0%";

        return {
            sisa_um: sisa_um,
            ldp: ldp,
            total_um: total_um,
            sb_um: sb_um,
            sisa_um_ll: sisa_um_ll,
            ldp_ll: ldp_ll,
            sb_um_ll: r.sb_um_ll,
            total_um_ll: r.total_um_ll,
            total_bb: total_bb,
            sb_bb: sb_bb,
            sisa_bb: sisa_bb,
            ldp_bb: ldp_bb
        }
    }

    function loadTagihan(r) {
        /************************ load table tagihan ***************************/

        let tagihan = hitungTagihan(r);

        changeVal("#dt-total_biaya_um", tagihan.total_um)
        changeVal("#dt-sudah_bayar_um", tagihan.sb_um)
        changeVal("#dt-sisa_tagihan_um", tagihan.sisa_um)
        changeVal("#dt-persentase_bayar_tagihan_um", tagihan.ldp)


        changeVal("#dt-total_biaya_um_ll", tagihan.total_um_ll)
        changeVal("#dt-sudah_bayar_um_ll", tagihan.sb_um_ll)
        changeVal("#dt-sisa_tagihan_um_ll", tagihan.sisa_um_ll)
        changeVal("#dt-persentase_bayar_tagihan_um_ll", tagihan.ldp_ll)



        changeVal("#dt-total_biaya_bb", tagihan.total_bb)
        changeVal("#dt-sudah_bayar_bb", tagihan.sb_bb)
        changeVal("#dt-sisa_tagihan_bb", tagihan.sisa_bb)
        changeVal("#dt-persentase_bayar_tagihan_bb", tagihan.ldp_bb)

        if (r.ku)
            $("#last_update_keuangan").html("Terakhir diupdate oleh: " + r.ku.username + " pada: " + format_datetime(r.ku.created_at));
        /************************ end of load table tagihan ***************************/
    }

    function loadProduksi(pr) {
        /************************ load produksi ***************************/
        if (pr) {
            $("#dt-st_0").prop('checked', pr.st_0)
            $("#dt-st_25").prop('checked', pr.st_25)
            $("#dt-st_50").prop('checked', pr.st_50)
            $("#dt-st_75").prop('checked', pr.st_75)
            $("#dt-st_100").prop('checked', pr.st_100)
            $("#dt-st_saluran").prop('checked', pr.st_saluran)
            $("#dt-st_jalan").prop('checked', pr.st_jalan)
            $("#dt-st_air").prop('checked', pr.st_air)
            $("#dt-bp").prop('checked', pr.bp)
            $("#dt-lpa").prop('checked', pr.lpa)
            $("#dt-slo").prop('checked', pr.slo)
            $("#dt-sumurbor").prop('checked', pr.sumurbor)

            setDatePicker(pr.lpa_tanggal, "#dt-lpa_tanggal")

            setDatePicker(pr.tanggal_pembangunan, '#dt-tanggal_pembangunan')
            setDatePicker(pr.tanggal_rencana_selesai_pembangunan, '#dt-tanggal_rencana_selesai_pembangunan')
            setDatePicker(pr.tanggal_selesai_pembangunan, '#dt-tanggal_selesai_pembangunan')

            $("#dt-progres_bangunan").val(pr.progres_bangunan)
            setText("#dt-t_progres_bangunan", pr.progres_bangunan)

            $("#dt-produksi_keterangan").val(pr.keterangan)

            changeVal("#dt-air_jenis", pr.air_jenis);
            changeVal("#dt-listrik_jenis", pr.listrik_jenis);

            changeVal("#dt-listrik_pln", pr.listrik_pln);
            changeVal("#dt-listrik_disediakan_no", pr.listrik_disediakan_no);
            changeVal("#dt-listrik_disediakan_tanggal", pr.listrik_disediakan_tanggal);
            changeVal("#dt-air_deskripsi_unit", pr.air_deskripsi_unit);
            changeVal("#dt-air_pdam_no", pr.air_pdam_no);


            changeVal("#dt-sumurbor_keterangan", pr.sumurbor_keterangan);
            setDatePicker(pr.sumurbor_tanggal, '#dt-sumurbor_tanggal')
            $("#dt-last_update-sumurbor").html(
                `Diubah pada: ${pr.sumurbor_updated ? format_datetime(pr.sumurbor_updated):'-'}, 
                    oleh: ${pr.sumurbor_oleh_u ? pr.sumurbor_oleh_u:'-'}`
            )

        } else {
            $("#dt-st_0").prop('checked', false)
            $("#dt-st_25").prop('checked', false)
            $("#dt-st_50").prop('checked', false)
            $("#dt-st_75").prop('checked', false)
            $("#dt-st_100").prop('checked', false)
            $("#dt-st_saluran").prop('checked', false)
            $("#dt-st_jalan").prop('checked', false)
            $("#dt-st_air").prop('checked', false)
            $("#dt-bp").prop('checked', false)
            $("#dt-lpa").prop('checked', false)
            $("#dt-slo").prop('checked', false)
            $("#dt-sumurbor").prop('checked', false)

            setDatePicker(null, "#dt-lpa_tanggal")

            setDatePicker(null, '#dt-tanggal_pembangunan')
            setDatePicker(null, '#dt-tanggal_rencana_selesai_pembangunan')
            setDatePicker(null, '#dt-tanggal_selesai_pembangunan')

            $("#dt-progres_bangunan").val(null)
            setText("#dt-t_progres_bangunan", null)

            $("#dt-produksi_keterangan").val(null)

            changeVal("#dt-air_jenis", null);
            changeVal("#dt-listrik_jenis", null);

            changeVal("#dt-listrik_pln", null);
            changeVal("#dt-listrik_disediakan_no", null);
            changeVal("#dt-listrik_disediakan_tanggal", null);
            changeVal("#dt-air_deskripsi_unit", null);
            changeVal("#dt-air_pdam_no", null);

            changeVal("#dt-sumurbor_keterangan", null);
            setDatePicker(null, '#dt-sumurbor_tanggal')
            $("#dt-last_update-sumurbor").html(null)
        }
        if (r.files)
            showFoto(r.files, 'dt-', "false");


        /************************ end of produksi ***************************/
    }

    function loadBuktiBayarPajak(r) {
        /************************ load bukti bayar pajak  ***************************/
        let dv = ''
        $.each(r.file_pph, function(i, v) {
            dv += `
                <a href="${base_url + v.lokasi}" class="btn btn-outline-primary" target=_blank>
                    <h5>Bukti pembayaran ${v.default_filename} </h5>
                    ${v.keterangan}
                    <br>
                    <embed src="${base_url + v.lokasi}" style="width: 90%;"
                        class="files-here dt-cl-ktp_here">
                        diunggah pada: ${format_datetime(v.upload_at)} (${v.uupload_by})
                </a>
                <br>
                <br>
                 `;
        });
        $("#dt-file_pph42-here").html(dv)

        dv = '';
        $.each(r.file_ppn, function(i, v) {
            dv += `
                <a href="${base_url + v.lokasi}" class="btn btn-outline-primary" target=_blank>
                    <h5>Bukti pembayaran ${v.default_filename} </h5>
                    ${v.keterangan}
                    <br>
                    <embed src="${base_url + v.lokasi}" style="width: 90%;"
                        class="files-here dt-cl-ktp_here">
                        diunggah pada: ${format_datetime(v.upload_at)} (${v.uupload_by})
                </a>
                <br>
                <br>
                 `;
        });
        $("#dt-file_ppn-here").html(dv)

    }

    function loadCashOut(d) {
        let dv = ''
        $("#dt-cashout-here").html("")
        $.each(d, function(i, v) {
            // co.push(v.id_cashout)

            id_cashout = !v.id ? "n" + v.id_cashout : v.id
            dv += `
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>${v.item}</strong>
                            </div>
                            <div class="card-body">
                                    <div class="row">
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tanggal Pembayaran</label>
                                            <input type="text" class="form-control flatpickr-human-friendly "
                                                id="" value="${format_date(v.tanggal_bayar?v.tanggal_bayar:"")}" name="" disabled>
                                        </div>                        
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="sumurbor_bayar_nominal">Nominal</label>
                                            <input type="text" disabled class="form-control num" id=""
                                                name="" value="${v.nominal?v.nominal:''}">
                                        </div>     
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <textarea class="form-control" id=""
                                                name="" rows="1" disabled placeholder="Keterangan">${v.keterangan?v.keterangan:''}</textarea>
                                            <small id="last_update-sumurbor_bayar" class=""></small>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                `
        });

        $("#dt-cashout-here").html(dv)
        $(".num").change()

    }

    function loadBayarProduksi(bprod) {
        let dv = ''
        $("#dt-div-bayar_produksi-here").html("")
        $.each(bprod, function(i, v) {
            // console.log(bprod)

            let id = !v.id ? "n" + v.id_bayar_produksi : v.id
            dv += `
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>${v.item}</strong>
                            </div>
                            <div class="card-body">
                                    <div class="row">
                                    <div class="col-md-6">
                                    
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Tanggal Pembayaran</label>
                                            <input disabled type="text" class="form-control fp-bayar_produksi flatpickr-human-friendly tbp${v.id_bayar_produksi}"
                                                id="dt-id-bayar_produksi[${id}][tanggal_bayar]" value="${v.tanggal_bayar?v.tanggal_bayar:''}" name="dt-id-bayar_produksi[${id}][tanggal_bayar]">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="sumurbor_bayar_nominal">Nominal</label>
                                            <input type="text" disabled class="form-control num nbp${v.id_bayar_produksi}" id="dt-id-bayar_produksi[${id}][nominal]"
                                                name="dt-id-bayar_produksi[${id}][nominal]" value="${v.nominal?v.nominal:''}">
                                            <input type="hidden" class="form-control" id="id-bayar_produksi[${id}][id_item_produksi]"
                                                name="id-bayar_produksi[${id}][id_item_produksi]" value="${id}">
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <textarea disabled class="form-control" id="dt-id-bayar_produksi[${id}][keterangan]"
                                                name="dt-id-bayar_produksi[${id}][keterangan]" rows="4" placeholder="Keterangan">${v.keterangan?v.keterangan:''}</textarea>
                                            <small id="last_update-sumurbor_bayar" class=""></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `
        });

        $("#dt-div-bayar_produksi-here").html(dv)
        $(".num").change()

    }

    function isi_data() {
        if (editdtt.length == 0)
            return swal('error', 'Terjad Kesalahan', 'Tidak ada kavling yang dipilih')

        //bug isi data with addmode
        if (editdtt.length > 1) {
            swal('error', 'Terjad Kesalahan', 'Tidak bisa merubah data lebih dari 1 kavling')
            hapus_seleksi();
            return;
        }

        var role,
            sh = editdtt[0],
            id_kavling = sh.id.substr(3);

        //jika admin login
        if (roleid == 1)
            role = $('#pilih-divisi option:selected').val()
        else
            role = roleid

        if (role == 7) { //produksi
            open_produksi(sh, role, id_kavling)
        } else if (role == 5) { //legal
            open_legal(sh, role, id_kavling)
        } else if (role == 4) { //mkdt
            open_mkdt(sh, role, id_kavling)
        } else if (role == 3) { //keunagan
            if (!sh.data.id_mkdt) {
                return swal('error', 'Terjad Kesalahan', `Belum ada data konsumen di kavling ${sh.data.nama_jalan}, No. ${sh.data.no_kavling}`)
            }
            open_keuangan(sh, role, id_kavling)
        } else if (role == 6) { //planning
            if (!addMode) {
                hapus_seleksi();
                open_planning(sh, role, id_kavling)
            } else {
                editdtt.push(sh)
                drawBorderEdit(sh)
            }
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_divisi' + role).modal({
                backdrop: 'static',
                keyboard: false
            });
        } else if (role == 8) { //sales promotion
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_divisi' + role).modal({
                backdrop: 'static',
                keyboard: false
            });
        } else if (role == 10) { //pajak
            open_pajak(sh, role, id_kavling);
        }
    }

    <?php if ($k == 1 || $k == 6): ?><?php endif; ?>
    <?php if ($k == 1 || $k == 3): ?> <?php endif; ?>
    /********************************* mkdt *******************************************/
    $("#fm-mkdt .num").change(function() {
        // total()
    })
    //tidak dipakai
    function total(id_form = "") {
        var harga_jual = removeComma(($(id_form + " #harga_jual").val() == '') ? 0 : $(id_form + " #harga_jual").val()),
            harga_diskon = removeComma(($(id_form + " #harga_diskon").val() == '') ? 0 : $(id_form + " #harga_diskon").val()),
            harga_penambahan = removeComma(($(id_form + " #harga_penambahan").val() == '') ? 0 : $(id_form + " #harga_penambahan").val()),
            harga_administrasi = removeComma(($(id_form + " #harga_administrasi").val() == '') ? 0 : $(id_form + " #harga_administrasi").val()),
            harga_ppn = removeComma(($(id_form + " #harga_ppn").val() == '') ? 0 : $(id_form + " #harga_ppn").val()),
            harga_bphtb = removeComma(($(id_form + " #harga_bphtb").val() == '') ? 0 : $(id_form + " #harga_bphtb").val()),
            harga_biaya_proses = removeComma(($(id_form + " #harga_biaya_proses").val() == '') ? 0 : $(id_form + " #harga_biaya_proses").val()),
            harga_kpr = removeComma(($(id_form + " #harga_kpr").val() == '') ? 0 : $(id_form + " #harga_kpr").val()),
            total_biaya = 0;

        total_biaya = (harga_jual - harga_kpr) - harga_diskon + harga_penambahan + harga_ppn + harga_bphtb + harga_biaya_proses;

        $(id_form + " #total_biaya").val(total_biaya).keyup();

        // console.log(total_biaya)

        $("#total_biaya2").val(total_biaya).keyup();

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
    let total_keu, cicilan_keu

    function sum_tg(e = 0, bb = '') {
        e = parseFloat(removeComma(e))

        if (bb == '') {
            total_keu = parseFloat(removeComma($("#mk-total_um").val()) || 0),
                cicilan_keu = parseFloat(removeComma($("#total_cicilan_um").val()) || 0)
        } else {
            total_keu = parseFloat(removeComma($("#mk-total_bb").val()) || 0),
                cicilan_keu = parseFloat(removeComma($("#total_cicilan_bb").val()) || 0)
        }

        if (cicilan_keu + e > total_keu)
            $("#nominal" + bb).val(total_keu - cicilan_keu).keyup()
    }

    var it = 0;
    /***************** list tagihan ****************/
    function tambah_(e = '') {
        let a = (e == '_bb') ? e : '_um'
        if ($("#total_cicilan" + a).val() == $("#mk-total" + a).val()) {
            swal('error', "Tidak bisa menambahkan lagi form ", null, false);
            return false;
        } else {
            if (!$("#berita_acara" + e).val() || !$("#nominal" + e).val() || !$("#jatuh_tempo_tgl" + e).val()) {
                swal('error', "Berita acara, nominal dan jatuh tempo tidak boleh kosong", null, false);
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

        if (data_um[$("#id_list_keu" + e).val()])
            i = $("#id_list_keu" + e).val()

        if (e == '') {
            data_um[i] = ({
                id_list_keu: i,
                id_keuangan: $("#id_keuangan").val(),
                berita_acara: $("#berita_acara").val(),
                nominal: $("#nominal").val(),
                jatuh_tempo_tgl: $("#jatuh_tempo_tgl").val(),
            })
        } else {
            data_bb[i] = ({
                id_list_keu_bb: i,
                id_keuangan_bb: $("#id_keuangan_bb").val(),
                berita_acara_bb: $("#berita_acara_bb").val(),
                nominal_bb: $("#nominal_bb").val(),
                jatuh_tempo_tgl_bb: $("#jatuh_tempo_tgl_bb").val(),
            })
        }

        tambah_ketagihan(e)

        fp = flatpickr("#jatuh_tempo_tgl" + e, {
            altInput: true,
            altFormat: 'F j, Y',
            dateFormat: 'Y-m-d'
        })

        // if (e == '') {
        //     var d = new Date(
        //         $("#jatuh_tempo_tgl").val()
        //     ).fp_incr(30);

        //     fp.setDate(d);
        //     var d = new Date(
        //         $("#jatuh_tempo_tgl").val()
        //     ).fp_incr(30);

        //     fp.setDate(d);
        // }

        it += 1;
    }

    function removeFromTable(x, y = null) {
        // if (y == '_bb') {
        //     if (data_bb[x].id_keuangan != '' || data_bb[x].id_keuangan != 'null') {
        //         return Swal.fire({
        //             //
        //             icon: 'error',
        //             title: "tidak dapat menghapus tagihan",
        //             showConfirmButton: true,
        //             // //timer: 1500
        //         })
        //     }
        // } else {
        //     if (data_um[x].id_keuangan != '' || data_um[x].id_keuangan != 'null') {
        //         return Swal.fire({
        //             //
        //             icon: 'error',
        //             title: "tidak dapat menghapus tagihan",
        //             showConfirmButton: true,
        //             // //timer: 1500
        //         })
        //     }
        // }

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

                        if (y == '_bb') delete data_bb[x];
                        else delete data_um[x];
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
        var d = data_um[x]

        $("#id_list_keu").val(x);
        $("#berita_acara").val(d.berita_acara);
        $("#nominal").val(d.nominal).keyup();
        $("#jatuh_tempo_tgl").val(d.jatuh_tempo_tgl);
        $("#tambah_list").html("Simpan Perubahan")
    }

    function tambah_ketagihan(e = '') {
        $("#list_cicilan_here").html("")
        let tb = "",
            tot_um = 0,
            tot_bb = 0

        //render table untuk data um
        for (var k in data_um) {
            if (!data_um.hasOwnProperty(k)) continue;
            var obj = data_um[k];
            tb += `<tr>
                        <td>` + obj.berita_acara + `</td>
                        <td>` + format_date(obj.jatuh_tempo_tgl) + `</td>
                        <td>` + obj.nominal + `</td>
                        <td>
                            <div class="btn-group">
                                <!--<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="editFromTable('` + k + `')"><i class="fa fa-edit"></i></button>-->
                                <button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="removeFromTable('` + k + `')"><i class="fa fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>`
            tot_um += parseFloat(removeComma(obj.nominal))
        }
        //render total um
        tb += `
            <tr class='table-secondary'>
                <td colspan='2'>Total Tagihan Uang Muka</td><td>` + num_format(tot_um) + `</td><td></td>
            </tr>`
        //render table untuk data bb
        for (var k in data_bb) {
            if (!data_bb.hasOwnProperty(k)) continue;
            var obj = data_bb[k];
            tb += `<tr>
                        <td>` + obj.berita_acara_bb + `</td>
                        <td>` + format_date(obj.jatuh_tempo_tgl_bb) + `</td>
                        <td>` + obj.nominal_bb + `</td>
                        <td>
                            <div class="btn-group">
                                <!--<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="editFromTable('` + k + `')"><i class="fa fa-edit"></i></button>-->
                                <button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="removeFromTable('` + k + `', '_bb')"><i class="fa fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>`
            tot_bb += parseFloat(removeComma(obj.nominal_bb))
        }
        //render total bb
        tb += `
            <tr class='table-secondary'>
                <td colspan='2'>Total Tagihan Biaya biaya</td><td>` + num_format(tot_bb) + `</td><td></td>
            </tr>`


        $("#list_cicilan_here").html(tb)
        $("#total_cicilan_um").val(tot_um).change().keyup()
        $("#total_cicilan_bb").val(tot_bb).change().keyup()
        $("#id_list_keu").val('');
        $("#id_list_keu_bb").val('');
        $("#nominal, #nominal_bb").change();
        $("#tambah_list").html("+ Cicilan UM")
        $("#tambah_list_bb").html("+ Cicilan BB")


        // $("#cicilan_belong_here").append('<div id="item' + it + '"><hr>' +
        //     '<input type="hidden" name="id_keuangan[' + it + ']" id="id_keuangan' + it + '" />' +
        //     '<div class="form-group">' +
        //     '<label>Keterangan</label>' +
        //     '<input required name="berita_acara[' + it + ']" id="berita_acara' + it + '" class="form-control" type="text" >' +
        //     '<span class="help-block"></span>' +
        //     '</div>' +
        //     '<div class="form-group">' +
        //     '<label>Nominal</label>' +
        //     '<input required name="nominal[' + it + ']" id="nominal' + it + '" class="form-control num tg" onchange="sum_tg(' + it + ')" type="text" >' +
        //     '<span class="help-block"></span>' +
        //     '</div>' +
        //     '<div class="form-group">' +
        //     '<label>Tanggal Jatuh Tempo</label>' +
        //     '<input required name="jatuh_tempo_tgl[' + it + ']" id="jatuh_tempo_tgl' + it + '" class="form-control flatpickr-human-friendly" type="date" >' +
        //     '<span class="help-block"></span>' +
        //     '</div>' +
        //     '</div>'
        // ).fadeIn(5000);
    }
    $("#pilih-divisi").select2()
</script>

<script src="https://cdn.jsdelivr.net/npm/exif-js"></script>

<?php if ($k == 1 || $k == 3): ?>
    <script src="<?= base_url() ?>/assets/js/keuangan.js"></script>
<?php endif; ?>
<?php if ($k == 1 || $k == 4): ?>
    <script src="<?= base_url() ?>/assets/js/mkdt.js"></script>
<?php endif; ?>
<?php if ($k == 1 || $k == 5): ?>
    <script src="<?= base_url() ?>/assets/js/legal.js"></script>
<?php endif; ?>
<?php if ($k == 1 || $k == 6): ?>
    <script src="<?= base_url() ?>/assets/js/planning.js"></script>
<?php endif; ?>
<?php if ($k == 1 || $k == 7): ?>
    <script src="<?= base_url() ?>/assets/js/produksi.js"></script>
<?php endif; ?>
<?php if ($k == 1 || $k == 8): ?>
    <script src="<?= base_url() ?>/assets/js/sales.js"></script>
<?php endif; ?>
<?php if ($k == 1 || $k == 9): ?>
    <script src="<?= base_url() ?>/assets/js/direksi.js"></script>
<?php endif; ?>
<?php if ($k == 1 || $k == 10): ?>
    <script src="<?= base_url() ?>/assets/js/pajak.js"></script>
<?php endif; ?>

<script>
    // stage.add(siteplan, masked, datal);
    stage.add(siteplan, masked);
    stage.draw();

    function downloadURI(uri, name, callback) {
        var link = document.createElement('a');
        link.download = name;
        link.href = uri;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        delete link;

        callback()
    }

    function export_siteplan() {
        $('#btn-export-siteplan').prop("disabled", true);
        $('#btn-export-siteplan').html('Export<i class="fa fa-spinner fa-spin"></i>');

        const stageh = stage.height()
        const stagew = stage.width()
        const stages = stage.scale()
        const stagep = stage.position()

        stage.height(imageObj.height)
        stage.width(imageObj.width)

        stage.position({
            x: 0,
            y: 0
        })

        var dataURL = stage.scale({
            x: 1,
            y: 1
        }).toDataURL({
            pixelRatio: 1
        });

        const nama_file = "Siteplan " + dt_proyek.nama_proyek + " Per " + format_date(today_date) + ".png";

        downloadURI(dataURL, nama_file, function() {
            stage.height(stageh)
            stage.width(stagew)
            stage.scale(stages)
            stage.position(stagep)
            $('#btn-export-siteplan').prop("disabled", false);
            $('#btn-export-siteplan').html('Export');
        })
    }

    //context menu


    //autofit
    function fitStageIntoParentContainer() {
        var container = document.querySelector('#stage-parent');

        // now we need to fit stage into parent container
        var containerWidth = container.offsetWidth;

        // but we also make the full scene visible
        // so we need to scale all objects on canvas
        var scale = containerWidth / sceneWidth;

        stage.width(sceneWidth * scale);
        stage.height(sceneHeight);
        stage.scale({
            x: scale,
            y: scale
        });
    }

    fitStageIntoParentContainer();

    function open_setting() {
        // $("#modal-setting-filter").modal()
        $("#modalEwe").modal()
    }

    function filter_option() {
        filter.id_cluster = $("#filter-id_cluster").val()
        filter.id_jalan = $("#filter-id_jalan").val()
        load_kavling()
    }

    function hapus_filter_option() {
        $('#filter-id_cluster').val(null).trigger('change');
        filter_option()
    }

    //select2 cluster
    $("#filter-id_cluster").select2({
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
                    id_proyek: dt_proyek.id_proyek
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
    $("#filter-id_cluster").on("change", function(e) {
        $('#filter-id_jalan').val(null).trigger('change');
        if (this.value)
            $("#filter-id_jalan").prop("disabled", false)
        else
            $("#filter-id_jalan").prop("disabled", true)
    });
    $("#filter-id_jalan").select2({
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
                    id_cluster: $("#filter-id_cluster").val(),
                    id_proyek: dt_proyek.id_proyek
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

    //remove bug arrow select2
    $(".select2-selection__arrow").removeClass("select2-selection__arrow")

    $("#br_siteplan").html(dt_proyek.nama_proyek)


    //context menu
    let currentShape;
    document.getElementById('menu-btn-lihat_detail').addEventListener('click', () => {
        if (currentShape.target.attrs.id) {
            //open detail modal
            lihat_detail();
        }
    });
    var menuNode = document.getElementById('menu');
    window.addEventListener('click', () => {
        // hide menu
        menuNode.style.display = 'none';
    });
    stage.on('contextmenu', function(e) {
        // prevent default behavior
        e.evt.preventDefault();

        if (e.target === stage) {
            // if we are on empty place of the stage we will do nothing
            return;
        }
        currentShape = e;
        // show menu
        menuNode.style.display = 'initial';
        var containerRect = stage.container().getBoundingClientRect();
        menuNode.style.top = stage.getPointerPosition().y + 4 + 'px';
        menuNode.style.left = stage.getPointerPosition().x + 20 + 'px';
    });

    function renderText() {

        $("#btn-renderText").prop("disabled", true);
        $("#btn-renderText").html('Tampilkan Keterangan Warna Di Siteplan <i class="fa fa-spinner fa-spin"></i>');
        // convert DOM into image
        html2canvas(document.querySelector("#keterangan-warna-here"))
            .then((canvas) => {
                // show it inside Konva.Image
                shape_ket.image(canvas);
                $("#btn-renderText").prop("disabled", false);
                $("#btn-renderText").html('Tampilkan Keterangan Warna Di Siteplan');
            });
    }

    function simpan_batal() {
        if (!palid("batal-keterangan_batal", "", "Keterangan Batal harus diisi"))
            return;


        var form = $('#fm-batal_booking')[0];
        var fd = new FormData(form);
        fd.append(csrfName, csrfHash);

        $.ajax({
            url: base_url + 'mkdt/simpan_batal',
            type: 'post',
            contentType: false,
            processData: false,
            data: fd,
            dataType: 'json',
            beforeSend: function() {
                $('#btn-simpan_batal_mkdt').prop("disabled", true);
                $('#btn-simpan_batal_mkdt').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(r) {
                csrfHash = r.token;

                if (r.success === true) {
                    Swal.fire({
                        //
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        //timer: 1500
                    }).then(function() {
                        $('.modal').modal('hide');
                        $('#btn-simpan_batal_mkdt').html('Simpan');
                        $('#btn-simpan_batal_mkdt').prop("disabled", false);
                    })
                } else {
                    Swal.fire({
                        //
                        icon: 'error',
                        title: r.messages,
                        showConfirmButton: false,
                        //timer: 1500
                    }).then(function() {
                        $('#btn-simpan_batal_mkdt').html('Simpan');
                        $('#btn-simpan_batal_mkdt').prop("disabled", false);
                    })
                }
                load_kavling();
                hapus_seleksi();
            },
            error: function() {
                Swal.fire({

                    icon: 'error',
                    title: "Terjadi kesalahan",
                    showConfirmButton: false,
                    //timer: 1500
                })
                $('#btn-simpan_batal_mkdt').html('Simpan');
                $('#btn-simpan_batal_mkdt').prop("disabled", false);
                return;
            }
        });
    }

    function ajukan_batal() {
        let sh = editdtt[0],
            id_kavling = sh.id.substr(3);
        if (sh.data.tipe != "kavling") {
            Swal.fire({
                //
                icon: 'error',
                title: "Tidak ada kavling terpilih ",
                showConfirmButton: true,
                // //timer: 1500
            })
            return;
        }
        if (!sh.data.id_mkdt) {
            Swal.fire({
                //
                icon: 'error',
                title: "Belum ada data konsumen di kavling: <br>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
                showConfirmButton: false,
                //timer: 1500
            })
            return;
        }
        $("#fm-batal_booking")[0].reset();
        $("#last_update-batal_mkdt").html("Dibatalkan oleh: -  Pada: -")

        $("#batal-id_kavling").val(id_kavling);
        $("#batal-id_mkdt").val(sh.data.id_mkdt);

        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");

        $.ajax({
            url: base_url + 'mkdt/batal_mkdt',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_mkdt: sh.data.id_mkdt,
                id_hargajual: sh.data2.id_hargajual,
                id_kavling: id_kavling
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(x) {
                let mkdt = x.data,
                    sb = x.sudah_bayar,
                    tb = x.total_biaya
                $.each(mkdt, function(k, v) {
                    $("#batal-" + k).val(v).change().keyup()
                })

                let src = not_found
                //load ktp npwp
                if (mkdt.surat_batal != null) {
                    src = mkdt.surat_batal
                }

                $("#list-file_surat_batal").prop("href", base_url + "/" + src)

                $("#last_update-batal_mkdt").html("Dibatalkan oleh: " + mkdt.mkdt_batal_oleh_u + " Pada: " + format_datetime(mkdt.mkdt_batal_tgl))

                $("#batal-total_biaya_um").val(tb.uang_muka).keyup()
                $("#batal-total_biaya_bb").val(tb.biaya_biaya).keyup()

                $("#batal-sudah_bayar_um").val(sb.uang_muka).keyup()
                $("#batal-sudah_bayar_bb").val(sb.biaya_biaya).keyup()

                $("#batal-sisa_tagihan_um").val(tb.uang_muka - sb.biaya_biaya).keyup()
                $("#batal-sisa_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()

                // $("#batal-persentase_bayar_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()
                // $("#batal-persentase_bayar_tagihan_um").val(tb.biaya_biaya - sb.biaya_biaya).keyup()


                $('#modal-batal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#loading").addClass("hidden");
            },
            error: function(e) {
                Swal.fire({
                    //
                    icon: 'error',
                    title: "Token tidak valid, muat ulang halaman",
                    showConfirmButton: true,
                    // //timer: 1500
                }).then(function() {
                    location.reload();
                })
            }
        });
    }

    function cek_tanggal_pembangunan(x = false) {
        let arr = `<tr><td colspan='6'> Tidak ada Data</td></tr>`;
        if (wr_pembangunan.length > 0) {
            let n = 1;
            arr = ''
            wr_pembangunan.forEach(i => {
                arr += `
                    <tr>
                        <td>${n++}</td>
                        <td>${i.nama_jalan} No. ${i.no_kavling} <br> (${i.tipe})</td>
                        <td>${i.progres} %</td>
                        <td>${format_date(i.tanggal_pembangunan)}</td>
                        <td>${format_date(i.tanggal_rencana_selesai_pembangunan)} (${daysBetween(today_date, i.tanggal_rencana_selesai_pembangunan)} hari)</td>
                        <td>${i.keterangan}</td>
                    </tr>
                `
            });
        }
        $("#list-rumah-belum-selesai-here").html(arr)
        if (x == true)
            $("#modal-list-rumah-belum-selesai").modal();
    }

    function terima_batal() {

        let sh = editdtt[0],
            id_kavling = sh.id.substr(3);
        if (sh.data.tipe != "kavling") {
            Swal.fire({
                //
                icon: 'error',
                title: "Tidak ada kavling terpilih ",
                showConfirmButton: true,
                // //timer: 1500
            })
            return;
        }
        if (!sh.data.id_mkdt) {
            Swal.fire({
                //
                icon: 'error',
                title: "Belum ada data konsumen di kavling: <br>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
                showConfirmButton: false,
                // ////timer: 1500
            })
            return;
        }
        // if (sh.data.is_batal == '0') {
        //     Swal.fire({
        //         //
        //         icon: 'error',
        //         title: "Belum ada data surat batal dari MKDT",
        //         showConfirmButton: false,
        //         // //timer: 1500
        //     })
        //     return;
        // }
        $("#fm-batal_booking")[0].reset();
        $("#last_update-batal_mkdt").html("Dibatalkan oleh: -  Pada: -")

        $("#batal-id_kavling").val(id_kavling);
        $("#batal-id_mkdt").val(sh.data.id_mkdt);

        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");

        $.ajax({
            url: base_url + 'mkdt/batal_mkdt',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_mkdt: sh.data.id_mkdt,
                id_hargajual: sh.data2.id_hargajual,
                id_kavling: id_kavling
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(x) {
                let mkdt = x.data,
                    sb = x.sudah_bayar,
                    tb = x.total_biaya
                $.each(mkdt, function(k, v) {
                    $("#batal-" + k).val(v).change().keyup()
                })

                let src = not_found
                //load ktp npwp
                if (mkdt.surat_batal != null) {
                    src = mkdt.surat_batal
                }

                $("#list-file_surat_batal").prop("href", base_url + "/" + src)

                $("#last_update-batal_mkdt").html("Dibatalkan oleh: " + mkdt.mkdt_batal_oleh_u + " Pada: " + format_datetime(mkdt.mkdt_batal_tgl))

                $("#batal-total_biaya_um").val(tb.uang_muka).keyup()
                $("#batal-total_biaya_bb").val(tb.biaya_biaya).keyup()

                $("#batal-sudah_bayar_um").val(sb.uang_muka).keyup()
                $("#batal-sudah_bayar_bb").val(sb.biaya_biaya).keyup()

                $("#batal-sisa_tagihan_um").val(tb.uang_muka - sb.biaya_biaya).keyup()
                $("#batal-sisa_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()

                // $("#batal-persentase_bayar_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()
                // $("#batal-persentase_bayar_tagihan_um").val(tb.biaya_biaya - sb.biaya_biaya).keyup()


                $('#modal-batal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#loading").addClass("hidden");
            },
            error: function(e) {
                Swal.fire({
                    //
                    icon: 'error',
                    title: "Token tidak valid, muat ulang halaman",
                    showConfirmButton: true,
                    // //timer: 1500
                }).then(function() {
                    location.reload();
                })
            }
        });
    }

    $(document).keydown(function(event) {
        if (event.key === 'Escape') {
            hapus_seleksi()
        }
    });


    $("#dt-listrik_jenis").change(function() {
        if (this.value == "PLN") {
            $("#dt-listrik-pln-input-form").removeClass("hidden");
            $("#dt-listrik_disediakan").addClass("hidden");
        } else {
            $("#dt-listrik-pln-input-form").addClass("hidden");
            $("#dt-listrik_disediakan").removeClass("hidden");
        }
    });
    $("#dt-air_jenis").change(function() {
        if (this.value == "Air Tanah") {
            $("#dt-air_tanah-input_form").removeClass("hidden");
            $("#dt-air_komunal-input_form").addClass("hidden");
            $("#dt-air_pdam-input_form").addClass("hidden");
        } else if (this.value == "Komunal Warga") {
            $("#dt-air_tanah-input_form").addClass("hidden");
            $("#dt-air_komunal-input_form").removeClass("hidden");
            $("#dt-air_pdam-input_form").addClass("hidden");
        } else {
            $("#dt-air_tanah-input_form").addClass("hidden");
            $("#dt-air_komunal-input_form").addClass("hidden");
            $("#dt-air_pdam-input_form").removeClass("hidden");
        }
    });
</script>