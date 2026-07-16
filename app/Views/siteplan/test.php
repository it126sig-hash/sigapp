<?php $k;
$v;
foreach (user()->getRoles() as $key => $val) {
    $k = $key;
    $v = $val;
} ?>
<link href="<?= base_url() ?>/app-assets/vendors/css/vendors.min.css" rel="stylesheet" type="text/css">
<link href="<?= base_url() ?>/app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css">
<link href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css" rel="stylesheet" type="text/css">
<link href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css" rel="stylesheet" type="text/css">
<script>
<script>
    var csrfName = '<?= csrf_token() ?>',
        csrfHash = '<?= csrf_hash() ?>';

    const rolename = '<?= $v ?>';
    const roleid = '<?= $k ?>';
    const base_url = "<?= base_url() ?>"
    var dt_proyek = '<?php echo json_encode($data['proyek']) ?>',
        dt_proyek = JSON.parse(dt_proyek);
    const proyek_nama = '<?= $data['proyek']->nama_proyek ?>';
</script>
    body {
        -webkit-user-select: none;
        -ms-user-select: none;
        user-select: none
    }

    .canvas {
        border: 1px solid #000;
        background-color: #eee
    }

    .float {
        position: fixed;
        width: 200;
        height: 70px;
        bottom: 40px;
        background-color: #fff;
        border: 1px solid;
        border-radius: 5px;
        text-align: center;
        box-shadow: 2px 2px 3px #999;
        z-index: 1040;
        padding: 0 10px 10px 10px
    }

    .my-float {
        margin-top: 22px
    }

    .disabled {
        pointer-events: none;
        cursor: default
    }

    div#legal,
    div#lpt,
    div#mkdt {
        height: 50vh;
        overflow: auto;
        background: #fff
    }

    .capitalize {
        text-transform: capitalize
    }
</style>
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">
            <div class="card">
                <div class="card-body">
                    <div class="card-text">
                        <div id="menu_here"></div>
                        <div class="hidden" style="overflow:auto">
                            <div style="float:left;margin-right:10px">Blur radius:</div><input id="blurRadius" onchange="onRadiusChange.apply(this,arguments)" style="float:left;width:20px;margin-right:10px">
                            <div id="threshold"></div>
                        </div>
                        <div id="stage-parent">
                            <div class="canvas" id="konva-holder"></div>
                        </div>
                    </div>
                </div>
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
</div><?php if ($k == 1 || $k == 6): ?><div class="fade modal modal-slide-in" id="modals-slide-in">
        <div class="modal-dialog sidebar-sm">
            <form class="modal-content pt-0 add-new-record" id="fm-add_kavling"><button class="close" type="button" data-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Tambah Data</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Proyek</label> <input name="points" class="form-control" id="points" value="" type="hidden" readonly> <input name="id_kavling" class="form-control id_kavling" value="" type="hidden" readonly> <input name="nama_proyek" class="form-control" id="nama_proyek" value="<?= $data['proyek']->nama_proyek ?>" readonly placeholder="ASI"> <input name="id_proyek" id="id_proyek" value="<?= $data['proyek']->id_proyek ?>" type="hidden"></div>
                    <div class="form-group"><label for="basic-icon-default-post" class="form-label">Jenis</label> <select class="custom-select select2 id_jenis" id="id_jenis" name="id_jenis">
                            <option value="">-</option>
                            <option value="kavling">Kavling</option>
                            <option value="jalan">Jalan</option>
                            <option value="fasos">Fasos</option>
                            <option value="rth">RTH</option>
                        </select></div>
                    <div class="form-group"><label for="basic-icon-default-post" class="form-label">Cluster</label> <select class="custom-select select2 id_cluster" id="id_cluster" name="id_cluster"></select></div>
                    <div class="form-group"><label for="basic-icon-default-post" class="form-label">Jalan</label> <select class="2custom-select id_jalan select" id="id_jalan" name="id_jalan" disabled></select></div>
                    <hr><span>Luas di Lapangan :<br><span class="t_luas_produksi"></span></span><br><br><span>Luas di Sertifikat :<br><span class="t_luas_legal"></span></span>
                    <hr>
                    <div class="form-group"><label for="f_luas" class="form-label">Luas</label> <input name="f_luas" class="form-control" id="f_luas" value="" placeholder="90"></div>
                    <div class="h" id="div_kavling">
                        <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">No Rumah</label> <input name="no_kavling" class="form-control" id="no_kavling" value="" placeholder="31"></div>
                        <div class="form-group"><label for="basic-icon-default-post" class="form-label">Tipe</label> <select class="custom-select select2 id_tipe" id="id_tipe" name="id_tipe"></select></div>
                        <div class="form-group"><label for="basic-icon-default-post" class="form-label">Status Kavling</label> <select class="custom-select select2" id="status_tanah" name="status_tanah" placeholder="standar/kelebihan tanah">
                                <option value="Standar">Standar</option>
                                <option value="Kelebihan Tanah">Kelebihan Tanah</option>
                            </select></div>
                    </div>
                    <div class="h" id="div_fasos">
                        <div class="form-group"><label for="f_planning_keterangan">Nama</label> <input name="f_nama" class="form-control" id="f_nama" value="" placeholder="FASUM/SOS"></div>
                    </div>
                    <div class="h" id="div_jalan">
                        <div class="form-group"><label for="f_planning_keterangan">Keterangan</label> <textarea class="form-control" id="f_planning_keterangan" rows="3" name="f_planning_keterangan" placeholder="Keterangan"></textarea></div>
                    </div><button class="btn waves-effect btn-block btn-outline-primary" type="button" id="pindah_lokasi_btn" onclick="pindah_kavling()">Pindah Lokasi</button>
                    <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">*catatan: gunakan titik koma ";" untuk pemisah nomor rumah jika akan input rumah lebih dari 1 kavling sekaligus</label></div><a href="javascript:void(0)" class="btn btn-primary mr-1 data-submit" id="add-form-btn" onclick="add_kavling()">Simpan</a> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div><?php endif; ?><?php if ($k == 7 || $k == 1): ?><div class="fade modal text-left" id="modal_fothersproduksi" aria-labelledby="modal_fothersproduksi" role="dialog" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Produksi</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <form class="modal-content pt-0 add-new-record" id="fm-fotherproduksi">
                    <div class="modal-body">
                        <p class="modal-title label_alamat" id="label_fothersproduksi"></p><br><span>Luas di Siteplan :<br><span class="t_luas_planning"></span></span><br><br><span>Luas di Sertifikat :<br><span class="t_luas_legal"></span></span>
                        <hr><input name="id_kavling" class="form-control id_kavling" value="" type="hidden"> <input name="id_produksi" class="form-control" id="id_produksi" value="" type="hidden">
                        <div class="form-group"><label for="f_progres_jalan">Progres</label> <input name="f_progres_jalan" class="form-control-range" id="f_progres_jalan" max="100" min="0" oninput='$(".r_progres").html($(this).val())' step="5" type="range"> <span class="r_progres"></span><span>%</span></div>
                        <div class="form-group"><label for="serah_terima_oleh">Luas Dilapangan</label> <input name="f_produksi_luas" class="form-control" id="f_produksi_luas" placeholder="Luas jalan dilapangan"></div>
                        <div class="form-group"><label for="produksi_keterangan">Keterangan</label> <textarea class="form-control" id="f_produksi_keterangan" rows="3" name="f_produksi_keterangan" placeholder="Keterangan"></textarea></div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary mr-1 data-submit" id="save_fotherproduksi-btn" onclick="save_fotherproduksi()" href="javascript:void(0)">Simpan</button> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="fade modal text-left" id="modal_divisi7" aria-labelledby="myModalLabel17" role="dialog" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Produksi</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <form class="modal-content pt-0 add-new-record" id="fm-produksi">
                    <div class="modal-body">
                        <p class="modal-title label_alamat" id="label_alamat7"></p>
                        <hr><input name="id_kavling" class="form-control id_kavling" value="" type="hidden"> <input name="id_produksi" class="form-control" id="id_produksi" value="" type="hidden"> <button class="btn waves-effect btn-block btn-outline-primary" type="button" id="download_gambar_kerja">Unduh Gambar Kerja</button>
                        <hr>
                        <div class="form-group">
                            <div class="custom-control custom-control-inline custom-switch"><input name="pondasi" class="custom-control-input cbp" id="pondasi" value="1" type="checkbox"> <label for="pondasi" class="custom-control-label">Pondasi</label></div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-control-inline custom-switch"><input name="naik_dinding" class="custom-control-input cbp" id="naik_dinding" value="1" type="checkbox"> <label for="naik_dinding" class="custom-control-label">Naik Dinding</label></div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-control-inline custom-switch"><input name="topping_off" class="custom-control-input cbp" id="topping_off" value="1" type="checkbox"> <label for="topping_off" class="custom-control-label">Topping Off</label></div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-control-inline custom-switch"><input name="finishing" class="custom-control-input cbp" id="finishing" value="1" type="checkbox"> <label for="finishing" class="custom-control-label">Finishing</label></div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-control-inline custom-switch"><input name="saluran" class="custom-control-input cbp" id="saluran" value="1" type="checkbox"> <label for="saluran" class="custom-control-label">Saluran</label></div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-control-inline custom-switch"><input name="jalan" class="custom-control-input cbp" id="jalan" value="1" type="checkbox"> <label for="jalan" class="custom-control-label">Jalan</label></div>
                        </div>
                        <hr>
                        <div class="af">
                            <div class="form-group">
                                <div class="custom-control custom-control-inline custom-switch"><input name="slo" class="custom-control-input cbp" id="slo" value="1" type="checkbox"> <label for="slo" class="custom-control-label">SLO</label></div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-control-inline custom-switch"><input name="bp" class="custom-control-input cbp" id="bp" value="1" type="checkbox"> <label for="bp" class="custom-control-label">BP</label></div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-control-inline custom-switch"><input name="lpa" class="custom-control-input cbp" id="lpa" value="1" type="checkbox"> <label for="lpa" class="custom-control-label">LPA</label></div>
                            </div>
                        </div>
                        <div class="form-group"><label for="progres_bangunan">Progres Bangunan</label> <input name="progres_bangunan" class="form-control-range" id="progres_bangunan" value="0" type="range" disabled> <span id="t_progres_bangunan"></span>%</div>
                        <div class="form-group"><label for="produksi_keterangan">Keterangan</label> <textarea class="form-control" id="produksi_keterangan" rows="3" name="produksi_keterangan" placeholder="Keterangan"></textarea></div>
                        <div class="divider">
                            <div class="divider-text">Checklist</div>
                        </div>
                        <p><button class="btn waves-effect btn-block btn-outline-primary" type="button" data-toggle="collapse" href="#collapseExample">Tampilkan Checklist</button></p>
                        <div class="collapse" id="collapseExample"><small class="text-muted" id="last_update_checklist_prod"></small>
                            <div class="card-body card"><?php $n = 1;
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
                                                        } ?></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary mr-1 data-submit" id="add-form-btn-produksi" onclick="save_produksi()" href="javascript:void(0)">Simpan</button> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="fade modal modal-slide-in" id="modal_komplain_produksi">
        <div class="modal-dialog sidebar-sm">
            <form class="modal-content pt-0 add-new-record" id="fm-komplain-produksi"><button class="close" type="button" data-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Komplain Kavling</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <p class="modal-title label_alamat" id="label_alamat5"></p>
                    <hr><input name="id_kavling" class="form-control id_kavling" value="" type="hidden"> <input name="id_komplain" class="form-control" id="id_komplain" value="" type="hidden"> <small class="text-muted" id="last_update_komplain_produksi"></small> <button class="btn waves-effect btn-block btn-outline-success hidden" type="button" id="komplain_selesai_btn_produksi">Komplain Selesai</button>
                    <div class="carousel slide" id="controls_produksi_foto_komplain_sales">
                        <div class="carousel-inner" id="foto_komplain_sales"></div><a href="#controls_produksi_foto_komplain_sales" class="carousel-control-prev" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span> <span class="sr-only">Previous</span> </a><a href="#controls_produksi_foto_komplain_sales" class="carousel-control-next" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span> <span class="sr-only">Next</span></a>
                    </div>
                    <div class="form-group"><label for="serah_terima_oleh">Dikomplain Oleh</label> <input name="username_komplain_oleh" class="form-control" id="username_komplain_oleh" readonly></div>
                    <div class="form-group"><label for="serah_terima_oleh">Tanggal Komplain</label> <input name="komplain_tgl" class="form-control flatpickr-human-friendly" id="komplain_tgl" disabled></div>
                    <div class="form-group"><label for="serah_terima_keterangan">Keterangan Komplain</label> <textarea class="form-control" id="keterangan_komplain" rows="3" name="keterangan_komplain" placeholder="Keterangan" readonly></textarea></div>
                    <div class="divider">
                        <div class="divider-text">Terima Komplain</div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-control-inline custom-switch"><input name="terima_komplain" class="custom-control-input" id="terima_komplain" value="1" type="checkbox"> <label for="terima_komplain" class="custom-control-label">Terima Komplain</label></div>
                    </div>
                    <div class="hidden ditangani_form" id="terima_komplain_div">
                        <div class="form-group"><label for="serah_terima_keterangan">Keterangan</label> <textarea class="form-control" id="keterangan_ditangani" rows="3" name="keterangan_ditangani" placeholder="Keterangan"></textarea></div>
                    </div>
                    <div class="hidden ditangani_form">
                        <div class="form-group"><label for="serah_terima_oleh">Komplain Diterima Oleh</label> <input name="username_ditangani_oleh" class="form-control" id="username_ditangani_oleh" disabled></div>
                        <div class="form-group"><label for="serah_terima_oleh">Tanggal Komplain Diterima</label> <input name="ditangani_tgl" class="form-control flatpickr-human-friendly" id="ditangani_tgl" disabled></div>
                    </div>
                    <div class="hidden" id="selesaikan_komplain_div">
                        <div class="divider">
                            <div class="divider-text">Selesaikan Komplain</div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-control-inline custom-switch"><input name="is_selesai_produksi" class="custom-control-input" id="is_selesai_produksi" value="1" type="checkbox"> <label for="is_selesai_produksi" class="custom-control-label">Selesaikan Komplain</label></div>
                        </div>
                        <div class="carousel slide" id="controls_produksi_foto_komplain_produksi">
                            <div class="carousel-inner" id="foto_komplain_produksi"></div><a href="#controls_produksi_foto_komplain_produksi" class="carousel-control-prev" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span> <span class="sr-only">Previous</span> </a><a href="#controls_produksi_foto_komplain_produksi" class="carousel-control-next" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span> <span class="sr-only">Next</span></a>
                        </div>
                        <div id="div_upload_komplain_produksi"><label for="upload_komplain_produksi">Foto Perbaikan</label>
                            <div class="custom-file"><input name="upload_komplain_produksi[]" class="custom-file-input" id="upload_komplain_produksi" accept="image/*" multiple type="file"> <label for="upload_komplain_produksi" class="custom-file-label" id="label_upload_komplain_produksi">Bisa Lebih dari 1 foto</label>
                                <div id="list_upload_komplain_produksi"></div>
                            </div>
                        </div>
                        <div class="form-group"><label for="selesai_keterangan_produksi">Keterangan</label> <textarea class="form-control" id="selesai_keterangan_produksi" rows="3" name="selesai_keterangan_produksi" placeholder="Keterangan"></textarea></div>
                        <div class="form-group"><label for="serah_terima_oleh">Diselesakan Oleh</label> <input name="username_selesai_oleh_produksi" class="form-control" id="username_selesai_oleh_produksi" disabled></div>
                        <div class="form-group"><label for="serah_terima_oleh">Tanggal Diselesaikan</label> <input name="selesai_tgl_produksi" class="form-control flatpickr-human-friendly" id="selesai_tgl_produksi" disabled></div>
                    </div>
                    <div class="hidden" id="komplain_selesai_sip">
                        <div class="divider">
                            <div class="divider-text">Komplain Selesai (sales)</div>
                        </div>
                        <div class="form-group"><label for="selesai_keterangan_sales">Keterangan</label> <textarea class="form-control" id="selesai_keterangan_sales" rows="3" name="selesai_keterangan_sales" placeholder="Keterangan" disabled></textarea></div>
                        <div class="form-group"><label for="serah_terima_oleh">Diselesakan Oleh</label> <input name="username_selesai_oleh_sales" class="form-control" id="username_selesai_oleh_sales" disabled></div>
                        <div class="form-group"><label for="serah_terima_oleh">Tanggal Diselesaikan</label> <input name="selesai_tgl_sales" class="form-control flatpickr-human-friendly" id="selesai_tgl_sales" disabled></div>
                    </div><a href="javascript:void(0)" class="btn btn-primary mr-1 data-submit" id="komplain-produksi-form-btn" onclick="save_komplain_produksi()">Simpan</a> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div><?php endif; ?><?php if ($k == 8 || $k == 1): ?><div class="fade modal modal-slide-in" id="modal_serah_terima">
        <div class="modal-dialog sidebar-sm">
            <form class="modal-content pt-0 add-new-record" id="fm-serah-terima"><button class="close" type="button" data-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Serah Terima</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <p class="modal-title label_alamat" id="label_alamat5"></p>
                    <hr><input name="id_kavling" class="form-control id_kavling" value="" type="hidden"> <input name="id_serah_terima" class="form-control" id="id_serah_terima" value="" type="hidden">
                    <div class="form-group">
                        <div class="custom-control custom-control-inline custom-switch"><input name="is_serah_terima" class="custom-control-input" id="is_serah_terima" value="1" type="checkbox"> <label for="is_serah_terima" class="custom-control-label">Sudah Serah Terima</label></div>
                    </div><small class="text-muted" id="last_update_serah_terima"></small>
                    <div class="form-group"><label for="serah_terima_oleh">Oleh</label> <input name="serah_terima_oleh" class="form-control" id="serah_terima_oleh"></div>
                    <div class="form-group"><label for="serah_terima_oleh">Ke</label> <input name="serah_terima_ke" class="form-control" id="serah_terima_ke"></div>
                    <div class="form-group"><label for="serah_terima_tgl">Tanggal Serah Terima</label> <input name="serah_terima_tgl" class="form-control flatpickr-human-friendly" id="serah_terima_tgl"></div>
                    <div class="form-group"><label for="serah_terima_keterangan">Keterangan Serah Terima</label> <textarea class="form-control" id="serah_terima_keterangan" rows="3" name="serah_terima_keterangan" placeholder="Keterangan"></textarea></div><button class="btn btn-primary mr-1 data-submit" id="serah-terima-form-btn" onclick="save_serah_terima()" href="javascript:void(0)">Simpan</button> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <div class="fade modal text-left" id="checklist_modal_sales" aria-labelledby="myModalLabel17" role="dialog" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Sales & Promotion</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <form class="modal-content pt-0 add-new-record" id="fm-checklist-sales">
                    <div class="modal-body">
                        <p class="modal-title label_alamat" id="label_alamat8"></p>
                        <hr><input name="id_kavling" class="form-control id_kavling" value="" type="hidden"> <input name="id_sales" class="form-control" id="id_sales" value="" type="hidden">
                        <div class="divider">
                            <div class="divider-text">Checklist</div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-control-inline custom-switch"><input name="is_checked" class="custom-control-input cbp" id="is_checked" value="1" type="checkbox"> <label for="is_checked" class="custom-control-label">Tandai sudah dicek</label></div>
                        </div>
                        <p><button class="btn waves-effect btn-block btn-outline-primary" type="button" data-toggle="collapse" href="#collapseExample">Tampilkan Checklist</button></p>
                        <div class="collapse" id="collapseExample"><small class="text-muted" id="last_update_checklist_prod2"></small><br><small class="text-muted" id="last_update_checklist_sales"></small>
                            <div class="card-body card"><?php $n = 1;
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
                                                        } ?></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary mr-1 data-submit" id="checklist-form-btn-sales" onclick="save_checklist_sales()" href="javascript:void(0)">Simpan</button> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button></div>
                </form>
            </div>
        </div>
    </div><?php endif; ?><div class="fade modal modal-slide-in" id="modal_komplain_sales">
    <div class="modal-dialog sidebar-sm">
        <form class="modal-content pt-0 add-new-record" id="fm-komplain-sales" enctype="multipart/form-data"><button class="close" type="button" data-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Komplain Kavling</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <p class="modal-title label_alamat" id="label_alamat5"></p>
                <hr><input name="id_kavling" class="form-control id_kavling" value="" type="hidden"> <input name="id_komplain" class="form-control" id="id_komplain" value="" type="hidden"> <small class="text-muted" id="last_update_komplain_sales"></small>
                <div class="hidden" id="batal_komplain">
                    <div class="carousel slide" id="control_sales_foto_komplain_sales">
                        <div class="carousel-inner" id="foto_komplain_sales"></div><a href="#control_sales_foto_komplain_sales" class="carousel-control-prev" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span> <span class="sr-only">Previous</span> </a><a href="#control_sales_foto_komplain_sales" class="carousel-control-next" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span> <span class="sr-only">Next</span></a>
                    </div><button class="btn waves-effect btn-block btn-outline-danger" type="button" id="batal_komplain_btn" onclick="batal_komplain()">Batalkan Komplain</button>
                    <div class="form-group"><label for="serah_terima_oleh">Dikomplain Oleh</label> <input name="username_komplain_oleh" class="form-control" id="username_komplain_oleh" readonly></div>
                    <div class="form-group"><label for="serah_terima_oleh">Tanggal Komplain</label> <input name="komplain_tgl" class="form-control flatpickr-human-friendly" id="komplain_tgl" disabled></div>
                </div>
                <div id="div_upload_komplain_sales"><label for="upload_komplain_sales">Foto Komplain</label>
                    <div class="custom-file"><input name="upload_komplain_sales[]" class="custom-file-input" id="upload_komplain_sales" accept="image/*" multiple type="file"> <label for="upload_komplain_sales" class="custom-file-label" id="label_upload_komplain_sales">Bisa Lebih dari 1 foto</label>
                        <div id="list_upload_komplain_sales"></div>
                    </div>
                </div>
                <div class="form-group"><label for="serah_terima_keterangan">Keterangan Komplain</label> <textarea class="form-control" id="keterangan_komplain" rows="3" name="keterangan_komplain" placeholder="Keterangan"></textarea></div>
                <div class="divider">
                    <div class="divider-text">Komplain Ditangani</div>
                </div>
                <div class="hidden" id="komplain_ditangani_sales">
                    <div class="form-group"><label for="serah_terima_keterangan">Keterangan</label> <textarea class="form-control" id="keterangan_ditangani" rows="3" name="keterangan_ditangani" placeholder="Keterangan" disabled></textarea></div>
                    <div class="form-group"><label for="serah_terima_oleh">Komplain Diterima Oleh</label> <input name="username_ditangani_oleh" class="form-control" id="username_ditangani_oleh" disabled></div>
                    <div class="form-group"><label for="serah_terima_oleh">Tanggal Komplain Diterima</label> <input name="ditangani_tgl" class="form-control flatpickr-human-friendly" id="ditangani_tgl" disabled></div>
                </div>
                <div class="hidden" id="selesaikan_komplain_div_sales">
                    <div class="divider">
                        <div class="divider-text">Komplain diselesaikan Produksi</div>
                    </div>
                    <div class="carousel slide" id="controls_sales_foto_komplain_produksi">
                        <div class="carousel-inner" id="foto_komplain_produksi"></div><a href="#controls_sales_foto_komplain_produksi" class="carousel-control-prev" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span> <span class="sr-only">Previous</span> </a><a href="#controls_sales_foto_komplain_produksi" class="carousel-control-next" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span> <span class="sr-only">Next</span></a>
                    </div>
                    <div class="form-group"><label for="selesai_keterangan_produksi">Keterangan</label> <textarea class="form-control" id="selesai_keterangan_produksi" rows="3" name="selesai_keterangan_produksi" placeholder="Keterangan" disabled></textarea></div>
                    <div class="form-group"><label for="serah_terima_oleh">Diselesakan Oleh</label> <input name="username_selesai_oleh_produksi" class="form-control" id="username_selesai_oleh_produksi" disabled></div>
                    <div class="form-group"><label for="serah_terima_oleh">Tanggal Diselesaikan</label> <input name="selesai_tgl_produksi" class="form-control flatpickr-human-friendly" id="selesai_tgl_produksi" disabled></div>
                    <div class="divider">
                        <div class="divider-text">Selesaikan Komplain</div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-control-inline custom-switch"><input name="is_selesai_sales" class="custom-control-input" id="is_selesai_sales" value="1" type="checkbox"> <label for="is_selesai_sales" class="custom-control-label">Selesaikan Komplain</label></div>
                    </div>
                    <div class="form-group"><label for="selesai_keterangan_sales">Keterangan</label> <textarea class="form-control" id="selesai_keterangan_sales" rows="3" name="selesai_keterangan_sales" placeholder="Keterangan"></textarea></div>
                    <div class="form-group"><label for="serah_terima_oleh">Diselesakan Oleh</label> <input name="username_selesai_oleh_sales" class="form-control" id="username_selesai_oleh_sales" disabled></div>
                    <div class="form-group"><label for="serah_terima_oleh">Tanggal Diselesaikan</label> <input name="selesai_tgl_sales" class="form-control flatpickr-human-friendly" id="selesai_tgl_sales" disabled></div>
                </div><button class="btn btn-primary mr-1 data-submit" id="komplain-sales-form-btn" onclick="save_komplain_sales()" href="javascript:void(0)">Simpan</button> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div><?php if ($k == 5 || $k == 1): ?><div class="fade modal modal-slide-in" id="modal_fotherlegal">
        <div class="modal-dialog sidebar-sm">
            <form class="modal-content pt-0 add-new-record" id="fm-fotherlegal"><button class="close" type="button" data-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Legal</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <p class="modal-title label_alamat" id="label_alamat5"></p><br><span>Luas di Siteplan :<br><span class="t_luas_planning"></span></span><br><br><span>Luas di Lapangan :<br><span class="t_luas_produksi"></span></span>
                    <hr><input name="id_kavling" class="form-control id_kavling" value="" type="hidden">
                    <div class="form-group"><label for="fl_progres_jalan">Progres</label> <input class="form-control-range" id="fl_progres_jalan" disabled type="range" max="100" min="0" oninput='$(".r_progres").html($(this).val())' step="5"> <span class="r_progres"></span><span>%</span></div>
                    <div class="form-group"><label for="serah_terima_oleh">Luas di Sertifikat</label> <input name="f_legal_luas" class="form-control" id="f_legal_luas" placeholder="Luas jalan di sertifikat"></div>
                    <div class="form-group"><label for="f_legal_keterangan">Keterangan</label> <textarea class="form-control" id="f_legal_keterangan" rows="3" name="f_legal_keterangan" placeholder="Keterangan"></textarea></div><button class="btn btn-primary mr-1 data-submit" id="save-fother-btn-legal" onclick="save_fotherlegal()" href="javascript:void(0)">Simpan</button> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <div class="fade modal modal-slide-in" id="modal_flegal">
        <div class="modal-dialog sidebar-sm">
            <form class="modal-content pt-0 add-new-record" id="fm-legal"><button class="close" type="button" data-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Legal</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <p class="modal-title label_alamat" id="label_alamat5"></p>
                    <hr><input name="id_kavling" class="form-control id_kavling" value="" type="hidden"> <input name="id_legal" class="form-control" id="id_legal" value="" type="hidden">
                    <h5 class="modal-title" id="exampleModalLabel">PBB</h5>
                    <div class="form-group"><label for="sertifikat_no_hgb">PBB</label> <input name="pbb" class="form-control" id="pbb"></div>
                    <h5 class="modal-title" id="exampleModalLabel">Sertifikat</h5>
                    <div class="form-group"><label for="sertifikat_tgl">Tanggal Sertifikat</label> <input name="sertifikat_tgl" class="form-control flatpickr-human-friendly" id="sertifikat_tgl" placeholder="-"></div>
                    <div class="form-group"><label for="sertifikat_no_hgb">Luas Tanah</label> <input name="sertifikat_luas" class="form-control" id="sertifikat_luas"></div>
                    <div class="form-group"><label for="sertifikat_no_hgb">No HGB</label> <input name="sertifikat_no_hgb" class="form-control" id="sertifikat_no_hgb"></div>
                    <div class="form-group"><label for="sertifikat_no_split">No Split</label> <input name="sertifikat_no_split" class="form-control" id="sertifikat_no_split"></div>
                    <div class="form-group"><label for="sertifikat_masa_berlaku">Masa Berlaku</label> <input name="sertifikat_masa_berlaku" class="form-control flatpickr-human-friendly" id="sertifikat_masa_berlaku" placeholder="-"></div>
                    <hr>
                    <h5 class="modal-title" id="exampleModalLabel">IMB</h5>
                    <div class="form-group"><label for="imb_tgl">Tanggal IMB</label> <input name="imb_tgl" class="form-control flatpickr-human-friendly" id="imb_tgl" placeholder="-"></div>
                    <div class="form-group"><label for="imb_no_induk">No Induk</label> <input name="imb_no_induk" class="form-control" id="imb_no_induk"></div>
                    <div class="form-group"><label for="imb_no_split">No Split</label> <input name="imb_no_split" class="form-control" id="imb_no_split"></div>
                    <hr>
                    <h5 class="modal-title" id="exampleModalLabel">BPHTB</h5>
                    <div class="form-group"><label for="bphtb_tgl">Tanggal BPHTB</label> <input name="bphtb_tgl" class="form-control flatpickr-human-friendly" id="bphtb_tgl" placeholder="-"></div>
                    <div class="form-group"><label for="bphtb_masa_berlaku">Masa Berlaku</label> <input name="bphtb_masa_berlaku" class="form-control flatpickr-human-friendly" id="bphtb_masa_berlaku" placeholder="-"></div>
                    <div class="form-group"><label for="bphtb_validasi">Validasi</label> <input name="bphtb_validasi" class="form-control flatpickr-human-friendly" id="bphtb_validasi" placeholder="-"></div>
                    <hr>
                    <div class="form-group"><label for="nop">NOP</label> <input name="nop_pbb" class="form-control" id="nop_pbb"></div>
                    <div class="form-group"><label for="pph">PPh</label> <input name="pph" class="form-control" id="pph"></div>
                    <div class="form-group"><label for="legal_akad_tgl">Tanggal Akad</label> <input name="legal_akad_tgl" class="form-control flatpickr-human-friendly" id="legal_akad_tgl" placeholder="-"></div>
                    <div class="form-group"><label for="legal_keterangan">Keterangan</label> <textarea class="form-control" id="legal_keterangan" rows="3" name="legal_keterangan" placeholder="Keterangan"></textarea></div><button class="btn btn-primary mr-1 data-submit" id="add-form-btn-legal" onclick="save_legal()" href="javascript:void(0)">Simpan</button> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div><?php endif; ?><?php if ($k == 4 || $k == 1): ?><div class="fade modal" id="modal_divisi4">
        <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
            <form class="modal-content pt-0 add-new-record" id="fm-mkdt" autocomplete="off">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Marketing Data</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body flex-grow-1">
                    <p class="modal-title label_alamat" id="label_alamat4"></p>
                    <hr>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a href="#data_konsumen" class="nav-link active" role="tab" aria-controls="data_konsumen" data-toggle="tab" aria-selected="true" id="data_konsumen-tab">Data Konsumen</a></li>
                        <li class="nav-item"><a href="#detail_biaya" class="nav-link" role="tab" aria-controls="detail_biaya" data-toggle="tab" aria-selected="true" id="detail_biaya-tab">Detail</a></li>
                        <li class="nav-item"><a href="#status" class="nav-link" role="tab" aria-controls="detail_tagihan" data-toggle="tab" aria-selected="false" id="status-tab">Status</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="data_konsumen" aria-labelledby="data_konsumen-tab" role="tabpanel"><input name="id_kavling" class="form-control id_kavling" value="" type="hidden"> <input name="id_mkdt" class="form-control" id="id_mkdt" value="" type="hidden"> <input name="id_konsumen" class="form-control" id="id_konsumen" value="" type="hidden"> <input name="mkdt_data_baru" class="form-control" id="mkdt_data_baru" value="" type="hidden">
                            <div class="row">
                                <div class="col-sm-12 col-lg-6 col-md-6">
                                    <div id="refresh_fmmkdt_div"><button class="btn waves-effect btn-block btn-outline-primary" type="button" id="refresh_fmmkdt_btn">Tambah Konsumen Baru</button></div>
                                    <div id="delete_kons_div"><button class="btn waves-effect btn-block btn-outline-danger" type="button" id="delete_kons_btn" onclick="delete_kons(!1)">Hapus Konsumen</button></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-lg-6 col-md-6">
                                    <div class="divider">
                                        <div class="divider-text">Data Konsumen</div>
                                    </div>
                                    <div class="form-group"><label for="nama_konsumen">No SPPTB</label> <input name="no_spptb" class="form-control" id="no_spptb"></div>
                                    <div class="form-group"><label for="nama_konsumen">Nama Konsumen</label> <input name="nama_konsumen" class="form-control" id="nama_konsumen" required></div>
                                    <div class="form-group"><label for="alamat_konsumen">Alamat Konsumen</label> <input name="alamat_konsumen" class="form-control" id="alamat_konsumen"></div>
                                    <div class="form-group"><label for="nik_konsumen">NIK</label> <input name="nik_konsumen" class="form-control" id="nik_konsumen"></div>
                                    <div class="form-group"><label for="npwp_konsumen">NPWP</label> <input name="npwp_konsumen" class="form-control" id="npwp_konsumen"></div>
                                    <div class="form-group"><label for="hp_konsumen">Kontak Konsumen</label> <input name="hp_konsumen" class="form-control" id="hp_konsumen"></div>
                                    <div class="form-group"><label for="status_kavling">Status Konsumen</label> <select class="form-control" id="status_konsumen" name="status_konsumen">
                                            <option value="">-</option>
                                            <option value="Umum">Umum</option>
                                            <option value="TWP">TWP</option>
                                        </select></div>
                                </div>
                                <div class="col-sm-12 col-lg-6 col-md-6">
                                    <div class="divider">
                                        <div class="divider-text">Data Pasangan</div>
                                    </div>
                                    <div class="form-group"><label for="status_kavling">Status Pernikahan</label> <select class="form-control" id="status_pernikahan" name="status_pernikahan">
                                            <option value="Belum Kawin">Belum Kawin</option>
                                            <option value="Kawin">Kawin</option>
                                            <option value="Cerai Mati">Cerai Mati</option>
                                            <option value="Cerai Hidup">Cerai Hidup</option>
                                        </select></div>
                                    <div class="form-group"><label for="nama_pasangan">Nama Pasangan</label> <input name="nama_pasangan" class="form-control" id="nama_pasangan"></div>
                                    <div class="form-group"><label for="hp_konsumen">NIK Pasangan</label> <input name="nik_pasangan" class="form-control" id="nik_pasangan"></div>
                                    <div class="divider">
                                        <div class="divider-text">Data Instansi</div>
                                    </div>
                                    <div class="form-group"><label for="nama_instansi">Nama Instansi</label> <input name="nama_instansi" class="form-control" id="nama_instansi"></div>
                                    <div class="form-group"><label for="alamat_instansi">Alamat Instansi</label> <input name="alamat_instansi" class="form-control" id="alamat_instansi"></div>
                                    <div class="form-group"><label for="tel_instansi">Telepon Instansi</label> <input name="tel_instansi" class="form-control" id="tel_instansi"></div>
                                    <div class="divider">
                                        <div class="divider-text">Sales</div>
                                    </div>
                                    <div class="form-group"><label for="alamat_instansi">Sales</label> <input name="sales" class="form-control" id="sales"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="detail_biaya" aria-labelledby="detail_biaya-tab" role="tabpanel">
                            <div class="row">
                                <div class="col-sm-12 col-lg-6 col-md-6">
                                    <div class="divider">
                                        <div class="divider-text">Status</div>
                                    </div>
                                    <div class="form-group"><label for="status_kavling">Status Kavling</label> <select class="form-control" id="status_mkdt" name="status_mkdt" required>
                                            <option value="">-</option>
                                            <option value="Booking">Booking</option>
                                            <option value="Akad">Akad</option>
                                            <option value="Batal">Batal</option>
                                        </select></div>
                                    <div class="hidden" id="show_keterangan_batal">
                                        <div class="form-group"><label for="keterangan_batal">Keterangan Batal</label> <textarea class="form-control" id="keterangan_batal" rows="3" name="keterangan_batal" placeholder="Keterangan"></textarea></div>
                                    </div>
                                    <div class="form-group"><label for="booking_tgl">Tanggal Booking</label> <input name="booking_tgl" class="form-control flatpickr-human-friendly" id="booking_tgl" placeholder="-"></div>
                                    <div class="form-group"><label for="harga_jual">Booking Fee</label> <input name="booking_fee" class="form-control num" id="booking_fee"></div>
                                </div>
                                <div class="col-sm-12 col-lg-6 col-md-6">
                                    <div class="divider">
                                        <div class="divider-text">TUNAI/KPR</div>
                                    </div>
                                    <div class="form-group"><label for="is_kpr">Tunai/KPR</label> <select class="form-control" id="is_kpr" name="is_kpr" required>
                                            <option value="0">TUNAI</option>
                                            <option value="1">KPR</option>
                                        </select></div>
                                    <div class="form-group"><label for="bank">Bank</label> <input name="bank" class="form-control" id="bank" placeholder="-"></div>
                                    <div class="form-group"><label for="ewe_keterangan">Keterangan</label> <input name="mkdt_keterangan" class="form-control" id="mkdt_keterangan" placeholder="TUNAI/AAC SP3K/LAIN-LAIN"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="status" aria-labelledby="status-tab" role="tabpanel">
                            <div class="row">
                                <div class="col-sm-12 col-lg-4 col-md-4">
                                    <div class="divider">
                                        <div class="divider-text">SP3K</div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-control-inline custom-switch"><input name="wawancara" class="custom-control-input" id="wawancara" value="1" type="checkbox"> <label for="wawancara" class="custom-control-label">Sudah Wawancara</label></div>
                                    </div>
                                    <div class="form-group"><label for="wawancara_tgl">Tanggal Wawancara</label> <input name="wawancara_tgl" class="form-control flatpickr-human-friendly" id="wawancara_tgl" placeholder="-"></div>
                                    <div class="form-group"><label for="bank">Pengajuan</label> <input name="harga_kpr" class="form-control num" id="harga_kpr" placeholder="-"></div>
                                    <div class="form-group"><label for="bank">ACC</label> <input name="acc_harga_kpr" class="form-control num" id="acc_harga_kpr" placeholder="-"></div>
                                    <div class="form-group"><label for="bank">Turun KPR</label> <input name="harga_turun_kpr" class="form-control num" id="harga_turun_kpr" placeholder="-"></div>
                                    <div class="form-group"><label for="sp3k_tgl">Tanggal Terbit</label> <input name="sp3k_tgl" class="form-control flatpickr-human-friendly" id="sp3k_tgl" placeholder="-"></div>
                                    <div class="form-group"><label for="sp3k_tgl">Tanggal Expire</label> <input name="sp3k_tgl_exp" class="form-control flatpickr-human-friendly" id="sp3k_tgl_exp" placeholder="-"></div>
                                </div>
                                <div class="col-sm-12 col-lg-4 col-md-4">
                                    <div class="divider">
                                        <div class="divider-text">Perintah Bangun</div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-control-inline custom-switch"><input name="perintah_bangun" class="custom-control-input" id="perintah_bangun" value="1" type="checkbox"> <label for="perintah_bangun" class="custom-control-label">Perintah Bangun</label></div>
                                    </div>
                                    <div class="form-group"><label for="perintah_bangun_tgl">Tanggal Perintah Bangun</label> <input name="perintah_bangun_tgl" class="form-control flatpickr-human-friendly" id="perintah_bangun_tgl" readonly placeholder="-"></div>
                                    <div class="form-group"><label for="perintah_bangun_oleh">Oleh</label> <input name="perintah_bangun_oleh" class="form-control" id="perintah_bangun_oleh" readonly placeholder="-"></div>
                                    <div class="divider">
                                        <div class="divider-text">Akad</div>
                                    </div>
                                    <div class="form-group"><label for="rencana_akad_tgl">Rencana Akad</label> <input name="rencana_akad_tgl" class="form-control flatpickr-human-friendly" id="rencana_akad_tgl" placeholder="-"></div>
                                    <div class="form-group">
                                        <div class="custom-control custom-control-inline custom-switch"><input name="akad" class="custom-control-input" id="akad" value="1" type="checkbox"> <label for="akad" class="custom-control-label">Akad</label></div>
                                    </div>
                                    <div class="form-group"><label for="akad_tgl">Tanggal Akad</label> <input name="akad_tgl" class="form-control flatpickr-human-friendly" id="akad_tgl" placeholder="-"></div>
                                </div>
                                <div class="col-sm-12 col-lg-4 col-md-4">
                                    <div class="divider">
                                        <div class="divider-text">Harga Jual</div>
                                    </div>
                                    <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Tanggaal Pricelist</label> <input name="mkdt-tgl_harga" class="form-control text-right" id="mkdt-tgl_harga" value="" readonly></div>
                                    <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Harga Jual</label> <input name="mkdt-hargajual" class="form-control num" id="mkdt-hargajual" value="" readonly></div>
                                    <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">KPR</label> <input name="mkdt-kpr" class="form-control num" id="mkdt-kpr" value="" readonly></div>
                                    <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Uang Muka</label> <input name="mkdt-uang_muka" class="form-control num" id="mkdt-uang_muka" value="" readonly></div>
                                    <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">BPHTB</label> <input name="mkdt-bphtb" class="form-control num" id="mkdt-bphtb" value="" readonly></div>
                                    <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Biaya Adm</label> <input name="mkdt-biaya_adm" class="form-control num" id="mkdt-biaya_adm" value="" readonly></div>
                                    <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Biaya Proses</label> <input name="mkdt-biaya_proses" class="form-control num" id="mkdt-biaya_proses" value="" readonly></div>
                                </div>
                            </div>
                        </div>
                    </div><br>
                </div>
                <div class="modal-footer"><button class="btn btn-primary mr-1 data-submit" id="add-form-btn-mkdt" onclick="save_mkdt(this)" href="javascript:void(0)">Simpan</button> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button></div>
        </div>
    </div><?php endif; ?><?php if ($k == 9 || $k == 1): ?><div class="fade modal" id="modals-set_harga">
        <div class="modal-dialog modal-lg">
            <form class="modal-content pt-0 add-new-record" id="fm-set_harga">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Set Harga</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="row">
                        <div class="col-sm-12 col-lg-6 col-md-6">
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Proyek</label> <input name="points" class="form-control" id="points" value="" type="hidden" readonly> <input name="id_kavling" class="form-control id_kavling" value="" type="hidden" readonly> <input name="nama_proyek" class="form-control" id="nama_proyek" value="<?= $data['proyek']->nama_proyek ?>" readonly placeholder="ASI"></div>
                            <div class="form-group"><label for="basic-icon-default-post" class="form-label">Cluster</label> <select class="custom-select" id="id_cluster" name="id_cluster" disabled><?php foreach ($cluster as $p) {
                                                                                                                                                                                                            echo "<option  value='$p->id_cluster'>$p->nama_cluster</option>";
                                                                                                                                                                                                        } ?></select></div>
                            <div class="form-group"><label for="basic-icon-default-post" class="form-label">Jalan</label> <select class="custom-select" id="id_jalan" name="id_jalan" disabled><?php foreach ($jalan as $p) {
                                                                                                                                                                                                    echo "<option class='$p->nama_cluster' value='$p->id_jalan'>$p->nama_jalan</option>";
                                                                                                                                                                                                } ?></select></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">No Rumah</label> <input name="no_kavling" class="form-control" id="no_kavling" value="" readonly placeholder="31"></div>
                            <div class="form-group"><label for="basic-icon-default-post" class="form-label">Tipe</label> <select class="custom-select" id="id_tipe" name="id_tipe" disabled><?php foreach ($tipe as $p) {
                                                                                                                                                                                                echo "<option  value='$p->id_tipe'>$p->no_tipe_rumah ($p->tipe_rumah)</option>";
                                                                                                                                                                                            } ?></select></div>
                        </div>
                        <div class="col-sm-12 col-lg-6 col-md-6">
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Pricelist</label> <select class="sh-fm custom-select select2" id="sh-id" name="sh-id" value=""></select></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">ROW</label> <input name="sh-row" class="form-control num sh-fm" id="sh-row" value="" readonly></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Tipe</label> <input name="sh-tipe" class="form-control sh-fm text-right" id="sh-tipe" value="" readonly></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">LB</label> <input name="sh-lb" class="form-control num sh-fm" id="sh-lb" value="" readonly></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">LT</label> <input name="sh-lt" class="form-control num sh-fm" id="sh-lt" value="" readonly></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Harga Jual</label> <input name="sh-hargajual" class="form-control num sh-fm" id="sh-hargajual" value="" readonly></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">KPR</label> <input name="sh-kpr" class="form-control num sh-fm" id="sh-kpr" value="" readonly></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Uang Muka</label> <input name="sh-uang_muka" class="form-control num sh-fm" id="sh-uang_muka" value="" readonly></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">BPHTB</label> <input name="sh-bphtb" class="form-control num sh-fm" id="sh-bphtb" value="" readonly></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Biaya Adm</label> <input name="sh-biaya_adm" class="form-control num sh-fm" id="sh-biaya_adm" value="" readonly></div>
                            <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Biaya Proses</label> <input name="sh-biaya_proses" class="form-control num sh-fm" id="sh-biaya_proses" value="" readonly></div>
                        </div>
                        <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">*catatan: gunakan titik koma ";" untuk pemisah nomor rumah jika akan input rumah lebih dari 1 kavling sekaligus</label></div>
                    </div><a href="javascript:void(0)" class="btn btn-primary mr-1" id="set-harga-form-btn" onclick="set_harga()">Simpan</a> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div><?php endif; ?><?php if ($k == 3 || $k == 1): ?><div class="fade modal" id="isi_tagihan-modal">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <form class="modal-content pt-0 add-new-record" id="fm-isi_tagihan" autocomplete="off">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Isi Tagihan</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body flex-grow-1">
                    <p class="modal-title label_alamat" id="label_alamat4"></p>
                    <div class="tab-content">
                        <div class="row">
                            <div class="col-sm-12 col-lg-3 col-md-3">
                                <div class="divider">
                                    <div class="divider-text">Harga Jual</div>
                                </div><input name="mk-id_mkdt" id="mk-id_mkdt" type="hidden">
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Pricelist</label> <select class="mk-fm custom-select select2" id="mk-id" name="mk-id" value=""></select></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Tanggal PriceList</label> <input name="mk-tgl_harga" class="form-control mk-fm text-right" id="mk-tgl_harga" value="" readonly></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Harga Jual</label> <input name="mk-hargajual" class="form-control num mk-fm" id="mk-hargajual" value="" readonly></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">KPR</label> <input name="mk-kpr" class="form-control num mk-fm" id="mk-kpr" value="" readonly></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Uang Muka</label> <input name="mk-uang_muka" class="form-control num mk-fm" id="mk-uang_muka" value="" readonly></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">BPHTB</label> <input name="mk-bphtb" class="form-control num mk-fm totalbb" id="mk-bphtb" value="" readonly></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Biaya Adm</label> <input name="mk-biaya_adm" class="form-control num mk-fm totalbb" id="mk-biaya_adm" value="" readonly></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Biaya Proses</label> <input name="mk-biaya_proses" class="form-control num mk-fm totalbb" id="mk-biaya_proses" value="" readonly></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">ROW</label> <input name="mk-row" class="form-control num mk-fm" id="mk-row" value="" readonly></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">Tipe</label> <input name="mk-tipe" class="form-control mk-fm text-right" id="mk-tipe" value="" readonly></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">LB</label> <input name="mk-lb" class="form-control num mk-fm" id="mk-lb" value="" readonly></div>
                                <div class="form-group"><label for="basic-icon-default-fullname" class="form-label">LT</label> <input name="mk-lt" class="form-control num mk-fm" id="mk-lt" value="" readonly></div>
                            </div>
                            <div class="col-sm-12 col-lg-3 col-md-3"><input name="id_mkdt" class="form-control num" id="id_mkdt" readonly type="hidden">
                                <div class="divider">
                                    <div class="divider-text">KPR</div>
                                </div>
                                <div class="form-group"><label for="total_biaya2">ACC KPR</label> <input name="mk-harga_kpr_acc" class="form-control num" id="mk-harga_kpr_acc" readonly></div>
                                <div class="form-group"><label for="total_biaya2">Tutun KPR</label> <input name="mk-harga_penambahan_um" class="form-control num" id="mk-harga_penambahan_um" readonly></div>
                                <div class="divider">
                                    <div class="divider-text">Penambahan Biaya</div>
                                </div>
                                <div class="form-group"><label for="total_biaya2">Diskon</label> <input name="mk-diskon" class="form-control num" id="mk-diskon"></div>
                                <div class="form-group"><label for="total_biaya2">PPN</label> <input name="mk-harga_ppn" class="form-control num totalbb" id="mk-harga_ppn"></div>
                                <div class="form-group"><label for="total_biaya2">Penambahan Biaya</label> <input name="mk-harga_penambahan" class="form-control num totalbb" id="mk-harga_penambahan"></div>
                                <div class="form-group"><label for="total_biaya2">Keterangan Penambahan Biaya</label> <textarea class="form-control mk-fm" id="mk-keterangan_harga_penambahan" rows="2" name="mk-keterangan_harga_penambahan" cols="30"></textarea></div>
                            </div>
                            <div class="col-sm-12 col-lg-6 col-md-6">
                                <div class="table-responsive">
                                    <table class="table" id="list_kendaraan">
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
                                                <td class="text-center" colspan="5">Tidak Ada Data</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-lg-6 col-md-6">
                                        <div class="divider">
                                            <div class="divider-text">Tagihan UM</div>
                                        </div>
                                        <div class="form-group"><label for="mk-total_um">Total UM</label> <input name="mk-total_um" class="form-control num" id="mk-total_um" readonly></div><input name="total_cicilan_um" class="form-control num" id="total_cicilan_um" readonly type="hidden"> <input name="id_list_keu" class="form-control" id="id_list_keu" type="hidden"> <input name="id_keuangan" class="form-control" id="id_keuangan" type="hidden">
                                        <div class="form-group"><label>Keterangan</label> <input name="berita_acara" class="form-control" id="berita_acara" required> <span class="help-block"></span></div>
                                        <div class="form-group"><label>Nominal</label> <input name="nominal" class="form-control num tg" id="nominal" required onchange="sum_tg(this.value)"> <span class="help-block"></span></div>
                                        <div class="form-group"><label>Tanggal Jatuh Tempo</label> <input name="jatuh_tempo_tgl" class="form-control flatpickr-human-friendly" id="jatuh_tempo_tgl" required type="date"> <span class="help-block"></span></div>
                                        <div id="cicilan_belong_here"></div><button class="btn waves-effect btn-block btn-outline-primary" type="button" id="tambah_list" onclick="tambah_()">+ Cicilan UM</button>
                                    </div>
                                    <div class="col-sm-12 col-lg-6 col-md-6">
                                        <div class="divider">
                                            <div class="divider-text">Tagihan Biaya-biaya</div>
                                        </div>
                                        <div class="form-group"><label for="mk-total_bb">Total Biaya-biaya</label> <input name="mk-total_bb" class="form-control num" id="mk-total_bb" readonly></div><input name="total_cicilan_bb" class="form-control num" id="total_cicilan_bb" readonly type="hidden"> <input name="id_list_keu_bb" class="form-control" id="id_list_keu_bb" type="hidden"> <input name="id_keuangan_bb" class="form-control" id="id_keuangan_bb" type="hidden">
                                        <div class="form-group"><label>Keterangan</label> <input name="berita_acara_bb" class="form-control" id="berita_acara_bb" required> <span class="help-block"></span></div>
                                        <div class="form-group"><label>Nominal</label> <input name="nominal_bb" class="form-control num tg" id="nominal_bb" required onchange='sum_tg(this.value,"_bb")'> <span class="help-block"></span></div>
                                        <div class="form-group"><label>Tanggal Jatuh Tempo</label> <input name="jatuh_tempo_tgl_bb" class="form-control flatpickr-human-friendly" id="jatuh_tempo_tgl_bb" required type="date"> <span class="help-block"></span></div><button class="btn waves-effect btn-block btn-outline-primary" type="button" id="tambah_list_bb" onclick='tambah_("_bb")'>+ Cicilan BB</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button> <button class="btn btn-primary mr-1 data-submit" id="add-form-isi-tagihan" onclick="save_isi_tagihan(this)" href="javascript:void(0)">Simpan</button></div>
            </form>
        </div>
    </div>
    <div class="fade modal text-left" id="modal_divisi3">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <form class="modal-content pt-0 add-new-record" id="fm-keuangan" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Keuangan</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body flex-grow-1">
                    <p class="modal-title label_konsumen" id="label_konsumen"></p>
                    <p class="modal-title label_alamat" id="label_alamat3"></p>
                    <hr>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a href="#booking" class="nav-link active" role="tab" aria-controls="home" data-toggle="tab" aria-selected="true" id="booking-tab">Booking</a></li>
                        <li class="nav-item"><a href="#tagihan" class="nav-link" role="tab" aria-controls="home" data-toggle="tab" aria-selected="true" id="tagihan-tab">Uang Muka</a></li>
                        <li class="nav-item"><a href="#log_pembayaran" class="nav-link" role="tab" aria-controls="log_pembayaran" data-toggle="tab" aria-selected="false" id="log_pembayaran-tab">Log Pembayaran</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="booking" aria-labelledby="booking-tab" role="tabpanel"><input name="status_mkdt" class="form-control" id="status_mkdt" value="" type="hidden"> <input name="id_kavling" class="form-control id_kavling" value="" type="hidden"> <input name="id_keuangan" class="form-control" id="id_keuangan" value="" type="hidden"> <input name="id_mkdt" class="form-control" id="id_mkdt" value="" type="hidden"> <input name="nama_konsumen" class="form-control" id="nama_konsumen" value="" type="hidden">
                            <div class="col-sm-12 col-lg-6 col-md-6">
                                <div class="form-group"><label for="booking_tgl">Tanggal Booking</label> <input name="booking_tgl" class="form-control flatpickr-human-friendly" id="booking_tgl" disabled placeholder="-"></div>
                                <div class="form-group"><label for="harga_jual">Booking Fee</label> <input name="booking_fee" class="form-control num" id="booking_fee" readonly></div>
                                <hr>
                                <div class="form-group"><label for="status_kavling">Sudah Bayar Booking Fee</label> <select class="form-control" id="booking_fee_paid" name="booking_fee_paid">
                                        <option value="0">Belum</option>
                                        <option value="1">Sudah</option>
                                    </select></div>
                                <div class="form-group"><label for="keu_booking_fee">Booking Fee</label> <input name="keu_booking_fee" class="form-control num" id="keu_booking_fee"></div>
                                <div class="form-group"><label for="keu_booking_tgl">Tanggal Bayar Booking Fee</label> <input name="keu_booking_tgl" class="form-control flatpickr-human-friendly" id="keu_booking_tgl" placeholder="-"></div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tagihan" aria-labelledby="tagihan-tab" role="tabpanel">
                            <div class="row">
                                <div class="col-sm-12 col-lg-6 col-md-6">
                                    <div class="divider">
                                        <div class="divider-text">Tagihan Uang Muka</div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="mb-0 table">
                                            <thead>
                                                <tr>
                                                    <th class="text-nowrap" scope="col">No</th>
                                                    <th class="text-nowrap" scope="col">Berita Acara</th>
                                                    <th class="text-nowrap" scope="col">Nominal</th>
                                                    <th class="text-nowrap" scope="col">Jatuh Tempo</th>
                                                    <th class="text-nowrap" scope="col">Oleh</th>
                                                    <th class="text-nowrap" scope="col">Sudah DIbayar</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tb-data-tagihan"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-lg-3 col-md-3"><a href="#collapseExample" class="btn waves-effect btn-outline-primary col-sm-12" role="button" aria-controls="collapseExample" data-toggle="collapse" aria-expanded="false">Lihat Detail Biaya</a>
                                    <div class="collapse" id="collapseExample">
                                        <div class="form-group"><label for="harga_jual">Harga Jual</label> <input name="harga_jual" class="form-control num" id="harga_jual" readonly></div>
                                        <div class="form-group"><label for="harga_kpr">KPR</label> <input name="harga_kpr" class="form-control num" id="harga_kpr" readonly></div>
                                        <hr>
                                        <div class="form-group"><label for="harga_diskon">Diskon</label> <input name="harga_diskon" class="form-control num" id="harga_diskon" readonly></div>
                                        <div class="form-group"><label for="harga_penambahan">Penambahan Biaya</label> <input name="harga_penambahan" class="form-control num" id="harga_penambahan" readonly></div>
                                        <div class="form-group"><label for="harga_administrasi">Administrasi</label> <input name="harga_administrasi" class="form-control num" id="harga_administrasi" readonly></div>
                                        <div class="form-group"><label for="harga_ppn">PPN</label> <input name="harga_ppn" class="form-control num" id="harga_ppn" readonly></div>
                                        <div class="form-group"><label for="harga_bphtb">BPHTB</label> <input name="harga_bphtb" class="form-control num" id="harga_bphtb" readonly></div>
                                        <div class="form-group"><label for="harga_biaya_proses">Biaya Proses</label> <input name="harga_biaya_proses" class="form-control num" id="harga_biaya_proses" readonly></div>
                                    </div>
                                    <div class="divider">
                                        <div class="divider-text">Total Uang Muka</div>
                                    </div>
                                    <div class="form-group"><label for="total_biaya">Total Uang Muka</label> <input name="total_biaya" class="form-control num" id="total_biaya" readonly></div>
                                    <hr>
                                    <div class="form-group"><label for="sudah_bayar">Sudah Bayar Uang Muka</label> <input name="sudah_bayar" class="form-control num" id="sudah_bayar" readonly></div>
                                    <div class="form-group"><label for="sisa_tagihan">Sisa Tagihan Uang Muka</label> <input name="sisa_tagihan" class="form-control num" id="sisa_tagihan" readonly></div>
                                    <div class="form-group"><label for="sisa_tagihan">Persentase</label> <input name="persentase_bayar_tagihan" class="form-control" id="persentase_bayar_tagihan" readonly style="text-align:right"></div>
                                    <div id="hide_refund">
                                        <div class="divider">
                                            <div class="divider-text">Refund</div>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-control-inline custom-switch"><input name="refund_paid" class="custom-control-input cbp" id="refund_paid" value="1" type="checkbox"> <label for="refund_paid" class="custom-control-label">Pembayaran Selesai</label></div>
                                        </div>
                                        <div class="form-group"><label for="keterangan_refund">Keterangan</label> <textarea class="form-control" id="keterangan_refund" rows="3" name="keterangan_refund" placeholder="Keterangan"></textarea></div>
                                        <div class="form-group"><label for="nominal_refund">Nominal</label> <input name="nominal_refund" class="form-control num" id="nominal_refund"></div>
                                        <div class="form-group"><label for="tanggal_refund">Tanggal Refund</label> <input name="tanggal_refund" class="form-control flatpickr-human-friendly" id="tanggal_refund" placeholder="-"></div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-lg-3 col-md-3">
                                    <div id="hide_lunas">
                                        <div class="divider">
                                            <div class="divider-text">Pembayaran</div>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-control-inline custom-switch"><input name="is_lunas" class="custom-control-input cbp" id="is_lunas" value="1" type="checkbox"> <label for="is_lunas" class="custom-control-label">Pembayaran Selesai</label></div>
                                        </div>
                                        <div class="form-group"><label for="berita_acara">Berita Acara Pembayaran</label> <textarea class="form-control" id="berita_acara" rows="3" name="berita_acara" placeholder="Keterangan"></textarea></div>
                                        <div class="form-group"><label for="sisa_tagihan">Nominal Pembayaran</label> <input name="bayar_tagihan" class="form-control num" id="bayar_tagihan"></div>
                                        <div class="form-group"><label for="tanggal_bayar">Tanggal Pembayaran</label> <input name="tanggal_bayar" class="form-control flatpickr-human-friendly" id="tanggal_bayar" placeholder="-"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="log_pembayaran" aria-labelledby="log_pembayaran-tab" role="tabpanel">
                            <div class="table-responsive">
                                <table class="mb-0 table">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap" scope="col">No</th>
                                            <th class="text-nowrap" scope="col">Berita Acara</th>
                                            <th class="text-nowrap" scope="col">Nominal</th>
                                            <th class="text-nowrap" scope="col">Tanggal Bayar</th>
                                            <th class="text-nowrap" scope="col">Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tb-data-log_pembayaran"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button> <button class="btn btn-primary mr-1 data-submit" id="add-form-btn-keuangan" onclick="return save_keuangan(),!1" href="javascript:void(0)">Simpan</button></div>
                </div>
            </form>
        </div>
    </div>
    <div class="fade modal text-left" id="print_tagihan_modal" aria-labelledby="myModalLabel17" role="dialog" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Print Surat Tagihan</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="col-12 col-md-12 col-xl-12">
                    <div class="card invoice-preview-card">
                        <div class="card-body invoice-padding pb-0">
                            <div class="form-group"><label for="no_sruat">No Surat</label> <input name="no_sruat" class="form-control" id="no_sruat"></div>
                            <div class="form-group"><label for="tanggal_surat_tagihan">Tanggal Surat Tagihan</label> <input name="tanggal_surat_tagihan" class="form-control flatpickr-human-friendly" id="tanggal_surat_tagihan" placeholder="-"></div>
                        </div>
                        <hr class="invoice-spacing">
                        <div class="pt-0 card-body invoice-padding">
                            <div class="invoice-spacing">
                                <div class="col-xl-8 p-0">
                                    <h6 class="mb-2">Ditagihkan Ke:</h6>
                                    <h6 class="mb-25" id="pt_nama_konsumen"></h6>
                                    <p class="card-text mb-25" id="pt_alamat_konsumen"></p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body invoice-padding invoice-product-details">
                            <form class="source-item">
                                <div data-repeater-list="group-a">
                                    <div class="repeater-wrapper" data-repeater-item>
                                        <div class="row">
                                            <div class="table-responsive">
                                                <table class="mb-0 table">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-nowrap" scope="col">No</th>
                                                            <th class="text-nowrap" scope="col">Berita Acara</th>
                                                            <th class="text-nowrap" scope="col">Jatuh Tempo</th>
                                                            <th class="text-nowrap" scope="col">Nominal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tb-print-data-tagihan"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <hr class="invoice-spacing mt-0">
                        <div class="card-body invoice-padding py-0">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-2"><label for="note" class="form-label font-weight-bold">Catatan:</label> <textarea class="form-control" id="note" rows="2">It was a pleasure working with you and your team. We hope you will keep us in mind for future freelance projects. Thank You!</textarea></div>
                                </div>
                            </div>
                        </div><button class="btn btn-primary mr-1 data-submit" id="print-btn" onclick="return doPrint(),!1" href="javascript:void(0)">Print</button> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="fade modal text-left" id="dana_akad_modal" aria-labelledby="dana_akad_modal" role="dialog" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <form class="modal-content pt-0 add-new-record" id="fm-dana_akad" autocomplete="off">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Dana Akad</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body flex-grow-1">
                        <p class="modal-title label_konsumen" id="label_konsumen"></p>
                        <p class="modal-title label_alamat" id="label_alamat3"></p>
                        <hr><input name="id_mkdt" class="form-control" id="id_mkdt" value="" type="hidden"> <input name="id_dana_cair" class="form-control" id="id_dana_cair" value="" type="hidden">
                        <div class="form-group"><label for="nominal_dana_akad">Nominal</label> <input name="nominal_dana_akad" class="form-control num" id="nominal_dana_akad"></div>
                        <div class="form-group"><label for="tgl_cair">Tanggal Rencana Cair</label> <input name="tgl_rencana_cair" class="form-control flatpickr-human-friendly" id="tgl_rencana_cair" placeholder="-"></div>
                        <div class="form-group"><label for="berita_acara">Keterangan</label> <textarea class="form-control" id="keterangan_dana_jaminan" rows="3" name="keterangan_dana_jaminan" placeholder="Keterangan Dana Jaminan"></textarea></div>
                        <hr>
                        <div class="form-group">
                            <div class="custom-control custom-control-inline custom-switch"><input name="dana_akad_cair" class="custom-control-input cbp" id="dana_akad_cair" value="1" type="checkbox"> <label for="dana_akad_cair" class="custom-control-label">Sudah Cair</label></div>
                        </div>
                        <div class="form-group"><label for="tgl_cair">Tanggal Cair</label> <input name="tgl_cair" class="form-control flatpickr-human-friendly" id="tgl_cair" placeholder="-"></div><button class="btn btn-primary mr-1 data-submit" id="add-form-btn-dana_akad" onclick="return save_dana_akad(),!1" href="javascript:void(0)">Simpan</button> <button class="btn btn-outline-secondary" type="reset" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div><?php endif; ?><div class="fade modal" id="modal_detail">
    <div class="modal-dialog modal-lg">
        <form class="modal-content pt-0" id="fm-detail">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Detail Kavling</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <p class="modal-title label_alamat" id="detail_kavling_header"></p>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item"><a href="#mkdt" class="nav-link active" role="tab" aria-controls="mkdt" data-toggle="tab" aria-selected="true" id="mkdt-tab">Konsumen</a></li>
                            <li class="nav-item"><a href="#tgt" class="nav-link" role="tab" aria-controls="tgt" data-toggle="tab" aria-selected="false" id="tgt-tab">Tagihan</a></li>
                            <li class="nav-item"><a href="#legal" class="nav-link" role="tab" aria-controls="legal" data-toggle="tab" aria-selected="false" id="legal-tab">Legal</a></li>
                            <li class="nav-item"><a href="#produksi" class="nav-link" role="tab" aria-controls="produksi" data-toggle="tab" aria-selected="false" id="produksi-tab">Bangunan</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="mkdt" aria-labelledby="mkdt-tab" role="tabpanel"><small class="text-muted" id="last_update_mkdt"></small>
                                <div class="form-group"><label for="sertifikat_tgl">Data Konsumen</label></div>
                                <div class="form-group"><label for="nama_konsumen">Nama Konsumen</label> <input name="nama_konsumen" class="form-control" id="detail_nama_konsumen" disabled></div>
                                <div class="form-group"><label for="alamat_konsumen">Alamat Konsumen</label> <input name="alamat_konsumen" class="form-control" id="detail_alamat_konsumen" disabled></div>
                                <div class="form-group"><label for="nik_konsumen">NIK Konsumen</label> <input name="nik_konsumen" class="form-control" id="detail_nik_konsumen" disabled></div>
                                <div class="form-group"><label for="hp_konsumen">Kontak Konsumen</label> <input name="hp_konsumen" class="form-control" id="detail_hp_konsumen" disabled></div>
                                <div class="form-group"><label for="status_kavling">Status Konsumen</label> <select class="form-control" id="detail_status_konsumen" name="status_konsumen" disabled>
                                        <option value="">-</option>
                                        <option value="Umum">Umum</option>
                                        <option value="KPR">KPR</option>
                                        <option value="Komersil">Komersil</option>
                                        <option value="TWP">TWP</option>
                                    </select></div>
                                <hr>
                                <div class="form-group"><label for="sertifikat_tgl">Status</label></div>
                                <div class="form-group"><label for="status_kavling">Status Kavling</label> <select class="form-control" id="detail_status_mkdt" name="status_mkdt" disabled>
                                        <option value="">-</option>
                                        <option value="Booking">Booking</option>
                                        <option value="Akad">Akad</option>
                                        <option value="Batal">Batal</option>
                                    </select></div>
                                <div class="form-group"><label for="booking_tgl">Tanggal Booking</label> <input name="booking_tgl" class="form-control flatpickr-human-friendly" id="detail_booking_tgl" disabled placeholder="-"></div>
                                <div class="form-group"><label for="harga_jual">Booking Fee</label> <input name="booking_fee" class="form-control num" id="detail_booking_fee" disabled></div>
                                <hr>
                                <div class="form-group"><label for="sertifikat_tgl">Detail Biaya</label></div>
                                <div class="form-group"><label for="harga_jual">Harga Jual</label> <input name="harga_jual" class="form-control num" id="detail_harga_jual" disabled></div>
                                <div class="form-group"><label for="harga_kpr">KPR</label> <input name="harga_kpr" class="form-control num" id="detail_harga_kpr" disabled></div>
                                <hr>
                                <div class="form-group"><label for="harga_diskon">Diskon</label> <input name="harga_diskon" class="form-control num" id="detail_harga_diskon" disabled></div>
                                <div class="form-group"><label for="harga_penambahan">Penambahan Biaya</label> <input name="harga_penambahan" class="form-control num" id="detail_harga_penambahan" disabled></div>
                                <div class="form-group"><label for="harga_administrasi">Administrasi</label> <input name="harga_administrasi" class="form-control num" id="detail_harga_administrasi" disabled></div>
                                <div class="form-group"><label for="harga_ppn">PPN</label> <input name="harga_ppn" class="form-control num" id="detail_harga_ppn" disabled></div>
                                <div class="form-group"><label for="harga_bphtb">BPHTB</label> <input name="harga_bphtb" class="form-control num" id="detail_harga_bphtb" disabled></div>
                                <div class="form-group"><label for="harga_biaya_proses">Biaya Proses</label> <input name="harga_biaya_proses" class="form-control num" id="detail_harga_biaya_proses" disabled></div>
                                <hr>
                                <div class="form-group"><label for="total_biaya">Total</label> <input name="total_biaya" class="form-control num" id="detail_total_biaya" disabled readonly></div>
                                <hr>
                                <div class="form-group"><label for="wawancara_tgl">Tanggal Wawancara</label> <input name="wawancara_tgl" class="form-control flatpickr-human-friendly" id="detail_wawancara_tgl" disabled placeholder="-"></div>
                                <div class="form-group"><label for="sp3k_tgl">Tanggal SP3K</label> <input name="sp3k_tgl" class="form-control flatpickr-human-friendly" id="detail_sp3k_tgl" disabled placeholder="-"></div>
                                <div class="form-group"><label for="rencana_akad_tgl">Rencana Akad</label> <input name="rencana_akad_tgl" class="form-control flatpickr-human-friendly" id="detail_rencana_akad_tgl" disabled placeholder="-"></div>
                                <div class="form-group"><label for="akad_tgl">Tanggal Akad</label> <input name="akad_tgl" class="form-control flatpickr-human-friendly" id="detail_akad_tgl" disabled placeholder="-"></div>
                                <div class="form-group"><label for="mkdt_keterangan">Keterangan</label> <textarea class="form-control" id="detail_mkdt_keterangan" rows="3" name="mkdt_keterangan" placeholder="Keterangan" disabled></textarea></div>
                            </div>
                            <div class="tab-pane" id="tgt" aria-labelledby="tgt-tab" role="tabpanel"><small class="text-muted" id="last_update_keuangan"></small>
                                <div class="form-group"><label for="sertifikat_no_hgb">Persentase</label> <input name="lihat_detail_persentase" class="form-control" id="lihat_detail_persentase" disabled></div>
                                <div class="form-group"><label for="sertifikat_no_hgb">Total Uang Muka</label> <input name="lihat_detail_total_biaya" class="form-control num" id="lihat_detail_total_biaya" disabled></div>
                                <div class="form-group"><label for="sertifikat_no_hgb">Total Sudah Bayar</label> <input name="lihat_detail_sudah_bayar" class="form-control num" id="lihat_detail_sudah_bayar" disabled></div>
                                <div class="form-group"><label for="sertifikat_no_hgb">Sisa</label> <input name="lihat_detail_sisa" class="form-control num" id="lihat_detail_sisa" disabled></div>
                            </div>
                            <div class="tab-pane" id="lpt" aria-labelledby="lpt-tab" role="tabpanel"><small class="text-muted" id="last_update_keuangan"></small>
                                <div class="table-responsive">
                                    <table class="mb-0 table">
                                        <thead>
                                            <tr>
                                                <th class="text-nowrap" scope="col">No</th>
                                                <th class="text-nowrap" scope="col">Berita Acara</th>
                                                <th class="text-nowrap" scope="col">Nominal</th>
                                                <th class="text-nowrap" scope="col">Tanggal Bayar</th>
                                                <th class="text-nowrap" scope="col">Oleh</th>
                                                <th class="text-nowrap" scope="col">Pada</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tb-detail-log_pembayaran"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="legal" aria-labelledby="legal-tab" role="tabpanel"><small class="text-muted" id="last_update_legal"></small>
                                <h5 class="modal-title" id="exampleModalLabel">PBB</h5>
                                <div class="form-group"><label for="sertifikat_no_hgb">PBB</label> <input name="detail_pbb" class="form-control" id="detail_pbb" disabled></div>
                                <h5 class="modal-title" id="exampleModalLabel">Sertifikat</h5>
                                <div class="form-group"><label for="sertifikat_tgl">Tanggal Sertifikat</label> <input name="sertifikat_tgl" class="form-control flatpickr-human-friendly" id="detail_sertifikat_tgl" disabled placeholder="-"></div>
                                <div class="form-group"><label for="sertifikat_no_hgb">Luas</label> <input name="detail_sertifikat_luas" class="form-control" id="detail_sertifikat_luas" disabled></div>
                                <div class="form-group"><label for="sertifikat_no_hgb">No HGB</label> <input name="sertifikat_no_hgb" class="form-control" id="detail_sertifikat_no_hgb" disabled></div>
                                <div class="form-group"><label for="sertifikat_no_split">No Split</label> <input name="sertifikat_no_split" class="form-control" id="detail_sertifikat_no_split" disabled></div>
                                <div class="form-group"><label for="sertifikat_masa_berlaku">Masa Berlaku</label> <input name="sertifikat_masa_berlaku" class="form-control flatpickr-human-friendly" id="detail_sertifikat_masa_berlaku" disabled placeholder="-"></div>
                                <hr>
                                <h5 class="modal-title" id="detail_exampleModalLabel" disabled>IMB</h5>
                                <div class="form-group"><label for="imb_tgl">Tanggal IMB</label> <input name="imb_tgl" class="form-control flatpickr-human-friendly" id="detail_imb_tgl" disabled placeholder="-"></div>
                                <div class="form-group"><label for="imb_no_induk">No Induk</label> <input name="imb_no_induk" class="form-control" id="detail_imb_no_induk" disabled></div>
                                <div class="form-group"><label for="imb_no_split">No Split</label> <input name="imb_no_split" class="form-control" id="detail_imb_no_split" disabled></div>
                                <hr>
                                <h5 class="modal-title" id="detail_exampleModalLabel" disabled>BPHTB</h5>
                                <div class="form-group"><label for="bphtb_tgl">Tanggal BPHTB</label> <input name="bphtb_tgl" class="form-control flatpickr-human-friendly" id="detail_bphtb_tgl" disabled placeholder="-"></div>
                                <div class="form-group"><label for="bphtb_masa_berlaku">Masa Berlaku</label> <input name="bphtb_masa_berlaku" class="form-control flatpickr-human-friendly" id="detail_bphtb_masa_berlaku" disabled placeholder="-"></div>
                                <div class="form-group"><label for="bphtb_validasi">Validasi</label> <input name="bphtb_validasi" class="form-control flatpickr-human-friendly" id="detail_bphtb_validasi" disabled placeholder="-"></div>
                                <hr>
                                <div class="form-group"><label for="nop">NOP</label> <input name="nop_pbb" class="form-control" id="detail_nop_pbb" disabled></div>
                                <div class="form-group"><label for="pph">PPh</label> <input name="pph" class="form-control" id="detail_pph" disabled></div>
                                <div class="form-group"><label for="legal_akad_tgl">Tanggal Akad</label> <input name="legal_akad_tgl" class="form-control flatpickr-human-friendly" id="detail_legal_akad_tgl" disabled placeholder="-"></div>
                                <div class="form-group"><label for="legal_keterangan">Keterangan</label> <textarea class="form-control" id="detail_legal_keterangan" rows="3" name="legal_keterangan" placeholder="Keterangan" disabled></textarea></div>
                            </div>
                            <div class="tab-pane" id="produksi" aria-labelledby="produksi-tab" role="tabpanel"><small class="text-muted" id="last_update_produksi"></small>
                                <div class="form-group">
                                    <div class="custom-control custom-control-inline custom-switch"><input name="detail_pondasi" class="custom-control-input cbp" id="detail_pondasi" value="1" type="checkbox" disabled> <label for="detail_pondasi" class="custom-control-label">Pondasi</label></div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-control-inline custom-switch"><input name="detail_naik_dinding" class="custom-control-input cbp" id="detail_naik_dinding" value="1" type="checkbox" disabled> <label for="detail_naik_dinding" class="custom-control-label">Naik Dinding</label></div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-control-inline custom-switch"><input name="detail_topping_off" class="custom-control-input cbp" id="detail_topping_off" value="1" type="checkbox" disabled> <label for="detail_topping_off" class="custom-control-label">Topping Off</label></div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-control-inline custom-switch"><input name="detail_finishing" class="custom-control-input cbp" id="detail_finishing" value="1" type="checkbox" disabled> <label for="detail_finishing" class="custom-control-label">Finishing</label></div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-control-inline custom-switch"><input name="detail_saluran" class="custom-control-input cbp" id="detail_saluran" value="1" type="checkbox" disabled> <label for="detail_saluran" class="custom-control-label">Saluran</label></div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-control-inline custom-switch"><input name="detail_jalan" class="custom-control-input cbp" id="detail_jalan" value="1" type="checkbox" disabled> <label for="detail_jalan" class="custom-control-label">Jalan</label></div>
                                </div>
                                <hr>
                                <div class="af">
                                    <div class="form-group">
                                        <div class="custom-control custom-control-inline custom-switch"><input name="detail_slo" class="custom-control-input cbp" id="detail_slo" value="1" type="checkbox" disabled> <label for="detail_slo" class="custom-control-label">SLO</label></div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-control-inline custom-switch"><input name="detail_bp" class="custom-control-input cbp" id="detail_bp" value="1" type="checkbox" disabled> <label for="detail_bp" class="custom-control-label">BP</label></div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-control-inline custom-switch"><input name="detail_lpa" class="custom-control-input cbp" id="detail_lpa" value="1" type="checkbox" disabled> <label for="detail_lpa" class="custom-control-label">LPA</label></div>
                                    </div>
                                </div>
                                <div class="form-group"><label for="detail_progres_bangunan">Progres Bangunan</label> <input name="detail_progres_bangunan" class="form-control-range" id="detail_progres_bangunan" value="0" type="range" disabled disabled> <span id="detail_t_progres_bangunan"></span>%</div>
                                <div class="form-group"><label for="detail_produksi_keterangan">Keterangan</label> <textarea class="form-control" id="detail_produksi_keterangan" rows="3" name="detail_produksi_keterangan" placeholder="Keterangan"></textarea></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>/assets/js/magic-wand.min.js"></script>
<script src="<?= base_url() ?>/assets/js/konva.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script>
<script src="<?= base_url() ?>assets/js/siteplan/test.js?v=<?= filemtime(FCPATH.'assets/js/siteplan/test.js') ?>"></script>
</script>
