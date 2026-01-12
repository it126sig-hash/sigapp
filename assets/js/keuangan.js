let id_mkdt_old = null,
    id_konsumen_old = null,
    is_ganti_nama = false

/********************************* keuangan *******************************************/
// $("#bt-bayar_tagihan_um").change(function () {
//     let s = removeComma($("#bt-sisa_tagihan_um").val()),
//         b = removeComma(this.value);
//     if (b > s)
//         $("#bt-bayar_tagihan_um").val(s).keyup();
//     else
//         $("#bt-bayar_tagihan_um").val(b).keyup();
// });


$("#idk-rincian").richText({
    fonts: false,
    // uploads
    imageUpload: false,
    fileUpload: false,

    // media
    videoEmbed: false,

    // link
    urls: false,
})
$("#snk").richText({
    // text formatting
    bold: true,
    italic: true,
    underline: true,

    // text alignment
    leftAlign: true,
    centerAlign: true,
    rightAlign: true,
    justify: true,

    // lists
    ol: true,
    ul: true,

    // title
    heading: true,

})
$("#kopsurat").select2({
    placeholder: "Pilih Kop Surat",
    allowClear: true,
    ajax: {
        url: base_url + "/Home/getKop",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
            return {
                [csrfName]: csrfHash,
                search: params.term,
            };
        },
        processResults: function(r) {
            csrfHash = r.token

            let results = [];
            $.each(r.data, function(k, v) {
                results.push({
                    id: v.id,
                    text: v.nama + " (" + v.ukuran + ")",
                    lokasi: v.lokasi,
                    ukuran: v.ukuran,
                    mt: v.mt,
                    mb: v.mb,
                    ml: v.ml,
                    mr: v.mr
                });
            });

            return {
                results: results
            };
        },
        cache: false
    },
})

function print_tagihan() {

    $("#pt_id_mkdt").html("")
    $("#pt_id_konsumen").html("")
    $("#pt_id_kavling").html("")
    $("#pt_detail_konsumen").html("")
    $("#pt_detail_kavling").html("")
    $("#list_inv-here").html("")

    $("#cp_telp").html("")
    $("#tb-print-data-tagihan").html("");


    $('.nav-tabs a[href="#form_list_inv"]').tab('show');

    document.querySelector("#tanggal_surat_tagihan")._flatpickr.setDate(new Date().toDateInputValue());
    document.querySelector("#pt-tanggal_jatuh_tempo")._flatpickr.setDate(new Date().fp_incr(7));

    let role,
        sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    if (editdtt.length == 0) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Terjadi Kesalahan.",
            text: "Tidak ada kavling yang dipilih",
            showConfirmButton: false,
        });
        return;
    } else if (!sh.data.id_mkdt) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
            showConfirmButton: false,
            timer: 1500
        })
        return;
    }

    $.ajax({
        url: base_url + 'keuangan/get_tagihan/inv',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_keuangan: sh.data.id_keuangan,
            id_kavling: id_kavling,
            id_mkdt: sh.data.id_mkdt
        },
        dataType: 'json',
        success: function(r) {
            let kons = r.detail,
                lt = r.list_tagihan
            csrfHash = r.token;

            if (!lt.length) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Tagihan tidak ditemukan",
                    text: "Isi tagihan terlebih dahulu",
                    showConfirmButton: false,
                })
                return;
            }

            if (r.invoice.length) {
                let tb = ""
                $.each(r.invoice, function(i, v) {
                    tb += "<tr>"
                    tb += "<td>" + v.no_inv + "</td> " +
                        "<td>" + format_date(v.tanggal_invoice) + "</td> " +
                        "<td>" + format_date(v.tanggal_jatuh_tempo) + "</td> " +
                        "<td>" + v.uadd_by + " <br>" + format_date(v.date_add) + "</td> " +
                        `<td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="print_inv('` + v.no_inv + `')"><i class="fa fa-print"></i></button>
                                    </div>
                                </td> `
                    tb += "</tr>"
                });

                $("#list_inv-here").append(tb)
            } else {
                $("#list_inv-here").html("<tr><td colspan=5>Tidak ada data</td></tr>");
            }


            //load company profile detail
            // $("#cp_nama_perusahaan").html(r.compro.nama_perusahaan)
            // $("#cp_alamat_perusahaan").html(r.compro.alamat)
            // $("#cp_telp").html(r.compro.telp + " - " + r.compro.telp2)

            //load konsumen detail
            $("#pt_id_mkdt").html(kons.id_mkdt)
            $("#pt_id_konsumen").html(kons.id_konsumen)
            $("#pt_id_kavling").html(kons.id_kavling)
            $("#pt_detail_konsumen").html(
                kons.nama_konsumen + " (" + kons.hp_konsumen + ")" +
                "<br>" + kons.alamat_konsumen
            )
            $("#pt_detail_kavling").html(
                    dt_proyek.nama_proyek + "<br>" + sh.data.nama_jalan + " No. " + sh.data.no_kavling
                )
                // $("#pt_hp_konsumen").html(kons.hp_konsumen)


            /************************ load table tagihan ***************************/
            let tr_tg = "",
                no = 1,
                tot_tg = 0,
                sb_button = "",
                chkd = "",
                tg = r.list_tagihan,
                sudah_bayar = (r.sudah_bayar) ? r.sudah_bayar : 0;


            $.each(tg, function(i, v) {
                chkd = (v.sudah_dibayar == 1) ? "checked" : ""
                sb_button = `<div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" ` + chkd + ` onchange="save_sb(` + v.id_keuangan + `)" class="custom-control-input " disabled id="sb_btn` + v.id_keuangan + `" name="sb_btn[` + v.id_keuangan + `]" value="1" />
                                        <label class="custom-control-label" for="sb_btn` + v.id_keuangan + `"></label>
                                    </div>
                                </div>`;

                tot_tg += parseInt(v.nominal);
                tr_tg += "<tr>" +
                    "<td>" + no + "</td>" +
                    "<td>" + v.berita_acara + "</td>" +
                    "<td>" + format_date(v.jatuh_tempo_tgl) + "</td>" +
                    // "<td>" + sb_button + "</td>" +
                    "<td style='text-align:right'>" + num_format(v.nominal) + "</td>" +
                    "<tr>";
                no++;
            })

            tr_tg += "<tr>" +
                "<th colspan='3' style='text-align:right'>Total Tagihan</th>" +
                "<th style='text-align:right'>" + num_format(tot_tg) + "</th>" +
                "<tr>";

            tr_tg += "<tr>" +
                "<th colspan='3' style='text-align:right'>Sudah Bayar</th>" +
                "<th style='text-align:right'>" + num_format(sudah_bayar) + "</th>" +
                "<tr>";
            tr_tg += "<tr>" +
                "<th colspan='3' style='text-align:right'>Sisa</th>" +
                "<th style='text-align:right'>" + num_format(tot_tg - parseInt(sudah_bayar)) + "</th>" +
                "<tr>";

            $("#tb-print-data-tagihan").append(tr_tg);

            $("#print_tagihan_modal").modal({
                backdrop: 'static',
                keyboard: false,
            })
        },
        error: function() {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
            })
        }
    })
}

function save_inv() {
    $.ajax({
        url: base_url + 'keuangan/save_inv',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            no_inv: $("#no_sruat").val(),
            id_konsumen: $("#pt_id_konsumen").html(),
            id_mkdt: $("#pt_id_mkdt").html(),
            id_kavling: $("#pt_id_kavling").html(),
            id_kopsurat: $("#kopsurat").val(),
            tanggal_invoice: $("#tanggal_surat_tagihan").val(),
            tanggal_jatuh_tempo: $("#pt-tanggal_jatuh_tempo").val(),
            tagihan: $("#tb-print-data-tagihan").html(),
            terms: $("#snk").val(),
        },
        dataType: 'json',
        beforeSend: function() {
            $("#loading").removeClass("hidden");
            $('#form_add_inv-btn').html('Menyimpan');
            $('#form_add_inv-btn').prop("disabled", true);
        },
        success: function(r) {
            csrfHash = r.token;
            $("#loading").addClass("hidden");
            if (r.success === true) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'success',
                    title: r.messages,
                    showConfirmButton: false,
                }).then(function() {
                    print_tagihan()
                        // $('.nav-tabs a[href="#form_list_inv"]').tab('show');
                    $('#form_add_inv-btn').html('Simpan');
                    $('#form_add_inv-btn').prop("disabled", false);
                })
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                }).then(function() {
                    $('#form_add_inv-btn').html('Simpan');
                    $('#form_add_inv-btn').prop("disabled", false);
                })
            }
        },
        error: function() {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan",
                showConfirmButton: true,
                // timer: 1500
            }).then(function() {
                $('#form_add_inv-btn').html('Simpan');
                $('#form_add_inv-btn').prop("disabled", false);
            })
        }
    });
}

function print_inv(e) {
    var myWindow = window.open(base_url + "/keuangan/print_tagihan/?id=" + e, "_blank", "top=100,left=300,width=700,height=600");
    setTimeout(function() {
        myWindow.focus();
    }, 1000);
}

function doPrint() {
    (async() => {
        const rawResponse = await fetch(base_url + 'keuangan/doPrint', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    [csrfName]: csrfHash,
                    konsumen: $("#pt_nama_konsumen").html(),
                    alamat: $("#pt_alamat_konsumen").html(),
                    no_sruat: $("#no_sruat").val(),
                    tanggal_surat_tagihan: $("#tanggal_surat_tagihan").val(),
                    table: $("#tb-print-data-tagihan").html()
                })
            })
            .then(resp => resp.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;

                // the filename you want
                a.download = 'Tagihan ' + $("#pt_nama_konsumen").html() + " " + $("#tanggal_surat_tagihan").val() + '.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);

            })
            .catch(() => alert('oh no!'));

    })();
}

//if pelunasan lebih dari sisa tagihan pelunasan diisi sisa tagihan
// $("#bt-bayar_tagihan_um").change(function () {
//     if (parseFloat(removeComma(this.value)) > parseFloat(removeComma($("#bt-sisa_tagihan_um").val())))
//         $("#bt-bayar_tagihan_um").val($("#bt-sisa_tagihan_um").val())
// })

//simpan status sudah bayar
function save_sb(id) {
    let i = ($("#sb_btn" + id).prop("checked")) ? 1 : 0;
    $.ajax({
        url: base_url + 'keuangan/save_sb',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_keuangan: id,
            sb: i
        },
        dataType: 'json',
        beforeSend: function() {
            $("#loading").removeClass("hidden");
        },
        success: function(r) {
            csrfHash = r.token;
            $("#loading").addClass("hidden");
        }
    });
}
$("#mk-id").select2({
    placeholder: "Pilih Pricelist",
    allowClear: true,
    ajax: {
        url: base_url + "/Hargajual/getAll",
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
            $.each(r.data, function(k, v) {
                results.push({
                    id: v.id,
                    text: "Rp. " + num_format(v.hargajual) + " (" + v.tipe_rumah + ")" + ": Per " + format_date(v.tgl_harga),
                    row: v.row,
                    tipe: v.tipe_rumah,
                    lb: v.lb,
                    lt: v.lt,
                    hargajual: v.hargajual,
                    kpr: v.kpr,
                    uang_muka: v.uang_muka,
                    bphtb: v.bphtb,
                    biaya_adm: v.biaya_adm,
                    biaya_proses: v.biaya_proses,
                    id_tipe: v.id_tipe,
                    tgl_harga: format_date(v.tgl_harga)
                });
            });

            return {
                results: results
            };
        },
        cache: false
    },
})
$("#mk-id").on("select2:selecting", function(e) {
    // if (Object.keys(data_um).length > 0 || Object.keys(data_bb).length > 0) {
    //     Swal.fire({
    //         title: 'Lakukan perubahan?',
    //         text: "data pada tabel tagihan akan terhapus!",
    //         type: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#3085d6',
    //         cancelButtonColor: '#d33',
    //         confirmButtonText: 'Ya!',
    //         confirmButtonClass: 'btn btn-primary',
    //         cancelButtonClass: 'btn btn-danger ml-1',
    //         buttonsStyling: !1
    //     }).then(function (t) {
    //         if (t.value) {
    //             var i = e.params.args.data
    //             $.each(i, function (k, v) {
    //                 $("#mk-" + k).val(v).change().keyup()
    //             })
    //             sum_mktotal()

    //             data_um = {}
    //             data_bb = {}
    //             $("#list_cicilan_here").html("")
    //             $("#total_cicilan_um").val(0).change().keyup()
    //             $("#total_cicilan_bb").val(0).change().keyup()
    //             $("#id_list_keu").val('');
    //             $("#id_list_keu_bb").val('');
    //         } else
    //             return false
    //     })
    // } else {
    //     var i = e.params.args.data
    //     $.each(i, function (k, v) {
    //         $("#mk-" + k).val(v).change().keyup()
    //     })
    //     sum_mktotal()
    // }

});
$("#mk-id").change(function() {
    if (!this.value)
        $(".mk-fm").val("")
})
$("#mk-harga_ppn, #mk-harga_penambahan, #mk-harga_penambahan_tanah, #mk-diskon").on('focusin', function() {
        $(this).data('val', $(this).val());
    })
    // $("#mk-harga_ppn, #mk-harga_penambahan, #mk-diskon").change(function () {
    //     var prev = $(this).data('val'),
    //         current = $(this).val(),
    //         th = $(this);

//     if (Object.keys(data_um).length > 0 || Object.keys(data_bb).length > 0) {
//         Swal.fire({
//             title: 'Lakukan perubahan?',
//             text: "data pada tabel tagihan akan terhapus!",
//             type: 'warning',
//             showCancelButton: true,
//             confirmButtonColor: '#3085d6',
//             cancelButtonColor: '#d33',
//             confirmButtonText: 'Ya!',
//             confirmButtonClass: 'btn btn-primary',
//             cancelButtonClass: 'btn btn-danger ml-1',
//             buttonsStyling: !1
//         }).then(function (t) {
//             if (t.isConfirmed) {
//                 sum_mktotal()
//                 data_um = {}
//                 data_bb = {}
//                 $("#list_cicilan_here").html("")
//                 $("#total_cicilan_um").val(0).change().keyup()
//                 $("#total_cicilan_bb").val(0).change().keyup()
//                 $("#id_list_keu").val('');
//                 $("#id_list_keu_bb").val('');
//             } else
//                 th.val(prev)
//         })
//     } else
//         sum_mktotal()
// })
$(".mk-fm, #idk-is_subsidi").change(function() {
    sum_mktotal()
})

function sum_mktotal() {
    let totalum = 0,
        totalbb = 0,
        hj = parseFloat(removeComma($("#mk-hargajual").val()) || 0),
        diskon_hj = parseFloat(removeComma($("#mk-diskon_harga_jual").val()) || 0),
        hj_net = parseFloat(removeComma($("#mk-hargajual_net").val()) || 0),
        kpr = parseFloat(removeComma($("#mk-kpr").val()) || 0),
        um = parseFloat(removeComma($("#mk-uang_muka").val()) || 0),
        diskon_um = parseFloat(removeComma($("#mk-diskon_uang_muka").val()) || 0),
        badm = parseFloat(removeComma($("#mk-biaya_adm").val()) || 0),
        ppn = parseFloat(removeComma($("#mk-ppn").val()) || 0),
        bphtb = parseFloat(removeComma($("#mk-bphtb").val()) || 0),
        bproses = parseFloat(removeComma($("#mk-biaya_proses").val()) || 0),

        hj_real = 0,
        persentase_kpr = ($("#idk-is_subsidi").val() == 1) ? 0.05 : 0.1, //persentase kpr
        penambahan_biaya = parseFloat(removeComma($("#mk-harga_penambahan").val()) || 0),
        penambahan_biaya_tanah = parseFloat(removeComma($("#mk-harga_penambahan_tanah").val()) || 0),
        penambahan_biaya_um = parseFloat(removeComma($("#mk-harga_penambahan_um").val()) || 0);

    // hj_net = hj - diskon_hj
    // um = hj_net - kpr

    // kpr = hj - (hj * persentase_kpr)

    $("#mk-hargajual_net").val(hj_net).keyup()
        // $("#mk-kpr").val(kpr).keyup()

    // um = hj_net - kpr 
    // totalum = um + badm + penambahan_biaya_um - diskon_um + penambahan_biaya
    totalum = um + badm + penambahan_biaya + penambahan_biaya_tanah + penambahan_biaya_um - diskon_um
    totalbb = ppn + bphtb + bproses

    // alert(totalbb)
    $(".tum").val(totalum).keyup();
    $(".tbb").val(totalbb).keyup();
}

$('#isi_tagihan-modal').on('hidden.bs.modal', function() {
    data_um = {}
    data_bb = {}
})

function simpan_dt_konsumen_keuangan(e) {
    if (!palid("idk-nama_konsumen", "", "Nama konsumen harus diisi"))
        return;
    if (!palid("idk-status_mkdt", "", "Status harus diisi"))
        return;

    if (parseFloat(removeComma($("#total_cicilan_um").val() || 0)) > 0) {
        if ($("#total_cicilan_um").val() != $("#mk-total_um").val()) {
            return swal('error', 'Total tagihan Uang Muka tidak sesuai dengan Total Uang Muka')
        }
    }
    if (parseFloat(removeComma($("#total_cicilan_bb").val() || 0)) > 0) {
        if ($("#total_cicilan_bb").val() != $("#mk-total_bb").val()) {
            return swal('error', 'Total tagihan Biay-biaya tidak sesuai dengan total biaya biaya')
        }
    }

    let dt = {}
    dt[csrfName] = csrfHash
    $("form#fm-idk_keu :input").each(function() {
        dt[this.name] = this.value
    });

    let i = 0
        //cicilan um


    let form = $("#fm-idk_keu")[0];
    let fd = new FormData(form);
    fd.append(csrfName, csrfHash);

    if (is_ganti_nama) {
        fd.append('id_mkdt_old', id_mkdt_old)
        fd.append('id_konsumen_old', id_konsumen_old)
        fd.append('is_ganti_nama', is_ganti_nama)
    }


    for (var k in data_um) {
        if (!data_um.hasOwnProperty(k)) continue;
        var obj = data_um[k];

        for (var d in obj) {
            if (!obj.hasOwnProperty(d)) continue;
            var x = obj[d];

            dt[d + "[" + i + "]"] = is_ganti_nama == 'Ganti Nama' ? '' : x;
            fd.append(`${d}[${i}]`, x);
        }
        i++;
    }
    //cicilan bb
    i = 0
    for (var k in data_bb) {
        if (!data_bb.hasOwnProperty(k)) continue;
        var obj = data_bb[k];

        for (var d in obj) {
            if (!obj.hasOwnProperty(d)) continue;
            var x = obj[d];
            dt[d + "[" + i + "]"] = is_ganti_nama == 'Ganti Nama' ? '' : x;
            fd.append(`${d}[${i}]`, x);
        }
        i++;
    }

    $.ajax({
        url: base_url + 'keuangan/save_kons',
        type: 'post',
        contentType: false,
        processData: false,
        data: fd,
        dataType: 'json',
        beforeSend: function() {
            simpanBtn("#add-form-btn-idk_keu", true);
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
                    simpanBtn("#add-form-btn-idk_keu", false);
                })
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    simpanBtn("#add-form-btn-idk_keu", false);
                })
            }
            load_kavling();
            hapus_seleksi();
        },
        error: function(e) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan",
                showConfirmButton: true,
                // timer: 1500
            }).then(function() {
                simpanBtn("#add-form-btn-idk_keu", false);
            })
        }
    });

}

$("#refresh-btn-idk_keu").click(function() {
    $("#fm-idk_keu .num").prop('disabled', false);

    $("#idk_data_baru").val(1);
    // $("#fm-idk_keu")[0].reset();

    // refresh_fmmkdt(false);
    $("#fm-idk_keu input:text, #fm-idk_keu select, #fm-idk_keu textarea").prop("disabled", false);
    $("#fm-idk_keu #idk-id_konsumen").val("");

    $("#idk-show_keterangan_batal").addClass('hidden');

})

// $("#mk-keterangan_harga_penambahan").change(function () {
//     // console.log(this.value)
//     if (this.value)
//         $("#berita_acara").append(`<option id='opt-keterangan_harga_penambahan'>${this.value}</option>`)
//     else
//         $("#berita_acara option[id='opt-keterangan_harga_penambahan']").remove()
// })

$("#berita_acara").change(function() {
    let nom = 0;
    switch (this.value) {
        case 'Uang Muka':
            nom = parseFloat(removeComma($("#mk-uang_muka").val())) - parseFloat(removeComma($("#mk-diskon_uang_muka").val()))
            break;
        case 'Biaya Administrasi':
            nom = parseFloat(removeComma($("#mk-biaya_adm").val()))
            break;
        case 'Turun KPR':
            nom = parseFloat(removeComma($("#mk-harga_penambahan_um").val()))
            break;
        case 'Biaya Kavling Strategis':
            nom = parseFloat(removeComma($("#mk-harga_penambahan").val()))
            break;
        case 'Biaya Kelebihan Tanah':
            nom = parseFloat(removeComma($("#mk-harga_penambahan_tanah").val()))
            break;
        default:
            nom = 0
            break;
    }
    changeVal("#nominal", nom)
})
$("#berita_acara_bb").change(function() {
    let nom = 0;
    switch (this.value) {
        case 'PPN':
            nom = parseFloat(removeComma($("#mk-ppn").val()))
            break;
        case 'BPHTB':
            nom = parseFloat(removeComma($("#mk-bphtb").val()))
            break;
        case 'Biaya Proses':
            nom = parseFloat(removeComma($("#mk-biaya_proses").val()))
            break;
        default:
            nom = 0
            break;
    }
    changeVal("#nominal_bb", nom)
})

function isi_data_konsumen() {
    id_mkdt_old = null
    id_konsumen_old = null
    is_ganti_nama = false

    // $("#berita_acara option[id='opt-keterangan_harga_penambahan']").remove()

    if (!editdtt[0]) {
        return swal('error', 'Tidak ada kavling yang dipilih')
    }
    let role,
        sh = editdtt[0],
        id_kavling = sh.id.substr(3),
        id_hargajual = sh.data2.id_hargajual;

    if (sh.data.tipe != "kavling") {
        return swal('error', "Tidak ada kavling terpilih ")
    }
    if (sh.data2.harga_akhir == "-") {
        return swal('error', 'Kavling belum dipasarkan (tidak ada harga jual)')
    }

    data_um = {}
    data_bb = {}
    $("#list_cicilan_here").html("")
    $("#total_cicilan_um").val(0).change().keyup()
    $("#total_cicilan_bb").val(0).change().keyup()
    $("#id_list_keu").val('');
    $("#id_list_keu_bb").val('');
    $("#mk-total_bb").val(0)
    $("#mk-total_um").val(0)


    $("#fm-idk_keu .num").prop('disabled', false);

    $("#idk_data_baru").val(1);
    $("#fm-idk_keu")[0].reset();

    $("#mk-diskon_harga_jual, #mk-diskon_uang_muka").val(0);


    // refresh_fmmkdt(false);
    $("#fm-idk_keu input:text, #fm-idk_keu select, #fm-idk_keu textarea").prop("disabled", false);
    $("#fm-idk_keu #idk-id_konsumen").val("");

    $(".delete_kons_div").addClass("hidden");

    $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").addClass('hidden');

    // $("#fm-mkdt .num").val(0)

    $("#btn-print_spptb").prop("href", "#")

    $('#idk-rincian').prev('.richText-editor').trigger('setContent', '');


    $(".id_kavling").val(id_kavling);
    $("#idk-id_mkdt").val(sh.data.id_mkdt);

    $.ajax({
        url: base_url + 'keuangan/get_data_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_mkdt: sh.data.id_mkdt,
            id_kavling: id_kavling,
            id_hargajual: id_hargajual
        },
        dataType: 'json',
        beforeSend: function() {
            $("#loading").removeClass("hidden");
        },
        success: function(x) {
            $("#loading").addClass("hidden");
            csrfHash = x.token;
            let v = x.data, //data mkdt
                h = x.hj, //pricelist
                tg = x.tagihan,
                dk = x.diskresi

            let u_hj = (dk.username_harga_akhir) ? dk.username_harga_akhir : "-";

            if (dk.username_diskresi) {
                $("#idk-diskresi_st").removeClass('hidden')

                changeVal("#idk-diskresi_harga", dk.diskresi_harga)
                changeVal("#idk-diskresi_memo", dk.diskresi_memo)
                $("#idk-diskresi_oleh").text(dk.username_diskresi)
                $("#idk-diskresi_tgl").text(format_date(dk.diskresi_at))

            } else {
                $("#idk-diskresi_st").addClass('hidden')
                changeVal("#idk-diskresi_harga", "-")
                changeVal("#idk-diskresi_memo", "-")
                $("#idk-diskresi_oleh").text('-')
                $("#idk-diskresi_tgl").text('-')
            }


            //load hargajual 
            if (h.hargajual) {
                $.each(h, function(k, v) {
                    $("#mk-" + k).val(v).change().keyup();
                })

                setDatePicker(h.tgl_harga, "#mk-tgl_harga")
                $("#idk-tgl_harga").val(format_date(h.tgl_harga));
                $("#idk-harga_kpr").val(h.kpr).change();

                changeVal("#idk-mkdt_hargajual", h.hargajual)
                $("#idk-mkdt_hargajual_by").text(u_hj)
                $("#idk-mkdt_hargajual_tgl").text(format_date(dk.harga_akhir_tgl))
            }

            //load link print spptb
            if (sh.data.id_mkdt == null) {
                $("#btn-print_spptb").attr("onclick", `return swal('error', 'Data konsumen harus disimpang terlebih dahulu');`)
                $("#btn-print_spptb").attr("target", '')
            } else {
                $("#btn-print_spptb").attr("onclick", ``)
                $("#btn-print_spptb").prop("href", base_url + 'keuangan/printSPPTB?id_mkdt=' + sh.data.id_mkdt + '&id_kavling=' + id_kavling + '&id_proyek=' + dt_proyek.id_proyek)
                $("#btn-print_spptb").attr("target", '_blank')
            }

            $("#idk-harga_akhir").val(id_hargajual)

            $("#idk-file_ktp-here, #idk-file_npwp-here, #idk-file_data_diri-here").html("Tidak ada data")
                // $("#idk-file_ktp-here, #idk-file_npwp-here, #idk-file_data_diri-here").prop("href", "#");
                // $("#idk-file_ktp-here, #idk-file_npwp-here, #idk-file_data_diri-here").prop("onclick", 'return swal("error", "Tidak ada data");');

            if (v) {

                src = not_found;
                //load ktp npwp
                if (v.ktp_lok != null) {
                    src = v.ktp_lok;
                    $("#idk-file_ktp-here").html(`<img src = "${base_url + src}" width="100%" height="150px"/>`);
                }
                $("#idk-file_ktp-here").prop("href", base_url + src);
                // $("#idk-file_ktp-here").prop("onclick", "");

                //load npwp
                src = not_found;
                if (v.npwp_lok != null) {
                    src = v.npwp_lok;
                    $("#idk-file_npwp-here").html(`<img src = "${base_url + src}" width="90%" height="150px"/>`);
                }
                $("#idk-file_npwp-here").prop("href", base_url + src);
                // $("#idk-file_npwp-here").prop("onclick", "");

                //load data diri
                src = not_found;
                if (v.data_diri_lok != null) {
                    src = v.data_diri_lok;
                    $("#idk-file_data_diri-here").html('Klik untuk melihat file');
                }
                $("#idk-file_data_diri-here").prop("href", base_url + src);
                // $("#idk-file_data_diri-here").prop("onclick", "");

                if (v.status_mkdt == "Batal") {
                    $("#fm-idk_keu input:text, #fm-idk_keu select, #fm-idk_keu textarea").prop("disabled", true);
                    $("#fm-idk_keu #idk-id_konsumen").val("");
                    $("#fm-idk_keu #idk-id_keuangan0").val("");

                    $(".refresh_fmmkdt_div").removeClass("hidden");
                    // $(".delete_kons_div").addClass("hidden");

                    $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").removeClass('hidden');
                    // $("#delete_kons_div").addClass("hidden");
                }

                if (v.id_konsumen)
                    $("#idk_data_baru").val(0);

                $("#fm-idk_keu #idk-no_spptb").val(v.no_spptb)
                $("#fm-idk_keu #idk-status_mkdt").val(v.status_mkdt)
                $("#fm-idk_keu #idk-keterangan_batal").val(v.keterangan_batal)
                if (v.booking_tgl != "0000-00-00")
                    document.querySelector("#idk-booking_tgl")._flatpickr.setDate(v.booking_tgl);
                $("#fm-idk_keu #idk-booking_fee").val(v.booking_fee)

                $("#fm-idk_keu #idk-id_konsumen").val(v.id_konsumen)
                $("#fm-idk_keu #idk-nama_konsumen").val(v.nama_konsumen)
                $("#fm-idk_keu #idk-alamat_konsumen").val(v.alamat_konsumen)
                $("#fm-idk_keu #idk-nik_konsumen").val(v.nik_konsumen)
                $("#fm-idk_keu #idk-npwp_konsumen").val(v.npwp_konsumen)
                $("#fm-idk_keu #idk-hp_konsumen").val(v.hp_konsumen)
                $("#fm-idk_keu #idk-email_konsumen").val(v.email_konsumen)
                $("#fm-idk_keu #idk-status_konsumen").val(v.status_konsumen)

                $("#fm-idk_keu #idk-status_pernikahan").val(v.status_pernikahan)
                $("#fm-idk_keu #idk-nama_pasangan").val(v.nama_pasangan)
                $("#fm-idk_keu #idk-nik_pasangan").val(v.nik_pasangan)
                $("#fm-idk_keu #idk-nama_instansi").val(v.nama_instansi)
                $("#fm-idk_keu #idk-alamat_instansi").val(v.alamat_instansi)
                $("#fm-idk_keu #idk-tel_instansi").val(v.tel_instansi)
                $("#fm-idk_keu #idk-sales").val(v.sales)
                $("#fm-idk_keu #idk-is_kpr").val(v.is_kpr)
                $("#fm-idk_keu #idk-is_subsidi").val(v.is_subsidi)
                $("#fm-idk_keu #idk-jenis_subsidi").val(v.jenis_subsidi)


                $('#idk-rincian').prev('.richText-editor').trigger('setContent', v.rincian);
                $("#fm-idk_keu #idk-rincian").html(v.rincian)
                    // $('#id_textarea').richText();

                if (v.keuangan_saved_by) {
                    //harga_jual
                    $("#fm-idk_keu #mk-hargajual").val(v.harga_jual)
                    $("#fm-idk_keu #mk-hargajual_net").val(v.harga_jual_net)
                    $("#fm-idk_keu #mk-kpr").val(v.harga_kpr)
                    $("#fm-idk_keu #mk-uang_muka").val(v.harga_uang_muka)
                    $("#fm-idk_keu #mk-biaya_adm").val(v.harga_administrasi)
                    $("#fm-idk_keu #mk-bphtb").val(v.harga_bphtb)
                    $("#fm-idk_keu #mk-ppn").val(v.harga_ppn)
                    $("#fm-idk_keu #mk-biaya_proses").val(v.harga_biaya_proses)
                    $("#fm-idk_keu #mk-harga_penambahan").val(v.harga_penambahan)
                    $("#fm-idk_keu #mk-harga_penambahan_tanah").val(v.harga_penambahan_tanah)
                }



                $("#fm-idk_keu #idk-promo").val(v.promo)


                //kpr
                $("#mk-harga_kpr_acc").val(v.harga_kpr_acc).change().keyup()

                let turun_kpr = (v.harga_kpr_acc == 0) ? 0 : v.harga_kpr - v.harga_kpr_acc;
                $("#mk-harga_penambahan_um").val(turun_kpr).change().keyup()



                //
                let spptb_ttd_file = v.file_spptb ? `<a href="${base_url + v.file_spptb}" target=_blank class="btn btn-outline-primary">Klik untuk melihat File SPPTB Yang Sudah ditandatangan</a>` : `Tidak ada data`
                $("#spptb_ttd_file").html(spptb_ttd_file)



            }
            let list_spptb = '<tr><td colspan="3"> Tidak ada data</td></tr>';
            if (x.list_spptb) {
                list_spptb = ""
                $.each(x.list_spptb, function(index, val) {
                    list_spptb += `
                        <tr>
                            <td>${index + 1}</td>
                            <td><a href="${base_url + val.lokasi}" target=_blank>Klik untuk melihat file</a></td>
                            <td>${val.username + '<br>' + format_datetime(val.created_at)}</td>
                         </tr>
                            `
                });
            }
            $("#spptb_ttd_file-here").html(list_spptb)

            if (tg) {
                let a = it;
                $.each(tg, function(i, v) {
                    if (v.status == "UM") {
                        data_um['lk' + a] = ({
                            id_list_keu: 'lk' + a,
                            id_keuangan: (v.id_keuangan),
                            berita_acara: (v.berita_acara),
                            nominal: num_format(v.nominal),
                            jatuh_tempo_tgl: (v.jatuh_tempo_tgl),
                        })
                    }
                    if (v.status == "BB") {
                        data_bb['lk' + a] = ({
                            id_list_keu_bb: 'lk' + a,
                            id_keuangan_bb: (v.id_keuangan),
                            berita_acara_bb: (v.berita_acara),
                            nominal_bb: num_format(v.nominal),
                            jatuh_tempo_tgl_bb: (v.jatuh_tempo_tgl),
                        })
                    }

                    a++;
                })
                tambah_ketagihan()
                it = a;
            }

            sum_mktotal()
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal-isi_data_konsumen').modal({
                backdrop: 'static',
                keyboard: false
            });

        },
        error: function(xhr, st, err) {
            $("#loading").addClass("hidden");
            return swal("error", err);
        }
    });
}

// function ganti_kavling(){
//     if ($("#spptb_ttd_file").html() == 'Tidak ada data') {
//         return swal('error', 'Kamu harus mengunggah file SPPTB yang sudah ditandatangani')
//     }
//     Swal.fire({
//         title: 'Pindah kavling?',
//         text: "Apakah anda yakin akan memindahkan kavling?",
//         // type: 'warning',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         confirmButtonText: 'Ya!',
//         confirmButtonClass: 'btn btn-primary',
//         cancelButtonClass: 'btn btn-danger ml-1',
//         buttonsStyling: !1
//     }).then(function (t) {
//         if (t.isConfirmed) {
//             id_mkdt_old = $("#idk-id_mkdt").val()
//             id_konsumen_old = $("#idk-id_konsumen").val()
//             is_ganti_nama = 'Pindah Kavling'

//             $("#btn-print_spptb").prop("href", "#")
//             $("#idk-id_konsumen, #idk-id_mkdt").val("")
//             $("#idk_data_konsumen-tab").click()
//         }
//     })
// }
function ganti_nama() {
    if ($("#spptb_ttd_file").html() == 'Tidak ada data') {
        return swal('error', 'Kamu harus mengunggah file SPPTB yang sudah ditandatangani')
    }
    Swal.fire({
        title: 'Ganti nama konsumen?',
        text: "Apakah anda yakin akan mengganti nama konsumen?",
        // type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya!',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: !1
    }).then(function(t) {
        if (t.isConfirmed) {
            id_mkdt_old = $("#idk-id_mkdt").val()
            id_konsumen_old = $("#idk-id_konsumen").val()
            is_ganti_nama = 'Ganti Nama'

            $("#btn-print_spptb").prop("href", "#")
            $(".gn, #idk-id_konsumen, #idk-id_mkdt").val("")
            $("#idk_data_konsumen-tab").click()
        }
    })
}

//sudah tidak dipakai
function isi_tagihan() {
    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    if (sh.data2.status_mkdt == 'Batal') {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Status konsumen batal",
            text: "Silahkan isi kavling dengan konsumen baru terlebih dahulu",
            // showConfirmButton: false,
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

    data_um = {}
    data_bb = {}
    $("#fm-isi_tagihan")[0].reset()
    $("#list_cicilan_here").html("")
    $("#total_cicilan_um").val(0).change().keyup()
    $("#total_cicilan_bb").val(0).change().keyup()
    $("#id_list_keu").val('');
    $("#id_list_keu_bb").val('');

    // $("#cicilan_belong_here").html("");
    // $("#berita_acara0").val("Uang Muka 1");
    // $("#nominal0").val(0).keyup();

    $.ajax({
        url: base_url + 'keuangan/get_data_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_keuangan: sh.data.id_keuangan,
            id_kavling: id_kavling,
            id_mkdt: sh.data.id_mkdt
        },
        dataType: 'json',
        beforeSend: function() {
            $("#loading").removeClass("hidden")
        },
        success: function(r) {
            $("#loading").addClass("hidden")
            let mkdt = r.mkdt,
                hj = r.hj,
                tg = r.tagihan

            $("#mk-id_mkdt").val(sh.data.id_mkdt)

            $('.select2').val(null).trigger('change');
            if (hj.id) {
                // if (hj.id) {

                for (let i in hj) {
                    $("#fm-isi_tagihan #mk-" + i).val(hj[i]).change().keyup();
                }
                $("#fm-isi_tagihan #mk-tgl_harga").val(format_date(hj.tgl_harga));
                $("#fm-isi_tagihan #mk-tipe").val(hj.tipe_rumah);
                $("#mk-id").append(
                    $("<option selected></option>")
                    .attr("value", hj.id)
                    .text("Rp. " + num_format(hj.hargajual) + " (" + hj.tipe_rumah + ")" + ": Per " + hj.tgl_harga)
                ).trigger('change');
                // } else {
                //     $(".mk-fm").val(0)
                // }
            } else {
                $("#mk-id").append(
                    $("<option selected></option>")
                    .attr("value", mkdt.id_hargajual)
                    .text("Rp. " + num_format(mkdt.harga_jual) + " (" + mkdt.tipe_rumah + ")" + ": " + mkdt.tgl_harga)
                ).trigger('change');

                $("#fm-isi_tagihan #mk-tgl_harga").val(format_date(mkdt.tgl_harga));
                $("#mk-row").val(mkdt.row).change()
                $("#mk-tipe").val(mkdt.tipe_rumah).change()
                $("#mk-lb").val(mkdt.hj_lb).change()
                $("#mk-lt").val(mkdt.hj_lt).change()

                $("#mk-hargajual").val(mkdt.harga_jual).change()
                $("#mk-kpr").val(mkdt.harga_kpr).change()
                $("#mk-uang_muka").val(mkdt.harga_jual - mkdt.harga_kpr).change()
                $("#mk-bphtb").val(mkdt.harga_bphtb).change()
                $("#mk-biaya_adm").val(mkdt.harga_administrasi).change()
                $("#mk-biaya_proses").val(mkdt.harga_biaya_proses).change()
            }
            $("#mk-diskon").val(mkdt.harga_diskon).change().keyup()
            $("#mk-harga_penambahan").val(mkdt.harga_penambahan).change().keyup()
            $("#mk-harga_penambahan_tanah").val(mkdt.harga_penambahan_tanah).change().keyup()
            $("#mk-keterangan_harga_penambahan").val(mkdt.keterangan_penambahan_biaya)


            $("#mk-harga_ppn").val(mkdt.harga_ppn).change().keyup()
            $("#mk-harga_kpr_acc").val(mkdt.harga_kpr_acc).change().keyup()

            let turun_kpr = (mkdt.harga_kpr_acc == 0) ? 0 : mkdt.harga_kpr - mkdt.harga_kpr_acc;
            $("#mk-harga_penambahan_um").val(turun_kpr).change().keyup()

            sum_mktotal()

            //load tagihan
            if (tg) {
                let a = it;
                $.each(tg, function(i, v) {
                    if (v.status == "UM") {
                        data_um['lk' + a] = ({
                            id_list_keu: 'lk' + a,
                            id_keuangan: (v.id_keuangan),
                            berita_acara: (v.berita_acara),
                            nominal: num_format(v.nominal),
                            jatuh_tempo_tgl: (v.jatuh_tempo_tgl),
                        })
                    }
                    if (v.status == "BB") {
                        data_bb['lk' + a] = ({
                            id_list_keu_bb: 'lk' + a,
                            id_keuangan_bb: (v.id_keuangan),
                            berita_acara_bb: (v.berita_acara),
                            nominal_bb: num_format(v.nominal),
                            jatuh_tempo_tgl_bb: (v.jatuh_tempo_tgl),
                        })
                    }

                    a++;
                })
                tambah_ketagihan()
                it = a;
            }

            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#isi_tagihan-modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            //load data form
            // for (let i in mkdt) {
            //     $("#fm-isi_tagihan #" + i).val(mkdt[i]);
            // }

            // if (r.tagihan) {
            //     it = 0;
            //     let tg = r.tagihan
            //     if (tg.length > 0) {
            //         for (i = 0; i < tg.length; i++) {
            //             if (i > 0)
            //                 tambah();

            //             fp = flatpickr("#fm-isi_tagihan #jatuh_tempo_tgl" + i, {
            //                 altInput: true,
            //                 altFormat: 'F j, Y',
            //                 dateFormat: 'Y-m-d'
            //             });
            //             // console.log(tg[i]['id_keuangan']);
            //             $("#fm-isi_tagihan #id_keuangan" + i).val(tg[i]['id_keuangan']);
            //             $("#fm-isi_tagihan #nominal" + i).val(tg[i]['nominal']).keyup().change();
            //             $("#fm-isi_tagihan #berita_acara" + i).val(tg[i]['berita_acara']);
            //             document.querySelector("#fm-isi_tagihan #jatuh_tempo_tgl" + i)._flatpickr.setDate(tg[i]['jatuh_tempo_tgl']);
            //         }
            //     }
            // }
            // total('#fm-isi_tagihan');
        },
        error: function() {
            $("#loading").addClass("hidden")
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
}


// $("#mk-jenis-diskon").change(function () {
//     if (this.value == "Harga Jual") {
//         $("#hjdis").removeClass("hidden")
//         // $("#umdis").addClass("hidden")
//     } else if (this.value == "Uang Muka") {
//         $("#hjdis").addClass("hidden")
//         // $("#umdis").removeClass("hidden")
//     }
//     sum_mktotal()
// })
$("#add-form-isi-tagihan, #btn-ganti_nama, #add-form-btn-idk_keu, #btn-ganti_kavling").click(function(e) {
    e.preventDefault();
});

//sudah tidak di pakai
function save_isi_tagihan(e) {
    if (parseFloat(removeComma($("#total_cicilan_um").val() || 0)) > 0 || parseFloat(removeComma($("#total_cicilan_bb").val() || 0)) > 0) {
        if ($("#total_cicilan_um").val() != $("#mk-total_um").val() || $("#total_cicilan_bb").val() != $("#mk-total_bb").val()) {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Total Cicilan tidak sesuai dengan total biaya",
                showConfirmButton: false,
                timer: 1500
            });
            return false;
        }
    }
    let dt = {}
    dt[csrfName] = csrfHash
    $("form#fm-idk_keu :input").each(function() {
        dt[this.name] = this.value
    });

    let i = 0
    for (var k in data_um) {
        if (!data_um.hasOwnProperty(k)) continue;
        var obj = data_um[k];

        for (var d in obj) {
            if (!obj.hasOwnProperty(d)) continue;
            var x = obj[d];
            dt[d + "[" + i + "]"] = x
        }
        i++;
    }
    i = 0
    for (var k in data_bb) {
        if (!data_bb.hasOwnProperty(k)) continue;
        var obj = data_bb[k];

        for (var d in obj) {
            if (!obj.hasOwnProperty(d)) continue;
            var x = obj[d];
            dt[d + "[" + i + "]"] = x
        }
        i++;
    }

    $.ajax({
        url: base_url + 'Keuangan/isi_tagihan',
        type: 'post',
        data: dt,
        dataType: 'json',
        beforeSend: function() {
            $('#add-form-isi-tagihan').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            $('#add-form-isi-tagihan').addClass("disabled");
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
                    // $('.modal').modal('hide');
                    load_kavling();
                    hapus_seleksi();
                    $('#add-form-isi-tagihan').html('Simpan');
                    $('#add-form-isi-tagihan').removeClass("disabled");
                })
            } else {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    // timer: 1500
                }).then(function() {
                    $('#add-form-isi-tagihan').html('Simpan');
                    $('#add-form-isi-tagihan').removeClass("disabled");
                })
            }
        },
        error: function() {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "terjadi kesalahan",
                showConfirmButton: false,

            }).then(function() {
                // $('.modal').modal('hide');
                $('#add-form-isi-tagihan').html('Simpan');
                $('#add-form-isi-tagihan').removeClass("disabled");
            })
            $('#add-form-isi-tagihan').html('Simpan');
            $('#add-form-isi-tagihan').removeClass("disabled");
        }
    });

}

$("#bt-for, #bt-for_bb").select2()
let keu_tg, keu_lp, keu_nom_ll, keu_nom_bb, keu_sb

function open_keuangan(sh, role, id_kavling) {
    $('#booking-tab').tab('show');

    $("#fm-keuangan")[0].reset(); //reset form
    $("#label_konsumen").html(""); //reset label nama
    $("#tb-data-log_pembayaran, #tb-data-log_pembayaran_bb, #tb-data-tagihan, #tb-data-tagihan_bb").empty(); //reset table log
    $("#booking_fee_paid, #keu_booking_fee").prop("disabled", false); //set disabled false untuk input booking 

    document.querySelector("#keu_booking_tgl")._flatpickr._input.disabled = false; //set disabled false untuk input tanggal booking 

    $("#hide_lunas").removeClass("hidden");
    $("#hide_refund").addClass("hidden");

    $("#is_lunas").prop("checked", false).change()

    $(".id_kavling").val(id_kavling);
    $("#fm-keuangan #id_mkdt").val(sh.data.id_mkdt);

    $('#add-form-btn-keuangan').prop("disabled", false);
    $("#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid").prop("disabled", 0);
    document.querySelector("#tanggal_refund")._flatpickr._input.disabled = false;

    $.ajax({
        url: base_url + 'keuangan/getTagihan',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_keuangan: sh.data.id_keuangan,
            id_kavling: id_kavling,
            id_mkdt: sh.data.id_mkdt,
            id_hargajual: sh.data2.id_hargajual
        },
        dataType: 'json',
        beforeSend: function() {
            $("#loading").removeClass("hidden");
        },
        success: function(r) {
            $("#loading").addClass("hidden");
            let mkdt = r.mkdt,
                sb = r.log_pembayaran,
                lp = r.log_pembayaran,
                disabled = ""
            tg = r.tagihan;
            csrfHash = r.token;

            // return

            if (!tg.length) {
                Swal.fire({
                    //position: 'bottom-end',
                    icon: 'error',
                    title: "Terjadi kesalahan saat memuat data",
                    text: "Isi tagihan terlebih dahulu",
                    showConfirmButton: false,
                })
                return;
            }

            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_divisi' + role).modal({
                backdrop: 'static',
                keyboard: false
            });

            //load detail biaya dari mkdt
            $("#label_konsumen").html(mkdt.nama_konsumen);
            if (mkdt) {
                $("#fm-keuangan #status_mkdt").val(mkdt.status_mkdt);

                //jika status batal
                if (mkdt.status_mkdt == "Batal") {
                    $("#hide_lunas").addClass("hidden");
                    $("#hide_refund").removeClass("hidden");
                }

                //matikan tombol simpan jika sudah refund
                if (mkdt.refund_paid == 1) {
                    $('#add-form-btn-keuangan').prop("disabled", true);
                    $("#hide_lunas").addClass("hidden");
                    $("#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid").prop("disabled", 1);
                    $("#fm-keuangan #refund_paid").prop("checked", 1);

                    $("#keterangan_refund").val(mkdt.refund_keterangan).change();
                    $("#nominal_refund").val(mkdt.refund).change();

                    setDatePicker(mkdt.refund_tgl, "#tanggal_refund")
                        // if (mkdt.refund_tgl != "0000-00-00")
                        //     document.querySelector("#tanggal_refund")._flatpickr.setDate(mkdt.refund_tgl);
                    document.querySelector("#tanggal_refund")._flatpickr._input.disabled = true;

                    disabled = "disabled"
                }

                //matikan tombol simpan jika sudah lunas
                // if (mkdt.is_lunas == 1) {
                //     $("#is_lunas").prop("checked", true)
                //     $(".hide_lunas").addClass("hidden");
                //     disabled = "disabled"
                // }

                $("#fm-keuangan #nama_konsumen").val(mkdt.nama_konsumen);

                $("#bt-total_biaya_um").val(parseFloat(mkdt.harga_uang_muka) - parseFloat(mkdt.harga_diskon_uang_muka)).change()
                $("#bt-total_biaya_um_ll").val(parseFloat(mkdt.harga_penambahan_um) + parseFloat(mkdt.harga_penambahan) + parseFloat(mkdt.harga_penambahan_tanah) + parseFloat(mkdt.harga_administrasi)).change()

                $("#bt-total_biaya_bb").val(parseFloat(mkdt.harga_bphtb) + parseFloat(mkdt.harga_biaya_proses) + parseFloat(mkdt.harga_ppn)).change()

                //set checnkbox value to prevent being 0 by automatic load data 
                $("#fm-keuangan #is_lunas").val(1);

                setDatePicker(mkdt.booking_tgl, '#fm-keuangan #booking_tgl')
                setDatePicker(mkdt.booking_tgl, '#keu_booking_tgl')

                $(".num").keyup().change();
                // total('#fm-keuangan');


                $("#fm-keuangan #booking_fee").val(mkdt.booking_fee).change()
                $("#keu_booking_fee").val(mkdt.booking_fee).change()

                //set booking paid/not
                $("#booking_fee_paid").val(1);
                if (mkdt.booking_paid == 1) {
                    $("#booking_fee_paid, #keu_booking_fee").prop("disabled", true);
                    document.querySelector("#keu_booking_tgl")._flatpickr._input.disabled = true
                }
            }


            //sudah bayar

            /************************* UM & BB **************************/
            // let nom = 0,
            //     tot = removeComma($("#bt-total_biaya_um").val()) || 0,
            //     sisa = 0,
            //     prs = 0,
            //     nom_bb = 0,
            //     tot_bb = removeComma($("#bt-total_biaya_bb").val()) || 0,
            //     sisa_bb = 0,
            //     prs_bb = 0,
            //     nom_ll = 0,
            //     // tot_ll = removeComma($("#bt-total_biaya_um_ll").val()) || 0,
            //     sisa_ll = 0,
            //     prs_ll = 0;
            // $.each(sb, function(i, v) {
            //     if (v.payment_type != "Booking") {
            //         let sp = v.payment_type.split(';')

            //         if (sp.includes('Uang Muka'))
            //             nom += parseFloat(v.nominal) || 0
            //         if (v.status == 'UM')
            //             nom_ll += parseFloat(v.nominal) || 0
            //         else if (v.status == 'BB')
            //             nom_bb += parseFloat(v.nominal) || 0
            //     }
            // })
            // nom = nom > tot ? tot : nom;
            // sisa = tot - nom
            // sisa_bb = tot_bb - nom_bb

            // prs = (nom == 0) ? 0 : nom / tot * 100;
            // prs_bb = (nom_bb == 0) ? 0 : nom_bb / tot_bb * 100;


            // $("#bt-sudah_bayar_um").val(nom).keyup();
            // $("#bt-sisa_tagihan_um").val(sisa).keyup();

            // $("#bt-persentase_bayar_tagihan_um").val(prs.toFixed(2) + "%");


            // $("#bt-sudah_bayar_bb").val(nom_bb).keyup();
            // $("#bt-sisa_tagihan_bb").val(sisa_bb).keyup();

            // $("#bt-persentase_bayar_tagihan_bb").val(prs_bb.toFixed(2) + "%");

            // return

            keu_sb = sb

            /************************ load table tagihan ***************************/
            // keu_nom_bb = nom_bb
            // keu_nom_ll = nom_ll

            keu_tg = tg

            // /************************ load table log pembayaran ***************************/

            keu_lp = lp

            /************************ end of load table log pembayaran ***************************/


        },
        error: function(xhr, st, err) {
            $("#loading").addClass("hidden");
            return swal('error', 'Terjadi kesalahan saat memuat data', err)
        },
    });

}

function loadKeuSB(sb) {
    let nom = 0,
        tot = removeComma($("#bt-total_biaya_um").val()) || 0,
        sisa = 0,
        prs = 0,
        nom_bb = 0,
        tot_bb = removeComma($("#bt-total_biaya_bb").val()) || 0,
        sisa_bb = 0,
        prs_bb = 0,
        nom_ll = 0,
        // tot_ll = removeComma($("#bt-total_biaya_um_ll").val()) || 0,
        sisa_ll = 0,
        prs_ll = 0;
    $.each(sb, function(i, v) {
        if (v.payment_type != "Booking") {
            let sp = v.payment_type.split(';')

            if (sp.includes('Uang Muka'))
                nom += parseFloat(v.nominal) || 0
            if (v.status == 'UM')
                nom_ll += parseFloat(v.nominal) || 0
            else if (v.status == 'BB')
                nom_bb += parseFloat(v.nominal) || 0
        }
    })
    nom = nom > tot ? tot : nom;
    sisa = tot - nom
    sisa_bb = tot_bb - nom_bb

    prs = (nom == 0) ? 0 : nom / tot * 100;
    prs_bb = (nom_bb == 0) ? 0 : nom_bb / tot_bb * 100;


    $("#bt-sudah_bayar_um").val(nom).keyup();
    $("#bt-sisa_tagihan_um").val(sisa).keyup();

    $("#bt-persentase_bayar_tagihan_um").val(prs.toFixed(2) + "%");


    $("#bt-sudah_bayar_bb").val(nom_bb).keyup();
    $("#bt-sisa_tagihan_bb").val(sisa_bb).keyup();

    $("#bt-persentase_bayar_tagihan_bb").val(prs_bb.toFixed(2) + "%");

    keu_nom_bb = nom_bb
    keu_nom_ll = nom_ll
}

function loadTableTagihan(tg) {
    if (!loaded['keu_sb']) {
        loadKeuSB(keu_sb)
    }

    let tr_tg = "",
        tr_tg_bb = "",
        no = 1,
        no_bb = 1,
        tot_tg = 0,
        tot_tg_bb = 0,
        sb_button = "",
        sb_button_bb = "",
        chkd = "",
        opt = "",
        opt_bb = "",
        dsb = "",
        disabled = ""
    $("#bt-for").html("")
    $("#bt-for_bb").html("")
    $.each(tg, function(i, v) {
        chkd = ""
        dsb = ""
        if (v.status == "UM") {
            if (v.sudah_dibayar == 1) {
                chkd = "checked"
                    // dsb = "disabled"
            }

            sb_button = `<div class="form-group">
                        <div class="custom-control custom-switch custom-control-inline">
                            <input type="checkbox" ` + chkd + ` onchange="save_sb(` + v.id_keuangan + `)" class="custom-control-input " ` + disabled + ` id="sb_btn` + v.id_keuangan + `" name="sb_btn[` + v.id_keuangan + `]" value="1" />
                            <label class="custom-control-label" for="sb_btn` + v.id_keuangan + `"></label>
                        </div>
                    </div>`;

            tot_tg += parseInt(v.nominal);
            tr_tg += "<tr>" +
                "<td>" + no + "</td>" +
                "<td>" + v.berita_acara + "</td>" +
                "<td style='text-align:right'>" + num_format(v.nominal) + "</td>" +
                "<td>" + format_date(v.jatuh_tempo_tgl) + "</td>" +
                "<td>" + v.username + "<br/>" + format_datetime(v.created_at) + " </td>" +
                "<td>" + sb_button + "</td>" +
                "<tr>";
            no++;

        } else if (v.status == "BB") {
            if (v.sudah_dibayar == 1) {
                chkd = "checked"
                    // dsb = "disabled"
            }

            sb_button_bb = `<div class="form-group">
                        <div class="custom-control custom-switch custom-control-inline">
                            <input type="checkbox" ` + chkd + ` onchange="save_sb(` + v.id_keuangan + `)" class="custom-control-input " ` + disabled + ` id="sb_btn` + v.id_keuangan + `" name="sb_btn[` + v.id_keuangan + `]" value="1" />
                            <label class="custom-control-label" for="sb_btn` + v.id_keuangan + `"></label>
                        </div>
                    </div>`;

            tot_tg_bb += parseInt(v.nominal);
            tr_tg_bb += "<tr>" +
                "<td>" + no_bb + "</td>" +
                "<td>" + v.berita_acara + "</td>" +
                "<td style='text-align:right'>" + num_format(v.nominal) + "</td>" +
                "<td>" + format_date(v.jatuh_tempo_tgl) + "</td>" +
                "<td>" + v.username + "<br/>" + format_datetime(v.created_at) + " </td>" +
                "<td>" + sb_button_bb + "</td>" +
                "<tr>";
            no_bb++;
        }
        opt += "<option " + dsb + " value='" + v.id_keuangan + "'>" + v.berita_acara + "</option>"

    })

    tr_tg += `
            <tr>
                <th colspan=2>Total</th>
                <th style='text-align:right'> ${num_format(tot_tg)}</th>
                <th colspan='3'></th>"
            </tr>
            <tr>
                <th colspan=2>Sudah Bayar</th>
                <th style='text-align:right'> ${num_format(keu_nom_ll)}</th>
                <th colspan='3'></th>"
            </tr>
            <tr>
                <th colspan=2>Sisa Tagihan</th>
                <th style='text-align:right'> ${num_format(tot_tg - keu_nom_ll)}</th>
                <th colspan='3'></th>"
            </tr>

        `;
    tr_tg_bb += `
            <tr>
                <th colspan=2>Total</th>
                <th style='text-align:right'> ${num_format(tot_tg_bb)}</th>
                <th colspan='3'></th>"
            </tr>
            <tr>
                <th colspan=2>Sudah Bayar</th>
                <th style='text-align:right'> ${num_format(keu_nom_bb)}</th>
                <th colspan='3'></th>"
            </tr>
            <tr>
                <th colspan=2>Sisa Tagihan</th>
                <th style='text-align:right'> ${num_format(tot_tg_bb - keu_nom_bb)}</th>
                <th colspan='3'></th>"
            </tr>

        `;


    $("#bt-for").append(opt)
    $("#bt-for_bb").append(opt)

    $("#tb-data-tagihan").append(tr_tg);
    $("#tb-data-tagihan_bb").append(tr_tg_bb);
}

function loadLogPembayaran(lp) {
    if (!loaded['keu_sb']) {
        loadKeuSB(keu_sb)
    }
    let t = "",
        tot_lp = 0,
        t_bb = "",
        tot_lp_bb = 0,
        no = 1,
        no_bb = 1;

    $.each(lp, function(k, v) {
        //set tgl & booking fee yang diinput oleh keuangan
        if (v.payment_type == 'Booking') {
            $("#keu_booking_fee").val(v.nominal).keyup();
            setDatePicker(v.tanggal_bayar, "#keu_booking_tgl")
        }

        tot_lp += parseInt(v.nominal);
        t += "<tr>" +
            "<td>" + no + "</td>" +
            "<td>" + format_date(v.tanggal_bayar) + "</td>" +
            "<td style='text-align:right'>" + num_format(v.nominal) + "</td>" +
            "<td class='text-left'> Untuk Pembayaran: " + v.payment_type + "</td>" +
            "<td>" + v.username + "<br/>" + format_datetime(v.created_at) + " </td>" +
            `<td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="printRiwayatBayar('` + v.id_pembayaran + `', '` + v.id_mkdt + `', '` + dt_proyek['id_proyek'] + `')"><i class="fa fa-print"></i></button>
                                    <button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="removeRiwayatBayar('` + v.id_pembayaran + `')"><i class="fa fa-trash"></i></button>
                                </div>
                            </td>` +
            "<tr>";
        no++;
    });

    t += "<tr>" +
        "<th colspan='2'>Total</th>" +
        "<th style='text-align:right'>" + num_format(tot_lp) + "</th>" +
        "<th colspan='3'></th>" +
        "<tr>";

    $("#tb-data-log_pembayaran").append(
        $(t).hide().slideDown(2000)
    );
}

function printRiwayatBayar(e, e2, e3) {
    var myWindow = window.open(base_url + "/keuangan/print_kuitansi/" + e + "/" + e2 + "/" + e3 + "/", "_blank", "top=100,left=300,width=700,height=600");
    setTimeout(function() {
        myWindow.focus();
        myWindow.print();
    }, 1000);
}

function removeRiwayatBayar(e) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Apakah anda yakin akan menghapus data?",
        // type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya!',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: !1
    }).then(function(t) {
        if (t.value) {
            $.ajax({
                url: base_url + 'Keuangan/removeLP',
                type: 'post',
                data: {
                    [csrfName]: csrfHash,
                    id_pembayaran: e
                },
                dataType: 'json',
                beforeSend: function() {
                    $("#loading").removeClass("hidden");
                },
                success: function(r) {
                    $("#loading").addClass("hidden");
                    csrfHash = r.token;
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        $('.modal').modal('hide');
                    })
                },
                error: function(e) {
                    Swal.fire({
                        //position: 'bottom-end',
                        icon: 'error',
                        title: "Terjadi Kesalahan",
                        showConfirmButton: true,
                        // timer: 1500
                    })
                }
            });
        }
    })
}

function save_keuangan(e = '') {

    if ($("#fm-keuangan #status_mkdt").val() == "Batal") {
        // if (!palid("keterangan_refund", "", "Keterangan harus diisi"))
        //     return;
        // if (!palid("nominal_refund", "", "Nominal harus diisi"))
        //     return;
    } else {
        if (e == '') {
            //validasi manual cuuukk
            if ($("#booking_fee_paid").prop("disabled") == true) {
                if ($("#fm-keuangan #is_lunas").prop("checked") == false) {
                    if ($("#bt-sisa_tagihan_um").val() != '0') {

                        if (!palid("bt-berita_acara_um", "", "Keterangan pembayaran harus diisi"))
                            return;
                        //nominal harus diisi
                        if (!palid("bt-bayar_tagihan_um", "0", "Nominal Tidak boleh 0"))
                            return;
                        if (!palid("bt-bayar_tagihan_um", null, "Nominal Tidak boleh kosong"))
                            return;
                        if (!palid("bt-bayar_tagihan_um", '', "Nominal Tidak boleh kosong"))
                            return;
                        if (!palid("bt-tanggal_bayar_um", '', "Tanggal bayar Tidak boleh kosong"))
                            return;
                    }
                }
            }
        } else {
            if ($("#booking_fee_paid").prop("disabled") == true) {
                if ($("#fm-keuangan #is_lunas").prop("checked") == false) {
                    if ($("#bt-sisa_tagihan_um").val() != '0') {
                        if (!palid("bt-berita_acara_bb", "", "Keterangan pembayaran harus diisi"))
                            return;
                        //nominal harus diisi
                        if (!palid("bt-bayar_tagihan_bb", "0", "Nominal Tidak boleh 0"))
                            return;
                        if (!palid("bt-bayar_tagihan_bb", null, "Nominal Tidak boleh kosong"))
                            return;
                        if (!palid("bt-bayar_tagihan_bb", '', "Nominal Tidak boleh kosong"))
                            return;
                        if (!palid("bt-tanggal_bayar_bb", '', "Tanggal bayar Tidak boleh kosong"))
                            return;
                    }
                }
            }
        }
    }
    Swal.fire({
        title: 'Simpan Data?',
        text: "",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya!',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: !1
    }).then(function(t) {
        if (t.value) {
            let text_um = []

            $('#bt-for option:selected').each(function() {
                text_um.push($(this).text()); // Get the text of the selected option
            });

            // Join the texts with semicolon
            text_um = text_um.join(';')
            text_um = text_um != '' ? text_um + ';' : text_um

            let text_bb = []

            $('#bt-for_bb option:selected').each(function() {
                text_bb.push($(this).text()); // Get the text of the selected option
            });

            // Join the texts with semicolon
            text_bb = text_bb.join(';')

            text_bb = text_bb != '' ? text_bb + ';' : text_bb

            $.ajax({
                url: base_url + 'Keuangan/save',
                type: 'post',
                data: $("#fm-keuangan").serialize() + "&" + csrfName + "=" + csrfHash + "&text_um=" + text_um + "&text_bb=" + text_bb + '&e=' + e + '&cis_lunas=' + $("#is_lunas").prop('checked'),
                dataType: 'json',
                beforeSend: function() {
                    simpanBtn(".add-form-btn-keuangan", true)
                },
                success: function(r) {
                    csrfHash = r.token;
                    if (r.success === true) {
                        swal('success', r.messages)
                        loaded = []
                        keu_lp = []
                        keu_tg = []

                        keu_sb = []
                        keu_nom_bb = 0
                        keu_nom_ll = 0


                        isi_data()
                            // $('.modal').modal('hide');
                    } else {
                        swal('error', r.messages)
                    }
                    simpanBtn(".add-form-btn-keuangan", false)

                    // load_kavling();
                    // hapus_seleksi();
                },
                error: function(e, f, g) {
                    simpanBtn(".add-form-btn-keuangan", false)
                    swal('error', g)
                }
            });
        } else
            return false
    })
}

function dana_akad() {
    $("#fm-dana_akad")[0].reset()
    $("#da-jaminan_here").html("")
    $("#da-cair_jaminan_here").html("")
    if (!editdtt[0]) {
        return swal('error', "Tidak ada kavling yang dipilih")

    }

    var role,
        sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    if (!sh.data.id_mkdt) {
        return swal('error', 'Terjadi kesalahan', "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling)
    }

    if (sh.data2.status_mkdt != "Akad") {
        return swal('error', 'Terjadi kesalahan', "Kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "Belum Akad!")
    }

    $.ajax({
        url: base_url + 'keuangan/getDanaAkad',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_kavling: id_kavling,
            id_mkdt: sh.data.id_mkdt
        },
        dataType: 'json',
        beforeSend: function() {
            $("#loading").removeClass("hidden")
        },
        success: function(r) {
            $("#loading").addClass("hidden")
            csrfHash = r.token;
            let ld = r.list_dajam

            $("#da-id_mkdt").val(sh.data.id_mkdt);
            $("#da-id_kavling").val(sh.id.substr(3));

            let dv = "",
                dvc = ""
            let id_list_dajam = ''
            let kpr_acc = r.mkdt.harga_kpr_acc ? r.mkdt.harga_kpr_acc : 0
            let hasil_akad = parseFloat(kpr_acc)
            let id_dajam = ''
            let z = 0
            let sc = ""
            let dis = ""

            $('#da-dajam_selesai').prop('checked', parseInt(r.mkdt.dajam_selesai)); //set chceked status selesai
            changeVal("#da-kpr_acc", kpr_acc)

            $.each(ld, function(i, v) {
                hasil_akad += parseFloat(v.nominal ? v.nominal : 0)
                id_list_dajam = v.id_list_dajam ? v.id_list_dajam : v.id_list_dajam_ori;
                id_dajam = v.id == null ? "n" + z : v.id
                sc = v.sudah_cair == 1 ? "checked" : "";
                dis = v.sudah_cair == 1 ? "" : "disabled";
                dv += `
                    <div class="form-group row">
                        <div class="col-12 guarantee-item">
                            <div class="row">
                                <div class="col-md-2">
                                    <label><h5>${v.nama_jaminan}</h5></label>
                                </div>
                                <div class="col-md-2">
                                    <label>Nominal</label>
                                    <input type="hidden" value="${id_list_dajam}" id="da-id_dajam[${id_dajam}][id_list_dajam]" name="id_dajam[${id_dajam}][id_list_dajam]" class="form-control" />
                                    <input type="text" value="${v.nominal ? v.nominal : 0}" id="da-id_dajam[${id_dajam}][nominal]" name="id_dajam[${id_dajam}][nominal]" class="form-control num daf" onchange="hitung_dana_akad()"/>
                                    <input type="hidden" ${dis} value="${v.nominal_cair ? v.nominal_cair : 0}" id="da-id_dajam[${id_dajam}][nominal_cair]" name="id_dajam[${id_dajam}][nominal_cair]" class="form-control num cl${id_dajam}" onchange="" />
                                </div>
                                <div class="col-md-2">
                                    <label style="text-align: center; display: block;">Sudah Cair</label>
                                    <input type="checkbox"  ${sc} value="1" id="da-id_dajam[${id_dajam}][sudah_cair]" name="id_dajam[${id_dajam}][sudah_cair]" class="form-control" onclick="is_cair(this, '${id_dajam}')"/>
                                </div>
                                <div class="col-md-2">
                                    <label>Tanggal Cair</label>
                                    <input ${dis} type="text" value="${v.tgl_cair ? v.tgl_cair : ''}" id="da-id_dajam[${id_dajam}][tgl_cair]" name="id_dajam[${id_dajam}][tgl_cair]" class="form-control flatpickr-human-friendly fp-dajam cl${id_dajam}" />
                                </div>
                                <div class="col-md-4">
                                    <label>Catatan</label>
                                    <textarea row="4" ${dis} class="form-control cl${id_dajam}" id="da-id_dajam[${id_dajam}][keterangan]" name="id_dajam[${id_dajam}][keterangan]">${v.keterangan == null ? "" : v.keterangan}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                 `
                z++
            });
            dv += ``

            $("#da-jaminan_here").append(dv)
                // $("#da-cair_jaminan_here").append(dvc)

            flatpickr(".fp-dajam", {
                altInput: true,
                altFormat: 'F j, Y',
                dateFormat: 'Y-m-d'
            })


            changeVal("#da-hasil_akad", hasil_akad)
            $("#fm-dana_akad .num").keyup()

            hitung_dana_akad()

            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#dana_akad_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
        },
        error: function(a, b, c) {
            $("#loading").addClass("hidden")
            return swal('error', 'Terjadi kesalahan', c);
        }
    });


}

function hitung_dana_akad() {
    let total = parseFloat(removeComma($("#da-kpr_acc").val())) || 0;
    let total_dajam = 0;
    $(".daf").each(function() {
        total_dajam += parseFloat(removeComma($(this).val())) || 0;
    });
    changeVal("#da-total_dajam", total_dajam)
    changeVal("#da-hasil_akad", total - total_dajam)
}

function is_cair(e, id) {
    let is_true = $(e).prop("checked")
    $(`.cl${id}`).prop('disabled', !is_true)
}

function save_dana_akad() {
    $.ajax({
        url: base_url + 'keuangan/saveDanaAkad',
        type: 'post',
        data: $("#fm-dana_akad").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: 'json',
        beforeSend: function() {
            simpanBtn("#add-form-btn-dana_akad", true)
        },
        success: function(r) {
            csrfHash = r.token;
            if (r.success === true) {
                swal("success", r.messages)
                $('.modal').modal('hide');
                simpanBtn("#add-form-btn-dana_akad", false)
            } else {
                swal('error', 'Terjadi kesalahan', r.messages)
                simpanBtn("#add-form-btn-dana_akad", false)
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
            simpanBtn("#add-form-btn-dana_akad", false)
        }
    });
}

function getRiwayatGantinama() {
    if (!editdtt[0]) {
        return swal('error', 'Tidak ada kavling yang dipilih')
    }
    let sh = editdtt[0]

    $.ajax({
        url: base_url + 'keuangan/get_riwayat_gantinama',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_mkdt: sh.data.id_mkdt,
        },
        dataType: 'json',
        beforeSend: function() {
            simpanBtn("#btn-refresh-ganti_nama", true, 'Sedang Memuat <i class="fa fa-spinner fa-spin"></i>', 'Muat ulang riwayat')
        },
        success: function(x) {
            csrfHash = x.token;

            let tb = '<tr><td colspan = 2> Tidak Ada Data</td></tr>'
            if (x.riwayat) {
                tb = "";
                let n = 1
                x.riwayat.forEach(e => {
                    tb += `
                    <tr>
                            <td>${n}</td>
                            <td>
                                <a href="${base_url + e.file_spptb}" target=_blank class="btn btn-outline-primary">Klik untuk melihat file SPPTB Seblumnya</a>
                            </td>
                            <td>
                                -
                            </td>
                        </tr>"`
                    n++
                });

            }

            $("#riwayat_ganti_nama-here").html(tb)

            simpanBtn("#btn-refresh-ganti_nama", false, 'Sedang Memuat <i class="fa fa-spinner fa-spin"></i>', 'Muat ulang riwayat')
        },
        error: function(xhr, st, err) {
            simpanBtn("#btn-refresh-ganti_nama", false, 'Sedang Memuat <i class="fa fa-spinner fa-spin"></i>', 'Muat ulang riwayat')
            return swal("error", err);
        }
    });
}
var co = []

function isi_cashout() {
    var sh = editdtt[0],
        id_kavling = sh.id.substr(3);

    co = []

    $("#fm-cashout-keu")[0].reset()
    $("#div-cashout-here").html("")

    $.ajax({
        url: base_url + 'keuangan/getCashOut',
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
            let d = r.list_cashout
            let div = '',
                id_cashout

            $.each(d, function(i, v) {
                co.push(v.id_cashout)

                id_cashout = !v.id ? "n" + v.id_cashout : v.id
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
                                            <input type="text" class="form-control fp-cashout flatpickr-human-friendly tb${v.id_cashout}"
                                                id="id-cashout[${id_cashout}][tanggal_bayar]" value="${v.tanggal_bayar?v.tanggal_bayar:''}" name="id-cashout[${id_cashout}][tanggal_bayar]">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="sumurbor_bayar_nominal">Nominal</label>
                                            <input type="text" class="form-control num nb${v.id_cashout}" id="id-cashout[${id_cashout}][nominal]"
                                                name="id-cashout[${id_cashout}][nominal]" value="${v.nominal?v.nominal:''}">
                                            <input type="hidden" class="form-control" id="id-cashout[${id_cashout}][id_item_cashout]"
                                                name="id-cashout[${id_cashout}][id_item_cashout]" value="${id_cashout}">
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <textarea class="form-control" id="id-cashout[${id_cashout}][keterangan]"
                                                name="id-cashout[${id_cashout}][keterangan]" rows="4" placeholder="Keterangan">${v.keterangan?v.keterangan:''}</textarea>
                                            <small id="last_update-sumurbor_bayar" class=""></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                 `
            });

            $("#div-cashout-here").html(div)

            flatpickr(".fp-cashout", {
                altInput: true,
                altFormat: 'F j, Y',
                dateFormat: 'Y-m-d'
            })
            $(".num").change()

            // if (d) {
            //     $("#sumurbor_bayar").prop("checked", d.sumurbor_bayar == 1).change();

            //     changeVal("#sumurbor_bayar_nominal", d.sumurbor_bayar_nominal);
            //     changeVal("#sumurbor_bayar_keterangan", d.sumurbor_bayar_keterangan);
            //     setDatePicker(d.sumurbor_bayar_tanggal, '#sumurbor_bayar_tanggal')

            //     $("#last_update-sumurbor_bayar").html(
            //         `Diubah pada: ${d.sumurbor_bayar_updated ? format_datetime(d.sumurbor_bayar_updated):'-'}, 
            //         oleh: ${d.sumurbor_bayar_oleh_u ? d.sumurbor_bayar_oleh_u:'-'}`
            //     )
            // }

            $("#cashout-id_kavling").val(id_kavling)

            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal-cashout-keu').modal({
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

function save_cashout() {
    $.each(co, function(i, v) {
        if ($(".tb" + v)[0].value != "") {
            if ($(".nb" + v)[0].value == "") {
                $(".nb").addClass('is-invalid');
                return swal('error', "Nominal pembayaran harus diisi")
            } else {
                $(".nb").removeClass('is-invalid');
            }
        }
        if ($(".nb" + v)[0].value != "") {
            if ($(".tb" + v)[0].value == "") {
                $(".tb").addClass('is-invalid');
                return swal('error', "Tanggal pembayaran harus diisi")
            } else {
                $(".tb").removeClass('is-invalid');
            }
        }
    });



    let sbtn = "#add-form-btn-cashout"
    $.ajax({
        url: base_url + 'keuangan/saveCashOut',
        type: 'post',
        data: $("#fm-cashout-keu").serialize() + "&" + csrfName + "=" + csrfHash,
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
$('.modal').on('hidden.bs.modal', function() {
    data_um = {}
    data_bb = {}
})

$("#idk_riwayat-tab").click(function() {
    getRiwayatGantinama()
})
$('#close_modal_divisi3').click(function(e) {
    // Prevent the modal from closing
    e.preventDefault();

    // Show confirmation dialog
    // if (confirm('Apakah anda yakin ingin menutup halaman ini?')) {
    //     // If user confirms, close the modal
    //     $('#modal_divisi3').modal('hide');
    // }
});
/****************************** end of keunagan ****************************************/