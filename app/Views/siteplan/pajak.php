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