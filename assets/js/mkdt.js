$("#id_bank").select2({
    placeholder: "Pilih Bank",
    allowClear: true,
    ajax: {
        url: base_url + "api/bank",
        dataType: "json",
        delay: 250,
        method: "get",
        data: function(params) {
            return {
                [csrfName]: csrfHash,
                search: params.term
            };
        },
        processResults: function(r) {
            // csrfHash = r.token;

            let results = [];
            $.each(r, function(i, v) {
                results.push({
                    id: v.id,
                    text: `${v.bank}${v.keterangan ? ': (' + v.keterangan + ')':''}`,
                });
            });

            return {
                results: results,
            };
        },
        cache: false,
    },
});

$("#status_mkdt").change(function() {
    if ($("#status_mkdt option:selected").val() == "Batal")
        $("#show_keterangan_batal").removeClass("hidden");
    else $("#show_keterangan_batal").addClass("hidden");
});
//
// hitung turun kpr
$("#fm-mkdt #harga_kpr, #fm-mkdt #acc_harga_kpr").change(function() {
    if ($("#fm-mkdt #acc_harga_kpr").val() != "0") {
        $("#fm-mkdt #harga_turun_kpr")
            .val(
                parseFloat(removeComma($("#fm-mkdt #harga_kpr").val())) -
                parseFloat(removeComma($("#fm-mkdt #acc_harga_kpr").val()))
            )
            .change();
    } else {
        $("#fm-mkdt #harga_turun_kpr").val(0);
    }
});
//delete tanggal jika toogle di aktifkan
$("#wawancara").change(function() {
    if (!$("#wawancara").prop("checked")) {
        setDatePicker(null, "#wawancara_tgl");
    }
});

$("#refresh_fmmkdt_btn").click(function() {
    refresh_fmmkdt(false);
    $("#mkdt_data_baru").val(1);
});

function refresh_fmmkdt($st = true) {
    $("#fm-mkdt")[0].reset();
    $("#fm-mkdt input:text, #fm-mkdt select, #fm-mkdt textarea").prop(
        "disabled",
        $st
    );
    $("#id_konsumen").val("");
    $("#id_keuangan0").val("");
}

function delete_kons() {
    $(
        "#fm-mkdt #nama_konsumen, #fm-mkdt #alamat_konsumen, #fm-mkdt #nik_konsumen, #fm-mkdt #hp_konsumen, #fm-mkdt #status_konsumen"
    ).val("");
    $("#id_konsumen, #id_mkdt").val("");
    $("#mkdt_data_baru").val(1);
}

function open_mkdt(sh, role, id_kavling) {
    if (sh.data.tipe != "kavling")
        return swal("error", "Tidak ada kavling terpilih", null, true);

    // if (!sh.data.id_mkdt)
    //     return swal("error", "Belum ada data konsumen", null, true);

    if (sh.data2.harga_akhir == "-") {
        return swal("error", "Kavling belum dipasarkan (tidak ada harga jual)");
    }



    $("#label-file_ktp").html("Upload file KTP");
    $("#label-file_npwp").html("Upload file KTP");

    $("#refresh_fmmkdt_div").addClass("hidden");
    $("#delete_kons_div").addClass("hidden");
    $("#fm-mkdt .num").prop("disabled", false);

    $("#cicilan_belong_here").html("");
    it = 0;
    // $("#data_konsumen").tab('show');

    $("#mkdt_data_baru").val(0);

    refresh_fmmkdt(false);

    $("#fm-mkdt .num").val(0);

    $(".id_kavling").val(id_kavling);
    $("#id_mkdt").val(sh.data.id_mkdt);

    $.ajax({
        url: base_url + "mkdt/get_data_by_id",
        type: "post",
        data: {
            [csrfName]: csrfHash,
            id_mkdt: sh.data.id_mkdt,
            id_hargajual: sh.data2.id_hargajual,
            id_kavling: id_kavling,
        },
        dataType: "json",
        beforeSend: function() {
            $("#loading").removeClass("hidden");
        },
        success: function(x) {
            $("#loading").addClass("hidden");
            csrfHash = x.token;
            let r = x.data, //data mkdt
                pb = x.perintah_bangun,
                h = x.hj; //pricelist

            //load hargajual
            if (h.hargajual) {
                $.each(h, function(k, v) {
                    $("#mkdt-" + k)
                        .val(v)
                        .change()
                        .keyup();
                });
                $("#mkdt-tgl_harga").val(format_date(h.tgl_harga));
                $("#fm-mkdt #harga_kpr").val(h.kpr).change();
            }

            //load num
            // if (!r.id_mkdt) {
            //     $(".num").val(0).keyup();
            //     $("#fm-mkdt #harga_jual").val(sh.data2.harga_akhir);
            // }

            //set harga jual dari data kavling
            if (r) {
                if (r.status_mkdt == "Batal") {
                    refresh_fmmkdt(true);
                    $("#show_keterangan_batal, #refresh_fmmkdt_div").removeClass(
                        "hidden"
                    );
                    $("#delete_kons_div").addClass("hidden");
                }

                //autoload field ke input
                for (let i in r) {
                    if (
                        i != "perintah_bangun" &&
                        i != "wawancara" &&
                        i != "akad" &&
                        i != "sp3k" &&
                        i != "bast_file" &&
                        i != "sp3k_file" &&
                        i != "perintah_bangun_file"
                    )
                        $("#fm-mkdt #" + i).val(r[i]);
                }

                $("#fm-mkdt #mkdt_keterangan").val(r.keterangan);
                $("#fm-mkdt #acc_harga_kpr").val(r.harga_kpr_acc).change();
                $("#fm-mkdt #harga_turun_kpr").val(r.harga_penambahan_um).change();

                var newOption = new Option(r.nama_bank, r.id_bank, true, true);
                $('#id_bank').append(newOption).trigger('change');

                if (r.wawancara == 1) $("#wawancara").prop("checked", true);
                if (r.sp3k == 1) $("#sp3k").prop("checked", true);
                if (r.akad == 1) $("#akad").prop("checked", true);

                //set datepicker jika tanggal valid
                setDatePicker(pb.perintah_bangun_tgl, "#fm-mkdt #perintah_bangun_tgl");

                setDatePicker(r.booking_tgl, "#fm-mkdt #booking_tgl");
                setDatePicker(r.wawancara_tgl, "#fm-mkdt #wawancara_tgl");
                setDatePicker(r.sp3k_tgl, "#fm-mkdt #sp3k_tgl");
                setDatePicker(r.sp3k_tgl_exp, "#fm-mkdt #sp3k_tgl_exp");
                setDatePicker(r.rencana_akad_tgl, "#fm-mkdt #rencana_akad_tgl");
                setDatePicker(r.akad_tgl, "#fm-mkdt #akad_tgl");

                // if (r.refund_tgl != "0000-00-00")
                //     document.querySelector("#refund_tgl")._flatpickr.setDate(r.refund_tgl);

                $("#fm-mkdt .num").keyup().change(); //fomrat form number
                $("#status_mkdt").change(); //show/hide keterangan batal

                $("#mkdt_keterangan").val(r.keterangan);

                $("#file_ktp-here").html("Tidak ada data")
                src = not_found;
                //load ktp npwp
                if (r.ktp_lok != null) {
                    src = r.ktp_lok;
                    $("#file_ktp-here").html(`<embed src = "${base_url + src}" width="100%" height="150px"/>`);
                }
                $("#file_ktp-here").prop("href", base_url + src);


                $("#file_npwp-here").html("Tidak ada data")
                    //load npwp
                src = not_found;
                if (r.npwp_lok != null) {
                    src = r.npwp_lok;
                    $("#file_npwp-here").html(`<embed src = "${base_url + src}" width="90%" height="150px"/>`);
                }
                $("#file_npwp-here").prop("href", base_url + src);



                $("#file_data_diri-here").html('Tidak ada data');
                //load data diri
                src = not_found;
                if (r.data_diri_lok != null) {
                    src = r.data_diri_lok;
                    $("#file_data_diri-here").html(`<embed src = "${base_url + src}" width="90%" height="150px"/>`);
                }
                $("#file_data_diri-here").prop("href", base_url + src);

                src = not_found;
                //load bast
                if (r.bast_file != null) {
                    src = r.bast_file;
                }
                $("#list-upload_bast_file").prop("href", base_url + src);

                src = not_found;
                //load sp3k
                if (r.sp3k_file != null) {
                    src = r.sp3k_file;
                }
                $("#list-upload_sp3k_file").prop("href", base_url + src);


            }

            if (pb.perintah_bangun == 1) {
                $("#perintah_bangun").prop("checked", true);
                $("#fm-mkdt #perintah_bangun_oleh").val(pb.username);

                setDatePicker(pb.perintah_bangun_tgl, "#perintah_bangun_tgl")

                src = not_found;
                //load perintah_bangun
                if (pb.perintah_bangun_file != null) {
                    src = pb.perintah_bangun_file;
                }
                $("#list-upload_perintah_bangun_file").prop(
                    "href", base_url + src);
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
            $("#modal_divisi" + role).modal({
                backdrop: "static",
                keyboard: false,
            });
        },
        error: function(xhr, st, err) {
            $("#loading").addClass("hidden");
            return swal("error", err);
        },
    });
}

$("#add-form-btn-mkdt").click(function(e) {
    e.preventDefault();
});

function save_mkdt(e) {
    if (!palid("fm-mkdt #nama_konsumen", "", "nama konsumen harus diisi")) return;
    if (!palid("fm-mkdt #status_mkdt", "", "Status harus diisi")) return;
    if (!palid("fm-mkdt #id_bank", "", "Bank harus diisi")) return;

    for (let a = 0; a <= it; a++) {
        if (!palid(
                "fm-mkdt #jatuh_tempo_tgl" + a,
                "",
                "Tanggal jatuh tempo harus diisi"
            ))
            return false;
        if (!palid("fm-mkdt #berita_acara1" + a, "", "Keterangan harus diisi"))
            return false;
    }

    // if ($("#total_cicilan").val() != $("#total_biaya2").val()) {
    //     Swal.fire({
    //         //position: 'bottom-end',
    //         icon: 'error',
    //         title: "Total Cicilan tidak sesuai dengan total biaya",
    //         showConfirmButton: false,
    //         timer: 1500
    //     });
    //     return false;
    // }

    // var files = $('#file_ktp')[0].files;
    var form = $("#fm-mkdt")[0];
    var fd = new FormData(form);
    fd.append(csrfName, csrfHash);

    $.ajax({
        url: base_url + "mkdt/save",
        type: "post",
        // data: $("#fm-mkdt").serialize() + "&" + csrfName + "=" + csrfHash,
        contentType: false,
        processData: false,
        data: fd,
        dataType: "json",
        beforeSend: function() {
            simpanBtn("#add-form-btn-mkdt", true)
        },
        success: function(r) {
            csrfHash = r.token;

            if (r.success === true) {
                swal('success', r.messages)
                $(".modal").modal("hide");
            } else {
                swal('error', r.messages)
            }
            simpanBtn("#add-form-btn-mkdt", false)

            load_kavling();
            hapus_seleksi();
        },
        error: function(xhr, st, err) {
            return swal("error", err);
        },
    });
}

function set_harga() {
    $.ajax({
        url: base_url + "Hargajual/set_harga",
        type: "post",
        data: $("#fm-set_harga").serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
        dataType: "json",
        beforeSend: function() {
            $("#set-harga-form-btn").html(
                'Menyimpan <i class="fa fa-spinner fa-spin"></i>'
            );
            $("#set-harga-form-btn").addClass("disabled");
        },
        success: function(response) {
            csrfHash = response.token;
            swal(
                response.success ? "success" : "error",
                response.messages,
                null,
                true,
                () => {
                    if (response.success) {
                        $("#modals-set_harga").modal("hide");
                    }
                    $("#set-harga-form-btn").html("Simpan");
                    $("#set-harga-form-btn").removeClass("disabled");
                }
            );
            load_kavling();
            hapus_seleksi();
        },
    });
}

function formatDesign(item) {
    var selectionText = item.text.split(";");
    var $returnString = $('<span> <b>' + selectionText[0] + '</b></br >' + selectionText[1] + '</br>' + selectionText[2] + '</span>');
    return $returnString;
}
$("#sh-id").select2({
    placeholder: "Pilih Pricelist",
    allowClear: true,
    templateResult: formatDesign,
    ajax: {
        url: base_url + "Hargajual/getAll",
        dataType: "json",
        delay: 250,
        method: "post",
        data: function(params) {
            return {
                [csrfName]: csrfHash,
                search: params.term,
                id_proyek: dt_proyek.id_proyek,
            };
        },
        processResults: function(r) {
            csrfHash = r.token;

            let results = [];
            $.each(r.data, function(k, v) {
                results.push({
                    id: v.id,
                    text: `Rp. ${num_format(v.hargajual)} Per ${format_date(v.tgl_harga)} (ROW${v.row}); <b>Tipe:</b> ${v.tipe_rumah} (${v.no_tipe_rumah}); <b>Ket:</b> ${v.keterangan};`,
                    row: v.row,
                    tipe: v.tipe_rumah,
                    lb: v.lb,
                    lt: v.lt,
                    hargajual: v.hargajual,
                    hargajual_net: v.hargajual_net,
                    kpr: v.kpr,
                    uang_muka: v.uang_muka,
                    bphtb: v.bphtb,
                    ppn: v.ppn,
                    biaya_adm: v.biaya_adm,
                    biaya_proses: v.biaya_proses,
                    id_tipe: v.id_tipe,
                    lok: `${v.lokasi}/${v.file_name}`,

                });
            });

            return {
                results: results,
            };
        },
        cache: false,
    },
});
// on select cluster
$("#sh-id").on("select2:selecting", function(e) {
    var i = e.params.args.data;
    $.each(i, function(k, v) {
        changeVal("#sh-" + k, v)
    });

    let src = i.lok != 'null/null' ? i.lok : not_found;
    $("#sh-pricelist_file").prop("href", `${base_url}/${src}`);
});
$("#sh-id").change(function() {
    if (!this.value) $(".sh-fm").val("");
});

function open_set_turun_pembangunan() {
    $("#list-tp-upload_perintah_bangun_file").prop("href", base_url + not_found);
    $("#label-perintah_bangun_file").html("File Turun Perintah Bangun");
    if (editdtt.length == 0) {
        return swal('error', 'Tidak ada kavling terpilih');
    }
    $("#fm-turun_pembangunan")[0].reset();

    let data = [];

    for (let a = 0; a < editdtt.length; a++) {
        data.push(editdtt[a].id.substr(3));
    }
    $.ajax({
        url: base_url + "siteplan/get_turun_pembangunan",
        type: "post",
        data: {
            [csrfName]: csrfHash,
            id_kavling: data,
        },
        dataType: "json",
        beforeSend: function() {
            $("#loading").removeClass("hidden");
        },
        success: function(res) {
            csrfHash = res.token;
            let r = res.data,
                id_kavling = "",
                no = "";

            $(".select2").not("#pilih-divisi").val(null).trigger("change");

            if (r.length > 0) {
                r.forEach((v) => {
                    id_kavling += v.id_kavling + ";";
                    no += `${v.nama_jalan} No. ${v.no_kavling} \n`;
                });

                $(".id_kavling").val(id_kavling);
                $("#tp-kavling").val(no);

                $("#tp-perintah_bangun_oleh").val(r[0].username);

                $("#list-tp-upload_perintah_bangun_file").prop(
                    "href",
                    base_url + r[0].perintah_bangun_file
                );

                setDatePicker(r[0].perintah_bangun_tgl, "#tp-perintah_bangun_tgl");
            }

            $("#loading").addClass("hidden");
            $("#modals-turun_pembangunan").modal({
                backdrop: "static",
                keyboard: false,
            });
        },
        error: function(xhr, st, err) {
            return swal("error", err);
        },
    });
}

function set_tp() {
    if ($("#tp-perintah_bangun_tgl").val() == "") {
        return Swal.fire({
            icon: "error",
            title: "Tanggal Perintah Bangun harus diisi",
            showConfirmButton: false,
        });
    }
    let form = $("#fm-turun_pembangunan")[0];
    let fd = new FormData(form);
    fd.append(csrfName, csrfHash);

    $.ajax({
        url: base_url + "siteplan/set_turun_pembangunan",
        type: "post",
        contentType: false,
        processData: false,
        data: fd,
        dataType: "json",
        beforeSend: function() {
            $("#set-tp-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            $("#set-tp-btn").addClass("disabled");
        },
        success: function(response) {
            csrfHash = response.token;
            if (response.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: "success",
                    title: response.messages,
                    showConfirmButton: false,
                    timer: 1500,
                }).then(function() {
                    $("#modals-turun_pembangunan").modal("hide");
                    $("#set-tp-btn").html("Simpan");
                    $("#set-tp-btn").removeClass("disabled");
                });
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: "error",
                    title: response.messages,
                    showConfirmButton: false,
                    timer: 1500,
                }).then(function() {
                    $("#set-tp-btn").html("Simpan");
                    $("#set-tp-btn").removeClass("disabled");
                });
            }
            load_kavling();
            hapus_seleksi();
        },
        error: function(err) {
            Swal.fire({
                //position: 'bottom-end',
                icon: "error",
                title: "terjadi kesalahan saat menginput data",
                showConfirmButton: false,
            }).then(function() {
                $("#set-tp-btn").html("Simpan");
                $("#set-tp-btn").removeClass("disabled");
            });
        },
    });
}

function open_set_harga() {
    if (editdtt.length == 0)
        return swal("error", "Tidak ada kavling terpilih", null, true);


    $("#fm-set_harga")[0].reset();

    let data = [];

    for (let a = 0; a < editdtt.length; a++) {
        data.push(editdtt[a].id.substr(3));
    }

    $.ajax({
        url: base_url + "siteplan/get_harga_kavling",
        type: "post",
        data: {
            [csrfName]: csrfHash,
            id_kavling: data,
        },
        dataType: "json",
        success: function(res) {
            csrfHash = res.token;
            let r = res.data,
                id_kavling = "",
                src,
                no = ""

            $("#sh-id").val(null).trigger("change");

            if (r.length > 0) {
                for (let a = 0; a < r.length; a++) {
                    id_kavling += r[a].id_kavling + ";";
                    no += `${r[a].nama_jalan} No. ${r[a].no_kavling} - ${r[a].tipe_rumah}\n`;

                    if (r[a].harga_akhir) {
                        $("#sh-id")
                            .append(
                                $("<option selected></option>")
                                .attr("value", r[a].harga_akhir)
                                .text(
                                    `Rp. ${num_format(r[a].hargajual)} (${r[a].tipe_rumah
                    }) ROW ${r[a].row}: per ${format_date(r[a].tgl_harga)}`
                                )
                            )
                            .trigger("change");

                        changeVal("#sh-row", r[a].row);
                        changeVal("#sh-tipe", r[a].tipe_rumah);
                        changeVal("#sh-lb", r[a].hj_lb);
                        changeVal("#sh-lt", r[a].hj_lt);
                        changeVal("#sh-hargajual", r[a].hargajual);
                        changeVal("#sh-hargajual_net", r[a].hargajual_net);
                        changeVal("#sh-kpr", r[a].kpr);
                        changeVal("#sh-uang_muka", r[a].uang_muka);
                        changeVal("#sh-ppn", r[a].ppn);
                        changeVal("#sh-bphtb", r[a].bphtb);
                        changeVal("#sh-biaya_adm", r[a].biaya_adm);
                        changeVal("#sh-biaya_proses", r[a].biaya_proses);


                        src = not_found;
                        if (r[a].file_name) {
                            src = `${r[a].lokasi}/${r[a].file_name}`;
                        }
                        $("#sh-pricelist_file").prop("href", `${base_url}${src}`);
                    }
                }

                $(".id_kavling").val(id_kavling);
                $("#sh-kavling").val(no);
                // $("#fm-set_harga #id_tipe").val(id_tipe);
                // $("#fm-set_harga #harga").val(harga).keyup();
            }

            $("#modals-set_harga").modal({
                backdrop: "static",
                keyboard: false,
            });
        },
        error: function(xhr, st, err) {
            return swal("error", err);
        },
    });
}

function isi_si() {
    let sh = editdtt;

    if (sh.length == 0)
        return swal("error", "Tidak ada kavling terpilih", null, true);

    sh = sh[0]

    let id_kavling = sh.id.substr(3)

    $(".id_kavling").val(id_kavling)

    co = [];


    $("#fm-si")[0].reset();

    $.ajax({
        url: base_url + "mkdt/getsi",
        type: "post",
        data: {
            [csrfName]: csrfHash,
            id_kavling: id_kavling,
        },
        dataType: "json",
        success: function(res) {
            csrfHash = res.token;
            let d = res.data,
                id_si,
                div = '';

            $.each(d, function(i, v) {
                co.push(v.id_list_si_ori)

                id_si = !v.id ? "n" + v.id_list_si_ori : v.id
                div += `
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>${v.nama}</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Tanggal SI</label>
                                                <input type="text" class="form-control fp-si flatpickr-human-friendly tsi${v.id_list_si_ori}"
                                                    id="id-si[${id_si}][tanggal_si]" value="${v.tanggal_si?v.tanggal_si:''}" name="id-si[${id_si}][tanggal_si]">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Soft File</label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input " accept="application/pdf"
                                                        name="id-si-file-${id_si}" id="id-si-file-${id_si}" />
                                                    <label class="custom-file-label" id="label-si-file-${id_si}"
                                                        for="id-si-file-${id_si}">Upload Soft File</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                        
                                         <a href="${base_url + v.file}" target=_blank id="list-si-file-${id_si}"
                                                class="btn btn-outline-primary col-12">Klik untuk lihat file</a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <textarea class="form-control" id="id-si[${id_si}][keterangan]"
                                                name="id-si[${id_si}][keterangan]" rows="4" placyeholder="Keterangan">${v.keterangan?v.keterangan:''}</textarea>
                                            <small id="last_update-si${id_si}" class=""></small>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                 `
            });

            $("#dv-si-here").html(div)

            flatpickr(".fp-si", {
                altInput: true,
                altFormat: 'F j, Y',
                dateFormat: 'Y-m-d'
            })
            $(".num").change()


            $("#modals-si").modal({
                backdrop: "static",
                keyboard: false,
            });
        },
        error: function(xhr, st, err) {
            return swal("error", err);
        },
    });
}

function save_si() {
    // $.each(co, function(i, v) {
    //     if ($(".tsi" + v)[0].value == "") {
    //         $(".tsi" + v).addClass('is-invalid')
    //         return swal('error', "Nominal pembayaran harus diisi")
    //     } else
    //         $(".tsi" + v).removeClass('is-invalid');

    // });

    var form = $("#fm-si")[0];
    var fd = new FormData(form);
    fd.append(csrfName, csrfHash);

    let sbtn = "#btn-si-simpan"

    $.ajax({
        url: base_url + "mkdt/saveSI",
        type: "post",
        contentType: false,
        processData: false,
        data: fd,
        dataType: "json",
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

$("#sp3k_tgl").change(function() {
    document.querySelector("#sp3k_tgl_exp")._flatpickr.setDate(new Date(this.value).fp_incr(88));
})