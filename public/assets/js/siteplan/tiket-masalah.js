$(document).ready(function() {
    // Inisialisasi elemen
    const modalMasalah = $('#modal_tiket_masalah');
    modalMasalah.on('hidden.bs.modal', function() {
        $('#tm_subheader_nav, #tm_audit_footer').addClass('d-none');
    });
    let currentRefType = '';
    let currentRefId = 0;
    let currentRefData = null;
    let selectedFiles = []; // Antrean file gambar untuk form buat tiket
    let progressSelectedFiles = []; // Antrean file gambar untuk form add progress

    // --- BUKA MODAL ---
    window.openTiketMasalahAction = function() {
        if (typeof editdtt !== 'undefined' && editdtt.length > 0) {
            let sh = editdtt[0];
            let id = sh.id.replace(/\D/g, ''); // Extract numeric ID
            let tipe = sh.data && sh.data.tipe ? sh.data.tipe : 'kavling';
            let refType = (tipe === 'kavling') ? 'kavling' : 'others';
            window.openTiketMasalah(refType, id);
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.info("Pilih area yang akan dibuat laporan", "", { closeButton: true, tapToDismiss: false });
            } else {
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Pilih area yang akan dibuat laporan', showConfirmButton: false, timer: 3000 });
            }

            // Sembunyikan menu bawaan
            $('#menu_here').hide();

            // Inject tombol manual seleksi khusus TM jika belum ada (langsung ke body agar tidak terpengaruh CSS lain)
            if ($('#tm_container_manual_seleksi').length === 0) {
                let html = `
                    <div id="tm_container_manual_seleksi" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 1060; background: white; padding: 10px 20px; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); display: flex; gap: 10px; align-items: center; border: 1px solid #ff4d4f;">
                        <button type="button" class="btn btn-success btn-round btn-sm" id="tm_btn_selesai_seleksi_manual">
                            <i class="fas fa-check mr-1"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-round btn-sm" id="tm_btn_batal_seleksi_manual">
                            <i class="fas fa-times"></i> 
                        </button>
                        
                        <div class="border-left mx-1" style="height: 24px;"></div>
                        
                        <div class="custom-control custom-switch m-0" >
                            <input type="checkbox" value="1" class="custom-control-input" id="tm_tambah_jalan" name="tm_tambah_jalan" onchange="if($('#tambah_jalan').length === 0){ $('body').append('<input type=\\'checkbox\\' class=\\'d-none\\' id=\\'tambah_jalan\\' name=\\'tambah_jalan\\' />'); } $('#tambah_jalan').prop('checked', this.checked).trigger('change'); if(typeof hapus_seleksi === 'function') hapus_seleksi();" />
                            <label class="custom-control-label font-weight-bold" for="tm_tambah_jalan" style="cursor: pointer; padding-top: 2px;">Manual Seleksi</label>
                        </div>
                        
                        <div class="border-left mx-1" style="height: 24px;"></div>
                        
                        <button type="button" class="btn btn-warning btn-round btn-sm" id="tm_btn_undo_seleksi_manual" onclick="if(typeof undo_manual_selection === 'function') undo_manual_selection();">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                `;
                $('body').append(html);
            } else {
                $('#tm_container_manual_seleksi').show();
            }

            // Otomatis aktifkan mode manual seleksi
            $('#tm_tambah_jalan').prop('checked', true).trigger('change');
        }
    };

    $(document).on('click', '#tm_btn_batal_seleksi_manual', function() {
        if (typeof hapus_seleksi === 'function') hapus_seleksi();
        
        $('#tm_tambah_jalan').prop('checked', false).trigger('change');
        
        // Hapus container dan kembalikan menu bawaan
        $('#tm_container_manual_seleksi').remove();
        $('#menu_here').show();
    });

    $(document).on('click', '#tm_btn_selesai_seleksi_manual', function() {
        if (typeof add_jalan === 'function') add_jalan();

        let points = [];
        if (typeof dtt !== 'undefined' && dtt.length >= 6) {
            points = dtt;
        } else if (typeof line_ms !== 'undefined' && line_ms.points) {
            points = line_ms.points();
        } else if (typeof refresh_manual_selection_points === 'function') {
            points = refresh_manual_selection_points();
        }

        if (!points || points.length < 6 || points.length % 2 !== 0) {
            if (typeof toastr !== 'undefined') toastr.warning("Silahkan seleksi minimal 3 titik!");
            else Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Silahkan seleksi minimal 3 titik!', showConfirmButton: false, timer: 3000 });
            return;
        }

        let pointsStr = typeof points === 'string' ? points : points.join(',');

        // Set global points to be used on submit
        window.tmNewPoints = pointsStr;
        
        // Langsung buka form buat tiket dengan state 'new_others'
        window.openTiketMasalah('new_others', null);

        // Hapus/sembunyikan menu manual seleksi dan kembalikan menu utama
        $('#tm_container_manual_seleksi').remove();
        $('#menu_here').show();
    });


    function stripHtml(html) {
        if (!html) return '';
        let tmp = document.createElement('DIV');
        tmp.innerHTML = html;
        return (tmp.textContent || tmp.innerText || '').trim();
    }

    function getMobileHeroCardHtml(customSubNote) {
        let refInfo = currentRefData || {};
        let locVal = '';
        if (currentRefType === 'kavling') {
            locVal = `${refInfo.nama_jalan || ''}, No. ${refInfo.no_kavling || ''}`;
        } else {
            locVal = `${refInfo.nama_jalan ? refInfo.nama_jalan + ', ' : ''}${refInfo.no_kavling || 'Area Umum'}`;
        }
        locVal = locVal.replace(/^,\s*/, '').trim() || 'Unit Lapangan';

        let tipeVal = '';
        if (currentRefType === 'kavling') {
            let tRumah = refInfo.tipe_rumah ? `Tipe ${refInfo.tipe_rumah}` : '';
            let dim = (refInfo.lb || refInfo.lt) ? `(${refInfo.lb || 0}/${refInfo.lt || 0})` : '';
            let ket = refInfo.tipe_keterangan ? ` ${refInfo.tipe_keterangan}` : '';
            tipeVal = `${tRumah} ${dim} ${ket}`.trim() || 'Kavling Standar';
        } else {
            tipeVal = refInfo.tipe ? `Fasilitas: ${refInfo.tipe.toUpperCase()}` : 'Fasilitas Umum';
        }

        let progVal = parseInt(refInfo.progres_bangunan) || 0;
        let noteText = customSubNote || 'Terakhir diverifikasi dari opname lapangan';

        return `
            <div class="tm-mobile-hero-card d-block d-md-none">
                <div class="tm-mobile-hero-header">
                    <span class="tm-mobile-meta-label">NAMA PROYEK KONSTRUKSI</span>
                    <span class="tm-mobile-hero-badge">Mode Mandor</span>
                </div>
                <h4 class="tm-mobile-hero-title">${refInfo.nama_proyek || 'PROYEK'}</h4>

                <div class="tm-mobile-meta-item">
                    <div class="tm-mobile-meta-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <div class="tm-mobile-meta-label">LOKASI / KAVLING</div>
                        <div class="tm-mobile-meta-val">${locVal}</div>
                    </div>
                </div>

                <div class="tm-mobile-meta-item">
                    <div class="tm-mobile-meta-icon"><i class="fas fa-home"></i></div>
                    <div>
                        <div class="tm-mobile-meta-label">TIPE BANGUNAN</div>
                        <div class="tm-mobile-meta-val">${tipeVal}</div>
                    </div>
                </div>

                <div class="tm-mobile-hero-progress">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="tm-mobile-meta-label mb-0" style="color: #cbd5e1;">Progres Bangunan</span>
                        <span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 11px;">${progVal}%</span>
                    </div>
                    <div class="tm-progress-track">
                        <div class="tm-progress-fill" style="width: ${progVal}%;"></div>
                    </div>
                    <div class="tm-mobile-meta-sub font-italic mt-2">${noteText}</div>
                </div>
            </div>
        `;
    }

    window.openTiketMasalah = function(refType, refId) {
        currentRefType = refType;
        currentRefId = refId;
        selectedFiles = [];
        progressSelectedFiles = [];

        // Reset view & navigation
        $('#list_tiket_masalah').html('<div class="text-center p-4"><span class="spinner-border text-primary"></span></div>');
        $('#tm_subheader_nav, #tm_audit_footer').addClass('d-none');
        
        // Modal listener untuk konfirmasi tutup jika sedang mengisi form
        if (typeof removeModalListener === 'function') {
            removeModalListener('#modal_tiket_masalah');
        }

        // Tampilkan modal
        modalMasalah.modal('show');
        
        if (typeof initModalListener === 'function') {
            initModalListener('#modal_tiket_masalah');
        }

        if (refType === 'new_others') {
            $('#view_list_tiket, #view_detail_tiket').addClass('d-none');
            $('#form_buat_tiket').removeClass('d-none');
            
            // Show new fields and make them required
            $('#tm_new_others_fields').removeClass('d-none');
            $('#tm_id_jenis, #tm_nama_others').prop('required', true);

            // Reset form
            $('#form_buat_tiket_form')[0].reset();
            selectedFiles = [];
            if (typeof renderFilePreviews === 'function') renderFilePreviews();
            
            if ($('#tm_assigned_users').hasClass('select2-hidden-accessible')) {
                $('#tm_assigned_users').val(null).trigger('change');
            }
            initAssignedUsersSelect2();
            
            if (typeof $.fn.richText === 'function') {
                if ($('#keterangan_masalah').siblings('.richText-editor').length === 0) {
                    $('#keterangan_masalah').richText();
                }
                $('#keterangan_masalah').val('');
                $('#keterangan_masalah').prev('.richText-editor').trigger('setContent', '');
            }

            // Initialize flatpickr on date input if available
            if (typeof $.fn.flatpickr === 'function') {
                $('.flatpickr').flatpickr({ dateFormat: 'Y-m-d' });
            }

            // Fetch Cluster from server via API
            if (!$.fn.select2) return;
            if (!$('#tm_id_cluster').hasClass("select2-hidden-accessible")) {
                $('#tm_id_cluster').select2({
                    dropdownParent: $('#modal_tiket_masalah'),
                    placeholder: "Pilih Cluster",
                    allowClear: true,
                    ajax: {
                        url: base_url + "cluster/getAll",
                        dataType: "json",
                        delay: 250,
                        method: "post",
                        data: function (params) {
                            return {
                                [csrfName]: typeof csrfHash !== 'undefined' ? csrfHash : '',
                                search: params.term,
                                id_proyek: getSelectedProyekId()
                            };
                        },
                        processResults: function (r) {
                            if (typeof csrfHash !== 'undefined' && r.token) csrfHash = r.token;
                            let results = [];
                            if (r.data) {
                                $.each(r.data, function(index, item) {
                                    results.push({ id: item[0], text: item[3] });
                                });
                            }
                            return { results: results };
                        },
                        cache: true
                    }
                });

                $('#tm_id_jalan').prop('disabled', true);
                $('#tm_id_jalan').select2({
                    dropdownParent: $('#modal_tiket_masalah'),
                    placeholder: "Pilih Jalan/Blok",
                    allowClear: true,
                    ajax: {
                        url: base_url + "jalan/getAll",
                        dataType: "json",
                        delay: 250,
                        method: "post",
                        data: function (params) {
                            return {
                                [csrfName]: typeof csrfHash !== 'undefined' ? csrfHash : '',
                                search: params.term,
                                id_cluster: $('#tm_id_cluster').val(),
                                id_proyek: getSelectedProyekId()
                            };
                        },
                        processResults: function (r) {
                            if (typeof csrfHash !== 'undefined' && r.token) csrfHash = r.token;
                            let results = [];
                            if (r.data) {
                                $.each(r.data, function(index, item) {
                                    results.push({ id: item[0], text: item[3] });
                                });
                            }
                            return { results: results };
                        },
                        cache: true
                    }
                });

                $('#tm_id_cluster').on('change', function() {
                    $('#tm_id_jalan').val(null).trigger('change');
                    if ($(this).val()) {
                        $('#tm_id_jalan').prop('disabled', false);
                    } else {
                        $('#tm_id_jalan').prop('disabled', true);
                    }
                });
            }

            // Set hero header for new area
            $('#tm_hero_project_title').text('AREA BARU');
            $('#tm_hero_location_text').text('Belum Disimpan');
            $('#tm_hero_tipe_text').text('Silakan lengkapi form area');
            $('#tm_hero_progress_text').text('0%');
            $('#tm_hero_progress_bar').css('width', '0%');
            return;
        }

        // Jika bukan new_others, sembunyikan fields area baru
        $('#tm_new_others_fields').addClass('d-none');
        $('#tm_id_jenis, #tm_nama_others').prop('required', false);
        
        $('#view_list_tiket').removeClass('d-none');
        $('#view_detail_tiket, #form_buat_tiket').addClass('d-none');

        // Load info header
        $.ajax({
            url: base_url + 'api/tiket-masalah/ref-info',
            type: 'POST',
            data: { ref_type: refType, ref_id: refId },
            success: function(res) {
                if(res.success) {
                    currentRefData = res.data;
                    let info = res.data;

                    // Render Hero Card Header (Matching Reference Image)
                    $('#tm_hero_project_title').text(info.nama_proyek || 'PROYEK');

                    let locText = '';
                    if (refType === 'kavling') {
                        locText = `${info.nama_jalan || ''}, No. ${info.no_kavling || ''}`;
                    } else {
                        if (info.nama_jalan) {
                            locText = `${info.nama_jalan}, ${info.no_kavling || ''}`;
                        } else {
                            locText = `${info.no_kavling || 'Area Tidak Diketahui'}`;
                        }
                    }
                    locText = locText.replace(/^,\s*/, '').trim();
                    $('#tm_hero_location_text').text(locText);

                    let tipeText = '';
                    if (refType === 'kavling') {
                        let tRumah = info.tipe_rumah ? `Tipe ${info.tipe_rumah}` : '';
                        let dim = (info.lb || info.lt) ? `(${info.lb || 0}/${info.lt || 0})` : '';
                        let ket = info.tipe_keterangan ? ` ${info.tipe_keterangan}` : '';
                        tipeText = `${tRumah} ${dim} ${ket}`.trim() || 'Kavling Standar';
                    } else {
                        if (info.tipe) {
                            tipeText = `Fasilitas: ${info.tipe.toUpperCase()}`;
                        } else {
                            tipeText = `Fasilitas Umum`;
                        }
                    }
                    $('#tm_hero_tipe_text').text(tipeText);

                    let progress = parseInt(info.progres_bangunan) || 0;
                    $('#tm_hero_progress_text').text(progress + '%');
                    $('#tm_hero_progress_bar').css('width', progress + '%');
                    $('#tm_mobile_list_hero_container').html(getMobileHeroCardHtml('Daftar laporan kendala fisik unit'));
                }
            }
        });

        loadTiketList();
    };

    window.tm_open_detail = function(idTiket, refType, refId) {
        // Buka modal dan siapkan header
        window.openTiketMasalah(refType, refId);
        
        // Tunggu sebentar agar modal tampil dan header ter-set, lalu langsung lompat ke detail
        setTimeout(() => {
            window.loadTiketDetail(idTiket);
        }, 100);
    };

    window.existingPhotos = [];
    window.deletedFotoIds = [];

    window.editTiket = function(data) {
        window.currentEditTiketId = data.id;
        
        // Beralih view dari detail/list ke form
        $('#tm_subheader_nav, #tm_audit_footer').addClass('d-none');
        $('#view_detail_tiket, #view_list_tiket').addClass('d-none');
        $('#form_buat_tiket').removeClass('d-none');

        // Update header form untuk Mode Edit
        $('#tm_form_subtitle_label').text('EDIT DATA TIKET');
        $('#tm_form_title_heading').text('Edit Tiket #TKT-' + data.id);
        $('#tm_form_status_pill').text(data.status ? data.status.toUpperCase() : 'EDIT')
            .attr('class', 'badge badge-light-warning border border-warning px-3 py-1 font-weight-bold text-xs d-none d-sm-inline-block');
        $('#btn_simpan_tiket').html('<i class="fas fa-save mr-1"></i> Perbarui Tiket');
        $('#tm_mobile_form_hero_container').html(getMobileHeroCardHtml('Perubahan data tiket #TKT-' + data.id));
        
        // Reset file queue
        selectedFiles = [];
        window.existingPhotos = data.foto ? [...data.foto] : [];
        window.deletedFotoIds = [];
        if (typeof renderFilePreviews === 'function') renderFilePreviews();
        renderExistingPhotoPreviews();

        // Populate field standar
        let $tglMasalah = $('#form_buat_tiket_form [name="tanggal_masalah"]');
        $tglMasalah.val(data.tanggal_masalah);
        if ($tglMasalah.length > 0 && $tglMasalah[0]._flatpickr) {
            $tglMasalah[0]._flatpickr.setDate(data.tanggal_masalah);
        } else if (typeof $.fn.flatpickr === 'function') {
            $tglMasalah.flatpickr({ dateFormat: 'Y-m-d', defaultDate: data.tanggal_masalah });
        }
        
        let $tglKunjungan = $('#form_buat_tiket_form [name="tanggal_kunjungan"]');
        if (data.tanggal_kunjungan) {
            $tglKunjungan.val(data.tanggal_kunjungan);
            if ($tglKunjungan.length > 0 && $tglKunjungan[0]._flatpickr) {
                $tglKunjungan[0]._flatpickr.setDate(data.tanggal_kunjungan);
            } else if (typeof $.fn.flatpickr === 'function') {
                $tglKunjungan.flatpickr({ dateFormat: 'Y-m-d', defaultDate: data.tanggal_kunjungan });
            }
        } else {
            $tglKunjungan.val('');
            if ($tglKunjungan.length > 0 && $tglKunjungan[0]._flatpickr) {
                $tglKunjungan[0]._flatpickr.clear();
            }
        }
        
        $('#form_buat_tiket_form [name="prioritas"]').val(data.prioritas);
        
        // Populate keterangan
        if (typeof $.fn.richText === 'function') {
            if ($('#keterangan_masalah').siblings('.richText-editor').length === 0) {
                $('#keterangan_masalah').richText();
            }
            $('#keterangan_masalah').val(data.keterangan);
            $('#keterangan_masalah').prev('.richText-editor').trigger('setContent', data.keterangan);
        } else {
            $('#keterangan_masalah').val(data.keterangan);
        }

        // Jika ref_type others (manual selection)
        if (data.ref_type === 'others') {
            $('#tm_new_others_fields').removeClass('d-none');
            $('#tm_id_jenis').val(data.others_id_jenis).trigger('change');
            $('#tm_nama_others').val(data.others_nama);
            $('#tm_id_jenis, #tm_nama_others').prop('required', true);
        } else {
            $('#tm_new_others_fields').addClass('d-none');
            $('#tm_id_jenis, #tm_nama_others').prop('required', false);
        }

        // Hide "Save as Draft" if status is not draft
        if (data.status !== 'draft') {
            $('#btn_simpan_draft').hide();
        } else {
            $('#btn_simpan_draft').show();
        }

        // Setup assigned users
        initAssignedUsersSelect2(function() {
            if (data.assigned_users && data.assigned_users.length > 0) {
                let uids = data.assigned_users.map(u => u.id);
                $('#tm_assigned_users').val(uids).trigger('change');
            } else {
                $('#tm_assigned_users').val(null).trigger('change');
            }
        });
    };

    function renderExistingPhotoPreviews() {
        let container = $('#tm_existing_preview_container');
        container.empty();
        window.existingPhotos.forEach((file, index) => {
            let html = `
                <div class="upload-preview-item" style="position: relative; display: inline-block; margin-right: 10px; margin-bottom: 10px;">
                    <img src="${file.url}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                    <button type="button" class="remove-preview-btn" onclick="removeExistingPhoto(${index})" style="position: absolute; top: -5px; right: -5px; background: red; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; line-height: 20px; text-align: center; font-size: 12px; cursor: pointer;">&times;</button>
                </div>
            `;
            container.append(html);
        });
    }

    window.removeExistingPhoto = function(index) {
        let file = window.existingPhotos[index];
        window.deletedFotoIds.push(file.id); // Add ID to deleted list
        window.existingPhotos.splice(index, 1);
        renderExistingPhotoPreviews();
    };

    function loadTiketList() {
        $('#tm_subheader_nav, #tm_audit_footer').addClass('d-none');
        $.ajax({
            url: base_url + 'api/tiket-masalah/list',
            type: 'POST',
            data: { ref_type: currentRefType, ref_id: currentRefId },
            success: function(res) {
                if(res.success) {
                    renderTiketList(res.data);
                }
            },
            error: function() {
                $('#list_tiket_masalah').html('<div class="alert alert-danger">Gagal memuat data tiket.</div>');
            }
        });
    }

    function renderTiketList(data) {
        let html = '';
        if(data.length === 0) {
            html += `
                <div class="text-center p-4 p-md-5 bg-white border" style="border-radius: 16px; border: 1px dashed #cbd5e1 !important;">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; border-radius: 50%; background: #f0fdf4; color: #16a34a;">
                        <i class="feather icon-check-circle" style="font-size: 28px;"></i>
                    </div>
                    <h6 class="font-weight-bold text-dark mb-1" style="font-size: 1.05rem;">Tidak Ada Tiket Masalah</h6>
                    <p class="text-muted text-sm mb-3">Belum ada laporan kendala fisik untuk unit atau area ini.</p>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold shadow-sm" onclick="$('#btn_show_buat_tiket').click()">
                        <i class="fas fa-plus mr-1"></i> Buat Laporan Pertama
                    </button>
                </div>
            `;
        } else {
            data.forEach(function(item) {
                let tgl = new Date(item.created_at);
                let formattedDate = tgl.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'});

                let thumbHtml = '';
                if (item.foto_url_1) {
                    thumbHtml = `
                        <div class="position-relative mr-3 mb-2 mb-md-0" style="flex-shrink: 0; width: 88px; height: 88px;">
                            <img src="${item.foto_url_1}" class="w-100 h-100 rounded-10" style="object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0;">
                            <div class="position-absolute" style="bottom: 4px; left: 4px; background: rgba(15, 23, 42, 0.75); color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 700; backdrop-filter: blur(2px);">
                                <i class="fas fa-camera mr-1"></i> ${item.foto_count}
                            </div>
                        </div>
                    `;
                } else {
                    thumbHtml = `
                        <div class="mr-3 mb-2 mb-md-0 d-none d-sm-flex align-items-center justify-content-center" style="flex-shrink: 0; width: 88px; height: 88px; border: 1px dashed #cbd5e1; border-radius: 10px; background: #f8fafc; color: #94a3b8; font-size: 0.7rem; flex-direction: column;">
                            <i class="fas fa-image mb-1" style="font-size: 1.1rem; opacity: 0.6;"></i>
                            <span>No Foto</span>
                        </div>
                    `;
                }

                let lastUpdateHtml = '';
                if (item.last_progress_keterangan) {
                    let lastDate = new Date(item.last_progress_date);
                    let formattedLastDate = lastDate.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'});
                    lastUpdateHtml = `
                        <span class="badge badge-light-primary text-primary font-weight-bold px-1 py-0 mr-1" style="font-size: 9px;">UPDATE</span>
                        <span class="font-italic text-slate-600">"${stripHtml(item.last_progress_keterangan)}"</span>
                        <span class="text-muted ml-1" style="font-size: 10px;">(${formattedLastDate})</span>
                    `;
                } else {
                    lastUpdateHtml = `<span class="text-muted"><i class="far fa-clock mr-1"></i> Belum ada update progress</span>`;
                }

                let cleanKet = stripHtml(item.keterangan);

                html += `
                <div class="tm-list-card prio-${item.prioritas} mb-3 cursor-pointer" onclick="loadTiketDetail(${item.id})">
                    <div class="d-flex flex-column flex-md-row align-items-start w-100 p-3">
                        ${thumbHtml}
                        <div class="flex-grow-1 d-flex flex-column w-100" style="min-width: 0;">
                            <!-- Top Row: Priority, Ticket ID, Date, and Status -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center flex-wrap gap-1">
                                    ${getPriorityBadgeHtml(item.prioritas)}
                                    <span class="tm-tkt-id ml-2" style="font-size: 0.85rem;">#TKT-${item.id}</span>
                                    <span class="text-muted text-xs ml-2 d-none d-sm-inline">&bull; ${formattedDate}</span>
                                </div>
                                <div>
                                    ${getStatusBadgeHtml(item.status)}
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="font-weight-bold text-dark mb-2" style="font-size: 0.95rem; line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                ${cleanKet}
                            </div>
                            
                            <!-- PIC & Assigned Users -->
                            <div class="d-flex align-items-center flex-wrap gap-1 mb-2">
                                <span class="tm-badge-status" style="background: #f1f5f9; color: #334155; font-size: 11px; padding: 3px 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                    <i class="far fa-user text-muted mr-1"></i> PIC: <strong>${item.pic_username}</strong>
                                </span>
                                ${item.assigned_users_list ? `
                                <span class="tm-badge-status" style="background: #f1f5f9; color: #334155; font-size: 11px; padding: 3px 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                    <i class="fas fa-users text-muted mr-1"></i> Tim: <strong>${item.assigned_users_list}</strong>
                                </span>` : ''}
                            </div>
                            
                            <!-- Bottom Row: Last Progress & Link -->
                            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center" style="border-top-color: #f1f5f9 !important;">
                                <div class="text-xs text-muted text-truncate mr-2" style="max-width: 80%;">
                                    ${lastUpdateHtml}
                                </div>
                                <div class="text-primary text-xs font-weight-bold flex-shrink-0" style="white-space: nowrap;">
                                    Detail <i class="fas fa-chevron-right ml-1" style="font-size: 9px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
        }
        $('#list_tiket_masalah').html(html);
    }

    $('#btn_show_buat_tiket').click(function() {
        $('#tm_subheader_nav, #tm_audit_footer').addClass('d-none');
        $('#view_list_tiket, #view_detail_tiket').addClass('d-none');
        $('#form_buat_tiket').removeClass('d-none');

        // Update header form untuk Buat Baru
        window.currentEditTiketId = null;
        $('#tm_form_subtitle_label').text('FORM LAPORAN KENDALA');
        $('#tm_form_title_heading').text('Buat Laporan Baru');
        $('#tm_form_status_pill').text('Formulir Lapangan')
            .attr('class', 'badge badge-light-primary border border-primary px-3 py-1 font-weight-bold text-xs d-none d-sm-inline-block');
        $('#btn_simpan_tiket').html('<i class="fas fa-paper-plane mr-1"></i> Simpan Tiket');
        $('#tm_mobile_form_hero_container').html(getMobileHeroCardHtml('Formulir pelaporan kendala unit baru'));

        // Reset form & file queue
        $('#form_buat_tiket_form')[0].reset();
        selectedFiles = [];
        renderFilePreviews();
        
        window.existingPhotos = [];
        window.deletedFotoIds = [];
        $('#tm_existing_preview_container').empty();
        $('#btn_simpan_draft').show();

        // Reset Select2
        if ($('#tm_assigned_users').hasClass('select2-hidden-accessible')) {
            $('#tm_assigned_users').val(null).trigger('change');
        }

        // Aktifkan initModalListener saat mengedit/mengisi form
        if (typeof initModalListener === 'function') {
            initModalListener('#modal_tiket_masalah');
        }

        // Initialize flatpickr on date input if available
        if (typeof $.fn.flatpickr === 'function') {
            $('.flatpickr').each(function() {
                if (this._flatpickr) {
                    this._flatpickr.clear();
                } else {
                    $(this).flatpickr({ dateFormat: 'Y-m-d' });
                }
            });
        }

        // Load & Initialize Select2 for assigned users
        initAssignedUsersSelect2();
        
        // Init RichText
        if (typeof $.fn.richText === 'function') {
            if ($('#keterangan_masalah').siblings('.richText-editor').length === 0) {
                $('#keterangan_masalah').richText();
            }
            $('#keterangan_masalah').val('');
            $('#keterangan_masalah').prev('.richText-editor').trigger('setContent', '');
        }
    });

    $('#btn_batal_buat_tiket, #btn_batal_buat_tiket_top').click(function() {
        Swal.fire({
            title: 'Batalkan Perubahan?',
            text: "Semua data yang belum disimpan akan hilang.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batal',
            cancelButtonText: 'Kembali',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                if (typeof removeModalListener === 'function') {
                    removeModalListener('#modal_tiket_masalah');
                }
                $('#tm_subheader_nav, #tm_audit_footer').addClass('d-none');
                $('#form_buat_tiket').addClass('d-none');
                if (window.currentEditTiketId) {
                    let prevId = window.currentEditTiketId;
                    window.currentEditTiketId = null;
                    window.loadTiketDetail(prevId);
                } else {
                    $('#view_list_tiket').removeClass('d-none');
                    loadTiketList();
                }
            }
        });
    });

    function initAssignedUsersSelect2(callback = null) {
        if ($('#tm_assigned_users').hasClass('select2-hidden-accessible')) {
            if (callback) callback();
            return;
        }
        $.ajax({
            url: base_url + 'api/tiket-masalah/users',
            type: 'GET',
            success: function(res) {
                if(res.success) {
                    let opts = '';
                    res.data.forEach(function(u) {
                        let label = u.name ? `${u.name} (${u.username})` : u.username;
                        opts += `<option value="${u.id}">${label}</option>`;
                    });
                    $('#tm_assigned_users').html(opts).select2({
                        placeholder: "Pilih atau cari user...",
                        allowClear: true,
                        dropdownParent: $('#modal_tiket_masalah')
                    });
                    if (callback) callback();
                }
            }
        });
    }

    // --- ADVANCED FILE UPLOAD HANDLERS (Drag & Drop, Paste, Camera) ---
    function handleAddedFiles(files, containerType = 'main') {
        for (let i = 0; i < files.length; i++) {
            let file = files[i];
            if (file.type.startsWith('image/')) {
                if (containerType === 'main') {
                    selectedFiles.push(file);
                } else {
                    progressSelectedFiles.push(file);
                }
            }
        }
        if (containerType === 'main') {
            renderFilePreviews();
        } else {
            renderProgressFilePreviews();
        }
    }

    function renderFilePreviews() {
        let container = $('#tm_preview_container');
        container.empty();
        selectedFiles.forEach((file, index) => {
            let reader = new FileReader();
            reader.onload = function(e) {
                let html = `
                    <div class="upload-preview-item">
                        <img src="${e.target.result}">
                        <button type="button" class="remove-preview-btn" onclick="removeSelectedFile(${index})">&times;</button>
                    </div>
                `;
                container.append(html);
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeSelectedFile = function(index) {
        selectedFiles.splice(index, 1);
        renderFilePreviews();
    };

    function renderProgressFilePreviews() {
        let container = $('#tm_progress_preview_container');
        container.empty();
        progressSelectedFiles.forEach((file, index) => {
            let reader = new FileReader();
            reader.onload = function(e) {
                let html = `
                    <div class="upload-preview-item">
                        <img src="${e.target.result}">
                        <button type="button" class="remove-preview-btn" onclick="removeProgressSelectedFile(${index})">&times;</button>
                    </div>
                `;
                container.append(html);
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeProgressSelectedFile = function(index) {
        progressSelectedFiles.splice(index, 1);
        renderProgressFilePreviews();
    };

    // File input change handlers
    $('#tm_foto, #tm_foto_camera').on('change', function() {
        if (this.files.length > 0) {
            handleAddedFiles(this.files, 'main');
            $(this).val(''); // Reset input value
        }
    });

    // Drag & Drop handlers
    let dropzone = $('#tm_dropzone');
    dropzone.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.addClass('dragover');
    });
    dropzone.on('dragleave dragend drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.removeClass('dragover');
    });
    dropzone.on('drop', function(e) {
        let files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleAddedFiles(files, 'main');
        }
    });

    // Global Clipboard Paste Handler (Ctrl + V)
    $(document).on('paste', function(e) {
        if (!$('#form_buat_tiket').hasClass('d-none') || !$('#tm_form_progress_container').hasClass('d-none')) {
            let items = (e.clipboardData || e.originalEvent.clipboardData).items;
            let pastedFiles = [];
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    let file = items[i].getAsFile();
                    if (file) pastedFiles.push(file);
                }
            }
            if (pastedFiles.length > 0) {
                if (!$('#form_buat_tiket').hasClass('d-none')) {
                    handleAddedFiles(pastedFiles, 'main');
                } else if (!$('#tm_form_progress_container').hasClass('d-none')) {
                    handleAddedFiles(pastedFiles, 'progress');
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Gambar dari clipboard berhasil ditambahkan',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        }
    });

    // Button clicks set a flag on the form to identify if it's draft or normal
    let submitActionType = 'normal';
    $('#btn_simpan_tiket').click(function() { submitActionType = 'normal'; });
    $('#btn_simpan_draft').click(function() { submitActionType = 'draft'; });

    // Submit Form Buat Tiket
    $('#form_buat_tiket_form').submit(async function(e) {
        e.preventDefault();

        // Client-side Validation
        let tglMasalah = $(this).find('[name="tanggal_masalah"]').val();
        let tglKunjungan = $(this).find('[name="tanggal_kunjungan"]').val();
        let prioritas = $(this).find('[name="prioritas"]').val();
        let keterangan = $(this).find('[name="keterangan"]').val();
        let plainText = keterangan ? keterangan.replace(/(<([^>]+)>)/gi, "").trim() : "";

        let isValid = true;
        let errMsg = "";

        if (currentRefType === 'new_others' || (window.currentEditTiketId && currentRefType === 'others')) {
            let idJenis = $('#tm_id_jenis').val();
            let namaOthers = $('#tm_nama_others').val();
            if (!idJenis || !namaOthers || idJenis.trim() === '' || namaOthers.trim() === '') {
                isValid = false;
                errMsg = "Harap lengkapi Jenis Area dan Nama Area!";
            }
        }

        if (isValid && (!tglMasalah || !prioritas || plainText === '')) {
            isValid = false;
            errMsg = "Harap lengkapi semua field yang wajib diisi (Tanggal Laporan, Prioritas, dan Keterangan)!";
        }

        if (!isValid) {
            if (typeof toastr !== 'undefined') {
                toastr.error(errMsg);
            } else {
                Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: errMsg, showConfirmButton: false, timer: 3000 });
            }
            return;
        }

        let submitBtn = $('#btn_simpan_tiket');
        let draftBtn = $('#btn_simpan_draft');
        let originalSubmitText = submitBtn.html();
        let originalDraftText = draftBtn.html();
        
        let confirmMsg = submitActionType === 'draft' ? 'Simpan tiket ini sebagai draft?' : 'Simpan tiket masalah ini?';
        
        Swal.fire({
            title: 'Konfirmasi Penyimpanan',
            text: confirmMsg,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                submitBtn.prop('disabled', true);
                draftBtn.prop('disabled', true);
                
                if(submitActionType === 'draft') {
                    draftBtn.html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                } else {
                    submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                }

                try {
                    let formData = new FormData(this);
                    formData.append('id_proyek', activeProyekId());
                    if (submitActionType === 'draft') {
                        formData.append('is_draft', 1);
                    }
                    
                    if (window.currentEditTiketId) {
                        formData.append('id_tiket_masalah', window.currentEditTiketId);
                    }

                    // Append files from selectedFiles array
                    if (selectedFiles.length > 0) {
                        for (let i = 0; i < selectedFiles.length; i++) {
                            let file = selectedFiles[i];
                            try {
                                let compressedFile = await compressImage(file);
                                formData.append('foto[]', compressedFile, compressedFile.name);
                            } catch (err) {
                                formData.append('foto[]', file, file.name);
                            }
                        }
                    }

                    // Append deleted existing photos
                    if (window.deletedFotoIds && window.deletedFotoIds.length > 0) {
                        formData.append('deleted_foto_ids', window.deletedFotoIds.join(','));
                    }

                    const processSubmitTiket = (fd) => {
                        // Bersihkan field yang tidak diperlukan untuk store/update tiket
                        fd.delete('id_jenis');
                        fd.delete('nama');
                        fd.delete('id_cluster');
                        fd.delete('id_jalan');
                        fd.delete('points');

                        fd.set('ref_type', currentRefType);
                        fd.set('ref_id', currentRefId);

                        let targetUrl = window.currentEditTiketId ? (base_url + 'api/tiket-masalah/update') : (base_url + 'api/tiket-masalah/store');

                        $.ajax({
                            url: targetUrl,
                            type: 'POST',
                            data: fd,
                            processData: false,
                            contentType: false,
                            success: function(res) {
                                if (typeof removeModalListener === 'function') {
                                    removeModalListener('#modal_tiket_masalah');
                                }
                                
                                // Kembalikan UI dari mode manual seleksi
                                if (currentRefType === 'others') {
                                    if (typeof hapus_seleksi === 'function') hapus_seleksi();
                                    $('#tambah_jalan').prop('checked', false);
                                    if ($('#tm_btn_batal_seleksi_manual').length > 0) {
                                        $('#tm_btn_batal_seleksi_manual').trigger('click');
                                    }
                                }

                                Swal.fire('Berhasil', res.message, 'success');
                                $('#form_buat_tiket').addClass('d-none');
                                $('#view_list_tiket').removeClass('d-none');
                                loadTiketList();
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON?.message || 'Gagal membuat tiket';
                                if (xhr.responseJSON?.messages) {
                                    msg = Object.values(xhr.responseJSON.messages).join('<br>');
                                }
                                Swal.fire({ title: 'Error', html: msg, icon: 'error' });
                            },
                            complete: function() {
                                submitBtn.prop('disabled', false).html(originalSubmitText);
                                draftBtn.prop('disabled', false).html(originalDraftText);
                            }
                        });
                    };

                    // Convert array global window.tmNewPoints ke format JSON string atau FormData string jika ada
                    if (window.tmNewPoints && currentRefType === 'new_others') {
                        formData.append('points', window.tmNewPoints);
                    }
                    if (window.tmNewPoints && window.currentEditTiketId && currentRefType === 'others') {
                        formData.append('points', window.tmNewPoints);
                    }

                    // Jika ini adalah area baru (new_others), hit create-others-area dulu
                    if (currentRefType === 'new_others') {
                        let areaData = new FormData();
                        areaData.append('points', window.tmNewPoints || '');
                        areaData.append('tipe', $('#tm_id_jenis').val() || '');
                        areaData.append('id_cluster', $('#tm_id_cluster').val() || '');
                        areaData.append('id_jalan', $('#tm_id_jalan').val() || '');
                        areaData.append('nama', $('#tm_nama_others').val() || '');
                        areaData.append('id_proyek', getSelectedProyekId());

                        $.ajax({
                            url: base_url + 'api/tiket-masalah/create-others-area',
                            type: 'POST',
                            data: areaData,
                            processData: false,
                            contentType: false,
                            success: function(res) {
                                if (res.success) {
                                    // Update state ke area yang sudah ada
                                    currentRefType = 'others';
                                    currentRefId = res.data.id;
                                    
                                    // Lanjut submit tiket
                                    processSubmitTiket(formData);
                                } else {
                                    submitBtn.prop('disabled', false).html(originalSubmitText);
                                    draftBtn.prop('disabled', false).html(originalDraftText);
                                    Swal.fire('Error', res.message || 'Gagal menyimpan area', 'error');
                                }
                            },
                            error: function(xhr) {
                                submitBtn.prop('disabled', false).html(originalSubmitText);
                                draftBtn.prop('disabled', false).html(originalDraftText);
                                let msg = xhr.responseJSON?.message || 'Gagal menghubungi server untuk menyimpan area';
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    } else {
                        processSubmitTiket(formData);
                    }

                } catch (error) {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    submitBtn.prop('disabled', false).html(originalSubmitText);
                    draftBtn.prop('disabled', false).html(originalDraftText);
                }
            }
        });
    });

    // --- DETAIL & PROGRESS (Gambar 2 Layout) ---
    window.loadTiketDetail = function(id) {
        $('#view_list_tiket').addClass('d-none');
        $('#view_detail_tiket').removeClass('d-none');
        $('#tm_detail_content').html('<div class="text-center p-4"><span class="spinner-border text-primary"></span></div>');

        $.ajax({
            url: base_url + 'api/tiket-masalah/detail',
            type: 'POST',
            data: { id_tiket_masalah: id },
            success: function(res) {
                if(res.success) {
                    let activeUserId = (typeof current_user_id !== 'undefined') ? current_user_id : (window.current_user_id || 0);
                    let isPic = (res.data.pic_user_id == activeUserId) || (typeof window.is_supervisor_manager !== 'undefined' && window.is_supervisor_manager);
                    renderTiketDetail(res.data);
                    loadTiketProgress(id, isPic);
                }
            }
        });
    };

    $('#btn_back_to_list').click(function() {
        $('#view_detail_tiket').addClass('d-none');
        $('#view_list_tiket').removeClass('d-none');
        loadTiketList();
    });

    function parseIssueDescription(text) {
        if (!text) return { count: 1, items: ['-'] };
        
        // Convert block elements and line breaks to \n before stripping tags
        let formatted = text
            .replace(/<br\s*[\/]?>/gi, '\n')
            .replace(/<\/(p|div|li|h\d)>/gi, '\n')
            .replace(/<[^>]+>/g, ' ');

        let tempDiv = document.createElement('div');
        tempDiv.innerHTML = formatted;
        let plain = tempDiv.textContent || tempDiv.innerText || '';
        plain = plain.trim();
        
        let items = [];
        // Split on either: start/whitespace followed by number+dot/paren, OR non-digit followed by number+dot/paren
        let numberSplits = plain.split(/(?=(?:^|\s)\d+[\.\)])|(?<=\D)(?=\d+[\.\)])/).map(s => s.trim()).filter(s => s.length > 0);
        
        if (numberSplits.length > 1) {
            numberSplits.forEach(s => {
                let cleaned = s.replace(/^\d+[\.\)]\s*/, '').trim();
                if (cleaned) items.push(cleaned);
            });
        } else {
            let lines = plain.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
            if (lines.length > 1) {
                lines.forEach(l => {
                    let cleaned = l.replace(/^\d+[\.\)]\s*/, '').trim();
                    if (cleaned) items.push(cleaned);
                });
            } else if (plain) {
                items.push(plain);
            }
        }
        
        if (items.length === 0) {
            items.push(text);
        }
        
        return {
            count: items.length,
            items: items
        };
    }

    function renderTiketDetail(data) {
        let tglDetail = new Date(data.created_at);
        let formattedDate = tglDetail.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});

        let isCreator = (window.current_user_id == data.pic_user_id);
        let isSupervisor = (typeof window.is_supervisor_manager !== 'undefined' && window.is_supervisor_manager);
        let isAssigned = data.assigned_users && data.assigned_users.some(u => u.id == window.current_user_id);
        let hasAccess = isCreator || isSupervisor || isAssigned;

        let isClosed = data.status === 'batal' || data.status === 'selesai';
        let actionBtnHtml = '';
        if (isClosed) {
            actionBtnHtml = `
                <span class="badge badge-light text-muted border px-3 py-2 text-xs">
                    <i class="fas fa-lock mr-1"></i> Terkunci
                </span>
            `;
        } else if (!hasAccess) {
            actionBtnHtml = `
                <span class="badge badge-light-warning border border-warning text-xs px-3 py-2" title="Hanya pihak yang dilibatkan yang dapat menambah progres">
                    <i class="fas fa-ban mr-1"></i> Tidak Punya Akses
                </span>
            `;
        } else {
            actionBtnHtml = `
                <button type="button" class="btn btn-primary font-weight-bold btn-sm rounded-pill px-3 shadow-sm transition-all" id="btn_toggle_add_progress">
                    <i class="fas fa-plus mr-1"></i> <span class="btn-text">Tambah Progres Laporan</span>
                </button>
            `;
        }

        let editBtnTopHtml = '';
        let editBtnHtml = '';
        if (data.status !== 'selesai' && data.status !== 'batal' && (isCreator || isSupervisor)) {
            let labelEdit = data.status === 'draft' ? (isCreator ? "Edit Draft" : "Edit & Ambil Alih") : "Edit Tiket";
            editBtnTopHtml = `
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 font-weight-bold shadow-sm transition-all btn_edit_draft_tiket" data-id="${data.id}">
                    <i class="fas fa-edit mr-1"></i> ${labelEdit}
                </button>
            `;
            editBtnHtml = `
                <button type="button" class="btn btn-warning font-weight-bold btn-sm rounded-pill px-3 shadow-sm transition-all btn_edit_draft_tiket mr-2" data-id="${data.id}">
                    <i class="fas fa-edit mr-1"></i> ${labelEdit}
                </button>
            `;
        }

        // 1. RENDER SUB-HEADER NAVIGATION (1 Baris Rapi: Kembali, No Tiket, Prioritas, Status. Share & Print Dihilangkan)
        let subheaderHtml = `
            <div class="d-flex align-items-center justify-content-between w-100 flex-nowrap" style="gap: 8px;">
                <div class="d-flex align-items-center gap-1 gap-sm-2 flex-nowrap" style="min-width: 0; overflow-x: auto;">
                    <a href="javascript:void(0)" class="tm-btn-back flex-shrink-0" id="btn_back_to_list_custom">
                        <i class="fas fa-arrow-left"></i> <span class="d-none d-md-inline">Kembali ke </span>Daftar
                    </a>
                    <div class="tm-divider-vertical d-none d-sm-block flex-shrink-0"></div>
                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                        <span class="text-muted font-weight-medium d-none d-md-inline" style="font-size: 0.85rem;">Tiket Masalah /</span>
                        <span class="tm-tkt-id">#TKT-${data.id}</span>
                    </div>
                    <div class="d-flex align-items-center gap-1 flex-shrink-0 ml-1">
                        ${getPriorityBadgeHtml(data.prioritas)}
                        ${getStatusBadgeHtml(data.status)}
                    </div>
                </div>
                ${editBtnTopHtml ? `<div class="d-none d-md-block flex-shrink-0">${editBtnTopHtml}</div>` : ''}
            </div>
        `;
        $('#tm_subheader_nav').html(subheaderHtml).removeClass('d-none');

        // 1.5. MOBILE HERO CARD (Dark Navy - Shown exclusively on mobile view)
        let mobileHeroCardHtml = getMobileHeroCardHtml('Unit masih dalam inspeksi awal temuan belum ada penutupan progres.');

        // 2. PARSE DESKRIPSI MASALAH (INTERACTIVE CHECKLIST IN ROSE CARD)
        let issueData = parseIssueDescription(data.keterangan);
        let checkedKey = `sigapp_tm_checks_${data.id}`;
        let checkedItems = [];
        try {
            checkedItems = JSON.parse(localStorage.getItem(checkedKey) || '[]');
        } catch(e) { checkedItems = []; }

        let issueItemsHtml = issueData.items.map((item, idx) => {
            let isChecked = checkedItems.includes(idx);
            return `
                <li class="tm-issue-item ${isChecked ? 'is-checked' : ''}" data-item-idx="${idx}">
                    <input type="checkbox" class="tm-issue-check" data-tiket-id="${data.id}" data-idx="${idx}" ${isChecked ? 'checked' : ''}>
                    <span class="tm-issue-num">${idx + 1}</span>
                    <span class="tm-issue-text">${item}</span>
                </li>
            `;
        }).join('');

        let issueCardHtml = `
            <div class="tm-card-rose">
                <div class="d-flex align-items-start gap-3 mb-2">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="tm-title-rose mb-0"><i class="fas fa-exclamation-triangle"></i>DESKRIPSI MASALAH</h6>
                                <span class="tm-subtitle-rose d-block">Harus segera diperbaiki kontraktor</span>
                            </div>
                            <span class="tm-badge-points">${issueData.count} Poin Temuan</span>
                        </div>

                        <div class="tm-notice-banner mt-2">
                            <i class="fas fa-info-circle mr-1"></i> Centang nomor saat pengecekan fisik di lapangan:
                        </div>

                        <ul class="tm-issue-list">
                            ${issueItemsHtml}
                        </ul>
                    </div>
                </div>
            </div>
        `;

        // 3. LAMPIRAN DOKUMEN (AMBER PILL CARD)
        let mainPhoto = (data.foto && data.foto.length > 0) ? data.foto[0].url : base_url + 'assets/images/placeholder.jpg';
        let allUrlsStr = (data.foto && data.foto.length > 0) ? encodeURIComponent(JSON.stringify(data.foto.map(f => f.url))) : '[]';

       

        // 4. GALERI TEMUAN LAPANGAN (WHITE CARD WITH FOCUS PREVIEW & 4 THUMBNAILS)
        let thumbsHtml = '';
        if (data.foto && data.foto.length > 0) {
            let maxVisible = 4;
            let totalPhotos = data.foto.length;
            let showMoreBtn = totalPhotos > maxVisible;
            let limit = showMoreBtn ? 3 : totalPhotos;

            for (let i = 0; i < limit; i++) {
                let f = data.foto[i];
                let activeClass = (i === 0) ? 'active-thumb' : '';
                thumbsHtml += `
                    <button type="button" class="tm-thumb-btn ${activeClass}" data-index="${i}" data-url="${f.url}" title="Foto ${i + 1}">
                        <img src="${f.url}" alt="Thumbnail ${i + 1}">
                    </button>
                `;
            }

            if (showMoreBtn) {
                let remaining = totalPhotos - limit;
                thumbsHtml += `
                    <button type="button" class="tm-thumb-btn tm-thumb-more" onclick="window.openLightbox('${allUrlsStr}', ${limit})" title="Lihat semua foto">
                        <span>+${remaining} LAINNYA</span>
                    </button>
                `;
            }
        } else {
            thumbsHtml = '<div class="text-muted text-xs p-2">Tidak ada foto lampiran awal.</div>';
        }

        let photoCardHtml = `
            <div class="tm-card-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="font-weight-bold text-slate-800 text-xs text-uppercase d-block" style="letter-spacing: 0.5px;">GALERI TEMUAN LAPANGAN</span>
                        <span class="text-muted text-xs">${data.foto ? data.foto.length : 0} Foto Bukti Audit Tersimpan</span>
                    </div>
                    <button type="button" class="btn btn-link btn-sm p-0 font-weight-bold text-primary text-xs" onclick="window.openLightbox('${allUrlsStr}', 0)">
                        Tampilkan Semua
                    </button>
                </div>

                <!-- Main Focus Photo Box -->
                <div class="tm-focus-photo-box mb-2">
                    <img id="tm_focus_photo" src="${mainPhoto}" class="tm-focus-img" alt="Foto Utama">
                    <div class="tm-focus-badge">
                        <span class="badge-dot"></span>
                        <span id="tm_focus_label">Foto #1: ${data.foto && data.foto.length > 0 ? (data.foto[0].keterangan || 'Temuan Lapangan') : 'Bukti Utama'}</span>
                    </div>
                    ${(data.foto && data.foto.length > 0) ? `
                    <button type="button" class="tm-btn-see-full" id="btn_see_full_photo">
                        <i class="fas fa-search-plus mr-1"></i> Lihat Penuh
                    </button>
                    ` : ''}
                </div>

                <!-- 4-Column Thumbnail Grid -->
                <div class="tm-thumb-grid">
                    ${thumbsHtml}
                </div>

                <!-- Shortcut Ambil Foto Tambahan di Mobile -->
                <button type="button" class="tm-btn-add-photo-mobile" id="btn_mobile_quick_camera">
                    <i class="fas fa-camera text-primary"></i> Ambil Foto Tambahan / Progres
                </button>

                <div class="tm-gallery-tip mt-2">
                    <i class="fas fa-info-circle text-primary"></i>
                    <span>Klik salah satu foto untuk melihat perbesaran resolusi tinggi serta riwayat audit.</span>
                </div>
            </div>
        `;

        // 5. INFORMASI TIKET & PIC (2-COLUMN COMPACT GRID)
        let metadataGridHtml = `
            <div class="tm-metadata-grid">
                <!-- Card 1: Penanggung Jawab -->
                <div class="tm-meta-card">
                    <span class="tm-meta-card-label">PENANGGUNG JAWAB</span>
                    <div class="d-flex align-items-center">
                        <div class="tm-avatar-circle mr-2">
                            ${(data.pic_username || 'U').charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark text-truncate" style="font-size: 0.88rem; max-width: 130px;">${data.pic_username}</div>
                            <span class="text-muted text-xs d-block">Pelapor &amp; Pengawas</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Tanggal Dibuat -->
                <div class="tm-meta-card">
                    <span class="tm-meta-card-label">TANGGAL DIBUAT</span>
                    <div class="d-flex align-items-center">
                        <div class="tm-avatar-circle mr-2" style="background: #e2e8f0; color: #475569;">
                            <i class="far fa-calendar-alt"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 0.88rem;">${formattedDate}</div>
                            <span class="text-muted text-xs d-block">Waktu Pembuatan</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // 6. HISTORY PROGRESS SECTION
        let progressSectionHtml = `
            <div class="tm-progress-section">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
                    <div class="d-flex align-items-center">
                        <div class="tm-progress-accent-bar"></div>
                        <h6 class="mb-0 font-weight-bold text-slate-800" style="font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">
                            History Progress
                        </h6>
                    </div>
                    <span class="text-xs font-weight-bold text-muted" id="tm_progress_count">0 Catatan Aktivitas</span>
                </div>

                <div id="tm_form_progress_container" class="mb-4 d-none"></div>
                <div id="tm_progress_list"></div>
            </div>
        `;

        // Assemble All Detail Blocks
        $('#tm_detail_content').html(mobileHeroCardHtml + issueCardHtml + photoCardHtml + metadataGridHtml + progressSectionHtml);

        // 7. RENDER AUDIT FOOTER (DESKTOP & MOBILE STICKY BAR)
        let auditStatusHtml = '';
        if (isClosed) {
            auditStatusHtml = `
                <div class="tm-pill-warning" style="background: #f1f5f9; color: #475569; border-color: #cbd5e1;">
                    <i class="fas fa-lock"></i>
                    <span>Tiket ${data.status.toUpperCase()} (Terkunci)</span>
                </div>
            `;
        } else if (!hasAccess) {
            auditStatusHtml = `
                <div class="tm-pill-warning">
                    <i class="fas fa-ban"></i>
                    <span>Tidak Punya Akses Edit / Progress</span>
                </div>
            `;
        } else {
            auditStatusHtml = `
                <div class="tm-pill-warning" style="background: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                    <i class="fas fa-shield-alt"></i>
                    <span>Akses Diberikan (PIC / Tim Konstruksi)</span>
                </div>
            `;
        }

        let footerHtml = `
            <!-- Mobile Footer Layout -->
            <div class="d-flex d-md-none flex-column w-100">
                <div class="tm-audit-footer-status-row mb-1">
                    <div class="text-muted" style="font-size: 11px;">
                        <i class="far fa-clock mr-1"></i> Diubah: <strong>${formattedDate}</strong> (${data.pic_username})
                    </div>
                    <span class="badge badge-warning text-dark border px-2 py-1 font-weight-bold" style="font-size: 10px; background: #fef3c7; border-color: #fde68a;">
                        <i class="fas fa-lock mr-1"></i> Tinjau Lapangan
                    </span>
                </div>
                <div class="tm-mobile-bottom-actions">
                    <button type="button" class="tm-btn-mobile-secondary" id="btn_mobile_catat_temuan">
                        <i class="fas fa-edit mr-1"></i> Catat Temuan
                    </button>
                    <button type="button" class="tm-btn-mobile-primary" id="btn_mobile_mulai_perbaikan">
                        <i class="fas fa-check-circle mr-1"></i> Mulai Perbaikan
                    </button>
                </div>
            </div>

            <!-- Desktop Footer Layout -->
            <div class="d-none d-md-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-2 text-muted">
                    <i class="far fa-clock text-slate-400"></i>
                    <span>Terakhir diubah: <strong class="text-dark">${formattedDate} WIB</strong> oleh <strong class="text-dark">${data.pic_username}</strong></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    ${auditStatusHtml}
                    ${editBtnHtml}
                    ${actionBtnHtml}
                </div>
            </div>
        `;
        $('#tm_audit_footer').html(footerHtml).removeClass('d-none');

        // Checklist Change Handler
        $('.tm-issue-check').off('change').on('change', function() {
            let tId = $(this).data('tiket-id');
            let idx = $(this).data('idx');
            let isChecked = $(this).is(':checked');
            let $item = $(this).closest('.tm-issue-item');
            if (isChecked) {
                $item.addClass('is-checked');
            } else {
                $item.removeClass('is-checked');
            }
            let key = `sigapp_tm_checks_${tId}`;
            let currentList = [];
            try { currentList = JSON.parse(localStorage.getItem(key) || '[]'); } catch(e) { currentList = []; }
            if (isChecked && !currentList.includes(idx)) {
                currentList.push(idx);
            } else if (!isChecked) {
                currentList = currentList.filter(i => i !== idx);
            }
            localStorage.setItem(key, JSON.stringify(currentList));
        });

        // Event bindings for interactions
        window.currentActivePhotoIndex = 0;
        $('.tm-thumb-btn[data-index]').off('click').on('click', function() {
            let idx = parseInt($(this).data('index'));
            let url = $(this).data('url');
            $('.tm-thumb-btn').removeClass('active-thumb');
            $(this).addClass('active-thumb');
            $('#tm_focus_photo').attr('src', url);
            let photoLabel = (data.foto && data.foto[idx]) ? (data.foto[idx].keterangan || `Foto #${idx + 1}`) : `Foto #${idx + 1}`;
            $('#tm_focus_label').text(`Foto #${idx + 1}: ${photoLabel}`);
            window.currentActivePhotoIndex = idx;
        });

        $('#btn_see_full_photo').off('click').on('click', function() {
            window.openLightbox(allUrlsStr, window.currentActivePhotoIndex || 0);
        });

        $('#btn_back_to_list_custom').off('click').on('click', function() {
            $('#view_detail_tiket').addClass('d-none');
            $('#tm_subheader_nav').addClass('d-none');
            $('#tm_audit_footer').addClass('d-none');
            $('#view_list_tiket').removeClass('d-none');
            loadTiketList();
        });

        // Mobile Action Buttons Handlers
        $('#btn_mobile_quick_camera, #btn_mobile_mulai_perbaikan').off('click').on('click', function() {
            if (isClosed) {
                toastr.warning('Tiket telah selesai/ditutup dan tidak dapat ditambah progres.');
                return;
            }
            if (!hasAccess) {
                toastr.warning('Anda tidak memiliki akses untuk menambah progres pada tiket ini.');
                return;
            }
            let $container = $('#tm_form_progress_container');
            let isHidden = $container.hasClass('d-none') || $container.is(':hidden');
            if (isHidden) {
                $container.hide().removeClass('d-none').slideDown(300, function() {
                    $container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Trigger camera input if clicked from quick camera button
                    if ($(this).attr('id') === 'btn_mobile_quick_camera') {
                        $('#tm_progress_foto_camera').click();
                    }
                });
                $('#btn_toggle_add_progress').addClass('btn-secondary').removeClass('btn-primary').find('.btn-text').text('Tutup Form Progres');
            } else {
                $container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        $('#btn_mobile_catat_temuan').off('click').on('click', function() {
            if ($('.btn_edit_draft_tiket').length > 0) {
                $('.btn_edit_draft_tiket').first().click();
            } else {
                // Focus to checklist or scroll to form
                $('#btn_mobile_mulai_perbaikan').click();
            }
        });

        if(!isClosed) {
            setupProgressForm(data);
            $('#btn_toggle_add_progress').off('click').on('click', function() {
                let $container = $('#tm_form_progress_container');
                let isHidden = $container.hasClass('d-none') || $container.is(':hidden');
                
                if (isHidden) {
                    $container.hide().removeClass('d-none').slideDown(300, function() {
                        $container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                    $(this).html('<i class="fas fa-times mr-1"></i> <span class="btn-text">Batal Tambah Progress</span>');
                    $(this).removeClass('btn-primary').addClass('btn-secondary');
                } else {
                    $container.slideUp(300, function() {
                        $(this).addClass('d-none').css('display', '');
                    });
                    $(this).html('<i class="fas fa-plus mr-1"></i> <span class="btn-text">Tambah Progres Laporan</span>');
                    $(this).removeClass('btn-secondary').addClass('btn-primary');
                }
            });
        }

        $('.btn_edit_draft_tiket').off('click').on('click', function() {
            window.editTiket(data);
        });
    }

    function setupProgressForm(tiket) {
        let activeUserId = (typeof current_user_id !== 'undefined') ? current_user_id : (window.current_user_id || 0);
        let isPic = tiket.pic_user_id == activeUserId;
        progressSelectedFiles = [];

        let statusOptions = '';
        if(isPic) {
            statusOptions = `
                <div class="form-group mb-2">
                    <label class="tm-detail-label">Update Status (Opsional)</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">-- Tetap (${tiket.status.replace('_', ' ')}) --</option>
                        <option value="dalam_proses">Dalam Proses</option>
                        <option value="hold">Hold</option>
                        <option value="selesai">Selesai</option>
                        <option value="batal">Batal</option>
                    </select>
                </div>
            `;
        }

        let formHtml = `
            <div class="card bg-white border-0 shadow-sm rounded-12 mb-3 mt-3">
                <div class="card-body p-3">
                    <h6 class="font-weight-bold text-dark mb-3">
                        <i class="feather icon-edit-3 mr-1 text-primary"></i> Tambah Catatan Progress
                    </h6>
                    <form id="form_add_progress">
                        <input type="hidden" name="id_tiket_masalah" value="${tiket.id}">
                        <div class="form-group mb-3">
                            <textarea name="keterangan" id="progress_keterangan" class="form-control richtext" rows="3" placeholder="Tulis catatan atau pembaruan progres di sini..." required></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label class="tm-detail-label text-muted">Foto Progress (Opsional)</label>

                            <div class="drag-drop-zone p-3 mb-2 rounded-12 bg-light border-0" id="tm_progress_dropzone" style="border: 2px dashed #cbd5e1 !important;">
                                <p class="mb-2 text-xs font-weight-bold text-muted">Tarik & Lepas Foto di sini</p>
                                <div>
                                    <button type="button" class="btn btn-sm btn-white border rounded-pill mr-1 shadow-sm px-3" onclick="$('#foto_progress').click()"><i class="fas fa-folder-open mr-1"></i> Pilih File</button>
                                    <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-sm px-3" onclick="$('#foto_progress_camera').click()"><i class="fas fa-camera mr-1"></i> Kamera</button>
                                </div>
                            </div>

                            <input type="file" id="foto_progress" class="d-none" multiple accept="image/*">
                            <input type="file" id="foto_progress_camera" class="d-none" accept="image/*" capture="environment">
                            <div id="tm_progress_preview_container" class="upload-preview-container"></div>
                        </div>
                        ${statusOptions}
                        <div class="form-group mb-3 custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_pin_requested" name="is_pin_requested" value="1">
                            <label class="custom-control-label font-weight-bold text-dark" for="is_pin_requested">Request Pin ke Atas (Maks 3)</label>
                        </div>
                        <div class="d-flex flex-column flex-md-row justify-content-end mt-4 gap-2 border-top pt-3">
                            <button type="button" class="btn btn-light border rounded-pill px-4 mb-2 mb-md-0 order-2 order-md-1 font-weight-bold shadow-sm" id="btn_batal_progress">Batal</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 order-1 order-md-2 font-weight-bold shadow-sm">Kirim Progress</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        $('#tm_form_progress_container').html(formHtml);
        
        $('#btn_batal_progress').click(function() {
            Swal.fire({
                title: 'Batalkan Progress?',
                text: "Isian progress Anda akan hilang.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batal',
                cancelButtonText: 'Kembali',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#tm_form_progress_container').addClass('d-none');
                }
            });
        });

        if (typeof $.fn.richText === 'function') {
            $('#progress_keterangan').richText();
            $('#progress_keterangan').val('');
            $('#progress_keterangan').prev('.richText-editor').trigger('setContent', '');
        }

        // Progress file input handlers
        $('#foto_progress, #foto_progress_camera').on('change', function() {
            if (this.files.length > 0) {
                handleAddedFiles(this.files, 'progress');
                $(this).val('');
            }
        });

        // Progress Dropzone
        let pDropzone = $('#tm_progress_dropzone');
        pDropzone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            pDropzone.addClass('dragover');
        });
        pDropzone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            pDropzone.removeClass('dragover');
        });
        pDropzone.on('drop', function(e) {
            let files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleAddedFiles(files, 'progress');
            }
        });

        $('#form_add_progress').submit(async function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Konfirmasi Progress',
                text: 'Simpan catatan progress ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    let submitBtn = $(this).find('button[type="submit"]');
                    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

                    try {
                        let formData = new FormData(this);

                        // Append files from progressSelectedFiles
                        if (progressSelectedFiles.length > 0) {
                            for (let i = 0; i < progressSelectedFiles.length; i++) {
                                let file = progressSelectedFiles[i];
                                try {
                                    let compressedFile = await compressImage(file);
                                    formData.append('foto[]', compressedFile, compressedFile.name);
                                } catch (err) {
                                    formData.append('foto[]', file, file.name);
                                }
                            }
                        }

                        $.ajax({
                            url: base_url + 'api/tiket-masalah/add-progress',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(res) {
                                if(res.success) {
                                    Swal.fire('Berhasil', 'Progress berhasil ditambahkan', 'success');
                                    loadTiketDetail(tiket.id);
                                    
                                    // Reload tabel global jika ada (berada di halaman global tiket masalah)
                                    if (typeof window.tableTiketGlobal !== 'undefined') {
                                        window.tableTiketGlobal.ajax.reload(null, false);
                                    }
                                } else {
                                    Swal.fire('Error', res.message, 'error');
                                    submitBtn.prop('disabled', false).html('Simpan Progress');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyimpan progress', 'error');
                                submitBtn.prop('disabled', false).html('Simpan Progress');
                            }
                        });
                    } catch (error) {
                        Swal.fire('Error', 'Kesalahan sistem', 'error');
                        submitBtn.prop('disabled', false).html('Simpan Progress');
                    }
                }
            });
        });
    }

    function loadTiketProgress(id, isPic = false) {
        $.ajax({
            url: base_url + 'api/tiket-masalah/progress',
            type: 'POST',
            data: { id_tiket_masalah: id },
            success: function(res) {
                if(res.success) {
                    $('#tm_progress_count').text(res.data.length + ' Catatan Aktivitas');
                    let html = '';
                    if(res.data.length === 0) {
                        html = `
                            <div class="tm-empty-progress">
                                <div class="tm-empty-icon-circle">
                                    <i class="far fa-clock"></i>
                                </div>
                                <p class="text-xs font-weight-bold text-slate-700 mb-1">Belum ada pembaruan aktivitas perbaikan pada tiket ini.</p>
                                <p class="text-xs text-muted mb-0">Catatan revisi kontraktor dan verifikasi mandor akan tercatat otomatis di sini.</p>
                            </div>
                        `;
                    } else {
                        html = '<div class="tm-timeline">';
                        res.data.forEach(function(p, idx) {
                            let photos = '';
                            if(p.foto_urls && p.foto_urls.length > 0) {
                                photos = '<div class="tm-timeline-photos">';
                                let allUrlsStr = encodeURIComponent(JSON.stringify(p.foto_urls));
                                p.foto_urls.forEach((url, index) => {
                                    photos += `
                                        <a href="javascript:void(0)" onclick="window.openLightbox('${allUrlsStr}', ${index})" class="tm-timeline-thumb" title="Lampiran Foto ${index + 1}">
                                            <img src="${url}">
                                        </a>
                                    `;
                                });
                                photos += '</div>';
                            }

                            let statusChange = '';
                            if(p.status_sesudah && p.status_sesudah !== p.status_sebelum) {
                                statusChange = `
                                    <div>
                                        <span class="tm-status-change-pill">
                                            <i class="fas fa-sync-alt text-[9px]"></i> Status: <strong>${p.status_sebelum}</strong> &rarr; <strong>${p.status_sesudah}</strong>
                                        </span>
                                    </div>
                                `;
                            }
                            
                            let pinBadgeHtml = '';
                            if (p.is_pinned == 1) {
                                pinBadgeHtml = `<span class="tm-badge-pinned"><i class="fas fa-thumbtack text-xs"></i> Pinned</span>`;
                            } else if (p.is_pin_requested == 1) {
                                pinBadgeHtml = `<span class="tm-badge-req-pin"><i class="fas fa-hand-paper text-xs"></i> Req Pin</span>`;
                            } else if (idx === 0) {
                                pinBadgeHtml = `<span class="tm-badge-newest">Terbaru</span>`;
                            }
                            
                            let pinActionHtml = '';
                            if (isPic) {
                                if (p.is_pinned == 1) {
                                    pinActionHtml = `<button type="button" class="tm-btn-pin-action is-pinned" onclick="window.togglePinProgress(${p.id}, ${id})" title="Lepas Sematan"><i class="fas fa-thumbtack" style="transform: rotate(45deg);"></i> <span>Unpin</span></button>`;
                                } else {
                                    pinActionHtml = `<button type="button" class="tm-btn-pin-action" onclick="window.togglePinProgress(${p.id}, ${id})" title="Sematkan ke Atas"><i class="fas fa-thumbtack"></i> <span>Pin</span></button>`;
                                }
                            }
                            
                            let tglProgress = new Date(p.created_at);
                            let formattedDate = tglProgress.toLocaleDateString('id-ID', {day: '2-digit', month: 'short'}) + ' • ' + tglProgress.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});

                            html += `
                            <div class="tm-timeline-item ${p.is_pinned == 1 ? 'is-pinned-item' : ''}">
                                <div class="tm-timeline-node">
                                    ${(p.user_username || 'U').charAt(0).toUpperCase()}
                                </div>
                                <div class="tm-timeline-card ${p.is_pinned == 1 ? 'card-pinned' : ''}">
                                    <div class="tm-timeline-header-bar">
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <span class="tm-timeline-user">${p.user_username}</span>
                                            <span class="text-muted" style="font-size: 10px;">•</span>
                                            <span class="tm-timeline-time">${formattedDate}</span>
                                            ${pinBadgeHtml}
                                        </div>
                                        <div>
                                            ${pinActionHtml}
                                        </div>
                                    </div>
                                    <p class="tm-timeline-text">${p.keterangan}</p>
                                    ${statusChange}
                                    ${photos}
                                </div>
                            </div>
                            `;
                        });
                        html += '</div>';
                    }
                    $('#tm_progress_list').html(html);
                }
            }
        });
    }

    // --- UTILS & BADGE HELPERS ---
    window.printTiket = function(id) {
        window.print();
    };

    function getPriorityBadgeHtml(prio) {
        prio = (prio || 'normal').toLowerCase();
        if (prio === 'urgent') {
            return `<span class="tm-badge-urgent"><span class="dot-ping"></span> URGENT</span>`;
        } else if (prio === 'medium') {
            return `<span class="tm-badge-medium"><i class="fas fa-exclamation-circle text-xs"></i> MEDIUM</span>`;
        } else if (prio === 'low') {
            return `<span class="tm-badge-low"><i class="fas fa-arrow-down text-xs"></i> LOW</span>`;
        } else if (prio === 'laporan') {
            return `<span class="badge badge-prio-laporan" style="background-color: #00cfe8; color: #fff; padding: 4px 10px; border-radius: 9999px; font-weight: 700; font-size: 11px;">LAPORAN</span>`;
        }
        return `<span class="tm-badge-normal"><i class="fas fa-info-circle text-xs"></i> NORMAL</span>`;
    }

    function getStatusBadgeHtml(status) {
        status = (status || 'dibuat').toLowerCase();
        let label = status.replace('_', ' ').toUpperCase();
        let cls = 'tm-status-dibuat';

        if (status === 'selesai') cls = 'tm-status-selesai';
        else if (status === 'dalam_proses') cls = 'tm-status-proses';
        else if (status === 'hold') cls = 'tm-status-hold';
        else if (status === 'batal') cls = 'tm-status-batal';
        else if (status === 'draft') cls = 'tm-status-dibuat';

        return `<span class="tm-badge-status ${cls}"><span class="tm-status-dot"></span> ${label}</span>`;
    }

    function getSelectedProyekId() {
        if ($('#f_id_proyek').length) {
            return $('#f_id_proyek').val();
        }
        return window.SIGAPP && window.SIGAPP.activeProyekId ? window.SIGAPP.activeProyekId : 1;
    }

    async function compressImage(file) {
        if (typeof imageCompression !== 'function') {
            console.warn("browser-image-compression not loaded, skipping client-side compression");
            return file;
        }
        const options = {
            maxSizeMB: 2,
            maxWidthOrHeight: 1920,
            useWebWorker: true,
            initialQuality: 0.7
        };
        try {
            return await imageCompression(file, options);
        } catch (error) {
            console.error("Compression error:", error);
            throw error;
        }
    }

    window.togglePinProgress = function(idProgress, idTiket) {
        Swal.fire({
            title: 'Konfirmasi',
            text: "Ubah status pin untuk progress ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'api/tiket-masalah/toggle-pin',
                    type: 'POST',
                    data: { id_progress: idProgress },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Berhasil', res.message, 'success');
                            // Reload detail/progress to show new pin status
                            window.loadTiketDetail(idTiket);
                        } else {
                            Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                        }
                    },
                    error: function(err) {
                        let msg = err.responseJSON && err.responseJSON.messages ? err.responseJSON.messages : 'Gagal menghubungi server';
                        if (typeof msg === 'object') msg = Object.values(msg).join(', ');
                        Swal.fire('Gagal', msg, 'error');
                    }
                });
            }
        });
    };

    window.openLightbox = function(encodedUrls, activeIndex) {
        try {
            let urls = JSON.parse(decodeURIComponent(encodedUrls));
            let innerHtml = '';
            urls.forEach((url, i) => {
                let activeClass = (i === activeIndex) ? 'active' : '';
                innerHtml += `
                    <div class="carousel-item ${activeClass}">
                        <img class="d-block w-100" src="${url}" style="max-height: 85vh; object-fit: contain;">
                    </div>
                `;
            });
            
            // Hide controls if only 1 image
            if (urls.length <= 1) {
                $('#tm_lightbox_carousel .carousel-control-prev, #tm_lightbox_carousel .carousel-control-next').hide();
            } else {
                $('#tm_lightbox_carousel .carousel-control-prev, #tm_lightbox_carousel .carousel-control-next').show();
            }

            $('#tm_lightbox_inner').html(innerHtml);
            $('#tm_lightbox_modal').modal('show');
        } catch (e) {
            console.error("Error opening lightbox:", e);
        }
    };
});
