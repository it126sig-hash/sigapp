
/**************************** planning ***************************** */
var act;
//masking kavling on dbl click
siteplan.on('dblclick dbltap', function (e) {
    if ($("#tambah_jalan").prop("checked"))
        return;

    let va = $("#pilih-divisi option:selected").val();

    //planning only
    // if (va != 6 roleid) {
    //     Swal.fire({
    //         //position: 'bottom-end',
    //         icon: 'error',
    //         title: "Ubah list data ke pilihan planning",
    //         showConfirmButton: false,
    //         timer: 1500
    //     })
    //     return;
    // }
    dtt = [];

    e = e.evt;

    allowDraw = true;
    addMode = e.ctrlKey;

    downPoint = stage.getPointerPosition();

    if (!addMode)
        hapus_seleksi();

    let a = stage.getAbsoluteTransform().copy();
    a.invert();
    let l = a.point(downPoint);

    let xy = {
        x: parseInt(l.x, 10),
        y: parseInt(l.y, 10)
    }

    drawMask(xy.x, xy.y);
});

//open modal untuk tambah kavling


// $("#add_kavling").click(function() {});
function edit_kavling_batch() {
    if (editdtt.length == 0) return;
         $("#pindah_lokasi_btn").hide()
    if (editdtt.length == 1)
        $("#pindah_lokasi_btn").show()

    $(".t_luas_legal, .t_luas_produksi, .r_progres").html('-')

    let data,
        tipe;

    let url = base_url + "/siteplan/get_others";
    tipe = editdtt[0].data.tipe;
    data = editdtt[0].id.substr(6)

    if (tipe == "kavling") {
        data = [];
        url = base_url + '/siteplan/get_kavling_by_multiple_id';
        for (let a = 0; a < editdtt.length; a++) {
            data.push(editdtt[a].id.substr(3))
            tipe = editdtt[a].data.tipe;
        }
    }


    $("#fm-add_kavling")[0].reset();
    $('.select2').not("#pilih-divisi").val(null).trigger('change');

    $.ajax({
        url: url,
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_kavling: data
        },
        dataType: 'json',
        beforeSend: function () {
            $("#loading").removeClass("hidden");
        },
        success: function (res) {
            csrfHash = res.token;
            $("#id_jenis").val(tipe).change();

            let r = res.data,
                id_kavling = "",
                id_cluster,
                id_jalan,
                id_tipe,
                no = "",
                points = "";

            $("#id_cluster").append($("<option selected></option>").attr("value", r[0].id_cluster).text(r[0].nama_cluster)).trigger('change');
            $("#id_jalan").append($("<option selected></option>").attr("value", r[0].id_jalan).text(r[0].nama_jalan)).trigger('change');
            $("#id_tipe").append($("<option selected></option>").attr("value", r[0].id_tipe).text(r[0].no_tipe_rumah + " (" + r[0].tipe_rumah + ")")).trigger('change');


            if (tipe == "kavling") {
                if (r.length > 0) {
                    for (let a = 0; a < r.length; a++) {
                        id_kavling += r[a].id_kavling + ";";
                        no += r[a].no_kavling + ";";
                        id_cluster = r[a].id_cluster;
                        id_jalan = r[a].id_jalan;
                        id_tipe = r[a].id_tipe;
                        points += r[a].points + ";"
                    }
                    $("#status_tanah").val(r[0].status_tanah).change();
                    $(".id_kavling").val(id_kavling);
                    $("#no_kavling").val(no);
                    $("#points").val(points)
                    $("#f_luas").val(r[0].luas_tanah);

                }
            } else {
                if (r.length > 0) {
                    $(".id_kavling").val(r[0].id);
                    $("#f_luas").val(r[0].planning_luas);
                    $("#f_nama").val(r[0].nama);
                    $("#f_planning_keterangan").val(r[0].planning_keterangan);

                    let d = r[0];

                    if (d.produksi_luas)
                        $(".t_luas_produksi").html(d.produksi_luas + "  m&sup2  (" + d.produksi_edit + ": " + format_datetime(d.produksi_updated_at) + ")")
                    if (d.legal_luas)
                        $(".t_luas_legal").html(d.legal_luas + "  m&sup2  (" + d.legal_edit + ": " + format_datetime(d.legal_updated_at) + ")")
                }
            }

            $('#modals-slide-in').modal({
                backdrop: 'static',
                keyboard: false
            });

        },
        error: function () {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }
    });

    $("#loading").addClass("hidden");
    act = "edit"
}

function open_planning(sh, role, id_kavling) {
    $("#fm-add_kavling")[0].reset();
    $(".id_kavling").val(id_kavling);
    $(".t_luas_legal, .t_luas_produksi, .r_progres").html('-')

    $.ajax({
        url: base_url + '/siteplan/get_kavling_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_kavling: id_kavling
        },
        dataType: 'json',
        beforeSend: function () {
            $("#loading").removeClass("hidden");
        },
        success: function (res) {
            csrfHash = res.token;
            let r = res.data;
            if (r) {
                for (let i in r) {
                    $("#fm-add_kavling #" + i).val(r[i]);
                }
                // $('.id_cluster').append($("<option selected></option>").attr("value",r['id_cluster']).text(r['nama_cluster']));
                // var id_cluster = new Option(r.nama_cluster, r.id_cluster, false, false);
                // $('.id_cluster').append(newOption).trigger('change');
                // $('.id_cluster').trigger({
                //     type: 'select2:select',
                //     params: {
                //         data: r
                //     }
                // });

                $('#modals-slide-in-edit').modal({
                    backdrop: 'static',
                    keyboard: false
                });
            }
        },
        error: function () {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }
    });
    $("#loading").addClass("hidden");
}

function edit_kavling() {
    let no_kav = $("#fm-add_kavling #no_kavling").val().split(";"),
        no_kavlen = (no_kav[no_kav.length - 1] == '') ? no_kav.length - 1 : no_kav.length,
        tipe = editdtt[0].data.tipe,
        url = base_url + '/siteplan/edit_others';

    //jika no kavling dan selection tidak sesuai
    if (tipe == 'kavling') {
        if (editdtt.length > 0) {
            if (editdtt.length != no_kavlen) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi Kesalahan.",
                    text: "Jumlah Kavling yang dipilih: " + editdtt.length + "\n" + "Jumlah No Kavling yang diisi: " + no_kavlen,
                    showConfirmButton: false,
                });
                return;
            }
        }
        url = base_url + '/siteplan/edit_kavling';
    }

    $.ajax({
        url: url,
        type: 'post',
        data: $("#fm-add_kavling").serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
        dataType: 'json',
        beforeSend: function () {
            $('#add-form-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            $('#add-form-btn').addClass("disabled");
        },
        success: function (response) {
            csrfHash = response.token;
            if (response.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: response.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function () {
                    $('#modals-slide-in').modal('hide');
                    $('#add-form-btn').html('Simpan');
                    $('#add-form-btn').removeClass("disabled");
                })
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: response.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function () {
                    $('#add-form-btn').html('Simpan');
                    $('#add-form-btn').removeClass("disabled");
                })
            }
            load_kavling();
            hapus_seleksi();
        },
        error: function () {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan",
                showConfirmButton: false,
                timer: 1500
            }).then(function () {
                $('#add-form-btn').html('Simpan');
                $('#add-form-btn').removeClass("disabled");
            })
        }
    });
}

//proses tambah kavling ke db
function add_kavling() {
    if (act == "edit")
        return edit_kavling();

    if ($("#id_jenis").val() == "") {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Jenis harus diisi",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }
    if (!$("#id_cluster").val()) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Cluster harus diisi",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }
    if (!$("#id_jalan").val()) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "jalan harus diisi",
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }


    $(".form-control").removeClass('is-invalid').removeClass('is-valid');
    let par = "";

    for (let z = 0; z < batchdtt.length; z++) {
        par += "&bpoints[]=" + batchdtt[z];
    }

    //jika no kavling terakhir kosong
    let no_kav = $("#fm-add_kavling #no_kavling").val().split(";"),
        no_kavlen = (no_kav[no_kav.length - 1] == '') ? no_kav.length - 1 : no_kav.length;

    if ($("#id_jenis").val() == "kavling") {
        //jika no kavling dan selection tidak sesuai
        if (batchdtt.length != no_kavlen) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi Kesalahan.",
                text: "Jumlah Kavling yang dipilih: " + batchdtt.length + "\n" + "Jumlah No Kavling yang diisi: " + no_kavlen,
                showConfirmButton: false,
            });
            return;
        }

    }

    $.ajax({
        url: base_url + '/siteplan/add_kavling',
        type: 'post',
        data: $("#fm-add_kavling").serialize() + par + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
        dataType: 'json',
        beforeSend: function () {
            $('#add-form-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            $('#add-form-btn').addClass("disabled");
        },
        success: function (response) {
            csrfHash = response.token;

            if (response.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: response.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function () {
                    $('#modals-slide-in').modal('hide');
                    load_kavling()
                    hapus_seleksi()
                })
            } else {
                // $('#modals-slide-in').modal('hide');
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: response.messages,
                    showConfirmButton: false,
                    timer: 1500
                })
            }
            $('#add-form-btn').html('Simpan');
            $('#add-form-btn').removeClass("disabled");
        },
        error: function () {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi Kesalahan saat melakukan penambahan data kaving, jalan atau fasos",
                showConfirmButton: false,
                timer: 1500

            })
            $('#add-form-btn').html('Simpan');
            $('#add-form-btn').removeClass("disabled");
        }
    });
}

var editdtt_tmp;

function pindah_kavling() {
    editdtt_tmp = editdtt;
    $('#modals-slide-in').modal('hide');
    $('#add_kavling, #edit_kavling_batch, #planning_toggle_btn').hide()
    $('#selesai_pindah_btn, #batal_pindah_btn').show()
    hapus_seleksi()
}

function selesai_selection(e) {
    if (e == 1) {
        if (dtt == "") {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Tidak ada lokasi yang dipilih",
                showConfirmButton: false,
                timer: 1500
            })
            return;
        }

        $("#points").val(dtt)
    }
    editdtt = editdtt_tmp;

    $('#modals-slide-in').modal('show');
    $('#add_kavling, #edit_kavling_batch, #planning_toggle_btn').show()
    $('#selesai_pindah_btn, #batal_pindah_btn').hide()

}

//select2 cluster
$("#id_cluster").select2({
    placeholder: "Pilih Cluster",
    allowClear: true,
    ajax: {
        url: base_url + "/cluster/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function (params) {
            return {
                [csrfName]: csrfHash,
                search: params.term,
                id_proyek: $("#id_proyek").val()
            };
        },
        processResults: function (r) {
            csrfHash = r.token

            let results = [];
            $.each(r.data, function (index, item) {
                results.push({
                    id: item[0],
                    text: item[3]
                });
            });

            return {
                results: results
            };
        },
        cache: false
    },
})
// on select cluster
$("#id_cluster").on("change", function (e) {
    $('#id_jalan').val(null).trigger('change');
    if (this.value)
        $("#id_jalan").prop("disabled", false)
    else
        $("#id_jalan").prop("disabled", true)
});

//select jalan
$("#id_jalan").select2({
    placeholder: "Pilih Blok",
    allowClear: true,
    ajax: {
        url: base_url + "/jalan/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function (params) {
            return {
                [csrfName]: csrfHash,
                search: params.term,
                id_cluster: $("#id_cluster").val(),
                id_proyek: $("#id_proyek").val()
            };
        },
        processResults: function (r) {
            csrfHash = r.token

            let results = [];
            $.each(r.data, function (index, item) {
                results.push({
                    id: item[0],
                    text: item[3]
                });
            });

            return {
                results: results
            };
        },
        cache: true
    },
})

$("#id_tipe").select2({
    placeholder: "Pilih Tipe",
    allowClear: true,
    ajax: {
        url: base_url + "/tipe/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function (params) {
            return {
                [csrfName]: csrfHash,
                search: params.term,
                id_proyek: $("#id_proyek").val()
            };
        },
        processResults: function (r) {
            csrfHash = r.token

            let results = [];
            $.each(r.data, function (index, item) {
                results.push({
                    id: item[0],
                    text: item[2] + "(" + item[3] + ")"
                });
            });

            return {
                results: results
            };
        },
        cache: true
    },
})

$("#status_tanah").select2()

$("#id_jenis").select2()
$("#id_jenis").change(function () {
    if (this.value == "") {
        $(".h").hide()
    } else if (this.value == "kavling") {
        $(".h").hide()
        $("#div_kavling").show()
    } else if (this.value == "jalan") {
        $(".h").hide()
        $("#div_jalan, #div_luas").show()
    } else if (this.value == "fasos" || this.value == "rth") {
        $(".h").hide()
        $("#div_jalan, #div_fasos").show()
    } else {
        $(".h").hide()
    }

})

/**************************** planning ***************************** */
