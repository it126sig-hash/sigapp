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