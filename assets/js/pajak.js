
function open_pajak(sh, role, id_kavling) {
    sv_url = '/pajak/save'
    sv_fm = $('#fm-pajak')
    sv_btn = $('#add-form-btn-pajak')

    sv_fm[0].reset();

    if (sh.data.tipe != "kavling") {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Tidak ada kavling terpilih ",
            showConfirmButton: true,
            // timer: 1500
        })
        return;
    }
    if (!sh.data.id_mkdt) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }
    if (sh.data.status_mkdt == "Batal") {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Kavling dengan konsumen batal.",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }

    $.ajax({
        url: base_url + '/pajak/getOne',
        type: 'post',
        data: {
            id_mkdt: sh.data.id_mkdt,
            [csrfName]: csrfHash
        },
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        dataType: 'json',
        beforeSend: function() {
            $("#loading").removeClass("hidden");
        },
        success: function(r) {
            csrfHash = r.token;
            $("#loading").addClass("hidden");

            $("#fm-pajak #id_mkdt").val(sh.data.id_mkdt)

            for (let i in r) {
                $("#fm-pajak #" + i).val(r[i]);
            }

            if (r.pph42_tgl_bayar != "0000-00-00")
                document.querySelector("#fm-pajak #pph42_tgl_bayar")._flatpickr.setDate(r.pph42_tgl_bayar);
            if (r.ppn_tgl_bayar != "0000-00-00")
                document.querySelector("#fm-pajak #ppn_tgl_bayar")._flatpickr.setDate(r.ppn_tgl_bayar);
            if (r.ppn_tgl_bayar != "0000-00-00")
                document.querySelector("#fm-pajak #ppnjk_tgl_bayar")._flatpickr.setDate(r.ppnjk_tgl_bayar);

            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>" + "Konsumen: " + r.nama_konsumen);
            $('#modal_pajak').modal({
                backdrop: 'static',
                keyboard: false
            });
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_divisi' + role).modal({
                backdrop: 'static',
                keyboard: false
            });
        },
        error: function() {
            $("#loading").addClass("hidden");
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Oops!! Terjadi Kesalahan",
                showConfirmButton: false,
                // timer: 1500
            })
        }
    });
}
$("#add-form-btn-pajak").click(function(e) {
    e.preventDefault();
});

function save_() {
    $.ajax({
        url: base_url + sv_url,
        type: 'post',
        data: sv_fm.serialize() + "&" + csrfName + "=" + csrfHash + "&" + sv_par,
        dataType: 'json',
        beforeSend: function() {
            sv_btn.prop("disabled", true);
            sv_btn.html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
        },
        success: function(r) {
            csrfHash = r.token;
            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                })
                $('.modal').modal('hide');
                sv_btn.html('Simpan');
                sv_btn.prop("disabled", false);
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                })
                sv_btn.html('Simpan');
                sv_btn.prop("disabled", false);
            }
            sv_url = ''
            sv_fm = ''
            sv_btn = ''
            sv_par = ''
            load_kavling();
            hapus_seleksi();
        },
        error: function() {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Oops!! Terjadi Kesalahan",
                showConfirmButton: false,
                // timer: 1500
            })
            sv_btn.html('Simpan');
            sv_btn.prop("disabled", false);

            sv_url = ''
            sv_fm = ''
            sv_btn = ''
            sv_par = ''
        }
    });
}
