/********************************* Legal *******************************************/
$("#fl-btn-upload").click(function(e) {
    return e.preventDefault();
});

function removeDoc(e, id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Apakah anda yakin akan menghapus data?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya!',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: false
    }).then(function(t) {
        if (t.value) {
            $.ajax({
                url: base_url + '/api/legal/removeDoc',
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
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        load_file_upload(id);
                    });
                },
                error: function(e) {
                    $("#loading").addClass("hidden");
                    Swal.fire({
                        icon: 'error',
                        title: "Terjadi Kesalahan",
                        showConfirmButton: true
                    });
                }
            });
        }
    });
}

function load_file_upload(id_kavling) {
    $.ajax({
        url: base_url + '/api/legal/getDoc',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_kavling: id_kavling
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;
            $("#tb-fl-file").html("");
            let tb = `<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>`;
            if (r.data && r.data.length > 0) {
                tb = '';
                let no = 0;
                $.each(r.data, function(i, v) {
                    no++;
                    const docUrl = v.access_url || (typeof file_url === 'function' ? file_url('file_upload', v.id, true) : '#');
                    const uploadAt = typeof format_datetime === 'function' ? format_datetime(v.upload_at) : (v.upload_at || '-');
                    tb += `<tr>
                                <td>` + no + `</td>
                                <td>` + (v.file_name || '-') + `</td>
                                <td>` + (v.keterangan || '-') + `</td>
                                <td> <a href="` + docUrl + `" target="_blank" rel="noopener noreferrer">Klik disini untuk mengunduh</a></td>
                                <td> ` + (v.uadd_by || '-') + `</td>
                                <td> ` + uploadAt + ` </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="removeDoc('` + v.id + `', '` + id_kavling + `')"><i class="fa fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>`;
                });
            }
            $("#tb-fl-file").html(tb);
        },
        error: function() {
            $("#tb-fl-file").html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat dokumen</td></tr>');
        }
    });
}

function fl_upload() {
    if (!$("#fl-file").val()) {
        Swal.fire({
            icon: 'error',
            title: "Tidak ada file yang di upload",
            showConfirmButton: false,
            timer: 1500
        });
        return false;
    }

    var files = $('#fl-file')[0].files;
    var form = $('#fl-legal')[0];
    var fd = new FormData(form);
    fd.append(csrfName, csrfHash);
    fd.append('id_kavling', $(".id_kavling").val());

    $.ajax({
        url: base_url + '/api/legal/upload',
        type: 'POST',
        contentType: false,
        processData: false,
        data: fd,
        beforeSend: function() {
            $('#fl-btn-upload').html('<i class="fa fa-spinner fa-spin"></i> Mengunggah');
            $('#fl-btn-upload').prop('disabled', true);
        },
        success: function(response) {
            csrfHash = response.token;

            if (response.success === true) {
                Swal.fire({
                    icon: 'success',
                    title: response.messages,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    load_file_upload($(".id_kavling").val());
                    $("#fl-legal")[0].reset();
                    $("#fl-label").html('Pilih Berkas');
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: response.messages || 'Gagal mengunggah file',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
            $('#fl-btn-upload').html('Unggah');
            $('#fl-btn-upload').prop('disabled', false);
        },
        error: function(xhr) {
            let msg = 'Terjadi kesalahan saat mengunggah';
            if (xhr.responseJSON && xhr.responseJSON.messages) {
                msg = xhr.responseJSON.messages;
            }
            Swal.fire({
                icon: 'error',
                title: msg,
                showConfirmButton: true
            });
            $('#fl-btn-upload').html('Unggah');
            $('#fl-btn-upload').prop('disabled', false);
        }
    });
}

function open_legal() {
     if (editdtt.length == 0)
            return swal('error', 'Terjad Kesalahan', 'Tidak ada kavling yang dipilih')

        //bug isi data with addmode
        if (editdtt.length > 1) {
            swal('error', 'Terjad Kesalahan', 'Tidak bisa merubah data lebih dari 1 kavling')
            hapus_seleksi();
            return;
        }

        let role,
            sh = editdtt[0],
            id_kavling = sh.id.substr(3);

        //jika admin login
        if (roleid == 1)
            role = $('#pilih-divisi option:selected').val()
        else
            role = roleid

    if (sh.data.tipe == "kavling") {
        return open_flegal(sh, role, id_kavling);
    } else {
        return open_fotherlegal(sh);
    }
}

function open_flegal(sh, role, id_kavling) {
    $(".select-pph-validasi-offline, .select-pph-validasi-online, .select-pbg_is_revisi, .select-pbb_is_balik_nama, .select-sertifikat_is_balik_nama, .select-sertifikat_is_split").hide();

    $("#fm-legal")[0].reset();

    $(".id_kavling").val(id_kavling);
    $("#id_legal").val(sh.data.id_legal);

    const legalProjectName = typeof dt_proyek !== 'undefined' && dt_proyek && dt_proyek.nama_proyek ? dt_proyek.nama_proyek : "-";
    const legalKavlingLabel = [
        sh.data.tipe,
        sh.data.nama_jalan,
        sh.data.no_kavling ? "No " + sh.data.no_kavling : ""
    ].filter(Boolean).join(" ");

    $("#legal-sidebar-project").text(legalProjectName);
    $("#legal-sidebar-kavling").text(legalKavlingLabel || "-");
    $("#legal-sidebar-consumer").text("Belum ada konsumen");

    $.ajax({
        url: base_url + '/api/legal/get',
        type: 'post',
        data: {
            [csrfName]: csrfHash,
            id_legal: sh.data.id_legal,
            id_kavling: id_kavling
        },
        dataType: 'json',
        success: function(r) {
            csrfHash = r.token;

            load_file_upload(id_kavling);

            if (r) {
                const sourceData = r.legal || r;
                for (let i in sourceData) {
                    if (sourceData[i] !== null && typeof sourceData[i] !== 'object') {
                        const el = document.getElementById(i);
                        if (el) {
                            $(el).val(sourceData[i]).trigger('change');
                        }
                    }
                }

                setDatePicker(r.sertifikat_split_tanggal_terbit, "#sertifikat_split_tanggal_terbit");
                setDatePicker(r.sertifikat_split_tanggal_berakhir, "#sertifikat_split_tanggal_berakhir");
                setDatePicker(r.sertifikat_split_tanggal_surat_ukur, "#sertifikat_split_tanggal_surat_ukur");
                setDatePicker(r.sertifikat_balik_nama_tgl_pengiriman, "#sertifikat_balik_nama_tgl_pengiriman");
                setDatePicker(r.pbb_pecah_tanggal_bayar, "#pbb_pecah_tanggal_bayar");

                setDatePicker(r.bphtb_tanggal_verifikasi, "#bphtb_tanggal_verifikasi");
                setDatePicker(r.bphtb_jatuh_tempo, "#bphtb_jatuh_tempo");
                setDatePicker(r.bphtb_perpanjang_jatuh_tempo, "#bphtb_perpanjang_jatuh_tempo");
                setDatePicker(r.bphtb_tanggal_pembayaran, "#bphtb_tanggal_pembayaran");
                setDatePicker(r.bphtb_tanggal_validasi, "#bphtb_tanggal_validasi");

                setDatePicker(r.pph_tgl_permohonan, "#pph_tgl_permohonan");
                setDatePicker(r.pph_tanggal_validasi, "#pph_tanggal_validasi");
                setDatePicker(r.pph_tgl_bayar, "#pph_tgl_bayar");
                setDatePicker(r.ajb_tanggal, "#ajb_tanggal");
                setDatePicker(r.ajb_tanggal_dikirim, "#ajb_tanggal_dikirim");
                setDatePicker(r.ppjb_tanggal, "#ppjb_tanggal");

                setDatePicker(r.pbb_balik_nama_tgl_pengiriman, "#pbb_balik_nama_tgl_pengiriman");
                setDatePicker(r.pbb_tgl_pembetulan, "#pbb_tgl_pembetulan");
                setDatePicker(r.pbg_tanggal_kirim, "#pbg_tanggal_kirim");
                setDatePicker(r.pbg_tanggal_terbit, "#pbg_tanggal_terbit");
                setDatePicker(r.pbg_tanggal_terbit_revisi, "#pbg_tanggal_terbit_revisi");
                setDatePicker(r.pph_tgl_selesai, "#pph_tgl_selesai");

                if (r.keterangan) {
                    $("#legal_keterangan").val(r.keterangan);
                }
            }
            if (r.data) {
                const namaKonsumenLegal = r.data.nama_konsumen ? r.data.nama_konsumen : "Belum ada konsumen";

                $("#legal-sidebar-consumer").text(namaKonsumenLegal);
                $("#sertifikat_balik_nama").val(namaKonsumenLegal);
                $("#pbb_balik_nama").val(namaKonsumenLegal);
                $("#bphtb_nominal_disetujui").val(r.data.harga_bphtb ? r.data.harga_bphtb : '').change().keyup();

                $("#pph_ntpn").val(r.data.pph42_ntpn || '');
                $("#pph_nominal_bayar").val(r.data.pph42_nilai || '').keyup();
                setDatePicker(r.data.pph42_tgl_bayar, "#pph_tgl_bayar");
            }
            const namaProyekTxt = typeof dt_proyek !== 'undefined' && dt_proyek && dt_proyek.nama_proyek ? dt_proyek.nama_proyek : '';
            $(".label_alamat").html(`
                    ${namaProyekTxt}
                    <br/> <span class='capitalize'> ${sh.data.tipe}<span> ${sh.data.nama_jalan} No ${sh.data.no_kavling} `);
            if (typeof initModalListener === 'function') {
                initModalListener('#modal_flegal');
            }
            $('#modal_flegal').modal({
                backdrop: 'static',
                keyboard: false
            });
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

function open_fotherlegal(sh) {
    $("#fm-fotherlegal")[0].reset();
    $("#fl_progres_jalan").val(0);
    $(".t_luas_planning .t_luas_produksi, .r_progres").html(" ");
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
                $(".id_kavling").val(d.id);
                $(".t_luas_planning, .t_luas_produksi").html("-");

                if (d.planning_luas) {
                    $(".t_luas_planning").html(d.planning_luas + "  m&sup2  (" + d.planning_edit + ": " + (typeof format_datetime === 'function' ? format_datetime(d.planning_updated_at) : d.planning_updated_at) + ")");
                }
                if (d.produksi_luas) {
                    $(".t_luas_produksi").html(d.produksi_luas + "  m&sup2  (" + d.produksi_edit + ": " + (typeof format_datetime === 'function' ? format_datetime(d.produksi_updated_at) : d.produksi_updated_at) + ")");
                }

                $("#f_legal_luas").val(d.legal_luas);
                $("#f_legal_keterangan").val(d.legal_keterangan);
                $("#fl_progres_jalan").val(progres);
                $(".r_progres").html(progres);
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: "Terjadi kesalahan saat memuat data",
                showConfirmButton: false,
                timer: 1500
            });
        }
    });

    const namaProyekTxt = typeof dt_proyek !== 'undefined' && dt_proyek && dt_proyek.nama_proyek ? dt_proyek.nama_proyek : '';
    $(".label_alamat").html(namaProyekTxt + "<br/> <span class='capitalize'>" + sh.data.tipe + "<span>: " + sh.data.nama_jalan + "");
    if (typeof initModalListener === 'function') {
        initModalListener('#modal_fotherlegal');
    }
    $('#modal_fotherlegal').modal({
        backdrop: 'static',
        keyboard: false
    });
}

function save_legal() {
    $.ajax({
        url: base_url + '/api/legal/save',
        type: 'post',
        data: $("#fm-legal").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: 'json',
        beforeSend: function() {
            if (typeof simpanBtn === 'function') {
                simpanBtn('#add-form-btn-legal', true);
            }
        },
        success: function(r) {
            csrfHash = r.token;
            if (r.success === true) {
                if (typeof swal === 'function') {
                    swal('success', r.messages);
                } else {
                    Swal.fire({ icon: 'success', title: r.messages, timer: 1500, showConfirmButton: false });
                }
                if (typeof isi_data === 'function') {
                    isi_data();
                }
                if (typeof simpanBtn === 'function') {
                    simpanBtn('#add-form-btn-legal', false);
                }
            } else {
                if (typeof swal === 'function') {
                    swal('error', r.messages);
                } else {
                    Swal.fire({ icon: 'error', title: r.messages });
                }
                if (typeof simpanBtn === 'function') {
                    simpanBtn('#add-form-btn-legal', false);
                }
            }
            if (typeof load_kavling === 'function') {
                load_kavling();
            }
            if (typeof hapus_seleksi === 'function') {
                hapus_seleksi();
            }
        },
        error: function(a, b, err) {
            if (typeof simpanBtn === 'function') {
                simpanBtn('#add-form-btn-legal', false);
            }
            if (typeof swal === 'function') {
                swal('error', err);
            } else {
                Swal.fire({ icon: 'error', title: err });
            }
        }
    });
}

function save_fotherlegal() {
    $.ajax({
        url: base_url + '/api/legal/edit_others',
        type: 'POST',
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
                    icon: 'success',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                });

                if (typeof removeModalListener === 'function') {
                    removeModalListener('#modal_fotherlegal');
                }
                $('.modal').modal('hide');
                if (typeof hapus_seleksi === 'function') {
                    hapus_seleksi();
                }
                if (typeof load_kavling === 'function') {
                    load_kavling();
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: r.messages,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
            $('#save-fother-btn-legal').html('Simpan');
            $('#save-fother-btn-legal').prop('disabled', false);
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: "Terjadi kesalahan saat menginput data",
                showConfirmButton: false,
                timer: 1500
            });
            $('#save-fother-btn-legal').html('Simpan');
            $('#save-fother-btn-legal').prop('disabled', false);
        }
    });
}

$(document).ready(function() {
    // ScrollSpy Modal Legal — scoped to #modal_flegal
    const legalModal = document.getElementById("modal_flegal");
    if (!legalModal) return;

    const scrollArea = legalModal.querySelector(".modal-main");
    const sections = Array.from(legalModal.querySelectorAll(".scroll-section"));
    const navItems = legalModal.querySelectorAll(".legal-tab-nav-item");

    if (scrollArea && sections.length > 0) {
        // Klik tab = Scroll smooth ke section terkait
        navItems.forEach(item => {
            item.addEventListener("click", function(e) {
                e.preventDefault();
                const targetId = this.getAttribute("href").substring(1);
                const targetEl = document.getElementById(targetId);
                if (targetEl && scrollArea) {
                    scrollArea.scrollTo({
                        top: targetEl.offsetTop - scrollArea.offsetTop,
                        behavior: "smooth"
                    });
                }
            });
        });

        // Scroll konten = Update tab active
        scrollArea.addEventListener("scroll", function() {
            let current = "";
            const currentPosition = scrollArea.scrollTop;

            sections.forEach(section => {
                const sectionTop = section.offsetTop - scrollArea.offsetTop - 50;
                if (currentPosition >= sectionTop) {
                    current = section.getAttribute("id");
                }
            });

            navItems.forEach(item => {
                item.classList.remove("active");
                if (item.getAttribute("href") === "#" + current) {
                    item.classList.add("active");
                }
            });
        });
    }
});

/****************************** end of Legal ****************************************/
