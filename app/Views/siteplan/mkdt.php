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
            <div class="modal-body flex-grow-1">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Proyek</label>
                            <input type="hidden" class="form-control" id="points" readonly name="points" value="" />
                            <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                            <input type="text" class="form-control" id="nama_proyek" readonly name="nama_proyek"
                                value="<?= $data['proyek']->nama_proyek ?>" placeholder="ASI" />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-post">Kavling</label>
                            <textarea class="form-control" id="sh-kavling" name="sh-kavling" rows="6" readonly
                                placeholder="Kavling"></textarea>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Pricelist</label>
                            <select class="select2 custom-select sh-fm" id="sh-id" name="sh-id" value=""></select>
                        </div>
                        <div class="form-group">
                            <a href="#" target="_blank" id="sh-pricelist_file" rel="noopener noreferrer"
                                class="form-control btn btn-outline btn-primary">Klik unuk melihat file</a>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">ROW</label>
                            <input type="text" class="form-control num sh-fm" id="sh-row" name="sh-row" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Tipe</label>
                            <input type="text" class="form-control sh-fm text-right" id="sh-tipe" name="sh-tipe"
                                value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">LB</label>
                            <input type="text" class="form-control num sh-fm" id="sh-lb" name="sh-lb" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">LT</label>
                            <input type="text" class="form-control num sh-fm" id="sh-lt" name="sh-lt" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Harga Jual</label>
                            <input type="text" class="form-control num sh-fm" id="sh-hargajual" name="sh-hargajual"
                                value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Harga Jual Net</label>
                            <input type="text" class="form-control num sh-fm" id="sh-hargajual_net"
                                name="sh-hargajual_net" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">KPR</label>
                            <input type="text" class="form-control num sh-fm" id="sh-kpr" name="sh-kpr" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Uang Muka</label>
                            <input type="text" class="form-control num sh-fm" id="sh-uang_muka" name="sh-uang_muka"
                                value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Biaya Adm</label>
                            <input type="text" class="form-control num sh-fm" id="sh-biaya_adm" name="sh-biaya_adm"
                                value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                            <input type="text" class="form-control num sh-fm" id="sh-bphtb" name="sh-bphtb" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">PPN</label>
                            <input type="text" class="form-control num sh-fm" id="sh-ppn" name="sh-ppn" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Biaya Proses</label>
                            <input type="text" class="form-control num sh-fm" id="sh-biaya_proses"
                                name="sh-biaya_proses" value="" readonly />
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
                <h5 class="modal-title" id="exampleModalLabel">Marketing Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1" style="background-color:#eee">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Detail Kavling</h5>
                            </div>
                            <div class="card-body">
                                <p class="modal-title label_alamat" id="label_alamat4"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">

                    <div class="card-body pb-0 pt-0">
                        <ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="data_konsumen-tab" data-toggle="tab"
                                    href="#data_konsumen" aria-controls="data_konsumen" role="tab"
                                    aria-selected="true">Data Konsumen</a>
                            </li>
                            <!-- <li class="nav-item">
                        <a class="nav-link" id="detail_biaya-tab" data-toggle="tab" href="#detail_biaya" aria-controls="detail_biaya" role="tab" aria-selected="true">Detail</a>
                    </li> -->
                            <!-- <li class="nav-item">
                            <a class="nav-link" id="detail_tagihan-tab" data-toggle="tab" href="#detail_tagihan" aria-controls="detail_tagihan" role="tab" aria-selected="false">Detail Tagihan</a>
                        </li> -->
                            <li class="nav-item">
                                <a class="nav-link" id="status-tab" data-toggle="tab" href="#status"
                                    aria-controls="detail_tagihan" role="tab" aria-selected="false">Status </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane show active" id="data_konsumen" aria-labelledby="data_konsumen-tab"
                                role="tabpanel">
                                <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                                <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                                <input type="hidden" class="form-control" id="id_konsumen" name="id_konsumen"
                                    value="" />

                                <input type="hidden" class="form-control" id="mkdt_data_baru" name="mkdt_data_baru"
                                    value="" />
                                <div class="row">
                                    <div class="col-sm-12 col-md-4 col-lg-4">
                                        <div id="refresh_fmmkdt_div">
                                            <button id="refresh_fmmkdt_btn" type="button"
                                                class="btn btn-outline-primary btn-block waves-effect">Tambah Konsumen
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
                                    <div class="col-sm-12 col-md-3 col-lg-3">
                                        <div class="divider">
                                            <div class="divider-text">Status</div>
                                        </div>
                                        <div class="form-group">
                                            <label for="status_kavling">Status Booking</label>
                                            <select required class="form-control" id="status_mkdt" name="status_mkdt">
                                                <option value="">-</option>
                                                <option value="Booking">Booking</option>
                                                <option value="Akad">Akad</option>
                                                <option disabled value="Batal">Batal</option>
                                            </select>
                                        </div>
                                        <div id="show_keterangan_batal" class="hidden">
                                            <div class="form-group">
                                                <label for="keterangan_batal">Keterangan Batal</label>
                                                <textarea class="form-control" id="keterangan_batal"
                                                    name="keterangan_batal" rows="3"
                                                    placeholder="Keterangan"></textarea>
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
                                            <input type="text" class="form-control" id="hp_konsumen" name="hp_konsumen">
                                        </div>
                                        <div class="form-group">
                                            <label for="hp_konsumen">Email Konsumen</label>
                                            <input type="text" class="form-control" id="email_konsumen"
                                                name="email_konsumen">
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
                                            <select required class="form-control" id="is_subsidi" name="is_subsidi">
                                                <option value="0">Non-Subsidi</option>
                                                <option value="1">Subsidi</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="jenis_subsidi">Jenis Subsidi</label>
                                            <input type="text" placeholder="FLPP/TAPERA/LAIN-LAIN" class="form-control"
                                                id="jenis_subsidi" name="jenis_subsidi">
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
                                                <input type="file" class="custom-file-input" accept="application/pdf"
                                                    name="file_data_diri" id="file_data_diri" />
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
                                        <a href="" id="file_ktp-here" target="_blank"
                                            class="w-100 btn btn-outline-primary">klik untuk melihat file</a>

                                        <div class="divider">
                                            <div class="divider-text">NPWP</div>
                                        </div>
                                        <a href="" id="file_npwp-here" target="_blank"
                                            class=" btn btn-outline-primary w-100">klik untuk melihat file</a>
                                        <div class="divider">
                                            <div class="divider-text">Data Diri</div>
                                        </div>
                                        <a href="" id="file_data_diri-here" class="btn btn-outline-primary w-100"
                                            target="_blank">klik untuk melihat file</a>
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
                                            <input type="text" id="booking_tgl" name="booking_tgl"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div>
                                        <div class="form-group">
                                            <label for="booking_fee">Booking Fee</label>
                                            <input type="text" readonly class="form-control num" id="booking_fee"
                                                name="booking_fee">
                                        </div>
                                        <div class="divider">
                                            <div class="divider-text">Wawancara</div>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-switch custom-control-inline">
                                                <input type="checkbox" class="custom-control-input" id="wawancara"
                                                    name="wawancara" value="1" />
                                                <label class="custom-control-label" for="wawancara">Sudah
                                                    Wawancara</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="bank">Bank</label>
                                            <input type="text" id="bank" name="bank" class="form-control"
                                                placeholder="-" />
                                        </div>
                                        <div class="form-group">
                                            <label for="wawancara_tgl">Tanggal Wawancara</label>
                                            <input type="text" id="wawancara_tgl" name="wawancara_tgl"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div>


                                    </div>

                                    <div class="col-sm-12 col-md-4 col-lg-4">
                                        <div class="divider">
                                            <div class="divider-text">SP3K</div>
                                        </div>
                                        <div class="form-group">
                                            <label>Status</label>
                                            <input type="text" id="mkdt_keterangan" name="mkdt_keterangan"
                                                class="form-control" placeholder="ACC SP3K/REJECT/WAWANCARA/DLL" />
                                            <!-- <select class="form-control" id="mkdt_keterangan" name="mkdt_keterangan">
                                                <option value="">-</option>
                                                <option value="Disetujui">Disetujui</option>
                                                <option value="Ditolak">Ditolak</option>
                                            </select> -->
                                            <!-- <input type="text"  class="form-control" placeholder="Disetujui/Ditolak" /> -->
                                        </div>
                                        <div class="form-group">
                                            <label for="bank">Pengajuan</label>
                                            <input type="text" id="harga_kpr" name="harga_kpr" class="form-control num"
                                                placeholder="-" />
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
                                            <label for="bank">No SP3K</label>
                                            <input type="text" id="sp3k_no" name="sp3k_no" class="form-control"
                                                placeholder="-" />
                                        </div>
                                        <div class="form-group">
                                            <label>SP3K</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" accept="application/pdf"
                                                    name="sp3k_file" id="sp3k_file" />
                                                <label class="custom-file-label" id="label-sp3k_file"
                                                    for="label-sp3k_file">Upload File SP3K</label>
                                                <a href="" target=_blank id="list-upload_sp3k_file">Klik untuk lihat
                                                    file</a>
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
                                            <input type="text" id="sp3k_tgl" name="sp3k_tgl"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div>
                                        <div class="form-group">
                                            <label for="sp3k_tgl">Tanggal Kadaluarsa</label>
                                            <input type="text" id="sp3k_tgl_exp" name="sp3k_tgl_exp"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-4 col-lg-4">
                                        <div class="divider">
                                            <div class="divider-text">Perintah Bangun</div>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-switch custom-control-inline">
                                                <input type="checkbox" class="custom-control-input" id="perintah_bangun"
                                                    name="perintah_bangun" value="1" />
                                                <label class="custom-control-label" for="perintah_bangun">Perintah
                                                    Bangun</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="perintah_bangun_tgl">Tanggal Perintah Bangun</label>
                                            <input type="text" readonly="readonly" id="perintah_bangun_tgl"
                                                name="perintah_bangun_tgl" class="form-control flatpickr-human-friendly"
                                                placeholder="-" />
                                        </div>
                                        <div class="form-group">
                                            <label for="perintah_bangun_oleh">Oleh</label>
                                            <input type="text" readonly="readonly" id="perintah_bangun_oleh"
                                                name="perintah_bangun_oleh" class="form-control" placeholder="-" />
                                        </div>
                                        <div class="form-group">
                                            <label>Perintah Bangun</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" accept="application/pdf"
                                                    name="perintah_bangun_file" id="perintah_bangun_file" />
                                                <label class="custom-file-label" id="label-perintah_bangun_file"
                                                    for="label-perintah_bangun_file">Upload File Perintah Bangun</label>
                                                <a href="#" target=_blank id="list-upload_perintah_bangun_file">Klik
                                                    untuk lihat
                                                    file</a>
                                            </div>
                                        </div>
                                        <div class="divider">
                                            <div class="divider-text">Akad</div>
                                        </div>
                                        <div class="form-group">
                                            <label for="rencana_akad_tgl">Rencana Akad</label>
                                            <input type="text" id="rencana_akad_tgl" name="rencana_akad_tgl"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
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
                                            <input type="text" id="debitur_no" name="debitur_no" class="form-control"
                                                placeholder="-" />
                                        </div>
                                        <div class="form-group">
                                            <label>No BAST</label>
                                            <input type="text" id="bast_no" name="bast_no" class="form-control"
                                                placeholder="-" />
                                        </div>
                                        <div class="form-group">
                                            <label>BAST</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" accept="application/pdf"
                                                    name="bast_file" id="bast_file" />
                                                <label class="custom-file-label" id="label-bast_file"
                                                    for="label-bast_file">Upload File BAST</label>
                                                <a href="" target=_blank id="list-upload_bast_file">Klik untuk lihat
                                                    file</a>
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
                    </div>
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
<script></script>