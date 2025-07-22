<div class="modal fade " id="modal-diskresi">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="fm-diskresi" class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Diskresi Harga Jual</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <a href="#" target="_blank" id="dir-pricelist_file" rel="noopener noreferrer"
                                class="form-control btn btn-outline btn-primary">Klik unuk melihat file</a>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Tanggal Pricelist</label>
                            <input type="text" disabled class="form-control flatpickr-human-friendly dir-fm"
                                id="dir-tgl_harga" name="dir-tgl_harga" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">ROW</label>
                            <input type="text" class="form-control num dir-fm" id="dir-row" name="dir-row" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Tipe</label>
                            <input type="text" class="form-control dir-fm text-right" id="dir-tipe_rumah"
                                name="dir-tipe_rumah" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">LB</label>
                            <input type="text" class="form-control num dir-fm" id="dir-lb" name="dir-lb" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">LT</label>
                            <input type="text" class="form-control num dir-fm" id="dir-lt" name="dir-lt" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Harga Jual</label>
                            <input type="text" class="form-control num dir-fm" id="dir-hargajual" name="dir-hargajual"
                                value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Harga Jual Net</label>
                            <input type="text" class="form-control num dir-fm" id="dir-hargajual_net"
                                name="dir-hargajual_net" value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">KPR</label>
                            <input type="text" class="form-control num dir-fm" id="dir-kpr" name="dir-kpr" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Uang Muka</label>
                            <input type="text" class="form-control num dir-fm" id="dir-uang_muka" name="dir-uang_muka"
                                value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Biaya Adm</label>
                            <input type="text" class="form-control num dir-fm" id="dir-biaya_adm" name="dir-biaya_adm"
                                value="" readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">BPHTB</label>
                            <input type="text" class="form-control num dir-fm" id="dir-bphtb" name="dir-bphtb" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">PPN</label>
                            <input type="text" class="form-control num dir-fm" id="dir-ppn" name="dir-ppn" value=""
                                readonly />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Biaya Proses</label>
                            <input type="text" class="form-control num dir-fm" id="dir-biaya_proses"
                                name="dir-biaya_proses" value="" readonly />
                        </div>


                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label class="dir-kavling_detail" for="basic-icon-default-fullname">Detail Kavling</label>
                            <textarea class="form-control" id="dir-kavling_detail" readonly name="dir-kavling_detail"
                                rows="3" placeholder="Detail Kavling"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-fullname">Harga Jual</label>
                            <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                            <input type="text" class="form-control num" id="dir-diskresi_harga"
                                name="dir-diskresi_harga" value="" placeholder="Perubahan Harga" />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="basic-icon-default-post">Memo</label>
                            <textarea class="form-control" id="dir-diskresi_memo" name="dir-diskresi_memo" rows="6"
                                placeholder="Memo perubahan harga"></textarea>
                        </div>
                        <div class="form-group">
                            <p>Diskresi Harga terakhir diubah oleh: <br> <span id="dir-username"
                                    style="font-weight: bold;"></span> (Pada: <span style="font-weight: bold;"
                                    id="dir-diskresi_at"></span>)</p>
                        </div>
                    </div>


                </div>


            </div>
            <div class="modal-footer">
                <a id="btn-save_diskresi" class="btn btn-primary mr-1" onclick="save_diskresi()"
                    href="javascript:void(0)">Simpan</a>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>