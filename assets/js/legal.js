/********************************* Legal *******************************************/
$("#fl-btn-upload").click(function(e) {
    return e.preventDefault()
})

function removeDoc(e, id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Apakah anda yakin akan menghapus data?",
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
            $.ajax({
                url: base_url + '/Legal/removeDoc',
                type: 'post',
                data: {
                    [csrfName]: csrfHash,
                    id: e
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
                        load_file_upload(id)
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

function load_file_upload(id_kavling) {
    $.ajax({
        url: base_url + '/Legal/getDoc',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_kavling: id_kavling
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;
            $("#tb-fl-file").html("");
            let tb = `<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>`
            if (r.data) {
                tb = '';
                let no = 0
                $.each(r.data, function(i, v) {
                    no++
                    tb += `<tr>
                                <td>` + no + `</td>
                                <td>` + v.file_name + `</td>
                                <td>` + v.keterangan + `</td>
                                <td> <a href='` + base_url + '/' + v.lokasi + `' target=blank>Klik disini untuk mengunduh</a></td>
                                <td> ` + v.uadd_by + `</td>
                                <td> ` + format_datetime(v.upload_at) + ` </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="removeDoc('` + v.id + `', '` + id_kavling + `')"><i class="fa fa-trash"></i></button>
                                    </div>
                                </td>
                                </tr>`
                });
            }
            $("#tb-fl-file").html(tb);
        },
        error: function() {

        }
    });

}

function fl_upload() {
    if (!$("#fl-file").val()) {
        Swal.fire({
            //position: 'bottom-end',
            icon: 'error',
            title: "Tidak ada file yang di upload",
            showConfirmButton: false,
            timer: 1500
        })
        return false;
    }

    var files = $('#fl-file')[0].files;
    var form = $('#fl-legal')[0];
    var fd = new FormData(form);
    fd.append(csrfName, csrfHash);
    fd.append('id_kavling', $(".id_kavling").val());

    $.ajax({
        url: base_url + '/Legal/upload',
        type: 'POST',
        contentType: false,
        processData: false,
        data: fd, // /converting the form data into array and sending it to server
        beforeSend: function() {
            $('#fl-btn-upload').html('<i class="fa fa-spinner fa-spin"></i> Mengunggah');
            $('#fl-btn-upload').prop('disabled', true);
        },
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
                    load_file_upload($(".id_kavling").val())
                    $("#fl-legal")[0].reset()
                    $("#fl-label").html('Pilih Berkas')
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
            $('#fl-btn-upload').html('Unggah');
            $('#fl-btn-upload').prop('disabled', false);
        },
        error: function() {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'success',
                title: response.messages,
                showConfirmButton: false,
            })
            $('#fl-btn-upload').html('Unggah');
            $('#fl-btn-upload').prop('disabled', false);
        }
    });

}

function open_legal(sh, role, id_kavling) {
    if (sh.data.tipe == "kavling") {
        return open_flegal(sh, role, id_kavling);
    } else {
        return open_fotherlegal(sh)
    }
}

function open_flegal(sh, role, id_kavling) {
    $(".select-pph-validasi-offline, .select-pph-validasi-online, .select-pbg_is_revisi, .select-pbb_is_balik_nama, .select-sertifikat_is_balik_nama, .select-sertifikat_is_split").hide()

    $("#fm-legal")[0].reset();

    $(".id_kavling").val(id_kavling);
    $("#id_legal").val(sh.data.id_legal);

    $.ajax({
        url: base_url + '/legal/get_data_by_id',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_legal: sh.data.id_legal,
            id_kavling: id_kavling
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;


            load_file_upload(id_kavling)

            if (r.data) {
                $("#sertifikat_balik_nama").val(r.data.nama_konsumen ? r.data.nama_konsumen : '')
                $("#bphtb_nominal_disetujui").val(r.data.harga_bphtb ? r.data.harga_bphtb : '').change().keyup()


                $("#pph_ntpn").val(r.data.pph42_ntpn)
                $("#pph_nominal_bayar").val(r.data.pph42_nilai).keyup()
                setDatePicker(r.data.pph42_tgl_bayar, "#pph_tgl_bayar")
            }

            if (r) {
                for (let i in r) {
                    // console.log(i, r[i])
                    $("#" + i).val(r[i]).change();
                }



                setDatePicker(r.sertifikat_split_tanggal_terbit, "#sertifikat_split_tanggal_terbit")
                setDatePicker(r.sertifikat_split_tanggal_berakhir, "#sertifikat_split_tanggal_berakhir")
                setDatePicker(r.sertifikat_split_tanggal_surat_ukur, "#sertifikat_split_tanggal_surat_ukur")
                setDatePicker(r.sertifikat_balik_nama_tgl_pengiriman, "#sertifikat_balik_nama_tgl_pengiriman")
                setDatePicker(r.pbb_pecah_tanggal_bayar, "#pbb_pecah_tanggal_bayar")

                setDatePicker(r.bphtb_tanggal_verifikasi, "#bphtb_tanggal_verifikasi")
                setDatePicker(r.bphtb_jatuh_tempo, "#bphtb_jatuh_tempo")
                setDatePicker(r.bphtb_perpanjang_jatuh_tempo, "#bphtb_perpanjang_jatuh_tempo")
                setDatePicker(r.bphtb_tanggal_pembayaran, "#bphtb_tanggal_pembayaran")
                setDatePicker(r.bphtb_tanggal_validasi, "#bphtb_tanggal_validasi")

                setDatePicker(r.pph_tgl_permohonan, "#pph_tgl_permohonan")
                setDatePicker(r.pph_tanggal_validasi, "#pph_tanggal_validasi")
                setDatePicker(r.pph_tgl_bayar, "#pph_tgl_bayar")
                setDatePicker(r.ajb_tanggal, "#ajb_tanggal")
                setDatePicker(r.ajb_tanggal_dikirim, "#ajb_tanggal_dikirim")
                setDatePicker(r.ppjb_tanggal, "#ppjb_tanggal")

                setDatePicker(r.pbb_balik_nama_tgl_pengiriman, "#pbb_balik_nama_tgl_pengiriman")
                setDatePicker(r.pbb_tgl_pembetulan, "#pbb_tgl_pembetulan")
                setDatePicker(r.pbg_tanggal_kirim, "#pbg_tanggal_kirim")
                setDatePicker(r.pph_tgl_selesai, "#pph_tgl_selesai")

                $("#legal_keterangan").val(r.keterangan);

                // if (r.sertifikat_tgl != "0000-00-00")
                //     document.querySelector("#sertifikat_tgl")._flatpickr.setDate(r.sertifikat_tgl);
                // if (r.sertifikat_masa_berlaku != "0000-00-00")
                //     document.querySelector("#sertifikat_masa_berlaku")._flatpickr.setDate(r.sertifikat_masa_berlaku);
                // if (r.imb_tgl != "0000-00-00")
                //     document.querySelector("#imb_tgl")._flatpickr.setDate(r.imb_tgl);
                // if (r.bphtb_tgl != "0000-00-00")
                //     document.querySelector("#bphtb_tgl")._flatpickr.setDate(r.bphtb_tgl);
                // if (r.bphtb_masa_berlaku != "0000-00-00")
                //     document.querySelector("#bphtb_masa_berlaku")._flatpickr.setDate(r.bphtb_masa_berlaku);
                // if (r.bphtb_validasi != "0000-00-00")
                //     document.querySelector("#bphtb_validasi")._flatpickr.setDate(r.bphtb_validasi);
                // if (r.akad_tgl != "0000-00-00")
                //     document.querySelector("#legal_akad_tgl")._flatpickr.setDate(r.akad_tgl);



            }
            $(".label_alamat").html(`
                    ${dt_proyek.nama_proyek} 
                    <br/> <span class='capitalize'> ${sh.data.tipe}<span> ${sh.data.nama_jalan} No ${sh.data.no_kavling} `);
            $('#modal_flegal').modal({
                backdrop: 'static',
                keyboard: false
            });
        },
        error: function() {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
            })
            return;
        }
    });
}

function open_fotherlegal(sh) {
    $("#fm-fotherlegal")[0].reset()
    $("#fl_progres_jalan").val(0)
    $(".t_luas_planning .t_luas_produksi, .r_progres").html(" ")
    $.ajax({
        url: base_url + '/siteplan/get_others',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_kavling: editdtt[0].id.substr(6)
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;

            if (r.data) {
                let d = r.data[0],
                    progres = (d.progres) ? d.progres : 0;
                $(".id_kavling").val(d.id)
                $(".t_luas_planning, .t_luas_produksi").html("-")



                if (d.planning_luas)
                    $(".t_luas_planning").html(d.planning_luas + "  m&sup2  (" + d.planning_edit + ": " + format_datetime(d.planning_updated_at) + ")")
                if (d.produksi_luas)
                    $(".t_luas_produksi").html(d.produksi_luas + "  m&sup2  (" + d.produksi_edit + ": " + format_datetime(d.produksi_updated_at) + ")")

                $("#f_legal_luas").val(d.legal_luas)
                $("#f_legal_keterangan").val(d.legal_keterangan)
                $("#fl_progres_jalan").val(progres)
                $(".r_progres").html(progres)

            }

        },
        error: function() {
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

    $(".label_alamat").html(dt_proyek.nama_proyek + "<br/> <span class='capitalize'>" + sh.data.tipe + "<span>: " + sh.data.nama_jalan + "");
    $('#modal_fotherlegal').modal({
        backdrop: 'static',
        keyboard: false
    });
}

function save_legal() {
    $.ajax({
        url: base_url + 'legal/save',
        type: 'post',
        data: $("#fm-legal").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: 'json',
        beforeSend: function() {
            simpanBtn('#add-form-btn-legal', true)
        },
        success: function(r) {
            csrfHash = r.token;
            if (r.success === true) {
                swal('success', r.messages)
                    // $('.modal').modal('hide');
                isi_data();
                simpanBtn('#add-form-btn-legal', false)
            } else {
                swal('error', r.messages)
                simpanBtn('#add-form-btn-legal', false)
            }
            load_kavling();
            hapus_seleksi();
        },
        error: function(a, b, err) {
            simpanBtn('#add-form-btn-legal', false)
            swal('error', err)
        }
    });

}

function save_fotherlegal() {
    $.ajax({
        url: base_url + '/legal/edit_others',
        type: 'POST',
        // data: $("#fm-komplain-sales").serialize() + "&" + csrfName + "=" + csrfHash,
        data: $("#fm-fotherlegal").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: 'json',
        beforeSend: function() {
            $('#save-fother-btn-legal').prop('disabled', true);
            $('#save-fother-btn-legal').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
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
            $('#save-fother-btn-legal').html('Simpan');
            $('#save-fother-btn-legal').prop('disabled', false);
        },
        error: function() {
            Swal.fire({
                //position: 'bottom-end',
                icon: 'error',
                title: "Terjadi kesalahan saat menginput data",
                showConfirmButton: false,
                timer: 1500
            })
            $('#save-fother-btn-legal').html('Simpan');
            $('#save-fother-btn-legal').prop('disabled', false);
        }
    });
}
/****************************** end of Legal ****************************************/