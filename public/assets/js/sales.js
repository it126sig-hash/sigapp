
function open_checklist_sales() {
    if (!editdtt[0]) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Pilih salahsatu kavling",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }


    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    $("#fm-checklist-sales")[0].reset();
    //reset keterangan last update
    $("#last_update_checklist_prod2").html('Terakhir diupdate oleh: -, pada: -');
    $("#last_update_checklist_sales").html('Terakhir diupdate oleh: -, pada: -');

    $(".id_kavling").val(id_kavling);
    $("#id_sales").val(sh.data.id_sales);


    $.ajax({
        url: base_url + '/sales/get_data_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_sales: sh.data.id_sales,
            id_kavling: id_kavling
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;
            let cl = r.cl;
            if (r.kav.is_checked == 1) {
                $("#is_checked").prop('checked', true);
            }
            if (cl.length > 0) {
                let lates_date = cl[0].produksi_cek_tgl,
                    lates_date_sales = cl[0].sales_cek_tgl;
                $.each(cl, function(key, val) {
                    //load form ceklst produksi
                    if (val.hasil_cek_t == 1)
                        $("#hasil_cek_t\\[" + val.id_subitem + "\\]").prop('checked', true);
                    if (val.hasil_cek_f == 1)
                        $("#hasil_cek_f\\[" + val.id_subitem + "\\]").prop('checked', true);
                    if (val.hasil_cek_v == 1)
                        $("#hasil_cek_v\\[" + val.id_subitem + "\\]").prop('checked', true);

                    // $("#hasil_cek_t\\[" + val.id_subitem + "\\]").val(val.hasil_cek_t);
                    // $("#hasil_cek_f\\[" + val.id_subitem + "\\]").val(val.hasil_cek_f);
                    // $("#hasil_cek_v\\[" + val.id_subitem + "\\]").val(val.hasil_cek_v);
                    $("#keterangan_cek_produksi\\[" + val.id_subitem + "\\]").val(val.keterangan_cek_produksi);

                    //load form ceklst sales
                    if (val.hasil_cek_t_s == 1)
                        $("#hasil_cek_t_s\\[" + val.id_subitem + "\\]").prop('checked', true);
                    if (val.hasil_cek_f_s == 1)
                        $("#hasil_cek_f_s\\[" + val.id_subitem + "\\]").prop('checked', true);
                    if (val.hasil_cek_v_s == 1)
                        $("#hasil_cek_v_s\\[" + val.id_subitem + "\\]").prop('checked', true);
                    // $("#hasil_cek_t_s\\[" + val.id_subitem + "\\]").val(val.hasil_cek_t_s);
                    // $("#hasil_cek_f_s\\[" + val.id_subitem + "\\]").val(val.hasil_cek_f_s);
                    // $("#hasil_cek_v_s\\[" + val.id_subitem + "\\]").val(val.hasil_cek_v_s);
                    $("#keterangan_cek_sales\\[" + val.id_subitem + "\\]").val(val.keterangan_cek_sales);

                    if (lates_date < val.produksi_cek_tgl)
                        lates_date = val.produksi_cek_tgl;
                    if (lates_date_sales < val.sales_cek_tgl)
                        lates_date_sales = val.sales_cek_tgl;
                })
                $("#last_update_checklist_prod2").html('Terakhir diupdate (produksi) oleh: ' + cl[0].username_prod + ', pada: ' + format_date(lates_date));
                $("#last_update_checklist_sales").html('Terakhir diupdate (sales) oleh: ' + cl[0].username_sales + ', pada: ' + format_date(lates_date_sales));
            }
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#checklist_modal_sales').modal({
                backdrop: 'static',
                keyboard: false
            });

        },
        error: function() {},

    });
}

function save_checklist_sales() {
    $.ajax({
        url: base_url + '/sales/save_checklist',
        type: 'post',
        data: $("#fm-checklist-sales").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: 'json',
        beforeSend: function() {
            $('#checklist-form-btn-sales').prop('disabled', true);
            $('#checklist-form-btn-sales').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
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
                }).then(function() {
                    $('.modal').modal('hide');
                    $('#checklist-form-btn-sales').html('Simpan');
                    $('#checklist-form-btn-sales').prop('disabled', false);
                })
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    $('#checklist-form-btn-sales').html('Simpan');
                    $('#checklist-form-btn-sales').prop('disabled', false);
                })
            }
            load_kavling();
            hapus_seleksi();
        }
    });
}


//menampilkan list file yang akan diupload
let fuks = document.getElementById("upload_komplain_sales"),
    fluks = [],
    flduks = "";

fuks.addEventListener("change", function(e) {
    fluks = [];
    flduks = "";
    $("#list_upload_komplain_sales").html();
    for (let p = 0; p < fuks.files.length; p++) {
        fluks.push(fuks.files[p]);

        flduks += "<p>" + (p + 1) + ": " + fuks.files[p].name + " </p>";
        $("#list_upload_komplain_sales").html(flduks);
    }

});

function open_komplain_sales() {
    if (!editdtt[0]) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Pilih salahsatu kavling",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }
    $("#fm-komplain-sales #foto_komplain_sales").html("")
    $("#list_upload_komplain_sales").html("");
    $("#label_upload_komplain_sales").html("Bisa Upload lebih dari 1 foto");


    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    $("#fm-komplain-sales")[0].reset();

    $("#last_update_komplain_sales").html('Terakhir diupdate oleh: -, pada: -');

    $("#batal_komplain_btn").addClass("btn-outline-danger")
    $("#batal_komplain_btn").removeClass("btn-outline-primary")
    $("#batal_komplain_btn").html("Batalkan Komplain")

    $("#batal_komplain, #selesaikan_komplain_div_sales, #komplain_ditangani_sales").addClass('hidden', true);
    $("#batal_komplain_btn, #komplain-sales-form-btn, #fm-komplain-sales #keterangan_komplain, #fm-komplain-sales #selesai_keterangan_sales").prop('disabled', false);

    $(".id_kavling").val(id_kavling);
    $("#fm-komplain-sales #id_komplain").val(sh.data2.id_komplain);


    $.ajax({
        url: base_url + '/sales/get_data_komplain_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_komplain: sh.data2.id_komplain,
            id_kavling: id_kavling
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;
            let st = r.komplain,
                fotok, fotokp,
                fotok_display = "",
                fotokp_display = "";




            if (st) {

                fotok = st.upload_komplain_sales;
                if (fotok)
                    fotok = fotok.split(";")

                if (Array.isArray(fotok)) {

                    let is_active = "active";
                    for (let e = 0; e < (fotok.length - 1); e++) {
                        if (e > 0)
                            is_active = "";

                        fotok_display += '<div class="carousel-item ' + is_active + '">' +
                            '<img class="d-block w-100 ft_kom" src="' + base_url + "/" + fotok[e] + '" alt="First slide">' +
                            '</div>';
                    }
                }
                $("#fm-komplain-sales #foto_komplain_sales").html(fotok_display);

                //display foto penyelsaian dari produksi
                fotokp = st.upload_komplain_produksi;
                if (fotokp)
                    fotokp = fotokp.split(";")

                if (Array.isArray(fotokp)) {

                    let is_active = "active";
                    for (let e = 0; e < (fotokp.length - 1); e++) {
                        if (e > 0)
                            is_active = "";

                        fotokp_display += '<div class="carousel-item ' + is_active + '">' +
                            '<img class="d-block w-100 ft_kom" src="' + base_url + "/" + fotokp[e] + '" alt="First slide">' +
                            '</div>';
                    }
                }
                $("#fm-komplain-sales #foto_komplain_produksi").html(fotokp_display);


                if (st.status_komplain == 1) {
                    $("#batal_komplain").removeClass('hidden', true);
                } else if (st.status_komplain == 2) {
                    $("#batal_komplain_btn, #komplain-sales-form-btn, #fm-komplain-sales #keterangan_komplain").prop('disabled', true);
                    $("#batal_komplain").removeClass('hidden', true);
                    $("#komplain_ditangani_sales").removeClass('hidden', true);


                    $("#fm-komplain-sales #keterangan_ditangani").val(st.keterangan_ditangani);
                    $("#fm-komplain-sales #username_ditangani_oleh").val(st.username_ditangani_oleh);

                    if (st.ditangani_tgl != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #ditangani_tgl")._flatpickr.setDate(st.ditangani_tgl);

                } else if (st.status_komplain == 3) {
                    $("#batal_komplain_btn, #fm-komplain-sales #keterangan_komplain").prop('disabled', true);
                    $("#batal_komplain, #selesaikan_komplain_div_sales, #komplain_ditangani_sales").removeClass('hidden', true);

                    $("#fm-komplain-sales #keterangan_ditangani").val(st.keterangan_ditangani);
                    $("#fm-komplain-sales #username_ditangani_oleh").val(st.username_ditangani_oleh);

                    if (st.ditangani_tgl != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #ditangani_tgl")._flatpickr.setDate(st.ditangani_tgl);

                    $("#fm-komplain-sales #selesai_keterangan_produksi").val(st.selesai_keterangan_produksi);
                    $("#fm-komplain-sales #username_selesai_oleh_produksi").val(st.username_selesai_oleh_produksi);

                    if (st.selesai_tgl_produksi != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #selesai_tgl_produksi")._flatpickr.setDate(st.selesai_tgl_produksi);

                } else if (st.status_komplain == 4) {
                    $("#batal_komplain_btn, #komplain-sales-form-btn, #fm-komplain-sales #keterangan_komplain, #fm-komplain-sales #selesai_keterangan_sales").prop('disabled', true);
                    $("#batal_komplain, #selesaikan_komplain_div_sales, #komplain_ditangani_sales").removeClass('hidden', true);

                    $("#batal_komplain_btn").html("Komplain Selesai")
                    $("#batal_komplain_btn").removeClass("btn-outline-danger")
                    $("#batal_komplain_btn").addClass("btn-outline-primary")

                    $("#fm-komplain-sales #keterangan_ditangani").val(st.keterangan_ditangani);
                    $("#fm-komplain-sales #username_ditangani_oleh").val(st.username_ditangani_oleh);

                    if (st.ditangani_tgl != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #ditangani_tgl")._flatpickr.setDate(st.ditangani_tgl);

                    $("#fm-komplain-sales #selesai_keterangan_produksi").val(st.selesai_keterangan_produksi);
                    $("#fm-komplain-sales #username_selesai_oleh_produksi").val(st.username_selesai_oleh_produksi);

                    if (st.selesai_tgl_produksi != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #selesai_tgl_produksi")._flatpickr.setDate(st.selesai_tgl_produksi);

                    $("#fm-komplain-sales #selesai_keterangan_sales").val(st.selesai_keterangan_sales);
                    $("#fm-komplain-sales #username_selesai_oleh_sales").val(st.username_selesai_oleh_sales);

                    if (st.selesai_tgl_produksi != "0000-00-00")
                        document.querySelector("#fm-komplain-sales #selesai_tgl_sales")._flatpickr.setDate(st.selesai_tgl_sales);

                }


                $("#fm-komplain-sales #keterangan_komplain").val(st.keterangan_komplain);
                $("#fm-komplain-sales #username_komplain_oleh").val(st.username_komplain_oleh);

                if (st.komplain_tgl != "0000-00-00")
                    document.querySelector("#fm-komplain-sales #komplain_tgl")._flatpickr.setDate(st.komplain_tgl);

                $("#last_update_komplain_sales").html('Terakhir diupdate oleh: ' + st.username_last_update + ', pada: ' + format_datetime(st.updated_at));
            }
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_komplain_sales').modal({
                backdrop: 'static',
                keyboard: false
            });

        },
        error: function() {},

    });
}

function save_komplain_sales() {
    var files = $('#upload_komplain_sales')[0].files;
    var form = $('#fm-komplain-sales')[0];
    var fd = new FormData(form);
    fd.append(csrfName, csrfHash);

    $.ajax({
        url: base_url + '/sales/save_komplain_sales',
        type: 'POST',
        contentType: false,
        processData: false,
        // data: $("#fm-komplain-sales").serialize() + "&" + csrfName + "=" + csrfHash,
        data: fd,
        dataType: 'json',
        beforeSend: function() {
            $('#komplain-sales-form-btn').prop('disabled', true);
            $('#komplain-sales-form-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
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
                hapus_seleksi();
                load_kavling();
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                })
            }
            $('#komplain-sales-form-btn').html('Simpan');
            $('#komplain-sales-form-btn').prop('disabled', false);
        }
    });
}

function batal_komplain() {
    Swal.fire({
        title: 'Apakah anda yakin akan membatalkan komplain?',
        text: "Data komplain akan terhapus dan tidak bisa dikembalikan",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if (result.value) {
            $.ajax({
                url: base_url + '/sales/batalkan_komplain',
                type: 'post',
                data: {
                    [csrfName]: csrfHash,
                    id_kavling: $(".id_kavling").val(),
                    id_komplain: $("#id_komplain").val(),
                },
                dataType: 'json',
                success: function(response) {
                    csrfHash = response.token;
                    if (response.success === true) {
                        Swal.fire({
                            //position: 'bottom-end',
                            icon: 'success',
                            title: response.messages,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            load_kavling();
                            hapus_seleksi();
                            $(".modal").modal('hide')
                        })
                    } else {
                        Swal.fire({
                            //position: 'bottom-end',
                            icon: 'error',
                            title: response.messages,
                            showConfirmButton: false,
                            timer: 1500
                        })
                    }
                }
            });
        }
    })
}

function open_serah_terima() {
    if (!editdtt[0]) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Pilih salahsatu kavling",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }


    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    $("#fm-serah-terima")[0].reset();

    $("#last_update_serah_terima").html('Terakhir diupdate oleh: -, pada: -');

    $(".id_kavling").val(id_kavling);
    $("#is_serah_terima").val(sh.data2.id_serah_terima);


    $.ajax({
        url: base_url + '/sales/get_data_serah_terima_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            is_serah_terima: sh.data2.is_serah_terima,
            id_kavling: id_kavling
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;
            let st = r.serah_terima;

            if (st) {
                if (st.is_serah_terima == 1)
                    $("#is_serah_terima").prop('checked', true);

                // let lates_date = cl[0].produksi_cek_tgl,
                //     lates_date_sales = cl[0].sales_cek_tgl;
                for (let i in st) {
                    if (i != 'is_serah_terima')
                        $("#fm-serah-terima #" + i).val(st[i]);
                }
                if (st.serah_terima_tgl != "0000-00-00")
                    document.querySelector("#serah_terima_tgl")._flatpickr.setDate(st.serah_terima_tgl);

                $("#fm-serah-terima #is_serah_terima").val(1);
                // $("#last_update_checklist_prod2").html('Terakhir diupdate (produksi) oleh: ' + cl[0].username_prod + ', pada: ' + format_date(lates_date));
                $("#last_update_serah_terima").html('Terakhir diupdate oleh: ' + st.username + ', pada: ' + format_date(st.updated_at));
            }
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_serah_terima').modal({
                backdrop: 'static',
                keyboard: false
            });

        },
        error: function() {},

    });
}

function save_serah_terima() {
    $.ajax({
        url: base_url + '/sales/save_serah_terima',
        type: 'post',
        data: $("#fm-serah-terima").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: 'json',
        beforeSend: function() {
            $('#serah-terima-form-btn').prop('disabled', true);
            $('#serah-terima-form-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
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
                hapus_seleksi();
                load_kavling();
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                })
            }
            $('#serah-terima-form-btn').html('Simpan');
            $('#serah-terima-form-btn').prop('disabled', false);
        }
    });
}
