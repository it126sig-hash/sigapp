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
                // position: 'bottom-end',
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
        padding: 0 10px 20px 10px;
        margin: 5px 0 0 5px;
        max-width: 90vw;
        /* left: 50%;
        transform: translateX(-50%); */
    }

    .my-float {
        margin-top: 22px;
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
                            <input id="blurRadius" type="text" onchange="onRadiusChange.apply(this, arguments)" style="float: left; width: 20px; margin-right: 10px;" />
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
                        <div class="col-md-3 d-none d-md-block" style="overflow-y:auto" id="filter-side">
                            <div class="divider">
                                <div class="divider-text">FilterCluster</div>
                            </div>

                            <div class="form-group">
                                <select id="filter-id_cluster" name="id_cluster" class="select2 select-sm form-control-sm"></select>
                            </div>
                            <div class="form-group">
                                <select disabled id="filter-id_jalan" name="id_jalan" class="select-sm form-control-sm select2 "></select>
                            </div>
                            <div class="form-group row">
                                <button class="btn btn-primary col-5 ml-1 mt-1 mb-1 btn-sm " onclick="filter_option()">Filter Data</button>
                                <button class="btn btn-outline-warning col-5 m-1 btn-sm " onclick="hapus_filter_option()">Hapus Filter</button>
                            </div>
                            <hr>
                            <div id="keterangan-warna-here"></div>
                            <hr>
                            <div class="form-group">
                                <!-- <button onclick="renderText()" class="btn btn-outline-warning  col-12" id="btn-renderText"> Tampilkan Keterangan Warna Di Siteplan</button> -->
                                <button onclick="export_siteplan()" class="btn btn-sm btn-outline-primary  col-12" id="btn-export-siteplan"> Export Siteplan</button>
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

<!--#################################### Modal Filter/Setting #########################################-->
<div class="modal modal-slide-in fade" id="modal-setting-filter">
    <div class="modal-dialog sidebar-sm">
        <div class="add-new-record modal-content pt-0">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Filter</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <div id="modal-filter"></div>
            </div>
        </div>
    </div>
</div>






<!-- Modal to add new record -->
<div class="modal modal-slide-in fade" id="modals-slide-in">
    <div class="modal-dialog sidebar-sm">
        <form id="fm-add_kavling" class="add-new-record modal-content pt-0">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Data</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="form-group">
                    <label class="form-label" for="basic-icon-default-fullname">Proyek</label>
                    <input type="hidden" class="form-control" id="points" readonly name="points" value="" />
                    <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                    <input type="text" class="form-control" id="nama_proyek" readonly name="nama_proyek" value="<?= $data['proyek']->nama_proyek ?>" placeholder="ASI" />
                    <input type="hidden" name="id_proyek" id="id_proyek" value="<?= $data['proyek']->id_proyek ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="basic-icon-default-post">Jenis</label>
                    <select id="id_jenis" name="id_jenis" class="select2 id_jenis custom-select">
                        <option value=""> - </option>
                        <option value="kavling">Kavling</option>
                        <option value="jalan">Jalan</option>
                        <option value="fasos">Fasos</option>
                        <option value="rth">RTH</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="basic-icon-default-post">Cluster</label>
                    <select id="id_cluster" name="id_cluster" class="select2 id_cluster custom-select"></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="basic-icon-default-post">Jalan</label>
                    <select disabled id="id_jalan" name="id_jalan" class="select id_jalan 2custom-select"></select>
                </div>
                <hr>
                <span>Luas di Lapangan : <br>
                    <span class='t_luas_produksi'></span>
                </span>
                <br>
                <br>
                <span>Luas di Sertifikat : <br>
                    <span class='t_luas_legal'></span>
                </span>
                <hr>
                <div class="form-group">
                    <label class="form-label" for="f_luas">Luas</label>
                    <input type="text" class="form-control" id="f_luas" name="f_luas" value="" placeholder="90" />
                </div>

                <!-- kavling -->
                <div id="div_kavling" class="h">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">No Rumah </label>
                        <input type="text" class="form-control" id="no_kavling" name="no_kavling" value="" placeholder="31" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-post">Tipe</label>
                        <select id="id_tipe" name="id_tipe" class="select2 id_tipe custom-select"></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-post">Status Kavling</label>
                        <select id="status_tanah" name="status_tanah" class="select2 custom-select" placeholder="standar/kelebihan tanah">
                            <option value="Standar">Standar</option>
                            <option value="Kelebihan Tanah">Kelebihan Tanah</option>
                        </select>
                    </div>
                </div>

                <!-- Fasos -->
                <div id="div_fasos" class="h">
                    <div class="form-group">
                        <label for="f_nama">Nama</label>
                        <input type="text" class="form-control" id="f_nama" name="f_nama" value="" placeholder="FASUM/SOS" />
                    </div>
                </div>

                <!-- luas -->
                <!-- <div class="div_luas" class="h">
                   </div> -->

                <!-- jalan -->
                <div id="div_jalan" class="h">
                    <div class="form-group">
                        <label for="f_planning_keterangan">Keterangan</label>
                        <textarea class="form-control" id="f_planning_keterangan" name="f_planning_keterangan" rows="3" placeholder="Keterangan"></textarea>
                    </div>
                </div>
                <button id="pindah_lokasi_btn" onclick="pindah_kavling()" type="button" class="btn btn-outline-primary btn-block waves-effect">Pindah Lokasi</button>



                <div class="form-group">
                    <label class="form-label" for="basic-icon-default-fullname">*catatan: gunakan titik koma ";" untuk pemisah nomor rumah jika akan input rumah lebih dari 1 kavling sekaligus</label>
                </div>
                <a id="add-form-btn" class="btn btn-primary data-submit mr-1" onclick="add_kavling()" href="javascript:void(0)">Simpan</a>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- ############################### edit kavling planning ###############################3# -->
<!-- <div class="modal modal-slide-in fade" id="modals-slide-in-edit">
       <div class="modal-dialog sidebar-sm">
           <form id="fm-edit_kavling" class="add-new-record modal-content pt-0">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
               <div class="modal-header mb-1">
                   <h5 class="modal-title" id="exampleModalLabel">Ubah Kavling</h5>
               </div>
               <div class="modal-body flex-grow-1">
                   <div class="form-group">
                       <label class="form-label" for="basic-icon-default-fullname">Proyek</label>
                       <input type="hidden" class="form-control" id="points" readonly name="points" value="" />
                       <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                       <input type="text" class="form-control" id="nama_proyek" readonly name="nama_proyek" value="<?= $data['proyek']->nama_proyek ?>" placeholder="ASI" />
                   </div>
                   <div class="form-group">
                       <label class="form-label" for="basic-icon-default-post">Cluster</label>
                       <select id="id_cluster" name="id_cluster" class="id_cluster select custom-select"></select>
                       
                   </div>
                   <div class="form-group">
                       <label class="form-label" for="basic-icon-default-post">Jalan</label>
                       <select id="id_jalan" name="id_jalan" class="select2 id_jalan custom-select"></select>
                       
                   </div>
                   <div class="form-group">
                       <label class="form-label" for="basic-icon-default-fullname">No Rumah </label>
                       <input type="text" class="form-control" id="no_kavling" name="no_kavling" value="" placeholder="31" />
                   </div>
                   <div class="form-group">
                       <label class="form-label" for="basic-icon-default-post">Tipe</label>
                       <select id="id_tipe" name="id_tipe" class="select2 id_tipe custom-select"></select>
                       
                   </div>
                   <div class="form-group">
                       <label class="form-label" for="basic-icon-default-fullname">*catatan: gunakan titik koma ";" untuk pemisah nomor rumah jika akan input rumah lebih dari 1 kavling sekaligus</label>
                   </div>
                   <a id="edit-form-btn" class="btn btn-primary mr-1" onclick="edit_kavling()" href="javascript:void(0)">Simpan</a>
                   <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
               </div>
           </form>
       </div>
   </div> -->

   <div class="modal fade text-left" id="modal_fothersproduksi" tabindex="-1" role="dialog" aria-labelledby="modal_fothersproduksi" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Produksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="fm-fotherproduksi" class="add-new-record modal-content pt-0">
                <div class="modal-body">
                    <p class="modal-title label_alamat" id="label_fothersproduksi"></p>
                    <br>
                    <span>Luas di Siteplan : <br>
                        <span class='t_luas_planning'></span>
                    </span>
                    <br>
                    <br>
                    <span>Luas di Sertifikat : <br>
                        <span class='t_luas_legal'></span>
                    </span>
                    <hr>
                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="id_produksi" name="id_produksi" value="" />

                    <div class="form-group">
                        <label for="f_progres_jalan">Progres</label>
                        <input type="range" onInput="$('.r_progres').html($(this).val())" class="form-control-range" min="0" max="100" step="5" id="f_progres_jalan" name="f_progres_jalan">
                        <span class="r_progres"></span><span>%</span>
                    </div>

                    <div class="form-group">
                        <label for="f_produksi_luas">Luas Dilapangan</label>
                        <input type="text" class="form-control" id="f_produksi_luas" name="f_produksi_luas" placeholder="Luas jalan dilapangan" />
                    </div>

                    <div class="form-group">
                        <label for="f_produksi_keterangan">Keterangan</label>
                        <textarea class="form-control" id="f_produksi_keterangan" name="f_produksi_keterangan" rows="3" placeholder="Keterangan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="save_fotherproduksi-btn" class="btn btn-primary data-submit mr-1" onclick="save_fotherproduksi()" href="javascript:void(0)">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade text-left" id="modal_divisi7" tabindex="-1" role="dialog" aria-labelledby="myModalLabel17" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Produksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="fm-produksi" class="add-new-record modal-content pt-0">
                <div class="modal-body">
                    <p class="modal-title label_alamat" id="label_alamat7"></p>
                    <hr>
                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="id_produksi" name="id_produksi" value="" />
                    <button id="download_gambar_kerja" type="button" class="btn btn-outline-primary btn-block waves-effect">Unduh Gambar Kerja</button>
                    <hr>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="fm-prod-progress-tab" data-toggle="tab" href="#fm-prod-progress" role="tab" aria-selected="true">Proges</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="fm-prod-dokumentasi-tab" data-toggle="tab" href="#fm-prod-dokumentasi" role="tab" aria-selected="true">Dokumentasi Bangunan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="fm-prod-slf-tab" data-toggle="tab" href="#fm-prod-slf" role="tab" aria-selected="true">SLF</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="fm-prod-listrik-tab" data-toggle="tab" href="#fm-prod-listrik" role="tab" aria-selected="true">Listrik</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="fm-prod-air-tab" data-toggle="tab" href="#fm-prod-air" role="tab" aria-selected="true">Air</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="fm-prod-progress" aria-labelledby="fm-prod-progress-tab" role="tabpanel">
                            <div class="form-group">
                                <div class="custom-control custom-switch custom-control-inline">
                                    <input type="checkbox" value="1" class="custom-control-input cbp" id="pondasi" name="pondasi" />
                                    <label class="custom-control-label" for="pondasi">Pondasi</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch custom-control-inline">
                                    <input type="checkbox" value="1" class="custom-control-input cbp" id="naik_dinding" name="naik_dinding" />
                                    <label class="custom-control-label" for="naik_dinding">Naik Dinding</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch custom-control-inline">
                                    <input type="checkbox" value="1" class="custom-control-input cbp" id="topping_off" name="topping_off" />
                                    <label class="custom-control-label" for="topping_off">Topping Off</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch custom-control-inline">
                                    <input type="checkbox" value="1" class="custom-control-input cbp" id="finishing" name="finishing" />
                                    <label class="custom-control-label" for="finishing">Finishing</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch custom-control-inline">
                                    <input type="checkbox" value="1" class="custom-control-input cbp" id="saluran" name="saluran" />
                                    <label class="custom-control-label" for="saluran">Saluran</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch custom-control-inline">
                                    <input type="checkbox" value="1" class="custom-control-input cbp" id="jalan" name="jalan" />
                                    <label class="custom-control-label" for="jalan">Jalan</label>
                                </div>
                            </div>
                            <hr>
                            <div class="af">
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" value="1" class="custom-control-input cbp" id="slo" name="slo" />
                                        <label class="custom-control-label" for="slo">SLO</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" value="1" class="custom-control-input cbp" id="bp" name="bp" />
                                        <label class="custom-control-label" for="bp">BP</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" value="1" class="custom-control-input cbp" id="lpa" name="lpa" />
                                        <label class="custom-control-label" for="lpa">LPA</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="progres_bangunan">Progres Bangunan</label>
                                <input type="range" class="form-control-range" value="0" id="progres_bangunan" name="progres_bangunan" step="5">
                                <span id="t_progres_bangunan"></span>%
                            </div>
                            <div class="form-group">
                                <label for="produksi_keterangan">Keterangan</label>
                                <textarea class="form-control" id="produksi_keterangan" name="produksi_keterangan" rows="3" placeholder="Keterangan"></textarea>
                            </div>
                        </div>
                        <div class="tab-pane" id="fm-prod-dokumentasi" aria-labelledby="fm-prod-dokumentasi-tab" role="tabpanel">
                            <div cloass="form-group">
                                <label for="upload_komplain_produksi">Foto Konstruksi(Jika Ada, Pembesian, Pondasi)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" accept="image/*" name="prod_foto_konstruksi[]" id="prod_foto_konstruksi" multiple />
                                    <label class="custom-file-label" id="label_prod_foto_konstruksi" for="prod_foto_konstruksi">Bisa Lebih dari 1 foto</label>
                                    <div id="list_prod_foto_konstruksi"></div>
                                </div>
                            </div>
                            <hr>
                            <div cloass="form-group">
                                <label for="upload_komplain_produksi">Foto Exterior(Depan dan Belakang(min. 1 photo), foto memiliki titik koordinat)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" accept="image/*" name="prod_foto_exterior[]" id="prod_foto_exterior" multiple />
                                    <label class="custom-file-label" id="label_prod_foto_exterior" for="prod_foto_exterior">Bisa Lebih dari 1 foto</label>
                                    <div id="list_prod_foto_exterior"></div>
                                </div>
                            </div>
                            <hr>
                            <div cloass="form-group">
                                <label for="upload_komplain_produksi">Foto Interior(kamar, dapur, toilet, dan ruang tengah (min. 1 photo), foto memiliki titik koordinat)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" accept="image/*" name="prod_foto_interior[]" id="prod_foto_interior" multiple />
                                    <label class="custom-file-label" id="label_prod_foto_interior" for="prod_foto_interior">Bisa Lebih dari 1 foto</label>
                                    <div id="list_prod_foto_interior"></div>
                                </div>
                            </div>

                        </div>
                        <div class="tab-pane" id="fm-prod-slf" aria-labelledby="fm-prod-slf-tab" role="tabpanel">
                            <div class="divider">
                                <div class="divider-text">Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi (SLF)</div>
                            </div>
                            <div class="form-group">
                                <label>Jenis Dokumen</label>
                                <select id="slf_jenis" name="slf_jenis" class="form-control">
                                    <option value="SLF">SLF</option>
                                    <option value="Surat Pernyataan">Surat Pernyataan</option>
                                </select>
                            </div>
                            <div id="slf-input-form">
                                <div class="form-group">
                                    <label>No Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi (sesuai dokumen)</label>
                                    <input type="text" class="form-control" id="slf_no" name="slf_no">
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi (sesuai dokumen)</label>
                                    <input type="text" class="form-control flatpickr-human-friendly" id="slf_tanggal" name="slf_tanggal">
                                </div>
                                <div cloass="form-group">
                                    <label for="label_slf_dokumen">Dokumen Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="application/pdf" name="slf_dokumen" id="slf_dokumen" />
                                        <label class="custom-file-label" id="label_slf_dokumen" for="slf_dokumen"></label>
                                    </div>
                                </div>
                            </div>
                            <div id="surat_pernyataan-input-form" class="hidden">
                                <div class="form-group">
                                    <label>No Surat Pernyataan Laik Fungsi(sesuai dokumen)</label>
                                    <input type="text" class="form-control" id="surat_pernyataan_no" name="surat_pernyataan_no">
                                </div>
                                <div class="form-group">
                                    <label>NPWP Penertbit Surat Pernyataan Laik Fungsi (sesuai dokumen)</label>
                                    <input type="text" class="form-control" id="surat_pernyataan_npwp" name="surat_pernyataan_npwp">
                                </div>
                                <div class="form-group">
                                    <label>Nama Penertbit Surat Pernyataan Laik Fungsi (sesuai dokumen)</label>
                                    <input type="text" class="form-control" id="surat_pernyataan_nama" name="surat_pernyataan_nama">
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Surat Pernyataan Laik Fungsi</label>
                                    <input type="text" class="form-control flatpickr-human-friendly" id="surat_pernyataan_tanggal" name="surat_pernyataan_tanggal">
                                </div>
                                <div cloass="form-group">
                                    <label for="label_surat_pernyataan_dokumen">Tanggal Surat Pernyataan Laik Fungsi</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="application/pdf" name="surat_pernyataan_dokumen" id="surat_pernyataan_dokumen" />
                                        <label class="custom-file-label" id="label_surat_pernyataan_dokumen" for="surat_pernyataan_dokumen"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="fm-prod-listrik" aria-labelledby="fm-prod-listrik-tab" role="tabpanel">
                            <div class="divider">
                                <div class="divider-text">Ketersediaan Listrik</div>
                            </div>
                            <div class="form-group">
                                <label>Jenis Sumber Listrik</label>
                                <select id="listrik_jenis" name="listrik_jenis" class="form-control">
                                    <option value="PLN">PLN</option>
                                    <option value="Disendiakan Pengembang">Disendiakan Pengembang (Dalam Pengajuan)</option>
                                </select>
                            </div>
                            <div id="listrik-pln-input-form">
                                <div class="form-group">
                                    <label>No ID Pelanggan/Nomor Meteran Listrik PLN</label>
                                    <input type="text" class="form-control" id="listrik_pln" name="listrik_pln">
                                </div>
                                <div cloass="form-group">
                                    <label for="label_slf_dokumen">Foto Ketersediaan Lampu Menyala</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="image/*" name="listrik_pln_foto" id="listrik_pln_foto" />
                                        <label class="custom-file-label" id="label_slf_dokumen" for="slf_dokumen"></label>
                                    </div>
                                </div>
                            </div>
                            <div id="listrik_disediakan" class="hidden">
                                <div class="form-group">
                                    <label>No Pengajuan Listrik PLN</label>
                                    <input type="text" class="form-control" id="listrik_disediakan_no" name="listrik_disediakan_no">
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Pengajuan Listrik PLN</label>
                                    <input type="text" class="form-control flatpickr-human-friendly" id="listrik_disediakan_tanggal" name="listrik_disediakan_tanggal">
                                </div>
                                <div cloass="form-group">
                                    <label for="label_listrik_disediakan_dokumen">Upload Bukti Pengajuan</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="application/pdf" name="listrik_disediakan_dokumen" id="listrik_disediakan_dokumen" />
                                        <label class="custom-file-label" id="label_listrik_disediakan_dokumen" for="listrik_disediakan_dokumen"></label>
                                    </div>
                                </div>
                                <div cloass="form-group">
                                    <label for="listrik_disediakan_foto">Foto Ketersediaan Lampu Menyala</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="image/*" name="listrik_disediakan_foto" id="listrik_disediakan_foto" />
                                        <label class="custom-file-label" id="labe_listrik_disediakan_foto" for="listrik_disediakan_foto"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="fm-prod-air" aria-labelledby="fm-prod-air-tab" role="tabpanel">
                            <div class="divider">
                                <div class="divider-text">Ketersediaan Air</div>
                            </div>
                            <div class="form-group">
                                <label>Jenis Sumber Air</label>
                                <select id="air_jenis" name="air_jenis" class="form-control">
                                    <option value="Air Tanah">Air Tanah</option>
                                    <option value="Komunal Warga">Komunal Warga</option>
                                    <option value="PDAM">PDAM</option>
                                </select>
                            </div>
                            <div id="air_tanah_input_form">
                                <div cloass="form-group">
                                    <label for="air_tanah">Foto ketersediaan air bersih dengan air mengalir & sumber air (min. 1 foto)</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="image/*" name="air_tanah[]" id="air_tanah" multiple />
                                        <label class="custom-file-label" id="label_air_tanah" for="air_tanah"></label>
                                    </div>
                                </div>
                            </div>
                            <div id="air_komunal_input_form" class="hidden">
                                <div cloass="form-group">
                                    <label for="air_komunal">Foto ketersediaan air bersih dengan air mengalir & sumber air komunal bersama (min. 1 foto)</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="image/*" name="air_komunal[]" id="air_komunal" multiple />
                                        <label class="custom-file-label" id="label_air_komunal" for="air_komunal"></label>
                                    </div>
                                </div>
                            </div>
                            <div id="air_pdam_input_form" class="hidden">
                                <div class="form-group">
                                    <label>No Meteran Air PDAM</label>
                                    <input type="text" class="form-control" id="air_pdam_no" name="air_pdam_no">
                                </div>
                                <div cloass="form-group">
                                    <label for="air_pdam">Foto ketersediaan air bersih dengan air mengalir & meteran air PDAM (min. 1 foto)</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="image/*" name="air_pdam[]" id="air_pdam" multiple />
                                        <label class="custom-file-label" id="label_air_pdam" for="air_pdam"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Deskripsi Unit (informasi keunggulan unit)</label>
                                <input type="text" class="form-control" id="air_deskripsi_unit" name="air_deskripsi_unit">
                            </div>
                        </div>
                    </div>

                    <div class="divider">
                        <div class="divider-text">Checklist</div>
                    </div>
                    <p>
                        <button data-toggle="collapse" href="#collapseExample" type="button" class="btn btn-outline-primary btn-block waves-effect">Tampilkan Checklist</button>
                    </p>
                    <div class="collapse" id="collapseExample">
                        <small id="last_update_checklist_prod" class="text-muted"></small>
                        <div class="card card-body">
                            <?php
                            $n = 1;
                            foreach ($list as $l) {
                                echo '
                                    <div class="divider">
                                        <div class="divider-text">' . $n . '.) ' . $l->nama_group . ' - ' . $l->nama_item . '</div>
                                    </div>
                                    <dl class="row">
                                        <dd class="col-sm-2">' . $l->nama_subitem . '</dd>                                        
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_t[' . $l->id_subitem . ']" name="hasil_cek_t[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_t[' . $l->id_subitem . ']">Tes</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_f[' . $l->id_subitem . ']" name="hasil_cek_f[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_f[' . $l->id_subitem . ']">Fungsi</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_v[' . $l->id_subitem . ']" name="hasil_cek_v[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_v[' . $l->id_subitem . ']">Visual</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-sm-4"><textarea placeholder="keterangan" type="text" class="form-control" id="keterangan_cek_produksi[' . $l->id_subitem . ']" name="keterangan_cek_produksi[' . $l->id_subitem . ']"></textarea></dd>
                                    </dl>
                                    ';
                                $n++;
                            }
                            ?>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="add-form-btn-produksi" class="btn btn-primary data-submit mr-1" onclick="save_produksi()" href="javascript:void(0)">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_komplain_produksi">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form id="fm-komplain-produksi" class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Komplain Kavling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_alamat" id="label_alamat5"></p>
                <hr>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="fmkp-komplain-tab" data-toggle="tab" href="#fmkp-komplain" aria-controls="fmkp-komplain" role="tab" aria-selected="true">Komplain</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="fmkp-ditangani-tab" data-toggle="tab" href="#fmkp-ditangani" aria-controls="fmkp-ditangani" role="tab" aria-selected="true">Tangani</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="fmkp-selesai-tab" data-toggle="tab" href="#fmkp-selesai" aria-controls="fmkp-ditangani" role="tab" aria-selected="true">Selesai</a>
                    </li>
                </ul>

                <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                <input type="hidden" class="form-control" id="id_komplain" name="id_komplain" value="" />
                <small id="last_update_komplain_produksi" class="text-muted"></small>

                <div class="tab-content">
                    <div class="tab-pane active" id="fmkp-komplain" aria-labelledby="fmkp-komplain-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="form-group">
                                    <label for="username_komplain_oleh">Dikomplain Oleh</label>
                                    <input readonly type="text" class="form-control" id="username_komplain_oleh" name="username_komplain_oleh" />
                                </div>
                                <div class="form-group">
                                    <label for="komplain_tgl">Tanggal Komplain</label>
                                    <input disabled type="text" class="form-control flatpickr-human-friendly" id="komplain_tgl" name="komplain_tgl" />
                                </div>
                                <div class="form-group">
                                    <label for="keterangan_komplain">Keterangan Komplain</label>
                                    <textarea readonly class="form-control" id="keterangan_komplain" name="keterangan_komplain" rows="3" placeholder="Keterangan"></textarea>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <button id="komplain_selesai_btn_produksi" type="button" class="btn btn-outline-success btn-block waves-effect hidden">Komplain Selesai</button>
                                <h5>Foto Komplain</h5>
                                <!-- -----------------------------------dikomplain--------------------------------------- -->
                                <div id="controls_produksi_foto_komplain_sales" class="carousel slide">
                                    <div class="carousel-inner" id="foto_komplain_sales">
                                        <!-- Foto komplain belongs here -->
                                    </div>
                                    <a class="carousel-control-prev" href="#controls_produksi_foto_komplain_sales" role="button" data-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                    <a class="carousel-control-next" href="#controls_produksi_foto_komplain_sales" role="button" data-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Next</span>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="fmkp-ditangani" aria-labelledby="fmkp-ditangani-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <!-- ------------------------------terima komplain------------------------------ -->
                                <div class="divider">
                                    <div class="divider-text">Terima Komplain</div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" value="1" class="custom-control-input" id="terima_komplain" name="terima_komplain" />
                                        <label class="custom-control-label" for="terima_komplain">Terima Komplain</label>
                                    </div>
                                </div>
                                <div id="terima_komplain_div" class="hidden ditangani_form">
                                    <div class="form-group">
                                        <label for="keterangan_ditangani">Keterangan</label>
                                        <textarea class="form-control" id="keterangan_ditangani" name="keterangan_ditangani" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                </div>
                                <div class="hidden ditangani_form">
                                    <div class="form-group">
                                        <label for="username_ditangani_oleh">Komplain Diterima Oleh</label>
                                        <input disabled type="text" class="form-control" id="username_ditangani_oleh" name="username_ditangani_oleh" />
                                    </div>
                                    <div class="form-group">
                                        <label for="ditangani_tgl">Tanggal Komplain Diterima</label>
                                        <input disabled type="text" class="form-control flatpickr-human-friendly" id="ditangani_tgl" name="ditangani_tgl" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <!-- ---------------------------------------- komplain diselesaikan ---------------------------->
                                <div id="selesaikan_komplain_div" class="hidden">
                                    <div class="divider">
                                        <div class="divider-text">Selesaikan Komplain</div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" value="1" class="custom-control-input" id="is_selesai_produksi" name="is_selesai_produksi" />
                                            <label class="custom-control-label" for="is_selesai_produksi">Selesaikan Komplain</label>
                                        </div>
                                    </div>
                                    <div id="div_upload_komplain_produksi">
                                        <label for="upload_komplain_produksi">Foto Perbaikan</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" accept="image/*" name="upload_komplain_produksi[]" id="upload_komplain_produksi" multiple />
                                            <label class="custom-file-label" id="label_upload_komplain_produksi" for="upload_komplain_produksi">Bisa Lebih dari 1 foto</label>
                                            <div id="list_upload_komplain_produksi"></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="selesai_keterangan_produksi">Keterangan </label>
                                        <textarea class="form-control" id="selesai_keterangan_produksi" name="selesai_keterangan_produksi" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="username_selesai_oleh_produksi">Diselesakan Oleh</label>
                                        <input disabled type="text" class="form-control" id="username_selesai_oleh_produksi" name="username_selesai_oleh_produksi" />
                                    </div>
                                    <div class="form-group">
                                        <label for="selesai_tgl_produksi">Tanggal Diselesaikan</label>
                                        <input disabled type="text" class="form-control flatpickr-human-friendly" id="selesai_tgl_produksi" name="selesai_tgl_produksi" />
                                    </div>
                                    <div id="controls_produksi_foto_komplain_produksi" class="carousel slide">
                                        <div class="carousel-inner" id="foto_komplain_produksi">
                                            <!-- Foto komplain belongs here -->
                                        </div>
                                        <a class="carousel-control-prev" href="#controls_produksi_foto_komplain_produksi" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">
                                                << /span>
                                        </a>
                                        <a class="carousel-control-next" href="#controls_produksi_foto_komplain_produksi" role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="fmkp-selesai" aria-labelledby="fmkp-selesai -tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <!-- ---------------------------------------- komplain diselesaikan ---------------------------->
                                <div id="komplain_selesai_sip" class="hidden">
                                    <div class="divider">
                                        <div class="divider-text">Komplain Selesai (sales)</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="selesai_keterangan_sales">Keterangan </label>
                                        <textarea disabled class="form-control" id="selesai_keterangan_sales" name="selesai_keterangan_sales" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="username_selesai_oleh_sales">Diselesakan Oleh</label>
                                        <input disabled type="text" class="form-control" id="username_selesai_oleh_sales" name="username_selesai_oleh_sales" />
                                    </div>
                                    <div class="form-group">
                                        <label for="selesai_tgl_sales">Tanggal Diselesaikan</label>
                                        <input disabled type="text" class="form-control flatpickr-human-friendly" id="selesai_tgl_sales" name="selesai_tgl_sales" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="komplain-produksi-form-btn" class="btn btn-primary data-submit mr-1" onclick="save_komplain_produksi()" href="javascript:void(0)">Simpan</a>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!--#################################### Modal Sales #########################################-->
<div class="modal modal-slide-in fade" id="modal_serah_terima">
    <div class="modal-dialog sidebar-sm">
        <form id="fm-serah-terima" class="add-new-record modal-content pt-0">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Serah Terima</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_alamat" id="label_alamat5"></p>
                <hr>

                <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                <input type="hidden" class="form-control" id="id_serah_terima" name="id_serah_terima" value="" />

                <div class="form-group">
                    <div class="custom-control custom-switch custom-control-inline">
                        <input type="checkbox" value="1" class="custom-control-input" id="is_serah_terima" name="is_serah_terima" />
                        <label class="custom-control-label" for="is_serah_terima">Sudah Serah Terima</label>
                    </div>
                </div>
                <small id="last_update_serah_terima" class="text-muted"></small>
                <div class="form-group">
                    <label for="serah_terima_oleh">Oleh</label>
                    <input type="text" class="form-control" id="serah_terima_oleh" name="serah_terima_oleh" />
                </div>
                <div class="form-group">
                    <label for="serah_terima_ke">Ke</label>
                    <input type="text" class="form-control" id="serah_terima_ke" name="serah_terima_ke" />
                </div>
                <div class="form-group">
                    <label for="serah_terima_tgl">Tanggal Serah Terima</label>
                    <input type="text" class="form-control flatpickr-human-friendly" id="serah_terima_tgl" name="serah_terima_tgl" />
                </div>
                <div class="form-group">
                    <label for="serah_terima_keterangan">Keterangan Serah Terima</label>
                    <textarea class="form-control" id="serah_terima_keterangan" name="serah_terima_keterangan" rows="3" placeholder="Keterangan"></textarea>
                </div>


                <button id="serah-terima-form-btn" class="btn btn-primary data-submit mr-1" onclick="save_serah_terima()" href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade text-left" id="checklist_modal_sales" tabindex="-1" role="dialog" aria-labelledby="myModalLabel17" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Sales & Promotion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="fm-checklist-sales" class="add-new-record modal-content pt-0">
                <div class="modal-body">
                    <p class="modal-title label_alamat" id="label_alamat8"></p>
                    <hr>
                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="id_sales" name="id_sales" value="" />
                    <div class="divider">
                        <div class="divider-text">Checklist</div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch custom-control-inline">
                            <input type="checkbox" value="1" class="custom-control-input cbp" id="is_checked" name="is_checked" />
                            <label class="custom-control-label" for="is_checked">Tandai sudah dicek</label>
                        </div>
                    </div>
                    <p>
                        <button data-toggle="collapse" href="#collapseExample" type="button" class="btn btn-outline-primary btn-block waves-effect">Tampilkan Checklist</button>
                    </p>
                    <div class="collapse" id="collapseExample">
                        <small id="last_update_checklist_prod2" class="text-muted"></small><br>
                        <small id="last_update_checklist_sales" class="text-muted"></small>

                        <div class="card card-body">
                            <?php
                            $n = 1;
                            foreach ($list as $l) {
                                echo '
                                    <div class="divider">
                                        <div class="divider-text">' . $n . '.) ' . $l->nama_group . ' - ' . $l->nama_item . '</div>
                                    </div>
                                    <div class="divider">
                                        <div class="divider-text">Checklist Produksi</div>
                                    </div>
                                    <dl class="row">
                                        <dd class="col-sm-2">' . $l->nama_subitem . '</dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input disabled type="checkbox" value="1" class="custom-control-input" id="hasil_cek_t[' . $l->id_subitem . ']" name="hasil_cek_t[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_t[' . $l->id_subitem . ']">Tes</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input disabled type="checkbox" value="1" class="custom-control-input" id="hasil_cek_f[' . $l->id_subitem . ']" name="hasil_cek_f[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_f[' . $l->id_subitem . ']">Fungsi</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input disabled type="checkbox" value="1" class="custom-control-input" id="hasil_cek_v[' . $l->id_subitem . ']" name="hasil_cek_v[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_v[' . $l->id_subitem . ']">Visual</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-sm-4"><textarea readonly placeholder="keterangan" type="text" class="form-control" id="keterangan_cek_produksi[' . $l->id_subitem . ']" name="keterangan_cek_produksi[' . $l->id_subitem . ']"></textarea></dd>
                                    </dl>
                                    <div class="divider">
                                        <div class="divider-text">Checklist Sales & Promotion</div>
                                    </div>
                                    <dl class="row">
                                        <dd class="col-sm-2">' . $l->nama_subitem . '</dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_t_s[' . $l->id_subitem . ']" name="hasil_cek_t_s[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_t_s[' . $l->id_subitem . ']">Tes</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_f_s[' . $l->id_subitem . ']" name="hasil_cek_f_s[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_f_s[' . $l->id_subitem . ']">Fungsi</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_v_s[' . $l->id_subitem . ']" name="hasil_cek_v_s[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_v_s[' . $l->id_subitem . ']">Visual</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-sm-4"><textarea placeholder="keterangan" type="text" class="form-control" id="keterangan_cek_sales[' . $l->id_subitem . ']" name="keterangan_cek_sales[' . $l->id_subitem . ']"></textarea></dd>
                                    </dl>
                                    
                                    ';
                                $n++;
                            }
                            ?>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="checklist-form-btn-sales" class="btn btn-primary data-submit mr-1" onclick="save_checklist_sales()" href="javascript:void(0)">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                        <a class="nav-link active" id="fmks-komplain-tab" data-toggle="tab" href="#fmks-komplain" aria-controls="fmks-komplain" role="tab" aria-selected="true">Komplain</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="fmks-ditangani-tab" data-toggle="tab" href="#fmks-ditangani" aria-controls="fmks-ditangani" role="tab" aria-selected="true">Ditangani</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="fmks-selesai-tab" data-toggle="tab" href="#fmks-selesai" aria-controls="fmks-ditangani" role="tab" aria-selected="true">Selesai</a>
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
                                        <input type="file" class="custom-file-input" accept="image/*" name="upload_komplain_sales[]" id="upload_komplain_sales" multiple />
                                        <label class="custom-file-label" id="label_upload_komplain_sales" for="upload_komplain_sales">Bisa Lebih dari 1 foto</label>
                                        <div id="list_upload_komplain_sales"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="keterangan_komplain">Keterangan Komplain</label>
                                    <textarea class="form-control" id="keterangan_komplain" name="keterangan_komplain" rows="3" placeholder="Keterangan"></textarea>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div id="batal_komplain" class="hidden">
                                    <button id="batal_komplain_btn" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="batal_komplain()">Batalkan Komplain</button>
                                    <div class="form-group">
                                        <label for="username_komplain_oleh">Dikomplain Oleh</label>
                                        <input readonly type="text" class="form-control" id="username_komplain_oleh" name="username_komplain_oleh" />
                                    </div>
                                    <div class="form-group">
                                        <label for="komplain_tgl">Tanggal Komplain</label>
                                        <input disabled type="text" class="form-control flatpickr-human-friendly" id="komplain_tgl" name="komplain_tgl" />
                                    </div>
                                    <div id="control_sales_foto_komplain_sales" class="carousel slide">
                                        <div class="carousel-inner" id="foto_komplain_sales">
                                            <!-- Foto komplain belongs here -->
                                        </div>
                                        <a class="carousel-control-prev" href="#control_sales_foto_komplain_sales" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#control_sales_foto_komplain_sales" role="button" data-slide="next">
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
                                <textarea disabled class="form-control" id="keterangan_ditangani" name="keterangan_ditangani" rows="3" placeholder="Keterangan"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="username_ditangani_oleh">Komplain Diterima Oleh</label>
                                <input disabled type="text" class="form-control" id="username_ditangani_oleh" name="username_ditangani_oleh" />
                            </div>
                            <div class="form-group">
                                <label for="ditangani_tgl">Tanggal Komplain Diterima</label>
                                <input disabled type="text" class="form-control flatpickr-human-friendly" id="ditangani_tgl" name="ditangani_tgl" />
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
                                        <textarea disabled class="form-control" id="selesai_keterangan_produksi" name="selesai_keterangan_produksi" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="username_selesai_oleh_produksi">Diselesakan Oleh</label>
                                        <input disabled type="text" class="form-control" id="username_selesai_oleh_produksi" name="username_selesai_oleh_produksi" />
                                    </div>
                                    <div class="form-group">
                                        <label for="selesai_tgl_produksi">Tanggal Diselesaikan</label>
                                        <input disabled type="text" class="form-control flatpickr-human-friendly" id="selesai_tgl_produksi" name="selesai_tgl_produksi" />
                                    </div>
                                    <div id="controls_sales_foto_komplain_produksi" class="carousel slide">
                                        <div class="carousel-inner" id="foto_komplain_produksi">
                                            <!-- Foto komplain belongs here -->
                                        </div>
                                        <a class="carousel-control-prev" href="#controls_sales_foto_komplain_produksi" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#controls_sales_foto_komplain_produksi" role="button" data-slide="next">
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
                                        <input type="checkbox" value="1" class="custom-control-input" id="is_selesai_sales" name="is_selesai_sales" />
                                        <label class="custom-control-label" for="is_selesai_sales">Selesaikan Komplain</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="selesai_keterangan_sales">Keterangan </label>
                                    <textarea class="form-control" id="selesai_keterangan_sales" name="selesai_keterangan_sales" rows="3" placeholder="Keterangan"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="username_selesai_oleh_sales">Diselesakan Oleh</label>
                                    <input disabled type="text" class="form-control" id="username_selesai_oleh_sales" name="username_selesai_oleh_sales" />
                                </div>
                                <div class="form-group">
                                    <label for="selesai_tgl_sales">Tanggal Diselesaikan</label>
                                    <input disabled type="text" class="form-control flatpickr-human-friendly" id="selesai_tgl_sales" name="selesai_tgl_sales" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="komplain-sales-form-btn" class="btn btn-primary data-submit mr-1" onclick="save_komplain_sales()" href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!--#################################### Modal Legal #########################################-->
<div class="modal modal-slide-in fade" id="modal_fotherlegal">
    <div class="modal-dialog sidebar-sm">
        <form id="fm-fotherlegal" class="add-new-record modal-content pt-0">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Legal</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_alamat" id="label_alamat5"></p>
                <br>
                <span>Luas di Siteplan : <br>
                    <span class='t_luas_planning'></span>
                </span>
                <br>
                <br>
                <span>Luas di Lapangan : <br>
                    <span class='t_luas_produksi'></span>
                </span>
                <hr>
                <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />

                <div class="form-group">
                    <label for="fl_progres_jalan">Progres</label>
                    <input disabled type="range" onInput="$('.r_progres').html($(this).val())" class="form-control-range" min="0" max="100" step="5" id="fl_progres_jalan">
                    <span class="r_progres"></span><span>%</span>
                </div>

                <div class="form-group">
                    <label for="f_legal_luas">Luas di Sertifikat</label>
                    <input type="text" class="form-control" id="f_legal_luas" name="f_legal_luas" placeholder="Luas jalan di sertifikat" />
                </div>

                <div class="form-group">
                    <label for="f_legal_keterangan">Keterangan</label>
                    <textarea class="form-control" id="f_legal_keterangan" name="f_legal_keterangan" rows="3" placeholder="Keterangan"></textarea>
                </div>

                <button id="save-fother-btn-legal" class="btn btn-primary data-submit mr-1" onclick="save_fotherlegal()" href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="modal_flegal">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Legal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_alamat" id="label_alamat5"></p>
                <hr>
                <ul class="nav nav-tabs" role="tablist">

                    <li class="nav-item">
                        <a class="nav-link active" id="legal-sertifikat-tab" data-toggle="tab" href="#legal-sertifikat" aria-controls="home" role="tab" aria-selected="true">Sertipikat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="legal-pbb-tab" data-toggle="tab" href="#legal-pbb" aria-controls="home" role="tab" aria-selected="true">PBB</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="legal-bphtb-tab" data-toggle="tab" href="#legal-bphtb" aria-controls="home" role="tab" aria-selected="true">BPHTB</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="legal-pbg-tab" data-toggle="tab" href="#legal-pbg" aria-controls="home" role="tab" aria-selected="true">IMB/PBG</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link " id="legal-pph-tab" data-toggle="tab" href="#legal-pph" aria-controls="home" role="tab" aria-selected="true">PPh</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="legal-ajb-tab" data-toggle="tab" href="#legal-ajb" aria-controls="home" role="tab" aria-selected="true">AJB</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="upload-sertifikat-tab" data-toggle="tab" href="#upload-sertifikat" aria-controls="home" role="tab" aria-selected="true">Upload Softfile</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane" id="legal-pbb" aria-labelledby="legal-pbb-tab" role="tabpanel">
                        <form id="fm-legal">

                            <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                            <input type="hidden" class="form-control" id="id_legal" name="id_legal" value="" />

                            <div class="row">
                                <div class="col-12">
                                    <div class="divider">
                                        <div class="divider-text">Mutasi Pecah PBB</div>
                                    </div>
                                    <div class="form-group">
                                        <label>NOP PBB</label>
                                        <input type="text" class="form-control" id="pbb_pecah_nop" name="pbb_pecah_nop">
                                    </div>
                                    <div class="form-group">
                                        <label>Luas Bumi</label>
                                        <input type="text" class="form-control" id="pbb_pecah_luas_bumi" name="pbb_pecah_luas_bumi">
                                    </div>
                                    <div class="form-group">
                                        <label>NJOP Bumi</label>
                                        <input type="text" class="form-control" id="pbb_pecah_njop_bumi" name="pbb_pecah_njop_bumi">
                                    </div>
                                    <div class="form-group">
                                        <label>Luas Bangunan</label>
                                        <input type="text" class="form-control" id="pbb_pecah_luas_bangunan" name="pbb_pecah_luas_bangunan">
                                    </div>
                                    <div class="form-group">
                                        <label>NJOP Bangunan</label>
                                        <input type="text" class="form-control" id="pbb_pecah_njop_bangunan" name="pbb_pecah_njop_bangunan">
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal bayar</label>
                                        <input type="text" id="pbb_pecah_tanggal_bayar" name="pbb_pecah_tanggal_bayar" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                    <div class="form-group">
                                        <label>Jumlah Tagihan</label>
                                        <input type="text" class="form-control" id="pbb_pecah_jumlah_tagihan" name="pbb_pecah_jumlah_tagihan">
                                    </div>

                                </div>

                                <!-- 
                                <div class="col-sm-12 col-md-3 col-lg-3">
                                    <h5 class="modal-title" id="exampleModalLabel">Sertifikat</h5>
                                    <div class="form-group">
                                        <label for="sertifikat_tgl">Tanggal Sertifikat</label>
                                        <input type="text" id="sertifikat_tgl" name="sertifikat_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                    <div class="form-group">
                                        <label for="sertifikat_luas">Luas Tanah</label>
                                        <input type="text" class="form-control" id="sertifikat_luas" name="sertifikat_luas">
                                    </div>
                                    <div class="form-group">
                                        <label for="sertifikat_no_hgb">No HGB</label>
                                        <input type="text" class="form-control" id="sertifikat_no_hgb" name="sertifikat_no_hgb">
                                    </div>
                                    <div class="form-group">
                                        <label for="sertifikat_no_split">No Split</label>
                                        <input type="text" class="form-control" id="sertifikat_no_split" name="sertifikat_no_split">
                                    </div>
                                    <div class="form-group">
                                        <label for="sertifikat_masa_berlaku">Masa Berlaku</label>
                                        <input type="text" id="sertifikat_masa_berlaku" name="sertifikat_masa_berlaku" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4">
                                    <div class="divider">
                                        <div class="divider-text">IMB</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="imb_tgl">Tanggal IMB</label>
                                        <input type="text" id="imb_tgl" name="imb_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                    <div class="form-group">
                                        <label for="imb_no_induk">No Induk</label>
                                        <input type="text" class="form-control" id="imb_no_induk" name="imb_no_induk">
                                    </div>
                                    <div class="form-group">
                                        <label for="imb_no_split">No Split</label>
                                        <input type="text" class="form-control" id="imb_no_split" name="imb_no_split">
                                    </div>
                                    <div class="divider">
                                        <div class="divider-text">BPHTB</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="bphtb_tgl">Tanggal BPHTB</label>
                                        <input type="text" id="bphtb_tgl" name="bphtb_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                    <div class="form-group">
                                        <label for="bphtb_masa_berlaku">Masa Berlaku</label>
                                        <input type="text" id="bphtb_masa_berlaku" name="bphtb_masa_berlaku" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                    <div class="form-group">
                                        <label for="bphtb_validasi">Validasi</label>
                                        <input type="text" id="bphtb_validasi" name="bphtb_validasi" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4">
                                    <div class="divider">
                                        <div class="divider-text">NOP</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="nop_pbb">NOP</label>
                                        <input type="text" class="form-control" id="nop_pbb" name="nop_pbb">
                                    </div>
                                    <div class="form-group">
                                        <label for="pph">PPh</label>
                                        <input type="text" class="form-control" id="pph" name="pph">
                                    </div>
                                    <div class="form-group">
                                        <label for="pbg">PBG</label>
                                        <input type="text" class="form-control" id="pbg" name="pbg">
                                    </div>
                                  
                                    <div class="form-group">
                                        <label for="legal_keterangan">Keterangan</label>
                                        <textarea class="form-control" id="legal_keterangan" name="legal_keterangan" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                </div> -->
                            </div>
                    </div>
                    <div class="tab-pane active" id="legal-sertifikat" aria-labelledby="legal-sertifikat-tab" role="tabpanel">
                        <div class="col-12">
                            <div class="divider">
                                <div class="divider-text">Sertipikat Split</div>
                            </div>
                            <div class="form-group">
                                <label>No HGB</label>
                                <input type="text" class="form-control" id="sertifikat_split_no_hgb" name="sertifikat_split_no_hgb">
                            </div>
                            <div class="form-group">
                                <label>Tanggal Terbit Sertipikat</label>
                                <input type="text" id="sertifikat_split_tanggal_terbit" name="sertifikat_split_tanggal_terbit" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tanggal Terbit Berakhir</label>
                                <input type="text" id="sertifikat_split_tanggal_berakhir" name="sertifikat_split_tanggal_berakhir" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>NIB</label>
                                <input type="text" class="form-control" id="sertifikat_split_nib" name="sertifikat_split_nib">
                            </div>
                            <div class="form-group">
                                <label>Tanggal Surat Ukur</label>
                                <input type="text" id="sertifikat_split_tanggal_surat_ukur" name="sertifikat_split_tanggal_surat_ukur" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>No Surat Ukur</label>
                                <input type="text" id="sertifikat_split_no_surat_ukur" name="sertifikat_split_no_surat_ukur" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Luas Tanah</label>
                                <input type="text" class="form-control" id="sertifikat_split_luas_tanah" name="sertifikat_split_luas_tanah">
                            </div>

                            <div class="divider">
                                <div class="divider-text">Sertipikat Balik Nama</div>
                            </div>

                            <div class="form-group">
                                <label>Nama Konsumen</label>
                                <input type="text" class="form-control" id="sertifikat_balik_nama" name="sertifikat_balik_nama">
                            </div>
                            <div class="form-group">
                                <label>Tanggal Pengiriman</label>
                                <input type="text" id="sertifikat_balik_nama_tgl_pengiriman" name="sertifikat_balik_nama_tgl_pengiriman" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Dikirim Ke</label>
                                <input type="text" class="form-control" id="sertifikat_balik_nama_ke" name="sertifikat_balik_nama_ke">
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane " id="legal-pbg" aria-labelledby="legal-pbg-tab" role="tabpanel">
                        <div class="col-12">
                            <div class="divider">
                                <div class="divider-text">IMB/PBG</div>
                            </div>
                            <div class="form-group">
                                <label>No IMB/PBG</label>
                                <input type="text" class="form-control" id="pbg_no" name="pbg_no">
                            </div>
                            <div class="form-group">
                                <label>Tanggal terbit</label>
                                <input type="text" id="pbg_tanggal_terbit" name="pbg_tanggal_terbit" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tanggal Pengajuan</label>
                                <input type="text" id="pbg_tanggal_pengajuan" name="pbg_tanggal_pengajuan" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tipe</label>
                                <input type="text" id="pbg_tipe" name="pbg_tipe" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select id="pbg_status" name="pbg_status" class="form-control">
                                    <option value="">-</option>
                                    <option value="Proses">Proses</option>
                                    <option value="Selesai">Selesai</option>
                                    <option value="Terjadi Masalah">Terjadi Masalah</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Dikirim Ke</label>
                                <input type="text" class="form-control" id="pbg_dikirim_ke" name="pbg_dikirim_ke">
                            </div>
                            <div class="form-group">
                                <label>Tanggal Kirim Ke Bank/Konsumen</label>
                                <input type="text" id="pbg_tanggal_kirim" name="pbg_tanggal_kirim" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <!-- <div class="form-group">
                                <label>NJOP Tanah/Bumi</label>
                                <input type="text" id="pbg_njop_tanah" name="pbg_njop_tanah" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>NJOP Bangunan</label>
                                <input type="text" id="pbg_njop_bangunan" name="pbg_njop_bangunan" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Mutasi Pecah/Split</label>
                                <input type="text" id="pbg_mutsai_split" name="pbg_mutsai_split" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Mutasi Habis</label>
                                <input type="text" id="pbg_mutasi_habis" name="pbg_mutasi_habis" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tipe di Siteplan</label>
                                <input type="text" id="pbg_tipe_siteplan" name="pbg_tipe_siteplan" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tipe di PBG</label>
                                <input type="text" id="pbg_tipe" name="pbg_tipe" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tipe di Bangun</label>
                                <input type="text" id="pbg_tipe_dibangun" name="pbg_tipe_dibangun" class="form-control" placeholder="-" />
                            </div> -->
                        </div>
                    </div>
                    <div class="tab-pane " id="legal-bphtb" aria-labelledby="legal-bphtb-tab" role="tabpanel">
                        <div class="col-12">
                            <div class="divider">
                                <div class="divider-text">Verifikasi BPHTB</div>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Verifikasi</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="bphtb_tanggal_verifikasi" name="bphtb_tanggal_verifikasi">
                            </div>
                            <div class="form-group">
                                <label>Jatuh Tempo</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="bphtb_jatuh_tempo" name="bphtb_jatuh_tempo">
                            </div>
                            <div class="form-group">
                                <label>Tanggal Perpanjangan Jatuh Tempo</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="bphtb_perpanjang_jatuh_tempo" name="bphtb_perpanjang_jatuh_tempo">
                            </div>

                            <div class="form-group">
                                <label>Tanggal Pembayaran</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="bphtb_tanggal_pembayaran" name="bphtb_tanggal_pembayaran">
                            </div>

                            <div class="form-group">
                                <label>Nominal Yang Disetujui</label>
                                <input type="text" class="form-control " id="bphtb_nominal_disetujui" name="bphtb_nominal_disetujui">
                            </div>

                            <div class="divider">
                                <div class="divider-text">Verifikasi BPHTB</div>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Validasi</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="bphtb_tanggal_validasi" name="bphtb_tanggal_validasi">
                            </div>
                            <div class="form-group">
                                <label>Nominal Tervalidasi</label>
                                <input type="text" class="form-control " id="bphtb_nominal_tervalidasi" name="bphtb_nominal_tervalidasi">
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane " id="legal-pph" aria-labelledby="legal-pph-tab" role="tabpanel">
                        <div class="col-12">
                            <div class="divider">
                                <div class="divider-text">PPh</div>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Permohonan</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="pph_tgl_permohonan" name="pph_tgl_permohonan">
                            </div>
                            <div class="form-group">
                                <label>Nominal Divalidasi</label>
                                <input type="text" class="form-control " id="pph_nominal_validasi" name="pph_nominal_validasi">
                            </div>
                            <div class="form-group">
                                <label>Nominal Dibayar</label>
                                <input type="text" class="form-control " id="pph_nominal_bayar" name="pph_nominal_bayar">
                            </div>
                            <div class="form-group">
                                <label>Nominal Disetujui</label>
                                <input type="text" class="form-control " id="pph_nominal_disetujui" name="pph_nominal_disetujui">
                            </div>
                            <div class="form-group">
                                <label>Tanggal Validasi</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="pph_tanggal_validasi" name="pph_tanggal_validasi">
                            </div>
                            <div class="form-group">
                                <label>No SKET</label>
                                <input type="text" class="form-control" id="pph_no_sket" name="pph_no_sket">
                            </div>
                            <div class="form-group">
                                <label>Kode Verifikasi</label>
                                <input type="text" class="form-control" id="pph_kode_verifikasi" name="pph_kode_verifikasi">
                            </div>
                            <div class="form-group">
                                <label>NTPN</label>
                                <input type="text" class="form-control" id="pph_ntpn" name="pph_ntpn">
                            </div>
                            <div class="form-group">
                                <label>Tanggal Bayar</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="pph_tgl_bayar" name="pph_tgl_bayar">
                            </div>
                            <div class="form-group">
                                <label>Jenis Validasi</label>
                                <select class="form-control" id="pph_jenis_validasi" name="pph_jenis_validasi">
                                    <option value="Online">Online</option>
                                    <option value="Offline">Offline</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane " id="legal-ajb" aria-labelledby="legal-ajb-tab" role="tabpanel">
                        <div class="col-12">
                            <div class="divider">
                                <div class="divider-text">AJB</div>
                            </div>
                            <div class="form-group">
                                <label>No AJB</label>
                                <input type="text" class="form-control " id="ajb_no" name="ajb_no">
                            </div>
                            <div class="form-group">
                                <label>Tanggal AJB</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="ajb_tanggal" name="ajb_tanggal">
                            </div>
                            <div class="form-group">
                                <label>Notaris</label>
                                <input type="text" class="form-control " id="ajb_notaris" name="ajb_notaris">
                            </div>
                            <div class="form-group">
                                <label>Dikirim Ke</label>
                                <input type="text" class="form-control " id="ajb_dikirim_ke" name="ajb_dikirim_ke">
                            </div>
                            <div class="form-group">
                                <label>Tanggal Dikirim Ke Bank/Konsumen</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="ajb_tanggal_dikirim" name="ajb_tanggal_dikirim">
                            </div>
                            <div class="divider">
                                <div class="divider-text">PPJB</div>
                            </div>
                            <div class="form-group">
                                <label>No PPJB</label>
                                <input type="text" class="form-control " id="ppjb_no" name="ppjb_no">
                            </div>
                            <div class="form-group">
                                <label>Tanggal PPJB</label>
                                <input type="text" class="form-control flatpickr-human-friendly" id="ppjb_tanggal" name="ppjb_tanggal">
                            </div>
                            <div class="form-group">
                                <label>Notaris</label>
                                <input type="text" class="form-control " id="ppjb_notaris" name="ppjb_notaris">
                            </div>
                        </div>
                    </div>
                    </form>
                    <div class="tab-pane" id="upload-sertifikat" aria-labelledby="upload-sertifikat-tab" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-nowrap">No</th>
                                        <th scope="col" class="text-nowrap">Nama File</th>
                                        <th scope="col" class="text-nowrap">Keterangan</th>
                                        <th scope="col" class="text-nowrap">Link</th>
                                        <th scope="col" class="text-nowrap">Oleh</th>
                                        <th scope="col" class="text-nowrap">Tanggal Upload</th>
                                        <th scope="col" class="text-nowrap"></th>
                                    </tr>
                                </thead>
                                <tbody id="tb-fl-file">
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <form id="fl-legal">
                            <div class="row">
                                <div class="col-sm-12 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <label for="fl-kategori"> Ketgori </label>
                                        <select id="fl-kategori" class="form-control" name="fl-kategori">
                                            <option value="1">Sertifikat</option>
                                            <option value="2">AJB</option>
                                            <option value="0">Lain-lain</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="fl-file_name"> Nama File: </label>
                                        <input type="text" id="fl-file_name" name="fl-file_name" class="form-control" placeholder="Nama File" maxlength="255">
                                    </div>
                                    <div class="form-group">
                                        <label for="fl-keterangan"> Keterangan: </label>
                                        <textarea cols="40" rows="5" id="fl-keterangan" name="fl-keterangan" class="form-control" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="fl-file"> Pilih File: </label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="fl-file" accept="application/pdf" id="fl-file" />
                                            <label class="custom-file-label" id="fl-label" for="customFile">Pilih Berkas</label>
                                        </div>
                                    </div>
                                    <button type="reset" onclick="$('#fl-label').html('Pilih Berkas')" class="btn btn-outline-secondary">Reset</button>
                                    <button onclick="fl_upload()" class="btn btn-primary data-submit mr-1" id="fl-btn-upload">Unggah</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button id="add-form-btn-legal" class="btn btn-primary data-submit mr-1" onclick="save_legal()" href="javascript:void(0)">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- ################################### modal mkdt turun pembangunan ##################################### -->
<div class="modal fade " id="modals-turun_pembangunan">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="fm-turun_pembangunan" class="modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Turun Pembangunan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Proyek</label>
                            <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                            <input type="text" class="form-control" id="tp-nama_proyek" readonly name="nama_proyek" value="<?= $data['proyek']->nama_proyek ?>" placeholder="ASI" />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-post">Kavling</label>
                            <textarea class="form-control" id="tp-kavling" name="tp-kavling" rows="6" readonly placeholder="Kavling"></textarea>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label for="perintah_bangun_tgl">Tanggal Perintah Bangun</label>
                            <input type="text" readonly="readonly" id="tp-perintah_bangun_tgl" name="perintah_bangun_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                        </div>
                        <div class="form-group">
                            <label>Perintah Bangun</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" accept="application/pdf" name="perintah_bangun_file" id="tp-perintah_bangun_file" />
                                <label class="custom-file-label" id="label-perintah_bangun_file" for="label-perintah_bangun_file">Upload File Perintah Bangun</label>
                                <a href="#" target=_blank id="list-tp-upload_perintah_bangun_file" class="btn btn-outline-primary col-12">Klik untuk lihat file</a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="perintah_bangun_oleh">Oleh</label>
                            <input type="text" readonly="readonly" id="tp-perintah_bangun_oleh" name="perintah_bangun_oleh" class="form-control" placeholder="-" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">*catatan: gunakan titik koma ";" untuk pemisah nomor rumah jika akan input rumah lebih dari 1 kavling sekaligus</label>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <a id="set-tp-btn" class="btn btn-primary mr-1" onclick="set_tp()" href="javascript:void(0)">Simpan</a>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
<!-- ################################### modal mkdt set harga ##################################### -->
<div class="modal fade " id="modals-set_harga">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="fm-set_harga" class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Set Harga</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Proyek</label>
                            <input type="hidden" class="form-control" id="points" readonly name="points" value="" />
                            <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                            <input type="text" class="form-control" id="nama_proyek" readonly name="nama_proyek" value="<?= $data['proyek']->nama_proyek ?>" placeholder="ASI" />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-post">Cluster</label>
                            <select id="id_cluster" disabled name="id_cluster" class="custom-select">
                                <?php
                                foreach ($cluster as $p) {
                                    echo "<option  value='$p->id_cluster'>$p->nama_cluster</option>";
                                }
                                ?>
                            </select>
                            <!-- <input type="text" id="basic-icon-default-post" class="form-control dt-post" placeholder="Web Developer" aria-label="Web Developer" /> -->
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-post">Jalan</label>
                            <select id="id_jalan" disabled name="id_jalan" class="custom-select">
                                <?php
                                foreach ($jalan as $p) {
                                    echo "<option class='$p->nama_cluster' value='$p->id_jalan'>$p->nama_jalan</option>";
                                }
                                ?>
                            </select>
                            <!-- <input type="text" id="basic-icon-default-post" class="form-control dt-post" placeholder="Web Developer" aria-label="Web Developer" /> -->
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">No Rumah </label>
                            <input type="text" class="form-control" readonly id="no_kavling" name="no_kavling" value="" placeholder="31" />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-post">Tipe</label>
                            <select id="id_tipe" disabled name="id_tipe" class="custom-select">
                                <?php
                                foreach ($tipe as $p) {
                                    echo "<option  value='$p->id_tipe'>$p->no_tipe_rumah ($p->tipe_rumah)</option>";
                                }
                                ?>
                            </select>
                            <!-- <input type="text" id="basic-icon-default-post" class="form-control dt-post" placeholder="Web Developer" aria-label="Web Developer" /> -->
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Pricelist</label>
                            <select class="select2 custom-select sh-fm" id="sh-id" name="sh-id" value=""></select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">ROW</label>
                            <input type="text" class="form-control num sh-fm" id="sh-row" name="sh-row" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Tipe</label>
                            <input type="text" class="form-control sh-fm text-right" id="sh-tipe" name="sh-tipe" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">LB</label>
                            <input type="text" class="form-control num sh-fm" id="sh-lb" name="sh-lb" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">LT</label>
                            <input type="text" class="form-control num sh-fm" id="sh-lt" name="sh-lt" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Harga Jual</label>
                            <input type="text" class="form-control num sh-fm" id="sh-hargajual" name="sh-hargajual" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Harga Jual Net</label>
                            <input type="text" class="form-control num sh-fm" id="sh-hargajual_net" name="sh-hargajual_net" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">KPR</label>
                            <input type="text" class="form-control num sh-fm" id="sh-kpr" name="sh-kpr" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Uang Muka</label>
                            <input type="text" class="form-control num sh-fm" id="sh-uang_muka" name="sh-uang_muka" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Biaya Adm</label>
                            <input type="text" class="form-control num sh-fm" id="sh-biaya_adm" name="sh-biaya_adm" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                            <input type="text" class="form-control num sh-fm" id="sh-bphtb" name="sh-bphtb" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">PPN</label>
                            <input type="text" class="form-control num sh-fm" id="sh-ppn" name="sh-ppn" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Biaya Proses</label>
                            <input type="text" class="form-control num sh-fm" id="sh-biaya_proses" name="sh-biaya_proses" value="" readonly />
                        </div>


                    </div>
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">*catatan: gunakan titik koma ";" untuk pemisah nomor rumah jika akan input rumah lebih dari 1 kavling sekaligus</label>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <a id="set-harga-form-btn" class="btn btn-primary mr-1" onclick="set_harga()" href="javascript:void(0)">Simpan</a>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
<!--#################################### Modal Mkdt #########################################-->
<div class="modal fade" id="modal_divisi4">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <form id="fm-mkdt" enctype="multipart/form-data" class="add-new-record modal-content pt-0" autocomplete="off">
            <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button> -->
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Marketing Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_alamat" id="label_alamat4"></p>
                <hr>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="data_konsumen-tab" data-toggle="tab" href="#data_konsumen" aria-controls="data_konsumen" role="tab" aria-selected="true">Data Konsumen</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" id="detail_biaya-tab" data-toggle="tab" href="#detail_biaya" aria-controls="detail_biaya" role="tab" aria-selected="true">Detail</a>
                    </li> -->
                    <!-- <li class="nav-item">
                            <a class="nav-link" id="detail_tagihan-tab" data-toggle="tab" href="#detail_tagihan" aria-controls="detail_tagihan" role="tab" aria-selected="false">Detail Tagihan</a>
                        </li> -->
                    <li class="nav-item">
                        <a class="nav-link" id="status-tab" data-toggle="tab" href="#status" aria-controls="detail_tagihan" role="tab" aria-selected="false">Status </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane show active" id="data_konsumen" aria-labelledby="data_konsumen-tab" role="tabpanel">
                        <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                        <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                        <input type="hidden" class="form-control" id="id_konsumen" name="id_konsumen" value="" />

                        <input type="hidden" class="form-control" id="mkdt_data_baru" name="mkdt_data_baru" value="" />
                        <div class="row">
                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div id="refresh_fmmkdt_div">
                                    <button id="refresh_fmmkdt_btn" type="button" class="btn btn-outline-primary btn-block waves-effect">Tambah Konsumen Baru</button>
                                </div>
                                <div id="delete_kons_div">
                                    <button id="delete_kons_btn" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="delete_kons(false)">Hapus Konsumen</button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="divider">
                                    <div class="divider-text">Status</div>
                                </div>
                                <div class="form-group">
                                    <label for="status_kavling">Status Booking</label>
                                    <select required class="form-control" id="status_mkdt" name="status_mkdt">
                                        <option value="">-</option>
                                        <option value="Booking">Booking</option>
                                        <option value="Akad">Akad</option>
                                        <option value="Batal">Batal</option>
                                    </select>
                                </div>
                                <div id="show_keterangan_batal" class="hidden">
                                    <div class="form-group">
                                        <label for="keterangan_batal">Keterangan Batal</label>
                                        <textarea class="form-control" id="keterangan_batal" name="keterangan_batal" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <!-- <div class="form-group">
                                        <label for="harga_jual">Refund</label>
                                        <input type="text" class="form-control num" id="refund" name="refund">
                                    </div>
                                    <div class="form-group">
                                        <label for="refund_tgl">Tanggal Refund</label>
                                        <input type="text" id="refund_tgl" name="refund_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div> -->
                                </div>
                                <div class="divider">
                                    <div class="divider-text">Data Konsumen</div>
                                </div>
                                <div class="form-group">
                                    <label for="no_spptb">No SPPTB</label>
                                    <input type="text" class="form-control" id="no_spptb" name="no_spptb">
                                </div>
                                <div class="form-group">
                                    <label for="nama_konsumen">Nama Konsumen</label>
                                    <input type="text" class="form-control" id="nama_konsumen" required name="nama_konsumen">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_konsumen">Alamat Konsumen</label>
                                    <input type="text" class="form-control" id="alamat_konsumen" name="alamat_konsumen">
                                </div>
                                <div class="form-group">
                                    <label for="nik_konsumen">NIK</label>
                                    <input type="text" class="form-control" id="nik_konsumen" name="nik_konsumen">
                                </div>
                                <div class="form-group">
                                    <label for="npwp_konsumen">NPWP</label>
                                    <input type="text" class="form-control" id="npwp_konsumen" name="npwp_konsumen">
                                </div>
                                <div class="form-group">
                                    <label for="hp_konsumen">Kontak Konsumen</label>
                                    <input type="text" class="form-control" id="hp_konsumen" name="hp_konsumen">
                                </div>
                                <div class="form-group">
                                    <label for="hp_konsumen">Email Konsumen</label>
                                    <input type="text" class="form-control" id="email_konsumen" name="email_konsumen">
                                </div>
                                <div class="form-group hidden">
                                    <label for="status_kavling">Status Konsumen</label>
                                    <select class="form-control" id="status_konsumen" name="status_konsumen">
                                        <option value="">-</option>
                                        <option value="Umum">Umum</option>
                                        <option value="TWP">TWP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="divider">
                                    <div class="divider-text">Data Pasangan</div>
                                </div>
                                <div class="form-group">
                                    <label for="status_kavling">Status Pernikahan</label>
                                    <select class="form-control" id="status_pernikahan" name="status_pernikahan">
                                        <option value="Belum Kawin">Belum Kawin</option>
                                        <option value="Kawin">Kawin</option>
                                        <option value="Cerai Mati">Cerai Mati</option>
                                        <option value="Cerai Hidup">Cerai Hidup</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="nama_pasangan">Nama Pasangan</label>
                                    <input type="text" class="form-control" id="nama_pasangan" name="nama_pasangan">
                                </div>
                                <div class="form-group">
                                    <label for="hp_konsumen">NIK Pasangan</label>
                                    <input type="text" class="form-control" id="nik_pasangan" name="nik_pasangan">
                                </div>
                                <div class="divider">
                                    <div class="divider-text">Data Instansi</div>
                                </div>
                                <div class="form-group">
                                    <label for="nama_instansi">Nama Instansi</label>
                                    <input type="text" class="form-control" id="nama_instansi" name="nama_instansi">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_instansi">Alamat Instansi</label>
                                    <input type="text" class="form-control" id="alamat_instansi" name="alamat_instansi">
                                </div>
                                <div class="form-group">
                                    <label for="tel_instansi">Telepon Instansi</label>
                                    <input type="text" class="form-control" id="tel_instansi" name="tel_instansi">
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="divider">
                                    <div class="divider-text">Sales</div>
                                </div>
                                <div class="form-group">
                                    <label for="alamat_instansi">Sales</label>
                                    <input type="text" class="form-control" id="sales" name="sales">
                                </div>
                                <div class="divider">
                                    <div class="divider-text">TUNAI/KPR</div>
                                </div>
                                <div class="form-group">
                                    <label for="is_kpr">Tunai/KPR</label>
                                    <select required class="form-control" id="is_kpr" name="is_kpr">
                                        <option value="0">TUNAI</option>
                                        <option value="1">KPR</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="is_subsidi">Subsidi/Non-Subsidi</label>
                                    <select required class="form-control" id="is_subsidi" name="is_subsidi">
                                        <option value="0">Non-Subsidi</option>
                                        <option value="1">Subsidi</option>
                                    </select>
                                </div>
                                <div class="divider">
                                    <div class="divider-text">File Upload</div>
                                </div>
                                <div class="form-group">
                                    <label>KTP</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="image/*" name="file_ktp" id="file_ktp" />
                                        <label class="custom-file-label" id="label-file_ktp" for="label-file_ktp">Upload File KTP</label>
                                        <div id="list-upload_file_ktp"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>NPWP</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="image/*" name="file_npwp" id="file_npwp" />
                                        <label class="custom-file-label" id="label-file_npwp" for="label-file_npwp">Upload File NPWP</label>
                                        <div id="list-upload_file_npwp"></div>
                                    </div>
                                </div>
                                <div>
                                    <img id="file_ktp-here" src="" width="100%">
                                    <img id="file_npwp-here" src="" width="100%">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="detail_biaya" aria-labelledby="detail_biaya-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-6">


                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">


                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="status" aria-labelledby="status-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="divider">
                                    <div class="divider-text">Booking</div>
                                </div>
                                <div class="form-group">
                                    <label for="booking_tgl">Tanggal Booking</label>
                                    <input type="text" id="booking_tgl" name="booking_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="booking_fee">Booking Fee</label>
                                    <input type="text" readonly class="form-control num" id="booking_fee" name="booking_fee">
                                </div>
                                <div class="divider">
                                    <div class="divider-text">Wawancara</div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="wawancara" name="wawancara" value="1" />
                                        <label class="custom-control-label" for="wawancara">Sudah Wawancara</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="bank">Bank</label>
                                    <input type="text" id="bank" name="bank" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="wawancara_tgl">Tanggal Wawancara</label>
                                    <input type="text" id="wawancara_tgl" name="wawancara_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>


                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="divider">
                                    <div class="divider-text">SP3K</div>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" id="mkdt_keterangan" name="mkdt_keterangan">
                                        <option value="">-</option>
                                        <option value="Disetujui">Disetujui</option>
                                        <option value="Ditolak">Ditolak</option>
                                    </select>
                                    <!-- <input type="text"  class="form-control" placeholder="Disetujui/Ditolak" /> -->
                                </div>
                                <div class="form-group">
                                    <label for="bank">Pengajuan</label>
                                    <input type="text" id="harga_kpr" name="harga_kpr" class="form-control num" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="bank">Disetujui</label>
                                    <input type="text" id="acc_harga_kpr" name="acc_harga_kpr" class="form-control num" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="bank">Turun KPR</label>
                                    <input type="text" id="harga_turun_kpr" name="harga_turun_kpr" class="form-control num" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="bank">No SP3K</label>
                                    <input type="text" id="sp3k_no" name="sp3k_no" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label>SP3K</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="application/pdf" name="sp3k_file" id="sp3k_file" />
                                        <label class="custom-file-label" id="label-sp3k_file" for="label-sp3k_file">Upload File NPWP</label>
                                        <a href="" target=_blank id="list-upload_sp3k_file">Klik untuk lihat file</a>
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" class="custom-control-input" id="sp3k" name="sp3k" value="1" />
                                            <label class="custom-control-label" for="sp3k">SP3K</label>
                                        </div>
                                    </div> -->
                                <div class="form-group">
                                    <label for="sp3k_tgl">Tanggal Terbit</label>
                                    <input type="text" id="sp3k_tgl" name="sp3k_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="sp3k_tgl">Tanggal Kadaluarsa</label>
                                    <input type="text" id="sp3k_tgl_exp" name="sp3k_tgl_exp" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="divider">
                                    <div class="divider-text">Perintah Bangun</div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="perintah_bangun" name="perintah_bangun" value="1" />
                                        <label class="custom-control-label" for="perintah_bangun">Perintah Bangun</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="perintah_bangun_tgl">Tanggal Perintah Bangun</label>
                                    <input type="text" readonly="readonly" id="perintah_bangun_tgl" name="perintah_bangun_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="perintah_bangun_oleh">Oleh</label>
                                    <input type="text" readonly="readonly" id="perintah_bangun_oleh" name="perintah_bangun_oleh" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label>Perintah Bangun</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="application/pdf" name="perintah_bangun_file" id="perintah_bangun_file" />
                                        <label class="custom-file-label" id="label-perintah_bangun_file" for="label-perintah_bangun_file">Upload File Perintah Bangun</label>
                                        <a href="" target=_blank id="list-upload_perintah_bangun_file">Klik untuk lihat file</a>
                                    </div>
                                </div>
                                <div class="divider">
                                    <div class="divider-text">Akad</div>
                                </div>
                                <div class="form-group">
                                    <label for="rencana_akad_tgl">Rencana Akad</label>
                                    <input type="text" id="rencana_akad_tgl" name="rencana_akad_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="akad" name="akad" value="1" />
                                        <label class="custom-control-label" for="akad">Akad</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="akad_tgl">Tanggal Akad</label>
                                    <input type="text" id="akad_tgl" name="akad_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label>No Debitur</label>
                                    <input type="text" id="debitur_no" name="debitur_no" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label>No BAST</label>
                                    <input type="text" id="bast_no" name="bast_no" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label>BAST</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="application/pdf" name="bast_file" id="bast_file" />
                                        <label class="custom-file-label" id="label-bast_file" for="label-bast_file">Upload File BAST</label>
                                        <a href="" target=_blank id="list-upload_bast_file">Klik untuk lihat file</a>
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                        <label for="mkdt_keterangan">Keterangan</label>
                                        <textarea class="form-control" id="mkdt_keterangan" name="mkdt_keterangan" rows="3" placeholder="Keterangan"></textarea>
                                    </div> -->
                                <!-- <div class="divider">
                                    <div class="divider-text">Harga Jual</div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Tanggaal Pricelist</label>
                                    <input type="text" class="form-control text-right" id="mkdt-tgl_harga" name="mkdt-tgl_harga" value="" readonly />
                                </div>

                                <input type="hidden" class="form-control" id="mkdt-harga_akhir" name="mkdt-harga_akhir" value="" readonly />
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Harga Jual</label>
                                    <input type="text" class="form-control num" id="mkdt-hargajual" name="mkdt-hargajual" value="" readonly />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">KPR</label>
                                    <input type="text" class="form-control num" id="mkdt-kpr" name="mkdt-kpr" value="" readonly />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Uang Muka</label>
                                    <input type="text" class="form-control num" id="mkdt-uang_muka" name="mkdt-uang_muka" value="" readonly />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                                    <input type="text" class="form-control num" id="mkdt-bphtb" name="mkdt-bphtb" value="" readonly />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Biaya Adm</label>
                                    <input type="text" class="form-control num" id="mkdt-biaya_adm" name="mkdt-biaya_adm" value="" readonly />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Biaya Proses</label>
                                    <input type="text" class="form-control num" id="mkdt-biaya_proses" name="mkdt-biaya_proses" value="" readonly />
                                </div> -->
                            </div>

                        </div>

                    </div>
                </div>
                <br>
            </div>
            <div class="modal-footer">
                <button id="add-form-btn-mkdt" class="btn btn-primary data-submit mr-1" onclick="save_mkdt(this)" href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
    </div>
    </form>
</div>
<script></script>



<!--#################################### Modal Keuangan #########################################-->
<div class="modal fade text-left" id="modal_divisi3">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form id="fm-keuangan" class="add-new-record modal-content pt-0" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Keuangan</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_konsumen" id="label_konsumen"></p>
                <p class="modal-title label_alamat" id="label_alamat3"></p>
                <div class="form-group">
                    <div class="custom-control custom-switch custom-control-inline">
                        <input type="checkbox" class="custom-control-input cbp" id="is_lunas" name="is_lunas" value="1" />
                        <label class="custom-control-label" for="is_lunas">Pembayaran Lunas</label>
                    </div>
                </div>
                <hr>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="booking-tab" data-toggle="tab" href="#booking" aria-controls="home" role="tab" aria-selected="true">Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tagihan-tab" data-toggle="tab" href="#tagihan" aria-controls="home" role="tab" aria-selected="true">Uang Muka</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="bb-tab" data-toggle="tab" href="#bb" aria-controls="home" role="tab" aria-selected="true">Biaya-biaya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="log_pembayaran-tab" data-toggle="tab" href="#log_pembayaran" aria-controls="log_pembayaran" role="tab" aria-selected="false">Riwayat Pembayaran</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="booking" aria-labelledby="booking-tab" role="tabpanel">
                        <input type="hidden" class="form-control" name="status_mkdt" id="status_mkdt" value="" />
                        <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                        <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                        <input type="hidden" class="form-control" id="nama_konsumen" name="nama_konsumen" value="" />

                        <div class="col-md-6 col-sm-12 col-lg-6">
                            <div class="form-group">
                                <label for="booking_tgl">Tanggal Booking</label>
                                <input disabled type="text" id="booking_tgl" name="booking_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="harga_jual">Booking Fee</label>
                                <input readonly type="text" class="form-control num" id="booking_fee" name="booking_fee">
                            </div>

                            <hr>
                            <div class="hidden">
                                <div class="form-group">
                                    <label for="booking_fee_paid">Sudah Bayar Booking Fee</label>
                                    <select class="form-control" id="booking_fee_paid" name="booking_fee_paid">
                                        <option value="0">Belum</option>
                                        <option value="1" selected>Sudah</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="keu_booking_fee">Booking Fee</label>
                                    <input type="text" id="keu_booking_fee" name="keu_booking_fee" class="form-control num" value="" />
                                </div>
                                <div class="form-group">
                                    <label for="keu_booking_tgl">Tanggal Bayar Booking Fee</label>
                                    <input type="text" id="keu_booking_tgl" name="keu_booking_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                            <button class="add-form-btn-keuangan btn btn-primary data-submit mr-1" onclick="save_keuangan(); return false;" href="javascript:void(0)">Simpan</button>
                        </div>
                    </div>
                    <div class="tab-pane" id="tagihan" aria-labelledby="tagihan-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-lg-6">
                                <div class="divider">
                                    <div class="divider-text">Tagihan Uang Muka</div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-nowrap">No</th>
                                                <th scope="col" class="text-nowrap">Berita Acara</th>
                                                <th scope="col" class="text-nowrap">Nominal</th>
                                                <th scope="col" class="text-nowrap">Jatuh Tempo</th>
                                                <th scope="col" class="text-nowrap">Oleh</th>
                                                <th scope="col" class="text-nowrap">Sudah DIbayar </th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb-data-tagihan">
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                            <div class="col-md-3 col-sm-12 col-lg-3">
                                <div class="divider">
                                    <div class="divider-text">Total Uang Muka</div>
                                </div>
                                <div class="form-group">
                                    <label for="bt-total_biaya_um">Total Uang Muka</label>
                                    <input readonly type="text" class="form-control num" id="bt-total_biaya_um" name="bt-total_biaya_um">
                                </div>

                                <hr>
                                <div class="form-group">
                                    <label for="bt-sudah_bayar_um">Sudah Bayar Uang Muka</label>
                                    <input type="text" class="form-control num" readonly id="bt-sudah_bayar_um" name="bt-sudah_bayar_um">
                                </div>
                                <div class="form-group">
                                    <label for="bt-sisa_tagihan_um">Sisa Tagihan Uang Muka</label>
                                    <input type="text" class="form-control num" readonly id="bt-sisa_tagihan_um" name="bt-sisa_tagihan_um">
                                </div>
                                <div class="form-group">
                                    <label for="bt-persentase_bayar_tagihan_um">Persentase</label>
                                    <input type="text" class="form-control" style="text-align:right" readonly id="bt-persentase_bayar_tagihan_um" name="bt-persentase_bayar_tagihan_um">
                                </div>
                                <div id="hide_refund">
                                    <div class="divider">
                                        <div class="divider-text">Refund</div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch custom-control-inline">
                                            <input type="checkbox" class="custom-control-input cbp" id="refund_paid" name="refund_paid" value="1" />
                                            <label class="custom-control-label" for="refund_paid">Pembayaran Selesai</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="keterangan_refund">Keterangan</label>
                                        <textarea class="form-control" id="keterangan_refund" name="keterangan_refund" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="nominal_refund">Nominal</label>
                                        <input type="text" class="form-control num" id="nominal_refund" name="nominal_refund">
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_refund">Tanggal Refund</label>
                                        <input type="text" id="tanggal_refund" name="tanggal_refund" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                </div>
                                <a class="btn btn-outline-primary waves-effect col-sm-12" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                    Lihat Detail Biaya
                                </a>
                                <div class="collapse" id="collapseExample">
                                    <!-- <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Pricelist</label>
                                            <select class="select2 custom-select bt-fm" id="bt-id" name="bt-id" value=""></select>
                                        </div> -->
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">Harga Jual</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_jual" name="bt-harga_jual" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">ACC KPR</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_kpr_acc" name="bt-harga_kpr_acc" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">Diskon</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_diskon" name="bt-harga_diskon" value="" readonly />
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-3 col-sm-12 col-lg-3">
                                <div id="hide_lunas">
                                    <div class="divider">
                                        <div class="divider-text">Pembayaran</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="keterangan_refund">Untuk Pembayaran</label>
                                        <select name="bt-for" id="bt-for" class="form-control form-select"></select>
                                    </div>
                                    <div class="form-group">
                                        <label for="berita_acara">Keterangan Pembayaran</label>
                                        <textarea class="form-control" id="bt-berita_acara_um" name="bt-berita_acara_um" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="sisa_tagihan">Nominal Pembayaran</label>
                                        <input type="text" class="form-control num" id="bt-bayar_tagihan_um" name="bt-bayar_tagihan_um">
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_bayar">Tanggal Pembayaran</label>
                                        <input type="text" id="bt-tanggal_bayar_um" name="bt-tanggal_bayar_um" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                            <button class="add-form-btn-keuangan btn btn-primary data-submit mr-1" onclick="save_keuangan(); return false;" href="javascript:void(0)">Simpan</button>
                        </div>
                    </div>
                    <div class="tab-pane" id="log_pembayaran" aria-labelledby="log_pembayaran-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-lg-12">
                                <div class="divider">
                                    <div class="divider-text">Riwayat Pembayaran Uang Muka</div>
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
                            <div class="col-md-12 col-sm-12 col-lg-12">
                                <div class="divider">
                                    <div class="divider-text">Riwayat Pembayaran Biaya-biaya</div>
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
                                            <tbody id="tb-data-log_pembayaran_bb">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="bb" aria-labelledby="bb-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-lg-6">
                                <div class="divider">
                                    <div class="divider-text">Tagihan Biaya-biaya</div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-nowrap">No</th>
                                                <th scope="col" class="text-nowrap">Berita Acara</th>
                                                <th scope="col" class="text-nowrap">Nominal</th>
                                                <th scope="col" class="text-nowrap">Jatuh Tempo</th>
                                                <th scope="col" class="text-nowrap">Oleh</th>
                                                <th scope="col" class="text-nowrap">Sudah DIbayar </th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb-data-tagihan_bb">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-12 col-lg-3">

                                <div class="divider">
                                    <div class="divider-text">Total Biaya-biaya</div>
                                </div>
                                <div class="form-group">
                                    <label for="bt-total_biaya_bb">Total Biaya-biaya</label>
                                    <input readonly type="text" class="form-control num" id="bt-total_biaya_bb" name="bt-total_biaya_bb">
                                </div>

                                <hr>
                                <div class="form-group">
                                    <label for="bt-sudah_bayar_bb">Sudah Bayar Biaya-biaya</label>
                                    <input type="text" class="form-control num" readonly id="bt-sudah_bayar_bb" name="bt-sudah_bayar_bb">
                                </div>
                                <div class="form-group">
                                    <label for="bt-sisa_tagihan_um">Sisa Tagihan Biaya-biaya</label>
                                    <input type="text" class="form-control num" readonly id="bt-sisa_tagihan_bb" name="bt-sisa_tagihan_bb">
                                </div>
                                <div class="form-group">
                                    <label for="bt-persentase_bayar_tagihan_bb">Persentase</label>
                                    <input type="text" class="form-control" style="text-align:right" readonly id="bt-persentase_bayar_tagihan_bb" name="bt-persentase_bayar_tagihan_bb">
                                </div>
                                <a class="btn btn-outline-primary waves-effect col-sm-12" data-toggle="collapse" href="#collapseExampleBB" role="button" aria-expanded="false" aria-controls="collapseExampleBB">
                                    Lihat Detail Biaya
                                </a>
                                <div class="collapse" id="collapseExampleBB">
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">PPN</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_ppn" name="bt-harga_ppn" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_bphtb" name="bt-harga_bphtb" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">Biaya Adm</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_administrasi" name="bt-harga_administrasi" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">Biaya Proses</label>
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_biaya_proses" name="bt-harga_biaya_proses" value="" readonly />
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname">Penambahan Biaya</label>
                                        <input type="text" class="form-control bt-fm" id="bt-keterangan_penambahan_biaya" name="bt-keterangan_penambahan_biaya" value="" readonly />
                                        <input type="text" class="form-control num bt-fm" id="bt-harga_penambahan" name="bt-harga_penambahan" value="" readonly />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-12 col-lg-3">
                                <div id="hide_lunas">
                                    <div class="divider">
                                        <div class="divider-text">Pembayaran</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="keterangan_refund">Untuk Pembayaran</label>
                                        <select name="bt-for_bb" id="bt-for_bb" class="form-control form-select"></select>
                                    </div>
                                    <div class="form-group">
                                        <label for="berita_acara">Keterangan Pembayaran</label>
                                        <textarea class="form-control" id="bt-berita_acara_bb" name="bt-berita_acara_bb" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="sisa_tagihan">Nominal Pembayaran</label>
                                        <input type="text" class="form-control num" id="bt-bayar_tagihan_bb" name="bt-bayar_tagihan_bb">
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_bayar">Tanggal Pembayaran</label>
                                        <input type="text" id="bt-tanggal_bayar_bb" name="bt-tanggal_bayar_bb" class="form-control flatpickr-human-friendly" placeholder="-" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                            <button class="add-form-btn-keuangan btn btn-primary data-submit mr-1" onclick="save_keuangan('bb'); return false;" href="javascript:void(0)">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- ################################## Modal Isi Data Konsumen ##########################################-->
<div class="modal fade" id="modal-isi_data_konsumen">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <form id="fm-idk_keu" class="add-new-record modal-content pt-0" autocomplete="off">
            <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button> -->
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Isi Data Konsumen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_alamat" id="label_alamat4"></p>
                <hr>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="idk_data_konsumen-tab" data-toggle="tab" href="#idk_data_konsumen" aria-controls="idk_data_konsumen" role="tab" aria-selected="true">Data Konsumen</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="idk_biaya-tab" data-toggle="tab" href="#idk_biaya" aria-controls="idk_biaya" role="tab" aria-selected="true">Biaya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="idk_tagihan-tab" data-toggle="tab" href="#idk_tagihan" aria-controls="data_konsumen" role="tab" aria-selected="true">Tagihan</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane show active" id="idk_data_konsumen" aria-labelledby="idk_data_konsumen-tab" role="tabpanel">
                        <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                        <input type="hidden" class="form-control" id="idk-id_mkdt" name="id_mkdt" value="" />
                        <input type="hidden" class="form-control" id="idk-id_konsumen" name="id_konsumen" value="" />

                        <input type="hidden" class="form-control" id="idk-harga_akhir" name="idk-harga_akhir" value="" readonly />
                        <input type="hidden" class="form-control" id="idk-hargajual" name="idk-hargajual" value="" readonly />
                        <input type="hidden" class="form-control" id="idk-kpr" name="idk-kpr" value="" readonly />
                        <input type="hidden" class="form-control" id="idk-uang_muka" name="mkdt-uang_muka" value="" readonly />
                        <input type="hidden" class="form-control" id="idk-bphtb" name="idk-bphtb" value="" readonly />
                        <input type="hidden" class="form-control" id="idk-biaya_adm" name="idk-biaya_adm" value="" readonly />
                        <input type="hidden" class="form-control" id="idk-biaya_proses" name="idk-biaya_proses" value="" readonly />

                        <input type="hidden" class="form-control" id="idk_data_baru" name="mkdt_data_baru" value="" />

                        <div class="row">
                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="refresh_fmmkdt_div">
                                    <button id="refresh-btn-idk_keu" type="button" class="btn btn-outline-primary btn-block waves-effect">Tambah Konsumen Baru</button>
                                </div>
                                <div class="delete_kons_div">
                                    <button id="delete-btn-idk_keu" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="delete_kons(false)">Hapus Konsumen</button>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="divider">
                                    <div class="divider-text">Status</div>
                                </div>
                                <div class="form-group">
                                    <label for="status_kavling">Status Kavling</label>
                                    <select required class="form-control" id="idk-status_mkdt" name="dt-status_mkdt">
                                        <option value="">-</option>
                                        <option value="Booking">Booking</option>
                                        <option value="Akad">Akad</option>
                                        <option value="Batal">Batal</option>
                                    </select>
                                </div>
                                <div id="idk-show_keterangan_batal" class="hidden">
                                    <div class="form-group">
                                        <label for="keterangan_batal">Keterangan Batal</label>
                                        <textarea class="form-control" id="idk-keterangan_batal" name="dt-keterangan_batal" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="harga_jual">Nominal Pengembalian Dana</label>
                                        <input type="text" class="form-control num" id="idk-refund" name="dt-refund">
                                    </div>

                                </div>

                                <div class="divider">
                                    <div class="divider-text">Data Konsumen</div>
                                </div>
                                <div class="form-group">
                                    <label for="no_spptb">No SPPTB</label>
                                    <input type="text" class="form-control" id="idk-no_spptb" name="no_spptb">
                                </div>
                                <div class="form-group">
                                    <label for="nama_konsumen">Nama Konsumen</label>
                                    <input type="text" class="form-control" id="idk-nama_konsumen" required name="nama_konsumen">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_konsumen">Alamat Konsumen</label>
                                    <input type="text" class="form-control" id="idk-alamat_konsumen" name="alamat_konsumen">
                                </div>
                                <div class="form-group">
                                    <label for="nik_konsumen">NIK</label>
                                    <input type="text" class="form-control" id="idk-nik_konsumen" name="nik_konsumen">
                                </div>
                                <div class="form-group">
                                    <label for="npwp_konsumen">NPWP</label>
                                    <input type="text" class="form-control" id="idk-npwp_konsumen" name="npwp_konsumen">
                                </div>
                                <div class="form-group">
                                    <label for="hp_konsumen">Kontak Konsumen</label>
                                    <input type="text" class="form-control" id="idk-hp_konsumen" name="hp_konsumen">
                                </div>
                                <div class="form-group">
                                    <label for="hp_konsumen">Email Konsumen</label>
                                    <input type="text" class="form-control" id="idk-email_konsumen" name="email_konsumen">
                                </div>
                                <div class="form-group hidden">
                                    <label for="status_kavling">Status Konsumen</label>
                                    <select class="form-control" id="idk-status_konsumen" name="status_konsumen">
                                        <option value="">-</option>
                                        <option value="Umum">Umum</option>
                                        <option value="TWP">TWP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="divider">
                                    <div class="divider-text">Data Pasangan</div>
                                </div>
                                <div class="form-group">
                                    <label for="status_kavling">Status Pernikahan</label>
                                    <select class="form-control" id="idk-status_pernikahan" name="status_pernikahan">
                                        <option value="Belum Kawin">Belum Kawin</option>
                                        <option value="Kawin">Kawin</option>
                                        <option value="Cerai Mati">Cerai Mati</option>
                                        <option value="Cerai Hidup">Cerai Hidup</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="nama_pasangan">Nama Pasangan</label>
                                    <input type="text" class="form-control" id="idk-nama_pasangan" name="nama_pasangan">
                                </div>
                                <div class="form-group">
                                    <label for="hp_konsumen">NIK Pasangan</label>
                                    <input type="text" class="form-control" id="idk-nik_pasangan" name="nik_pasangan">
                                </div>
                                <div class="divider">
                                    <div class="divider-text">Data Instansi</div>
                                </div>
                                <div class="form-group">
                                    <label for="nama_instansi">Nama Instansi</label>
                                    <input type="text" class="form-control" id="idk-nama_instansi" name="nama_instansi">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_instansi">Alamat Instansi</label>
                                    <input type="text" class="form-control" id="idk-alamat_instansi" name="alamat_instansi">
                                </div>
                                <div class="form-group">
                                    <label for="tel_instansi">Telepon Instansi</label>
                                    <input type="text" class="form-control" id="idk-tel_instansi" name="tel_instansi">
                                </div>

                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-4">
                                <div class="divider">
                                    <div class="divider-text">Sales</div>
                                </div>
                                <div class="form-group">
                                    <label for="alamat_instansi">Sales</label>
                                    <input type="text" class="form-control" id="idk-sales" name="sales">
                                </div>
                                <div class="divider">
                                    <div class="divider-text">TUNAI/KPR</div>
                                </div>
                                <div class="form-group">
                                    <label for="is_kpr">Tunai/KPR</label>
                                    <select required class="form-control" id="idk-is_kpr" name="is_kpr" onchange="sum_mktotal()">
                                        <option value="0">TUNAI/CASH KERAS</option>
                                        <option value="2">TUNAI/CASH BERTAHAP</option>
                                        <option value="1">KPR</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="is_subsidi">Subsidi/Non-Subsidi</label>
                                    <select required class="form-control" id="idk-is_subsidi" name="is_subsidi">
                                        <option value="0">Non-Subsidi</option>
                                        <option value="1">Subsidi</option>
                                    </select>
                                </div>
                                <div class="divider">
                                    <div class="divider-text">File Upload</div>
                                </div>
                                <div class="form-group">
                                    <label>KTP</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="image/*" name="idk-file_ktp" id="idk-file_ktp" />
                                        <label class="custom-file-label" id="label-idk-file_ktp" for="label-idk-file_ktp">Upload File KTP</label>
                                        <div id="list_upload_komplain_sales"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>NPWP</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="image/*" name="idk-file_npwp" id="idk-file_npwp" />
                                        <label class="custom-file-label" id="label-idk-file_npwp" for="label-idk-file_npwp">Upload File NPWP</label>
                                        <div id="list_upload_komplain_sales"></div>
                                    </div>
                                </div>
                                <div>
                                    <img id="idk-file_ktp-here" src="<?= base_url("/images/not_found.png") ?>" width="100%">
                                    <img id="idk-file_npwp-here" src="<?= base_url("/images/not_found.png") ?>" width="100%">
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane" id="idk_biaya" aria-labelledby="idk_biaya-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class="divider">
                                    <div class="divider-text">Harga Jual</div>
                                </div>
                                <!-- <input type="hidden" name="mk-id_mkdt" id="mk-id_mkdt"> -->
                                <!-- <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">No Tipe</label>
                                    <input readonly class="form-control mk-fm" id="mk-no_tipe" name="mk-text_hargajual" value="">
                                </div> -->
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Tanggal PriceList</label>
                                    <input type="text" class="form-control text-right mk-fm flatpickr-human-friendly" id="mk-tgl_harga" name="mk-tgl_harga" value="" readonly />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Harga Jual</label>
                                    <input type="text" class="form-control num mk-fm" id="mk-hargajual" name="mk-hargajual" value="" />
                                </div>
                                <div class="form-group" id="hjdis">
                                    <label class="form-label" for="basic-icon-default-fullname">Diskon Harga Jual</label>
                                    <input type="text" class="form-control num mk-fm" id="mk-diskon_harga_jual" name="mk-diskon_harga_jual" value="" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Harga Jual Net</label>
                                    <input type="text" class="form-control num mk-fm" id="mk-hargajual_net" name="mk-hargajual_net" value="" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">KPR</label>
                                    <input type="text" class="form-control num mk-fm" id="mk-kpr" name="mk-kpr" value="" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Uang Muka</label>
                                    <input type="text" class="form-control num mk-fm" id="mk-uang_muka" name="mk-uang_muka" value="" />
                                </div>
                                <div class="form-group" id="umdis">
                                    <label class="form-label" for="basic-icon-default-fullname">Diskon Uang Muka</label>
                                    <input type="text" class="form-control num mk-fm" id="mk-diskon_uang_muka" name="mk-diskon_uang_muka" value="" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Biaya Adm</label>
                                    <input type="text" class="form-control num mk-fm" id="mk-biaya_adm" name="mk-biaya_adm" value="" />
                                </div>
                                <div class="form-group">
                                    <label for="total_biaya2">PPN</label>
                                    <input type="text" class="form-control num totalbb" id="mk-ppn" name="mk-ppn">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                                    <input type="text" class="form-control num mk-fm totalbb" id="mk-bphtb" name="mk-bphtb" value="" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Biaya Proses</label>
                                    <input type="text" class="form-control num mk-fm totalbb" id="mk-biaya_proses" name="mk-biaya_proses" value="" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">ROW</label>
                                    <input type="text" class="form-control num mk-fm" id="mk-row" name="mk-row" value="" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">Tipe</label>
                                    <input type="text" class="form-control mk-fm text-right" id="mk-tipe" name="mk-tipe" value="" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">LB</label>
                                    <input type="text" class="form-control num mk-fm" id="mk-lb" name="mk-lb" value="" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="basic-icon-default-fullname">LT</label>
                                    <input type="text" class="form-control num mk-fm" id="mk-lt" name="mk-lt" value="" />
                                </div>


                            </div>
                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class="divider">
                                    <div class="divider-text">Booking</div>
                                </div>
                                <div class="form-group">
                                    <label for="booking_tgl">Tanggal Booking</label>
                                    <input type="text" id="idk-booking_tgl" name="dt-booking_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="harga_jual">Booking Fee</label>
                                    <input type="text" class="form-control num" id="idk-booking_fee" name="dt-booking_fee">
                                </div>
                                <div class="divider">
                                    <div class="divider-text">KPR</div>
                                </div>
                                <div class="form-group">
                                    <label for="total_biaya2">KPR Disetujui</label>
                                    <input readonly type="text" class="form-control num" id="mk-harga_kpr_acc" name="mk-harga_kpr_acc">
                                </div>
                                <div class="form-group">
                                    <label for="total_biaya2">Turun KPR</label>
                                    <input readonly type="text" class="form-control num" id="mk-harga_penambahan_um" name="mk-harga_penambahan_um">
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <!-- <div class="divider">
                                    <div class="divider-text">Potongan</div>
                                </div>
                                <div class="form-group">
                                    <label for="total_biaya2">Diskon</label>
                                    <select class="form-control" name="mk-jenis-diskon" id="mk-jenis-diskon" onchange="sum_mktotal()">
                                        <option value="">-</option>
                                        <option value="Uang Muka">Uang Muka</option>
                                        <option value="Harga Jual">Harga Jual</option>
                                    </select>
                                </div> -->
                                <!-- <div class="form-group">
                                    <label for="total_biaya2">Nominal</label>
                                    <input type="text" class="form-control num" id="mk-diskon" name="mk-diskon">
                                </div> -->
                                <div class="divider">
                                    <div class="divider-text">Penambahan Biaya</div>
                                </div>
                                <div class="form-group">
                                    <label for="total_biaya2">Penambahan Biaya</label>
                                    <input type="text" class="form-control num" id="mk-harga_penambahan" name="mk-harga_penambahan">
                                </div>
                                <div class="form-group">
                                    <label for="total_biaya2">Keterangan Penambahan Biaya</label>
                                    <textarea name="mk-keterangan_harga_penambahan" id="mk-keterangan_harga_penambahan" class="form-control mk-fm" cols="30" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class="divider">
                                    <div class="divider-text">Total Biaya</div>
                                </div>
                                <div class="form-group">
                                    <label>Total Uang Muka + Biaya ADM</label>
                                    <input readonly type="text" class="form-control num tum" id="mk-tum" name="mk-tum">
                                </div>
                                <div class="form-group">
                                    <label>Total Biaya-Biaya</label>
                                    <input readonly type="text" class="form-control num tbb" id="mk-tbb" name="mk-tbb">
                                </div>
                            </div>


                        </div>

                    </div>
                    <div class="tab-pane" id="idk_tagihan" aria-labelledby="idk_tagihan-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="table-responsive">
                                    <table id="list_kendaraan" class="table">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Keterangan</th>
                                                <th>Jatuh Tempo</th>
                                                <th>Nominal</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="list_cicilan_here">
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak Ada Data</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!-- <button class="btn btn-sm btn-primary" onclick="addRow()">Tambah Baris</button> -->

                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6 col-lg-6">
                                        <div class="divider">
                                            <div class="divider-text">Tagihan UM</div>
                                        </div>
                                        <div class="form-group">
                                            <label for="mk-total_um">Total Uang Muka</label>
                                            <input readonly type="text" class="form-control num tum" id="mk-total_um" name="mk-total_um">
                                        </div>
                                        <!-- <div class="form-group">
                                        <label for="total_cicilan">Total Cicilan UM</label> -->
                                        <input readonly type="hidden" class="form-control num" id="total_cicilan_um" name="total_cicilan_um">
                                        <!-- </div> -->
                                        <input name="id_list_keu" id="id_list_keu" class="form-control" type="hidden">
                                        <input name="id_keuangan" id="id_keuangan" class="form-control" type="hidden">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <input required name="berita_acara" id="berita_acara" class="form-control" type="text">
                                            <span class="help-block"></span>
                                        </div>
                                        <div class="form-group">
                                            <label>Nominal</label>
                                            <input required name="nominal" id="nominal" onchange="sum_tg(this.value)" class="form-control num tg" type="text">
                                            <span class="help-block"></span>
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Jatuh Tempo</label>
                                            <input required name="jatuh_tempo_tgl" id="jatuh_tempo_tgl" class="form-control flatpickr-human-friendly" type="date">
                                            <span class="help-block"></span>
                                        </div>
                                        <div id="cicilan_belong_here"></div>
                                        <button id="tambah_list" type="button" class="btn btn-outline-primary btn-block waves-effect" onclick="tambah_()">+ Tagihan Uang Muka</button>
                                        <!-- <button id="hapus_list" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="hapus()">+ Hapus List</button> -->
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-6 ">
                                        <div class="divider">
                                            <div class="divider-text">Tagihan Biaya-biaya</div>
                                        </div>
                                        <div class="form-group">
                                            <label for="mk-total_bb">Total Biaya-biaya</label>
                                            <input readonly type="text" class="form-control num tbb" id="mk-total_bb" name="mk-total_bb">
                                        </div>
                                        <!-- <div class="form-group">
                                        <label for="total_cicilan">Total Cicilan UM</label> -->
                                        <input readonly type="hidden" class="form-control num" id="total_cicilan_bb" name="total_cicilan_bb">
                                        <!-- </div> -->
                                        <input name="id_list_keu_bb" id="id_list_keu_bb" class="form-control" type="hidden">
                                        <input name="id_keuangan_bb" id="id_keuangan_bb" class="form-control" type="hidden">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <input required name="berita_acara_bb" id="berita_acara_bb" class="form-control" type="text">
                                            <span class="help-block"></span>
                                        </div>
                                        <div class="form-group">
                                            <label>Nominal</label>
                                            <input required name="nominal_bb" id="nominal_bb" onchange="sum_tg(this.value, '_bb')" class="form-control num tg" type="text">
                                            <span class="help-block"></span>
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Jatuh Tempo</label>
                                            <input required name="jatuh_tempo_tgl_bb" id="jatuh_tempo_tgl_bb" class="form-control flatpickr-human-friendly" type="date">
                                            <span class="help-block"></span>
                                        </div>
                                        <button id="tambah_list_bb" type="button" class="btn btn-outline-primary btn-block waves-effect" onclick="tambah_('_bb')">+ Tagihan Biaya-biaya</button>
                                        <!-- <button id="hapus_list" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="hapus()">+ Hapus List</button> -->
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                <br>
            </div>
            <div class="modal-footer">
                <button id="add-form-btn-idk_keu" class="btn btn-primary data-submit mr-1" onclick="simpan_dt_konsumen_keuangan(this)" href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
    </div>
    </form>
</div>

<!-- ################################## Modal Tagihan ##########################################-->
<div class="modal fade text-left" id="print_tagihan_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel17" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Print Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="col-xl-12 col-md-12 col-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="form_list_inv-tab" data-toggle="tab" href="#form_list_inv" aria-controls="form_list_inv" role="tab" aria-selected="true">List Invoice</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="form_add_inv-tab" data-toggle="tab" href="#form_add_inv" aria-controls="form_add_inv" role="tab" aria-selected="true">Tambah Invoice</a>
                    </li>
                </ul>
                <div class="tab-content">

                    <div class="tab-pane active" id="form_list_inv" aria-labelledby="form_list_inv-tab" role="tabpanel">
                        <div class="card invoice-preview-card">
                            <div class="card-body invoice-padding pb-0">
                                <div class="row">
                                    <div class="table-responsive">
                                        <table class="table mb-0" id="tbl-tagihan">
                                            <thead>
                                                <tr>
                                                    <th>No Invoice</th>
                                                    <th>Tanggal Terbit</th>
                                                    <th>Tanggal Kadaluarsa</th>
                                                    <th>Oleh</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="list_inv-here"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane " id="form_add_inv" aria-labelledby="form_add_inv-tab" role="tabpanel">
                        <div class="card invoice-preview-card">
                            <div class="card-body invoice-padding pb-0">
                                <div class="d-flex justify-content-between flex-md-row flex-column invoice-spacing mt-0">
                                    <div class="col-md-4">
                                        <select class="select2 custom-select " id="kopsurat" name="kopsurat"></select>
                                        <!-- <div class="logo-wrapper" id="pc-logo_perusahaan"></div>
                                        <p class="card-text mb-25" id="pc-alamat_perusahaan">Office 149, 450 South Brand Brooklyn</p>
                                        <p class="card-text mb-25" id="pc-website_perusahaan">San Diego County, CA 91905, USA</p>
                                        <p class="card-text mb-0" id="pc-kontak_perusahaan">+1 (123) 456 7891, +44 (876) 543 2198</p> -->

                                    </div>
                                    <div class="invoice-number-date mt-md-0 mt-4">
                                        <div class="">
                                            <h4 class="invoice-title">No Invoice</h4>
                                            <div class="input-group input-group-merge invoice-edit-input-group">
                                                <input id="no_sruat" name="no_sruat" type="text" class="form-control invoice-edit-input" placeholder="53634">
                                            </div>
                                        </div>
                                        <div class="">
                                            <span class="title">Tanggal:</span>
                                            <input type="text" id="tanggal_surat_tagihan" name="tanggal_surat_tagihan" class="form-control flatpickr-human-friendly" placeholder="-">
                                        </div>
                                        <div class="">
                                            <span class="title">Tenggat Waktu:</span>
                                            <input type="text" id="pt-tanggal_jatuh_tempo" name="pt-tanggal_jatuh_tempo" class="form-control flatpickr-human-friendly" placeholder="-">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Header starts -->
                            <!-- <div class="card-body invoice-padding pb-0">
                        <div class="form-group">
                            <label for="no_sruat">No Surat</label>
                            <input type="text" class="form-control" id="no_sruat" name="no_sruat">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_surat_tagihan">Tanggal Surat Tagihan</label>
                            <input type="text" id="tanggal_surat_tagihan" name="tanggal_surat_tagihan" class="form-control flatpickr-human-friendly" placeholder="-" />
                        </div>
                    </div> -->
                            <!-- Header ends -->

                            <hr class="invoice-spacing" />

                            <!-- Address and Contact starts -->
                            <div class="card-body invoice-padding pt-0">
                                <div class="invoice-spacing  row">
                                    <div class="col-xl-6 p-0">
                                        <h6 class="mb-2">Ditagihkan Ke:</h6>
                                        <h6 class="hidden" id="pt_id_konsumen"></h6>
                                        <h6 class="mb-25" id="pt_detail_konsumen"></h6>
                                        <!-- <p class="card-text mb-25" id="pt_hp_konsumen"></p> -->
                                    </div>
                                    <div class="col-xl-6 p-0">
                                        <h6 class="mb-2">Perumahan</h6>
                                        <h6 class="hidden" id="pt_id_kavling"></h6>
                                        <h6 class="hidden" id="pt_id_mkdt"></h6>
                                        <h6 class="mb-25" id="pt_detail_kavling"></h6>
                                        <!-- <p class="card-text mb-25" id="pt_hp_konsumen"></p> -->
                                    </div>
                                </div>
                            </div>
                            <!-- Address and Contact ends -->

                            <!-- Product Details starts -->
                            <div class="card-body invoice-padding invoice-product-details">
                                <form class="source-item">
                                    <div data-repeater-list="group-a">
                                        <div class="repeater-wrapper" data-repeater-item>
                                            <div class="row">
                                                <div class="table-responsive">
                                                    <table class="table mb-0" id="tbl-tagihan">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" class="text-nowrap">No</th>
                                                                <th scope="col" class="text-nowrap">Berita Acara</th>
                                                                <th scope="col" class="text-nowrap">Jatuh Tempo</th>
                                                                <!-- <th scope="col" class="text-nowrap">Sudah Dibayar</th> -->
                                                                <th scope="col" class="text-nowrap">Nominal</th>
                                                                <!-- <th scope="col" class="text-nowrap">Masukan Dalam Surat</th> -->
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tb-print-data-tagihan">
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Product Details ends -->
                            <hr class="invoice-spacing mt-0" />

                            <div class="card-body invoice-padding py-0">
                                <!-- Invoice Note starts -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-2">
                                            <label for="note" class="form-label font-weight-bold">Syarat & Ketentuan:</label>
                                            <textarea class="form-control" rows="5" id="snk"><ol><li><span style="font-size: 1rem; letter-spacing: 0.01rem;">Lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari denda&nbsp;</span></li><li><span style="font-size: 1rem; letter-spacing: 0.01rem;">Pembayaran yang sah hanya melalui transfer ke rekening atas nama <br><b>PT. Sanggarindah Karya Sentosa</b> <b>Raya</b> BCA KC Setiabudi - Bandung, Nomor Rekening :<b>2337 887 887</b>&nbsp;</span></li><li>Konfirmasi pembayaran ke bagian keuangan kami dan lampirkan bukti transfer.</li></ol></textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- Invoice Note ends -->

                            </div>
                            <div class="modal-footer">
                                <button id="form_add_inv-btn" class="btn btn-primary data-submit mr-1" onclick="save_inv()" href="javascript:void(0)">Simpan Invoice</button>
                                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ################################## Dana Akad ##########################################-->
<div class="modal fade text-left" id="dana_akad_modal" tabindex="-1" role="dialog" aria-labelledby="dana_akad_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form id="fm-dana_akad" class="add-new-record modal-content pt-0" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Dana Akad</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body flex-grow-1">
                    <p class="modal-title label_konsumen" id="label_konsumen"></p>
                    <p class="modal-title label_alamat" id="label_alamat3"></p>
                    <hr>

                    <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                    <input type="hidden" class="form-control" id="id_dana_cair" name="id_dana_cair" value="" />
                    <div class="form-group">
                        <label for="nominal_dana_akad">Nominal</label>
                        <input type="text" id="nominal_dana_akad" name="nominal_dana_akad" class="form-control num" />
                    </div>
                    <div class="form-group">
                        <label for="tgl_cair">Tanggal Rencana Cair</label>
                        <input type="text" id="tgl_rencana_cair" name="tgl_rencana_cair" class="form-control flatpickr-human-friendly" placeholder="-" />
                    </div>
                    <div class="form-group">
                        <label for="berita_acara">Keterangan</label>
                        <textarea class="form-control" id="keterangan_dana_jaminan" name="keterangan_dana_jaminan" rows="3" placeholder="Keterangan Dana Jaminan"></textarea>
                    </div>
                    <hr>
                    <div class="form-group">
                        <div class="custom-control custom-switch custom-control-inline">
                            <input type="checkbox" class="custom-control-input cbp" id="dana_akad_cair" name="dana_akad_cair" value="1" />
                            <label class="custom-control-label" for="dana_akad_cair">Sudah Cair</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tgl_cair">Tanggal Cair</label>
                        <input type="text" id="tgl_cair" name="tgl_cair" class="form-control flatpickr-human-friendly" placeholder="-" />
                    </div>

                    <button id="add-form-btn-dana_akad" class="btn btn-primary data-submit mr-1" onclick="save_dana_akad(); return false;" href="javascript:void(0)">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                </div>

            </div>
        </form>
    </div>

</div>

<script>
    // function addRow() {
    //         const table = document.getElementById('list_kendaraan').getElementsByTagName('tbody')[0];
    //         const newRow = table.insertRow();

    //         // Create cells
    //         const keteranganCell = newRow.insertCell(0);
    //         const jatuhTempoCell = newRow.insertCell(1);
    //         const nominalCell = newRow.insertCell(2);
    //         const actionCell = newRow.insertCell(3);

    //         // Set cells to be editable
    //         keteranganCell.innerHTML = '<span class="editable" onclick="editCell(this)">Keterangan</span>';
    //         jatuhTempoCell.innerHTML = '<span class="editable" onclick="editCell(this)">Jatuh Tempo</span>';
    //         nominalCell.innerHTML = '<span class="editable" onclick="editCell(this)">Nominal</span>';
    //         actionCell.innerHTML = '<button onclick="deleteRow(this)">Hapus</button>';
    //     }

    //     function editCell(element) {
    //         const cell = element.parentNode;
    //         const currentValue = element.innerText;
    //         cell.innerHTML = `<input type="text" value="${currentValue}" onblur="saveCell(this)">`;
    //         cell.firstChild.focus();
    //     }

    //     function saveCell(input) {
    //         const cell = input.parentNode;
    //         const newValue = input.value;
    //         cell.innerHTML = `<span class="editable" onclick="editCell(this)">${newValue}</span>`;
    //     }

    //     function deleteRow(button) {
    //         const row = button.parentNode.parentNode;
    //         row.parentNode.removeChild(row);
    //     }
</script>

<!-- ################################## Dana Akad ##########################################-->
<div class="modal fade text-left" id="modal_divisi10" tabindex="-1" role="dialog" aria-labelledby="dana_akad_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <form id="fm-pajak" class="add-new-record modal-content pt-0" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Pajak</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body flex-grow-1">
                    <p class="modal-title label_konsumen" id="label_konsumen"></p>
                    <p class="modal-title label_alamat" id="label_alamat3"></p>
                    <hr>

                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                    <input type="hidden" class="form-control" id="id" name="id" value="" />

                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="fp-pph42-tab" data-toggle="tab" href="#fp-pph42" aria-controls="data_konsumen" role="tab" aria-selected="true">PPh4(2)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="fp-ppn-tab" data-toggle="tab" href="#fp-ppn" aria-controls="fp-ppn" role="tab" aria-selected="true">PPn</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="fp-ppnjk-tab" data-toggle="tab" href="#fp-ppnjk" aria-controls="fp-ppnjk" role="tab" aria-selected="false">PPn Jasa Konstruksi</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="fp-pph42" aria-labelledby="fp-pph42-tab" role="tabpanel">
                            <div class="form-group">
                                <label for="bank">Kewajiban Pajak (PPh4(2))</label>
                                <input type="text" id="pph42_kewajiban_pajak" name="pph42_kewajiban_pajak" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="bank">Dasar Pengenaan Pajak (PPh4(2))</label>
                                <input type="text" id="pph42_dpp" name="pph42_dpp" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="bank">Tarif (PPh4(2))</label>
                                <input type="text" id="pph42_tarif" name="pph42_tarif" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="bank">Nilai (PPh4(2))</label>
                                <input type="text" id="pph42_nilai" name="pph42_nilai" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="bank">Tanggal Bayar (PPh4(2))</label>
                                <input type="text" id="pph42_tgl_bayar" name="pph42_tgl_bayar" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="bank">NTPN (PPh4(2))</label>
                                <input type="text" id="pph42_ntpn" name="pph42_ntpn" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="mkdt_keterangan">Keterangan</label>
                                <textarea class="form-control" id="pph42_keterangan" name="pph42_keterangan" rows="3" placeholder="Keterangan"></textarea>
                            </div>
                        </div>
                        <div class="tab-pane" id="fp-ppn" aria-labelledby="fp-ppn-tab" role="tabpanel">
                            <div class="form-group">
                                <label for="bank">Kewajiban Pajak (PPN)</label>
                                <input type="text" id="ppn_kewajiban_pajak" name="ppn_kewajiban_pajak" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="bank">Dasar Pengenaan Pajak (PPN)</label>
                                <input type="text" id="ppn_dpp" name="ppn_dpp" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="bank">Tarif (PPN)</label>
                                <input type="text" id="ppn_tarif" name="ppn_tarif" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="bank">Nilai (PPN)</label>
                                <input type="text" id="ppn_nilai" name="ppn_nilai" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="bank">Tanggal Bayar (PPN)</label>
                                <input type="text" id="ppn_tgl_bayar" name="ppn_tgl_bayar" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="bank">NTPN (PPN)</label>
                                <input type="text" id="ppn_ntpn" name="ppn_ntpn" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label for="mkdt_keterangan">Keterangan (PPN)</label>
                                <textarea class="form-control" id="ppn_keterangan" name="ppn_keterangan" rows="3" placeholder="Keterangan"></textarea>
                            </div>
                        </div>
                        <div class="tab-pane" id="fp-ppnjk" aria-labelledby="fp-ppnjk-tab" role="tabpanel">
                            <div class="form-group">
                                <label>Kewajiban Pajak (PPN Jasa Konstruksi)</label>
                                <input type="text" id="ppnjk_kewajiban_pajak" name="ppnjk_kewajiban_pajak" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Dasar Pengenaan Pajak (PPN Jasa Konstruksi)</label>
                                <input type="text" id="ppnjk_dpp" name="ppnjk_dpp" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tarif (PPN Jasa Konstruksi)</label>
                                <input type="text" id="ppnjk_tarif" name="ppnjk_tarif" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Nilai (PPN Jasa Konstruksi)</label>
                                <input type="text" id="ppnjk_nilai" name="ppnjk_nilai" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Tanggal Bayar (PPN Jasa Konstruksi)</label>
                                <input type="text" id="ppnjk_tgl_bayar" name="ppnjk_tgl_bayar" class="form-control flatpickr-human-friendly" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>NTPN (PPN Jasa Konstruksi)</label>
                                <input type="text" id="ppnjk_ntpn" name="ppnjk_ntpn" class="form-control" placeholder="-" />
                            </div>
                            <div class="form-group">
                                <label>Keterangan (PPN Jasa Konstruksi)</label>
                                <textarea class="form-control" id="ppnjk_keterangan" name="ppnjk_keterangan" rows="3" placeholder="Keterangan"></textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button id="add-form-btn-pajak" class="btn btn-primary data-submit mr-1" onclick="save_(); return false;" href="javascript:void(0)">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                </div>

            </div>
        </form>
    </div>

</div>
<script></script>

<!-- modal detail kavling -->
<div class="modal fade" id="modal_detail">
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
</div>

<!-- <div class="modal fade" id="modal_detail">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form id="fm-detail" class="modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Detail Kavling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body">
               
            </div>
            <div class="modal-footer">

            </div>
            <div class="col-xl-12 col-lg-12">

            </div>
        </form>
    </div>
</div> -->

<!-- END: Content-->
<div class="modal fade text-left" id="modal-batal" tabindex="-1" role="dialog" aria-labelledby="modal-batal" aria-hidden="true">
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
                                <input disabled type="text" class="form-control" id="batal-no_spptb" name="batal-no_spptb">
                            </div>
                            <div class="form-group">
                                <label>Nama Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-nama_konsumen" required name="batal-nama_konsumen">
                            </div>
                            <div class="form-group">
                                <label>Alamat Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-alamat_konsumen" name="batal-alamat_konsumen">
                            </div>
                            <div class="form-group">
                                <label>NIK</label>
                                <input disabled type="text" class="form-control" id="batal-nik_konsumen" name="batal-nik_konsumen">
                            </div>
                            <div class="form-group">
                                <label>NPWP</label>
                                <input disabled type="text" class="form-control" id="batal-npwp_konsumen" name="batal-npwp_konsumen">
                            </div>
                            <div class="form-group">
                                <label>Kontak Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-hp_konsumen" name="batal-hp_konsumen">
                            </div>
                            <div class="form-group">
                                <label>Email Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-email_konsumen" name="batal-email_konsumen">
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
                                <input disabled type="text" class="form-control" id="batal-sales" required name="batal-sales">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <input readonly type="hidden" class="form-control" id="batal-id_konsumen" name="batal-id_konsumen">
                            <input readonly type="hidden" class="form-control" id="batal-id_mkdt" name="batal-id_mkdt">
                            <input readonly type="hidden" class="form-control" id="batal-id_kavling" name="batal-id_kavling">
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
                                <input readonly type="text" class="form-control num" id="batal-harga_kpr_acc" name="batal-harga_kpr_acc">
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">Turun KPR</label>
                                <input readonly type="text" class="form-control num" id="batal-harga_penambahan_um" name="batal-harga_penambahan_um">
                            </div>
                            <div class="divider">
                                <div class="divider-text">Penambahan Biaya</div>
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">Penambahan Biaya</label>
                                <input disabled type="text" class="form-control num totalbb" id="batal-harga_penambahan" name="batal-harga_penambahan">
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">Keterangan Penambahan Biaya</label>
                                <textarea disabled name="batal-keterangan_penambahan_biaya" id="batal-keterangan_penambahan_biaya" class="form-control batal-fm" cols="30" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="divider">
                                <div class="divider-text">Total Uang Muka</div>
                            </div>
                            <div class="form-group">
                                <label for="batal-total_biaya_um">Total Uang Muka</label>
                                <input readonly type="text" class="form-control num" id="batal-total_biaya_um" name="batal-total_biaya_um">
                            </div>

                            <hr>
                            <div class="form-group">
                                <label for="batal-sudah_bayar_um">Sudah Bayar Uang Muka</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sudah_bayar_um" name="batal-sudah_bayar_um">
                            </div>
                            <div class="form-group">
                                <label for="batal-sisa_tagihan_um">Sisa Tagihan Uang Muka</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sisa_tagihan_um" name="batal-sisa_tagihan_um">
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
                                <input readonly type="text" class="form-control num" id="batal-total_biaya_bb" name="batal-total_biaya_bb">
                            </div>

                            <hr>
                            <div class="form-group">
                                <label for="batal-sudah_bayar_bb">Sudah Bayar Biaya-biaya</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sudah_bayar_bb" name="batal-sudah_bayar_bb">
                            </div>
                            <div class="form-group">
                                <label for="batal-sisa_tagihan_um">Sisa Tagihan Biaya-biaya</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sisa_tagihan_bb" name="batal-sisa_tagihan_bb">
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
                                <textarea class="form-control" id="batal-keterangan_batal" name="batal-keterangan_batal" rows="3" placeholder="Keterangan"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Surat Batal</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" accept="application/pdf" name="file_surat_batal" id="file_surat_batal" />
                                    <label class="custom-file-label" id="label-file_surat_batal" for="label-file_surat_batal">Upload Surat Batal</label>
                                    <a href="" target=_blank id="list-file_surat_batal">klik untuk melihat surat batal</a>
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
    let data_um = [],
        data_bb = []
    //sewwtalert2 fix error cant type after open modal
    $.fn.modal.Constructor.prototype._enforceFocus = function() {};

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
    imageObj.src = base_url + '/' + dt_proyek.siteplan;

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
        load_kavling(true);
        load_menu();
        $("#tambah_jalan").change(function(e) {
            hapus_seleksi()
        })

        // scaling layer to fit stage
        let konva_w = parseFloat($("#konva-holder").width())
        let konva_h = parseFloat($("#konva-holder").height())
        let l
        if (konva_w > konva_h)
            l = parseFloat($("#konva-holder").width()) / imageObj.width;
        else
            l = parseFloat($("#konva-holder").height()) / imageObj.height;

        $('#filter-side').css('height', konva_h)


        stage.scale({
            x: l,
            y: l
        });

        group.scale({
            x: 1 / l,
            y: 1 / l
        })

        if ($(window).width() < 768) {
            $("#div-filter").appendTo(
                $("#modal-filter")
            )
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
    });

    function load_menu() {
        // let va = $("#pilih-divisi option:selected").val();
        $.ajax({
            url: base_url + '/home/getMenuBtn',
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
            },
            error: function() {
                $("#loading").addClass("hidden");

            }
        })
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

        $.each(filterwarna, function(i, v) {
            div += `
            <div class="divider">
                <div class="divider-text">${i}</div>
            </div>`;

            $.each(v, function(x, y) {
                // console.log(x,y)
                kv = (x == "Def") ? "Data yang bisa diolah" : x;

                div += `<div class="form-group row">
                            <div class="btn col-2 ml-1" style="background-color:${y}"></div>
                            <div class="col-9"> ${kv} (${filterwarnahitung[x]})</div>
                        </div>`;
            })
        })
        $("#keterangan-warna-here").html(div)
    }
    //load shape kavling
    function load_kavling(refresh = false) {
        hapus_seleksi()
        filterwarna = {
            Status: null,
            Subsidi: null,
            Komersil: null,
            'Lain-lain': null
        };
        filterwarnahitung = {};
        var d = siteplan.find('Line');
        // if (refresh == false) {
        //     d.forEach(d_ => {
        //         d.attrs.data
        //     });
        //     return;
        // }

        // remove all available kavling and others

        d.forEach(d_ => {
            d_.destroy();
        });
        // zzzzz

        // siteplan.children = [];

        let va = $("#pilih-divisi option:selected").val();
        //load kavling
        $.ajax({
            url: base_url + '/siteplan/get_kavling_all',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek,
                id_cluster: filter.id_cluster,
                id_jalan: filter.id_jalan,
                id_role: va
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(result) {
                $("#loading").addClass("hidden");
                csrfHash = result.token;
                stroke = ""
                fill = ""
                strokeWidth = ""
                dashed = ""

                let r = result['data'],
                    data2,
                    hit,
                    tp_rumah;


                for (var p = 0; p < r.length; p++) {
                    tp_rumah = r[p].tipe_rumah
                    no_tp_rumah = r[p].no_tipe_rumah

                    //set default shape color 
                    // set_fill("#fff67a", "#000000", 0, null)
                    hit = {
                        fill: set_fill2("Def"),
                        tipe: 'Lain-lain'
                    }
                    if (r[p].harga_akhir) {
                        tp_rumah = r[p].hj_tipe_rumah
                        no_tp_rumah = r[p].hj_no_tipe_rumah
                        hit = set_fill2(r[p].hj_tipe_rumah)
                        hit = {
                            fill: hit,
                            tipe: (r[p].is_subsidi == 1) ? "Subsidi" : "Komersil"
                        }
                        // if (r[p].is_subsidi == 1)
                        //     hit = set_fill2("Subsidi")
                        // else
                        //     hit = set_fill2("Non-Subsidi")
                    }
                    if (r[p].status_mkdt) {
                        hit = set_fill2(r[p].status_mkdt)
                    } else {
                        if (r[p].progres_bangunan == "100") {
                            // jika sudah selesai
                            hit = set_fill2("Ready Stock")
                        }
                    }

                    if (va == 3) { //keuangan
                        // if (r[p].is_kpr == 1){
                        //     hit = set_fill2('Subsidi')
                        // }else if (r[p].is_kpr == 0){
                        //     hit = set_fill2('Non-Subsidi')
                        // }
                        // if (r[p].harga_akhir) {
                        //     if (r[p].is_subsidi == 1)
                        //         hit = set_fill2("Subsidi")
                        //     else
                        //         hit = set_fill2("Non-Subsidi")
                        // }

                        // if (r[p].status_mkdt == "Booking")
                        // hit = set_fill2(r[p].status_mkdt)
                        // if (r[p].status_mkdt == "Akad")
                        //     hit = set_fill2('Akad')
                        // if (r[p].status_mkdt == "Batal")
                        // hit = set_fill2('Batal')


                        if (r[p].is_lunas == 0 || r[p].is_lunas == null || r[p].is_lunas == "undefined") {
                            // if (r[p].status_mkdt == "Batal")
                            //     hit = set_fill2('Batal')

                            if (r[p].jatuh_tempo_tgl != null && r[p].jatuh_tempo_tgl != "0000-00-00") {
                                if (daysBetween(today_date, r[p].jatuh_tempo_tgl) < 7)
                                    hit = set_fill2('Jatuh Tempo')
                            }
                        } else if (r[p].is_lunas == 1) {
                            hit = set_fill2('Lunas')
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
                    } else if (va == 7) { //produksi


                        if (r[p].progres_bangunan == "100") {
                            // jika sudah selesai
                            hit = set_fill2("Ready Stock") // warna merah
                        } else if (parseInt(r[p].progres_bangunan) > 0) {
                            hit = set_fill2("Pembangunan") // warna merah
                        }

                        if (r[p].status_mkdt)
                            hit = set_fill2(r[p].status_mkdt)

                        if (r[p].perintah_bangun == 1)
                            hit = set_fill2("Perintah Bangun")

                        // console.LOG(r[p])
                        // hit = set_fill2(r[p].status_mkdt)


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

                    } else if (va == 9) {
                        if (r[p].harga_akhir) {
                            tp_rumah = r[p].hj_tipe_rumah
                            no_tp_rumah = r[p].hj_no_tipe_rumah
                            hit = set_fill2(r[p].hj_tipe_rumah)

                            //ubah var hit ke object
                            hit = {
                                fill: hit,
                                tipe: (r[p].is_subsidi == 1) ? "Subsidi" : "Komersil"
                            }
                            // if (r[p].is_subsidi == 1)
                            //     hit = set_fill2("Subsidi")
                            // else
                            //     hit = set_fill2("Non-Subsidi")
                        }
                    }
                    // else {
                    //     if (r[p].harga_akhir) {
                    //         if (r[p].is_subsidi == 1)
                    //             hit = set_fill2("Subsidi")
                    //         else
                    //             hit = set_fill2("Non-Subsidi")
                    //     }
                    //     if (r[p].status_mkdt)
                    //         hit = set_fill2(r[p].status_mkdt)

                    // }


                    //harga jual
                    let id_hargajual = r[p].harga_akhir;
                    r[p].harga_akhir = (r[p].hargajual) ? num_format(r[p].hargajual) + "(" + format_date(r[p].tgl_harga) + ")" : '-';

                    //total biaya
                    // let ktotal_biaya = (r[p].total_biaya == "undefined")?0:r[p].total_biaya

                    //sudah bayar
                    // let ksudah_bayar = (r[p].sudah_bayar)?0:r[p].sudah_bayar

                    //cek jika var hit itu objek

                    if (typeof hit !== 'object') {
                        hit = {
                            fill: hit,
                            tipe: 'Status'
                        }
                    }

                    //set untuk filter warna
                    filterwarna[hit.tipe] = {
                        ...filterwarna[hit.tipe],
                        [hit.fill]: conf[hit.fill].fill
                    }

                    hitung_kavling(hit)
                    //data di tiap kavling harus disesuaikan dengan divisi yang dipilih
                    kav = new Konva.Line({
                        points: JSON.parse("[" + r[p].points + "]"),
                        // lineCap: 'round',
                        // lineJoin: 'round',
                        // stroke: stroke,
                        fill: fill,
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
            },
            error: function() {
                Swal.fire({
                    position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan",
                    showConfirmButton: false,
                    //timer: 1500
                })
                return;
            }
        });

        //load jalan fasos rth
        $.ajax({
            url: base_url + '/siteplan/get_others',
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
                    position: 'bottom-end',
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
            if (roleid == 1) {
                if (addMode == true && role == 6 || addMode == true && role == 9 || addMode == true && role == 3) {
                    if (sh.data.tipe != "kavling") {
                        Swal.fire({
                            position: 'bottom-end',
                            icon: 'error',
                            title: "Terjadi Kesalahan.",
                            text: "Multiple Selection hanya untuk data kavling ",
                            showConfirmButton: false,
                        });
                        return;
                    }
                    if (editdtt[0].data.tipe != 'kavling') {
                        Swal.fire({
                            position: 'bottom-end',
                            icon: 'error',
                            title: "Terjadi Kesalahan.",
                            text: "Multiple Selection hanya untuk data kavling ",
                            showConfirmButton: false,
                        });
                        return;
                    }
                    editdtt.push(sh)
                    drawBorderEdit(sh)
                } else {
                    hapus_seleksi();
                    editdtt.push(sh)
                    drawBorderEdit(sh)
                }
            } else if (roleid == 4) {
                if (addMode == true) {
                    editdtt.push(sh)
                    drawBorderEdit(sh)
                } else {
                    hapus_seleksi();
                    editdtt.push(sh)
                    drawBorderEdit(sh)
                }

            } else {
                if (addMode == true && roleid == 6 || addMode == true && roleid == 9 || addMode == true && role == 4) {
                    editdtt.push(sh)
                    drawBorderEdit(sh)
                } else {
                    hapus_seleksi();
                    editdtt.push(sh)
                    drawBorderEdit(sh)
                }
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
                position: 'bottom-end',
                icon: 'error',
                title: "Terjadi Kesalahan.",
                text: "Tidak ada kavling yang dipilih",
                showConfirmButton: false,
            });
            return;
        }
        $("#last_update_legal, #last_update_mkdt, #last_update_keuangan, #last_update_prod").html("Terakhir dipudate oleh: -, pada: - ");

        var sh = editdtt[0],
            id_kavling = sh.id.substr(3);

        $("#fm-detail")[0].reset();
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
            url: base_url + '/siteplan/get_others',
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
                    position: 'bottom-end',
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

    function detail_kavling(sh, id_kavling) {
        $("#fm-detail :input").prop("disabled", true)
        $.ajax({
            url: base_url + '/siteplan/get_detail',
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
                let mkdt = r.mkdt,
                    pl = r.pricelist;

                //load mkdt data
                if (mkdt) {
                    if (mkdt.username)
                        $("#last_update_mkdt").html("Terakhir diupdate oleh: " + mkdt.username + " pada: " + format_datetime(mkdt.updated_at));

                    for (let i in mkdt) {
                        $("#dt-" + i).val(mkdt[i]);
                    }
                    $("#dt-tgl_harga").val(format_date(mkdt.tgl_harga))

                    if (mkdt.booking_tgl != "0000-00-00")
                        document.querySelector("#dt-booking_tgl")._flatpickr.setDate(mkdt.booking_tgl);
                    if (mkdt.wawancara_tgl != "0000-00-00")
                        document.querySelector("#dt-wawancara_tgl")._flatpickr.setDate(mkdt.wawancara_tgl);
                    if (mkdt.sp3k_tgl != "0000-00-00")
                        document.querySelector("#dt-sp3k_tgl")._flatpickr.setDate(mkdt.sp3k_tgl);
                    if (mkdt.rencana_akad_tgl != "0000-00-00")
                        document.querySelector("#dt-rencana_akad_tgl")._flatpickr.setDate(mkdt.rencana_akad_tgl);
                    if (mkdt.akad_tgl != "0000-00-00")
                        document.querySelector("#dt-akad_tgl")._flatpickr.setDate(mkdt.akad_tgl);


                    // $(".num").keyup().change();
                    // lihat_total();
                    //load bast
                    src = not_found
                    if (mkdt.bast_file != null) {
                        src = mkdt.bast_file
                    }
                    $("#dt-bast_file").prop("href", base_url + "/" + src)

                    src = not_found
                    //load sp3k
                    if (mkdt.sp3k_file != null) {
                        src = mkdt.sp3k_file
                    }
                    $("#dt-sp3k_file").prop("href", base_url + "/" + src)

                    src = not_found
                    //load perintah_bangun
                    if (mkdt.perintah_bangun_file != null) {
                        src = mkdt.perintah_bangun_file
                    }
                    $("#dt-perintah_bangun_file").prop("href", base_url + "/" + src)

                    $("#dt-mkdt_keterangan").val(mkdt.keterangan);
                }

                if (pl) {
                    for (let i in pl) {
                        $("#pl-" + i).val(pl[i]);
                    }
                }

                /************************ load table tagihan ***************************/
                r.total_um = parseFloat(r.total_um)
                r.sb_um = parseFloat(r.sb_um)
                let sisa_um = r.total_um - r.sb_um,
                    ldp = (sisa_um == 0) ? 100 : r.sb_um / r.total_um * 100;
                ldp = (r.sb_um > 0) ? ~~ldp + "%" : "0%";

                $("#dt-total_biaya_um").val(r.total_um)
                $("#dt-sudah_bayar_um").val(r.sb_um)
                $("#dt-sisa_tagihan_um").val(sisa_um)
                $("#dt-persentase_bayar_tagihan_um").val(ldp)

                r.total_bb = parseFloat(r.total_bb)
                r.sb_bb = parseFloat(r.sb_bb)
                let sisa_bb = r.total_bb - r.sb_bb,
                    ldp_bb = (sisa_bb == 0) ? 100 : r.sb_bb / r.total_bb * 100;
                ldp_bb = (r.sb_bb > 0) ? ~~ldp_bb + "%" : "0%";

                $("#dt-total_biaya_bb").val(r.total_bb)
                $("#dt-sudah_bayar_bb").val(r.sb_bb)
                $("#dt-sisa_tagihan_bb").val(sisa_bb)
                $("#dt-persentase_bayar_tagihan_bb").val(ldp_bb)

                if (r.ku)
                    $("#last_update_keuangan").html("Terakhir diupdate oleh: " + r.ku.username + " pada: " + format_datetime(r.ku.created_at));
                /************************ end of load table tagihan ***************************/

                //load legal
                let leg = r.legal;
                if (leg) {
                    // console.log(leg)
                    if (leg.username)
                        $("#last_update_legal").html("Terakhir diupdate oleh: " + leg.username + " pada: " + format_datetime(leg.updated_at));

                    for (let i in leg) {
                        $("#dt-" + i).val(leg[i]);
                    }

                    if (leg.sertifikat_tgl != "0000-00-00")
                        document.querySelector("#dt-sertifikat_tgl")._flatpickr.setDate(leg.sertifikat_tgl);
                    if (leg.sertifikat_masa_berlaku != "0000-00-00")
                        document.querySelector("#dt-sertifikat_masa_berlaku")._flatpickr.setDate(leg.sertifikat_masa_berlaku);
                    if (leg.imb_tgl != "0000-00-00")
                        document.querySelector("#dt-imb_tgl")._flatpickr.setDate(leg.imb_tgl);
                    if (leg.bphtb_tgl != "0000-00-00")
                        document.querySelector("#dt-bphtb_tgl")._flatpickr.setDate(leg.bphtb_tgl);
                    if (leg.bphtb_masa_berlaku != "0000-00-00")
                        document.querySelector("#dt-bphtb_masa_berlaku")._flatpickr.setDate(leg.bphtb_masa_berlaku);
                    if (leg.bphtb_validasi != "0000-00-00")
                        document.querySelector("#dt-bphtb_validasi")._flatpickr.setDate(leg.bphtb_validasi);
                    // if (leg.akad_tgl != "0000-00-00")
                    //     document.querySelector("#dt-legal_akad_tgl")._flatpickr.setDate(leg.akad_tgl);

                    $("#dt-legal_keterangan").val(leg.keterangan);

                }

                //load produksi
                let prod = r.produksi;
                if (prod) {
                    pondasi = 0, topping_off = 0, naik_dinding = 0, finishing = 0, slo = 0, bp = 0, jalan = 0, lpa = 0, tot = 0, saluran = 0;
                    if (prod.username)
                        $("#last_update_produksi").html("Terakhir diupdate oleh: " + prod.username + " pada: " + format_datetime(prod.updated_at));

                    pondasi = (prod.pondasi == 1) ? 1 : 0;
                    $("#dt-pondasi").prop('checked', pondasi).change();
                    topping_off = (prod.topping_off == 1) ? 1 : 0;
                    $("#dt-topping_off").prop('checked', topping_off).change();
                    naik_dinding = (prod.naik_dinding == 1) ? 1 : 0;
                    $("#dt-naik_dinding").prop('checked', naik_dinding).change();
                    finishing = (prod.finishing == 1) ? 1 : 0;
                    $("#dt-finishing").prop('checked', finishing).change();

                    jalan = (prod.jalan == 1) ? 1 : 0;
                    $("#dt-jalan").prop('checked', jalan).change();
                    slo = (prod.slo == 1) ? 1 : 0;
                    $("#dt-slo").prop('checked', slo).change();
                    bp = (prod.bp == 1) ? 1 : 0;
                    $("#dt-bp").prop('checked', bp).change();
                    lpa = (prod.lpa == 1) ? 1 : 0;
                    $("#dt-lpa").prop('checked', lpa).change();
                    saluran = (prod.saluran == 1) ? 1 : 0;
                    $("#dt-saluran").prop('checked', saluran).change();

                    $("#dt-progres_bangunan").val(prod.progres_bangunan);
                    $("#dt-t_progres_bangunan").html(prod.progres_bangunan);
                    $("#dt-produksi_keterangan").val(prod.keterangan);
                }

                $(".num").keyup().change();

                $(".label_alamat").html("<?= $data['proyek']->nama_proyek ?> <br/>" +
                    sh.data.nama_jalan +
                    ", No." + sh.data.no_kavling +
                    "<br/>" + sh.data2.no_tipe_rumah +
                    " (" + sh.data2.tipe_rumah + ")<br/>" +
                    " Harga Jual: Rp. " + sh.data2.harga_akhir + "<br/>" +
                    "(" + format_date(sh.data2.harga_akhir_tgl) + " - " + sh.data2.harga_akhir_oleh + ")");

                $("#modal_detail").modal('show');
            },
            error: function() {
                Swal.fire({
                    position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan",
                    showConfirmButton: false,
                    //timer: 1500
                })
                return;
            }
        });
    }

    function isi_data() {
        if (editdtt.length == 0) {
            Swal.fire({
                position: 'bottom-end',
                icon: 'error',
                title: "Terjadi Kesalahan.",
                text: "Tidak ada kavling yang dipilih",
                showConfirmButton: false,
            });
            return;
        }

        //bug isi data with addmode
        if (editdtt.length > 1) {
            Swal.fire({
                position: 'bottom-end',
                icon: 'error',
                title: "Terjadi Kesalahan.",
                text: "Jangan pilih kavling lebih dari 1",
                showConfirmButton: false,
            });
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
                Swal.fire({
                    position: 'bottom-end',
                    icon: 'error',
                    title: "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
                    showConfirmButton: false,
                    //timer: 1500
                })
                return;
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
            Swal.fire({
                position: 'bottom-end',
                icon: 'error',
                title: "Tidak bisa menambahkan lagi form ",
                showConfirmButton: false,
                //timer: 1500
            });
            return false;
        } else {
            if (!$("#berita_acara" + e).val() || !$("#nominal" + e).val() || !$("#jatuh_tempo_tgl" + e).val()) {
                Swal.fire({
                    position: 'bottom-end',
                    icon: 'error',
                    title: "Berita acara, nominal dan jatuh tempo tidak boleh kosong",
                    showConfirmButton: false,
                    //timer: 1500
                });
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

        var d = new Date(
            $("#jatuh_tempo_tgl" + e).val()
        ).fp_incr(30);

        fp.setDate(d);

        it += 1;
    }

    function removeFromTable(x, y = null) {
        // if (y == '_bb') {
        //     if (data_bb[x].id_keuangan != '' || data_bb[x].id_keuangan != 'null') {
        //         return Swal.fire({
        //             //position: 'bottom-end',
        //             icon: 'error',
        //             title: "tidak dapat menghapus tagihan",
        //             showConfirmButton: true,
        //             // //timer: 1500
        //         })
        //     }
        // } else {
        //     if (data_um[x].id_keuangan != '' || data_um[x].id_keuangan != 'null') {
        //         return Swal.fire({
        //             //position: 'bottom-end',
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
                    url: base_url + '/Keuangan/isSudahBayar/' + editdtt[0].data.id_mkdt,
                    type: 'get',
                    dataType: 'json',
                    success: function(r) {
                        csrfHash = r.token;

                        if (r.success === false) {
                            Swal.fire({
                                //position: 'bottom-end',
                                icon: 'error',
                                title: r.messages,
                                showConfirmButton: false,
                                // timer: 1500
                            })
                            return;
                        }

                        if (y == '_bb') delete data_bb[x];
                        else delete data_um[x];
                        tambah_ketagihan();
                    },
                    error: function() {
                        return Swal.fire({
                            //position: 'bottom-end',
                            icon: 'error',
                            title: "terjadi kesalahan",
                            showConfirmButton: false,

                        })
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
</script>

<script>
    

/********************************* keuangan *******************************************/
$("#bt-bayar_tagihan_um").change(function () {
    let s = removeComma($("#bt-sisa_tagihan_um").val()),
        b = removeComma(this.value);
    if (b > s)
        $("#bt-bayar_tagihan_um").val(s).keyup();
    else
        $("#bt-bayar_tagihan_um").val(b).keyup();
});

$("#snk").richText()
$("#kopsurat").select2({
    placeholder: "Pilih Kop Surat",
    allowClear: true,
    ajax: {
        url: base_url + "/Home/getKop",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function (params) {
            return {
                [csrfName]: csrfHash,
                search: params.term,
            };
        },
        processResults: function (r) {
            csrfHash = r.token

            let results = [];
            $.each(r.data, function (k, v) {
                results.push({
                    id: v.id,
                    text: v.nama + " (" + v.ukuran + ")",
                    lokasi: v.lokasi,
                    ukuran: v.ukuran,
                    mt: v.mt,
                    mb: v.mb,
                    ml: v.ml,
                    mr: v.mr
                });
            });

            return {
                results: results
            };
        },
        cache: false
    },
})
function print_tagihan() {

    $("#pt_id_mkdt").html("")
    $("#pt_id_konsumen").html("")
    $("#pt_id_kavling").html("")
    $("#pt_detail_konsumen").html("")
    $("#pt_detail_kavling").html("")
    $("#list_inv-here").html("")

    $("#cp_telp").html("")
    $("#tb-print-data-tagihan").html("");


    $('.nav-tabs a[href="#form_list_inv"]').tab('show');

    document.querySelector("#tanggal_surat_tagihan")._flatpickr.setDate(new Date().toDateInputValue());
    document.querySelector("#pt-tanggal_jatuh_tempo")._flatpickr.setDate(new Date().fp_incr(7));

    let role,
        sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    if (editdtt.length == 0) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Terjadi Kesalahan.",
            text: "Tidak ada kavling yang dipilih",
            showConfirmButton: false,
        });
        return;
    } else if (!sh.data.id_mkdt) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }

    $.ajax({
        url: base_url + '/keuangan/get_tagihan/inv',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_keuangan: sh.data.id_keuangan,
            id_kavling: id_kavling,
            id_mkdt: sh.data.id_mkdt
        },
        dataType: 'json',
        success: function (r) {
            let kons = r.detail,
                lt = r.list_tagihan
            csrfHash = r.token;

            if (!lt.length) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Tagihan tidak ditemukan",
                    text: "Isi tagihan terlebih dahulu",
                    showConfirmButton: false,
                })
                return;
            }

            if (r.invoice.length) {
                let tb = ""
                $.each(r.invoice, function (i, v) {
                    tb += "<tr>"
                    tb += "<td>" + v.no_inv + "</td> " +
                        "<td>" + format_date(v.tanggal_invoice) + "</td> " +
                        "<td>" + format_date(v.tanggal_jatuh_tempo) + "</td> " +
                        "<td>" + v.uadd_by + " <br>" + format_date(v.date_add) + "</td> " +
                        `<td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="print_inv('` + v.no_inv + `')"><i class="fa fa-print"></i></button>
                                    </div>
                                </td> `
                    tb += "</tr>"
                });

                $("#list_inv-here").append(tb)
            } else {
                $("#list_inv-here").html("<tr><td colspan=5>Tidak ada data</td></tr>");
            }


            //load company profile detail
            // $("#cp_nama_perusahaan").html(r.compro.nama_perusahaan)
            // $("#cp_alamat_perusahaan").html(r.compro.alamat)
            // $("#cp_telp").html(r.compro.telp + " - " + r.compro.telp2)

            //load konsumen detail
            $("#pt_id_mkdt").html(kons.id_mkdt)
            $("#pt_id_konsumen").html(kons.id_konsumen)
            $("#pt_id_kavling").html(kons.id_kavling)
            $("#pt_detail_konsumen").html(
                kons.nama_konsumen + " (" + kons.hp_konsumen + ")" +
                "<br>" + kons.alamat_konsumen
            )
            $("#pt_detail_kavling").html(
                dt_proyek.nama_proyek + "<br>" + sh.data.nama_jalan + " No. " + sh.data.no_kavling
            )
            // $("#pt_hp_konsumen").html(kons.hp_konsumen)


            /************************ load table tagihan ***************************/
            let tr_tg = "",
                no = 1,
                tot_tg = 0,
                sb_button = "",
                chkd = "",
                tg = r.list_tagihan,
                sudah_bayar = (r.sudah_bayar) ? r.sudah_bayar : 0;


            $.each(tg, function (i, v) {
                chkd = (v.sudah_dibayar == 1) ? "checked" : ""
                sb_button = `<div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" ` + chkd + ` onchange="save_sb(` + v.id_keuangan + `)" class="custom-control-input " disabled id="sb_btn` + v.id_keuangan + `" name="sb_btn[` + v.id_keuangan + `]" value="1" />
                                        <label class="custom-control-label" for="sb_btn` + v.id_keuangan + `"></label>
                                    </div>
                                </div>`;

                tot_tg += parseInt(v.nominal);
                tr_tg += "<tr>" +
                    "<td>" + no + "</td>" +
                    "<td>" + v.berita_acara + "</td>" +
                    "<td>" + format_date(v.jatuh_tempo_tgl) + "</td>" +
                    // "<td>" + sb_button + "</td>" +
                    "<td style='text-align:right'>" + num_format(v.nominal) + "</td>" +
                    "<tr>";
                no++;
            })

            tr_tg += "<tr>" +
                "<th colspan='3' style='text-align:right'>Total Tagihan</th>" +
                "<th style='text-align:right'>" + num_format(tot_tg) + "</th>" +
                "<tr>";

            tr_tg += "<tr>" +
                "<th colspan='3' style='text-align:right'>Sudah Bayar</th>" +
                "<th style='text-align:right'>" + num_format(sudah_bayar) + "</th>" +
                "<tr>";
            tr_tg += "<tr>" +
                "<th colspan='3' style='text-align:right'>Sisa</th>" +
                "<th style='text-align:right'>" + num_format(tot_tg - parseInt(sudah_bayar)) + "</th>" +
                "<tr>";

            $("#tb-print-data-tagihan").append(tr_tg);

            $("#print_tagihan_modal").modal({
                backdrop: 'static',
                keyboard: false,
            })
        },
        error: function () {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
            })
        }
    })
}
function save_inv() {
    $.ajax({
        url: base_url + '/keuangan/save_inv',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            no_inv: $("#no_sruat").val(),
            id_konsumen: $("#pt_id_konsumen").html(),
            id_mkdt: $("#pt_id_mkdt").html(),
            id_kavling: $("#pt_id_kavling").html(),
            id_kopsurat: $("#kopsurat").val(),
            tanggal_invoice: $("#tanggal_surat_tagihan").val(),
            tanggal_jatuh_tempo: $("#pt-tanggal_jatuh_tempo").val(),
            tagihan: $("#tb-print-data-tagihan").html(),
            terms: $("#snk").val(),
        },
        dataType: 'json',
        beforeSend: function () {
            $("#loading").removeClass("hidden");
            $('#form_add_inv-btn').html('Menyimpan');
            $('#form_add_inv-btn').prop("disabled", true);
        },
        success: function (r) {
            csrfHash = r.token;
            $("#loading").addClass("hidden");
            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: r.messages,
                    showConfirmButton: false,
                }).then(function () {
                    print_tagihan()
                    // $('.nav-tabs a[href="#form_list_inv"]').tab('show');
                    $('#form_add_inv-btn').html('Simpan');
                    $('#form_add_inv-btn').prop("disabled", false);
                })
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                }).then(function () {
                    $('#form_add_inv-btn').html('Simpan');
                    $('#form_add_inv-btn').prop("disabled", false);
                })
            }
        }, error: function () {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan",
                showConfirmButton: true,
                // timer: 1500
            }).then(function () {
                $('#form_add_inv-btn').html('Simpan');
                $('#form_add_inv-btn').prop("disabled", false);
            })
        }
    });
}

function print_inv(e) {
    var myWindow = window.open(base_url + "/keuangan/print_tagihan/?id=" + e, "_blank", "top=100,left=300,width=700,height=600");
    setTimeout(function () {
        myWindow.focus();
    }, 1000);
}
function doPrint() {
    (async () => {
        const rawResponse = await fetch(base_url + '/keuangan/doPrint', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                [csrfName]: csrfHash,
                konsumen: $("#pt_nama_konsumen").html(),
                alamat: $("#pt_alamat_konsumen").html(),
                no_sruat: $("#no_sruat").val(),
                tanggal_surat_tagihan: $("#tanggal_surat_tagihan").val(),
                table: $("#tb-print-data-tagihan").html()
            })
        })
            .then(resp => resp.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;

                // the filename you want
                a.download = 'Tagihan ' + $("#pt_nama_konsumen").html() + " " + $("#tanggal_surat_tagihan").val() + '.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);

            })
            .catch(() => alert('oh no!'));

    })();
}

//if pelunasan lebih dari sisa tagihan pelunasan diisi sisa tagihan
$("#bt-bayar_tagihan_um").change(function () {
    if (parseFloat(removeComma(this.value)) > parseFloat(removeComma($("#bt-sisa_tagihan_um").val())))
        $("#bt-bayar_tagihan_um").val($("#bt-sisa_tagihan_um").val())
})

//simpan status sudah bayar
function save_sb(id) {
    let i = ($("#sb_btn" + id).prop("checked")) ? 1 : 0;
    $.ajax({
        url: base_url + '/keuangan/save_sb',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_keuangan: id,
            sb: i
        },
        dataType: 'json',
        beforeSend: function () {
            $("#loading").removeClass("hidden");
        },
        success: function (r) {
            csrfHash = r.token;
            $("#loading").addClass("hidden");
        }
    });
}
$("#mk-id").select2({
    placeholder: "Pilih Pricelist",
    allowClear: true,
    ajax: {
        url: base_url + "/Hargajual/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function (params) {
            return {
                [csrfName]: csrfHash,
                search: params.term,
                id_proyek: dt_proyek.id_proyek
            };
        },
        processResults: function (r) {
            csrfHash = r.token

            let results = [];
            $.each(r.data, function (k, v) {
                results.push({
                    id: v.id,
                    text: "Rp. " + num_format(v.hargajual) + " (" + v.tipe_rumah + ")" + ": Per " + format_date(v.tgl_harga),
                    row: v.row,
                    tipe: v.tipe_rumah,
                    lb: v.lb,
                    lt: v.lt,
                    hargajual: v.hargajual,
                    kpr: v.kpr,
                    uang_muka: v.uang_muka,
                    bphtb: v.bphtb,
                    biaya_adm: v.biaya_adm,
                    biaya_proses: v.biaya_proses,
                    id_tipe: v.id_tipe,
                    tgl_harga: format_date(v.tgl_harga)
                });
            });

            return {
                results: results
            };
        },
        cache: false
    },
})
$("#mk-id").on("select2:selecting", function (e) {
    // if (Object.keys(data_um).length > 0 || Object.keys(data_bb).length > 0) {
    //     Swal.fire({
    //         title: 'Lakukan perubahan?',
    //         text: "data pada tabel tagihan akan terhapus!",
    //         type: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#3085d6',
    //         cancelButtonColor: '#d33',
    //         confirmButtonText: 'Ya!',
    //         confirmButtonClass: 'btn btn-primary',
    //         cancelButtonClass: 'btn btn-danger ml-1',
    //         buttonsStyling: !1
    //     }).then(function (t) {
    //         if (t.value) {
    //             var i = e.params.args.data
    //             $.each(i, function (k, v) {
    //                 $("#mk-" + k).val(v).change().keyup()
    //             })
    //             sum_mktotal()

    //             data_um = {}
    //             data_bb = {}
    //             $("#list_cicilan_here").html("")
    //             $("#total_cicilan_um").val(0).change().keyup()
    //             $("#total_cicilan_bb").val(0).change().keyup()
    //             $("#id_list_keu").val('');
    //             $("#id_list_keu_bb").val('');
    //         } else
    //             return false
    //     })
    // } else {
    //     var i = e.params.args.data
    //     $.each(i, function (k, v) {
    //         $("#mk-" + k).val(v).change().keyup()
    //     })
    //     sum_mktotal()
    // }

});
$("#mk-id").change(function () {
    if (!this.value)
        $(".mk-fm").val("")
})
$("#mk-harga_ppn, #mk-harga_penambahan, #mk-diskon").on('focusin', function () {
    $(this).data('val', $(this).val());
})
$("#mk-harga_ppn, #mk-harga_penambahan, #mk-diskon").change(function () {
    var prev = $(this).data('val'),
        current = $(this).val(),
        th = $(this);

    if (Object.keys(data_um).length > 0 || Object.keys(data_bb).length > 0) {
        Swal.fire({
            title: 'Lakukan perubahan?',
            text: "data pada tabel tagihan akan terhapus!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya!',
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-danger ml-1',
            buttonsStyling: !1
        }).then(function (t) {
            if (t.isConfirmed) {
                sum_mktotal()
                data_um = {}
                data_bb = {}
                $("#list_cicilan_here").html("")
                $("#total_cicilan_um").val(0).change().keyup()
                $("#total_cicilan_bb").val(0).change().keyup()
                $("#id_list_keu").val('');
                $("#id_list_keu_bb").val('');
            } else
                th.val(prev)
        })
    } else
        sum_mktotal()
})
$(".mk-fm").change(function(){
    sum_mktotal()
})
function sum_mktotal() {
    let totalum = 0,
        totalbb = 0,
        diskon_um = parseFloat(removeComma($("#mk-diskon_uang_muka").val()) || 0),
        hj = parseFloat(removeComma($("#mk-hargajual").val()) || 0),
        hj_net = parseFloat(removeComma($("#mk-hargajual_net").val()) || 0),
        hj_real = parseFloat(removeComma($("#mk-hargajual").val()) || 0),
        kpr = parseFloat(removeComma($("#mk-kpr").val()) || 0),
        um = parseFloat(removeComma($("#mk-uang_muka").val()) || 0),
        badm = parseFloat(removeComma($("#mk-biaya_adm").val()) || 0),
        persentase_kpr = ($("#idk-is_subsidi").val() == 1) ? 0.05 : 0.1,
        penambahan_biaya = parseFloat(removeComma($("#mk-harga_penambahan").val()) || 0),
        penambahan_biaya_um = parseFloat(removeComma($("#mk-harga_penambahan_um").val()) || 0)
        ;


    // if ($("#mk-jenis-diskon").val() == "Harga Jual") {
    //     hj = hj_real - diskon

    //     $("#mk-hargajual_diskon").val(hj).keyup()

    //     $("#mk-kpr").val(hj - (hj * persentase_kpr)).keyup()
    //     $("#mk-uang_muka").val(hj * persentase_kpr).keyup()
    // } else {
    //     hj = hj_real

    //     $("#mk-hargajual_diskon").val(hj).keyup()

    //     $("#mk-kpr").val(kpr).keyup()
    //     $("#mk-uang_muka").val(um).keyup()
    // }


    // if ($("#idk-is_kpr").val() == 1) {
    //     totalum = hj - kpr + penambahan_biaya + penambahan_biaya_um
    //     if ($("#mk-jenis-diskon").val() == "Uang Muka")
    //         totalum = hj - kpr - diskon + penambahan_biaya
    // } else {
    //     totalum = hj + penambahan_biaya_um
    //     if ($("#mk-jenis-diskon").val() == "Uang Muka")
    //         totalum = hj - diskon + penambahan_biaya
    // }

    // um = hj_net - kpr 
    totalum = um + badm + penambahan_biaya_um - diskon_um + penambahan_biaya

    // $("#mk-uang_muka").val(um)


    $(".totalbb").toArray().forEach(function (f) {
        totalbb += parseFloat(removeComma(f.value) || 0)
    })
    // alert(totalbb)
    $(".tum").val(totalum).keyup();
    $(".tbb").val(totalbb).keyup();
}

$('#isi_tagihan-modal').on('hidden.bs.modal', function () {
    data_um = {}
    data_bb = {}
})

function simpan_dt_konsumen_keuangan(e) {
    if (!palid("idk-nama_konsumen", "", "Nama konsumen harus diisi"))
        return;
    if (!palid("idk-status_mkdt", "", "Status harus diisi"))
        return;
    if (parseFloat(removeComma($("#total_cicilan_um").val() || 0)) > 0 || parseFloat(removeComma($("#total_cicilan_bb").val() || 0)) > 0) {
        if ($("#total_cicilan_um").val() != $("#mk-total_um").val() || $("#total_cicilan_bb").val() != $("#mk-total_bb").val()) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Total Cicilan tidak sesuai dengan total biaya",
                showConfirmButton: false,
                timer: 1500
            });
            return false;
        }
    }

    let dt = {}
    dt[csrfName] = csrfHash
    $("form#fm-idk_keu :input").each(function () {
        dt[this.name] = this.value
    });

    let i = 0
    //cicilan um
    for (var k in data_um) {
        if (!data_um.hasOwnProperty(k)) continue;
        var obj = data_um[k];

        for (var d in obj) {
            if (!obj.hasOwnProperty(d)) continue;
            var x = obj[d];
            dt[d + "[" + i + "]"] = x
        }
        i++;
    }
    //cicilan bb
    i = 0
    for (var k in data_bb) {
        if (!data_bb.hasOwnProperty(k)) continue;
        var obj = data_bb[k];

        for (var d in obj) {
            if (!obj.hasOwnProperty(d)) continue;
            var x = obj[d];
            dt[d + "[" + i + "]"] = x
        }
        i++;
    }

    $.ajax({
        url: base_url + '/keuangan/save_kons',
        type: 'post',
        data: dt,
        dataType: 'json',
        beforeSend: function () {
            $('#add-form-btn-idk_keu').prop("disabled", true);
            $('#add-form-btn-idk_keu').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
        },
        success: function (r) {
            csrfHash = r.token;

            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function () {
                    $('.modal').modal('hide');
                    $('#add-form-btn-idk_keu').html('Simpan');
                    $('#add-form-btn-idk_keu').prop("disabled", false);
                })
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function () {
                    $('#add-form-btn-idk_keu').html('Simpan');
                    $('#add-form-btn-idk_keu').prop("disabled", false);
                })
            }
            load_kavling();
            hapus_seleksi();
        },
        error: function (e) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan",
                showConfirmButton: true,
                // timer: 1500
            }).then(function () {
                $('#add-form-btn-idk_keu').html('Simpan');
                $('#add-form-btn-idk_keu').prop("disabled", false);
            })
        }
    });

}

$("#refresh-btn-idk_keu").click(function () {
    $("#fm-idk_keu .num").prop('disabled', false);

    $("#idk_data_baru").val(1);
    $("#fm-idk_keu")[0].reset();

    // refresh_fmmkdt(false);
    $("#fm-idk_keu input:text, #fm-idk_keu select, #fm-idk_keu textarea").prop("disabled", false);
    $("#fm-idk_keu #idk-id_konsumen").val("");

    $("#idk-show_keterangan_batal").addClass('hidden');

})

function isi_data_konsumen() {
    if (!editdtt[0]) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Tidak ada kavling yang dipilih",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }

    var role,
        sh = editdtt[0],
        id_kavling = sh.id.substr(3),
        id_hargajual = sh.data2.id_hargajual;

    if (sh.data.tipe != "kavling") {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Tidak ada kavling terpilih ",
            showConfirmButton: true,
            // timer: 1500
        })
        return;
    }
    if (sh.data2.harga_akhir == "-") {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Kavling belum dipasarkan (tidak ada harga jual)",
            showConfirmButton: true,
            // timer: 1500
        })
        return;
    }

    data_um = {}
    data_bb = {}
    $("#list_cicilan_here").html("")
    $("#total_cicilan_um").val(0).change().keyup()
    $("#total_cicilan_bb").val(0).change().keyup()
    $("#id_list_keu").val('');
    $("#id_list_keu_bb").val('');
    $("#mk-total_bb").val(0)
    $("#mk-total_um").val(0)


    $("#fm-idk_keu .num").prop('disabled', false);

    $("#idk_data_baru").val(1);
    $("#fm-idk_keu")[0].reset();

    $("#mk-diskon_harga_jual, #mk-diskon_uang_muka").val(0);


    // refresh_fmmkdt(false);
    $("#fm-idk_keu input:text, #fm-idk_keu select, #fm-idk_keu textarea").prop("disabled", false);
    $("#fm-idk_keu #idk-id_konsumen").val("");

    $(".delete_kons_div").addClass("hidden");

    $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").addClass('hidden');

    // $("#fm-mkdt .num").val(0)

    $(".id_kavling").val(id_kavling);
    $("#idk-id_mkdt").val(sh.data.id_mkdt);

    $.ajax({
        url: base_url + '/keuangan/get_data_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_mkdt: sh.data.id_mkdt,
            id_kavling: id_kavling,
            id_hargajual: id_hargajual
        },
        dataType: 'json',
        beforeSend: function () {
            $("#loading").removeClass("hidden");
        },
        success: function (x) {
            $("#loading").addClass("hidden");
            csrfHash = x.token;
            let v = x.data, //data mkdt
                h = x.hj, //pricelist
                tg = x.tagihan

            //load hargajual 
            if (h.hargajual) {
                $.each(h, function (k, v) {
                    $("#mk-" + k).val(v).change().keyup();
                })

                if (h.tgl_harga != "0000-00-00")
                    document.querySelector("#mk-tgl_harga")._flatpickr.setDate(h.tgl_harga);

                $("#idk-tgl_harga").val(format_date(h.tgl_harga));
                $("#idk-harga_kpr").val(h.kpr).change();
            }

            $("#idk-harga_akhir").val(id_hargajual)

            if (v) {
                if (v.status_mkdt == "Batal") {
                    $("#fm-idk_keu input:text, #fm-idk_keu select, #fm-idk_keu textarea").prop("disabled", true);
                    $("#fm-idk_keu #idk-id_konsumen").val("");
                    $("#fm-idk_keu #idk-id_keuangan0").val("");

                    $(".refresh_fmmkdt_div").removeClass("hidden");
                    // $(".delete_kons_div").addClass("hidden");

                    $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").removeClass('hidden');
                    // $("#delete_kons_div").addClass("hidden");
                }

                if (v.id_konsumen)
                    $("#idk_data_baru").val(0);

                $("#fm-idk_keu #idk-no_spptb").val(v.no_spptb)
                $("#fm-idk_keu #idk-status_mkdt").val(v.status_mkdt)
                $("#fm-idk_keu #idk-keterangan_batal").val(v.keterangan_batal)
                if (v.booking_tgl != "0000-00-00")
                    document.querySelector("#idk-booking_tgl")._flatpickr.setDate(v.booking_tgl);
                $("#fm-idk_keu #idk-booking_fee").val(v.booking_fee)

                $("#fm-idk_keu #idk-id_konsumen").val(v.id_konsumen)
                $("#fm-idk_keu #idk-nama_konsumen").val(v.nama_konsumen)
                $("#fm-idk_keu #idk-alamat_konsumen").val(v.alamat_konsumen)
                $("#fm-idk_keu #idk-nik_konsumen").val(v.nik_konsumen)
                $("#fm-idk_keu #idk-npwp_konsumen").val(v.npwp_konsumen)
                $("#fm-idk_keu #idk-hp_konsumen").val(v.hp_konsumen)
                $("#fm-idk_keu #idk-email_konsumen").val(v.email_konsumen)
                $("#fm-idk_keu #idk-status_konsumen").val(v.status_konsumen)

                $("#fm-idk_keu #idk-status_pernikahan").val(v.status_pernikahan)
                $("#fm-idk_keu #idk-nama_pasangan").val(v.nama_pasangan)
                $("#fm-idk_keu #idk-nik_pasangan").val(v.nik_pasangan)
                $("#fm-idk_keu #idk-nama_instansi").val(v.nama_instansi)
                $("#fm-idk_keu #idk-alamat_instansi").val(v.alamat_instansi)
                $("#fm-idk_keu #idk-tel_instansi").val(v.tel_instansi)
                $("#fm-idk_keu #idk-sales").val(v.sales)
                $("#fm-idk_keu #idk-is_kpr").val(v.is_kpr)
                $("#fm-idk_keu #idk-is_subsidi").val(v.is_subsidi)

                //harga_jual
                $("#fm-idk_keu #mk-hargajual").val(v.harga_jual)
                $("#fm-idk_keu #mk-hargajual_net").val(v.harga_jual_net)
                $("#fm-idk_keu #mk-kpr").val(v.harga_kpr)
                $("#fm-idk_keu #mk-uang_muka").val(v.harga_uang_muka)
                $("#fm-idk_keu #mk-biaya_adm").val(v.harga_administrasi)
                $("#fm-idk_keu #mk-bphtb").val(v.harga_bphtb)
                $("#fm-idk_keu #mk-ppn").val(v.harga_ppn)
                $("#fm-idk_keu #mk-biaya_proses").val(v.harga_biaya_proses)
                $("#fm-idk_keu #mk-harga_penambahan").val(v.harga_penambahan)


                //kpr
                $("#mk-harga_kpr_acc").val(v.harga_kpr_acc).change().keyup()

                let turun_kpr = (v.harga_kpr_acc == 0) ? 0 : v.harga_kpr - v.harga_kpr_acc;
                $("#mk-harga_penambahan_um").val(turun_kpr).change().keyup()


            }

            if (tg) {
                let a = it;
                $.each(tg, function (i, v) {
                    if (v.status == "UM") {
                        data_um['lk' + a] = ({
                            id_list_keu: 'lk' + a,
                            id_keuangan: (v.id_keuangan),
                            berita_acara: (v.berita_acara),
                            nominal: num_format(v.nominal),
                            jatuh_tempo_tgl: (v.jatuh_tempo_tgl),
                        })
                    }
                    if (v.status == "BB") {
                        data_bb['lk' + a] = ({
                            id_list_keu_bb: 'lk' + a,
                            id_keuangan_bb: (v.id_keuangan),
                            berita_acara_bb: (v.berita_acara),
                            nominal_bb: num_format(v.nominal),
                            jatuh_tempo_tgl_bb: (v.jatuh_tempo_tgl),
                        })
                    }

                    a++;
                })
                tambah_ketagihan()
                it = a;
            }

            sum_mktotal()
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal-isi_data_konsumen').modal({
                backdrop: 'static',
                keyboard: false
            });

        },
        error: function (e) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Token tidak valid, muat ulang halaman",
                showConfirmButton: true,
                // timer: 1500
            }).then(function () {
                location.reload();
            })
        }
    });
}

//sudah tidak dipakai
function isi_tagihan() {
    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    if (sh.data2.status_mkdt == 'Batal') {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Status konsumen batal",
            text: "Silahkan isi kavling dengan konsumen baru terlebih dahulu",
            // showConfirmButton: false,
            // timer: 1500
        })
        return;
    }

    if (!sh.data.id_mkdt) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }

    data_um = {}
    data_bb = {}
    $("#fm-isi_tagihan")[0].reset()
    $("#list_cicilan_here").html("")
    $("#total_cicilan_um").val(0).change().keyup()
    $("#total_cicilan_bb").val(0).change().keyup()
    $("#id_list_keu").val('');
    $("#id_list_keu_bb").val('');

    // $("#cicilan_belong_here").html("");
    // $("#berita_acara0").val("Uang Muka 1");
    // $("#nominal0").val(0).keyup();

    $.ajax({
        url: base_url + '/keuangan/get_data_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_keuangan: sh.data.id_keuangan,
            id_kavling: id_kavling,
            id_mkdt: sh.data.id_mkdt
        },
        dataType: 'json',
        beforeSend: function () {
            $("#loading").removeClass("hidden")
        },
        success: function (r) {
            $("#loading").addClass("hidden")
            let mkdt = r.mkdt,
                hj = r.hj,
                tg = r.tagihan

            $("#mk-id_mkdt").val(sh.data.id_mkdt)

            $('.select2').val(null).trigger('change');
            if (hj.id) {
                // if (hj.id) {

                for (let i in hj) {
                    $("#fm-isi_tagihan #mk-" + i).val(hj[i]).change().keyup();
                }
                $("#fm-isi_tagihan #mk-tgl_harga").val(format_date(hj.tgl_harga));
                $("#fm-isi_tagihan #mk-tipe").val(hj.tipe_rumah);
                $("#mk-id").append(
                    $("<option selected></option>")
                        .attr("value", hj.id)
                        .text("Rp. " + num_format(hj.hargajual) + " (" + hj.tipe_rumah + ")" + ": Per " + hj.tgl_harga)
                ).trigger('change');
                // } else {
                //     $(".mk-fm").val(0)
                // }
            } else {
                $("#mk-id").append(
                    $("<option selected></option>")
                        .attr("value", mkdt.id_hargajual)
                        .text("Rp. " + num_format(mkdt.harga_jual) + " (" + mkdt.tipe_rumah + ")" + ": " + mkdt.tgl_harga)
                ).trigger('change');

                $("#fm-isi_tagihan #mk-tgl_harga").val(format_date(mkdt.tgl_harga));
                $("#mk-row").val(mkdt.row).change()
                $("#mk-tipe").val(mkdt.tipe_rumah).change()
                $("#mk-lb").val(mkdt.hj_lb).change()
                $("#mk-lt").val(mkdt.hj_lt).change()

                $("#mk-hargajual").val(mkdt.harga_jual).change()
                $("#mk-kpr").val(mkdt.harga_kpr).change()
                $("#mk-uang_muka").val(mkdt.harga_jual - mkdt.harga_kpr).change()
                $("#mk-bphtb").val(mkdt.harga_bphtb).change()
                $("#mk-biaya_adm").val(mkdt.harga_administrasi).change()
                $("#mk-biaya_proses").val(mkdt.harga_biaya_proses).change()
            }
            $("#mk-diskon").val(mkdt.harga_diskon).change().keyup()
            $("#mk-harga_penambahan").val(mkdt.harga_penambahan).change().keyup()
            $("#mk-keterangan_harga_penambahan").val(mkdt.keterangan_penambahan_biaya)


            $("#mk-harga_ppn").val(mkdt.harga_ppn).change().keyup()
            $("#mk-harga_kpr_acc").val(mkdt.harga_kpr_acc).change().keyup()

            let turun_kpr = (mkdt.harga_kpr_acc == 0) ? 0 : mkdt.harga_kpr - mkdt.harga_kpr_acc;
            $("#mk-harga_penambahan_um").val(turun_kpr).change().keyup()

            sum_mktotal()

            //load tagihan
            if (tg) {
                let a = it;
                $.each(tg, function (i, v) {
                    if (v.status == "UM") {
                        data_um['lk' + a] = ({
                            id_list_keu: 'lk' + a,
                            id_keuangan: (v.id_keuangan),
                            berita_acara: (v.berita_acara),
                            nominal: num_format(v.nominal),
                            jatuh_tempo_tgl: (v.jatuh_tempo_tgl),
                        })
                    }
                    if (v.status == "BB") {
                        data_bb['lk' + a] = ({
                            id_list_keu_bb: 'lk' + a,
                            id_keuangan_bb: (v.id_keuangan),
                            berita_acara_bb: (v.berita_acara),
                            nominal_bb: num_format(v.nominal),
                            jatuh_tempo_tgl_bb: (v.jatuh_tempo_tgl),
                        })
                    }

                    a++;
                })
                tambah_ketagihan()
                it = a;
            }

            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#isi_tagihan-modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            //load data form
            // for (let i in mkdt) {
            //     $("#fm-isi_tagihan #" + i).val(mkdt[i]);
            // }

            // if (r.tagihan) {
            //     it = 0;
            //     let tg = r.tagihan
            //     if (tg.length > 0) {
            //         for (i = 0; i < tg.length; i++) {
            //             if (i > 0)
            //                 tambah();

            //             fp = flatpickr("#fm-isi_tagihan #jatuh_tempo_tgl" + i, {
            //                 altInput: true,
            //                 altFormat: 'F j, Y',
            //                 dateFormat: 'Y-m-d'
            //             });
            //             // console.log(tg[i]['id_keuangan']);
            //             $("#fm-isi_tagihan #id_keuangan" + i).val(tg[i]['id_keuangan']);
            //             $("#fm-isi_tagihan #nominal" + i).val(tg[i]['nominal']).keyup().change();
            //             $("#fm-isi_tagihan #berita_acara" + i).val(tg[i]['berita_acara']);
            //             document.querySelector("#fm-isi_tagihan #jatuh_tempo_tgl" + i)._flatpickr.setDate(tg[i]['jatuh_tempo_tgl']);
            //         }
            //     }
            // }
            // total('#fm-isi_tagihan');
        },
        error: function () {
            $("#loading").addClass("hidden")
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }
    });
}


// $("#mk-jenis-diskon").change(function () {
//     if (this.value == "Harga Jual") {
//         $("#hjdis").removeClass("hidden")
//         // $("#umdis").addClass("hidden")
//     } else if (this.value == "Uang Muka") {
//         $("#hjdis").addClass("hidden")
//         // $("#umdis").removeClass("hidden")
//     }
//     sum_mktotal()
// })
$("#add-form-isi-tagihan").click(function (e) {
    e.preventDefault();
});
$("#add-form-btn-idk_keu").click(function (e) {
    e.preventDefault();
});

//sudah tidak di pakai
function save_isi_tagihan(e) {
    if (parseFloat(removeComma($("#total_cicilan_um").val() || 0)) > 0 || parseFloat(removeComma($("#total_cicilan_bb").val() || 0)) > 0) {
        if ($("#total_cicilan_um").val() != $("#mk-total_um").val() || $("#total_cicilan_bb").val() != $("#mk-total_bb").val()) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Total Cicilan tidak sesuai dengan total biaya",
                showConfirmButton: false,
                timer: 1500
            });
            return false;
        }
    }
        let dt = {}
        dt[csrfName] = csrfHash
        $("form#fm-idk_keu :input").each(function () {
            dt[this.name] = this.value
        });

    let i = 0
    for (var k in data_um) {
        if (!data_um.hasOwnProperty(k)) continue;
        var obj = data_um[k];

        for (var d in obj) {
            if (!obj.hasOwnProperty(d)) continue;
            var x = obj[d];
            dt[d + "[" + i + "]"] = x
        }
        i++;
    }
    i = 0
    for (var k in data_bb) {
        if (!data_bb.hasOwnProperty(k)) continue;
        var obj = data_bb[k];

        for (var d in obj) {
            if (!obj.hasOwnProperty(d)) continue;
            var x = obj[d];
            dt[d + "[" + i + "]"] = x
        }
        i++;
    }

    $.ajax({
        url: base_url + '/Keuangan/isi_tagihan',
        type: 'post',
        data: dt,
        dataType: 'json',
        beforeSend: function () {
            $('#add-form-isi-tagihan').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            $('#add-form-isi-tagihan').addClass("disabled");
        },
        success: function (r) {
            csrfHash = r.token;

            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function () {
                    // $('.modal').modal('hide');
                    load_kavling();
                    hapus_seleksi();
                    $('#add-form-isi-tagihan').html('Simpan');
                    $('#add-form-isi-tagihan').removeClass("disabled");
                })
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    // timer: 1500
                }).then(function () {
                    $('#add-form-isi-tagihan').html('Simpan');
                    $('#add-form-isi-tagihan').removeClass("disabled");
                })
            }
        },
        error: function () {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "terjadi kesalahan",
                showConfirmButton: false,

            }).then(function () {
                // $('.modal').modal('hide');
                $('#add-form-isi-tagihan').html('Simpan');
                $('#add-form-isi-tagihan').removeClass("disabled");
            })
            $('#add-form-isi-tagihan').html('Simpan');
            $('#add-form-isi-tagihan').removeClass("disabled");
        }
    });

}

$("#bt-for, #bt-for_bb").select2()

function open_keuangan(sh, role, id_kavling) {
    $("#fm-keuangan")[0].reset(); //reset form
    $("#label_konsumen").html(""); //reset label nama
    $("#tb-data-log_pembayaran, #tb-data-log_pembayaran_bb, #tb-data-tagihan, #tb-data-tagihan_bb").empty(); //reset table log
    $("#booking_fee_paid, #keu_booking_fee").prop("disabled", false); //set disabled false untuk input booking 

    document.querySelector("#keu_booking_tgl")._flatpickr._input.disabled = false; //set disabled false untuk input tanggal booking 

    $("#hide_lunas").removeClass("hidden");
    $("#hide_refund").addClass("hidden");

    $(".id_kavling").val(id_kavling);
    $("#fm-keuangan #id_mkdt").val(sh.data.id_mkdt);

    $('#add-form-btn-keuangan').prop("disabled", false);
    $("#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid").prop("disabled", 0);
    document.querySelector("#tanggal_refund")._flatpickr._input.disabled = false;

    $.ajax({
        url: base_url + '/keuangan/getTagihan',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_keuangan: sh.data.id_keuangan,
            id_kavling: id_kavling,
            id_mkdt: sh.data.id_mkdt,
            id_hargajual : sh.data2.id_hargajual
        },
        dataType: 'json',
        beforeSend: function () {
            $("#loading").removeClass("hidden");
        },
        success: function (r) {
            $("#loading").addClass("hidden");
            let mkdt = r.mkdt,
                sb = r.log_pembayaran,
                lp = r.log_pembayaran,
                disabled = ""
            tg = r.tagihan;
            csrfHash = r.token;

            if (tg.length) {
                //load detail biaya dari mkdt
                $("#label_konsumen").html(mkdt.nama_konsumen);
                if (mkdt) {
                    $("#fm-keuangan #status_mkdt").val(mkdt.status_mkdt);

                    //jika status batal
                    if (mkdt.status_mkdt == "Batal") {
                        $("#hide_lunas").addClass("hidden");
                        $("#hide_refund").removeClass("hidden");
                    }

                    //matikan tombol simpan jika sudah refund
                    if (mkdt.refund_paid == 1) {
                        $('#add-form-btn-keuangan').prop("disabled", true);
                        $("#hide_lunas").addClass("hidden");
                        $("#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid").prop("disabled", 1);
                        $("#fm-keuangan #refund_paid").prop("checked", 1);

                        $("#keterangan_refund").val(mkdt.refund_keterangan).change();
                        $("#nominal_refund").val(mkdt.refund).change();
                        if (mkdt.refund_tgl != "0000-00-00")
                            document.querySelector("#tanggal_refund")._flatpickr.setDate(mkdt.refund_tgl);
                        document.querySelector("#tanggal_refund")._flatpickr._input.disabled = true;

                        disabled = "disabled"
                    }

                    //matikan tombol simpan jika sudah lunas
                    if (mkdt.is_lunas == 1) {
                        $('#add-form-btn-keuangan').prop("disabled", true);
                        $("#hide_lunas").addClass("hidden");
                        disabled = "disabled"
                    }

                    //load data form
                    // for (let i in mkdt) {
                    //     $("#fm-keuangan #bt-" + i).val(mkdt[i]);
                    // }

                    $("#fm-keuangan #nama_konsumen").val(mkdt.nama_konsumen);


                    //load data um
                    $("#bt-harga_jual").val(mkdt.harga_jual).change()
                    $("#bt-harga_kpr_acc").val(mkdt.harga_kpr_acc).change()
                    $("#bt-harga_diskon").val(mkdt.harga_diskon).change()

                    $("#bt-total_biaya_um").val(parseFloat(mkdt.harga_uang_muka) + parseFloat(mkdt.harga_penambahan_um) + parseFloat(mkdt.harga_penambahan) + parseFloat(mkdt.harga_administrasi) - parseFloat(mkdt.harga_diskon_uang_muka)).change()

                    //load data bb
                    $("#bt-harga_bphtb").val(mkdt.harga_bphtb).change()
                    $("#bt-harga_administrasi").val(mkdt.harga_administrasi).change()
                    $("#bt-harga_biaya_proses").val(mkdt.harga_biaya_proses).change()
                    $("#bt-harga_ppn").val(mkdt.harga_ppn).change()
                    $("#bt-keterangan_penambahan_biaya").val(mkdt.keterangan_penambahan_biaya).change()
                    $("#bt-harga_penambahan").val(mkdt.harga_penambahan).change()

                    

                    $("#bt-total_biaya_bb").val(parseFloat(mkdt.harga_bphtb) + parseFloat(mkdt.harga_biaya_proses) + parseFloat(mkdt.harga_ppn)).change()

                    //set checnkbox value to prevent being 0 by automatic load data 
                    $("#fm-keuangan #is_lunas").val(1);

                    if (mkdt.booking_tgl != "0000-00-00"){
                        document.querySelector("#fm-keuangan #booking_tgl")._flatpickr.setDate(mkdt.booking_tgl);
                        document.querySelector("#keu_booking_tgl")._flatpickr.setDate(mkdt.booking_tgl);
                    }
                        

                    $(".num").keyup().change();
                    // total('#fm-keuangan');


                    $("#fm-keuangan #booking_fee").val(mkdt.booking_fee).change()
                    $("#keu_booking_fee").val(mkdt.booking_fee).change()
                    
                    //set booking paid/not
                    $("#booking_fee_paid").val(1);
                    if (mkdt.booking_paid == 1) {
                        $("#booking_fee_paid, #keu_booking_fee").prop("disabled", true);
                        document.querySelector("#keu_booking_tgl")._flatpickr._input.disabled = true
                    }
                }

                //sudah bayar

                /************************* UM & BB **************************/
                let nom = 0,
                    tot = removeComma($("#bt-total_biaya_um").val()) || 0,
                    sisa = 0,
                    prs = 0,
                    nom_bb = 0,
                    tot_bb = removeComma($("#bt-total_biaya_bb").val()) || 0,
                    sisa_bb = 0,
                    prs_bb = 0;
                $.each(sb, function (i, v) {
                    if (v.status == 'UM')
                        nom += parseFloat(v.nominal) || 0
                    else if (v.status == 'BB')
                        nom_bb += parseFloat(v.nominal) || 0
                })
                sisa = tot - nom
                sisa_bb = tot_bb - nom_bb

                prs = (nom == 0) ? 0 : nom / tot * 100;
                prs_bb = (nom_bb == 0) ? 0 : nom_bb / tot_bb * 100;


                $("#bt-sudah_bayar_um").val(nom).keyup();
                $("#bt-sisa_tagihan_um").val(sisa).keyup();

                $("#bt-persentase_bayar_tagihan_um").val(prs.toFixed(2) + "%");

                $("#bt-sudah_bayar_bb").val(nom_bb).keyup();
                $("#bt-sisa_tagihan_bb").val(sisa_bb).keyup();

                $("#bt-persentase_bayar_tagihan_bb").val(prs_bb.toFixed(2) + "%");

                /************************ load table tagihan ***************************/
                let tr_tg = "",
                    tr_tg_bb = "",
                    no = 1,
                    no_bb = 1,
                    tot_tg = 0,
                    tot_tg_bb = 0,
                    sb_button = "",
                    sb_button_bb = "",
                    chkd = "",
                    opt = "",
                    opt_bb = "",
                    dsb = ""
                $("#bt-for").html("")
                $("#bt-for_bb").html("")
                $.each(tg, function (i, v) {
                    chkd = ""
                    dsb = ""
                    if (v.status == "UM") {
                        if (v.sudah_dibayar == 1) {
                            chkd = "checked"
                            // dsb = "disabled"
                        }

                        sb_button = `<div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" ` + chkd + ` onchange="save_sb(` + v.id_keuangan + `)" class="custom-control-input " ` + disabled + ` id="sb_btn` + v.id_keuangan + `" name="sb_btn[` + v.id_keuangan + `]" value="1" />
                                        <label class="custom-control-label" for="sb_btn` + v.id_keuangan + `"></label>
                                    </div>
                                </div>`;

                        tot_tg += parseInt(v.nominal);
                        tr_tg += "<tr>" +
                            "<td>" + no + "</td>" +
                            "<td>" + v.berita_acara + "</td>" +
                            "<td style='text-align:right'>" + num_format(v.nominal) + "</td>" +
                            "<td>" + format_date(v.jatuh_tempo_tgl) + "</td>" +
                            "<td>" + v.username + "<br/>" + format_datetime(v.created_at) + " </td>" +
                            "<td>" + sb_button + "</td>" +
                            "<tr>";
                        no++;
                        opt += "<option " + dsb + " value='" + v.id_keuangan + "'>" + v.berita_acara + "</option>"
                    } else if (v.status == "BB") {
                        if (v.sudah_dibayar == 1) {
                            chkd = "checked"
                            dsb = "disabled"
                        }

                        sb_button_bb = `<div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" ` + chkd + ` onchange="save_sb(` + v.id_keuangan + `)" class="custom-control-input " ` + disabled + ` id="sb_btn` + v.id_keuangan + `" name="sb_btn[` + v.id_keuangan + `]" value="1" />
                                        <label class="custom-control-label" for="sb_btn` + v.id_keuangan + `"></label>
                                    </div>
                                </div>`;

                        tot_tg_bb += parseInt(v.nominal);
                        tr_tg_bb += "<tr>" +
                            "<td>" + no_bb + "</td>" +
                            "<td>" + v.berita_acara + "</td>" +
                            "<td style='text-align:right'>" + num_format(v.nominal) + "</td>" +
                            "<td>" + format_date(v.jatuh_tempo_tgl) + "</td>" +
                            "<td>" + v.username + "<br/>" + format_datetime(v.created_at) + " </td>" +
                            "<td>" + sb_button_bb + "</td>" +
                            "<tr>";
                        no_bb++;
                        opt_bb += "<option " + dsb + " value='" + v.id_keuangan + "'>" + v.berita_acara + "</option>"
                    }
                })
                tr_tg += "<tr>" +
                    "<th colspan='2'>Total</th>" +
                    "<th style='text-align:right'>" + num_format(tot_tg) + "</th>" +
                    "<th colspan='3'></th>" +
                    "<tr>";
                tr_tg_bb += "<tr>" +
                    "<th colspan='2'>Total</th>" +
                    "<th style='text-align:right'>" + num_format(tot_tg_bb) + "</th>" +
                    "<th colspan='3'></th>" +
                    "<tr>";

                $("#bt-for").append(opt)
                $("#bt-for_bb").append(opt_bb)

                $("#tb-data-tagihan").append(tr_tg);
                $("#tb-data-tagihan_bb").append(tr_tg_bb);


                /************************ load table log pembayaran ***************************/
                let t = "",
                    tot_lp = 0,
                    t_bb = "",
                    tot_lp_bb = 0;
                no = 1;
                no_bb = 1;
                $.each(lp, function (i, v) {
                    //set tgl & booking fee yang diinput oleh keuangan
                    if (v.payment_type == 'Booking') {
                        $("#keu_booking_fee").val(v.nominal).keyup();
                        document.querySelector("#keu_booking_tgl")._flatpickr.setDate(v.tanggal_bayar);
                    }
                    if (v.status == "UM") {
                        // if (v.payment_type == 'Refund')
                        //     tot_lp -= parseInt(v.nominal);
                        // if (v.payment_type == 'Pembayaran')
                        tot_lp += parseInt(v.nominal);
                        t += "<tr>" +
                            "<td>" + no + "</td>" +
                            "<td>" + format_date(v.tanggal_bayar) + "</td>" +
                            "<td style='text-align:right'>" + num_format(v.nominal) + "</td>" +
                            "<td class='text-left'>" + v.keterangan + "</td>" +
                            "<td>" + v.username + "<br/>" + format_datetime(v.created_at) + " </td>" +
                            `<td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="printRiwayatBayar('` + v.id_pembayaran + `', '` + v.id_mkdt + `', '` + dt_proyek['id_proyek'] + `')"><i class="fa fa-print"></i></button>
                                        <button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="removeRiwayatBayar('` + v.id_pembayaran + `')"><i class="fa fa-trash"></i></button>
                                    </div>
                                </td>` +
                            "<tr>";
                        no++;
                    } else if (v.status == "BB") {
                        tot_lp_bb += parseInt(v.nominal);
                        t_bb += "<tr>" +
                            "<td>" + no_bb + "</td>" +
                            "<td>" + format_date(v.tanggal_bayar) + "</td>" +
                            "<td style='text-align:right'>" + num_format(v.nominal) + "</td>" +
                            "<td class='text-left'>" + v.keterangan + "</td>" +
                            "<td>" + v.username + "<br/>" + format_datetime(v.created_at) + " </td>" +
                            `<td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="printRiwayatBayar('` + v.id_pembayaran + `')"><i class="fa fa-print"></i></button>
                                        <button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="removeRiwayatBayar('` + v.id_pembayaran + `')"><i class="fa fa-trash"></i></button>
                                    </div>
                                </td>` +
                            "<tr>";
                        no_bb++;
                    }
                });
                t += "<tr>" +
                    "<th colspan='2'>Total</th>" +
                    "<th style='text-align:right'>" + num_format(tot_lp) + "</th>" +
                    "<th colspan='3'></th>" +
                    "<tr>";
                t_bb += "<tr>" +
                    "<th colspan='2'>Total</th>" +
                    "<th style='text-align:right'>" + num_format(tot_lp_bb) + "</th>" +
                    "<th colspan='3'></th>" +
                    "<tr>";

                $("#tb-data-log_pembayaran").append(t);
                $("#tb-data-log_pembayaran_bb").append(t_bb);
                /************************ end of load table log pembayaran ***************************/

                $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
                $('#modal_divisi' + role).modal({
                    backdrop: 'static',
                    keyboard: false
                });
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan saat memuat data",
                    text: "Isi tagihan terlebih dahulu",
                    showConfirmButton: false,
                })
                return;
            }
        },
        error: function () {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }
    });

}

function printRiwayatBayar(e, e2, e3) {
    var myWindow = window.open(base_url + "/keuangan/print_kuitansi/" + e + "/" + e2 + "/" + e3 + "/", "_blank", "top=100,left=300,width=700,height=600");
    setTimeout(function () {
        myWindow.focus();
        myWindow.print();
    }, 1000);
}

function removeRiwayatBayar(e) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Apakah anda yakin akan menghapus data?",
        // type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya!',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: !1
    }).then(function (t) {
        if (t.value) {
            $.ajax({
                url: base_url + '/Keuangan/removeLP',
                type: 'post',
                data: {
                    [csrfName]: csrfHash,
                    id_pembayaran: e
                },
                dataType: 'json',
                beforeSend: function () {
                    $("#loading").removeClass("hidden");
                },
                success: function (r) {
                    $("#loading").addClass("hidden");
                    csrfHash = r.token;
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function () {
                        $('.modal').modal('hide');
                    })
                },
                error: function (e) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: "Terjadi Kesalahan",
                        showConfirmButton: true,
                        // timer: 1500
                    })
                }
            });
        }
    })
}

function save_keuangan(e = '') {

    if ($("#fm-keuangan #status_mkdt").val() == "Batal") {
        // if (!palid("keterangan_refund", "", "Keterangan harus diisi"))
        //     return;
        // if (!palid("nominal_refund", "", "Nominal harus diisi"))
        //     return;
    } else {
        if (e == '') {
            //validasi manual cuuukk
            if ($("#booking_fee_paid").prop("disabled") == true) {
                if ($("#fm-keuangan #is_lunas").prop("checked") == false) {

                    if (!palid("bt-berita_acara_um", "", "Keterangan pembayaran harus diisi"))
                        return;
                    //nominal harus diisi
                    if (!palid("bt-bayar_tagihan_um", "0", "Nominal Tidak boleh 0"))
                        return;
                    if (!palid("bt-bayar_tagihan_um", null, "Nominal Tidak boleh kosong"))
                        return;
                    if (!palid("bt-bayar_tagihan_um", '', "Nominal Tidak boleh kosong"))
                        return;
                    if (!palid("bt-tanggal_bayar_um", '', "Tanggal bayar Tidak boleh kosong"))
                        return;

                    //jika nonminal sama dengan sisa tagihan   
                    // if ($("#bayar_tagihan_um").val() != $("#bt-sisa_tagihan_um").val()) {
                    //     if (!palid("berita_acara_jatuh_tempo", "", "Berita acara selanjutnya harus diisi"))
                    //         return;
                    //     if (!palid("jatuh_tempo_tgl_next", "", "Tanggal Jatuh tempo harus diisi"))
                    //         return;
                    // }
                } else {
                    // if ($("#sisa_tagihan").val() != "0") {
                    //     Swal.fire({
                    //         position: 'bottom-end',
                    //         icon: 'error',
                    //         title: "Transaksi tidak bisa diselesaikan karena masih ada sisa tagihan",
                    //         showConfirmButton: false,
                    //         timer: 1500
                    //     });
                    //     return false;
                    // }

                }
            }
        } else {
            if ($("#booking_fee_paid").prop("disabled") == true) {
                if ($("#fm-keuangan #is_lunas").prop("checked") == false) {

                    if (!palid("bt-berita_acara_bb", "", "Keterangan pembayaran harus diisi"))
                        return;
                    //nominal harus diisi
                    if (!palid("bt-bayar_tagihan_bb", "0", "Nominal Tidak boleh 0"))
                        return;
                    if (!palid("bt-bayar_tagihan_bb", null, "Nominal Tidak boleh kosong"))
                        return;
                    if (!palid("bt-bayar_tagihan_bb", '', "Nominal Tidak boleh kosong"))
                        return;
                    if (!palid("bt-tanggal_bayar_bb", '', "Tanggal bayar Tidak boleh kosong"))
                        return;

                    //jika nonminal sama dengan sisa tagihan   
                    // if ($("#bayar_tagihan_um").val() != $("#bt-sisa_tagihan_um").val()) {
                    //     if (!palid("berita_acara_jatuh_tempo", "", "Berita acara selanjutnya harus diisi"))
                    //         return;
                    //     if (!palid("jatuh_tempo_tgl_next", "", "Tanggal Jatuh tempo harus diisi"))
                    //         return;
                    // }
                } else {
                    // if ($("#sisa_tagihan").val() != "0") {
                    //     Swal.fire({
                    //         position: 'bottom-end',
                    //         icon: 'error',
                    //         title: "Transaksi tidak bisa diselesaikan karena masih ada sisa tagihan",
                    //         showConfirmButton: false,
                    //         timer: 1500
                    //     });
                    //     return false;
                    // }

                }
            }
        }
    }
    Swal.fire({
        title: 'Simpan Data?',
        text: "",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya!',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: !1
    }).then(function (t) {
        if (t.value) {
            $.ajax({
                url: base_url + '/Keuangan/save',
                type: 'post',
                data: $("#fm-keuangan").serialize() + "&" + csrfName + "=" + csrfHash + "&text_um=" + $("#bt-for option:selected").text() + "&text_bb=" + $("#bt-for_bb option:selected").text() + '&e=' + e,
                dataType: 'json',
                beforeSend: function () {
                    $('.add-form-btn-keuangan').prop("disabled", true);
                    $('.add-form-btn-keuangan').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
                },
                success: function (r) {
                    csrfHash = r.token;
                    if (r.success === true) {
                        Swal.fire({
                            //position: 'bottom-end',
                            icon: 'success',
                            title: r.messages,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function () {
                            $('.modal').modal('hide');
                            $('.add-form-btn-keuangan').html('Simpan');
                            $('.add-form-btn-keuangan').prop("disabled", false);
                        })
                    } else {
                        Swal.fire({
                            //position: 'bottom-end',
                            icon: 'error',
                            title: r.messages,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function () {
                            $('.add-form-btn-keuangan').html('Simpan');
                            $('.add-form-btn-keuangan').prop("disabled", false);
                        })
                    }
                    load_kavling();
                    hapus_seleksi();
                },
                error: function (r) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: "terjadi kesalahan",
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function () {
                        $('#add-form-btn-keuangan').html('Simpan');
                        $('#add-form-btn-keuangan').prop("disabled", false);

                    })
                }
            });
        } else
            return false
    })
}

function dana_akad() {
    $("#fm-dana_akad")[0].reset()
    if (!editdtt[0]) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Tidak ada kavling yang dipilih",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }

    var role,
        sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    if (!sh.data.id_mkdt) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }

    if (sh.data2.status_mkdt != "Akad") {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "Belum Akad!",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }
    $.ajax({
        url: base_url + '/keuangan/getDanaAkad',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_kavling: id_kavling,
            id_mkdt: sh.data.id_mkdt
        },
        dataType: 'json',
        beforeSend: function () {
            $("#loading").removeClass("hidden")
        },
        success: function (r) {
            $("#loading").addClass("hidden")
            csrfHash = r.token;
            var d = r.data

            $("#fm-dana_akad #id_mkdt").val(sh.data.id_mkdt);
            $("#fm-dana_akad #id_dana_cair").val(d.id);
            $("#fm-dana_akad #nominal_dana_akad").val(d.nominal).change().keyup();
            $("#fm-dana_akad #keterangan_dana_jaminan").val(d.keterangan_dana_jaminan);

            if (d.tgl_rencana_cair != "0000-00-00")
                document.querySelector("#fm-dana_akad #tgl_rencana_cair")._flatpickr.setDate(d.tgl_rencana_cair);
            if (d.tgl_cair != "0000-00-00")
                document.querySelector("#fm-dana_akad #tgl_cair")._flatpickr.setDate(d.tgl_cair);

            if (d.sudah_cair == 1)
                $("#dana_akad_cair").prop("checked", true)
        },
        error: function (r) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "terjadi kesalahan",
                showConfirmButton: false,
                // timer: 1500
            })
        }
    });

    $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
    $('#dana_akad_modal').modal({
        backdrop: 'static',
        keyboard: false
    });
}

function save_dana_akad() {
    $.ajax({
        url: base_url + '/keuangan/saveDanaAkad',
        type: 'post',
        data: $("#fm-dana_akad").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: 'json',
        beforeSend: function () {
            $('#add-form-btn-dana_akad').prop("disabled", true);
            $('#add-form-btn-dana_akad').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
        },
        success: function (r) {
            csrfHash = r.token;
            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function () {
                    $('.modal').modal('hide');
                    $('#add-form-btn-dana_akad').prop("disabled", false);
                    $('#add-form-btn-dana_akad').html('Simpan');
                })
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function () {
                    $('#add-form-btn-dana_akad').prop("disabled", false);
                    $('#add-form-btn-dana_akad').html('Simpan');
                })
            }
            load_kavling();
            hapus_seleksi();
        },
        error: function (r) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "terjadi kesalahan",
                showConfirmButton: false,
                // timer: 1500
            })
            $('#add-form-btn-dana_akad').prop("disabled", false);
            $('#add-form-btn-dana_akad').html('Simpan');
        }
    });
}

$('.modal').on('hidden.bs.modal', function () {
    data_um = {}
    data_bb = {}
 })
/****************************** end of keunagan ****************************************/

</script>
<script>
    $("#status_mkdt").change(function () {
  if ($("#status_mkdt option:selected").val() == "Batal")
    $("#show_keterangan_batal").removeClass("hidden");
  else $("#show_keterangan_batal").addClass("hidden");
});
//
// hitung turun kpr
$("#fm-mkdt #harga_kpr, #fm-mkdt #acc_harga_kpr").change(function () {
  if ($("#fm-mkdt #acc_harga_kpr").val() != "0") {
    $("#fm-mkdt #harga_turun_kpr")
      .val(
        parseFloat(removeComma($("#fm-mkdt #harga_kpr").val())) -
          parseFloat(removeComma($("#fm-mkdt #acc_harga_kpr").val()))
      )
      .change();
  } else {
    $("#fm-mkdt #harga_turun_kpr").val(0);
  }
});
//delete tanggal jika toogle di aktifkan
$("#wawancara").change(function () {
  if (!$("#wawancara").prop("checked")) {
    document.querySelector("#wawancara_tgl")._flatpickr.setDate(null);
  }
});

$("#refresh_fmmkdt_btn").click(function () {
  refresh_fmmkdt(false);
  $("#mkdt_data_baru").val(1);
});

function refresh_fmmkdt($st = true) {
  $("#fm-mkdt")[0].reset();
  $("#fm-mkdt input:text, #fm-mkdt select, #fm-mkdt textarea").prop(
    "disabled",
    $st
  );
  $("#id_konsumen").val("");
  $("#id_keuangan0").val("");
}

function delete_kons() {
  $(
    "#fm-mkdt #nama_konsumen, #fm-mkdt #alamat_konsumen, #fm-mkdt #nik_konsumen, #fm-mkdt #hp_konsumen, #fm-mkdt #status_konsumen"
  ).val("");
  $("#id_konsumen, #id_mkdt").val("");
  $("#mkdt_data_baru").val(1);
}

function open_mkdt(sh, role, id_kavling) {
  if (sh.data.tipe != "kavling") {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Tidak ada kavling terpilih ",
      showConfirmButton: true,
      // timer: 1500
    });
    return;
  }
  if (sh.data2.harga_akhir == "-") {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Kavling belum dipasarkan (tidak ada harga jual)",
      showConfirmButton: true,
      // timer: 1500
    });
    return;
  }

  $("#refresh_fmmkdt_div").addClass("hidden");
  $("#delete_kons_div").addClass("hidden");
  $("#fm-mkdt .num").prop("disabled", false);

  $("#cicilan_belong_here").html("");
  it = 0;
  // $("#data_konsumen").tab('show');

  $("#mkdt_data_baru").val(0);

  refresh_fmmkdt(false);

  $("#fm-mkdt .num").val(0);

  $(".id_kavling").val(id_kavling);
  $("#id_mkdt").val(sh.data.id_mkdt);

  $.ajax({
    url: base_url + "/mkdt/get_data_by_id",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: sh.data.id_mkdt,
      id_kavling: id_kavling,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (x) {
      $("#loading").addClass("hidden");
      csrfHash = x.token;
      let r = x.data, //data mkdt
        h = x.hj; //pricelist

      //load hargajual
      if (h.hargajual) {
        $.each(h, function (k, v) {
          $("#mkdt-" + k)
            .val(v)
            .change()
            .keyup();
        });
        $("#mkdt-tgl_harga").val(format_date(h.tgl_harga));
        $("#fm-mkdt #harga_kpr").val(h.kpr).change();
      }

      //load num
      // if (!r.id_mkdt) {
      //     $(".num").val(0).keyup();
      //     $("#fm-mkdt #harga_jual").val(sh.data2.harga_akhir);
      // }

      //set harga jual dari data kavling
      if (r) {
        if (r.status_mkdt == "Batal") {
          refresh_fmmkdt(true);
          $("#show_keterangan_batal, #refresh_fmmkdt_div").removeClass(
            "hidden"
          );
          $("#delete_kons_div").addClass("hidden");
        }

        //autoload field ke input
        for (let i in r) {
          if (
            i != "perintah_bangun" &&
            i != "wawancara" &&
            i != "akad" &&
            i != "sp3k" &&
            i != "bast_file" &&
            i != "sp3k_file" &&
            i != "perintah_bangun_file"
          )
            $("#fm-mkdt #" + i).val(r[i]);
        }

        $("#fm-mkdt #mkdt_keterangan").val(r.keterangan);
        $("#fm-mkdt #acc_harga_kpr").val(r.harga_kpr_acc).change();
        $("#fm-mkdt #harga_turun_kpr").val(r.harga_penambahan_um).change();

        if (r.perintah_bangun == 1) {
          $("#perintah_bangun").prop("checked", true);
          $("#fm-mkdt #perintah_bangun_oleh").val(r.perintah_bangun_user);
        }

        if (r.wawancara == 1) $("#wawancara").prop("checked", true);
        if (r.sp3k == 1) $("#sp3k").prop("checked", true);
        if (r.akad == 1) $("#akad").prop("checked", true);

        //set datepicker jika tanggal valid
        setDatePicker(r.perintah_bangun_tgl,'#perintah_bangun_tgl')
        setDatePicker(r.booking_tgl,'#booking_tgl')
        setDatePicker(r.wawancara_tgl,'#wawancara_tgl')
        setDatePicker(r.sp3k_tgl,'#sp3k_tgl')
        setDatePicker(r.sp3k_tgl_exp,'#sp3k_tgl_exp')
        setDatePicker(r.rencana_akad_tgl,'#rencana_akad_tgl')
        setDatePicker(r.akad_tgl,'#akad_tgl')
        
      
        // if (r.refund_tgl != "0000-00-00")
        //     document.querySelector("#refund_tgl")._flatpickr.setDate(r.refund_tgl);

        $("#fm-mkdt .num").keyup().change(); //fomrat form number
        $("#status_mkdt").change(); //show/hide keterangan batal

        $("#mkdt_keterangan").val(r.keterangan);

        src = not_found;
        //load ktp npwp
        if (r.ktp_lok != null) {
          src = r.ktp_lok;
        }
        $("#file_ktp-here").prop("src", base_url +  src);

        //load npwp
        src = not_found;
        if (r.npwp_lok != null) {
          src = r.npwp_lok;
        }
        $("#file_npwp-here").prop("src", base_url +  src);

        src = not_found;
        //load bast
        if (r.bast_file != null) {
          src = r.bast_file;
        }
        $("#list-upload_bast_file").prop("href", base_url +  src);

        src = not_found;
        //load sp3k
        if (r.sp3k_file != null) {
          src = r.sp3k_file;
        }
        $("#list-upload_sp3k_file").prop("href", base_url +  src);

        src = not_found;
        //load perintah_bangun
        if (r.perintah_bangun_file != null) {
          src = r.perintah_bangun_file;
        }
        $("#list-upload_perintah_bangun_file").prop("href",base_url +  src);
      }
      $(".label_alamat").html(
        dt_proyek.nama_proyek +
          "<br/>" +
          sh.data.nama_jalan +
          ", No." +
          sh.data.no_kavling +
          "<br/>" +
          sh.data2.no_tipe_rumah +
          " (" +
          sh.data2.tipe_rumah +
          ")<br/>"
      );
      $("#modal_divisi" + role).modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (e) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: e.responseJSON.message,
        showConfirmButton: true,
        // timer: 1500
      });
      $("#loading").addClass("hidden");
    },
  });
}

$("#add-form-btn-mkdt").click(function (e) {
  e.preventDefault();
});

function save_mkdt(e) {
  if (!palid("fm-mkdt #nama_konsumen", "", "nama konsumen harus diisi")) return;
  if (!palid("fm-mkdt #status_mkdt", "", "Status harus diisi")) return;
  for (let a = 0; a <= it; a++) {
    if (
      !palid(
        "fm-mkdt #jatuh_tempo_tgl" + a,
        "",
        "Tanggal jatuh tempo harus diisi"
      )
    )
      return false;
    if (!palid("fm-mkdt #berita_acara1" + a, "", "Keterangan harus diisi"))
      return false;
  }

  // if ($("#total_cicilan").val() != $("#total_biaya2").val()) {
  //     Swal.fire({
  //         //position: 'bottom-end',
  //         icon: 'error',
  //         title: "Total Cicilan tidak sesuai dengan total biaya",
  //         showConfirmButton: false,
  //         timer: 1500
  //     });
  //     return false;
  // }

  // var files = $('#file_ktp')[0].files;
  var form = $("#fm-mkdt")[0];
  var fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "/mkdt/save",
    type: "post",
    // data: $("#fm-mkdt").serialize() + "&" + csrfName + "=" + csrfHash,
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      $("#add-form-btn-mkdt").prop("disabled", true);
      $("#add-form-btn-mkdt").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>'
      );
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $(".modal").modal("hide");
          $("#add-form-btn-mkdt").html("Simpan");
          $("#add-form-btn-mkdt").prop("disabled", false);
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#add-form-btn-mkdt").html("Simpan");
          $("#add-form-btn-mkdt").prop("disabled", false);
        });
      }
      load_kavling();
      hapus_seleksi();
    },
  });
}

function set_harga() {
  $.ajax({
    url: base_url + "/Hargajual/set_harga",
    type: "post",
    data: $("#fm-set_harga").serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
    dataType: "json",
    beforeSend: function () {
      $("#set-harga-form-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>'
      );
      $("#set-harga-form-btn").addClass("disabled");
    },
    success: function (response) {
      csrfHash = response.token;
      if (response.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#modals-set_harga").modal("hide");
          $("#set-harga-form-btn").html("Simpan");
          $("#set-harga-form-btn").removeClass("disabled");
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#set-harga-form-btn").html("Simpan");
          $("#set-harga-form-btn").removeClass("disabled");
        });
      }
      load_kavling();
      hapus_seleksi();
    },
  });
}

$("#sh-id").select2({
  placeholder: "Pilih Pricelist",
  allowClear: true,
  ajax: {
    url: base_url + "/Hargajual/getAll",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
        id_proyek: dt_proyek.id_proyek,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.data, function (k, v) {
        results.push({
          id: v.id,
          text:
            "Rp. " +
            num_format(v.hargajual) +
            " (" +
            v.tipe_rumah +
            ") ROW" +
            v.row +
            " : Per " +
            format_date(v.tgl_harga),
          row: v.row,
          tipe: v.tipe_rumah,
          lb: v.lb,
          lt: v.lt,
          hargajual: v.hargajual,
          hargajual_net: v.hargajual_net,
          kpr: v.kpr,
          uang_muka: v.uang_muka,
          bphtb: v.bphtb,
          ppn: v.ppn,
          biaya_adm: v.biaya_adm,
          biaya_proses: v.biaya_proses,
          id_tipe: v.id_tipe,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});
// on select cluster
$("#sh-id").on("select2:selecting", function (e) {
  var i = e.params.args.data;
  $.each(i, function (k, v) {
    $("#sh-" + k)
      .val(v)
      .change()
      .keyup();
  });
});
$("#sh-id").change(function () {
  if (!this.value) $(".sh-fm").val("");
});
function open_set_turun_pembangunan() {
  $("#list-tp-upload_perintah_bangun_file").prop("href", base_url +  not_found);
  $("#label-perintah_bangun_file").html("File Turun Perintah Bangun");
  if (editdtt.length == 0) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Tidak ada kavling terpilih ",
      showConfirmButton: true,
      // timer: 1500
    });
    return;
  }
  $("#fm-turun_pembangunan")[0].reset();

  let data = [];

  for (let a = 0; a < editdtt.length; a++) {
    data.push(editdtt[a].id.substr(3));
  }
  $.ajax({
    url: base_url + "/siteplan/get_turun_pembangunan",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: data,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (res) {
      csrfHash = res.token;
      let r = res.data,
        id_kavling = "",
        no = "";

      $(".select2").val(null).trigger("change");

      if (r.length > 0) {
        r.forEach((v) => {
          id_kavling += v.id_kavling + ";";
          no += `${v.nama_jalan} No. ${v.no_kavling} \n`;
        });

        $(".id_kavling").val(id_kavling);
        $("#tp-kavling").val(no);

        $("#tp-perintah_bangun_oleh").val(r[0].username);

        $("#list-tp-upload_perintah_bangun_file").prop(
          "href",
          base_url + "/" + r[0].perintah_bangun_file
        );

        setDatePicker(r[0].perintah_bangun_tgl, "#tp-perintah_bangun_tgl")
      }
      $("#loading").addClass("hidden");
      $("#modals-turun_pembangunan").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (err) {
      return Swal.fire({
        //position: 'bottom-end',
        icon: "danger",
        title: "terjadi kesalahan saat memuat data",
        showConfirmButton: false,
        timer: 1500,
      });
    },
  });
}
function set_tp() {
  if ($("#tp-perintah_bangun_tgl").val() == "") {
    return Swal.fire({
      icon: "error",
      title: "Tanggal Perintah Bangun harus diisi",
      showConfirmButton: false,
    });
  }
  let form = $("#fm-turun_pembangunan")[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "/siteplan/set_turun_pembangunan",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      $("#set-tp-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
      $("#set-tp-btn").addClass("disabled");
    },
    success: function (response) {
      csrfHash = response.token;
      if (response.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#modals-turun_pembangunan").modal("hide");
          $("#set-tp-btn").html("Simpan");
          $("#set-tp-btn").removeClass("disabled");
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#set-tp-btn").html("Simpan");
          $("#set-tp-btn").removeClass("disabled");
        });
      }
      load_kavling();
      hapus_seleksi();
    },
    error: function (err) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan saat menginput data",
        showConfirmButton: false,
      }).then(function () {
        $("#set-tp-btn").html("Simpan");
        $("#set-tp-btn").removeClass("disabled");
      });
    },
  });
}

function open_set_harga() {
  if (editdtt.length == 0) return;
  $("#fm-set_harga")[0].reset();

  let data = [];

  for (let a = 0; a < editdtt.length; a++) {
    data.push(editdtt[a].id.substr(3));
  }

  $.ajax({
    url: base_url + "/siteplan/get_harga_kavling",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: data,
    },
    dataType: "json",
    success: function (res) {
      csrfHash = res.token;
      let r = res.data,
        id_kavling = "",
        id_cluster,
        id_jalan,
        id_tipe,
        no = "",
        harga;

      $(".select2").val(null).trigger("change");
      if (r.length > 0) {
        for (let a = 0; a < r.length; a++) {
          id_kavling += r[a].id_kavling + ";";
          no += r[a].no_kavling + ";";
          id_cluster = r[a].id_cluster;
          id_jalan = r[a].id_jalan;
          id_tipe = r[a].id_tipe;
          harga = r[a].harga_akhir;

          if (r[a].harga_akhir) {
            $("#sh-id")
              .append(
                $("<option selected></option>")
                  .attr("value", r[a].harga_akhir)
                  .text(
                    "Rp. " +
                      num_format(r[a].hargajual) +
                      " (" +
                      r[a].tipe_rumah +
                      ")" +
                      ": " +
                      r[a].tgl_harga
                  )
              )
              .trigger("change");

            $("#sh-row").val(r[a].row).change();
            $("#sh-tipe").val(r[a].tipe_rumah).change();
            $("#sh-lb").val(r[a].hj_lb).change();
            $("#sh-lt").val(r[a].hj_lt).change();
            $("#sh-hargajual").val(r[a].hargajual).change();
            $("#sh-hargajual_net").val(r[a].hargajual_net).change();
            $("#sh-kpr").val(r[a].kpr).change();
            $("#sh-uang_muka").val(r[a].uang_muka).change();
            $("#sh-ppn").val(r[a].ppn).change();
            $("#sh-bphtb").val(r[a].bphtb).change();
            $("#sh-biaya_adm").val(r[a].biaya_adm).change();
            $("#sh-biaya_proses").val(r[a].biaya_proses).change();
          }
        }

        $(".id_kavling").val(id_kavling);
        $("#fm-set_harga #id_cluster").val(id_cluster);
        $("#fm-set_harga #id_jalan").val(id_jalan);
        $("#fm-set_harga #no_kavling").val(no);
        $("#fm-set_harga #id_tipe").val(id_tipe);
        $("#fm-set_harga #harga").val(harga).keyup();
      }
    },
  });

  $("#modals-set_harga").modal({
    backdrop: "static",
    keyboard: false,
  });
}

</script>
<script>
    
    /********************************* Legal *******************************************/
    $("#fl-btn-upload").click(function(e) {
        return e.preventDefault()
    })

    function removeDoc(e, id) {
        Swal.fire({
            title: 'Hapus Data?',
            text: "Apakah anda yakin akan menghapus data?",
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
                $.ajax({
                    url: base_url + '/Legal/removeDoc',
                    type: 'post',
                    data: {
                        [csrfName]: csrfHash,
                        id: e
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $("#loading").removeClass("hidden");
                    },
                    success: function(r) {
                        $("#loading").addClass("hidden");
                        csrfHash = r.token;
                        Swal.fire({
                            //position: 'bottom-end',
                            icon: 'success',
                            title: r.messages,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            load_file_upload(id)
                        })
                    },
                    error: function(e) {
                        Swal.fire({
                            //position: 'bottom-end',
                            icon: 'error',
                            title: "Terjadi Kesalahan",
                            showConfirmButton: true,
                            // timer: 1500
                        })
                    }
                });
            }
        })
    }

    function load_file_upload(id_kavling) {
        $.ajax({
            url: base_url + '/Legal/getDoc',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_kavling: id_kavling
            },
            dataType: 'json',
            success: function(r) {
                csrfHash = r.token;
                $("#tb-fl-file").html("");
                let tb = `<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>`
                if (r.data) {
                    tb = '';
                    let no = 0
                    $.each(r.data, function(i, v) {
                        no++
                        tb += `<tr>
                                <td>` + no + `</td>
                                <td>` + v.file_name + `</td>
                                <td>` + v.keterangan + `</td>
                                <td> <a href='` + base_url + '/' + v.lokasi + `' target=blank>Klik disini untuk mengunduh</a></td>
                                <td> ` + v.uadd_by + `</td>
                                <td> ` + format_datetime(v.upload_at) + ` </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="removeDoc('` + v.id + `', '` + id_kavling + `')"><i class="fa fa-trash"></i></button>
                                    </div>
                                </td>
                                </tr>`
                    });
                }
                $("#tb-fl-file").html(tb);
            },
            error: function() {

            }
        });

    }

    function fl_upload() {
        if (!$("#fl-file").val()) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Tidak ada file yang di upload",
                showConfirmButton: false,
                timer: 1500
            })
            return false;
        }

        var files = $('#fl-file')[0].files;
        var form = $('#fl-legal')[0];
        var fd = new FormData(form);
        fd.append(csrfName, csrfHash);
        fd.append('id_kavling', $(".id_kavling").val());

        $.ajax({
            url: base_url + '/Legal/upload',
            type: 'POST',
            contentType: false,
            processData: false,
            data: fd, // /converting the form data into array and sending it to server
            beforeSend: function() {
                $('#fl-btn-upload').html('<i class="fa fa-spinner fa-spin"></i> Mengunggah');
                $('#fl-btn-upload').prop('disabled', true);
            },
            success: function(response) {
                csrfHash = response.token;

                if (response.success === true) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: response.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        load_file_upload($(".id_kavling").val())
                        $("#fl-legal")[0].reset()
                        $("#fl-label").html('Pilih Berkas')
                    })
                } else {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: response.messages,
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
                $('#fl-btn-upload').html('Unggah');
                $('#fl-btn-upload').prop('disabled', false);
            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: response.messages,
                    showConfirmButton: false,
                })
                $('#fl-btn-upload').html('Unggah');
                $('#fl-btn-upload').prop('disabled', false);
            }
        });

    }

    function open_legal(sh, role, id_kavling) {
        if (sh.data.tipe == "kavling") {
            return open_flegal(sh, role, id_kavling);
        } else {
            return open_fotherlegal(sh)
        }
    }

    function open_flegal(sh, role, id_kavling) {
        $("#fm-legal")[0].reset();

        $(".id_kavling").val(id_kavling);
        $("#id_legal").val(sh.data.id_legal);

        $.ajax({
            url: base_url + '/legal/get_data_by_id',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_legal: sh.data.id_legal
            },
            dataType: 'json',
            success: function(r) {
                csrfHash = r.token;

                load_file_upload(id_kavling)
                if (r) {
                    for (let i in r) {
                        $("#" + i).val(r[i]);
                    }

                    // if (r.sertifikat_tgl != "0000-00-00")
                    //     document.querySelector("#sertifikat_tgl")._flatpickr.setDate(r.sertifikat_tgl);
                    // if (r.sertifikat_masa_berlaku != "0000-00-00")
                    //     document.querySelector("#sertifikat_masa_berlaku")._flatpickr.setDate(r.sertifikat_masa_berlaku);
                    // if (r.imb_tgl != "0000-00-00")
                    //     document.querySelector("#imb_tgl")._flatpickr.setDate(r.imb_tgl);
                    // if (r.bphtb_tgl != "0000-00-00")
                    //     document.querySelector("#bphtb_tgl")._flatpickr.setDate(r.bphtb_tgl);
                    // if (r.bphtb_masa_berlaku != "0000-00-00")
                    //     document.querySelector("#bphtb_masa_berlaku")._flatpickr.setDate(r.bphtb_masa_berlaku);
                    // if (r.bphtb_validasi != "0000-00-00")
                    //     document.querySelector("#bphtb_validasi")._flatpickr.setDate(r.bphtb_validasi);
                    // if (r.akad_tgl != "0000-00-00")
                    //     document.querySelector("#legal_akad_tgl")._flatpickr.setDate(r.akad_tgl);

                    $("#legal_keterangan").val(r.keterangan);

                }
                $(".label_alamat").html(dt_proyek.nama_proyek + "<br/> <span class='capitalize'>" + sh.data.tipe + "<span>: " + sh.data.nama_jalan + "");
                $('#modal_flegal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: false,
                })
                return;
            }
        });
    }

    function open_fotherlegal(sh) {
        $("#fm-fotherlegal")[0].reset()
        $("#fl_progres_jalan").val(0)
        $(".t_luas_planning .t_luas_produksi, .r_progres").html(" ")
        $.ajax({
            url: base_url + '/siteplan/get_others',
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
                    $(".id_kavling").val(d.id)
                    $(".t_luas_planning, .t_luas_produksi").html("-")



                    if (d.planning_luas)
                        $(".t_luas_planning").html(d.planning_luas + "  m&sup2  (" + d.planning_edit + ": " + format_datetime(d.planning_updated_at) + ")")
                    if (d.produksi_luas)
                        $(".t_luas_produksi").html(d.produksi_luas + "  m&sup2  (" + d.produksi_edit + ": " + format_datetime(d.produksi_updated_at) + ")")

                    $("#f_legal_luas").val(d.legal_luas)
                    $("#f_legal_keterangan").val(d.legal_keterangan)
                    $("#fl_progres_jalan").val(progres)
                    $(".r_progres").html(progres)

                }

            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: false,
                    timer: 1500
                })
                return;
            }
        });

        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/> <span class='capitalize'>" + sh.data.tipe + "<span>: " + sh.data.nama_jalan + "");
        $('#modal_fotherlegal').modal({
            backdrop: 'static',
            keyboard: false
        });
    }

    function save_legal() {
        $.ajax({
            url: base_url + '/legal/save',
            type: 'post',
            data: $("#fm-legal").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: 'json',
            beforeSend: function() {
                $('#add-form-btn-legal').prop("disabled", true);
                $('#add-form-btn-legal').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(r) {
                csrfHash = r.token;
                if (r.success === true) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        $('.modal').modal('hide');
                        $('#add-form-btn-legal').html('Simpan');
                        $('#add-form-btn-legal').prop("disabled", false);
                    })
                } else {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        $('#add-form-btn-legal').html('Simpan');
                        $('#add-form-btn-legal').prop("disabled", true);
                    })
                }
                load_kavling();
                hapus_seleksi();
            }
        });

    }

    function save_fotherlegal() {
        $.ajax({
            url: base_url + '/legal/edit_others',
            type: 'POST',
            // data: $("#fm-komplain-sales").serialize() + "&" + csrfName + "=" + csrfHash,
            data: $("#fm-fotherlegal").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: 'json',
            beforeSend: function() {
                $('#save-fother-btn-legal').prop('disabled', true);
                $('#save-fother-btn-legal').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(r) {
                csrfHash = r.token;

                if (r.success === true) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    })

                    $('.modal').modal('hide');
                    hapus_seleksi();
                    load_kavling();
                } else {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
                $('#save-fother-btn-legal').html('Simpan');
                $('#save-fother-btn-legal').prop('disabled', false);
            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan saat menginput data",
                    showConfirmButton: false,
                    timer: 1500
                })
                $('#save-fother-btn-legal').html('Simpan');
                $('#save-fother-btn-legal').prop('disabled', false);
            }
        });
    }
    /****************************** end of Legal ****************************************/

</script>
<script>
    /**************************** planning ***************************** */
    var act;
    //masking kavling on dbl click
    siteplan.on('dblclick dbltap', function(e) {
        if ($("#tambah_jalan").prop("checked"))
            return;

        let va = $("#pilih-divisi option:selected").val();

        //planning only
        // if (va != 6 roleid) {
        //     Swal.fire({
        //         //position: 'bottom-end',
        //         icon: 'error',
        //         title: "Ubah list data ke pilihan planning",
        //         showConfirmButton: false,
        //         timer: 1500
        //     })
        //     return;
        // }
        dtt = [];

        e = e.evt;

        allowDraw = true;
        addMode = e.ctrlKey;

        downPoint = stage.getPointerPosition();

        if (!addMode)
            hapus_seleksi();

        let a = stage.getAbsoluteTransform().copy();
        a.invert();
        let l = a.point(downPoint);

        let xy = {
            x: parseInt(l.x, 10),
            y: parseInt(l.y, 10)
        }

        drawMask(xy.x, xy.y);
    });

    //open modal untuk tambah kavling
    function tambah_kavling() {
        $(".t_luas_legal, .t_luas_produksi, .r_progres").html('-')
        $("#pindah_lokasi_btn").hide()
        act = "add";

        $("#fm-add_kavling")[0].reset();
        $('.select2').val(null).trigger('change');

        var shape
        if (editdtt.length > 0) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi Kesalahan.",
                text: "Lokasi sudah diisi oleh kavling lain",
                showConfirmButton: false,
            });
            return;
        } else if ($("#tambah_jalan").prop("checked")) {
            shape = dtt;
            batchdtt[0] = dtt

            if (shape.length < 6) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Seleksi manual minimal 3 titik",
                    showConfirmButton: false,
                    timer: 1500
                })
                return;
            }
        } else {
            shape = stage.find('#sel')[0];
            if (!shape) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Pilih kavling terlebih dahulu",
                    showConfirmButton: false,
                    timer: 1500
                })
                return;
            }
        }

        $('#status_tanah').val("Standar").trigger('change');


        $('#modals-slide-in').modal({
            backdrop: 'static',
            keyboard: false
        });
        $("#points").val(dtt);
    }

    // $("#add_kavling").click(function() {});
    function edit_kavling_batch() {
        if (editdtt.length == 0) return;
        $("#pindah_lokasi_btn").hide()
        if (editdtt.length == 1)
            $("#pindah_lokasi_btn").show()

        $(".t_luas_legal, .t_luas_produksi, .r_progres").html('-')

        let data,
            tipe;

        let url = base_url + "/siteplan/get_others";
        tipe = editdtt[0].data.tipe;
        data = editdtt[0].id.substr(6)

        if (tipe == "kavling") {
            data = [];
            url = base_url + '/siteplan/get_kavling_by_multiple_id';
            for (let a = 0; a < editdtt.length; a++) {
                data.push(editdtt[a].id.substr(3))
                tipe = editdtt[a].data.tipe;
            }
        }


        $("#fm-add_kavling")[0].reset();
        $('.select2').val(null).trigger('change');

        $.ajax({
            url: url,
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_kavling: data
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(res) {
                csrfHash = res.token;
                $("#id_jenis").val(tipe).change();

                let r = res.data,
                    id_kavling = "",
                    id_cluster,
                    id_jalan,
                    id_tipe,
                    no = "",
                    points = "";

                $("#id_cluster").append($("<option selected></option>").attr("value", r[0].id_cluster).text(r[0].nama_cluster)).trigger('change');
                $("#id_jalan").append($("<option selected></option>").attr("value", r[0].id_jalan).text(r[0].nama_jalan)).trigger('change');
                $("#id_tipe").append($("<option selected></option>").attr("value", r[0].id_tipe).text(r[0].no_tipe_rumah + " (" + r[0].tipe_rumah + ")")).trigger('change');


                if (tipe == "kavling") {
                    if (r.length > 0) {
                        for (let a = 0; a < r.length; a++) {
                            id_kavling += r[a].id_kavling + ";";
                            no += r[a].no_kavling + ";";
                            id_cluster = r[a].id_cluster;
                            id_jalan = r[a].id_jalan;
                            id_tipe = r[a].id_tipe;
                            points += r[a].points + ";"
                        }
                        $("#status_tanah").val(r[0].status_tanah).change();
                        $(".id_kavling").val(id_kavling);
                        $("#no_kavling").val(no);
                        $("#points").val(points)
                        $("#f_luas").val(r[0].luas_tanah);

                    }
                } else {
                    if (r.length > 0) {
                        $(".id_kavling").val(r[0].id);
                        $("#f_luas").val(r[0].planning_luas);
                        $("#f_nama").val(r[0].nama);
                        $("#f_planning_keterangan").val(r[0].planning_keterangan);

                        let d = r[0];

                        if (d.produksi_luas)
                            $(".t_luas_produksi").html(d.produksi_luas + "  m&sup2  (" + d.produksi_edit + ": " + format_datetime(d.produksi_updated_at) + ")")
                        if (d.legal_luas)
                            $(".t_luas_legal").html(d.legal_luas + "  m&sup2  (" + d.legal_edit + ": " + format_datetime(d.legal_updated_at) + ")")
                    }
                }

                $('#modals-slide-in').modal({
                    backdrop: 'static',
                    keyboard: false
                });

            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: false,
                    timer: 1500
                })
                return;
            }
        });

        $("#loading").addClass("hidden");
        act = "edit"
    }

    function open_planning(sh, role, id_kavling) {
        $("#fm-add_kavling")[0].reset();
        $(".id_kavling").val(id_kavling);
        $(".t_luas_legal, .t_luas_produksi, .r_progres").html('-')

        $.ajax({
            url: base_url + '/siteplan/get_kavling_by_id',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_kavling: id_kavling
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(res) {
                csrfHash = res.token;
                let r = res.data;
                if (r) {
                    for (let i in r) {
                        $("#fm-add_kavling #" + i).val(r[i]);
                    }
                    // $('.id_cluster').append($("<option selected></option>").attr("value",r['id_cluster']).text(r['nama_cluster']));
                    // var id_cluster = new Option(r.nama_cluster, r.id_cluster, false, false);
                    // $('.id_cluster').append(newOption).trigger('change');
                    // $('.id_cluster').trigger({
                    //     type: 'select2:select',
                    //     params: {
                    //         data: r
                    //     }
                    // });

                    $('#modals-slide-in-edit').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }
            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: false,
                    timer: 1500
                })
                return;
            }
        });
        $("#loading").addClass("hidden");
    }

    function edit_kavling() {
        let no_kav = $("#fm-add_kavling #no_kavling").val().split(";"),
            no_kavlen = (no_kav[no_kav.length - 1] == '') ? no_kav.length - 1 : no_kav.length,
            tipe = editdtt[0].data.tipe,
            url = base_url + '/siteplan/edit_others';

        //jika no kavling dan selection tidak sesuai
        if (tipe == 'kavling') {
            if (editdtt.length > 0) {
                if (editdtt.length != no_kavlen) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: "Terjadi Kesalahan.",
                        text: "Jumlah Kavling yang dipilih: " + editdtt.length + "\n" + "Jumlah No Kavling yang diisi: " + no_kavlen,
                        showConfirmButton: false,
                    });
                    return;
                }
            }
            url = base_url + '/siteplan/edit_kavling';
        }

        $.ajax({
            url: url,
            type: 'post',
            data: $("#fm-add_kavling").serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
            dataType: 'json',
            beforeSend: function() {
                $('#add-form-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
                $('#add-form-btn').addClass("disabled");
            },
            success: function(response) {
                csrfHash = response.token;
                if (response.success === true) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: response.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        $('#modals-slide-in').modal('hide');
                        $('#add-form-btn').html('Simpan');
                        $('#add-form-btn').removeClass("disabled");
                    })
                } else {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: response.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        $('#add-form-btn').html('Simpan');
                        $('#add-form-btn').removeClass("disabled");
                    })
                }
                load_kavling();
                hapus_seleksi();
            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan",
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    $('#add-form-btn').html('Simpan');
                    $('#add-form-btn').removeClass("disabled");
                })
            }
        });
    }

    //proses tambah kavling ke db
    function add_kavling() {
        if (act == "edit")
            return edit_kavling();

        if ($("#id_jenis").val() == "") {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Jenis harus diisi",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }
        if (!$("#id_cluster").val()) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Cluster harus diisi",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }
        if (!$("#id_jalan").val()) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "jalan harus diisi",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }


        $(".form-control").removeClass('is-invalid').removeClass('is-valid');
        let par = "";

        for (let z = 0; z < batchdtt.length; z++) {
            par += "&bpoints[]=" + batchdtt[z];
        }

        //jika no kavling terakhir kosong
        let no_kav = $("#fm-add_kavling #no_kavling").val().split(";"),
            no_kavlen = (no_kav[no_kav.length - 1] == '') ? no_kav.length - 1 : no_kav.length;

        if ($("#id_jenis").val() == "kavling") {
            //jika no kavling dan selection tidak sesuai
            if (batchdtt.length != no_kavlen) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi Kesalahan.",
                    text: "Jumlah Kavling yang dipilih: " + batchdtt.length + "\n" + "Jumlah No Kavling yang diisi: " + no_kavlen,
                    showConfirmButton: false,
                });
                return;
            }

        }

        $.ajax({
            url: base_url + '/siteplan/add_kavling',
            type: 'post',
            data: $("#fm-add_kavling").serialize() + par + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
            dataType: 'json',
            beforeSend: function() {
                $('#add-form-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
                $('#add-form-btn').addClass("disabled");
            },
            success: function(response) {
                csrfHash = response.token;

                if (response.success === true) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: response.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        $('#modals-slide-in').modal('hide');
                        load_kavling()
                        hapus_seleksi()
                    })
                } else {
                    // $('#modals-slide-in').modal('hide');
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: response.messages,
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
                $('#add-form-btn').html('Simpan');
                $('#add-form-btn').removeClass("disabled");
            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi Kesalahan saat melakukan penambahan data kaving, jalan atau fasos",
                    showConfirmButton: false,
                    timer: 1500

                })
                $('#add-form-btn').html('Simpan');
                $('#add-form-btn').removeClass("disabled");
            }
        });
    }

    var editdtt_tmp;

    function pindah_kavling() {
        editdtt_tmp = editdtt;
        $('#modals-slide-in').modal('hide');
        $('#add_kavling, #edit_kavling_batch, #planning_toggle_btn').hide()
        $('#selesai_pindah_btn, #batal_pindah_btn').show()
        hapus_seleksi()
    }

    function selesai_selection(e) {
        if (e == 1) {
            if (dtt == "") {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Tidak ada lokasi yang dipilih",
                    showConfirmButton: false,
                    timer: 1500
                })
                return;
            }

            $("#points").val(dtt)
        }
        editdtt = editdtt_tmp;

        $('#modals-slide-in').modal('show');
        $('#add_kavling, #edit_kavling_batch, #planning_toggle_btn').show()
        $('#selesai_pindah_btn, #batal_pindah_btn').hide()

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
            cache: false
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

    $("#id_tipe").select2({
        placeholder: "Pilih Tipe",
        allowClear: true,
        ajax: {
            url: base_url + "/tipe/getAll",
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
                        text: item[2] + "(" + item[3] + ")"
                    });
                });

                return {
                    results: results
                };
            },
            cache: true
        },
    })

    $("#status_tanah").select2()

    $("#id_jenis").select2()
    $("#id_jenis").change(function() {
        if (this.value == "") {
            $(".h").hide()
        } else if (this.value == "kavling") {
            $(".h").hide()
            $("#div_kavling").show()
        } else if (this.value == "jalan") {
            $(".h").hide()
            $("#div_jalan, #div_luas").show()
        } else if (this.value == "fasos" || this.value == "rth") {
            $(".h").hide()
            $("#div_jalan, #div_fasos").show()
        } else {
            $(".h").hide()
        }

    })

    /**************************** planning ***************************** */
</script>
<script>
    
    /******************************** produksi ******************************************/
    //cekbok produksi
    var slo = 0,
        bp = 0,
        jalan = 0,
        lpa = 0,
        tot = 0,
        saluran = 0,
        pondasi = 0,
        topping_off = 0,
        naik_dinding = 0,
        finishing = 0;

    $("#pondasi").change(function() {
        pondasi = (this.checked) ? 1 : 0;
    })
    $("#naik_dinding").change(function() {
        naik_dinding = (this.checked) ? 1 : 0;
    })
    $("#topping_off").change(function() {
        topping_off = (this.checked) ? 1 : 0;
    })
    $("#finishing").change(function() {
        finishing = (this.checked) ? 1 : 0;
    })
    $("#slo").change(function() {
        slo = (this.checked) ? 1 : 0;
    })
    $("#saluran").change(function() {
        saluran = (this.checked) ? 1 : 0;
    })
    $("#bp").change(function() {
        bp = (this.checked) ? 1 : 0;
    })
    $("#jalan").change(function() {
        jalan = (this.checked) ? 1 : 0;
    })
    $("#lpa").change(function() {
        lpa = (this.checked) ? 1 : 0;
    })

    $(".cbp").change(function() {
        ftot()
        cekstprod()
        tot = tot / 9 * 100

        // $("#progres_bangunan").val(tot.toFixed(2))
        // $("#t_progres_bangunan").html(tot.toFixed(2))
    })
    $("#slf_jenis").change(function(){
        if(this.value == "SLF"){
            $("#slf-input-form").removeClass("hidden")
            $("#surat_pernyataan-input-form").addClass("hidden")
        }else{
            $("#slf-input-form").addClass("hidden")
            $("#surat_pernyataan-input-form").removeClass("hidden")
        }
    })
    $("#listrik_jenis").change(function(){
        if(this.value == "PLN"){
            $("#listrik-pln-input-form").removeClass("hidden")
            $("#listrik_disediakan").addClass("hidden")
        }else{
            $("#listrik-pln-input-form").addClass("hidden")
            $("#listrik_disediakan").removeClass("hidden")
        }
    })
    $("#air_jenis").change(function(){
        if(this.value == "Air Tanah"){
            $("#air_tanah_input_form").removeClass("hidden")
            $("#air_komunal_input_form").addClass("hidden")
            $("#air_pdam_input_form").addClass("hidden")
        }else if(this.value == "Komunal Warga"){
            $("#air_tanah_input_form").addClass("hidden")
            $("#air_komunal_input_form").removeClass("hidden")
            $("#air_pdam_input_form").addClass("hidden")
        }else{
            $("#air_tanah_input_form").addClass("hidden")
            $("#air_komunal_input_form").addClass("hidden")
            $("#air_pdam_input_form").removeClass("hidden")
        }
    })

    $("#progres_bangunan").on("input", function(){
        $("#t_progres_bangunan").html($(this).val())
    })

    function ftot() {
        return tot = slo + bp + jalan + lpa + saluran + pondasi + topping_off + naik_dinding + finishing;
    }

    function cekstprod() {
        if (
            $("#pondasi").prop("checked") &&
            $("#naik_dinding").prop("checked") &&
            $("#topping_off").prop("checked") &&
            $("#finishing").prop("checked") &&
            $("#saluran").prop("checked") &&
            $("#jalan").prop("checked")
        )
            $(".af .cbp").prop("disabled", false)
        else
            $(".af .cbp").prop("disabled", true)
    }

    function save_produksi() {
        $.ajax({
            url: base_url + '/produksi/save',
            type: 'post',
            data: $("#fm-produksi").serialize() + "&" + csrfName + "=" + csrfHash + "&progres_bangunan=" + $("#t_progres_bangunan").html(),
            dataType: 'json',
            beforeSend: function() {
                $('#add-form-btn-produksi').prop('disabled', true);
                $('#add-form-btn-produksi').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(r) {
                csrfHash = r.token;
                // $('#add-form-btn-produksi').prop('disabled', false);
                // return;
                if (r.success === true) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        $('.modal').modal('hide');
                        $('#add-form-btn-produksi').html('Simpan');
                        $('#add-form-btn-produksi').prop('disabled', false);
                    })
                } else {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        $('#add-form-btn-produksi').html('Simpan');
                        $('#add-form-btn-produksi').prop('disabled', false);
                    })
                }
                load_kavling();
                hapus_seleksi();
            }
        });

    }
    $("#terima_komplain").change(function() {
        if (this.checked) {
            $("#terima_komplain_div").removeClass("hidden");
        } else {
            $("#terima_komplain_div").addClass("hidden", true)
        }
    });

    function open_komplain_produksi() {
        if (!editdtt[0]) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Pilih salahsatu kavling",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }

        var sh = editdtt[0],
            id_kavling = sh.id.substr(3);

        if (!sh.data2.id_komplain) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Tidak ada komplain",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }

        $("#fm-komplain-produksi")[0].reset();

        $("#fm-komplain-produksi #foto_komplain_sales, #fm-komplain-produksi #foto_komplain_produksi").html("");

        $(".ditangani_form, #selesaikan_komplain_div, #komplain_selesai_btn_produksi").addClass("hidden", true);
        $("#keterangan_ditangani").prop("readonly", false);
        $('#komplain-produksi-form-btn').prop('disabled', false);

        $("#terima_komplain, #is_selesai_produksi").attr('onclick', "");
        $("#fm-komplain-produksi #keterangan_ditangani").prop("disabled", false);
        $("#fm-komplain-produksi #selesai_keterangan_produksi").prop('disabled', false);

        $("#komplain_selesai_sip").addClass('hidden');

        $("#last_update_komplain_produksi").html('Terakhir diupdate oleh: -, pada: -');

        $(".id_kavling").val(id_kavling);
        $("#fm-komplain-produksi #id_komplain").val(sh.data2.id_komplain);

        $.ajax({
            url: base_url + '/produksi/get_data_komplain_by_id',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_komplain: sh.data2.id_komplain,
                id_kavling: id_kavling
            },
            dataType: 'json',
            success: function(r) {
                csrfHash = r.token;
                let st = r.komplain,
                    fotok,
                    fotok_display = "",
                    fotokp,
                    fotokp_display = "";

                if (st) {

                    //display foto komplain dari sales
                    fotok = st.upload_komplain_sales;

                    // console.log(fotok)
                    if (fotok)
                        fotok = fotok.split(";")

                    if (Array.isArray(fotok)) {

                        let is_active = "active";
                        for (let e = 0; e < (fotok.length - 1); e++) {
                            if (e > 0)
                                is_active = "";

                            fotok_display += '<div class="carousel-item ' + is_active + '">' +
                                '<img class="d-block w-100 ft_kom" src="' + base_url + "/" + fotok[e] + '" alt="First slide">' +
                                '</div>';
                        }
                    }
                    $("#fm-komplain-produksi #foto_komplain_sales").html(fotok_display);

                    //display foto penyelsaian dari produksi
                    fotokp = st.upload_komplain_produksi;
                    if (fotokp)
                        fotokp = fotokp.split(";")

                    if (Array.isArray(fotokp)) {

                        let is_active = "active";
                        for (let e = 0; e < (fotokp.length - 1); e++) {
                            if (e > 0)
                                is_active = "";

                            fotokp_display += '<div class="carousel-item ' + is_active + '">' +
                                '<img class="d-block w-100 ft_kom" src="' + base_url + "/" + fotokp[e] + '" alt="First slide">' +
                                '</div>';
                        }
                    }
                    $("#fm-komplain-produksi #foto_komplain_produksi").html(fotokp_display);


                    //komplain
                    $("#fm-komplain-produksi #keterangan_komplain").val(st.keterangan_komplain);
                    $("#fm-komplain-produksi #username_komplain_oleh").val(st.username_komplain_oleh);

                    if (st.komplain_tgl != "0000-00-00")
                        document.querySelector("#fm-komplain-produksi #komplain_tgl")._flatpickr.setDate(st.komplain_tgl);

                    $("#last_update_komplain_produksi").html('Terakhir diupdate oleh: ' + st.username_last_update + ', pada: ' + format_datetime(st.updated_at));

                    //ditangani
                    if (st.status_komplain == 2) {
                        $("#terima_komplain").attr('onclick', "return false;");
                        $("#terima_komplain").prop("checked", true);

                        $(".ditangani_form, #selesaikan_komplain_div").removeClass("hidden")

                        $("#fm-komplain-produksi #keterangan_ditangani").val(st.keterangan_ditangani);
                        $("#fm-komplain-produksi #username_ditangani_oleh").val(st.username_ditangani_oleh);

                        if (st.ditangani_tgl != "0000-00-00")
                            document.querySelector("#fm-komplain-produksi #ditangani_tgl")._flatpickr.setDate(st.ditangani_tgl);
                    } else if (st.status_komplain == 3) {
                        $("#terima_komplain").attr('onclick', "return false;");
                        $("#terima_komplain").prop("checked", true);

                        $("#is_selesai_produksi").attr('onclick', "return false;");
                        $("#is_selesai_produksi").prop("checked", true);

                        $("#keterangan_ditangani").prop("readonly", true);


                        $(".ditangani_form, #selesaikan_komplain_div").removeClass("hidden")

                        $("#fm-komplain-produksi #keterangan_ditangani").val(st.keterangan_ditangani);
                        $("#fm-komplain-produksi #username_ditangani_oleh").val(st.username_ditangani_oleh);

                        if (st.ditangani_tgl != "0000-00-00")
                            document.querySelector("#fm-komplain-produksi #ditangani_tgl")._flatpickr.setDate(st.ditangani_tgl);

                        $("#fm-komplain-produksi #selesai_keterangan_produksi").val(st.selesai_keterangan_produksi);
                        $("#fm-komplain-produksi #username_selesai_oleh_produksi").val(st.username_selesai_oleh_produksi);

                        if (st.selesai_tgl_produksi != "0000-00-00")
                            document.querySelector("#fm-komplain-produksi #selesai_tgl_produksi")._flatpickr.setDate(st.selesai_tgl_produksi);
                    } else if (st.status_komplain == 4) {
                        $("#terima_komplain").attr('onclick', "return false;");
                        $("#terima_komplain").prop("checked", true);

                        $("#is_selesai_produksi").attr('onclick', "return false;");
                        $("#is_selesai_produksi").prop("checked", true);

                        $('#komplain-produksi-form-btn').prop('disabled', true);

                        $("#keterangan_ditangani").prop("readonly", true);

                        $("#fm-komplain-produksi #selesai_keterangan_produksi").prop('disabled', true);

                        $("#fm-komplain-produksi #keterangan_ditangani").prop("disabled", true);


                        $(".ditangani_form, #selesaikan_komplain_div, #komplain_selesai_btn_produksi").removeClass("hidden")

                        $("#fm-komplain-produksi #keterangan_ditangani").val(st.keterangan_ditangani);
                        $("#fm-komplain-produksi #username_ditangani_oleh").val(st.username_ditangani_oleh);

                        if (st.ditangani_tgl != "0000-00-00")
                            document.querySelector("#fm-komplain-produksi #ditangani_tgl")._flatpickr.setDate(st.ditangani_tgl);

                        $("#fm-komplain-produksi #selesai_keterangan_produksi").val(st.selesai_keterangan_produksi);
                        $("#fm-komplain-produksi #username_selesai_oleh_produksi").val(st.username_selesai_oleh_produksi);

                        if (st.selesai_tgl_produksi != "0000-00-00")
                            document.querySelector("#fm-komplain-produksi #selesai_tgl_produksi")._flatpickr.setDate(st.selesai_tgl_produksi);

                        $("#komplain_selesai_sip").removeClass('hidden');

                        $("#fm-komplain-produksi #selesai_keterangan_sales").val(st.selesai_keterangan_sales);
                        $("#fm-komplain-produksi #username_selesai_oleh_sales").val(st.username_selesai_oleh_sales);

                        if (st.selesai_tgl_sales != "0000-00-00")
                            document.querySelector("#fm-komplain-produksi #selesai_tgl_sales")._flatpickr.setDate(st.selesai_tgl_sales);
                    }
                }
                $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
                $('#modal_komplain_produksi').modal({
                    backdrop: 'static',
                    keyboard: false
                });

            },
            error: function() {},

        });
    }

    function save_komplain_produksi() {
        var files = $('#upload_komplain_sales')[0].files;
        var form = $('#fm-komplain-produksi')[0];
        var fd = new FormData(form);
        fd.append(csrfName, csrfHash);

        $.ajax({
            url: base_url + '/produksi/save_komplain_produksi',
            type: 'POST',
            contentType: false,
            processData: false,
            // data: $("#fm-komplain-sales").serialize() + "&" + csrfName + "=" + csrfHash,
            data: fd,
            dataType: 'json',
            beforeSend: function() {
                $('#komplain-produksi-form-btn').prop('disabled', true);
                $('#komplain-produksi-form-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(r) {
                csrfHash = r.token;

                if (r.success === true) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    })

                    $('.modal').modal('hide');
                    hapus_seleksi();
                    load_kavling();
                } else {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
                $('#komplain-produksi-form-btn').html('Simpan');
                $('#komplain-produksi-form-btn').prop('disabled', false);
            }
        });
    }
    //open form add/edit
    function open_produksi(sh, role, id_kavling) {
        if (sh.data.tipe == "kavling") {
            return open_fproduksi(sh, role, id_kavling);
        } else {
            return open_fotherproduksi(sh)
        }
    }

    function save_fotherproduksi() {
        $.ajax({
            url: base_url + '/produksi/edit_others',
            type: 'POST',
            // data: $("#fm-komplain-sales").serialize() + "&" + csrfName + "=" + csrfHash,
            data: $("#fm-fotherproduksi").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: 'json',
            beforeSend: function() {
                $('#save_fotherproduksi-btn').prop('disabled', true);
                $('#save_fotherproduksi-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(r) {
                csrfHash = r.token;

                if (r.success === true) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    })

                    $('.modal').modal('hide');
                    hapus_seleksi();
                    load_kavling();
                } else {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
                $('#save_fotherproduksi-btn').html('Simpan');
                $('#save_fotherproduksi-btn').prop('disabled', false);
            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan saat menginput data",
                    showConfirmButton: false,
                    timer: 1500
                })
                $('#save_fotherproduksi-btn').html('Simpan');
                $('#save_fotherproduksi-btn').prop('disabled', false);
            }
        });
    }
    $("#save_fotherproduksi-btn").click(function(e) {
        e.preventDefault();
    });

    function open_fotherproduksi(sh) {
        $("#fm-fotherproduksi")[0].reset()
        $("#f_progres_jalan").val(0)
        $(".t_luas_legal, .t_luas_produksi, .r_progres").html(" ")
        $.ajax({
            url: base_url + '/siteplan/get_others',
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
                    $(".id_kavling").val(d.id)
                    $(".t_luas_legal, .t_luas_produksi").html("-")



                    if (d.planning_luas)
                        $(".t_luas_planning").html(d.planning_luas + "  m&sup2  (" + d.planning_edit + ": " + format_datetime(d.planning_updated_at) + ")")
                    if (d.legal_luas)
                        $(".t_luas_legal").html(d.legal_luas + "  m&sup2  (" + d.legal_edit + ": " + format_datetime(d.legal_updated_at) + ")")

                    $("#f_produksi_luas").val(d.produksi_luas)
                    $("#f_produksi_keterangan").val(d.produksi_keterangan)
                    $("#f_progres_jalan").val(progres)
                    $(".r_progres").html(progres)

                }

            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: false,
                    timer: 1500
                })
                return;
            }
        });

        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/> <span class='capitalize'>" + sh.data.tipe + "<span>: " + sh.data.nama_jalan + "");
        $('#modal_fothersproduksi').modal({
            backdrop: 'static',
            keyboard: false
        });
    }

    function open_fproduksi(sh, role, id_kavling) {
        pondasi = 0, topping_off = 0, naik_dinding = 0, finishing = 0, slo = 0, bp = 0, jalan = 0, lpa = 0, tot = 0, saluran = 0;

        $(".af .cbp").prop("disabled", true)

        $("#t_progres_bangunan").html("0")
        $("#fm-produksi")[0].reset();
        $("#last_update_checklist_prod").html('Terakhir diupdate oleh: -, pada: -');

        $(".id_kavling").val(id_kavling);
        $("#id_produksi").val(sh.data.id_produksi);

        $("#download_gambar_kerja").attr("onclick", "download(" + sh.data2.id_gambar_kerja + ")")

        $.ajax({
            url: base_url + '/produksi/get_data_by_id',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_produksi: sh.data.id_produksi,
                id_kavling: id_kavling
            },
            dataType: 'json',
            success: function(r) {
                csrfHash = r.token;
                let cl = r.cl,
                    pb = 0;
                if (r) {

                    if (r.progres_bangunan) {
                        $("#progres_bangunan").val(r.progres_bangunan);
                        $("#t_progres_bangunan").html(r.progres_bangunan)
                    }

                    pondasi = (r.pondasi == 1) ? 1 : 0;
                    $("#pondasi").prop('checked', pondasi).change();
                    topping_off = (r.topping_off == 1) ? 1 : 0;
                    $("#topping_off").prop('checked', topping_off).change();
                    naik_dinding = (r.naik_dinding == 1) ? 1 : 0;
                    $("#naik_dinding").prop('checked', naik_dinding).change();
                    finishing = (r.finishing == 1) ? 1 : 0;
                    $("#finishing").prop('checked', finishing).change();

                    jalan = (r.jalan == 1) ? 1 : 0;
                    $("#jalan").prop('checked', jalan).change();
                    slo = (r.slo == 1) ? 1 : 0;
                    $("#slo").prop('checked', slo).change();
                    bp = (r.bp == 1) ? 1 : 0;
                    $("#bp").prop('checked', bp).change();
                    lpa = (r.lpa == 1) ? 1 : 0;
                    $("#lpa").prop('checked', lpa).change();
                    saluran = (r.saluran == 1) ? 1 : 0;
                    $("#saluran").prop('checked', saluran).change();

                    if (cl) {
                        if (cl.length == 0)
                            return;
                        let lates_date = cl[0].produksi_cek_tgl;
                        $.each(cl, function(key, val) {
                            if (val.hasil_cek_t == 1) {
                                $("#hasil_cek_t\\[" + val.id_subitem + "\\]").prop('checked', true);
                            }
                            if (val.hasil_cek_f == 1) {
                                $("#hasil_cek_f\\[" + val.id_subitem + "\\]").prop('checked', true);
                            }
                            if (val.hasil_cek_v == 1) {
                                $("#hasil_cek_v\\[" + val.id_subitem + "\\]").prop('checked', true);
                            }
                            // $("#hasil_cek_t\\[" + val.id_subitem + "\\]").val(val.hasil_cek_t);
                            // $("#hasil_cek_f\\[" + val.id_subitem + "\\]").val(val.hasil_cek_f);
                            // $("#hasil_cek_v\\[" + val.id_subitem + "\\]").val(val.hasil_cek_v);
                            $("#keterangan_cek_produksi\\[" + val.id_subitem + "\\]").val(val.keterangan_cek_produksi);

                            if (lates_date < val.produksi_cek_tgl)
                                lates_date = val.produksi_cek_tgl;
                        })
                        $("#last_update_checklist_prod").html('Terakhir diupdate oleh: ' + cl[0].username + ', pada: ' + format_date(lates_date));
                    }

                    $("#produksi_keterangan").val(r.keterangan);
                }
            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: false,
                    timer: 1500
                })
                return;
            }
        });
        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
        $('#modal_divisi' + role).modal({
            backdrop: 'static',
            keyboard: false
        });

    }

    function download(e) {
        (async () => {
            const rawResponse = await fetch(base_url + '/produksi/get_gambarkerja', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        [csrfName]: csrfHash,
                        id_gambar_kerja: e,
                        pass: "password"
                    })
                })
                .then(resp => resp.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;

                    // the filename you want
                    a.download = 'gambar kerja.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(() => alert('oh no!'));

        })();

    }

</script>
<script>
    
function open_checklist_sales() {
    if (!editdtt[0]) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Pilih salahsatu kavling",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }


    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    $("#fm-checklist-sales")[0].reset();
    //reset keterangan last update
    $("#last_update_checklist_prod2").html('Terakhir diupdate oleh: -, pada: -');
    $("#last_update_checklist_sales").html('Terakhir diupdate oleh: -, pada: -');

    $(".id_kavling").val(id_kavling);
    $("#id_sales").val(sh.data.id_sales);


    $.ajax({
        url: base_url + '/sales/get_data_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_sales: sh.data.id_sales,
            id_kavling: id_kavling
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;
            let cl = r.cl;
            if (r.kav.is_checked == 1) {
                $("#is_checked").prop('checked', true);
            }
            if (cl.length > 0) {
                let lates_date = cl[0].produksi_cek_tgl,
                    lates_date_sales = cl[0].sales_cek_tgl;
                $.each(cl, function(key, val) {
                    //load form ceklst produksi
                    if (val.hasil_cek_t == 1)
                        $("#hasil_cek_t\\[" + val.id_subitem + "\\]").prop('checked', true);
                    if (val.hasil_cek_f == 1)
                        $("#hasil_cek_f\\[" + val.id_subitem + "\\]").prop('checked', true);
                    if (val.hasil_cek_v == 1)
                        $("#hasil_cek_v\\[" + val.id_subitem + "\\]").prop('checked', true);

                    // $("#hasil_cek_t\\[" + val.id_subitem + "\\]").val(val.hasil_cek_t);
                    // $("#hasil_cek_f\\[" + val.id_subitem + "\\]").val(val.hasil_cek_f);
                    // $("#hasil_cek_v\\[" + val.id_subitem + "\\]").val(val.hasil_cek_v);
                    $("#keterangan_cek_produksi\\[" + val.id_subitem + "\\]").val(val.keterangan_cek_produksi);

                    //load form ceklst sales
                    if (val.hasil_cek_t_s == 1)
                        $("#hasil_cek_t_s\\[" + val.id_subitem + "\\]").prop('checked', true);
                    if (val.hasil_cek_f_s == 1)
                        $("#hasil_cek_f_s\\[" + val.id_subitem + "\\]").prop('checked', true);
                    if (val.hasil_cek_v_s == 1)
                        $("#hasil_cek_v_s\\[" + val.id_subitem + "\\]").prop('checked', true);
                    // $("#hasil_cek_t_s\\[" + val.id_subitem + "\\]").val(val.hasil_cek_t_s);
                    // $("#hasil_cek_f_s\\[" + val.id_subitem + "\\]").val(val.hasil_cek_f_s);
                    // $("#hasil_cek_v_s\\[" + val.id_subitem + "\\]").val(val.hasil_cek_v_s);
                    $("#keterangan_cek_sales\\[" + val.id_subitem + "\\]").val(val.keterangan_cek_sales);

                    if (lates_date < val.produksi_cek_tgl)
                        lates_date = val.produksi_cek_tgl;
                    if (lates_date_sales < val.sales_cek_tgl)
                        lates_date_sales = val.sales_cek_tgl;
                })
                $("#last_update_checklist_prod2").html('Terakhir diupdate (produksi) oleh: ' + cl[0].username_prod + ', pada: ' + format_date(lates_date));
                $("#last_update_checklist_sales").html('Terakhir diupdate (sales) oleh: ' + cl[0].username_sales + ', pada: ' + format_date(lates_date_sales));
            }
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#checklist_modal_sales').modal({
                backdrop: 'static',
                keyboard: false
            });

        },
        error: function() {},

    });
}

function save_checklist_sales() {
    $.ajax({
        url: base_url + '/sales/save_checklist',
        type: 'post',
        data: $("#fm-checklist-sales").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: 'json',
        beforeSend: function() {
            $('#checklist-form-btn-sales').prop('disabled', true);
            $('#checklist-form-btn-sales').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
        },
        success: function(r) {
            csrfHash = r.token;

            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    $('.modal').modal('hide');
                    $('#checklist-form-btn-sales').html('Simpan');
                    $('#checklist-form-btn-sales').prop('disabled', false);
                })
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    $('#checklist-form-btn-sales').html('Simpan');
                    $('#checklist-form-btn-sales').prop('disabled', false);
                })
            }
            load_kavling();
            hapus_seleksi();
        }
    });
}


//menampilkan list file yang akan diupload
let fuks = document.getElementById("upload_komplain_sales"),
    fluks = [],
    flduks = "";

fuks.addEventListener("change", function(e) {
    fluks = [];
    flduks = "";
    $("#list_upload_komplain_sales").html();
    for (let p = 0; p < fuks.files.length; p++) {
        fluks.push(fuks.files[p]);

        flduks += "<p>" + (p + 1) + ": " + fuks.files[p].name + " </p>";
        $("#list_upload_komplain_sales").html(flduks);
    }

});

function open_komplain_sales() {
    if (!editdtt[0]) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Pilih salahsatu kavling",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }
    $("#fm-komplain-sales #foto_komplain_sales").html("")
    $("#list_upload_komplain_sales").html("");
    $("#label_upload_komplain_sales").html("Bisa Upload lebih dari 1 foto");


    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    $("#fm-komplain-sales")[0].reset();

    $("#last_update_komplain_sales").html('Terakhir diupdate oleh: -, pada: -');

    $("#batal_komplain_btn").addClass("btn-outline-danger")
    $("#batal_komplain_btn").removeClass("btn-outline-primary")
    $("#batal_komplain_btn").html("Batalkan Komplain")

    $("#batal_komplain, #selesaikan_komplain_div_sales, #komplain_ditangani_sales").addClass('hidden', true);
    $("#batal_komplain_btn, #komplain-sales-form-btn, #fm-komplain-sales #keterangan_komplain, #fm-komplain-sales #selesai_keterangan_sales").prop('disabled', false);

    $(".id_kavling").val(id_kavling);
    $("#fm-komplain-sales #id_komplain").val(sh.data2.id_komplain);


    $.ajax({
        url: base_url + '/sales/get_data_komplain_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_komplain: sh.data2.id_komplain,
            id_kavling: id_kavling
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;
            let st = r.komplain,
                fotok, fotokp,
                fotok_display = "",
                fotokp_display = "";




            if (st) {

                fotok = st.upload_komplain_sales;
                if (fotok)
                    fotok = fotok.split(";")

                if (Array.isArray(fotok)) {

                    let is_active = "active";
                    for (let e = 0; e < (fotok.length - 1); e++) {
                        if (e > 0)
                            is_active = "";

                        fotok_display += '<div class="carousel-item ' + is_active + '">' +
                            '<img class="d-block w-100 ft_kom" src="' + base_url + "/" + fotok[e] + '" alt="First slide">' +
                            '</div>';
                    }
                }
                $("#fm-komplain-sales #foto_komplain_sales").html(fotok_display);

                //display foto penyelsaian dari produksi
                fotokp = st.upload_komplain_produksi;
                if (fotokp)
                    fotokp = fotokp.split(";")

                if (Array.isArray(fotokp)) {

                    let is_active = "active";
                    for (let e = 0; e < (fotokp.length - 1); e++) {
                        if (e > 0)
                            is_active = "";

                        fotokp_display += '<div class="carousel-item ' + is_active + '">' +
                            '<img class="d-block w-100 ft_kom" src="' + base_url + "/" + fotokp[e] + '" alt="First slide">' +
                            '</div>';
                    }
                }
                $("#fm-komplain-sales #foto_komplain_produksi").html(fotokp_display);


                if (st.status_komplain == 1) {
                    $("#batal_komplain").removeClass('hidden', true);
                } else if (st.status_komplain == 2) {
                    $("#batal_komplain_btn, #komplain-sales-form-btn, #fm-komplain-sales #keterangan_komplain").prop('disabled', true);
                    $("#batal_komplain").removeClass('hidden', true);
                    $("#komplain_ditangani_sales").removeClass('hidden', true);


                    $("#fm-komplain-sales #keterangan_ditangani").val(st.keterangan_ditangani);
                    $("#fm-komplain-sales #username_ditangani_oleh").val(st.username_ditangani_oleh);

                    if (st.ditangani_tgl != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #ditangani_tgl")._flatpickr.setDate(st.ditangani_tgl);

                } else if (st.status_komplain == 3) {
                    $("#batal_komplain_btn, #fm-komplain-sales #keterangan_komplain").prop('disabled', true);
                    $("#batal_komplain, #selesaikan_komplain_div_sales, #komplain_ditangani_sales").removeClass('hidden', true);

                    $("#fm-komplain-sales #keterangan_ditangani").val(st.keterangan_ditangani);
                    $("#fm-komplain-sales #username_ditangani_oleh").val(st.username_ditangani_oleh);

                    if (st.ditangani_tgl != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #ditangani_tgl")._flatpickr.setDate(st.ditangani_tgl);

                    $("#fm-komplain-sales #selesai_keterangan_produksi").val(st.selesai_keterangan_produksi);
                    $("#fm-komplain-sales #username_selesai_oleh_produksi").val(st.username_selesai_oleh_produksi);

                    if (st.selesai_tgl_produksi != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #selesai_tgl_produksi")._flatpickr.setDate(st.selesai_tgl_produksi);

                } else if (st.status_komplain == 4) {
                    $("#batal_komplain_btn, #komplain-sales-form-btn, #fm-komplain-sales #keterangan_komplain, #fm-komplain-sales #selesai_keterangan_sales").prop('disabled', true);
                    $("#batal_komplain, #selesaikan_komplain_div_sales, #komplain_ditangani_sales").removeClass('hidden', true);

                    $("#batal_komplain_btn").html("Komplain Selesai")
                    $("#batal_komplain_btn").removeClass("btn-outline-danger")
                    $("#batal_komplain_btn").addClass("btn-outline-primary")

                    $("#fm-komplain-sales #keterangan_ditangani").val(st.keterangan_ditangani);
                    $("#fm-komplain-sales #username_ditangani_oleh").val(st.username_ditangani_oleh);

                    if (st.ditangani_tgl != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #ditangani_tgl")._flatpickr.setDate(st.ditangani_tgl);

                    $("#fm-komplain-sales #selesai_keterangan_produksi").val(st.selesai_keterangan_produksi);
                    $("#fm-komplain-sales #username_selesai_oleh_produksi").val(st.username_selesai_oleh_produksi);

                    if (st.selesai_tgl_produksi != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #selesai_tgl_produksi")._flatpickr.setDate(st.selesai_tgl_produksi);

                    $("#fm-komplain-sales #selesai_keterangan_sales").val(st.selesai_keterangan_sales);
                    $("#fm-komplain-sales #username_selesai_oleh_sales").val(st.username_selesai_oleh_sales);

                    if (st.selesai_tgl_produksi != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #selesai_tgl_sales")._flatpickr.setDate(st.selesai_tgl_sales);

                }


                $("#fm-komplain-sales #keterangan_komplain").val(st.keterangan_komplain);
                $("#fm-komplain-sales #username_komplain_oleh").val(st.username_komplain_oleh);

                if (st.komplain_tgl != "0000-00-00")
                    document.querySelector("#fm-komplain-sales #komplain_tgl")._flatpickr.setDate(st.komplain_tgl);

                $("#last_update_komplain_sales").html('Terakhir diupdate oleh: ' + st.username_last_update + ', pada: ' + format_datetime(st.updated_at));
            }
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_komplain_sales').modal({
                backdrop: 'static',
                keyboard: false
            });

        },
        error: function() {},

    });
}

function save_komplain_sales() {
    var files = $('#upload_komplain_sales')[0].files;
    var form = $('#fm-komplain-sales')[0];
    var fd = new FormData(form);
    fd.append(csrfName, csrfHash);

    $.ajax({
        url: base_url + '/sales/save_komplain_sales',
        type: 'POST',
        contentType: false,
        processData: false,
        // data: $("#fm-komplain-sales").serialize() + "&" + csrfName + "=" + csrfHash,
        data: fd,
        dataType: 'json',
        beforeSend: function() {
            $('#komplain-sales-form-btn').prop('disabled', true);
            $('#komplain-sales-form-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
        },
        success: function(r) {
            csrfHash = r.token;

            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                })

                $('.modal').modal('hide');
                hapus_seleksi();
                load_kavling();
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                })
            }
            $('#komplain-sales-form-btn').html('Simpan');
            $('#komplain-sales-form-btn').prop('disabled', false);
        }
    });
}

function batal_komplain() {
    Swal.fire({
        title: 'Apakah anda yakin akan membatalkan komplain?',
        text: "Data komplain akan terhapus dan tidak bisa dikembalikan",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if (result.value) {
            $.ajax({
                url: base_url + '/sales/batalkan_komplain',
                type: 'post',
                data: {
                    [csrfName]: csrfHash,
                    id_kavling: $(".id_kavling").val(),
                    id_komplain: $("#id_komplain").val(),
                },
                dataType: 'json',
                success: function(response) {
                    csrfHash = response.token;
                    if (response.success === true) {
                        Swal.fire({
                            //position: 'bottom-end',
                            icon: 'success',
                            title: response.messages,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            load_kavling();
                            hapus_seleksi();
                            $(".modal").modal('hide')
                        })
                    } else {
                        Swal.fire({
                            //position: 'bottom-end',
                            icon: 'error',
                            title: response.messages,
                            showConfirmButton: false,
                            timer: 1500
                        })
                    }
                }
            });
        }
    })
}

function open_serah_terima() {
    if (!editdtt[0]) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Pilih salahsatu kavling",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }


    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    $("#fm-serah-terima")[0].reset();

    $("#last_update_serah_terima").html('Terakhir diupdate oleh: -, pada: -');

    $(".id_kavling").val(id_kavling);
    $("#is_serah_terima").val(sh.data2.id_serah_terima);


    $.ajax({
        url: base_url + '/sales/get_data_serah_terima_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            is_serah_terima: sh.data2.is_serah_terima,
            id_kavling: id_kavling
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;
            let st = r.serah_terima;

            if (st) {
                if (st.is_serah_terima == 1)
                    $("#is_serah_terima").prop('checked', true);

                // let lates_date = cl[0].produksi_cek_tgl,
                //     lates_date_sales = cl[0].sales_cek_tgl;
                for (let i in st) {
                    if (i != 'is_serah_terima')
                        $("#fm-serah-terima #" + i).val(st[i]);
                }
                if (st.serah_terima_tgl != "0000-00-00")
                    document.querySelector("#serah_terima_tgl")._flatpickr.setDate(st.serah_terima_tgl);

                $("#fm-serah-terima #is_serah_terima").val(1);
                // $("#last_update_checklist_prod2").html('Terakhir diupdate (produksi) oleh: ' + cl[0].username_prod + ', pada: ' + format_date(lates_date));
                $("#last_update_serah_terima").html('Terakhir diupdate oleh: ' + st.username + ', pada: ' + format_date(st.updated_at));
            }
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_serah_terima').modal({
                backdrop: 'static',
                keyboard: false
            });

        },
        error: function() {},

    });
}

function save_serah_terima() {
    $.ajax({
        url: base_url + '/sales/save_serah_terima',
        type: 'post',
        data: $("#fm-serah-terima").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: 'json',
        beforeSend: function() {
            $('#serah-terima-form-btn').prop('disabled', true);
            $('#serah-terima-form-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
        },
        success: function(r) {
            csrfHash = r.token;

            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                })

                $('.modal').modal('hide');
                hapus_seleksi();
                load_kavling();
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                })
            }
            $('#serah-terima-form-btn').html('Simpan');
            $('#serah-terima-form-btn').prop('disabled', false);
        }
    });
}

</script>

<script>
    function open_pajak(sh, role, id_kavling) {
        sv_url = '/pajak/save'
        sv_fm = $('#fm-pajak')
        sv_btn = $('#add-form-btn-pajak')
    
        sv_fm[0].reset();
    
        if (sh.data.tipe != "kavling") {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Tidak ada kavling terpilih ",
                showConfirmButton: true,
                // timer: 1500
            })
            return;
        }
        if (!sh.data.id_mkdt) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }
        if (sh.data.status_mkdt == "Batal") {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Kavling dengan konsumen batal.",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }
    
        $.ajax({
            url: base_url + '/pajak/getOne',
            type: 'post',
            data: {
                id_mkdt: sh.data.id_mkdt,
                [csrfName]: csrfHash
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(r) {
                csrfHash = r.token;
                $("#loading").addClass("hidden");
    
                $("#fm-pajak #id_mkdt").val(sh.data.id_mkdt)
    
                for (let i in r) {
                    $("#fm-pajak #" + i).val(r[i]);
                }
    
                if (r.pph42_tgl_bayar != "0000-00-00")
                    document.querySelector("#fm-pajak #pph42_tgl_bayar")._flatpickr.setDate(r.pph42_tgl_bayar);
                if (r.ppn_tgl_bayar != "0000-00-00")
                    document.querySelector("#fm-pajak #ppn_tgl_bayar")._flatpickr.setDate(r.ppn_tgl_bayar);
                if (r.ppn_tgl_bayar != "0000-00-00")
                    document.querySelector("#fm-pajak #ppnjk_tgl_bayar")._flatpickr.setDate(r.ppnjk_tgl_bayar);
    
                $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>" + "Konsumen: " + r.nama_konsumen);
                $('#modal_pajak').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
                $('#modal_divisi' + role).modal({
                    backdrop: 'static',
                    keyboard: false
                });
            },
            error: function() {
                $("#loading").addClass("hidden");
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Oops!! Terjadi Kesalahan",
                    showConfirmButton: false,
                    // timer: 1500
                })
            }
        });
    }
    $("#add-form-btn-pajak").click(function(e) {
        e.preventDefault();
    });
    
    function save_() {
        $.ajax({
            url: base_url + sv_url,
            type: 'post',
            data: sv_fm.serialize() + "&" + csrfName + "=" + csrfHash + "&" + sv_par,
            dataType: 'json',
            beforeSend: function() {
                sv_btn.prop("disabled", true);
                sv_btn.html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(r) {
                csrfHash = r.token;
                if (r.success === true) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    })
                    $('.modal').modal('hide');
                    sv_btn.html('Simpan');
                    sv_btn.prop("disabled", false);
                } else {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    })
                    sv_btn.html('Simpan');
                    sv_btn.prop("disabled", false);
                }
                sv_url = ''
                sv_fm = ''
                sv_btn = ''
                sv_par = ''
                load_kavling();
                hapus_seleksi();
            },
            error: function() {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Oops!! Terjadi Kesalahan",
                    showConfirmButton: false,
                    // timer: 1500
                })
                sv_btn.html('Simpan');
                sv_btn.prop("disabled", false);
    
                sv_url = ''
                sv_fm = ''
                sv_btn = ''
                sv_par = ''
            }
        });
    }
    </script>

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
        $("#modal-setting-filter").modal()
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
        if (!palid("batal-keterangan_batal", "", "Keterrangan Batal harus diisi"))
            return;


        var form = $('#fm-batal_booking')[0];
        var fd = new FormData(form);
        fd.append(csrfName, csrfHash);

        $.ajax({
            url: base_url + '/mkdt/simpan_batal',
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
                        //position: 'bottom-end',
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
                        //position: 'bottom-end',
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
                    position: 'bottom-end',
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
                //position: 'bottom-end',
                icon: 'error',
                title: "Tidak ada kavling terpilih ",
                showConfirmButton: true,
                // //timer: 1500
            })
            return;
        }
        if (!sh.data.id_mkdt) {
            Swal.fire({
                //position: 'bottom-end',
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
            url: base_url + '/mkdt/batal_mkdt',
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
                    //position: 'bottom-end',
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

    function terima_batal() {

        let sh = editdtt[0],
            id_kavling = sh.id.substr(3);
        if (sh.data.tipe != "kavling") {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Tidak ada kavling terpilih ",
                showConfirmButton: true,
                // //timer: 1500
            })
            return;
        }
        if (!sh.data.id_mkdt) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Belum ada data konsumen di kavling: <br>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
                showConfirmButton: false,
                // ////timer: 1500
            })
            return;
        }
        if (sh.data.is_batal == '0') {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Belum ada data surat batal dari MKDT",
                showConfirmButton: false,
                // //timer: 1500
            })
            return;
        }
        $("#fm-batal_booking")[0].reset();
        $("#last_update-batal_mkdt").html("Dibatalkan oleh: -  Pada: -")

        $("#batal-id_kavling").val(id_kavling);
        $("#batal-id_mkdt").val(sh.data.id_mkdt);

        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");

        $.ajax({
            url: base_url + '/mkdt/batal_mkdt',
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
                    //position: 'bottom-end',
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
    function setDatePicker(val, id){
        if (val != "0000-00-00")
            return document.querySelector(id)._flatpickr.setDate(val);
    }
</script>