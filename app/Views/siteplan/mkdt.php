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
</style>
<!-- ################################## Modal Isi Data Konsumen ##########################################-->
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
                                <div class="col-12">
                                    <div class="refresh_fmmkdt_div ">
                                        <button id="refresh-btn-idk_keu" type="button"
                                            class="btn btn-primary btn-block waves-effect">Tambah Konsumen
                                            Baru</button>
                                    </div>
                                    <div class="delete_kons_div">
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
        </div>
        </form>
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
<div class="modal fade " id="modals-set_harga">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="fm-set_harga" class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Set Harga</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1" style="background-color: #eee;">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="divider divider-left pb-0">
                                    <div class="divider-text font-weight-bold"><strong><i
                                                class="fas fa-files"></i>Kavling</strong></div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Proyek</label>
                                            <input type="hidden" class="form-control" id="points" readonly name="points"
                                                value="" />
                                            <input type="hidden" class="form-control id_kavling" readonly
                                                name="id_kavling" value="" />
                                            <input type="text" class="form-control" id="nama_proyek" readonly
                                                name="nama_proyek" value="<?= $data['proyek']->nama_proyek ?>"
                                                placeholder="ASI" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-post">Kavling</label>
                                            <textarea class="form-control" id="sh-kavling" name="sh-kavling" rows="6"
                                                readonly placeholder="Kavling"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="divider divider-left pb-0">
                                    <div class="divider-text font-weight-bold"><strong>
                                            Pilih Pricelist</strong></div>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <select class="select2 custom-select sh-fm" id="sh-id" name="sh-id"
                                                value=""></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <a href="javascript:void(0)" target="_blank" id="sh-pricelist_file"
                                                rel="noopener noreferrer"
                                                class="form-control btn btn-outline btn-primary"><i
                                                    class="fas fa-file"></i>Klik unuk melihat
                                                file</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="divider divider-left pb-0">
                                    <div class="divider-text font-weight-bold"><strong><i class="fas fa-files"></i>
                                            Detail Pricelist</strong></div>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">ROW</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-row" name="sh-row"
                                                value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Tipe</label>
                                            <input type="text" class="form-control sh-fm text-right" id="sh-tipe"
                                                name="sh-tipe" value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Luas
                                                Bangunan</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-lb" name="sh-lb"
                                                value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Luas
                                                Tanah</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-lt" name="sh-lt"
                                                value="" readonly />
                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Harga
                                                Jual</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-hargajual"
                                                name="sh-hargajual" value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Harga Jual
                                                Net</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-hargajual_net"
                                                name="sh-hargajual_net" value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">KPR</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-kpr" name="sh-kpr"
                                                value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Uang
                                                Muka</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-uang_muka"
                                                name="sh-uang_muka" value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Biaya
                                                Adm</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-biaya_adm"
                                                name="sh-biaya_adm" value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-bphtb"
                                                name="sh-bphtb" value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">PPN</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-ppn" name="sh-ppn"
                                                value="" readonly />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="basic-icon-default-fullname">Biaya
                                                Proses</label>
                                            <input type="text" class="form-control num sh-fm" id="sh-biaya_proses"
                                                name="sh-biaya_proses" value="" readonly />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">*catatan: gunakan titik koma ";"
                            untuk pemisah nomor rumah jika akan input rumah lebih dari 1 kavling sekaligus</label>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <a id="set-harga-form-btn" class="btn btn-primary mr-1" onclick="set_harga()"
                    href="javascript:void(0)">Simpan</a>
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
                <h5 class="modal-title" id="exampleModalLabel">Marketing Data: Perbaharui Status Kavling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1" style="background-color:#eee">
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
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold"><i class="fa fa-user"></i> Konsumen</div>
                                </div>
                                <div class="form-group">
                                    <label for="no_spptb"><strong>No SPPTB</strong></label>
                                    <div id="lb-st-no_spptb"></div>
                                </div>
                                <div class="form-group">
                                    <label for="nama_konsumen"><strong>Nama Konsumen</strong></label>
                                    <div id="lb-st-nama_konsumen"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold">Status Kavling</div>
                                </div>
                                <div class="form-group floating-label floating-label-select">

                                    <select required class="form-control" id="status_mkdt" name="status_mkdt">
                                        <option value="">-</option>
                                        <option value="Booking">Booking</option>
                                        <option value="Akad">Akad</option>
                                        <option disabled value="Batal">Batal</option>
                                    </select>
                                    <label for="status_mkdt">Status Booking</label>
                                </div>
                                <div id="show_keterangan_batal" class="hidden">
                                    <div class="form-group">
                                        <label for="keterangan_batal">Keterangan Batal</label>
                                        <textarea class="form-control" id="keterangan_batal" name="keterangan_batal"
                                            rows="3" placeholder="Keterangan"></textarea>
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
                                <div class="form-group">
                                    <label for="booking_tgl">Tanggal Booking</label>
                                    <input type="text" id="booking_tgl" name="booking_tgl"
                                        class="form-control flatpickr-human-friendly" placeholder="-" readonly />
                                </div>
                                <div class="form-group">
                                    <label for="booking_fee">Booking Fee</label>
                                    <input type="text" readonly class="form-control num" id="booking_fee"
                                        name="booking_fee">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-body pb-0 pt-0">
                                <ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2" role="tablist">
                                    <!-- <li class="nav-item">
                                        <a class="nav-link active" id="data_konsumen-tab" data-toggle="tab"
                                            href="#data_konsumen" aria-controls="data_konsumen" role="tab"
                                            aria-selected="true">Data Konsumen</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="detail_biaya-tab" data-toggle="tab" href="#detail_biaya"
                                            aria-controls="detail_biaya" role="tab" aria-selected="true">Detail</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="detail_tagihan-tab" data-toggle="tab"
                                            href="#detail_tagihan" aria-controls="detail_tagihan" role="tab"
                                            aria-selected="false">Detail Tagihan</a>
                                    </li> -->
                                    <li class="nav-item">
                                        <a class="nav-link active" id="status-tab" data-toggle="tab" href="#status"
                                            aria-controls="detail_tagihan" role="tab" aria-selected="false">Status </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- <div class="card"> -->
                        <!-- <div class="card-body"> -->
                        <div class="tab-content">
                            <div class="tab-pane" id="data_konsumen" aria-labelledby="data_konsumen-tab"
                                role="tabpanel">
                                <input type="hidden" class="form-control id_kavling" name="id_kavling"
                                    value="" />
                                <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt"
                                    value="" />
                                <input type="hidden" class="form-control" id="id_konsumen" name="id_konsumen"
                                    value="" />

                                <input type="hidden" class="form-control" id="mkdt_data_baru"
                                    name="mkdt_data_baru" value="" />
                                <div class="row">
                                    <div class="col-sm-12 col-md-4 col-lg-4">
                                        <div id="refresh_fmmkdt_div">
                                            <button id="refresh_fmmkdt_btn" type="button"
                                                class="btn btn-outline-primary btn-block waves-effect">Tambah
                                                Konsumen
                                                Baru</button>
                                        </div>
                                        <div id="delete_kons_div">
                                            <button id="delete_kons_btn" type="button"
                                                class="btn btn-outline-danger btn-block waves-effect"
                                                onclick="delete_kons(false)">Hapus Konsumen</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <!-- <div class="col-sm-12 col-md-3 col-lg-3">
                                                <div class="divider">
                                                    <div class="divider-text">Status</div>
                                                </div>

                                                <div class="divider">
                                                    <div class="divider-text">Data Konsumen</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="no_spptb">No SPPTB</label>
                                                    <input type="text" class="form-control" id="no_spptb"
                                                        name="no_spptb">
                                                </div>
                                                <div class="form-group">
                                                    <label for="nama_konsumen">Nama Konsumen</label>
                                                    <input type="text" class="form-control" id="nama_konsumen" required
                                                        name="nama_konsumen">
                                                </div>
                                                <div class="form-group">
                                                    <label for="alamat_konsumen">Alamat Konsumen</label>
                                                    <input type="text" class="form-control" id="alamat_konsumen"
                                                        name="alamat_konsumen">
                                                </div>
                                                <div class="form-group">
                                                    <label for="nik_konsumen">NIK</label>
                                                    <input type="text" class="form-control" id="nik_konsumen"
                                                        name="nik_konsumen">
                                                </div>
                                                <div class="form-group">
                                                    <label for="npwp_konsumen">NPWP</label>
                                                    <input type="text" class="form-control" id="npwp_konsumen"
                                                        name="npwp_konsumen">
                                                </div>
                                                <div class="form-group">
                                                    <label for="hp_konsumen">Kontak Konsumen</label>
                                                    <input type="text" class="form-control" id="hp_konsumen"
                                                        name="hp_konsumen">
                                                </div>
                                                <div class="form-group">
                                                    <label for="hp_konsumen">Email Konsumen</label>
                                                    <input type="text" class="form-control" id="email_konsumen"
                                                        name="email_konsumen">
                                                </div>
                                                <div class="form-group hidden">
                                                    <label for="status_kavling">Status Konsumen</label>
                                                    <select class="form-control" id="status_konsumen"
                                                        name="status_konsumen">
                                                        <option value="">-</option>
                                                        <option value="Umum">Umum</option>
                                                        <option value="TWP">TWP</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-3 col-lg-3">
                                                <div class="divider">
                                                    <div class="divider-text">Data Pasangan</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="status_kavling">Status Pernikahan</label>
                                                    <select class="form-control" id="status_pernikahan"
                                                        name="status_pernikahan">
                                                        <option value="Belum Kawin">Belum Kawin</option>
                                                        <option value="Kawin">Kawin</option>
                                                        <option value="Cerai Mati">Cerai Mati</option>
                                                        <option value="Cerai Hidup">Cerai Hidup</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="nama_pasangan">Nama Pasangan</label>
                                                    <input type="text" class="form-control" id="nama_pasangan"
                                                        name="nama_pasangan">
                                                </div>
                                                <div class="form-group">
                                                    <label for="hp_konsumen">NIK Pasangan</label>
                                                    <input type="text" class="form-control" id="nik_pasangan"
                                                        name="nik_pasangan">
                                                </div>
                                                <div class="divider">
                                                    <div class="divider-text">Data Instansi</div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="nama_instansi">Nama Instansi</label>
                                                    <input type="text" class="form-control" id="nama_instansi"
                                                        name="nama_instansi">
                                                </div>
                                                <div class="form-group">
                                                    <label for="alamat_instansi">Alamat Instansi</label>
                                                    <input type="text" class="form-control" id="alamat_instansi"
                                                        name="alamat_instansi">
                                                </div>
                                                <div class="form-group">
                                                    <label for="tel_instansi">Telepon Instansi</label>
                                                    <input type="text" class="form-control" id="tel_instansi"
                                                        name="tel_instansi">
                                                </div>

                                            </div>
                                            <div class="col-sm-12 col-md-3 col-lg-3">
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
                                                    <select required class="form-control" id="is_subsidi"
                                                        name="is_subsidi">
                                                        <option value="0">Non-Subsidi</option>
                                                        <option value="1">Subsidi</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="jenis_subsidi">Jenis Subsidi</label>
                                                    <input type="text" placeholder="FLPP/TAPERA/LAIN-LAIN"
                                                        class="form-control" id="jenis_subsidi" name="jenis_subsidi">
                                                </div>
                                                <div class="divider">
                                                    <div class="divider-text">File Upload</div>
                                                </div>
                                                <div class="form-group">
                                                    <label>KTP</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" accept="image/*"
                                                            name="file_ktp" id="file_ktp" />
                                                        <label class="custom-file-label" id="label-file_ktp"
                                                            for="label-file_ktp">Upload
                                                            File KTP</label>
                                                        <div id="list-upload_file_ktp"></div>

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>NPWP</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" accept="image/*"
                                                            name="file_npwp" id="file_npwp" />
                                                        <label class="custom-file-label" id="label-file_npwp"
                                                            for="label-file_npwp">Upload File NPWP</label>
                                                        <div id="list-upload_file_npwp"></div>

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Data Diri</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input"
                                                            accept="application/pdf" name="file_data_diri"
                                                            id="file_data_diri" />
                                                        <label class="custom-file-label" id="label-file_data_diri"
                                                            for="label-file_data_diri">Upload
                                                            Data Diri</label>
                                                        <div id="list-upload_file_data_diri"></div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-3 col-lg-3 text-center">
                                                <div class="divider">
                                                    <div class="divider-text">KTP</div>
                                                </div>
                                                <button href="" id="file_ktp-here"
                                                    class="w-100 btn btn-outline-primary">klik untuk melihat
                                                    file</button>

                                                <div class="divider">
                                                    <div class="divider-text">NPWP</div>
                                                </div>
                                                <a href="" id="file_npwp-here" target="_blank"
                                                    class=" btn btn-outline-primary w-100">klik untuk melihat file</a>
                                                <div class="divider">
                                                    <div class="divider-text">Data Diri</div>
                                                </div>
                                                <a href="" id="file_data_diri-here"
                                                    class="btn btn-outline-primary w-100" target="_blank">klik untuk
                                                    melihat file</a>
                                            </div> -->
                                </div>
                            </div>
                            <div class="tab-pane" id="detail_biaya" aria-labelledby="detail_biaya-tab"
                                role="tabpanel">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6 col-lg-6"></div>
                                    <div class="col-sm-12 col-md-6 col-lg-6"></div>
                                </div>
                            </div>

                            <div class="tab-pane  show active" id="status" aria-labelledby="status-tab"
                                role="tabpanel">
                                <div class="card-columns">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">Status Konsumen</div>
                                            </div>
                                            <div class="form-group">
                                                <label>Status</label>
                                                <input type="text" id="mkdt_keterangan" name="mkdt_keterangan"
                                                    class="form-control"
                                                    placeholder="ACC SP3K/REJECT/WAWANCARA/DLL" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">Wawancara</div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="wawancara" name="wawancara" value="1" />
                                                    <label class="custom-control-label" for="wawancara">Sudah
                                                        Wawancara</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="bank">Bank</label>
                                                <select type="text" id="id_bank" name="id_bank"
                                                    class="form-control select2">
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="bank">Keterangan</label>
                                                <input type="text" id="bank" name="bank" class="form-control"
                                                    placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="wawancara_tgl">Tanggal Wawancara</label>
                                                <input type="text" id="wawancara_tgl" name="wawancara_tgl"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">Perintah Bangun</div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="perintah_bangun" name="perintah_bangun" value="1" />
                                                    <label class="custom-control-label"
                                                        for="perintah_bangun">Perintah
                                                        Bangun</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="perintah_bangun_tgl">Tanggal Perintah Bangun</label>
                                                <input type="text" readonly="readonly" id="perintah_bangun_tgl"
                                                    name="perintah_bangun_tgl"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="perintah_bangun_oleh">Oleh</label>
                                                <input type="text" readonly="readonly" id="perintah_bangun_oleh"
                                                    name="perintah_bangun_oleh" class="form-control"
                                                    placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>Perintah Bangun</label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input"
                                                        accept="application/pdf" name="perintah_bangun_file"
                                                        id="perintah_bangun_file" />
                                                    <label class="custom-file-label" id="label-perintah_bangun_file"
                                                        for="label-perintah_bangun_file">Upload File Perintah
                                                        Bangun</label>
                                                    <btn id="list-upload_perintah_bangun_file" class='btn btn-outline-primary waves-effect btn-sm col-12 mt-1'>Lihat File</btn>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">SP3K</div>
                                            </div>


                                            <div class="form-group">
                                                <label for="bank">No SP3K</label>
                                                <input type="text" id="sp3k_no" name="sp3k_no" class="form-control"
                                                    placeholder="-" />
                                            </div>

                                            <div class="form-group">
                                                <label>SP3K</label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input"
                                                        accept="application/pdf" name="sp3k_file" id="sp3k_file" />
                                                    <label class="custom-file-label" id="label-sp3k_file"
                                                        for="label-sp3k_file">Upload File SP3K</label>
                                                    <btn id="list-upload_sp3k_file" class='btn btn-outline-primary waves-effect btn-sm col-12 mt-1'>Lihat File</btn>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" id="sp3k"
                                                        name="sp3k" value="1" />
                                                    <label class="custom-control-label" for="sp3k">SP3K</label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="sp3k_tgl">Tanggal Terbit</label>
                                                <input type="text" id="sp3k_tgl" name="sp3k_tgl"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="sp3k_tgl_exp">Tanggal Kadaluarsa</label>
                                                <input type="text" id="sp3k_tgl_exp" name="sp3k_tgl_exp"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">KPR</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="bank">Pengajuan</label>
                                                <input type="text" id="harga_kpr" name="harga_kpr"
                                                    class="form-control num" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="bank">Disetujui</label>
                                                <input type="text" id="acc_harga_kpr" name="acc_harga_kpr"
                                                    class="form-control num" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label for="bank">Turun KPR</label>
                                                <input type="text" id="harga_turun_kpr" name="harga_turun_kpr"
                                                    class="form-control num" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <button id="btn-add-tagihan-turunkpr"
                                                    class="btn btn-primary col-12">Buat Tagihan untuk Turun
                                                    KPR</button>
                                            </div>
                                            <div id="mkdt-tagihan_kpr"></div>
                                        </div>
                                    </div>


                                    <div class="card">
                                        <div class="card-body">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">Akad</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="rencana_akad_tgl">Tanggal Rencana Akad</label>
                                                <input type="text" id="rencana_akad_tgl" name="rencana_akad_tgl"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>Notaris</label>
                                                <input type="text" id="notaris" name="notaris" class="form-control"
                                                    placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>PPJB/AJB</label>
                                                <select class="form-control" id="is_ajb" name="is_ajb">
                                                    <option value=""></option>
                                                    <option value="AJB">AJB</option>
                                                    <option value="PPJB">PPJB</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" class="custom-control-input" id="akad"
                                                        name="akad" value="1" />
                                                    <label class="custom-control-label" for="akad">Akad</label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="akad_tgl">Tanggal Akad</label>
                                                <input type="text" id="akad_tgl" name="akad_tgl"
                                                    class="form-control flatpickr-human-friendly" placeholder="-" />
                                            </div>
                                            <div class="form-group">
                                                <label>No Debitur</label>
                                                <input type="text" id="debitur_no" name="debitur_no"
                                                    class="form-control" placeholder="-" />
                                            </div>
                                            <div class="form-group hidden">
                                                <label>No BAST</label>
                                                <input type="text" id="bast_no" name="bast_no" class="form-control"
                                                    placeholder="-" />
                                            </div>
                                            <div class="form-group hidden">
                                                <label>BAST</label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input"
                                                        accept="application/pdf" name="bast_file" id="bast_file" />
                                                    <label class="custom-file-label" id="label-bast_file"
                                                        for="label-bast_file">Upload File BAST</label>
                                                    <a href="" target=_blank id="list-upload_bast_file">Klik untuk
                                                        lihat
                                                        file</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-md-4 col-lg-4">
                                    </div>
                                    <div class="col-sm-12 col-md-4 col-lg-4">
                                    </div>
                                    <div class="col-sm-12 col-md-4 col-lg-4">
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
                    </div>
                    <!-- </div> -->
                    <!-- </div> -->
                </div>

                <br>
            </div>
            <div class="modal-footer">
                <button id="add-form-btn-mkdt" class="btn btn-primary data-submit mr-1" onclick="save_mkdt(this)"
                    href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
    </div>
    </form>
</div>
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