<form id="fm-idk_keu" class="add-new-record modal-content pt-0" enctype="multipart/form-data" autocomplete="off">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Kavling</h5>
                </div>
                <div class="card-body">
                    <p class="modal-title label_alamat"></p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Status Kavling</h5>
                </div>
                <div class="col-12">
                    <div class="refresh_fmmkdt_div">
                        <button id="refresh-btn-idk_keu" type="button"
                            class="btn btn-primary btn-block waves-effect">Tambah
                            Konsumen
                            Baru</button>
                    </div>
                    <div class="delete_kons_div">
                        <button id="delete-btn-idk_keu" type="button"
                            class="btn btn-outline-danger btn-block waves-effect" onclick="delete_kons(false)">Hapus
                            Konsumen</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="divider divider-left">
                        <div class="divider-text font-weight-bold">Status Kavling</div>
                    </div>
                    <div class="form-group floating-label floating-label-select">
                        <select required class="form-control tab1" id="idk-status_mkdt" name="dt-status_mkdt">
                            <option value="">-</option>
                            <option value="Booking">Booking</option>
                            <option value="Akad">Akad</option>
                            <option value="Batal">Batal</option>
                        </select>
                        <label for="idk-status_mkdt">Status Kavling</label>
                    </div>
                    <div class="form-group floating-label floating-label-select">
                        <select required class="form-control tab1" id="idk-is_allin" name="idk-is_allin">
                            <option value=0>Tidak</option>
                            <option value=1>Ya</option>
                        </select>
                        <label for="idk-is_allin">Harga All In</label>
                    </div>

                    <div id="idk-show_keterangan_batal" class="hidden">
                        <div class="form-group">
                            <label for="keterangan_batal">Keterangan Batal</label>
                            <textarea class="form-control" id="idk-keterangan_batal" name="dt-keterangan_batal" rows="3"
                                placeholder="Keterangan"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="harga_jual">Nominal Pengembalian Dana</label>
                            <input type="text" class="form-control num" id="idk-refund" name="dt-refund">
                        </div>

                    </div>
                </div>
            </div>
            <div id="div-hargajual">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <div class="divider divider-left">
                                <div class="divider-text font-weight-bold">Harga Jual Pricelist</div>
                            </div>
                            <input type="text" readonly class="form-control num mk-fm" id="idk-mkdt_hargajual"
                                name="idk-mkdt_hargajual" value="" />
                            <span>Harga diinput oleh: <span id="idk-mkdt_hargajual_by" style="font-weight:bold"></span>
                                pada: <span id="idk-mkdt_hargajual_tgl" style="font-weight:bold"></span></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12"><a href="" id="btn-print_spptb" target="_blank" class="btn btn-success col-12"><i
                        class="fa fa-save"></i> Cetak SPPTB</a></div>
            <div class="row" id="idk-diskresi_st">
                <div style=" border: 1px solid red; background-color: red; border-radius: 10px 0px 0px 10px; color: white;"
                    class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname" style="color:white">Diskresi
                            harga</label>
                        <input type="text" readonly class="form-control num" id="idk-diskresi_harga"
                            name="mkdt_hargajual" value="" />
                        <span>Diskresi diinput oleh: <span style="font-weight:bold" id="idk-diskresi_oleh"></span> pada:
                            <span id="idk-diskresi_tgl" style="font-weight:bold"></span></span>

                    </div>
                </div>
                <div class="col-md-6"
                    style="border: 1px solid red; background-color: red; border-radius: 0px 10px 10px 0px; color: white;">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname" style="color:white">Memo</label>
                        <textarea name="idk-diskresi_memo" readonly id="idk-diskresi_memo" class="form-control"
                            cols="30" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9" id="tab-isi-konsumen">
            <div class="card">
                <div class="card-body pb-0 pt-0">
                    <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="idk_data_konsumen-tab" data-toggle="tab"
                                href="#idk_data_konsumen" aria-controls="idk_da""ta_konsumen" role="tab"
                                aria-selected="true">1. Data Konsumen ></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="idk_biaya-tab" data-toggle="tab" href="#idk_biaya"
                                aria-controls="idk_biaya" role="tab" aria-selected="true">2. Harga Jual ></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="idk_tagihan-tab" data-toggle="tab" href="#idk_tagihan"
                                aria-controls="data_konsumen" role="tab" aria-selected="true">3. Tagihan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="idk_arsip-tab" data-toggle="tab" href="#idk_arsip"
                                aria-controls="idk_arsip" role="tab" aria-selected="true">SPPTB Ditandatangani</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="idk_riwayat-tab" data-toggle="tab" href="#idk_riwayat"
                                aria-controls="idk_riwayat" role="tab" aria-selected="true">Riwayat Pindah
                                Kavling/Ganti Nama</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                <div class="tab-pane show active" id="idk_data_konsumen" aria-labelledby="idk_data_konsumen-tab"
                    role="tabpanel">
                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="idk-id_mkdt" name="id_mkdt" value="" />
                    <input type="hidden" class="form-control" id="idk-id_konsumen" name="id_konsumen" value="" />

                    <input type="hidden" class="form-control" id="idk-harga_akhir" name="idk-harga_akhir" value=""
                        readonly />
                    <input type="hidden" class="form-control" id="idk-hargajual" name="idk-hargajual" value=""
                        readonly />
                    <input type="hidden" class="form-control" id="idk-kpr" name="idk-kpr" value="" readonly />
                    <input type="hidden" class="form-control" id="idk-uang_muka" name="mkdt-uang_muka" value=""
                        readonly />
                    <input type="hidden" class="form-control" id="idk-bphtb" name="idk-bphtb" value="" readonly />
                    <input type="hidden" class="form-control" id="idk-biaya_adm" name="idk-biaya_adm" value=""
                        readonly />
                    <input type="hidden" class="form-control" id="idk-biaya_proses" name="idk-biaya_proses" value=""
                        readonly />

                    <input type="hidden" class="form-control" id="idk_data_baru" name="mkdt_data_baru" value="" />

                    <div class="row">

                    </div>
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
                                                <div class="dropzone dropzone-lg custom-file" id="dz-ktp">
                                                    <input type="file" class="custom-file-input dz-input"
                                                        accept="image/*" name="file_ktp" id="file_ktp">
                                                    <div class="dz-inner">
                                                        <div class="dz-preview" id="prev_file_ktp"></div>
                                                        <div class="dz-placeholder">
                                                            <div class="h5 mb-1">Tarik & letakkan gambar ke sini</div>
                                                            <div class="text-muted">atau klik untuk pilih file (PNG/JPG
                                                                maks
                                                                5 MB)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="" id="idk-file_ktp-here" target="_blank"
                                                class="w-100 btn btn-outline-primary">klik
                                                untuk melihat file</a>
                                        </div>
                                        <div class="col-md-4"> <!-- NPWP -->
                                            <div class="form-group">
                                                <label class="font-weight-bold">NPWP</label>
                                                <div class="dropzone dropzone-lg custom-file" id="dz-npwp">
                                                    <input type="file" class="custom-file-input dz-input"
                                                        accept="image/*" name="file_npwp" id="file_npwp">
                                                    <div class="dz-inner">
                                                        <div class="dz-preview" id="prev_file_npwp"></div>
                                                        <div class="dz-placeholder">
                                                            <div class="h5 mb-1">Tarik & letakkan gambar ke sini</div>
                                                            <div class="text-muted">atau klik (PNG/JPG maks 5 MB)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="" id="idk-file_npwp-here" target="_blank"
                                                class=" btn btn-outline-primary w-100">klik untuk melihat file</a>
                                        </div>
                                        <div class="col-md-4"> <!-- Data Diri (PDF) -->
                                            <div class="form-group">
                                                <label class="font-weight-bold">Data Diri (PDF)</label>
                                                <div class="dropzone dropzone-lg custom-file" id="dz-data-diri">
                                                    <input type="file" class="custom-file-input dz-input"
                                                        accept="application/pdf" name="file_data_diri"
                                                        id="file_data_diri">
                                                    <div class="dz-inner">
                                                        <div class="dz-preview" id="prev_file_data_diri"></div>
                                                        <div class="dz-placeholder">
                                                            <div class="h5 mb-1">Tarik & letakkan PDF ke sini</div>
                                                            <div class="text-muted">atau klik (PDF maks 10 MB)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="" id="idk-file_data_diri-here"
                                                class="btn btn-outline-primary w-100" target="_blank">klik untuk melihat
                                                file</a>
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
                                                <div class="divider-text font-weight-bold">Data Pribadi</div>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-no_spptb"
                                                    name="no_spptb" placeholder=" " required>
                                                <label for="idk-no_spptb">No SPPTB</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-nama_konsumen"
                                                    required name="nama_konsumen" placeholder=" ">
                                                <label for="idk-nama_konsumen">Nama Konsumen</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-nik_konsumen"
                                                    name="nik_konsumen" placeholder=" " required>
                                                <label for="idk-nik_konsumen">No. KTP</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-alamat_konsumen"
                                                    name="alamat_konsumen" placeholder=" ">
                                                <label for="idk-alamat_konsumen">Alamat Konsumen</label>
                                            </div>

                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-npwp_konsumen"
                                                    name="npwp_konsumen" placeholder=" ">
                                                <label for="idk-npwp_konsumen">NPWP</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-hp_konsumen"
                                                    name="hp_konsumen" placeholder=" ">
                                                <label for="idk-hp_konsumen">No. HP/telp</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-email_konsumen"
                                                    name="email_konsumen" placeholder=" ">
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
                                                <div class="divider-text font-weight-bold">Data Instansi</div>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-nama_instansi"
                                                    name="nama_instansi" placeholder=" " required>
                                                <label for="idk-nama_instansi">Nama Instansi</label>
                                            </div>

                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-alamat_instansi"
                                                    name="alamat_instansi" placeholder=" ">
                                                <label for="idk-alamat_instansi">Alamat Instansi</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-tel_instansi"
                                                    name="tel_instansi" placeholder=" ">
                                                <label for="idk-tel_instansi">No Hpt/telp Instansi</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-email_instansi"
                                                    name="email_instansi" placeholder=" ">
                                                <label for="idk-email_instansi">Email Instansi</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-alamat_surat"
                                                    name="alamat_surat" placeholder=" ">
                                                <label for="idk-alamat_surat">Alamat Surat</label>
                                            </div>

                                            <div class="form-group floating-label floating-label-select">
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
                                                <div class="divider-text font-weight-bold">Status Pernikahan</div>
                                            </div>

                                            <div class="form-group floating-label floating-label-select">
                                                <select class="form-control gn tab1" id="idk-status_pernikahan"
                                                    name="status_pernikahan">
                                                    <option value="Belum Kawin">Belum Kawin</option>
                                                    <option value="Kawin">Kawin</option>
                                                    <option value="Cerai Mati">Cerai Mati</option>
                                                    <option value="Cerai Hidup">Cerai Hidup</option>
                                                </select>
                                                <label for="idk-status_pernikahan">Status Pernikahan</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-nama_pasangan"
                                                    name="nama_pasangan" placeholder=" ">
                                                <label for="idk-nama_pasangan">Nama</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-nik_pasangan"
                                                    name="nik_pasangan" placeholder=" ">
                                                <label for="idk-nik_pasangan">No. KTP</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1" id="idk-hp_pasangan"
                                                    name="hp_pasangan" placeholder=" ">
                                                <label for="idk-hp_pasangan">No. HP/Telp</label>
                                            </div>
                                            <div class="form-group floating-label floating-label-select">
                                                <label for="idk-status_pekerjaan_pasangan">Status Pekerjaan</label>
                                                <select required class="form-control" id="idk-status_pekerjaan_pasangan"
                                                    name="status_pekerjaan_pasangan">
                                                    <option value="Bekerja">Bekerja</option>
                                                    <option value="Tidak Bekerja">Tidak Bekerja</option>
                                                    <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>
                                                </select>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control gn tab1"
                                                    id="idk-instansi_pasangan" name="instansi_pasangan" placeholder=" ">
                                                <label for="idk-instansi_pasangan">Instansi</label>
                                            </div>

                                        </div>

                                        <div class="col-md-3">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">Sales & Promo</div>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control" id="idk-sales" name="sales"
                                                    placeholder=" ">
                                                <label for="idk-sales">Sales</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control" id="idk-promo" name="promo"
                                                    placeholder=" ">
                                                <label for="idk-promo">Promo/Bonus/Hadiah</label>
                                            </div>

                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">TUNAI/KPR</div>
                                            </div>

                                            <div class="form-group floating-label floating-label-select">
                                                <select required class="form-control" id="idk-is_kpr" name="is_kpr"
                                                    onchange="sum_mktotal()">
                                                    <option value="0">TUNAI/CASH KERAS</option>
                                                    <option value="2">TUNAI/CASH BERTAHAP</option>
                                                    <option value="1">KPR</option>
                                                </select>
                                                <label for="idk-is_kpr">Tunai/KPR</label>
                                            </div>
                                            <div class="form-group floating-label floating-label-select">
                                                <select required class="form-control" id="idk-is_subsidi"
                                                    name="is_subsidi">
                                                    <option value="0">Non-Subsidi</option>
                                                    <option value="1">Subsidi</option>
                                                </select>
                                                <label for="idk-is_subsidi">Subsidi/Non-Subsidi</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control" id="idk-jenis_subsidi"
                                                    name="jenis_subsidi" placeholder=" ">
                                                <label for="idk-jenis_subsidi">Jenis Subsidi</label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="sticky-bottom bg-white border-top p-2 text-right">
                                <button class="btn btn-outline-secondary mr-2" data-dismiss="modal">Batal</button>
                                <button class="btn btn-primary">Simpan</button>
                            </div>
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
                                                <div class="divider-text font-weight-bold">1. Booking</div>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" required id="idk-booking_tgl" name="dt-booking_tgl"
                                                    class="form-control flatpickr-human-friendly tab2"
                                                    placeholder=" " />

                                                <label for="idk-booking_tgl">Tanggal Booking</label>
                                            </div>

                                            <div class="form-group floating-label">
                                                <input type="text" required class="form-control num tab2"
                                                    placeholder=" " id="idk-booking_fee" name="dt-booking_fee">
                                                <label for="idk-booking_fee">Booking Fee</label>
                                            </div>
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">2. Harga Jual</div>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text"
                                                    class="form-control text-right mk-fm flatpickr-human-friendly"
                                                    id="mk-tgl_harga" name="mk-tgl_harga" value="" readonly
                                                    placeholder=" " />
                                                <label class="form-label" for="mk-tgl_harga">Tanggal
                                                    PriceList</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control num mk-fm hitung-tambah"
                                                    id="mk-hargajual" name="mk-hargajual" value="" placeholder=" " />
                                                <label class="form-label" for="mk-hargajual">Harga
                                                    Jual</label>
                                            </div>

                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control num mk-fm " id="mk-hargajual_net"
                                                    name="mk-hargajual_net" value="" placeholder=" " />
                                                <label class="form-label" for="mk-hargajual_net">Harga Jual
                                                    Net</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control num mk-fm" id="mk-kpr"
                                                    name="mk-kpr" value="" placeholder=" " />
                                                <label class="form-label" for="mk-kpr">KPR(Pengajuan)</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control num mk-fm" id="mk-uang_muka"
                                                    name="mk-uang_muka" value="" placeholder=" " />
                                                <label class="form-label" for="mk-uang_muka">Uang
                                                    Muka</label>
                                            </div>


                                        </div>

                                        <div class="col-md-3">

                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">3. Biaya-biaya</div>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control num mk-fm" id="mk-biaya_adm"
                                                    name="mk-biaya_adm" value="" placeholder=" " />
                                                <label class="form-label" for="mk-biaya_adm">Biaya
                                                    Adm</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control num mk-fm totalbb" id="mk-ppn"
                                                    name="mk-ppn" placeholder=" ">
                                                <label for="mk-ppn">PPN</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control num mk-fm totalbb" id="mk-bphtb"
                                                    name="mk-bphtb" value="" placeholder=" " />
                                                <label class="form-label" for="mk-bphtb">BPHTB</label>
                                            </div>
                                            <div class="form-group floating-label">
                                                <input type="text" class="form-control num mk-fm totalbb"
                                                    id="mk-biaya_proses" name="mk-biaya_proses" value=""
                                                    placeholder=" " />
                                                <label class="form-label" for="mk-biaya_proses">Biaya
                                                    Proses</label>
                                            </div>
                                            <!-- disembunyikan dulu karna masih belum tau bakal kepake atau engga -->
                                            <div class="hidden">
                                                <div class="form-group floating-label">
                                                    <input type="text" class="form-control num mk-fm" id="mk-row"
                                                        name="mk-row" value="" placeholder=" " />
                                                    <label class="form-label" for="mk-row">ROW</label>
                                                </div>
                                                <div class="form-group floating-label">
                                                    <input type="text" class="form-control mk-fm text-right"
                                                        id="mk-tipe" name="mk-tipe" value="" placeholder=" " />
                                                    <label class="form-label" for="mk-tipe">Tipe</label>
                                                </div>
                                                <div class="form-group floating-label">
                                                    <input type="text" class="form-control num mk-fm" id="mk-lb"
                                                        name="mk-lb" value="" placeholder=" " />
                                                    <label class="form-label" for="mk-lb">LB</label>
                                                </div>
                                                <div class="form-group floating-label">
                                                    <input type="text" class="form-control num mk-fm" id="mk-lt"
                                                        name="mk-lt" value="" placeholder=" " />
                                                    <label class="form-label" for="mk-lt">LT</label>
                                                </div>

                                            </div>
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">4. Biaya Tambahan</div>
                                            </div>
                                            <div class="form-group">
                                                <label for="total_biaya2">Biaya Kelebihan Tanah</label>
                                                <input type="text" class="form-control num mk-fm"
                                                    id="mk-harga_penambahan_tanah" name="mk-harga_penambahan_tanah">
                                            </div>
                                            <div class="form-group hidden">
                                                <label for="total_biaya2">Keterangan Penambahan Biaya</label>
                                                <textarea name="mk-keterangan_harga_penambahan"
                                                    id="mk-keterangan_harga_penambahan" class="form-control mk-fm"
                                                    cols="30" rows="2"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="total_biaya2">Biaya Kavling Strategis</label>
                                                <input type="text" class="form-control num mk-fm"
                                                    id="mk-harga_penambahan" name="mk-harga_penambahan">
                                            </div>

                                        </div>

                                        <div class="col-md-3">

                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">5. Potongan</div>
                                            </div>
                                            <div class="form-group floating-label hidden" id="hjdis">
                                                <input type="text" class="form-control num mk-fm"
                                                    id="mk-diskon_harga_jual" name="mk-diskon_harga_jual" value=""
                                                    placeholder=" " />
                                                <label class="form-label" for="mk-diskon_harga_jual">Diskon Harga
                                                    Jual</label>
                                            </div>
                                            <div class="form-group floating-label" id="umdis">
                                                <input type="text" class="form-control num mk-fm"
                                                    id="mk-diskon_uang_muka" name="mk-diskon_uang_muka" value=""
                                                    placeholder=" " />
                                                <label class="form-label" for="mk-diskon_uang_muka">Diskon</label>
                                            </div>
                                            <div class="form-group floating-label" id="sbumdis">
                                                <input type="text" class="form-control num mk-fm" id="mk-harga_sbum"
                                                    name="mk-harga_sbum" value="" placeholder=" " />
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
                                                <div class="divider-text font-weight-bold">6. Catatan/Keterangan</div>
                                            </div>
                                            <div class="form-group floating-label floating-label-select">
                                                <textarea class="form-control" rows="4" id="idk-rincian"
                                                    name=" "></textarea>
                                                <label class="form-label" for="mk-lt">Keterangan</label>
                                            </div>


                                        </div>
                                        <div class="col-md-3">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">7. Total</div>
                                            </div>
                                            <div class="form-group hidden">
                                                <label>Total Uang Muka + Biaya ADM</label>
                                                <input readonly type="text" class="form-control num tum" id="mk-tum"
                                                    name="mk-tum">
                                            </div>
                                            <div class="form-group hidden">
                                                <label>Total Biaya-Biaya</label>
                                                <input readonly type="text" class="form-control num tbb" id="mk-tbb"
                                                    name="mk-tbb">
                                            </div>
                                            <div class="form-group floating-label">
                                                <label>Total Harga Allin</label>
                                                <input placeholder=" " type="text" required
                                                    class="form-control num mk-fm" id="mk-harga_allin"
                                                    name="mk-harga_allin">
                                            </div>
                                            <div class="form-group  floating-label">
                                                <label>Grand Total</label>
                                                <input readonly type="text" placeholder=" " class="form-control num tgt"
                                                    id="mk-tgt" name="mk-tgt">
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>


                    </div>

                </div>
                <div class="tab-pane" id="idk_tagihan" aria-labelledby="idk_tagihan-tab" role="tabpanel">
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
                                                <div class="divider-text font-weight-bold">Total Yang Harus Dibayar
                                                </div>
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
                                            <input name="id_list_keu" id="id_list_keu" class="form-control"
                                                type="hidden">
                                            <input name="id_keuangan" id="id_keuangan" class="form-control"
                                                type="hidden">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">Buat Tagihan</div>
                                            </div>
                                            <div class="form-group floating-label floating-label-select">
                                                <select class="form-control" required name="berita_acara"
                                                    id="berita_acara">
                                                    <option value="Angsuran">Angsuran</option>
                                                    <option value="Uang Muka">Uang Muka</option>
                                                    <option value="Biaya Administrasi">Biaya Administrasi</option>
                                                    <option value="Turun KPR">Turun KPR</option>
                                                    <option value="Biaya Kavling Strategis">Biaya Kavling Strategis
                                                    </option>
                                                    <option value="Biaya Kelebihan Tanah">Biaya Kelebihan Tanah
                                                    </option>
                                                </select>
                                                <label>Untuk Tagihan</label>
                                                <!-- <input required name="berita_acara" id="berita_acara"
                                                        class="form-control" type="text"> -->
                                                <span class="help-block"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>Nominal</label>
                                                <input name="nominal" id="nominal" onchange="sum_tg(this.value)"
                                                    class="form-control num tg" type="text">
                                                <span class="help-block"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>Tanggal Jatuh Tempo</label>
                                                <input required name="jatuh_tempo_tgl" id="jatuh_tempo_tgl"
                                                    class="form-control flatpickr-human-friendly" type="date">
                                                <span class="help-block"></span>
                                            </div>
                                            <div id="cicilan_belong_here"></div>
                                            <button id="tambah_list" type="button"
                                                class="btn btn-outline-primary btn-block waves-effect"
                                                onclick="tambah_()">+
                                                Tagihan Angsuran</button>
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
                                            <input readonly type="hidden" class="form-control num" id="total_cicilan_bb"
                                                name="total_cicilan_bb">
                                            <!-- </div> -->
                                            <input name="id_list_keu_bb" id="id_list_keu_bb" class="form-control"
                                                type="hidden">
                                            <input name="id_keuangan_bb" id="id_keuangan_bb" class="form-control"
                                                type="hidden">
                                            <div class="form-group">
                                                <label>Untuk Tagihan</label>
                                                <select class="form-control" required name="berita_acara_bb"
                                                    id="berita_acara_bb">
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
                                                    onchange="sum_tg(this.value, '_bb')" class="form-control num tg"
                                                    type="text">
                                                <span class="help-block"></span>
                                            </div>
                                            <div class="form-group">
                                                <label>Tanggal Jatuh Tempo</label>
                                                <input required name="jatuh_tempo_tgl_bb" id="jatuh_tempo_tgl_bb"
                                                    class="form-control flatpickr-human-friendly" type="date">
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
                                                    <td colspan="5" class="text-center">Tidak Ada Data</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!-- <button class="btn btn-sm btn-primary" onclick="addRow()">Tambah Baris</button> -->

                                    </div>
                                </div>
                            </div>

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
                                            <input type="file" class="custom-file-input" accept="application/pdf"
                                                name="file_spptb" id="idk_file_spptb" onchange="" />
                                            <label class="custom-file-label" id="label-idk_file_spptb"
                                                for="idk_file_spptb">Upload SPPTB yang sudah ditandatangani</label>
                                        </div>
                                        <div id="list-idk_file_spptb" style="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Lampiran Surat Kuasa SPPTB</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" accept="application/pdf"
                                                name="file_surat_kuasa" id="idk_file_surat_kuasa" onchange="" />
                                            <label class="custom-file-label" id="label-idk_file_surat_kuasa"
                                                for="idk_file_surat_kuasa">Upload Lampiran Surat Kuasa SPPTB</label>
                                        </div>
                                        <div id="list-idk_lampiran" style="">
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
                                            <a class="nav-link active" id="spptb_ttd-tab" data-toggle="tab"
                                                href="#spptb_ttd" aria-controls="spptb_ttd" role="tab"
                                                aria-selected="true">SPPTB Sudah Ditandatangan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="spptb_riwayat-tab" data-toggle="tab"
                                                href="#spptb_riwayat" aria-controls="spptb_riwayat" role="tab"
                                                aria-selected="true">Riwayat Upload SPPTB</a>
                                        </li>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="tab-pane show active" id="spptb_ttd" aria-labelledby="spptb_ttd-tab"
                                            role="tabpanel">
                                            <div id="spptb_ttd_file"></div>
                                        </div>
                                        <div class="tab-pane" id="spptb_riwayat" aria-labelledby="spptb_riwayat-tab"
                                            role="tabpanel">
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
                </div>
                <div class="tab-pane" id="idk_riwayat" aria-labelledby="idk_riwayat-tab" role="tabpanel">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6">

                            <div class="card file-container">
                                <div class="card-body">
                                    <button class="btn btn-outline-primary" id="btn-ganti_nama"
                                        onclick="ganti_nama()">Klik
                                        Untuk Ganti Nama Konsumen</button>
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
                                    <button class="btn btn-outline-warning" id="btn-refresh-ganti_kavling"
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
                </div>
            </div>
        </div>

    </div>
    <br>
</form>