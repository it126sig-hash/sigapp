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

<script>
    /******************************** direksi ******************************************/


function open_diskresi() {
    var role,
        sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    //jika admin login
    if (roleid == 1)
        role = $('#pilih-divisi option:selected').val()
    else
        role = roleid

    if (sh.data.tipe != "kavling")
        return swal("error", "Tidak ada kavling terpilih", null, true);

    if (sh.data2.harga_akhir == "-") {
        return swal("error", "Kavling belum dipasarkan (tidak ada harga jual)");
    }



    $("#fm-diskresi")[0].reset();
    $("#fm-diskresi .num").val(0);

    $(".id_kavling").val(id_kavling);
    $("#dir-pricelist_file").prop("href", "#")

    $("#dir-username, #dir-diskresi_at").text("-")

    $.ajax({
        url: base_url + "direksi/get_data_by_id",
        type: "post",
        data: {
            [csrfName]: csrfHash,
            id_kavling: id_kavling,
        },
        dataType: "json",
        beforeSend: function() {
            $("#loading").removeClass("hidden");
        },
        success: function(x) {
            csrfHash = x.token;
            let r = x.data,
                hj = x.harga_akhir


            $("#dir-kavling_detail").val(`${dt_proyek.nama_proyek}\n${sh.data.nama_jalan}, No.${sh.data.no_kavling}\n${sh.data2.no_tipe_rumah} (${sh.data2.tipe_rumah})`)

            if (hj) {
                $.each(hj, (i, v) => {
                    changeVal("#dir-" + i, v)
                })
                setDatePicker(hj.tgl_harga, "#dir-tgl_harga")
                $("#dir-pricelist_file").prop("href", hj.access_url || file_url('file_hargajual', hj.id_filehj))
            }

            if (r) {
                changeVal("#dir-diskresi_harga", r.diskresi_harga)
                changeVal("#dir-diskresi_memo", r.diskresi_memo)
                $("#dir-username").text(r.username)
                $("#dir-diskresi_at").text(format_datetime(r.diskresi_at))
            }

            $("#modal-diskresi").modal({
                backdrop: "static",
                keyboard: false,
            });
        },
        error: function(xhr, st, err) {
            return swal("error", err);
        },
        complete: function() {
            $("#loading").addClass("hidden");
        }
    });

}

function save_diskresi() {

    if (!palid("dir-diskresi_harga", "", "Harga Harus Diisi")) return;
    if (!palid("dir-diskresi_harga", 0, "Harga Tidak boleh 0")) return;
    if (!palid("dir-diskresi_memo", "", "Memo harus diisi")) return;


    $.ajax({
        url: base_url + "direksi/save",
        type: "post",
        data: $("#fm-diskresi").serialize() + "&" + csrfName + "=" + csrfHash,

        dataType: "json",
        beforeSend: function() {
            simpanBtn("#btn-save_diskresi", true, "Menyimpan", "Menyimpan <i class='fa fa-spinner fa-spin'></i>")
        },
        success: function(r) {
            csrfHash = r.token;

            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: "success",
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500,
                })
                $(".modal").modal("hide");
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: "error",
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500,
                })
            }
            load_kavling();
            hapus_seleksi();
        },
        error: function(xhr, st, err) {
            return swal("error", err);
        },
        complete: function() {
            simpanBtn("#btn-save_diskresi", false, "Simpan", "Simpan")
        }
    });
}

/******************************** direksi ******************************************/

</script>