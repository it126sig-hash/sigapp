<style>
    .sticky-button-wrapper {
        position: sticky;
        bottom: 0;
        background: #fff;
        padding: 12px;
        border-top: 1px solid #ddd;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.08);
        z-index: 10;
    }

    /* Mobile Friendly Styles untuk Modal Mkdt Divisi 4 */
    @media (max-width: 768px) {
        #modal_divisi4 .modal-body-custom {
            flex-direction: column !important;
            overflow: hidden !important; /* Tetap hidden di body, biarkan main content yang scroll */
        }

        #modal_divisi4 .modal-sidebar {
            width: 100% !important;
            border-right: none !important;
            border-bottom: 1px solid #ddd;
            padding: 15px 15px 5px 15px !important;
            flex: 0 0 auto !important; /* Jangan biarkan membesar/mengecil */
        }

        /* Ubah sidebar info box layout agar lebih hemat tempat */
        #modal_divisi4 .sidebar-info-box {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        #modal_divisi4 .sidebar-info-box .sidebar-info-label {
            margin-bottom: 2px;
            font-size: 0.75rem;
        }
        #modal_divisi4 .sidebar-info-box > div {
            width: 48%; /* Bagi 2 kolom */
        }
        #modal_divisi4 .sidebar-section-label {
            display: none; /* Sembunyikan label "Informasi Utama" agar lebih rapi di HP */
        }

        /* Navigasi menjadi mendatar dan bisa di-swipe */
        #modal_divisi4 .modal-sidebar-nav {
            display: flex;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 10px; /* Jarak untuk scrollbar */
            margin-bottom: 0 !important;
            scrollbar-width: thin;
        }

        #modal_divisi4 .sidebar-nav-item {
            white-space: nowrap;
            margin-right: 8px;
            padding: 8px 12px;
            border-radius: 20px;
            background: #f8f9fa;
            border: 1px solid #eee;
        }

        #modal_divisi4 .sidebar-nav-item.active {
            background: #007bff !important; /* Menggunakan standar primary Bootstrap */
            color: #fff !important;
            border-color: #007bff !important;
        }

        #modal_divisi4 .modal-main {
            padding: 15px !important;
            flex: 1 1 auto !important;
            overflow-y: auto !important;
        }
    }
</style>
<!-- ################################## Modal Isi Data Konsumen ##########################################-->
<?php /*echo view('siteplan/modal/mkdt-isi_data_konsumen'); */ ?>

<section class="isi_konsumen">
    <div class="modal fade" id="modal-isi_data_konsumen">
        <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
            <form id="fm-idk_keu" class="add-new-record modal-content pt-0" enctype="multipart/form-data"
                autocomplete="off">
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button> -->
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Isi Data Konsumen</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body flex-grow-1" style="background-color:#eee; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body bg-primary text-light">
                                    <p class="modal-title label_alamat"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="col-12 pt-1">
                                    <div class="refresh_fmmkdt_div ">
                                        <button id="refresh-btn-idk_keu" type="button"
                                            class="btn btn-primary btn-block waves-effect">Tambah Konsumen
                                            Baru</button>
                                    </div>
                                    <div class="delete_kons_div" hidden>
                                        <button id="delete-btn-idk_keu" type="button"
                                            class="btn btn-outline-danger btn-block waves-effect"
                                            onclick="delete_kons(false)">Hapus Konsumen</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="divider divider-left">
                                        <div class="divider-text font-weight-bold">Status Kavling</div>
                                    </div>
                                    <div class="form-group floating-label floating-label-select">
                                        <select required class="form-control tab1" id="idk-status_mkdt"
                                            name="dt-status_mkdt">
                                            <option value="">-</option>
                                            <option value="Booking">Booking</option>
                                            <option value="Akad">Akad</option>
                                            <option value="Batal">Batal</option>
                                        </select>
                                        <label for="idk-status_mkdt">Status Kavling</label>
                                    </div>
                                    <div class="form-group floating-label floating-label-select">
                                        <select required class="form-control tab1" id="idk-is_allin"
                                            name="idk-is_allin">
                                            <option value=0>Tidak</option>
                                            <option value=1>Ya</option>
                                        </select>
                                        <label for="idk-is_allin">Harga All In</label>
                                    </div>

                                    <div id="idk-show_keterangan_batal" class="hidden">
                                        <div class="form-group">
                                            <label for="keterangan_batal">Keterangan Batal</label>
                                            <textarea class="form-control" id="idk-keterangan_batal"
                                                name="dt-keterangan_batal" rows="3" placeholder="Keterangan"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="harga_jual">Nominal Pengembalian Dana</label>
                                            <input type="text" class="form-control num" id="idk-refund"
                                                name="dt-refund">
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div id="div-hargajual">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">Harga Jual
                                                    Pricelist
                                                </div>
                                            </div>
                                            <input type="text" readonly class="form-control num mk-fm"
                                                id="idk-mkdt_hargajual" name="idk-mkdt_hargajual" value="" />
                                            <span>Harga diinput oleh: <span id="idk-mkdt_hargajual_by"
                                                    style="font-weight:bold"></span>
                                                pada: <span id="idk-mkdt_hargajual_tgl"
                                                    style="font-weight:bold"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <a href="" id="btn-print_spptb" target="_blank" class="btn btn-success col-12"><i
                                        class="fa fa-save"></i> Cetak SPPTB</a>
                            </div>
                            <div class="row" id="idk-diskresi_st">
                                <div style=" border: 1px solid red; background-color: red; border-radius: 10px 0px 0px 10px; color: white;"
                                    class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname"
                                            style="color:white">Diskresi harga</label>
                                        <input type="text" readonly class="form-control num" id="idk-diskresi_harga"
                                            name="mkdt_hargajual" value="" />
                                        <span>Diskresi diinput oleh: <span style="font-weight:bold"
                                                id="idk-diskresi_oleh"></span> pada: <span id="idk-diskresi_tgl"
                                                style="font-weight:bold"></span></span>

                                    </div>
                                </div>
                                <div class="col-md-6"
                                    style="border: 1px solid red; background-color: red; border-radius: 0px 10px 10px 0px; color: white;">
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname"
                                            style="color:white">Memo</label>
                                        <textarea name="idk-diskresi_memo" readonly id="idk-diskresi_memo"
                                            class="form-control" cols="30" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9" id="tab-isi-konsumen">
                            <div class="card">
                                <div class="card-body pb-0 pt-0">
                                    <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link locked active" id="idk_data_konsumen-tab"
                                                data-toggle="tab" href="#idk_data_konsumen"
                                                aria-controls="idk_data_konsumen" role="tab" aria-selected="true">1.
                                                Data Konsumen ></a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link locked" id="idk_biaya-tab" data-toggle="tab"
                                                href="#idk_biaya" aria-controls="idk_biaya" role="tab"
                                                aria-selected="true">2. Harga Jual
                                                ></a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link locked" id="idk_tagihan-tab" data-toggle="tab"
                                                href="#idk_tagihan" aria-controls="data_konsumen" role="tab"
                                                aria-selected="true">3. Tagihan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="idk_arsip-tab" data-toggle="tab" href="#idk_arsip"
                                                aria-controls="idk_arsip" role="tab" aria-selected="true">SPPTB
                                                Ditandatangani</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="idk_riwayat-tab" data-toggle="tab"
                                                href="#idk_riwayat" aria-controls="idk_riwayat" role="tab"
                                                aria-selected="true">Riwayat Pindah
                                                Kavling/Ganti Nama</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="tab-content">
                                <div class="tab-pane show active" id="idk_data_konsumen"
                                    aria-labelledby="idk_data_konsumen-tab" role="tabpanel">
                                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                                    <input type="hidden" class="form-control" id="idk-id_mkdt" name="id_mkdt"
                                        value="" />
                                    <input type="hidden" class="form-control" id="idk-id_konsumen" name="id_konsumen"
                                        value="" />

                                    <input type="hidden" class="form-control" id="idk-harga_akhir"
                                        name="idk-harga_akhir" value="" readonly />
                                    <input type="hidden" class="form-control" id="idk-hargajual" name="idk-hargajual"
                                        value="" readonly />
                                    <input type="hidden" class="form-control" id="idk-kpr" name="idk-kpr" value=""
                                        readonly />
                                    <input type="hidden" class="form-control" id="idk-uang_muka" name="mkdt-uang_muka"
                                        value="" readonly />
                                    <input type="hidden" class="form-control" id="idk-bphtb" name="idk-bphtb" value=""
                                        readonly />
                                    <input type="hidden" class="form-control" id="idk-biaya_adm" name="idk-biaya_adm"
                                        value="" readonly />
                                    <input type="hidden" class="form-control" id="idk-biaya_proses"
                                        name="idk-biaya_proses" value="" readonly />

                                    <input type="hidden" class="form-control" id="idk_data_baru" name="mkdt_data_baru"
                                        value="" />

                                    <div class="row align-items-stretch">
                                        <div class="col-sm-12 col-md-12 col-lg-12 text-center">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">File Upload</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4"> <!-- KTP -->
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">KTP</label>
                                                                <div class="dropzone dropzone-lg custom-file"
                                                                    id="dz-ktp">
                                                                    <input type="file"
                                                                        class="custom-file-input dz-input"
                                                                        accept="image/*" name="file_ktp" id="file_ktp">
                                                                    <div class="dz-inner">
                                                                        <div class="dz-preview" id="prev_file_ktp">
                                                                        </div>
                                                                        <div class="dz-placeholder">
                                                                            <div class="h5 mb-1">Tarik & letakkan gambar
                                                                                ke sini</div>
                                                                            <div class="text-muted">atau klik untuk
                                                                                pilih file (PNG/JPG maks 5 MB)</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <a href="" id="idk-file_ktp-here"
                                                                onclick="window.open(this.href, '_blank'); return false;"
                                                                class="w-100 btn btn-outline-primary">klik
                                                                untuk melihat file</a>
                                                        </div>
                                                        <div class="col-md-4"> <!-- NPWP -->
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">NPWP</label>
                                                                <div class="dropzone dropzone-lg custom-file"
                                                                    id="dz-npwp">
                                                                    <input type="file"
                                                                        class="custom-file-input dz-input"
                                                                        accept="image/*" name="file_npwp"
                                                                        id="file_npwp">
                                                                    <div class="dz-inner">
                                                                        <div class="dz-preview" id="prev_file_npwp">
                                                                        </div>
                                                                        <div class="dz-placeholder">
                                                                            <div class="h5 mb-1">Tarik & letakkan gambar
                                                                                ke sini</div>
                                                                            <div class="text-muted">atau klik (PNG/JPG
                                                                                maks 5 MB)</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <a href="" id="idk-file_npwp-here"
                                                                onclick="window.open(this.href, '_blank'); return false;"
                                                                class=" btn btn-outline-primary w-100">klik untuk
                                                                melihat file</a>
                                                        </div>
                                                        <div class="col-md-4"> <!-- Data Diri (PDF) -->
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">Data Diri (PDF)</label>
                                                                <div class="dropzone dropzone-lg custom-file"
                                                                    id="dz-data-diri">
                                                                    <input type="file"
                                                                        class="custom-file-input dz-input"
                                                                        accept="application/pdf" name="file_data_diri"
                                                                        id="file_data_diri">
                                                                    <div class="dz-inner">
                                                                        <div class="dz-preview"
                                                                            id="prev_file_data_diri"></div>
                                                                        <div class="dz-placeholder">
                                                                            <div class="h5 mb-1">Tarik & letakkan PDF ke
                                                                                sini</div>
                                                                            <div class="text-muted">atau klik (PDF maks
                                                                                10 MB)</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <a href="" id="idk-file_data_diri-here"
                                                                class="btn btn-outline-primary w-100"
                                                                onclick="window.open(this.href, '_blank'); return false;">klik
                                                                untuk melihat file</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                        </div>
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">Data Konsumen</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Data Pribadi
                                                                </div>
                                                            </div>

                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="st-mkdt-no_spptb" name="no_spptb" placeholder=" "
                                                                    required>
                                                                <label for="idk-no_spptb">No SPPTB</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-nama_konsumen" required name="nama_konsumen"
                                                                    placeholder=" ">
                                                                <label for="idk-nama_konsumen">Nama Konsumen</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-nik_konsumen" name="nik_konsumen"
                                                                    placeholder=" " required>
                                                                <label for="idk-nik_konsumen">No. KTP</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-alamat_konsumen" name="alamat_konsumen"
                                                                    placeholder=" ">
                                                                <label for="idk-alamat_konsumen">Alamat Konsumen</label>
                                                            </div>

                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-npwp_konsumen" name="npwp_konsumen"
                                                                    placeholder=" ">
                                                                <label for="idk-npwp_konsumen">NPWP</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-hp_konsumen" name="hp_konsumen"
                                                                    placeholder=" ">
                                                                <label for="idk-hp_konsumen">No. HP/telp</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-email_konsumen" name="email_konsumen"
                                                                    placeholder=" ">
                                                                <label for="idk-email_konsumen">Email Konsumen</label>
                                                            </div>
                                                            <div class="form-group hidden">
                                                                <label for="idk-status_konsumen">Status Konsumen</label>
                                                                <select class="form-control" id="idk-status_konsumen"
                                                                    name="status_konsumen">
                                                                    <option value="">-</option>
                                                                    <option value="Umum">Umum</option>
                                                                    <option value="TWP">TWP</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Data Instansi
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-nama_instansi" name="nama_instansi"
                                                                    placeholder=" " required>
                                                                <label for="idk-nama_instansi">Nama Instansi</label>
                                                            </div>

                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-alamat_instansi" name="alamat_instansi"
                                                                    placeholder=" ">
                                                                <label for="idk-alamat_instansi">Alamat Instansi</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-tel_instansi" name="tel_instansi"
                                                                    placeholder=" ">
                                                                <label for="idk-tel_instansi">No Hpt/telp
                                                                    Instansi</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-email_instansi" name="email_instansi"
                                                                    placeholder=" ">
                                                                <label for="idk-email_instansi">Email Instansi</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-alamat_surat" name="alamat_surat"
                                                                    placeholder=" ">
                                                                <label for="idk-alamat_surat">Alamat Surat</label>
                                                            </div>

                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <select required class="form-control" id="idk-pekerjaan"
                                                                    name="pekerjaan">
                                                                    <option value="Karyawan">Karyawan</option>
                                                                    <option value="Wirausaha">Wirausaha</option>
                                                                </select>
                                                                <label for="idk-pekerjaan">Pekerjaan</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" placeholder=" " class="form-control"
                                                                    id="idk-lama_bekerja" name="lama_bekerja">
                                                                <label for="idk-lama_bekerja">Lama Bekerja</label>
                                                            </div>
                                                            <div class="form-group floating-label">

                                                                <input type="text" placeholder=" " class="form-control"
                                                                    id="idk-bidang_pekerjaan" name="bidang_pekerjaan">
                                                                <label for="bidang_pekerjaan">Bidang Pekerjaan</label>
                                                            </div>


                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Status
                                                                    Pernikahan</div>
                                                            </div>

                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <select class="form-control gn tab1"
                                                                    id="idk-status_pernikahan" name="status_pernikahan">
                                                                    <option value="Belum Kawin">Belum Kawin</option>
                                                                    <option value="Kawin">Kawin</option>
                                                                    <option value="Cerai Mati">Cerai Mati</option>
                                                                    <option value="Cerai Hidup">Cerai Hidup</option>
                                                                </select>
                                                                <label for="idk-status_pernikahan">Status
                                                                    Pernikahan</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-nama_pasangan" name="nama_pasangan"
                                                                    placeholder=" ">
                                                                <label for="idk-nama_pasangan">Nama</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-nik_pasangan" name="nik_pasangan"
                                                                    placeholder=" ">
                                                                <label for="idk-nik_pasangan">No. KTP</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-hp_pasangan" name="hp_pasangan"
                                                                    placeholder=" ">
                                                                <label for="idk-hp_pasangan">No. HP/Telp</label>
                                                            </div>
                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <label for="idk-status_pekerjaan_pasangan">Status
                                                                    Pekerjaan</label>
                                                                <select required class="form-control"
                                                                    id="idk-status_pekerjaan_pasangan"
                                                                    name="status_pekerjaan_pasangan">
                                                                    <option value="Bekerja">Bekerja</option>
                                                                    <option value="Tidak Bekerja">Tidak Bekerja</option>
                                                                    <option value="Ibu Rumah Tangga">Ibu Rumah Tangga
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-instansi_pasangan" name="instansi_pasangan"
                                                                    placeholder=" ">
                                                                <label for="idk-instansi_pasangan">Instansi</label>
                                                            </div>

                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Sales & Promo
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control" id="idk-sales"
                                                                    name="sales" placeholder=" ">
                                                                <label for="idk-sales">Sales</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control" id="idk-promo"
                                                                    name="promo" placeholder=" ">
                                                                <label for="idk-promo">Promo/Bonus/Hadiah</label>
                                                            </div>

                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">TUNAI/KPR
                                                                </div>
                                                            </div>

                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <select required class="form-control" id="idk-is_kpr"
                                                                    name="is_kpr" onchange="sum_mktotal()">
                                                                    <option value="0">TUNAI/CASH KERAS</option>
                                                                    <option value="2">TUNAI/CASH BERTAHAP</option>
                                                                    <option value="1">KPR</option>
                                                                </select>
                                                                <label for="idk-is_kpr">Tunai/KPR</label>
                                                            </div>
                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <select required class="form-control"
                                                                    id="idk-is_subsidi" name="is_subsidi">
                                                                    <option value="0">Non-Subsidi</option>
                                                                    <option value="1">Subsidi</option>
                                                                </select>
                                                                <label for="idk-is_subsidi">Subsidi/Non-Subsidi</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control"
                                                                    id="idk-jenis_subsidi" name="jenis_subsidi"
                                                                    placeholder=" ">
                                                                <label for="idk-jenis_subsidi">Jenis Subsidi</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>


                                    </div>

                                    <div class="sticky-button-wrapper">
                                        <div>
                                            <button type="reset" class="btn btn-outline-danger mr-1"
                                                data-dismiss="modal">X Tutup</button>
                                            <a class="btn btn-primary data-submit mr-1" href="javascript:void(0)"
                                                onclick="btnNext('#idk_biaya-tab')">
                                                Selanjutnya <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="idk_biaya" aria-labelledby="idk_biaya-tab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">Detail Harga Jual</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">1. Booking
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" required id="idk-booking_tgl"
                                                                    name="dt-booking_tgl"
                                                                    class="form-control flatpickr-human-friendly tab2"
                                                                    placeholder=" " />

                                                                <label for="idk-booking_tgl">Tanggal Booking</label>
                                                            </div>

                                                            <div class="form-group floating-label">
                                                                <input type="text" required
                                                                    class="form-control num tab2" disabled placeholder=" "
                                                                    id="idk-booking_fee" name="dt-booking_fee">
                                                                <label for="idk-booking_fee">Booking Fee</label>
                                                            </div>
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">2. Harga Jual
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text"
                                                                    class="form-control text-right mk-fm flatpickr-human-friendly"
                                                                    id="mk-tgl_harga" name="mk-tgl_harga" value=""
                                                                    readonly placeholder=" " />
                                                                <label class="form-label" for="mk-tgl_harga">Tanggal
                                                                    PriceList</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text"
                                                                    class="form-control num mk-fm hitung-tambah"
                                                                    id="mk-hargajual" name="mk-hargajual" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label" for="mk-hargajual">Harga
                                                                    Jual</label>
                                                            </div>

                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control num mk-fm "
                                                                    id="mk-hargajual_net" name="mk-hargajual_net"
                                                                    value="" placeholder=" " />
                                                                <label class="form-label" for="mk-hargajual_net">Harga
                                                                    Jual
                                                                    Net</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-kpr" name="mk-kpr" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label"
                                                                    for="mk-kpr">KPR(Pengajuan)</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-uang_muka" name="mk-uang_muka" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label" for="mk-uang_muka">Uang
                                                                    Muka</label>
                                                            </div>


                                                        </div>

                                                        <div class="col-md-3">

                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">3.
                                                                    Biaya-biaya</div>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-biaya_adm" name="mk-biaya_adm" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label" for="mk-biaya_adm">Biaya
                                                                    Adm</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text"
                                                                    class="form-control num mk-fm totalbb" id="mk-ppn"
                                                                    name="mk-ppn" placeholder=" ">
                                                                <label for="mk-ppn">PPN</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text"
                                                                    class="form-control num mk-fm totalbb" id="mk-bphtb"
                                                                    name="mk-bphtb" value="" placeholder=" " />
                                                                <label class="form-label" for="mk-bphtb">BPHTB</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text"
                                                                    class="form-control num mk-fm totalbb"
                                                                    id="mk-biaya_proses" name="mk-biaya_proses" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label" for="mk-biaya_proses">Biaya
                                                                    Proses</label>
                                                            </div>
                                                            <!-- disembunyikan dulu karna masih belum tau bakal kepake atau engga -->
                                                            <div class="hidden">
                                                                <div class="form-group floating-label">
                                                                    <input type="text" class="form-control num mk-fm"
                                                                        id="mk-row" name="mk-row" value=""
                                                                        placeholder=" " />
                                                                    <label class="form-label" for="mk-row">ROW</label>
                                                                </div>
                                                                <div class="form-group floating-label">
                                                                    <input type="text"
                                                                        class="form-control mk-fm text-right"
                                                                        id="mk-tipe" name="mk-tipe" value=""
                                                                        placeholder=" " />
                                                                    <label class="form-label" for="mk-tipe">Tipe</label>
                                                                </div>
                                                                <div class="form-group floating-label">
                                                                    <input type="text" class="form-control num mk-fm"
                                                                        id="mk-lb" name="mk-lb" value=""
                                                                        placeholder=" " />
                                                                    <label class="form-label" for="mk-lb">LB</label>
                                                                </div>
                                                                <div class="form-group floating-label">
                                                                    <input type="text" class="form-control num mk-fm"
                                                                        id="mk-lt" name="mk-lt" value=""
                                                                        placeholder=" " />
                                                                    <label class="form-label" for="mk-lt">LT</label>
                                                                </div>

                                                            </div>
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">4. Biaya
                                                                    Tambahan</div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="total_biaya2">Biaya Kelebihan Tanah</label>
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-harga_penambahan_tanah"
                                                                    name="mk-harga_penambahan_tanah">
                                                            </div>
                                                            <div class="form-group hidden">
                                                                <label for="total_biaya2">Keterangan Penambahan
                                                                    Biaya</label>
                                                                <textarea name="mk-keterangan_harga_penambahan"
                                                                    id="mk-keterangan_harga_penambahan"
                                                                    class="form-control mk-fm" cols="30"
                                                                    rows="2"></textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="total_biaya2">Biaya Kavling
                                                                    Strategis</label>
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-harga_penambahan" name="mk-harga_penambahan">
                                                            </div>

                                                        </div>

                                                        <div class="col-md-3">

                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">5. Potongan
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label hidden" id="hjdis">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-diskon_harga_jual"
                                                                    name="mk-diskon_harga_jual" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label"
                                                                    for="mk-diskon_harga_jual">Diskon Harga
                                                                    Jual</label>
                                                            </div>
                                                            <div class="form-group floating-label" id="umdis">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-diskon_uang_muka" name="mk-diskon_uang_muka"
                                                                    value="" placeholder=" " />
                                                                <label class="form-label"
                                                                    for="mk-diskon_uang_muka">Diskon</label>
                                                            </div>
                                                            <div class="form-group floating-label" id="sbumdis">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-harga_sbum" name="mk-harga_sbum" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label" for="mk-sbum">SBUM</label>
                                                            </div>
                                                            <!-- <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">6. KPR Diseutjui</div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="total_biaya2">KPR Disetujui</label>
                                                                <input readonly type="text" class="form-control num mk-fm"
                                                                    id="mk-harga_kpr_acc" name="mk-harga_kpr_acc">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="total_biaya2">Turun KPR</label>
                                                                <input readonly type="text" class="form-control num mk-fm"
                                                                    id="mk-harga_penambahan_um" name="mk-harga_penambahan_um">
                                                            </div> -->
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">6.
                                                                    Catatan/Keterangan</div>
                                                            </div>
                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <textarea class="form-control" rows="4" id="idk-rincian"
                                                                    name=" "></textarea>
                                                                <label class="form-label" for="mk-lt">Keterangan</label>
                                                            </div>


                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">7. Total
                                                                </div>
                                                            </div>
                                                            <div class="form-group hidden">
                                                                <label>Total Uang Muka + Biaya ADM</label>
                                                                <input readonly type="text" class="form-control num tum"
                                                                    id="mk-tum" name="mk-tum">
                                                            </div>
                                                            <div class="form-group hidden">
                                                                <label>Total Biaya-Biaya</label>
                                                                <input readonly type="text" class="form-control num tbb"
                                                                    id="mk-tbb" name="mk-tbb">
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <label>Total Harga Allin</label>
                                                                <input placeholder=" " type="text" required
                                                                    class="form-control num mk-fm" id="mk-harga_allin"
                                                                    name="mk-harga_allin">
                                                            </div>
                                                            <div class="form-group  floating-label">
                                                                <label>Grand Total</label>
                                                                <input readonly type="text" placeholder=" "
                                                                    class="form-control num tgt" id="mk-tgt"
                                                                    name="mk-tgt">
                                                            </div>

                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>


                                    </div>
                                    <div class="sticky-button-wrapper">
                                        <div>
                                            <button type="reset" class="btn btn-outline-danger mr-1"
                                                data-dismiss="modal">Tutup</button>

                                            <a onclick="btnNext('#idk_data_konsumen-tab')"
                                                class="btn btn-secondary mr-1" href="javascript:void(0)"><i
                                                    class="fa fa-arrow-left" aria-hidden="true"></i> Sebelumnya</a>
                                            <a class="btn btn-primary data-submit mr-1" href="javascript:void(0)"
                                                onclick="btnNext('#idk_tagihan-tab')">
                                                Selanjutnya <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="idk_tagihan" aria-labelledby="idk_tagihan-tab"
                                    role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-4 col-lg-4">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-12 col-lg-12">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h5 class="card-title">Buat Tagihan</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Total Yang
                                                                    Harus Dibayar</div>
                                                            </div>
                                                            <div class="form-group">
                                                                <input readonly type="text" class="form-control num tgt"
                                                                    id="mk-total_tot" name="mk-total_tot">
                                                            </div>
                                                            <div class="form-group" hidden>
                                                                <label for="mk-total_um">Total Uang Muka</label>
                                                                <input readonly type="text" class="form-control num tum"
                                                                    id="mk-total_um" name="mk-total_um">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="total_cicilan">Total Cicilan UM</label>
                                                                <input readonly type="hidden" class="form-control num"
                                                                    id="mk-total_cicilan_um" name="total_cicilan_um">
                                                            </div>
                                                            <input name="id_list_keu" id="id_list_keu"
                                                                class="form-control" type="hidden">
                                                            <input name="id_keuangan" id="id_keuangan"
                                                                class="form-control" type="hidden">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Buat Tagihan
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <select class="form-control" required
                                                                    name="berita_acara" id="berita_acara">
                                                                    <option value="Angsuran">Angsuran</option>
                                                                    <option value="Uang Muka">Uang Muka</option>
                                                                    <option value="Biaya Administrasi">Biaya
                                                                        Administrasi</option>
                                                                    <option value="Turun KPR">Turun KPR</option>
                                                                    <option value="Biaya Kavling Strategis">Biaya
                                                                        Kavling Strategis
                                                                    </option>
                                                                    <option value="Biaya Kelebihan Tanah">Biaya
                                                                        Kelebihan Tanah
                                                                    </option>
                                                                    <option value="PPN">PPN</option>
                                                                    <option value="BPHTB">BPHTB</option>
                                                                    <option value="Biaya Proses">Biaya Proses</option>
                                                                </select>
                                                                <label>Untuk Tagihan</label>
                                                                <!-- <input required name="berita_acara" id="berita_acara"
                                                        class="form-control" type="text"> -->
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Nominal</label>
                                                                <input name="nominal" id="nominal"
                                                                    onchange="sum_tg(this.value)"
                                                                    class="form-control num tg" type="text">
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Tanggal Jatuh Tempo</label>
                                                                <input required name="jatuh_tempo_tgl"
                                                                    id="jatuh_tempo_tgl"
                                                                    class="form-control flatpickr-human-friendly"
                                                                    type="date">
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <div id="cicilan_belong_here"></div>
                                                            <button id="tambah_list" type="button"
                                                                class="btn btn-outline-primary btn-block waves-effect"
                                                                onclick="tambah_()">+ Tagihan Angsuran</button>
                                                            <!-- <button id="hapus_list" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="hapus()">+ Hapus List</button> -->
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-sm-12 col-md-6 col-lg-6 " hidden>
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h5 class="card-title">Tagihan Biaya-biaya</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="form-group">
                                                                <label for="mk-total_bb">Total Biaya-biaya</label>
                                                                <input readonly type="text" class="form-control num tbb"
                                                                    id="mk-total_bb" name="mk-total_bb">
                                                            </div>
                                                            <!-- <div class="form-group">
                                        <label for="total_cicilan">Total Cicilan UM</label> -->
                                                            <input readonly type="hidden" class="form-control num"
                                                                id="total_cicilan_bb" name="total_cicilan_bb">
                                                            <!-- </div> -->
                                                            <input name="id_list_keu_bb" id="id_list_keu_bb"
                                                                class="form-control" type="hidden">
                                                            <input name="id_keuangan_bb" id="id_keuangan_bb"
                                                                class="form-control" type="hidden">
                                                            <div class="form-group">
                                                                <label>Untuk Tagihan</label>
                                                                <select class="form-control" required
                                                                    name="berita_acara_bb" id="berita_acara_bb">
                                                                    <option value="PPN">PPN</option>
                                                                    <option value="BPHTB">BPHTB</option>
                                                                    <option value="Biaya Proses">Biaya Proses</option>
                                                                </select>

                                                                <!-- <input required name="berita_acara_bb" id="berita_acara_bb"
                                                        class="form-control" type="text"> -->
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Nominal</label>
                                                                <input name="nominal_bb" id="nominal_bb"
                                                                    onchange="sum_tg(this.value, '_bb')"
                                                                    class="form-control num tg" type="text">
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Tanggal Jatuh Tempo</label>
                                                                <input required name="jatuh_tempo_tgl_bb"
                                                                    id="jatuh_tempo_tgl_bb"
                                                                    class="form-control flatpickr-human-friendly"
                                                                    type="date">
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <button id="tambah_list_bb" type="button"
                                                                class="btn btn-outline-primary btn-block waves-effect"
                                                                onclick="tambah_('_bb')">+ Tagihan Biaya-biaya</button>
                                                            <!-- <button id="hapus_list" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="hapus()">+ Hapus List</button> -->
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-8 col-lg-8">

                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">List Tagihan</h5>
                                                </div>
                                                <div class="card-body">
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
                                                                    <td colspan="5" class="text-center">Tidak Ada Data
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <!-- <button class="btn btn-sm btn-primary" onclick="addRow()">Tambah Baris</button> -->

                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                    <div class="sticky-button-wrapper">
                                        <div>
                                            <button type="reset" class="btn btn-outline-danger mr-1"
                                                data-dismiss="modal">Tutup</button>

                                            <a onclick="btnNext('#idk_biaya-tab')" class="btn btn-secondary mr-1"
                                                href="javascript:void(0)"><i class="fa fa-arrow-left"
                                                    aria-hidden="true"></i> Sebelumnya</a>
                                            <a class="btn btn-success data-submit mr-1" href="javascript:void(0)"
                                                onclick="btnNext('save')">
                                                Simpan <i class="fa fa-save" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="idk_arsip" aria-labelledby="idk_arsip-tab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="card file-container">
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>File SPPTB Yang Sudah Ditandatangani</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input"
                                                                accept="application/pdf" name="file_spptb"
                                                                id="idk_file_spptb" onchange="" />
                                                            <label class="custom-file-label" id="label-idk_file_spptb"
                                                                for="idk_file_spptb">Upload SPPTB yang sudah
                                                                ditandatangani</label>
                                                        </div>
                                                        <div id="list-idk_file_spptb">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Lampiran Surat Kuasa SPPTB</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input"
                                                                accept="application/pdf" name="file_surat_kuasa"
                                                                id="idk_file_surat_kuasa" onchange="" />
                                                            <label class="custom-file-label"
                                                                id="label-idk_file_surat_kuasa"
                                                                for="idk_file_surat_kuasa">Upload Lampiran Surat Kuasa
                                                                SPPTB</label>
                                                        </div>
                                                        <div id="list-idk_lampiran">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="card file-container">
                                                <div class="card-head">
                                                    <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" id="spptb_ttd-tab"
                                                                data-toggle="tab" href="#spptb_ttd"
                                                                aria-controls="spptb_ttd" role="tab"
                                                                aria-selected="true">SPPTB Sudah Ditandatangan</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" id="spptb_riwayat-tab" data-toggle="tab"
                                                                href="#spptb_riwayat" aria-controls="spptb_riwayat"
                                                                role="tab" aria-selected="true">Riwayat Upload SPPTB</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="card-body">
                                                    <div class="tab-content">
                                                        <div class="tab-pane show active" id="spptb_ttd"
                                                            aria-labelledby="spptb_ttd-tab" role="tabpanel">
                                                            <div id="spptb_ttd_file"></div>
                                                        </div>
                                                        <div class="tab-pane" id="spptb_riwayat"
                                                            aria-labelledby="spptb_riwayat-tab" role="tabpanel">
                                                            <div class="table-responsive">
                                                                <table class="table mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>No</th>
                                                                            <th>File</th>
                                                                            <th>Oleh</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="spptb_ttd_file-here"></tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="sticky-button-wrapper">
                                        <div>
                                            <button type="reset" class="btn btn-outline-danger mr-1"
                                                data-dismiss="modal">Tutup</button>

                                            <a onclick="btnNext('#idk_tagihan-tab')" class="btn btn-secondary mr-1"
                                                href="javascript:void(0)"><i class="fa fa-arrow-left"
                                                    aria-hidden="true"></i> Sebelumnya</a>
                                            <a class="btn btn-success data-submit mr-1" href="javascript:void(0)"
                                                onclick="btnNext('save')">
                                                Simpan <i class="fa fa-save" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="idk_riwayat" aria-labelledby="idk_riwayat-tab"
                                    role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6 col-lg-6">

                                            <div class="card file-container">
                                                <div class="card-body">
                                                    <button class="btn btn-outline-primary" id="btn-ganti_nama"
                                                        onclick="ganti_nama()">Klik Untuk Ganti Nama Konsumen</button>
                                                    <button class="btn btn-outline-warning" id="btn-refresh-ganti_nama"
                                                        onclick="getRiwayatGantinama()">Muat Ulang Diwayat</button>
                                                    <div class="divider">
                                                        <div class="divider-text">Riwayat Ganti Nama </div>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>File</th>
                                                                    <th>Oleh</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="riwayat_ganti_nama-here"></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="card file-container">
                                                <div class="card-body">
                                                    <button class="btn btn-outline-primary" id="btn-ganti_kavling"
                                                        onclick="ganti_kavling()">Klik Untuk Ganti Kavling</button>
                                                    <button class="btn btn-outline-warning"
                                                        id="btn-refresh-ganti_kavling"
                                                        onclick="getRiwayatGantiKavling()">Muat Ulang Data </button>
                                                    <div class="divider">
                                                        <div class="divider-text">Riwayat Ganti Nama </div>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>File</th>
                                                                    <th>Oleh</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="ganti_kavling-here"></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="sticky-button-wrapper">
                                        <div>
                                            <button type="reset" class="btn btn-outline-danger mr-1"
                                                data-dismiss="modal">Tutup</button>

                                            <a onclick="btnNext('#idk_tagihan-tab')" class="btn btn-secondary mr-1"
                                                href="javascript:void(0)"><i class="fa fa-arrow-left"
                                                    aria-hidden="true"></i> Sebelumnya</a>
                                            <a class="btn btn-success data-submit mr-1" href="javascript:void(0)"
                                                onclick="btnNext('save')">
                                                Simpan <i class="fa fa-save" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <br>
                </div>
                <!-- <div class="modal-footer">
                    <button type="reset" class="btn btn-outline-danger" data-dismiss="modal">Tutup</button>

                    <a id="prev-form-btn-idk_keu" onclick="btnPrev()" disabled="disabled" class="btn btn-secondary mr-1"
                        href="javascript:void(0)"><i class="fa fa-arrow-left" aria-hidden="true"></i> Sebelumnya</a>
                    <a id="add-form-btn-idk_keu" class="btn btn-primary data-submit mr-1" href="javascript:void(0)"
                        onclick="btnNext('#add-form-btn-idk_keu')">
                        Simpan <i class="fa fa-arrow-right" aria-hidden="true"></i></a>

                </div> -->
        </form>
    </div>
</div>
</section>

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
                            <input type="text" class="form-control" id="tp-nama_proyek" readonly name="nama_proyek"
                                value="<?= $data['proyek']->nama_proyek ?>" placeholder="ASI" />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-post">Kavling</label>
                            <textarea class="form-control" id="tp-kavling" name="tp-kavling" rows="6" readonly
                                placeholder="Kavling"></textarea>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label for="perintah_bangun_tgl">Tanggal Perintah Bangun</label>
                            <input type="text" readonly="readonly" id="tp-perintah_bangun_tgl"
                                name="perintah_bangun_tgl" class="form-control flatpickr-human-friendly"
                                placeholder="-" />
                        </div>
                        <div class="form-group">
                            <label>Perintah Bangun</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" accept="application/pdf"
                                    name="perintah_bangun_file" id="tp-perintah_bangun_file" />
                                <label class="custom-file-label" id="label-perintah_bangun_file"
                                    for="label-perintah_bangun_file">Upload File Perintah Bangun</label>
                                <a href="#" target=_blank id="list-tp-upload_perintah_bangun_file"
                                    class="btn btn-outline-primary col-12">Klik untuk lihat file</a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="perintah_bangun_oleh">Oleh</label>
                            <input type="text" readonly="readonly" id="tp-perintah_bangun_oleh"
                                name="perintah_bangun_oleh" class="form-control" placeholder="-" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">*catatan: gunakan titik koma ";"
                            untuk pemisah nomor rumah jika akan input rumah lebih dari 1 kavling sekaligus</label>
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
<div class="modal fade" id="modals-set_harga">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="fm-set_harga" class="add-new-record modal-content modal-content-custom pt-0" style="background-color: transparent;">
            <div class="modal-header-custom">
                <div>
                    <div class="modal-title-main">Manajemen Kavling</div>
                    <div class="modal-title-kavling">Set Harga</div>
                    <div class="header-meta">
                        <span class="header-meta-item"><i class="fas fa-home"></i> Proyek: <?= $data['proyek']->nama_proyek ?></span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" data-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>

            <div class="modal-body-custom">
                <div class="modal-main">



                    <div class="row">
                        <div class="col-md-6">
                            <div class="section-card">
                                <div class="section-card-header">
                                    <div class="section-card-icon icon-amber"><i class="fas fa-map-marker-alt"></i></div>
                                    <h6 class="section-card-title">Kavling Terpilih</h6>
                                </div>
                                <div class="section-card-body">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom" for="sh-kavling">Kavling</label>
                                        <input type="hidden" class="form-control" id="points" readonly name="points" value="" />
                                        <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                                        <input type="hidden" class="form-control" id="nama_proyek" readonly name="nama_proyek" value="<?= $data['proyek']->nama_proyek ?>" />

                                        <textarea class="form-control-custom" id="sh-kavling" name="sh-kavling" rows="3" readonly placeholder="Kavling"></textarea>
                                        <small style="display:block; margin-top:5px; color:var(--text-light); font-size:0.75rem;">*catatan: gunakan titik koma ";" untuk pemisah nomor rumah jika akan input rumah lebih dari 1 kavling sekaligus</small>
                                    </div>
                                </div>
                            </div>
                            <div class="section-card">
                                <div class="section-card-header">
                                    <div class="section-card-icon icon-blue"><i class="fas fa-list"></i></div>
                                    <h6 class="section-card-title">Pilih Pricelist</h6>
                                </div>
                                <div class="section-card-body">
                                    <div class="one-col">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Pricelist</label>
                                            <select class="select2 custom-select sh-fm form-control-custom" id="sh-id" name="sh-id" value=""></select>
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">&nbsp;</label>
                                            <a href="javascript:void(0)" target="_blank" id="sh-pricelist_file" rel="noopener noreferrer" class="btn-outline-custom w-100"><i class="fas fa-file mr-1"></i> Klik untuk melihat file</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="section-card">
                                <div class="section-card-header">
                                    <div class="section-card-icon icon-green"><i class="fas fa-money-bill-wave"></i></div>
                                    <h6 class="section-card-title">Detail Pricelist</h6>
                                </div>
                                <div class="section-card-body">
                                    <div class="one-col">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">ROW</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-row" name="sh-row" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Tipe</label>
                                            <input type="text" class="form-control-custom sh-fm text-right" id="sh-tipe" name="sh-tipe" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Luas Bangunan</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-lb" name="sh-lb" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Luas Tanah</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-lt" name="sh-lt" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Harga Jual</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-hargajual" name="sh-hargajual" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Harga Jual Net</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-hargajual_net" name="sh-hargajual_net" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">KPR</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-kpr" name="sh-kpr" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Uang Muka</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-uang_muka" name="sh-uang_muka" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Biaya Adm</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-biaya_adm" name="sh-biaya_adm" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">BPHTB</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-bphtb" name="sh-bphtb" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">PPN</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-ppn" name="sh-ppn" value="" readonly />
                                        </div>
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Biaya Proses</label>
                                            <input type="text" class="form-control-custom num sh-fm" id="sh-biaya_proses" name="sh-biaya_proses" value="" readonly />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-cancel" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancel</button>
                <a id="set-harga-form-btn" class="btn-save" onclick="set_harga()" href="javascript:void(0)"><i class="fas fa-save mr-1"></i> Simpan Terpilih</a>
            </div>
        </form>
    </div>
</div>
<!--#################################### Modal Mkdt #########################################-->
<div class="modal fade" id="modal_divisi4">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <form id="fm-mkdt" enctype="multipart/form-data" class="add-new-record modal-content modal-content-custom pt-0" autocomplete="off" style="background-color: transparent;">

            <div class="modal-header-custom">
                <div>
                    <div class="modal-title-main">Marketing Data</div>
                    <div class="modal-title-kavling">Perbaharui Status Kavling</div>
                    <div class="header-meta">
                        <span class="header-meta-item label_alamat"></span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" data-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>

            <div class="modal-body-custom" style="padding: 0; overflow: hidden; align-items: stretch;">
                <!-- SIDEBAR NAVIGATION -->
                <div class="modal-sidebar">
                    <div class="sidebar-info-box">
                        <div class="sidebar-info-label">No. SPPTB</div>
                        <div class="sidebar-info-value">
                            <div id="lb-st-no_spptb" class="font-weight-bold" style="min-height:24px;"></div>
                        </div>
                        <div class="sidebar-info-label">Konsumen</div>
                        <div class="sidebar-info-value" style="margin-bottom:8px">
                            <div id="lb-st-nama_konsumen" class="font-weight-bold" style="min-height:24px;"></div>
                        </div>
                    </div>
                    <div class="sidebar-section-label">Informasi Utama</div>

                    <ul class="nav nav-pills modal-sidebar-nav" id="sidebar-tabs-alur" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link sidebar-nav-item" id="tab-sd-status" href="#sd-status">
                                <i class="fas fa-flag"></i> Status Kavling
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link sidebar-nav-item" id="tab-sd-wawancara" href="#sd-wawancara">
                                <i class="fas fa-comments"></i> Wawancara
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link sidebar-nav-item" id="tab-sd-pb" href="#sd-pb">
                                <i class="fas fa-hammer"></i> Perintah Bangun
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link sidebar-nav-item" id="tab-sd-sp3k" href="#sd-sp3k">
                                <i class="fas fa-file-contract"></i> SP3K
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link sidebar-nav-item" id="tab-sd-kpr" href="#sd-kpr">
                                <i class="fas fa-money-check-alt"></i> KPR
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link sidebar-nav-item" id="tab-sd-akad" href="#sd-akad">
                                <i class="fas fa-handshake"></i> Akad
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- MAIN CONTENT AREA (SCROLLABLE) -->
                <div class="modal-main" id="mkdt-main-scroll-area" style="flex-grow: 1; padding: 24px; overflow-y: auto; scroll-behavior: smooth;">
                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                    <input type="hidden" class="form-control" id="id_konsumen" name="id_konsumen" value="" />
                    <input type="hidden" class="form-control" id="mkdt_data_baru" name="mkdt_data_baru" value="" />

                    <?php
                    /*
                    <!-- KONSUMEN BLOCK -->
                    <div id="sd-konsumen" class="scroll-section" style="padding-bottom: 2rem;">
                        <div class="section-card mb-2">
                            <div class="section-card-header">
                                <div class="section-card-icon icon-blue"><i class="fas fa-user"></i></div>
                                <h6 class="section-card-title">Data Konsumen Utama</h6>
                            </div>
                            <div class="section-card-body">
                                <div class="form-group-custom pl-1 pr-1">
                                    <label class="form-label-custom">No SPPTB</label>
                                    <div id="lb-st-no_spptb" class="font-weight-bold" style="min-height:24px;"></div>
                                </div>
                                <div class="form-group-custom pl-1 pr-1">
                                    <label class="form-label-custom">Nama Konsumen</label>
                                    <div id="lb-st-nama_konsumen" class="font-weight-bold" style="min-height:24px;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Aksi Konsumen Tambahan -->
                        <div class="section-card mb-2">
                            <div class="section-card-header">
                                <div class="section-card-icon icon-grey"><i class="fas fa-cog"></i></div>
                                <h6 class="section-card-title">Aksi</h6>
                            </div>
                            <div class="section-card-body">
                                <div id="refresh_fmmkdt_div" class="mb-1">
                                    <button id="refresh_fmmkdt_btn" type="button" class="btn btn-outline-custom w-100">Tambah Konsumen Baru</button>
                                </div>
                                <div id="delete_kons_div">
                                    <button id="delete_kons_btn" type="button" class="btn btn-outline-danger w-100" onclick="delete_kons(false)">Hapus Konsumen</button>
                                 </div>
                            </div>
                        </div>
                    </div>*/ ?>

                    <!-- STATUS KAVLING BLOCK -->
                    <div id="sd-status" class="scroll-section" style="padding-bottom: 2rem;">
                        <div class="section-card mb-2">
                            <div class="section-card-header">
                                <div class="section-card-icon icon-amber"><i class="fas fa-flag"></i></div>
                                <h6 class="section-card-title">Status Booking Kavling</h6>
                            </div>
                            <div class="section-card-body">
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="status_mkdt">Status Booking</label>
                                    <select required class="form-control-custom custom-select" id="status_mkdt" name="status_mkdt">
                                        <option value="">-</option>
                                        <option value="Booking">Booking</option>
                                        <option value="Akad">Akad</option>
                                        <option disabled value="Batal">Batal</option>
                                    </select>
                                </div>
                                <div id="show_keterangan_batal" class="hidden">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom" for="keterangan_batal">Keterangan Batal</label>
                                        <textarea class="form-control-custom" id="keterangan_batal" name="keterangan_batal" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="booking_tgl">Tanggal Booking</label>
                                    <input type="text" id="booking_tgl" name="booking_tgl" class="form-control-custom flatpickr-human-friendly" placeholder="-" readonly />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="booking_fee">Booking Fee</label>
                                    <input type="text" readonly class="form-control-custom num" id="booking_fee" name="booking_fee">
                                </div>
                            </div>
                        </div>

                        <div class="section-card mb-2">
                            <div class="section-card-header">
                                <div class="section-card-icon icon-grey"><i class="fas fa-clipboard-list"></i></div>
                                <h6 class="section-card-title">Status Keterangan</h6>
                            </div>
                            <div class="section-card-body">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Keterangan Khusus</label>
                                    <input type="text" id="mkdt_keterangan" name="mkdt_keterangan" class="form-control-custom" placeholder="ACC SP3K/REJECT/WAWANCARA/DLL" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- WAWANCARA BLOCK -->
                    <div id="sd-wawancara" class="scroll-section" style="padding-bottom: 2rem;">
                        <div class="section-card mb-2">
                            <div class="section-card-header">
                                <div class="section-card-icon icon-teal"><i class="fas fa-comments"></i></div>
                                <h6 class="section-card-title">Detail Wawancara</h6>
                            </div>
                            <div class="section-card-body">
                                <div class="form-group-custom">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="wawancara" name="wawancara" value="1" />
                                        <label class="custom-control-label" for="wawancara">Sudah Wawancara</label>
                                    </div>
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="id_bank">Bank</label>
                                    <select type="text" id="id_bank" name="id_bank" class="form-control-custom select2"></select>
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="bank">Keterangan Bank</label>
                                    <input type="text" id="bank" name="bank" class="form-control-custom" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="wawancara_tgl">Tanggal Wawancara</label>
                                    <input type="text" id="wawancara_tgl" name="wawancara_tgl" class="form-control-custom flatpickr-human-friendly" placeholder="-" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PERINTAH BANGUN BLOCK -->
                    <div id="sd-pb" class="scroll-section" style="padding-bottom: 2rem;">
                        <div class="section-card mb-2">
                            <div class="section-card-header">
                                <div class="section-card-icon icon-amber"><i class="fas fa-hammer"></i></div>
                                <h6 class="section-card-title">Instruksi Pembangunan</h6>
                            </div>
                            <div class="section-card-body">
                                <div class="form-group-custom">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="perintah_bangun" name="perintah_bangun" value="1" />
                                        <label class="custom-control-label" for="perintah_bangun">Perintah Bangun</label>
                                    </div>
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="perintah_bangun_tgl">Tanggal Perintah Bangun</label>
                                    <input type="text" readonly="readonly" id="perintah_bangun_tgl" name="perintah_bangun_tgl" class="form-control-custom flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="perintah_bangun_oleh">Oleh</label>
                                    <input type="text" readonly="readonly" id="perintah_bangun_oleh" name="perintah_bangun_oleh" class="form-control-custom" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom">File Perintah Bangun (PDF)</label>
                                    <div class="custom-file mb-1">
                                        <input type="file" class="custom-file-input" accept="application/pdf" name="perintah_bangun_file" id="perintah_bangun_file" />
                                        <label class="custom-file-label form-control-custom" id="label-perintah_bangun_file" for="label-perintah_bangun_file" style="line-height: inherit; color: var(--text-light);">Pilih File</label>
                                    </div>
                                    <btn id="list-upload_perintah_bangun_file" class='btn-outline-custom w-100' style="display:block; text-align:center;"><i class="fas fa-file-pdf"></i> Lihat File</btn>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SP3K BLOCK -->
                    <div id="sd-sp3k" class="scroll-section" style="padding-bottom: 2rem;">
                        <div class="section-card mb-2">
                            <div class="section-card-header">
                                <div class="section-card-icon icon-green"><i class="fas fa-file-contract"></i></div>
                                <h6 class="section-card-title">Surat Penegasan Persetujuan Penyediaan Kredit</h6>
                            </div>
                            <div class="section-card-body">
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="sp3k_no">No SP3K</label>
                                    <input type="text" id="sp3k_no" name="sp3k_no" class="form-control-custom" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom">File SP3K</label>
                                    <div class="custom-file mb-1">
                                        <input type="file" class="custom-file-input" accept="application/pdf" name="sp3k_file" id="sp3k_file" />
                                        <label class="custom-file-label form-control-custom" id="label-sp3k_file" for="label-sp3k_file" style="line-height: inherit; color: var(--text-light);">Pilih File</label>
                                    </div>
                                    <btn id="list-upload_sp3k_file" class='btn-outline-custom w-100' style="display:block; text-align:center;"><i class="fas fa-file-pdf"></i> Lihat File</btn>
                                </div>
                                <div class="form-group-custom">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="sp3k" name="sp3k" value="1" />
                                        <label class="custom-control-label" for="sp3k">Status Verifikasi SP3K</label>
                                    </div>
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="sp3k_tgl">Tanggal Terbit</label>
                                    <input type="text" id="sp3k_tgl" name="sp3k_tgl" class="form-control-custom flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="sp3k_tgl_exp">Tanggal Kadaluarsa</label>
                                    <input type="text" id="sp3k_tgl_exp" name="sp3k_tgl_exp" class="form-control-custom flatpickr-human-friendly" placeholder="-" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPR BLOCK -->
                    <div id="sd-kpr" class="scroll-section" style="padding-bottom: 2rem;">
                        <div class="section-card mb-2">
                            <div class="section-card-header">
                                <div class="section-card-icon icon-blue"><i class="fas fa-money-check-alt"></i></div>
                                <h6 class="section-card-title">Kredit Pemilikan Rumah (KPR)</h6>
                            </div>
                            <div class="section-card-body">
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="harga_kpr">Nilai Pengajuan</label>
                                    <input type="text" id="harga_kpr" name="harga_kpr" class="form-control-custom num" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="acc_harga_kpr">Nilai Disetujui</label>
                                    <input type="text" id="acc_harga_kpr" name="acc_harga_kpr" class="form-control-custom num" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="harga_turun_kpr">Turun KPR (Selisih)</label>
                                    <input type="text" id="harga_turun_kpr" name="harga_turun_kpr" class="form-control-custom num" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <button type="button" id="btn-add-tagihan-turunkpr" class="btn-save w-100" style="padding: 10px; font-size: 0.85rem;"><i class="fas fa-plus mr-1"></i> Buat Tagihan untuk Turun KPR</button>
                                </div>
                                <div id="mkdt-tagihan_kpr"></div>
                            </div>
                        </div>
                    </div>

                    <!-- AKAD BLOCK -->
                    <div id="sd-akad" class="scroll-section" style="padding-bottom: 3rem;">
                        <div class="section-card mb-2">
                            <div class="section-card-header">
                                <div class="section-card-icon icon-amber"><i class="fas fa-handshake"></i></div>
                                <h6 class="section-card-title">Persiapan Akad</h6>
                            </div>
                            <div class="section-card-body">
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="rencana_akad_tgl">Tanggal Rencana Akad</label>
                                    <input type="text" id="rencana_akad_tgl" name="rencana_akad_tgl" class="form-control-custom flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Notaris</label>
                                    <input type="text" id="notaris" name="notaris" class="form-control-custom" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom">PPJB/AJB</label>
                                    <select class="form-control-custom custom-select" id="is_ajb" name="is_ajb">
                                        <option value=""></option>
                                        <option value="AJB">AJB</option>
                                        <option value="PPJB">PPJB</option>
                                    </select>
                                </div>
                                <div class="form-group-custom">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="akad" name="akad" value="1" />
                                        <label class="custom-control-label" for="akad">Status Akad Rampung</label>
                                    </div>
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="akad_tgl">Tanggal Akad</label>
                                    <input type="text" id="akad_tgl" name="akad_tgl" class="form-control-custom flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group-custom">
                                    <label class="form-label-custom">No Debitur</label>
                                    <input type="text" id="debitur_no" name="debitur_no" class="form-control-custom" placeholder="-" />
                                </div>
                                <div class="form-group-custom hidden">
                                    <label class="form-label-custom">No BAST</label>
                                    <input type="text" id="bast_no" name="bast_no" class="form-control-custom" placeholder="-" />
                                </div>
                                <div class="form-group-custom hidden">
                                    <label class="form-label-custom">BAST File</label>
                                    <div class="custom-file mb-1">
                                        <input type="file" class="custom-file-input" accept="application/pdf" name="bast_file" id="bast_file" />
                                        <label class="custom-file-label form-control-custom" id="label-bast_file" for="label-bast_file" style="line-height:inherit; color:var(--text-light);">Pilih File BAST</label>
                                    </div>
                                    <a href="" target=_blank id="list-upload_bast_file" class="btn-outline-custom w-100" style="display:block; text-align:center;"><i class="fas fa-file-pdf"></i> Lihat File BAST</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-cancel" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancel</button>
                <button type="button" id="add-form-btn-mkdt" class="btn-save" onclick="save_mkdt(this)"><i class="fas fa-save mr-1"></i> Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const mkdtModal = document.getElementById("modal_divisi4");
        if (!mkdtModal) return;

        const scrollArea = document.getElementById("mkdt-main-scroll-area");
        const sections = Array.from(mkdtModal.querySelectorAll(".scroll-section"));
        const navItems = mkdtModal.querySelectorAll(".sidebar-nav-item");

        if (scrollArea && sections.length > 0) {
            // Handle Sidebar Clicks to scroll to section
            navItems.forEach(item => {
                item.addEventListener("click", function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute("href").substring(1);
                    const targetEl = document.getElementById(targetId);
                    if (targetEl && scrollArea) {
                        scrollArea.scrollTo({
                            top: targetEl.offsetTop - scrollArea.offsetTop,
                            behavior: "smooth"
                        });
                    }
                });
            });

            // Handle scroll syncing
            scrollArea.addEventListener("scroll", function() {
                let current = "";
                const currentPosition = scrollArea.scrollTop;

                sections.forEach(section => {
                    const sectionTop = section.offsetTop - scrollArea.offsetTop - 50;
                    if (currentPosition >= sectionTop) {
                        current = section.getAttribute("id");
                    }
                });

                navItems.forEach(item => {
                    item.classList.remove("active");
                    if (item.getAttribute("href") === "#" + current) {
                        item.classList.add("active");
                    }
                });
            });
        }
    });
</script>



<!-- ################################### modal mkdt standing instruction ##################################### -->
<div class="modal fade " id="modals-si">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="fm-si" class="modal-content pt-0" enctype="multipart/form-data">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Standing Instruction</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="row" style="display: flex; justify-content: center;">
                    <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                    <div id="dv-si-here"></div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="btn-si-simpan" class="btn btn-primary mr-1" onclick="save_si()"
                    href="javascript:void(0)">Simpan</a>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    $("#id_bank").select2({
  placeholder: "Pilih Bank",
  allowClear: true,
  ajax: {
    url: base_url + "api/bank",
    dataType: "json",
    delay: 250,
    method: "get",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
      };
    },
    processResults: function (r) {
      // csrfHash = r.token;

      let results = [];
      $.each(r, function (i, v) {
        results.push({
          id: v.id,
          text: `${v.bank}${v.keterangan ? ": (" + v.keterangan + ")" : ""}`,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});

// $('a[data-toggle="tab"]').on("show.bs.tab", function (e) {
//   const targetId = $(e.target).attr("href"); // ex: #profile
//   if (
//     targetId === "#idk_data_konsumen" ||
//     targetId === "#idk_biaya" ||
//     targetId === "#idk_tagihan"
//   ) {
//     if (!state.status.tab.isClosed) {
//       let isValid = isValidKonsumen(getActiveIndex());

//       if (isValid != undefined && !isValid[getActiveIndex()]) {
//         e.preventDefault(); // mencegah tab berpindah
//         return;
//       }
//     }
//   }
// });

// tab button

const containerIsiKonsumen = $("#tab-isi-konsumen");
let latestIsiDataKonsumenRequestId = 0;

// Array urutan tab
// const tabOrder = ["#idk_data_konsumen", "#idk_biaya", "#idk_tagihan"];

// Ambil index tab aktif
// function getActiveIndex() {
//   const activeId = containerIsiKonsumen.find(".tab-pane.active").attr("id");
//   return tabOrder.findIndex((sel) => sel === "#" + activeId);
// }

// Pindah ke tab ke-i
// function goTo(i) {
//   if (i < 0 || i >= tabOrder.length) return;
//   containerIsiKonsumen.find('a[href="' + tabOrder[i] + '"]').tab("show");
// }

// Update tombol
// function updateButtons(next, prev) {
//   const i = getActiveIndex();
//   const bPrev = $(prev);
//   const bNext = $(next);

//   bPrev.prop("disabled", i === 0);

//   if (i === tabOrder.length - 1) {
//     bNext
//       .html('Simpan <i class="fa fa-save" aria-hidden="true"></i>')
//       .data("action", "save")
//       .removeClass("btn-primary")
//       .addClass("btn-success");
//     return true;
//   } else {
//     bNext
//       .html('Selanjutnya <i class="fa fa-arrow-right" aria-hidden="true"></i>')
//       .data("action", "next")
//       .removeClass("btn-success")
//       .addClass("btn-primary");
//     return false;
//   }
// }

function isValidKonsumen(i) {
  let isValid = true;

  if (i == "#idk_biaya-tab" || i == "#idk_data_konsumen-tab") {
    $("#fm-idk_keu")
      .find("input.tab1[required], select.tab1[required]")
      .each(function () {
        let id = $(this).attr("id");
        let value = $(this).val().trim();

        if (value === "") {
          let labelText = $('label[for="' + id + '"]').text();
          isValid = false;
          showToast(labelText + " harus diisi", "warning");
          $(this).focus();
          this.reportValidity();
          return false; // Stop the $.each loop immediately if an invalid field is found
        }
      });
    return isValid;
  } else if (i == "#idk_tagihan-tab") {
    if ($("#idk-booking_tgl").val() == "") {
      showToast("Tanggal Booking harus diisi", "warning");
      $("#idk-booking_tgl").get(0)._flatpickr.open();
      isValid = false;
    } else if ($("#idk-booking_fee").val() == "") {
      showToast("Booking Fee harus diisi", "warning");
      $("#idk-booking_fee").focus();
      isValid = false;
    }

    return isValid;
  } else if (i == "save") {
    if (parseFloat(removeComma($("#mk-total_tot").val() || 0)) > 0) {
      if ($("#mk-total_tot").val() != $("#mk-total_cicilan_um").val()) {
        showToast(
          "Total tagihan tida sesuai dengan total harus dibayar",
          "danger",
        );
        isValid = false;
      }
    }
    return isValid;
  }
}
// Klik NEXT/SIMPAN
function btnNext(next) {
  let isValid = isValidKonsumen(next);
  if (next === "save" && isValid) {
    Swal.fire({
      title: "Konfirmasi",
      text: "Apakah data sudah benar dan akan disimpan?",
      showDenyButton: true,
      confirmButtonText: "Simpan",
      denyButtonText: `Kembali`,
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        simpan_dt_konsumen_keuangan();
      }
    });
  } else {
    if (isValid) {
      $(next).tab("show");
    } else {
      return;
    }
  }
}
$("a.locked").on("click", function (e) {
  e.preventDefault();
  e.stopPropagation();
});

// ====== Helpers ======
const ui = {
  form: {
    kons: $("#fm-idk_keu"),
  },
  loading: $("#loading"),
  tabs: {
    konsumen: $("#idk_data_konsumen-tab"),
  },
  btn: {
    printSPPTB: $("#btn-print_spptb"),
    addKons: $("#add-form-btn-idk_keu"),
    prevKons: $("#prev-form-btn-idk_keu"),
    delKons: $("#delete-btn-idk_keu"),
  },
  fields: {
    id_kavling: $(".id_kavling"),
    id_mkdt: $("#idk-id_mkdt"),
    hargaAkhirSelect: $("#idk-harga_akhir"),
    rincian: $("#idk-rincian"),
    richText: () => $("#idk-rincian").prev(".richText-editor"),
    // file previews
    ktpHere: $("#idk-file_ktp-here"),
    npwpHere: $("#idk-file_npwp-here"),
    ddHere: $("#idk-file_data_diri-here"),
  },
};

function withLoading(fn) {
  ui.loading.removeClass("hidden");
  return Promise.resolve(fn()).finally(() => ui.loading.addClass("hidden"));
}

function disableForm(disabled) {
  ui.form.kons.find("input:text, select, textarea").prop("disabled", disabled);
}

function setVal(sel, val) {
  $(sel)
    .val(val ?? "")
    .triggerHandler("input");
}
function setDate(dateStr, sel) {
  if (
    dateStr &&
    dateStr !== "0000-00-00" &&
    document.querySelector(sel)?._flatpickr
  ) {
    document.querySelector(sel)._flatpickr.setDate(dateStr);
  }
}
function setRichText(html) {
  ui.fields.richText().trigger("setContent", html ?? "");
  ui.fields.rincian.html(html ?? "");
}

// function updateButtons() {
//   // ganti logika lamamu jika perlu
//   ui.btn.add.prop("disabled", false);
//   ui.btn.prev.prop("disabled", false);
// }

function formatDateSafe(d) {
  return d ? format_date(d) : "-";
}

// ====== Data layer ======
function getTransaksiDetail({ id_mkdt, id_kavling, id_hargajual }) {
  return $.ajax({
    url: base_url + "api/transaksi/ambilsatu",
    type: "POST",
    dataType: "json",
    data: { [csrfName]: csrfHash, id_mkdt, id_kavling, id_hargajual },
  });
}

// ====== Binders ======
function bindKavlingContext(sh) {
  // navigasi/tab & tombol
  ui.tabs.konsumen.tab("show");
  // updateButtons(ui.btn.addKons, ui.btn.prevKons);
  // reset form
  ui.form.kons[0].reset();
  ui.form.kons.find(".num").prop("disabled", false);

  $("#mk-total_bb, #mk-total_um").val(0);
  $("#list_cicilan_here").empty();
  $("#mk-total_cicilan_um, #total_cicilan_bb").val(0).triggerHandler("input");
  $("#id_list_keu, #id_list_keu_bb").val("");
  $("#mk-diskon_harga_jual, #mk-diskon_uang_muka").val(0);
  $("#idk_data_baru").val(1);
  $("#idk-rincian").prev(".richText-editor").trigger("setContent", "");

  // set state dasar
  state.id_kavling = sh.data.id_kavling || sh.id.substr(3);
  state.id_mkdt = sh.data.id_mkdt || null;

  // isi hidden fields
  ui.fields.id_kavling.val(state.id_kavling);
  ui.fields.id_mkdt.val(state.id_mkdt);

  // tombol print
  if (state.id_mkdt == null) {
    ui.btn.printSPPTB
      .attr(
        "onclick",
        `return swal('error', 'Data konsumen harus disimpan terlebih dahulu');`,
      )
      .attr("target", "")
      .prop("href", "#");
  } else {
    ui.btn.printSPPTB
      .attr("onclick", "")
      .prop(
        "href",
        `${base_url}print/spptb?id_mkdt=${state.id_mkdt}&id_kavling=${state.id_kavling}&id_proyek=${dt_proyek.id_proyek}`,
      )
      .attr("target", "_blank");
  }
}

function fillPriceSection(h, dk) {
  if (!h?.hargajual) return;
  // masal: map kunci → #mk-*
  const mkMap = [
    "hargajual",
    "hargajual_net",
    "kpr",
    "uang_muka",
    "biaya_adm",
    "bphtb",
    "ppn",
    "biaya_proses",
    "harga_penambahan",
    "harga_penambahan_tanah",
  ];
  // console.log(h, dk);
  setVal("#mk-diskon_uang_muka", h.diskon_uang_muka);
  mkMap.forEach((k) => setVal(`#mk-${k}`, h[k]));

  setDatePicker(h.tgl_harga, "#mk-tgl_harga");
  setVal("#idk-tgl_harga", formatDateSafe(h.tgl_harga));
  setVal("#idk-harga_kpr", h.kpr);

  setVal("#idk-mkdt_hargajual", h.hargajual);
  $("#idk-mkdt_hargajual_by").text(dk?.username_harga_akhir ?? "-");
  $("#idk-mkdt_hargajual_tgl").text(formatDateSafe(dk?.harga_akhir_tgl));
}

function fillDiskresi(dk) {
  if (dk?.username_diskresi) {
    $("#idk-diskresi_st").removeClass("hidden");
    setVal("#idk-diskresi_harga", dk.diskresi_harga);
    setVal("#idk-diskresi_memo", dk.diskresi_memo);
    $("#idk-diskresi_oleh").text(dk.username_diskresi);
    $("#idk-diskresi_tgl").text(formatDateSafe(dk.diskresi_at));
  } else {
    $("#idk-diskresi_st").addClass("hidden");
    setVal("#idk-diskresi_harga", "-");
    setVal("#idk-diskresi_memo", "-");
    $("#idk-diskresi_oleh").text("-");
    $("#idk-diskresi_tgl").text("-");
  }
}

function fillFiles(v) {
  setImgOrPlaceholder(ui.fields.ktpHere, v?.ktp_lok, not_found);
  setImgOrPlaceholder(ui.fields.npwpHere, v?.npwp_lok, not_found, "90%");
  ui.fields.ddHere
    .html(v?.data_diri_lok ? "Klik untuk melihat file" : "Tidak ada data")
    .prop("href", base_url + (v?.data_diri_lok || not_found));
}

$("#idk-is_allin").change(function () {
  is_allin(this);
});

function is_allin(e) {
  if (e.value == "0") {
    harga_Total = $("#mk-tgt").val();
    $("#mk-harga_allin").hide();
  } else {
    harga_Total = $("#mk-harga_allin").val();
    $("#mk-harga_allin").show();
  }
  sum_mktotal();
}

function fillMkdt(v) {
  if (!v) return;

  if (v.status_mkdt === "Batal") {
    disableForm(true);
    $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").removeClass("hidden");
    ui.form.kons.find("#idk-id_konsumen, #idk-id_keuangan0").val("");
    ui.btn.delKons.removeClass("hidden");
  }

  // console.log(v)

  if (v.id_konsumen) $("#idk_data_baru").val(0);

  // basic fields
  setVal("#idk-is_allin", v.is_allin);
  $("#idk-is_allin").change();

  setVal("#mk-harga_allin", v.harga_allin);

  setVal("#idk-status_mkdt", v.status_mkdt);
  setVal("#idk-keterangan_batal", v.keterangan_batal);
  setDate(v.booking_tgl, "#idk-booking_tgl");
  setVal("#idk-booking_fee", v.booking_fee);

  ui.form.kons.find("#idk-id_konsumen").val(v.id_konsumen ?? "");

  setVal("#st-mkdt-no_spptb", v.no_spptb);
  setVal("#idk-nama_konsumen", v.nama_konsumen);
  setVal("#idk-nik_konsumen", v.nik_konsumen);
  setVal("#idk-alamat_konsumen", v.alamat_konsumen);
  setVal("#idk-npwp_konsumen", v.npwp_konsumen);
  setVal("#idk-hp_konsumen", v.hp_konsumen);
  setVal("#idk-email_konsumen", v.email_konsumen);
  setVal("#idk-status_konsumen", v.status_konsumen);

  setVal("#idk-nama_instansi", v.nama_instansi);
  setVal("#idk-alamat_instansi", v.alamat_instansi);
  setVal("#idk-tel_instansi", v.tel_instansi);
  setVal("#idk-email_instansi", v.email_instansi);
  setVal("#idk-alamat_surat", v.alamat_surat);
  setVal("#idk-pekerjaan", v.pekerjaan);
  setVal("#idk-lama_bekerja", v.lama_bekerja);
  setVal("#idk-bidang_pekerjaan", v.bidang_pekerjaan);

  setVal("#idk-status_pernikahan", v.status_pernikahan);
  setVal("#idk-nama_pasangan", v.nama_pasangan);
  setVal("#idk-nik_pasangan", v.nik_pasangan);
  setVal("#idk-hp_pasangan", v.hp_pasangan);
  setVal("#idk-status_pekerjaan_pasangan", v.status_pekerjaan_pasangan);
  setVal("#idk-instansi_pasangan", v.instansi_pasangan);

  setVal("#idk-sales", v.sales);

  setVal("#idk-is_kpr", v.is_kpr);
  setVal("#idk-is_subsidi", v.is_subsidi);
  setVal("#idk-jenis_subsidi", v.jenis_subsidi);

  setRichText(v.rincian);

  // if (v.keuangan_saved_by) {
  setVal("#mk-hargajual", v.harga_jual);
  setVal("#mk-hargajual_net", v.harga_jual_net);
  setVal("#mk-kpr", v.harga_kpr);
  setVal("#mk-uang_muka", v.harga_uang_muka);
  setVal("#mk-biaya_adm", v.harga_administrasi);
  setVal("#mk-bphtb", v.harga_bphtb);
  setVal("#mk-ppn", v.harga_ppn);
  setVal("#mk-biaya_proses", v.harga_biaya_proses);
  setVal("#mk-harga_sbum", v.harga_sbum);
  setVal("#mk-harga_penambahan", v.harga_penambahan);
  setVal("#mk-harga_penambahan_tanah", v.harga_penambahan_tanah);
  setVal("#mk-diskon_uang_muka", v.harga_diskon_uang_muka);
  // }

  setVal("#idk-promo", v.promo);

  // KPR turun
  // setVal("#mk-harga_kpr_acc", v.harga_kpr_acc);
  const turun_kpr = v.harga_kpr_acc ? v.harga_kpr - v.harga_kpr_acc : 0;
  // setVal("#mk-harga_penambahan_um", turun_kpr);

  // SPPTB file
  const spptbLink = v.file_spptb
    ? `<a href="${
        file_url('mkdt_file_spptb', v.id_mkdt)
      }" target=_blank class="btn btn-outline-primary">Klik untuk melihat File SPPTB Yang Sudah ditandatangan</a>`
    : `Tidak ada data`;
  $("#spptb_ttd_file").html(spptbLink);
}

function fillSpptbList(list) {
  const html =
    list && list.length
      ? list
          .map(
            (val, i) => `
      <tr>
        <td>${i + 1}</td>
        <td><a href="${val.access_url || file_url('file_spptb', val.id)}" target=_blank>Klik untuk melihat file</a></td>
        <td>${val.username}<br>${format_datetime(val.created_at)}</td>
      </tr>`,
          )
          .join("")
      : '<tr><td colspan="3">Tidak ada data</td></tr>';
  $("#spptb_ttd_file-here").html(html);
}

function fillTagihan(tg) {
  state.data_um = {};
  state.data_bb = {};
  if (tg.length == 0) return;

  let a = it; // mengikuti variabel lamamu
  tg.forEach((v) => {
    const id = "lk" + a;
    // if (v.status === "UM") {
    state.data_um[id] = {
      id_list_keu: id,
      id_keuangan: v.id_keuangan,
      berita_acara: v.berita_acara,
      nominal: num_format(v.nominal),
      jatuh_tempo_tgl: v.jatuh_tempo_tgl,
    };
    // } else if (v.status === "BB") {
    //   state.data_bb[id] = {
    //     id_list_keu_bb: id,
    //     id_keuangan_bb: v.id_keuangan,
    //     berita_acara_bb: v.berita_acara,
    //     nominal_bb: num_format(v.nominal),
    //     jatuh_tempo_tgl_bb: v.jatuh_tempo_tgl,
    //   };
    // }
    a++;
  });

  // data_um = state.data_um
  // data_bb = state.data_bb
  // render list tagihan sekali saja
  tambah_ketagihan();
  it = a;
}

async function isi_data_konsumen() {
  const requestId = ++latestIsiDataKonsumenRequestId;
  mkdtUpload();
  // VALIDASI PILIHAN
  if (!editdtt?.[0]) return swal("error", "Tidak ada kavling yang dipilih");
  const sh = editdtt[0];
  if (sh.data.tipe !== "kavling")
    return swal("error", "Tidak ada kavling terpilih");
  if (sh.data2.harga_akhir === "-")
    return swal("error", "Kavling belum dipasarkan (tidak ada harga jual)");

  disableForm(false);
  ui.btn.delKons.addClass("hidden");
  $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").addClass("hidden");

  ui.form.kons.find("#idk-id_konsumen").val("");
  // Siapkan konteks UI & state
  bindKavlingContext(sh);
  state.id_hargajual = sh.data2.id_hargajual;
  setVal("#idk-harga_akhir", state.id_hargajual);

  $("#idk-is_allin").change();

  try {
    await withLoading(async () => {
      const res = await getTransaksiDetail({
        id_mkdt: sh.data.id_mkdt,
        id_kavling: state.id_kavling,
        id_hargajual: state.id_hargajual,
      });
      if (requestId !== latestIsiDataKonsumenRequestId) return;

      // CSRF update
      csrfHash = res.token;

      const v = res.data; // mkdt
      const h = res.hj; // pricelist
      const tg = res.tagihan;
      const dk = res.diskresi;

      state.mkdt = {
        harga_jual: res.hj,
        diskresi: res.diskresi,
      };

      // Diskresi & HJ
      fillDiskresi(dk);
      fillPriceSection(h, dk);

      // File preview
      fillFiles(v);

      // MKDT fields
      fillMkdt(v);

      // SPPTB list
      fillSpptbList(res.list_spptb || []);

      // Tagihan + render
      fillTagihan(tg);

      // Hitung total & label alamat sekali saja
      sum_mktotal();

      let label_alamat = setLabelAlamat(
        dt_proyek.nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $(".label_alamat").html(label_alamat);

      // Buka modal
      $("#modal-isi_data_konsumen").modal({
        backdrop: "static",
        keyboard: false,
      });
      initModalListener("#modal-isi_data_konsumen");
      state.status.tab.isClosed = false;
    });
  } catch (e) {
    console.log(e);
    // Error path konsisten
    return swal("error", e?.statusText || e?.message || "Terjadi kesalahan");
  }
}
function appendCollectionToFormData(fd, collection) {
  if (!collection || typeof collection !== "object") return;
  let i = 0;

  // Izinkan collection berupa Array atau Object keyed
  const items = Array.isArray(collection)
    ? collection
    : Object.values(collection);

  for (const item of items) {
    if (!item || typeof item !== "object") {
      i++;
      continue;
    }
    for (const [key, val] of Object.entries(item)) {
      // Nullish -> string kosong supaya backend nggak terima "undefined"
      fd.append(`${key}[${i}]`, val ?? "");
    }
    i++;
  }
}
function simpan_dt_konsumen_keuangan(e) {
  const btnSave = "#add-form-btn-idk_keu";
  // updateButtons(btnSave, "#prev-form-btn-idk_keu");

  if (parseFloat(removeComma($("#mk-total_cicilan_um").val() || 0)) > 0) {
    if ($("#mk-total_tot").val() != $("#mk-total_cicilan_um").val()) {
      return swal(
        "error",
        "Gagal Menyimpan Data",
        "Total tagihan dan total yang harus dibayar tidak sesuai",
      );
    }
  }

  let dt = {};
  dt[csrfName] = csrfHash;
  ui.form.kons.find(":input").each(function () {
    dt[this.name] = this.value;
  });

  let i = 0;
  //cicilan um

  let form = ui.form.kons[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);
  let is_ganti_nama = false;

  if (is_ganti_nama) {
    fd.append("id_mkdt_old", id_mkdt_old);
    fd.append("id_konsumen_old", id_konsumen_old);
    fd.append("is_ganti_nama", is_ganti_nama);
  }

  appendCollectionToFormData(fd, state.data_um);

  $.ajax({
    url: base_url + "api/transaksi/simpan",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(btnSave, true);
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
          removeModalListener("#modal-isi_data_konsumen");
          $(".modal").modal("hide");
          simpanBtn(btnSave, false);

          load_kavling();
          hapus_seleksi();
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          simpanBtn(btnSave, false);
        });
      }
    },
    error: function (e) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan",
        showConfirmButton: true,
        // timer: 1500
      }).then(function () {
        simpanBtn(btnSave, false);
      });
    },
  });
}

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
          parseFloat(removeComma($("#fm-mkdt #acc_harga_kpr").val())),
      )
      .change();
  } else {
    $("#fm-mkdt #harga_turun_kpr").val(0);
  }
});
//delete tanggal jika toogle di aktifkan
$("#wawancara").change(function () {
  if (!$("#wawancara").prop("checked")) {
    setDatePicker(null, "#wawancara_tgl");
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
    $st,
  );
  $("#id_konsumen").val("");
  ui.form.kons.find("#idk-id_konsumen").val("");
  $("#id_keuangan0").val("");
}

function delete_kons() {
  $(
    "#fm-mkdt #nama_konsumen, #fm-mkdt #alamat_konsumen, #fm-mkdt #nik_konsumen, #fm-mkdt #hp_konsumen, #fm-mkdt #status_konsumen",
  ).val("");
  $("#id_konsumen, #id_mkdt").val("");
  $("#mkdt_data_baru").val(1);
}

function open_mkdt(sh, role, id_kavling) {
  if (sh.data.tipe != "kavling")
    return swal("error", "Tidak ada kavling terpilih", null, true);

  if (!sh.data.id_mkdt)
    return swal("error", "Belum ada data konsumen", null, true);

  if (sh.data2.harga_akhir == "-") {
    return swal(
      "error",
      "Kavling belum dipasarkan",
      "Kavling belum memiliki harga jual",
    );
  }
  $("#lb-st-no_spptb").html("-");
  $("#lb-st-nama_konsumen").html("-");

  // $("#label-file_ktp").html("Upload file KTP");
  // $("#label-file_npwp").html("Upload file KTP");

  // $("#refresh_fmmkdt_div").addClass("hidden");
  // $("#delete_kons_div").addClass("hidden");
  // $("#fm-mkdt .num").prop("disabled", false);

  // $("#cicilan_belong_here").html("");
  it = 0;
  // $("#data_konsumen").tab('show');

  $("#mkdt_data_baru").val(0);

  refresh_fmmkdt(false);

  $("#fm-mkdt .num").val(0);

  $(".id_kavling").val(id_kavling);
  $("#id_mkdt").val(sh.data.id_mkdt);

  $.ajax({
    url: base_url + "api/transaksi/status/ambilsatu",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: sh.data.id_mkdt,
      id_hargajual: sh.data2.id_hargajual,
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
        pb = x.perintah_bangun,
        tkpr = x.tagihan;

      //load hargajual
      // if (h.hargajual) {
      //   $.each(h, function (k, v) {
      //     $("#mkdt-" + k)
      //       .val(v)
      //       .change()
      //       .keyup();
      //   });
      //   $("#mkdt-tgl_harga").val(format_date(h.tgl_harga));
      //   $("#fm-mkdt #harga_kpr").val(h.kpr).change();
      // }

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
            "hidden",
          );
          $("#delete_kons_div").addClass("hidden");
          $("#delete-btn-idk_keu").addClass("hidden");
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

        $("#lb-st-no_spptb").html(r.no_spptb);
        $("#lb-st-nama_konsumen").html(r.nama_konsumen);

        $("#fm-mkdt #mkdt_keterangan").val(r.keterangan);
        $("#fm-mkdt #acc_harga_kpr").val(r.harga_kpr_acc).change();
        $("#fm-mkdt #harga_turun_kpr").val(r.harga_penambahan_um).change();

        var newOption = new Option(r.nama_bank, r.id_bank, true, true);
        $("#id_bank").append(newOption).trigger("change");

        if (r.wawancara == 1) $("#wawancara").prop("checked", true);
        if (r.sp3k == 1) $("#sp3k").prop("checked", true);
        if (r.akad == 1) $("#akad").prop("checked", true);

        //set datepicker jika tanggal valid
        setDatePicker(pb.perintah_bangun_tgl, "#fm-mkdt #perintah_bangun_tgl");

        setDatePicker(r.booking_tgl, "#fm-mkdt #booking_tgl");
        setDatePicker(r.wawancara_tgl, "#fm-mkdt #wawancara_tgl");
        setDatePicker(r.sp3k_tgl, "#fm-mkdt #sp3k_tgl");
        setDatePicker(r.sp3k_tgl_exp, "#fm-mkdt #sp3k_tgl_exp");
        setDatePicker(r.rencana_akad_tgl, "#fm-mkdt #rencana_akad_tgl");
        setDatePicker(r.akad_tgl, "#fm-mkdt #akad_tgl");

        // if (r.refund_tgl != "0000-00-00")
        //     document.querySelector("#refund_tgl")._flatpickr.setDate(r.refund_tgl);

        $("#fm-mkdt .num").keyup().change(); //fomrat form number
        $("#status_mkdt").change(); //show/hide keterangan batal

        $("#mkdt_keterangan").val(r.keterangan);

        // $("#file_ktp-here").html("Tidak ada data");
        // src = not_found;

        // src = not_found;
        //load bast
        // if (r.bast_file != null) {
        //   src = r.bast_file;
        // }
        // $("#list-upload_bast_file").prop("href", base_url + src);

        // src = not_found;
        //load sp3k
        // if (r.sp3k_file != null) {
        //   src = r.sp3k_file;
        // }
        // $("#list-upload_sp3k_file").prop("href", base_url + src);
        setBtnHref("#list-upload_sp3k_file", r.sp3k_file);
      }

      if (pb.perintah_bangun == 1) {
        $("#perintah_bangun").prop("checked", true);
        $("#fm-mkdt #perintah_bangun_oleh").val(pb.username);
        setBtnHref(
          "#list-upload_perintah_bangun_file",
          pb.perintah_bangun_file,
        );
        setDatePicker(pb.perintah_bangun_tgl, "#perintah_bangun_tgl");
      }

      load_tagihankpr(tkpr);

      let label_alamat = setLabelAlamat(
        dt_proyek.nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $(".label_alamat").html(label_alamat);

      $("#modal_divisi" + role).modal({
        backdrop: "static",
        keyboard: false,
      });
      initModalListener("#modal_divisi" + role);
    },
    error: function (xhr, st, err) {
      $("#loading").addClass("hidden");
      return swal("error", err);
    },
  });
}
async function hapus_turunkpr(id_keuangan) {
  const { isConfirmed } = await Swal.fire({
    title: "Yakin ingin menghapus?",
    text: "Data keuangan ini akan dihapus permanen!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
  });

  if (isConfirmed) {
    Swal.fire({
      title: "Menghapus...",
      text: "Tunggu sebentar",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    try {
      let response = await fetch(`${base_url}tagihan/hapusturunkpr`, {
        method: "POST", // atau 'DELETE' kalau API pakai method delete
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id_keuangan: id_keuangan }),
      });

      let result = await response.json();

      if (response.ok && result.success) {
        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: result.message,
        }).then(() => {
          // refresh table atau halaman
          load_tagihankpr(null);
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: result.message || "Terjadi kesalahan saat menghapus",
        });
      }
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message,
      });
    }
  }
}
function load_tagihankpr(val) {
  $("#mkdt-tagihan_kpr").html("");

  // console.log(val);

  if (val == null) {
    return;
  }

  let div = `
  <div class="divider divider-left">
      <div class="divider-text font-weight-bold">Tagihan Turun KPR</div>
  </div>
  `;
  if (val.berita_acara == "Turun KPR") {
    div += `
      <div class="form-group">
          <label for="bank">Tanggal Jatuh Tempo Turun KPR</label>
          <input type="text" readonly class="form-control" value='${format_date(
            val.jatuh_tempo_tgl,
          )}' />
              <a href="#" onclick="hapus_turunkpr(${
                val.id_keuangan
              })"class="text-danger"><i class="fa fa-trash"></i>Klik untuk hapus tagihan</a>
      </div>
      `;
  }

  $("#mkdt-tagihan_kpr").html(div);
}

$("#add-form-btn-mkdt").click(function (e) {
  e.preventDefault();
});

function save_mkdt(e) {
  const btn = "#add-form-btn-mkdt";
  if (!palid("fm-mkdt #status_mkdt", "", "Status harus diisi")) return;
  if (!palid("fm-mkdt #id_bank", "", "Bank harus diisi")) return;

  if (
    removeComma($("#harga_turun_kpr").val()) > 0 &&
    $("#mkdt-tagihan_kpr").html() == ""
  ) {
    swal(
      "warning",
      "Tagihan untuk turun KPR harus dibuat terlebih dahulu",
      "Karena ada nilai di turun KPR, jadi harus buat tagihannya dulu ya!",
      false,
      hlButton("#btn-add-tagihan-turunkpr"),
    );
    return;
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
    url: base_url + "api/transaksi/status/simpan",
    type: "post",
    // data: $("#fm-mkdt").serialize() + "&" + csrfName + "=" + csrfHash,
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(btn, true);
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        removeModalListener("#modal_divisi4");
        load_kavling();
        hapus_seleksi();

        swal("success", r.messages);
        $(".modal").modal("hide");
      } else {
        swal("error", r.messages);
      }
      simpanBtn(btn, false);
    },
    error: function (xhr, st, err) {
      simpanBtn(btn, false);
      return swal("error", err);
    },
  });
}

function set_harga() {
  $.ajax({
    url: base_url + "Hargajual/set_harga",
    type: "post",
    data: $("#fm-set_harga").serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
    dataType: "json",
    beforeSend: function () {
      $("#set-harga-form-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
      $("#set-harga-form-btn").addClass("disabled");
    },
    success: function (response) {
      csrfHash = response.token;
      swal(
        response.success ? "success" : "error",
        response.messages,
        null,
        true,
        () => {
          if (response.success) {
            $("#modals-set_harga").modal("hide");
          }
          $("#set-harga-form-btn").html("Simpan");
          $("#set-harga-form-btn").removeClass("disabled");
        },
      );
      load_kavling();
      hapus_seleksi();
    },
  });
}

function formatDesign(item) {
  var selectionText = item.text.split(";");
  var $returnString = $(
    "<span> <b>" +
      selectionText[0] +
      "</b></br >" +
      selectionText[1] +
      "</br>" +
      selectionText[2] +
      "</span>",
  );
  return $returnString;
}
$("#sh-id").select2({
  placeholder: "Pilih Pricelist",
  allowClear: true,
  templateResult: formatDesign,
  ajax: {
    url: base_url + "hargajual/get",
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
          text: `Rp. ${num_format(v.hargajual)} Per ${format_date(
            v.tgl_harga,
          )} (ROW ${v.row}); <b>Tipe:</b> ${v.id_tipe}; <b>Ket:</b> ${
            v.keterangan
          };`,
          row: v.row,
          tipe: v.id_tipe,
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
          lok: file_url('file_hargajual', v.id_filehj),
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
    changeVal("#sh-" + k, v);
  });

  let src = i.lok != "null/null" ? i.lok : not_found;
  setFileHref("#sh-pricelist_file", false, src);
});

$("#sh-id").change(function () {
  if (!this.value) $(".sh-fm").val("");
});

function open_set_turun_pembangunan() {
  $("#list-tp-upload_perintah_bangun_file").prop("href", base_url + not_found);
  $("#label-perintah_bangun_file").html("File Turun Perintah Bangun");
  if (editdtt.length == 0) {
    return swal("error", "Tidak ada kavling terpilih");
  }
  $("#fm-turun_pembangunan")[0].reset();

  let data = [];

  for (let a = 0; a < editdtt.length; a++) {
    data.push(editdtt[a].id.substr(3));
  }
  $.ajax({
    url: base_url + "siteplan/get_turun_pembangunan",
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

      $(".select2").not("#pilih-divisi").val(null).trigger("change");

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
          base_url + r[0].perintah_bangun_file,
        );

        setDatePicker(r[0].perintah_bangun_tgl, "#tp-perintah_bangun_tgl");
      }

      $("#loading").addClass("hidden");
      $("#modals-turun_pembangunan").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      return swal("error", err);
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
    url: base_url + "siteplan/set_turun_pembangunan",
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

function setFileHref(id, remove = true, url = null) {
  if (remove) {
    $(id).removeAttr("target");
    $(id).prop("href", "javascript:void(0)");
  } else {
    $(id).prop("href", `${base_url}${url}`);
    $(id).prop("target", "_blank");
  }
}

function open_set_harga() {
  if (editdtt.length == 0)
    return swal("error", "Tidak ada kavling terpilih", null, true);

  setFileHref("#sh-pricelist_file");

  $("#fm-set_harga")[0].reset();

  let data = [];

  for (let a = 0; a < editdtt.length; a++) {
    data.push(editdtt[a].id.substr(3));
  }

  $.ajax({
    url: base_url + "siteplan/get_harga_kavling",
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
      $("#loading").addClass("hidden");
      let r = res.data,
        id_kavling = "",
        src,
        no = "";

      $("#sh-id").val(null).trigger("change");

      if (r.length > 0) {
        for (let a = 0; a < r.length; a++) {
          id_kavling += r[a].id_kavling + ";";
          no += `${r[a].nama_jalan} No. ${r[a].no_kavling} - ${r[a].tipe_rumah}\n`;

          if (r[a].harga_akhir) {
            $("#sh-id")
              .append(
                $("<option selected></option>")
                  .attr("value", r[a].harga_akhir)
                  .text(
                    `Rp. ${num_format(r[a].hargajual)} (${
                      r[a].tipe_rumah
                    }) ROW ${r[a].row}: per ${format_date(r[a].tgl_harga)}`,
                  ),
              )
              .trigger("change");

            changeVal("#sh-row", r[a].row);
            changeVal("#sh-tipe", r[a].tipe_rumah);
            changeVal("#sh-lb", r[a].hj_lb);
            changeVal("#sh-lt", r[a].hj_lt);
            changeVal("#sh-hargajual", r[a].hargajual);
            changeVal("#sh-hargajual_net", r[a].hargajual_net);
            changeVal("#sh-kpr", r[a].kpr);
            changeVal("#sh-uang_muka", r[a].uang_muka);
            changeVal("#sh-ppn", r[a].ppn);
            changeVal("#sh-bphtb", r[a].bphtb);
            changeVal("#sh-biaya_adm", r[a].biaya_adm);
            changeVal("#sh-biaya_proses", r[a].biaya_proses);

            src = not_found;
            if (r[a].file_name) {
              src = file_url('file_hargajual', r[a].id_filehj);
            }
            setFileHref("#sh-pricelist_file", false, src);
          } else {
            setFileHref("#sh-pricelist_file");
          }
        }

        $(".id_kavling").val(id_kavling);
        $("#sh-kavling").val(no);
        // $("#fm-set_harga #id_tipe").val(id_tipe);
        // $("#fm-set_harga #harga").val(harga).keyup();
      }

      $("#modals-set_harga").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      $("#loading").addClass("hidden");
      return swal("error", err);
    },
  });
}

function isi_si() {
  let sh = editdtt;

  if (sh.length == 0)
    return swal("error", "Tidak ada kavling terpilih", null, true);

  sh = sh[0];

  let id_kavling = sh.id.substr(3);

  $(".id_kavling").val(id_kavling);

  co = [];

  $("#fm-si")[0].reset();

  $.ajax({
    url: base_url + "mkdt/getsi",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: id_kavling,
    },
    dataType: "json",
    success: function (res) {
      csrfHash = res.token;
      let d = res.data,
        id_si,
        div = "";

      $.each(d, function (i, v) {
        co.push(v.id_list_si_ori);

        id_si = !v.id ? "n" + v.id_list_si_ori : v.id;
        div += `
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>${v.nama}</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Tanggal SI</label>
                                                <input type="text" class="form-control fp-si flatpickr-human-friendly tsi${
                                                  v.id_list_si_ori
                                                }"
                                                    id="id-si[${id_si}][tanggal_si]" value="${
                                                      v.tanggal_si
                                                        ? v.tanggal_si
                                                        : ""
                                                    }" name="id-si[${id_si}][tanggal_si]">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Soft File</label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input " accept="application/pdf"
                                                        name="id-si-file-${id_si}" id="id-si-file-${id_si}" />
                                                    <label class="custom-file-label" id="label-si-file-${id_si}"
                                                        for="id-si-file-${id_si}">Upload Soft File</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">

                                         <a href="${
                                           base_url + v.file
                                         }" target=_blank id="list-si-file-${id_si}"
                                                class="btn btn-outline-primary col-12">Klik untuk lihat file</a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <textarea class="form-control" id="id-si[${id_si}][keterangan]"
                                                name="id-si[${id_si}][keterangan]" rows="4" placyeholder="Keterangan">${
                                                  v.keterangan
                                                    ? v.keterangan
                                                    : ""
                                                }</textarea>
                                            <small id="last_update-si${id_si}" class=""></small>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                 `;
      });

      $("#dv-si-here").html(div);

      flatpickr(".fp-si", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
      });
      $(".num").change();

      $("#modals-si").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      return swal("error", err);
    },
  });
}

function save_si() {
  // $.each(co, function(i, v) {
  //     if ($(".tsi" + v)[0].value == "") {
  //         $(".tsi" + v).addClass('is-invalid')
  //         return swal('error', "Nominal pembayaran harus diisi")
  //     } else
  //         $(".tsi" + v).removeClass('is-invalid');

  // });

  var form = $("#fm-si")[0];
  var fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  let sbtn = "#btn-si-simpan";

  $.ajax({
    url: base_url + "mkdt/saveSI",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(sbtn, true);
    },
    success: function (r) {
      csrfHash = r.token;
      if (r.success === true) {
        swal("success", r.messages);
        $(".modal").modal("hide");
        simpanBtn(sbtn, false);
      } else {
        swal("error", "Terjadi kesalahan", r.messages);
        simpanBtn(sbtn, false);
      }
      load_kavling();
      hapus_seleksi();
    },
    error: function (r) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan",
        showConfirmButton: false,
        // timer: 1500
      });
      simpanBtn(sbtn, false);
    },
  });
}

$("#fm-mkdt #sp3k_tgl").change(function () {
  if (!this.value) return;
  document
    .querySelector("#fm-mkdt #sp3k_tgl_exp")
    ._flatpickr.setDate(new Date(this.value).fp_incr(88));
});

//untuk tambah konsumen baru ketika batal
$("#refresh-btn-idk_keu").click(function () {
  $("#fm-idk_keu .num").prop("disabled", false);
  $("#fm-idk_keu")[0].reset();

  // refresh_fmmkdt(false);
  $("#fm-idk_keu input:text, #fm-idk_keu select, #fm-idk_keu textarea").prop(
    "disabled",
    false,
  );
  ui.form.kons.find("#idk-id_konsumen").val("");
  $("#idk_data_baru").val(1);
  $("#idk-show_keterangan_batal").addClass("hidden");

  if (state.mkdt.harga_jual && state.mkdt.diskresi) {
    fillPriceSection(state.mkdt.harga_jual, state.mkdt.diskresi);
  }
  state.data_um = {};
  tambah_ketagihan();
  sum_mktotal();
});

function mkdtUpload() {
  const inputs = [
    { id: "file_ktp" },
    { id: "file_npwp" },
    { id: "file_data_diri" },
  ];

  inputs.forEach((item) => {
    load_dropzone(item.id);
  });
}

function postTurunKPR(val) {
  // console.log(val)
  const { berita_acara, nominal, tgl, id_mkdt } = val;
  $.ajax({
    url: base_url + "tagihan/turunkpr",
    type: "POST",
    dataType: "json",
    data: { [csrfName]: csrfHash, berita_acara, nominal, tgl, id_mkdt },
    beforeSend: () => {},
    success: () => {},
    error: () => {},
  });
}
async function loadFormTagihan(nominal_kpr) {
  const { isConfirmed, isDenied, value } = await Swal.fire({
    title: "Tambah Ke Tagihan",
    html: `
      <div class="swal2-content mt-1" style="text-align:left">
        <div class="form-group floating-label">
          <input type="text" class="form-control" value="Turun KPR" readonly id="fkpr-berita_acara" placeholder=" " required>
          <label for="fkpr-berita_acara">Untuk Tagihan</label>
        </div>
        <div class="form-group floating-label">
          <input type="text" class="form-control" value="${nominal_kpr}" readonly id="fkpr-nominal" placeholder=" " required>
          <label for="fkpr-nominal">Nominal</label>
        </div>
        <div class="form-group floating-label">
          <input type="text" class="form-control fp-jatuhtempo" id="fkpr-jatuh_tempo_tgl" placeholder=" " required>
          <label for="fkpr-jatuh_tempo_tgl">Jatuh Tempo</label>
        </div>
      </div>
    `,
    focusConfirm: false,
    showDenyButton: true,
    confirmButtonText: "Ya",
    denyButtonText: "Tidak",
    allowOutsideClick: false,
    showLoaderOnConfirm: true,

    didOpen: () => {
      // const popup = Swal.getPopup();
      const el = document.querySelector(".fp-jatuhtempo");
      if (el && el._flatpickr) el._flatpickr.destroy();
      flatpickr(el, {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
        allowInput: true,
        // appendTo: popup, // penting biar z-index aman
      });
    },

    preConfirm: async () => {
      const p = Swal.getPopup();
      const berita_acara = p.querySelector("#fkpr-berita_acara").value.trim();
      const nominalStr = p.querySelector("#fkpr-nominal").value.trim();
      const tgl = p.querySelector("#fkpr-jatuh_tempo_tgl").value.trim();
      const id_mkdt = document.querySelector("#id_mkdt").value.trim();
      const id_kavling = document.querySelector(".id_kavling").value.trim();
      const id_konsumen = document.querySelector("#id_konsumen").value.trim();
      const harga_kpr = document.querySelector("#harga_kpr").value.trim();
      const acc_harga_kpr = document
        .querySelector("#acc_harga_kpr")
        .value.trim();

      const nominal = Number(nominalStr.replace(/[^\d.-]/g, "")) || 0;

      if (!berita_acara)
        return Swal.showValidationMessage("Untuk Tagihan wajib diisi");
      if (nominal <= 0)
        return Swal.showValidationMessage("Nominal tidak boleh 0");
      if (!tgl)
        return Swal.showValidationMessage(
          "Tanggal jatuh tempo tidak boleh kosong",
        );

      // ---- POST ke server ----
      try {
        // (opsional) Abort kalau kelamaan
        const ac = new AbortController();
        const timeout = setTimeout(() => ac.abort(), 20000); // 20 detik

        const res = await fetch(`${base_url}tagihan/turunkpr`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            // sertakan CSRF kalau CI4 mengaktifkan:
            // "X-CSRF-TOKEN": window.CSRF_TOKEN
          },
          body: JSON.stringify({
            berita_acara,
            nominal,
            jatuh_tempo: tgl,
            id_mkdt,
            id_kavling,
            id_konsumen,
            harga_kpr,
            acc_harga_kpr,
          }),
          signal: ac.signal,
        });
        clearTimeout(timeout);

        // Tangani error HTTP
        if (!res.ok) {
          const text = await res.text().catch(() => "");
          throw new Error(text || `Gagal menyimpan (HTTP ${res.status})`);
        }

        const data = await res.json().catch(() => ({}));
        // Jika API kamu kirim {success:false, message:"..."}
        if (data && data.success === false) {
          throw new Error(data.messages || "Gagal menyimpan");
        }
        // Return untuk diteruskan ke .then(...) sebagai `value`
        return data;
      } catch (err) {
        // Tetap di popup + tampilkan pesan error di bawah tombol
        return Swal.showValidationMessage(err.message || "Gagal menyimpan");
      }
    },
  });

  return { isConfirmed, isDenied, value };
}
let btnTunruKpr = "#btn-add-tagihan-turunkpr";
$(btnTunruKpr).click(async function (e) {
  e.preventDefault();
  let nominal_kpr = removeComma($("#harga_turun_kpr").val());

  if (nominal_kpr == 0) {
    return swal(
      "error",
      "Terjadi Kesalahan",
      "Tidak bisa menambahkan ke tagihan jika nominal Turun KPR 0!",
    );
  }
  const { isConfirmed, isDenied, value } = await loadFormTagihan(nominal_kpr);

  if (isConfirmed) {
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Tagihan ditambahkan.",
    }).then(() => {
      load_tagihankpr(value.data);
    });
  } else if (isDenied) {
    Swal.fire("Dibatalkan", "Aksi dibatalkan.", "info");
  }
});

</script>