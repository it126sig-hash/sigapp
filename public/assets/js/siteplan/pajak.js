
// ############################# pajak #################################
$("#pajak-pph42_tarif").change(function () {
    let id_tarif = $("#pajak-pph42_tarif option:selected").val()

    let hj_net = removeComma($("#pajak-harga_jual_net").val())
    let tarif = pph.find(pph => pph.id === (id_tarif));
    let nom = hj_net * parseFloat(tarif['besar']) / 100;

    $("#pajak-pph42_nilai").val(nom).keyup()
})

$("#add-form-btn-pajak").click(function (e) {
    e.preventDefault();
});

function pajakEmptyFile() {
    return '<div class="pajak-empty-file">Belum ada berkas</div>';
}

function pajakFileTile(v, titlePrefix, withPreview = true) {
    const href = v.access_url || file_url('file_upload', v.id);
    const filename = v.default_filename || v.file_name || 'Berkas';
    const uploadedAt = v.upload_at ? format_datetime(v.upload_at) : '-';
    const uploadedBy = v.uupload_by || '-';
    const preview = withPreview ? `<embed src="${href}" class="pajak-file-preview">` : '';

    return `
        <a href="${href}" class="pajak-file-tile" target="_blank" rel="noopener">
            <div class="pajak-file-title">${titlePrefix} ${filename}</div>
            <div class="pajak-file-meta">${v.keterangan || '-'}</div>
            <div class="pajak-file-meta">Diunggah ${uploadedAt} (${uploadedBy})</div>
            ${preview}
        </a>
    `;
}

function open_pajak(sh, role, id_kavling) {
    sv_fm = $('#fm-pajak')
    sv_fm[0].reset();

    if (sh.data.tipe != "kavling")
        return swal('error', 'Tidak ada kavling terpilih')
    if (!sh.data.id_mkdt)
        return swal('error', "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling)
    if (sh.data.status_mkdt == "Batal")
        return swal('error', 'Kavling dengan konsumen batal')

    $.ajax({
        url: base_url + 'pajak/getOne',
        type: 'post',
        data: {
            id_mkdt: sh.data.id_mkdt,
            id_kavling: id_kavling,
            [csrfName]: csrfHash
        },
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        dataType: 'json',
        beforeSend: function () {
            $("#loading").removeClass("hidden");
        },
        success: function (r) {
            csrfHash = r.token;
            $("#loading").addClass("hidden");

            $("#fm-pajak #id_mkdt").val(sh.data.id_mkdt)

            //load detail kavling

            $(".id_kavling").val(id_kavling)
            $("#pajak-nama_konsumen").val(r.nama_konsumen)
            $("#pajak-npwp").val(r.npwp)
            $("#pajak-alamat_konsumen").val(r.alamat_konsumen)
            $("#pajak-hp_konsumen").val(r.hp_konsumen)
            $("#pajak-pbb_pecah_nop").val(r.pbb_pecah_nop)
            $("#pajak-harga_jual_net").val(r.harga_jual_net).keyup()
            $("#pajak-harga_ppn").val(r.harga_ppn).keyup()
            $("#modal_divisi10 .label_konsumen").text(r.nama_konsumen || "-")
            $("#modal_divisi10 .pajak-tax-tabs a[href='#fp-pph42']").tab('show')


            $("#pajak-id_pajak").val(r.id)

            //load pph
            $("#pajak-pph42_id_billing").val(r.pph42_id_billing)
            $("#pajak-pph42_ntpn").val(r.pph42_ntpn)
            $("#pajak-pph42_keterangan").val(r.pph42_keterangan)

            let dv = ''
            $.each(r.file || [], function (i, v) {
                dv += pajakFileTile(v, 'Bukti pembayaran')
            });
            $("#file_pph42-here").html(dv || pajakEmptyFile())

            setDatePicker(r.akad_tgl, '#pajak-akad_tgl')
            setDatePicker(r.pph42_tgl_bayar, '#pajak-pph42_tgl_bayar')

            if (r.pph42_nilai >= 0) {
                $("#pajak-pph42_nilai").val(r.pph42_nilai).keyup()
                $("#pajak-pph42_tarif").val(r.pph42_tarif)
            } else {
                const is_subsidi = r.is_subsidi == "1" ? "Subsidi" : "Komersil";
                const default_tarif = $('#pajak-pph42_tarif option[data-ket="' + is_subsidi + '"]').val();
                const pph_tarif = pph.find(pph => pph.id === (r.pph42_tarif || default_tarif));
                const nilai = r.harga_jual_net * pph_tarif.besar / 100;


                $(`#pajak-pph42_tarif option[data-ket="${is_subsidi}"]`).prop('selected', true)
                $("#pajak-pph42_nilai").val(nilai).keyup()
            }

            // load ppn
            $("#pajak-ppn_id_billing").val(r.ppn_id_billing)
            $("#pajak-ppn_ntpn").val(r.ppn_ntpn)
            $("#pajak-ppn_keterangan").val(r.ppn_keterangan)
            $("#pajak-ppn_no_faktur").val(r.ppn_no_faktur)

            setDatePicker(r.ppn_tgl_bayar, '#pajak-ppn_tgl_bayar')

            if (r.pph42_nilai > 0) {
                $("#pajak-ppn_nilai").val(r.ppn_nilai).keyup()
                $("#pajak-ppn_tarif").val(r.ppn_tarif)
            } else {
                $("#pajak-ppn_nilai").val('')
            }

            dv = ''
            $.each(r.file_ppn || [], function (i, v) {
                dv += pajakFileTile(v, 'Bukti pembayaran')
            });

            $("#file_ppn-here").html(dv || pajakEmptyFile())

            dv = '';
            $.each(r.file_ajb || [], function (i, v) {
                dv += pajakFileTile(v, 'Berkas AJB', false)
            });

            $("#file_ajb-here").html(dv || pajakEmptyFile())

            $("#modal_divisi10 .label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_divisi' + role).modal({
                backdrop: 'static',
                keyboard: false
            });
        },
        error: function () {
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



function save_pajak() {
    Swal.fire({
        title: 'Simpan data?',
        text: "Pastikan form sudah terisi sesuai?",
        // type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya!',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: !1
    }).then(function (t) {
        if (t.value) {
            var form = $("#fm-pajak")[0];
            var fd = new FormData(form);
            fd.append(csrfName, csrfHash);

            $.ajax({
                url: base_url + "pajak/save",
                type: 'post',
                // data: sv_fm.serialize() + "&" + csrfName + "=" + csrfHash + "&" + sv_par,
                contentType: false,
                processData: false,
                data: fd,
                dataType: "json",
                beforeSend: function () {
                    simpanBtn("#add-form-btn-pajak", true)
                },
                success: function (r) {
                    csrfHash = r.token;
                    if (r.success === true) {
                        swal('success', r.messages)
                        isi_data()
                    } else {
                        swal('error', r.messages)
                    }
                    simpanBtn("#add-form-btn-pajak", false)
                    sv_url = ''
                    sv_fm = ''
                    sv_btn = ''
                    sv_par = ''
                    // load_kavling();
                    // hapus_seleksi();
                },
                error: function (xhr, st, err) {
                    swal("error", err);
                    simpanBtn("#add-form-btn-pajak", false)

                    sv_url = ''
                    sv_fm = ''
                    sv_btn = ''
                    sv_par = ''
                    return
                }
            });
        }
    })
}
