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
                $("#dir-pricelist_file").prop("href", base_url + hj.lokasi + hj.file_name)
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