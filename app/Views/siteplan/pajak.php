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
                <div class="modal-body flex-grow-1" style="background-color:#eee">
                    <p class="modal-title label_konsumen" id="label_konsumen"></p>
                    <p class="modal-title label_alamat" id="label_alamat3"></p>
                    <hr>

                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                    <input type="hidden" class="form-control" id="pajak-id_pajak" name="id" value="" />
                    <div class="card">
                        <div class="card-body pb-0 pt-0">
                            <ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="fp-pph42-tab" data-toggle="tab" href="#fp-pph42" aria-controls="data_konsumen" role="tab" aria-selected="true">PPh4(2)</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="fp-ppn-tab" data-toggle="tab" href="#fp-ppn" aria-controls="fp-ppn" role="tab" aria-selected="true">PPN</a>
                                </li>
                                <!-- <li class="nav-item">
                                    <a class="nav-link" id="fp-ppnjk-tab" data-toggle="tab" href="#fp-ppnjk" aria-controls="fp-ppnjk" role="tab" aria-selected="false">PPn Jasa Konstruksi</a>
                                </li> -->
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Detail Kavling</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Nama Konsumen</label>
                                        <input type="text" id="pajak-nama_konsumen" name="nama_konsumen" class="form-control" readonly placeholder="Nama Konsumen" />
                                    </div>
                                    <div class="form-group">
                                        <label>Alamat Konsumen</label>
                                        <input type="text" id="pajak-alamat_konsumen" name="alamat_konsumen" class="form-control" readonly placeholder="Alamat Konsumen" />
                                    </div>
                                    <div class="form-group">
                                        <label>NPWP</label>
                                        <input type="text" id="pajak-npwp" name="npwp" class="form-control" readonly placeholder="NPWP" />
                                    </div>
                                    <div class="form-group">
                                        <label>No Telepon</label>
                                        <input type="text" id="pajak-hp_konsumen" name="hp_konsumen" class="form-control" readonly placeholder="Kontak Konsumen" />
                                    </div>
                                    <div class="form-group">
                                        <label>NOP</label>
                                        <input type="text" id="pajak-pbb_pecah_nop" name="pbb_pecah_nop" class="form-control" readonly placeholder="NOP" />
                                    </div>
                                    <div class="form-group">
                                        <label>Harga Jual (Net)</label>
                                        <input type="text" id="pajak-harga_jual_net" name="harga_jual_net" class="form-control num" readonly placeholder="Harga Jual Net" />
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal Akad</label>
                                        <input type="text" id="pajak-akad_tgl" name="akad_tgl" class="form-control flatpickr-human-friendly" disabled />
                                    </div>
                                    <div class="form-group">
                                        <label>Nominal PPN</label>
                                        <input type="text" id="pajak-harga_ppn" name="harga_ppn" class="form-control num" readonly />
                                    </div>
                                    <div class="divider">
                                        <div class="divider-text">Berkas yang sudah diunggah</div>
                                    </div>
                                    <div id="file_ajb-here"></div>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-9">
                            <div class="card">
                                <div class="crad-body m-1">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="fp-pph42" aria-labelledby="fp-pph42-tab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Tarif PPH 4(2)</label>
                                                        <select name="pph42_tarif" class="form-control" id="pajak-pph42_tarif">
                                                            <?php
                                                            foreach ($pph as $p) {
                                                                echo "<option data-ket='$p->ket' value='$p->id'>$p->besar%</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Nominal PPH 4(2)</label>
                                                        <input type="text" id="pajak-pph42_nilai" name="pph42_nilai" class="form-control num" placeholder="Nilai Bayar PPH" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>ID Billing</label>
                                                        <input type="text" id="pajak-pph42_id_billing" name="pph42_id_billing" class="form-control" placeholder="ID Billing" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>NTPN</label>
                                                        <input type="text" id="pajak-pph42_ntpn" name="pph42_ntpn" class="form-control" placeholder="NTPN" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal Pembayaran PPH</label>
                                                        <input type="text" id="pajak-pph42_tgl_bayar" name="pph42_tgl_bayar" class="form-control flatpickr-human-friendly" placeholder="Tanggal Pembayaran" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="mkdt_keterangan">Keterangan</label>
                                                        <textarea class="form-control" id="pajak-pph42_keterangan" name="pph42_keterangan" rows="3" placeholder="Keterangan"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="hidden form-group">
                                                        <label> Ketgori </label>
                                                        <select id="pajak-pph42_kategori" class="form-control" name="pph42_kategori-ebilling">
                                                            <option selected value="E-billing">E-billing</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="fl-file"> Unggah E-billing </label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="pph42_file-ebilling"
                                                                accept="application/pdf" id="pph42_file-ebilling" />
                                                            <label class="custom-file-label" id="pph42_file-ebilling-label" for="pph42_file-ebilling">Pilih
                                                                Berkas</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label> Keterangan: </label>
                                                        <textarea cols="40" rows="3" id="pajak-pph42_file_keterangan-ebilling" name="pph42_file_keterangan-ebilling"
                                                            class="form-control" placeholder="Keterangan"></textarea>
                                                    </div>

                                                    <hr class="col-12">
                                                    <div class="hidden form-group">
                                                        <label> Ketgori </label>
                                                        <select id="pajak-pph42_kategori-bpn" class="form-control" name="pph42_kategori-bpn">
                                                            <option selected value="Bukti Penerimaan Negara (BPN)">Bukti Penerimaan Negara (BPN)</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="fl-file"> Unggah Bukti Penerimaan Negara (BPN) </label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="pph42_file-bpn"
                                                                accept="application/pdf" id="pph42_file-bpn" />
                                                            <label class="custom-file-label" id="pph42_file-bpn-label" for="pph42_file-bpn">Pilih
                                                                Berkas</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label> Keterangan: </label>
                                                        <textarea cols="40" rows="3" id="pajak-pph42_file_keterangan-bpn" name="pph42_file_keterangan-bpn"
                                                            class="form-control" placeholder="Keterangan"></textarea>
                                                    </div>

                                                </div>
                                                <div class="col-md-4">
                                                    <div class="divider">
                                                        <div class="divider-text">Berkas yang sudah diunggah</div>
                                                    </div>
                                                    <div id="file_pph42-here">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="fp-ppn" aria-labelledby="fp-ppn-tab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Tarif PPN</label>
                                                        <select name="ppn_tarif" class="form-control" id="pajak-ppn_tarif">
                                                            <?php
                                                            foreach ($ppn as $p) {
                                                                echo "<option value='$p->id'>$p->besar%</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Nominal PPN</label>
                                                        <input type="text" id="pajak-ppn_nilai" name="ppn_nilai" class="form-control num" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>ID Billing</label>
                                                        <input type="text" id="pajak-ppn_id_billing" name="ppn_id_billing" class="form-control" placeholder="ID Billing" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>NTPN</label>
                                                        <input type="text" id="pajak-ppn_ntpn" name="ppn_ntpn" class="form-control" placeholder="NTPN" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>No Faktur</label>
                                                        <input type="text" id="pajak-ppn_no_faktur" name="ppn_no_faktur" class="form-control" placeholder="No Faktur" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Tanggal Pembayaran PPN</label>
                                                        <input type="text" id="pajak-ppn_tgl_bayar" name="ppn_tgl_bayar" class="form-control flatpickr-human-friendly" placeholder="Tanggal Pembayaran" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="mkdt_keterangan">Keterangan</label>
                                                        <textarea class="form-control" id="pajak-ppn_keterangan" name="ppn_keterangan" rows="3" placeholder="Keterangan"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="hidden form-group">
                                                        <label>Unggah E-Billing</label>
                                                        <select id="pajak-ppn_kategori" class="form-control hidden" name="ppn_kategori-ebilling">
                                                            <option selected value="E-billing">E-billing</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="fl-file"> Unggah E-Billing </label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="ppn_file-ebilling"
                                                                accept="application/pdf" id="ppn_file-ebilling" />
                                                            <label class="custom-file-label" id="ppn_file-ebilling-label" for="ppn_file-ebilling">Pilih
                                                                Berkas</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label> Keterangan: </label>
                                                        <textarea cols="40" rows="3" id="pajak-ppn_file_keterangan-ebilling" name="ppn_file_keterangan-ebilling"
                                                            class="form-control" placeholder="Keterangan"></textarea>
                                                    </div>
                                                    <hr class="col-12">

                                                    <div class=" hidden form-group">
                                                        <label> Unggah BPN </label>
                                                        <select id="pajak-ppn_kategori-bpn" class="hidden form-control" name="ppn_kategori-bpn">
                                                            <option selected value="Bukti Penerimaan Negara (BPN)">Bukti Penerimaan Negara (BPN)</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="fl-file"> Unggah BPN </label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="ppn_file-bpn"
                                                                accept="application/pdf" id="ppn_file-bpn" />
                                                            <label class="custom-file-label" id="ppn_file-bpn-label" for="ppn_file-bpn">Pilih
                                                                Berkas</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label> Keterangan: </label>
                                                        <textarea cols="40" rows="3" id="pajak-ppn_file_keterangan-bpn" name="ppn_file_keterangan-bpn"
                                                            class="form-control" placeholder="Keterangan"></textarea>
                                                    </div>

                                                    <hr class="col-12">
                                                    <div class="hidden form-group">
                                                        <label> Unggah Faktu Pajak </label>
                                                        <select id="pajak-ppn_kategori-faktur" class="hidden form-control" name="ppn_kategori-faktur">
                                                            <option selected value="Faktur Pajak">Faktur Pajak</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="fl-file"> Unggah Faktur Pajak </label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="ppn_file-faktur"
                                                                accept="application/pdf" id="ppn_file-faktur" />
                                                            <label class="custom-file-label" id="ppn_file-bpn-label" for="ppn_file-faktur">Pilih
                                                                Berkas</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label> Keterangan: </label>
                                                        <textarea cols="40" rows="3" id="pajak-ppn_file_keterangan-faktur" name="ppn_file_keterangan-faktur"
                                                            class="form-control" placeholder="Keterangan"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="divider">
                                                        <div class="divider-text">Berkas yang sudah diunggah</div>
                                                    </div>
                                                    <div id="file_ppn-here">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="fp-ppnjk" aria-labelledby="fp-ppnjk-tab" role="tabpanel">

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button id="add-form-btn-pajak" class="btn btn-primary data-submit mr-1" onclick="save_pajak(); return false;" href="javascript:void(0)">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                </div>

            </div>
        </form>
    </div>

</div>