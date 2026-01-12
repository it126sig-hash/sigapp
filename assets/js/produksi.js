function updateState(elementId, variableName) {
    $(elementId).change(function() {
        window[variableName] = this.checked ? 1 : 0;
    });
}
/******************************** produksi ******************************************/
//cekbok produksi
var slo = 0,
    bp = 0,
    lpa = 0,
    tot = 0,
    st_0 = 0,
    st_25 = 0,
    st_50 = 0,
    st_75 = 0,
    st_100 = 0,
    st_saluran = 0,
    st_jalan = 0,
    st_air = 0

updateState("#bp", "bp");
updateState("#slo", "slo");
updateState("#lpa", "lpa");
updateState("#st_0", "st_0");
updateState("#st_25", "st_25");
updateState("#st_50", "st_50");
updateState("#st_75", "st_75");
updateState("#st_100", "st_100");
updateState("#st_saluran", "st_saluran");
updateState("#st_100", "st_100");
updateState("#st_100", "st_100");

$(".cbp").change(function() {
    ftot();
    cekstprod();
    tot = (tot / 9) * 100;

    // $("#progres_bangunan").val(tot.toFixed(2))
    // $("#t_progres_bangunan").html(tot.toFixed(2))
});


$("#listrik_jenis").change(function() {
    if (this.value == "PLN") {
        $("#listrik-pln-input-form").removeClass("hidden");
        $("#listrik_disediakan").addClass("hidden");
    } else {
        $("#listrik-pln-input-form").addClass("hidden");
        $("#listrik_disediakan").removeClass("hidden");
    }
});
$("#air_jenis").change(function() {
    if (this.value == "Air Tanah") {
        $("#air_tanah-input_form").removeClass("hidden");
        $("#air_komunal-input_form").addClass("hidden");
        $("#air_pdam-input_form").addClass("hidden");
    } else if (this.value == "Komunal Warga") {
        $("#air_tanah-input_form").addClass("hidden");
        $("#air_komunal-input_form").removeClass("hidden");
        $("#air_pdam-input_form").addClass("hidden");
    } else {
        $("#air_tanah-input_form").addClass("hidden");
        $("#air_komunal-input_form").addClass("hidden");
        $("#air_pdam-input_form").removeClass("hidden");
    }
});

$("#progres_bangunan").on("input", function() {
    $("#t_progres_bangunan").html($(this).val());
});



function ftot() {
    // return (tot =
    //   slo +
    //   bp +
    //   jalan +
    //   lpa +
    //   saluran +
    //   pondasi +
    //   topping_off +
    //   naik_dinding +
    //   finishing);
}

function cekstprod() {
    // if (
    //   $("#pondasi").prop("checked") &&
    //   $("#naik_dinding").prop("checked") &&
    //   $("#topping_off").prop("checked") &&
    //   $("#finishing").prop("checked") &&
    //   $("#saluran").prop("checked") &&
    //   $("#jalan").prop("checked")
    // )
    //   $(".af .cbp").prop("disabled", false);
    // else $(".af .cbp").prop("disabled", true);
}

function save_produksi() {
    if ($("#tanggal_pembangunan").val() == '') {
        $(".tanggal_pembangunan").addClass('is-invalid');
        return swal('error', "Tanggal pembangunan harus diisi")
    }
    $(".tanggal_pembangunan").removeClass('is-invalid');

    if ($("#tanggal_rencana_selesai_pembangunan").val() == '') {
        $(".tanggal_rencana_selesai_pembangunan").addClass('is-invalid');
        return swal('error', "Tanggal rencana selesai pembangunan harus diisi")
    }
    $(".tanggal_rencana_selesai_pembangunan").removeClass('is-invalid');

    let form = $("#fm-produksi")[0];
    let fd = new FormData(form);
    fd.append(csrfName, csrfHash);

    $.ajax({
        url: base_url + "produksi/save",
        type: "post",
        contentType: false,
        processData: false,
        data: fd,
        dataType: "json",
        beforeSend: function() {
            simpanBtn("#add-form-btn-produksi", true);
        },
        success: function(r) {
            csrfHash = r.token;
            // $('#add-form-btn-produksi').prop('disabled', false);
            // return;
            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: "success",
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500,
                }).then(function() {
                    $(".modal").modal("hide");
                    simpanBtn("#add-form-btn-produksi", false);
                });
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: "error",
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500,
                }).then(function() {
                    simpanBtn("#add-form-btn-produksi", false);
                });
            }
            load_kavling();
            hapus_seleksi();
        },
        error: function(xhr, st, err) {
            simpanBtn("#add-form-btn-produksi", false);
            return swal("error", err);
        },
    });
}
$("#terima_komplain").change(function() {
    if (this.checked) {
        $("#terima_komplain_div").removeClass("hidden");
    } else {
        $("#terima_komplain_div").addClass("hidden", true);
    }
});

function open_komplain_produksi() {
    if (!editdtt[0]) {
        Swal.fire({
            //position: 'bottom-end',
            icon: "error",
            title: "Pilih salahsatu kavling",
            showConfirmButton: false,
            timer: 1500,
        });
        return;
    }

    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    if (!sh.data2.id_komplain) {
        Swal.fire({
            //position: 'bottom-end',
            icon: "error",
            title: "Tidak ada komplain",
            showConfirmButton: false,
            timer: 1500,
        });
        return;
    }

    $("#fm-komplain-produksi")[0].reset();

    $(
        "#fm-komplain-produksi #foto_komplain_sales, #fm-komplain-produksi #foto_komplain_produksi"
    ).html("");

    $(
        ".ditangani_form, #selesaikan_komplain_div, #komplain_selesai_btn_produksi"
    ).addClass("hidden", true);
    $("#keterangan_ditangani").prop("readonly", false);
    $("#komplain-produksi-form-btn").prop("disabled", false);

    $("#terima_komplain, #is_selesai_produksi").attr("onclick", "");
    $("#fm-komplain-produksi #keterangan_ditangani").prop("disabled", false);
    $("#fm-komplain-produksi #selesai_keterangan_produksi").prop(
        "disabled",
        false
    );

    $("#komplain_selesai_sip").addClass("hidden");

    $("#last_update_komplain_produksi").html(
        "Terakhir diupdate oleh: -, pada: -"
    );

    $(".id_kavling").val(id_kavling);
    $("#fm-komplain-produksi #id_komplain").val(sh.data2.id_komplain);

    $.ajax({
        url: base_url + "produksi/get_data_komplain_by_id",
        type: "post",
        data: {
            [csrfName]: csrfHash,
            id_komplain: sh.data2.id_komplain,
            id_kavling: id_kavling,
        },
        dataType: "json",
        success: function(r) {
            csrfHash = r.token;
            let st = r.komplain,
                fotok,
                fotok_display = "",
                fotokp,
                fotokp_display = "";

            if (st) {
                //display foto komplain dari sales
                fotok = st.upload_komplain_sales;

                // console.log(fotok)
                if (fotok) fotok = fotok.split(";");

                if (Array.isArray(fotok)) {
                    let is_active = "active";
                    for (let e = 0; e < fotok.length - 1; e++) {
                        if (e > 0) is_active = "";

                        fotok_display +=
                            '<div class="carousel-item ' +
                            is_active +
                            '">' +
                            '<img class="d-block w-100 ft_kom" src="' +
                            base_url +
                            "/" +
                            fotok[e] +
                            '" alt="First slide">' +
                            "</div>";
                    }
                }
                $("#fm-komplain-produksi #foto_komplain_sales").html(fotok_display);

                //display foto penyelsaian dari produksi
                fotokp = st.upload_komplain_produksi;
                if (fotokp) fotokp = fotokp.split(";");

                if (Array.isArray(fotokp)) {
                    let is_active = "active";
                    for (let e = 0; e < fotokp.length - 1; e++) {
                        if (e > 0) is_active = "";

                        fotokp_display +=
                            '<div class="carousel-item ' +
                            is_active +
                            '">' +
                            '<img class="d-block w-100 ft_kom" src="' +
                            base_url +
                            "/" +
                            fotokp[e] +
                            '" alt="First slide">' +
                            "</div>";
                    }
                }
                $("#fm-komplain-produksi #foto_komplain_produksi").html(fotokp_display);

                //komplain
                $("#fm-komplain-produksi #keterangan_komplain").val(
                    st.keterangan_komplain
                );
                $("#fm-komplain-produksi #username_komplain_oleh").val(
                    st.username_komplain_oleh
                );

                if (st.komplain_tgl != "0000-00-00")
                    document
                    .querySelector("#fm-komplain-produksi #komplain_tgl")
                    ._flatpickr.setDate(st.komplain_tgl);

                $("#last_update_komplain_produksi").html(
                    "Terakhir diupdate oleh: " +
                    st.username_last_update +
                    ", pada: " +
                    format_datetime(st.updated_at)
                );

                //ditangani
                if (st.status_komplain == 2) {
                    $("#terima_komplain").attr("onclick", "return false;");
                    $("#terima_komplain").prop("checked", true);

                    $(".ditangani_form, #selesaikan_komplain_div").removeClass("hidden");

                    $("#fm-komplain-produksi #keterangan_ditangani").val(
                        st.keterangan_ditangani
                    );
                    $("#fm-komplain-produksi #username_ditangani_oleh").val(
                        st.username_ditangani_oleh
                    );

                    if (st.ditangani_tgl != "0000-00-00")
                        document
                        .querySelector("#fm-komplain-produksi #ditangani_tgl")
                        ._flatpickr.setDate(st.ditangani_tgl);
                } else if (st.status_komplain == 3) {
                    $("#terima_komplain").attr("onclick", "return false;");
                    $("#terima_komplain").prop("checked", true);

                    $("#is_selesai_produksi").attr("onclick", "return false;");
                    $("#is_selesai_produksi").prop("checked", true);

                    $("#keterangan_ditangani").prop("readonly", true);

                    $(".ditangani_form, #selesaikan_komplain_div").removeClass("hidden");

                    $("#fm-komplain-produksi #keterangan_ditangani").val(
                        st.keterangan_ditangani
                    );
                    $("#fm-komplain-produksi #username_ditangani_oleh").val(
                        st.username_ditangani_oleh
                    );

                    if (st.ditangani_tgl != "0000-00-00")
                        document
                        .querySelector("#fm-komplain-produksi #ditangani_tgl")
                        ._flatpickr.setDate(st.ditangani_tgl);

                    $("#fm-komplain-produksi #selesai_keterangan_produksi").val(
                        st.selesai_keterangan_produksi
                    );
                    $("#fm-komplain-produksi #username_selesai_oleh_produksi").val(
                        st.username_selesai_oleh_produksi
                    );

                    if (st.selesai_tgl_produksi != "0000-00-00")
                        document
                        .querySelector("#fm-komplain-produksi #selesai_tgl_produksi")
                        ._flatpickr.setDate(st.selesai_tgl_produksi);
                } else if (st.status_komplain == 4) {
                    $("#terima_komplain").attr("onclick", "return false;");
                    $("#terima_komplain").prop("checked", true);

                    $("#is_selesai_produksi").attr("onclick", "return false;");
                    $("#is_selesai_produksi").prop("checked", true);

                    $("#komplain-produksi-form-btn").prop("disabled", true);

                    $("#keterangan_ditangani").prop("readonly", true);

                    $("#fm-komplain-produksi #selesai_keterangan_produksi").prop(
                        "disabled",
                        true
                    );

                    $("#fm-komplain-produksi #keterangan_ditangani").prop(
                        "disabled",
                        true
                    );

                    $(
                        ".ditangani_form, #selesaikan_komplain_div, #komplain_selesai_btn_produksi"
                    ).removeClass("hidden");

                    $("#fm-komplain-produksi #keterangan_ditangani").val(
                        st.keterangan_ditangani
                    );
                    $("#fm-komplain-produksi #username_ditangani_oleh").val(
                        st.username_ditangani_oleh
                    );

                    if (st.ditangani_tgl != "0000-00-00")
                        document
                        .querySelector("#fm-komplain-produksi #ditangani_tgl")
                        ._flatpickr.setDate(st.ditangani_tgl);

                    $("#fm-komplain-produksi #selesai_keterangan_produksi").val(
                        st.selesai_keterangan_produksi
                    );
                    $("#fm-komplain-produksi #username_selesai_oleh_produksi").val(
                        st.username_selesai_oleh_produksi
                    );

                    if (st.selesai_tgl_produksi != "0000-00-00")
                        document
                        .querySelector("#fm-komplain-produksi #selesai_tgl_produksi")
                        ._flatpickr.setDate(st.selesai_tgl_produksi);

                    $("#komplain_selesai_sip").removeClass("hidden");

                    $("#fm-komplain-produksi #selesai_keterangan_sales").val(
                        st.selesai_keterangan_sales
                    );
                    $("#fm-komplain-produksi #username_selesai_oleh_sales").val(
                        st.username_selesai_oleh_sales
                    );

                    if (st.selesai_tgl_sales != "0000-00-00")
                        document
                        .querySelector("#fm-komplain-produksi #selesai_tgl_sales")
                        ._flatpickr.setDate(st.selesai_tgl_sales);
                }
            }
            $(".label_alamat").html(
                dt_proyek.nama_proyek +
                "<br/>" +
                sh.data.nama_jalan +
                ", No." +
                sh.data.no_kavling +
                "<br/>" +
                sh.data2.no_tipe_rumah +
                " (" +
                sh.data2.tipe_rumah +
                ")<br/>"
            );
            $("#modal_komplain_produksi").modal({
                backdrop: "static",
                keyboard: false,
            });
        },
        error: function() {},
    });
}

function save_komplain_produksi() {
    var files = $("#upload_komplain_sales")[0].files;
    var form = $("#fm-komplain-produksi")[0];
    var fd = new FormData(form);
    fd.append(csrfName, csrfHash);

    $.ajax({
        url: base_url + "produksi/save_komplain_produksi",
        type: "POST",
        contentType: false,
        processData: false,
        // data: $("#fm-komplain-sales").serialize() + "&" + csrfName + "=" + csrfHash,
        data: fd,
        dataType: "json",
        beforeSend: function() {
            $("#komplain-produksi-form-btn").prop("disabled", true);
            $("#komplain-produksi-form-btn").html(
                'Menyimpan <i class="fa fa-spinner fa-spin"></i>'
            );
        },
        success: function(r) {
            csrfHash = r.token;

            if (r.success === true) {
                swal('success', r.messages)
                    // Swal.fire({
                    //   //position: 'bottom-end',
                    //   icon: "success",
                    //   title: r.messages,
                    //   showConfirmButton: false,
                    //   timer: 1500,
                    // });

                $(".modal").modal("hide");
                hapus_seleksi();
                load_kavling();
            } else {
                swal('error', r.messages)
            }
            $("#komplain-produksi-form-btn").html("Simpan");
            $("#komplain-produksi-form-btn").prop("disabled", false);
        },
    });
}
//open form add/edit
function open_produksi(sh, role, id_kavling) {
    if (editdtt.length > 1) {
        swal('error', 'Tidak bisa mengisi data lebih dari 1 secara bersamaan')
    }
    if (sh.data.tipe == "kavling") {
        return open_fproduksi(sh, role, id_kavling);
    } else {
        return open_fotherproduksi(sh);
    }
}

function save_fotherproduksi() {
    $.ajax({
        url: base_url + "produksi/edit_others",
        type: "POST",
        // data: $("#fm-komplain-sales").serialize() + "&" + csrfName + "=" + csrfHash,
        data: $("#fm-fotherproduksi").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: "json",
        beforeSend: function() {
            $("#save_fotherproduksi-btn").prop("disabled", true);
            $("#save_fotherproduksi-btn").html(
                'Menyimpan <i class="fa fa-spinner fa-spin"></i>'
            );
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
                });

                $(".modal").modal("hide");
                hapus_seleksi();
                load_kavling();
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: "error",
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500,
                });
            }
            $("#save_fotherproduksi-btn").html("Simpan");
            $("#save_fotherproduksi-btn").prop("disabled", false);
        },
        error: function() {
            Swal.fire({
                //position: 'bottom-end',
                icon: "error",
                title: "Terjadi kesalahan saat menginput data",
                showConfirmButton: false,
                timer: 1500,
            });
            $("#save_fotherproduksi-btn").html("Simpan");
            $("#save_fotherproduksi-btn").prop("disabled", false);
        },
    });
}
$("#save_fotherproduksi-btn").click(function(e) {
    e.preventDefault();
});

function open_fotherproduksi(sh) {
    $("#fm-fotherproduksi")[0].reset();
    $("#f_progres_jalan").val(0);
    $(".t_luas_legal, .t_luas_produksi, .r_progres").html(" ");
    $.ajax({
        url: base_url + "siteplan/get_others",
        type: "post",
        data: {
            [csrfName]: csrfHash,
            id_kavling: editdtt[0].id.substr(6),
        },
        dataType: "json",
        success: function(r) {
            csrfHash = r.token;

            if (r.data) {
                let d = r.data[0],
                    progres = d.progres ? d.progres : 0;
                $(".id_kavling").val(d.id);
                $(".t_luas_legal, .t_luas_produksi").html("-");

                if (d.planning_luas)
                    $(".t_luas_planning").html(
                        d.planning_luas +
                        "  m&sup2  (" +
                        d.planning_edit +
                        ": " +
                        format_datetime(d.planning_updated_at) +
                        ")"
                    );
                if (d.legal_luas)
                    $(".t_luas_legal").html(
                        d.legal_luas +
                        "  m&sup2  (" +
                        d.legal_edit +
                        ": " +
                        format_datetime(d.legal_updated_at) +
                        ")"
                    );

                $("#f_produksi_luas").val(d.produksi_luas);
                $("#f_produksi_keterangan").val(d.produksi_keterangan);
                $("#f_progres_jalan").val(progres);
                $(".r_progres").html(progres);
            }
        },
        error: function() {
            Swal.fire({
                //position: 'bottom-end',
                icon: "error",
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
                timer: 1500,
            });
            return;
        },
    });

    $(".label_alamat").html(
        dt_proyek.nama_proyek +
        "<br/> <span class='capitalize'>" +
        sh.data.tipe +
        "<span>: " +
        sh.data.nama_jalan +
        ""
    );
    $("#modal_fothersproduksi").modal({
        backdrop: "static",
        keyboard: false,
    });
}


function open_fproduksi(sh, role, id_kavling) {
    let update_tanggal_pembangunan = has_akses.update_tanggal_pembangunan ? true : false;
    $(".tgl_bangun").prop('disabled', !update_tanggal_pembangunan)

    $("#fm-prod-progress-tab").click();
    (st_0 = 0),
    (st_25 = 0),
    (st_50 = 0),
    (st_75 = 0),
    (st_100 = 0),
    (st_saluran = 0),
    (st_air = 0),
    (st_jalan = 0),
    (bp = 0),
    (lpa = 0),
    (tot = 0);

    let categories = [
        "rab_dokumen",
        "prod_foto_konstruksi",
        "prod_foto_exterior",
        "prod_foto_interior",
        "jalan_foto",
        "jalan_foto_update",
        "listrik_pln_foto",
        "listrik_disediakan_dokumen",
        "air_komunal",
        "air_tanah",
        "air_pdam",
    ];

    categories.forEach((cat) => {
        $("#list_" + cat).html("");
        $("#label_" + cat).html("Upload file/Foto");
    });

    $(".af .cbp").prop("disabled", true);

    $("#t_progres_bangunan").html("0");
    $("#fm-produksi")[0].reset();
    $("#last_update_checklist_prod").html("Terakhir diupdate oleh: -, pada: -");

    $(".id_kavling").val(id_kavling);
    $("#id_produksi").val(sh.data.id_produksi);

    $("#download_gambar_kerja").click(function() {
        simpanBtn('#download_gambar_kerja', true, 'Mengunduh <i class="fa fa-spinner fa-spin"></i>', 'Unduh Gambar Kerja')
        download(sh.data2.id_gambar_kerja, () => {
            simpanBtn('#download_gambar_kerja', false, 'Mengunduh <i class="fa fa-spinner fa-spin"></i>', 'Unduh Gambar Kerja')
        })
    });

    $.ajax({
        url: base_url + "produksi/get_data_by_id",
        type: "post",
        data: {
            [csrfName]: csrfHash,
            id_produksi: sh.data.id_produksi,
            id_kavling: id_kavling,
        },
        dataType: "json",
        success: function(r) {
            csrfHash = r.token;
            let cl = r.cl,
                pb = 0;
            if (r) {
                if (r.progres_bangunan) {
                    $("#progres_bangunan").val(r.progres_bangunan);
                    $("#t_progres_bangunan").html(r.progres_bangunan);
                }

                changeVal("#air_jenis", r.air_jenis);
                changeVal("#listrik_jenis", r.listrik_jenis);

                changeVal("#listrik_pln", r.listrik_pln);
                changeVal("#listrik_disediakan_no", r.listrik_disediakan_no);
                changeVal("#listrik_disediakan_tanggal", r.listrik_disediakan_tanggal);
                changeVal("#air_deskripsi_unit", r.air_deskripsi_unit);
                changeVal("#air_pdam_no", r.air_pdam_no);
                // changeVal("#lpa_tanggal", r.lpa_tanggal);

                setDatePicker(r.lpa_tanggal, '#lpa_tanggal')

                setDatePicker(r.tanggal_pembangunan, '#tanggal_pembangunan')
                setDatePicker(r.tanggal_rencana_selesai_pembangunan, '#tanggal_rencana_selesai_pembangunan')
                setDatePicker(r.tanggal_selesai_pembangunan, '#tanggal_selesai_pembangunan')

                if (!r.tanggal_pembangunan) {
                    $(".tgl_bangun").prop('disabled', false)
                }

                $('#tanggal_pembangunan_old').val(r.tanggal_pembangunan)
                $('#tanggal_rencana_selesai_pembangunan_old').val(r.tanggal_rencana_selesai_pembangunan)
                $('#tanggal_selesai_pembangunan_old').val(r.tanggal_selesai_pembangunan)


                $("#lu-tanggal_pembangunan").html(
                    `Diinput pada: 
            ${r.tanggal_pembangunan_pada ? format_datetime(r.tanggal_pembangunan_pada):'-'}, 
            oleh: ${r.tanggal_pembangunan_oleh ? r.tanggal_pembangunan_oleh_u:'-'}`
                )
                $("#lu-tanggal_rencana_selesai_pembangunan").html(
                    `Diubah pada: 
            ${r.tanggal_pembangunan_diubah_pada ? format_datetime(r.tanggal_pembangunan_diubah_pada):'-'}, 
            oleh: ${r.tanggal_pembangunan_diubah_oleh ? r.tanggal_pembangunan_diubah_oleh_u:'-'}`
                )
                $("#lu-tanggal_selesai_pembangunan").html(
                    `Diinput pada: 
            ${r.tanggal_selesai_pembangunan_diubah_pada ? format_datetime(r.tanggal_selesai_pembangunan_diubah_pada):'-'}, 
            oleh: ${r.tanggal_selesai_pembangunan_diubah_oleh ? r.tanggal_selesai_pembangunan_diubah_oleh_u:'-'}`
                )


                changeVal('#tanggal_pembangunan_oleh', r.tanggal_pembangunan_oleh)
                changeVal('#tanggal_pembangunan_diubah_oleh', r.tanggal_pembangunan_diubah_oleh)
                changeVal('#tanggal_selesai_pembangunan_oleh', r.tanggal_selesai_pembangunan_oleh)
                changeVal('#tanggal_selesai_pembangunan_diubah_oleh', r.tanggal_selesai_pembangunan_diubah_oleh)

                setDatePicker(r.tanggal_pembangunan_pada, '#tanggal_pembangunan_pada')
                setDatePicker(r.tanggal_pembangunan_diubah_pada, '#tanggal_pembangunan_diubah_pada')
                setDatePicker(r.tanggal_selesai_pembangunan_pada, '#tanggal_selesai_pembangunan_pada')
                setDatePicker(r.tanggal_selesai_pembangunan_diubah_pada, '#tanggal_selesai_pembangunan_diubah_pada')


                changeVal("#sumurbor_keterangan", r.sumurbor_keterangan);
                setDatePicker(r.sumurbor_tanggal, '#sumurbor_tanggal')
                $("#last_update-sumurbor").html(
                    `Diubah pada: ${r.sumurbor_updated ? format_datetime(r.sumurbor_updated):'-'}, 
                    oleh: ${r.sumurbor_oleh_u ? r.sumurbor_oleh_u:'-'}`
                )

                const fields = [
                    { id: "st_0", value: r.st_0 },
                    { id: "st_25", value: r.st_25 },
                    { id: "st_50", value: r.st_50 },
                    { id: "st_75", value: r.st_75 },
                    { id: "st_100", value: r.st_100 },
                    { id: "st_saluran", value: r.st_saluran },
                    { id: "st_air", value: r.st_air },
                    { id: "st_jalan", value: r.st_jalan },
                    { id: "bp", value: r.bp },
                    { id: "lpa", value: r.lpa },
                    { id: "slo", value: r.slo },
                    { id: "sumurbor", value: r.sumurbor },
                ];

                fields.forEach((field) => {
                    $("#" + field.id)
                        .prop("checked", field.value == 1)
                        .change();
                });

                if (cl && cl.length > 0) {
                    let lates_date = cl[0].produksi_cek_tgl;
                    cl.forEach((val) => {
                        ["t", "f", "v"].forEach((type) => {
                            if (val["hasil_cek_" + type] == 1) {
                                $("#hasil_cek_" + type + "\\[" + val.id_subitem + "\\]").prop(
                                    "checked",
                                    true
                                );
                            }
                        });

                        $("#keterangan_cek_produksi\\[" + val.id_subitem + "\\]").val(
                            val.keterangan_cek_produksi
                        );

                        if (lates_date < val.produksi_cek_tgl)
                            lates_date = val.produksi_cek_tgl;
                    });
                    $("#last_update_checklist_prod").html(
                        "Terakhir diupdate oleh: " +
                        cl[0].username +
                        ", pada: " +
                        format_date(lates_date)
                    );
                }

                $("#produksi_keterangan").val(r.keterangan);

                showFoto(r.files);
            }
        },
        error: function() {
            Swal.fire({
                //position: 'bottom-end',
                icon: "error",
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
                timer: 1500,
            });
            return;
        },
    });
    $(".label_alamat").html(
        dt_proyek.nama_proyek +
        "<br/>" +
        sh.data.nama_jalan +
        ", No." +
        sh.data.no_kavling +
        "<br/>" +
        sh.data2.no_tipe_rumah +
        " (" +
        sh.data2.tipe_rumah +
        ")<br/>"
    );
    $("#modal_divisi" + role).modal({
        backdrop: "static",
        keyboard: false,
    });
}

function download(e, callback = null) {
    (async() => {
        const response = await fetch(base_url + "produksi/get_gambarkerja", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                [csrfName]: csrfHash,
                id_gambar_kerja: e,
                pass: "password",
            }),
        });

        if (!response.ok) {
            throw new Error('Gagal mengunduh file');
        }

        const data = await response.json();
        const url = data.lokasi;
        const a = document.createElement("a");
        let sh = editdtt[0].data2

        a.href = url;
        const date = new Date();
        const filename = `${sh.no_tipe_rumah}: diunduh pada: ${date.toISOString().split('T')[0]} - ${date.getHours()}:${date.getMinutes()}}.pdf`;
        a.download = filename;
        a.click();
        callback();
    })().catch((error) => {
        swal('error', 'Gagal mengunduh file');
        callback();
    });
    // (async () => {
    //   const rawResponse = await fetch(base_url + "produksi/get_gambarkerja", {
    //     method: "POST",
    //     headers: {
    //       Accept: "application/json",
    //       "Content-Type": "application/json",
    //     },
    //     body: JSON.stringify({
    //       [csrfName]: csrfHash,
    //       id_gambar_kerja: e,
    //       pass: "password",
    //     }),
    //   })
    //     .then((resp) => resp.blob())
    //     .then((blob) => {
    //       const url = window.URL.createObjectURL(blob);
    //       const a = document.createElement("a");
    //       a.style.display = "none";
    //       a.href = url;
    //       let sh = editdtt[0].data2

    //       // the filename you want
    //       a.download = `${sh.no_tipe_rumah}.pdf`;
    //       document.body.appendChild(a);
    //       a.click();
    //       window.URL.revokeObjectURL(url);
    //       callback();
    //     })
    //     .catch(() => {
    //       swal('error', 'Gagal mengunduh file')
    //       callback();
    //     });
    // })();
}

$("#fm-slf-id_kavling").select2({
    placeholder: "Pilih Kavling",
    allowClear: true,
    ajax: {
        url: base_url + "/produksi/getKavling",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
            return {
                [csrfName]: csrfHash,
                search: params.term,
                id_proyek: dt_proyek.id_proyek
            };
        },
        processResults: function(r) {
            csrfHash = r.token

            let results = [];
            $.each(r.data, function(i, v) {
                results.push({
                    id: v.id_kavling,
                    text: `${v.nama_jalan} No ${v.no_kavling}: ${v.nama_konsumen ? v.nama_konsumen : "-"}`
                });
            });

            return {
                results: results
            };
        },
        cache: true
    },
})



function simpan_slf() {
    // Validate required fields
    let requiredFields = $('#fm-pr_slf').find('[required]');
    let isValid = true;

    requiredFields.each(function() {
        if ($(this).val() === '') {
            isValid = false;
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    if ($("#fm-slf-id_kavling").val().length == 0) {
        isValid = false;
        $(this).addClass('is-invalid');
    } else {
        isValid = true;
        $(this).removeClass('is-invalid');
    }

    if (!isValid) {
        swal('Error', 'Mohon lengkapi semua field yang wajib diisi', 'error');
        return;
    }

    // If all required fields are filled, proceed with form submission
    let formData = new FormData($('#fm-pr_slf')[0]);

    formData.append(csrfName, csrfHash);
    formData.append("id_proyek", dt_proyek.id_proyek);

    $.ajax({
        url: base_url + 'produksi/saveSLF',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success === true) {
                swal('success', 'Berhasil', 'Data SLF berhasil disimpan');
                // Optionally, refresh the SLF list or perform other actions
                form_slf_reset()
                    // Refresh the SLF list
                getlistSLF()

                // Reset and focus on the SLF tab
                $('#fm-slf-id_kavling').val(null).trigger('change');
                $('a[href="#fm-pr_list_slf"]').tab('show');
            } else {
                swal('error', 'terjadi kesalahan', 'Gagal menyimpan data SLF');
            }
        },
        error: function() {
            swal('error', 'Terjadi kesalahan', 'Terjadi kesalahan saat menyimpan data');
        }
    });
}

function getlistSLF() {
    $.ajax({
        url: base_url + 'produksi/getSlf',
        type: 'GET',
        data: { id_proyek: dt_proyek.id_proyek },
        success: function(response) {
            if (response.data && response.data.length > 0) {
                let tableContent = '';
                $.each(response.data, function(i, v) {
                    tableContent += `
                                  <tr>
                                      <td>${i + 1}</td>
                                      <td>${v.no_slf}</td>
                                      <td>${v.kavling}</td>
                                      <td>                                                
                                          <div class="form-group">
                                               <a href="${base_url}produksi/getSLFPDF/${v.id}" class="btn btn-outline-primary waves-effect btn-sm" target="_blank"><i class="fa fa-eye"></i></a>
                                              <a href=javascript:void(0) onclick="editSLF(${v.id})" class="btn btn-outline-warning waves-effect btn-sm"><i class="fa fa-edit"></i></a>
                                              <a href=javascript:void(0) onclick="hapusSLF(${v.id})" class="btn btn-outline-danger waves-effect btn-sm"><i class="fa fa-trash"></i></a>
                                          </div>
                                         </td>
                                      <td>${v.username + "<br>" + format_datetime(v.created_at)}</td>
                                  </tr>
                              `;
                });
                $("#tb-pr_lsit_slf-here").html(tableContent);
            } else {
                $("#tb-pr_lsit_slf-here").html("<tr><td colspan='5' style='text-align: center'>Tidak ada data</td></tr>");
            }
        },
        error: function() {
            swal('Error', 'Gagal memuat data SLF', 'error');
        }
    });
}

function hapusSLF(id) {
    Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data SLF akan dihapus secara permanen!",
            icon: "warning",
            showCancelButton: true,
            onfirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, hapus!",
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete.isConfirmed) {
                $.ajax({
                    url: base_url + 'produksi/hapusSLF',
                    type: 'POST',
                    data: { id: id, [csrfName]: csrfHash },
                    success: function(response) {
                        if (response.status === 'success') {
                            swal("Berhasil!", "Data SLF telah dihapus.", "success");
                            getlistSLF(); // Refresh the SLF list
                        } else {
                            swal("Error", "Gagal menghapus data SLF", "error");
                        }
                    },
                    error: function() {
                        swal("Error", "Terjadi kesalahan saat menghapus data SLF", "error");
                    }
                });
            }
        });
}

function form_slf_reset() {
    $("#fm-pr_slf")[0].reset()
    $("#tb-pr_lsit_slf-here").html("<tr><td colspan='5' style='text-align: center'>Tidak ada data</td></tr>")

    $("#fm-slf-id_kavling").val(null).trigger('change');

    $("#fm-slf-kelurahan").val(dt_proyek.kelurahan)
    $("#fm-slf-kecamatan").val(dt_proyek.kecamatan)
    $("#fm-slf-kota").val(dt_proyek.kota)
    $("#fm-slf-provinsi").val(dt_proyek.provinsi)
    $("#fm-slf-alamat_proyek").val(dt_proyek.alamat_proyek)
    $("#fm-slf-nama_perusahaan").val(dt_proyek.nama_pt)
    $("#fm-slf-nama_bangunan").val(dt_proyek.nama_proyek)


}

function buat_slf() {
    // if (editdtt.length == 0) {
    //     return swal('error', 'Tidak ada kavling terpilih');
    // }
    let sh = editdtt;
    let dvl = ""
    form_slf_reset()
    getlistSLF()


    $(".label_alamat").html(dt_proyek.nama_proyek);
    $("#modal-pr_slf").modal({
        backdrop: "static",
        keyboard: false,
    });


}
var bp = []

function isi_pembayaran() {
    if (editdtt.length == 0)
        return swal('error', 'Terjad Kesalahan', 'Tidak ada kavling yang dipilih')

    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    bp = []

    $("#fm-bayar_produksi-prod")[0].reset()
    $("#div-bayar_produksi-here").html("")

    $.ajax({
        url: base_url + 'produksi/getBayarProduksi',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_kavling: id_kavling
        },
        dataType: 'json',
        beforeSend: function() {
            $("#loading").removeClass("hidden");
        },
        success: function(r) {
            $("#loading").addClass("hidden");
            csrfHash = r.token;
            let d = r.list_bayar_produksi
            let div = '',
                id

            $.each(d, function(i, v) {
                bp.push(v.id_bayar_produksi)

                id = !v.id ? "n" + v.id_bayar_produksi : v.id
                div += `
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>${v.item}</strong>
                            </div>
                            <div class="card-body">
                                    <div class="row">
                                    <div class="col-md-6">
                                    
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Tanggal Pembayaran</label>
                                            <input type="text" class="form-control fp-bayar_produksi flatpickr-human-friendly tbp${v.id_bayar_produksi}"
                                                id="id-bayar_produksi[${id}][tanggal_bayar]" value="${v.tanggal_bayar?v.tanggal_bayar:''}" name="id-bayar_produksi[${id}][tanggal_bayar]">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="sumurbor_bayar_nominal">Nominal</label>
                                            <input type="text" class="form-control num nbp${v.id_bayar_produksi}" id="id-bayar_produksi[${id}][nominal]"
                                                name="id-bayar_produksi[${id}][nominal]" value="${v.nominal?v.nominal:''}">
                                            <input type="hidden" class="form-control" id="id-bayar_produksi[${id}][id_item_produksi]"
                                                name="id-bayar_produksi[${id}][id_item_produksi]" value="${id}">
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <textarea class="form-control" id="id-bayar_produksi[${id}][keterangan]"
                                                name="id-bayar_produksi[${id}][keterangan]" rows="4" placeholder="Keterangan">${v.keterangan?v.keterangan:''}</textarea>
                                            <small id="last_update-sumurbor_bayar" class=""></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                 `
            });

            $("#div-bayar_produksi-here").html(div)

            flatpickr(".fp-bayar_produksi", {
                altInput: true,
                altFormat: 'F j, Y',
                dateFormat: 'Y-m-d'
            })
            $(".num").change()


            $("#bayar_produksi-id_kavling").val(id_kavling)

            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal-bayar_produksi-prod').modal({
                backdrop: 'static',
                keyboard: false
            });
        },
        error: function(r) {
            $("#loading").addClass("hidden");
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "terjadi kesalahan",
                showConfirmButton: false,
                // timer: 1500
            })
        }
    });
}

function save_bayar_produksi() {

    $.each(bp, function(i, v) {
        if ($(".tbp" + v)[0].value != "") {
            if ($(".nbp" + v)[0].value == "") {
                $(".nbp").addClass('is-invalid');
                return swal('error', "Nominal pembayaran harus diisi")
            } else {
                $(".nbp").removeClass('is-invalid');
            }
        }
        if ($(".nbp" + v)[0].value != "") {
            if ($(".tbp" + v)[0].value == "") {
                $(".tbp").addClass('is-invalid');
                return swal('error', "Tanggal pembayaran harus diisi")
            } else {
                $(".tbp").removeClass('is-invalid');
            }
        }
    });


    let sbtn = "#add-form-btn-bayar_produksi"
    $.ajax({
        url: base_url + 'produksi/saveBayarProduksi',
        type: 'post',
        data: $("#fm-bayar_produksi-prod").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: 'json',
        beforeSend: function() {
            simpanBtn(sbtn, true)
        },
        success: function(r) {
            csrfHash = r.token;
            if (r.success === true) {
                swal("success", r.messages)
                $('.modal').modal('hide');
                simpanBtn(sbtn, false)
            } else {
                swal('error', 'Terjadi kesalahan', r.messages)
                simpanBtn(sbtn, false)
            }
            load_kavling();
            hapus_seleksi();
        },
        error: function(r) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "terjadi kesalahan",
                showConfirmButton: false,
                // timer: 1500
            })
            simpanBtn(sbtn, false)
        }
    });
}