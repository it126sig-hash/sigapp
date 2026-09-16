$(document).ready(function() {
    // Inisialisasi elemen
    const modalMasalah = $('#modal_tiket_masalah');
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


    window.openTiketMasalah = function(refType, refId) {
        currentRefType = refType;
        currentRefId = refId;
        selectedFiles = [];
        progressSelectedFiles = [];

        // Reset view
        $('#list_tiket_masalah').html('<div class="text-center p-4"><span class="spinner-border text-primary"></span></div>');
        
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
        
        // Beralih view dari detail ke form
        $('#view_detail_tiket').addClass('d-none');
        $('#form_buat_tiket').removeClass('d-none');
        
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
                <div class="text-center p-4 p-md-5 bg-white rounded-12 border">
                    <i class="feather icon-check-circle text-success font-large-2 mb-2"></i>
                    <h6 class="font-weight-bold text-dark mb-1">Tidak Ada Tiket Masalah</h6>
                    <p class="text-muted text-sm mb-0">Belum ada laporan kendala untuk unit/item ini.</p>
                </div>
            `;
        } else {
            data.forEach(function(item) {
                let tgl = new Date(item.created_at);
                let formattedDate = tgl.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'});

                let thumbHtml = '';
                if (item.foto_url_1) {
                    thumbHtml = `
                        <div class="position-relative mr-md-3 mb-3 mb-md-0 mx-auto mx-md-0" style="flex-shrink: 0; width: 100px; height: 100px; max-width: 100%;">
                            <img src="${item.foto_url_1}" class="w-100 h-100 rounded" style="object-fit: cover; border: 1px solid #edf0f2; padding: 3px; background: #fafafa;">
                            <div class="position-absolute" style="bottom: 4px; left: 4px; background: rgba(0,0,0,0.7); color: white; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600;">
                                <i class="fas fa-image mr-1"></i> ${item.foto_count} Foto
                            </div>
                        </div>
                    `;
                } else {
                    thumbHtml = `
                        <div class="position-relative mr-md-3 mb-3 mb-md-0 mx-auto mx-md-0 d-none d-md-block" style="flex-shrink: 0; width: 100px; height: 100px;">
                            <div style="border: 1px dashed #cbd5e1; border-radius: 8px; height: 100%; display: flex; align-items: center; justify-content: center; background: #f8fafc; color: #94a3b8; font-size: 0.7rem;">
                                <i class="fas fa-image mb-1 mr-1"></i> No Foto
                            </div>
                        </div>
                    `;
                }

                let lastUpdateHtml = '';
                if (item.last_progress_keterangan) {
                    let lastDate = new Date(item.last_progress_date);
                    let formattedLastDate = lastDate.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'});
                    lastUpdateHtml = `<div class="text-xs text-muted text-truncate" style="max-width: 100%;"><i class="far fa-clock mr-1"></i> Last update (${formattedLastDate}): ${item.last_progress_keterangan}</div>`;
                } else {
                    lastUpdateHtml = `<div class="text-xs text-muted"><i class="far fa-clock mr-1"></i> Belum ada update progress</div>`;
                }

                html += `
                <div class="tm-list-card prio-${item.prioritas} mb-3 cursor-pointer" onclick="loadTiketDetail(${item.id})" style="border-radius: 12px; overflow: hidden; border: 1px solid #edf0f2; box-shadow: 0 4px 12px rgba(0,0,0,0.03); background: #ffffff;">
                    <div class="d-flex flex-column flex-md-row align-items-start w-100 p-2 p-md-3">
                        ${thumbHtml}
                        <div class="flex-grow-1 d-flex flex-column w-100" style="min-width: 0;">
                            <!-- Top Row -->
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center flex-wrap mb-2 mb-md-0">
                                    <div class="mr-2 mb-1">${getPriorityBadgeHtml(item.prioritas)}</div>
                                    <div class="text-xs font-weight-medium mb-1" style="color: #64748b;">${formattedDate} &bull; #TKT-${item.id}</div>
                                </div>
                                <div class="text-left text-md-right mt-1 mt-md-0">
                                    <span class="d-inline-block d-md-block text-md-right mb-1 mr-2 mr-md-0" style="font-size: 0.6rem; font-weight: 800; letter-spacing: 1px; color: #94a3b8;">STATUS</span>
                                    ${getStatusBadgeHtml(item.status)}
                                </div>
                            </div>
                            
                            <!-- Title & Users -->
                            <div class="mb-2">
                                <div class="text-xs mb-1" style="color: #94a3b8; font-weight: 500;">Deskripsi Masalah</div>
                                <h5 class="font-weight-bold text-dark tm-title-text mb-2 text-truncate" style="line-height: 1.4; font-size: 1.05rem;">${item.keterangan}</h5>
                                
                                <div class="d-flex flex-wrap mt-2">
                                    <span class="badge bg-light text-dark border-0 text-xs shadow-none mr-2 mb-1" style="border-radius: 20px; padding: 5px 12px; font-weight: 500;">
                                        <i class="far fa-user text-secondary mr-1"></i> PIC: ${item.pic_username}
                                    </span>
                                    ${item.assigned_users_list ? `
                                    <span class="badge bg-light text-dark border-0 text-xs shadow-none mr-2 mb-1" style="border-radius: 20px; padding: 5px 12px; font-weight: 500;">
                                        <i class="fas fa-users text-secondary mr-1"></i> Dilibatkan: ${item.assigned_users_list}
                                    </span>` : ''}
                                </div>
                            </div>
                            
                            <!-- Bottom Row -->
                            <div class="mt-auto">
                                <hr class="my-2" style="border-top: 1px dashed #e2e8f0;">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center pt-1 overflow-hidden">
                                    ${lastUpdateHtml}
                                    <div class="text-primary text-xs font-weight-bold mt-2 mt-md-0" style="white-space: nowrap;">
                                        Detail Tiket <i class="fas fa-chevron-right ml-1" style="font-size: 0.7rem;"></i>
                                    </div>
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
        $('#view_list_tiket').addClass('d-none');
        $('#form_buat_tiket').removeClass('d-none');

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

    $('#btn_batal_buat_tiket').click(function() {
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
                $('#form_buat_tiket').addClass('d-none');
                $('#view_list_tiket').removeClass('d-none');
                window.currentEditTiketId = null; // Clear edit ID on cancel
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

    function renderTiketDetail(data) {
        let styleHtml = `
        <style>
            .tm-btn-action { width: 100%; }
            .transition-all { transition: all 0.3s ease; }
            @media (min-width: 768px) {
                .tm-btn-action { width: auto; min-width: 150px; }
            }
        </style>
        `;
        let tglDetail = new Date(data.created_at);
        let formattedDate = tglDetail.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});

        let photos = '';
        if(data.foto && data.foto.length > 0) {
            photos = '<div class="d-flex flex-wrap gap-2 mt-2">';
            let allUrlsStr = encodeURIComponent(JSON.stringify(data.foto.map(f => f.url)));
            data.foto.forEach((f, index) => {
                photos += `<a href="javascript:void(0)" onclick="window.openLightbox('${allUrlsStr}', ${index})"><img src="${f.url}" class="img-thumb-grid"></a>`;
            });
            photos += '</div>';
        } else {
            photos = '<div class="text-muted text-xs">Tidak ada lampiran foto awal.</div>';
        }

        let isCreator = (window.current_user_id == data.pic_user_id);
        let isSupervisor = (typeof window.is_supervisor_manager !== 'undefined' && window.is_supervisor_manager);
        let isAssigned = data.assigned_users && data.assigned_users.some(u => u.id == window.current_user_id);
        let hasAccess = isCreator || isSupervisor || isAssigned;

        let isClosed = data.status === 'batal' || data.status === 'selesai';
        let actionBtnHtml = '';
        if (isClosed) {
            actionBtnHtml = `
                <div class="alert alert-secondary text-center text-xs mb-0 rounded-12 tm-btn-action d-flex align-items-center justify-content-center">
                    <i class="fas fa-lock mr-1"></i> Tiket sudah ${data.status.toUpperCase()} (Terkunci)
                </div>
            `;
        } else if (!hasAccess) {
            actionBtnHtml = `
                <div class="alert alert-warning text-center text-xs mb-0 rounded-12 tm-btn-action d-flex align-items-center justify-content-center" title="Hanya pihak yang dilibatkan yang dapat menambah progres">
                    <i class="fas fa-ban mr-1"></i> Tidak Punya Akses
                </div>
            `;
        } else {
            actionBtnHtml = `
                <button class="btn btn-primary font-weight-bold py-1 px-1 shadow-sm rounded-12 tm-btn-action transition-all" id="btn_toggle_add_progress">
                    <i class="fas fa-plus mr-1"></i> <span class="btn-text">Tambah Progres Laporan</span>
                </button>
            `;
        }

        let editBtnTopHtml = '';
        let editBtnHtml = '';
        if (data.status !== 'selesai' && data.status !== 'batal' && (isCreator || isSupervisor)) {
            let labelEdit = data.status === 'draft' ? (isCreator ? "Edit Draft" : "Edit & Ambil Alih") : "Edit Tiket";
            editBtnTopHtml = `
                <button class="btn btn-sm btn-light border rounded-pill px-3 font-weight-bold shadow-sm transition-all btn_edit_draft_tiket" data-id="${data.id}">
                    <i class="fas fa-edit mr-1"></i> ${labelEdit}
                </button>
            `;
            editBtnHtml = `
                <button class="btn btn-warning font-weight-bold py-2 px-4 shadow-sm rounded-12 tm-btn-action transition-all btn_edit_draft_tiket" data-id="${data.id}">
                    <i class="fas fa-edit mr-1"></i> ${labelEdit}
                </button>
            `;
        }

        let mainPhoto = (data.foto && data.foto.length > 0) ? data.foto[0].url : base_url + 'assets/images/placeholder.jpg';
        let allUrlsStr = (data.foto && data.foto.length > 0) ? encodeURIComponent(JSON.stringify(data.foto.map(f => f.url))) : '[]';
        
        let photosHtml = '';
        if(data.foto && data.foto.length > 0) {
            data.foto.forEach((f, index) => {
                photosHtml += `
                <a href="javascript:void(0)" onclick="window.openLightbox('${allUrlsStr}', ${index})" class="border rounded-12 d-flex align-items-center justify-content-center bg-white p-1 mr-2 mb-2" style="width: 60px; height: 60px;">
                    <img src="${f.url}" class="w-100 h-100 rounded" style="object-fit: cover;">
                </a>`;
            });
        }

        let assignedHtml = '';
        if (data.assigned_users && data.assigned_users.length > 0) {
            assignedHtml = data.assigned_users.map(u => `<span class="badge bg-light text-primary border-0 rounded-pill px-2 py-1 text-xs" style="background-color: #f3e8ff !important; color: #7e22ce !important;"><i class="far fa-user mr-1"></i>${u.username}</span>`).join('');
        } else {
            assignedHtml = '-';
        }

        let html = `
            <!-- Custom Top Navbar-like Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 px-1">
                <div class="d-flex flex-wrap align-items-center mb-2 mb-md-0 w-100 w-md-auto">
                    <button class="btn btn-sm btn-light border rounded-pill py-1 px-3 mr-3 mb-2" id="btn_back_to_list_custom">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                    </button>
                    <div class="mr-3 text-muted d-none d-md-block mb-2" style="font-size: 1.2rem;">|</div>
                    <h5 class="mb-2 mr-3 font-weight-bold text-muted d-none d-md-block">Tiket Masalah <span class="mx-1">/</span> <span class="text-dark">#TKT-${data.id}</span></h5>
                    <div class="d-flex flex-wrap align-items-center mb-2">
                        <div class="mr-2">${getPriorityBadgeHtml(data.prioritas)}</div>
                        <div>${getStatusBadgeHtml(data.status)}</div>
                    </div>
                </div>
                <div class="d-flex w-100 w-md-auto justify-content-start justify-content-md-end">
                    <button class="btn btn-sm btn-light border rounded-circle shadow-sm mr-2" style="width: 32px; height: 32px; padding: 0;"><i class="fas fa-print text-muted"></i></button>
                    ${editBtnTopHtml}
                </div>
            </div>

            <div class="row">
                <!-- KOLOM KIRI: Visual & Tombol -->
                <div class="col-md-5 mb-4 mb-md-0">
                    <div class="card border-0 shadow-sm rounded-12 mb-0">
                        <div class="card-header bg-white border-bottom p-2 p-md-3 d-flex justify-content-between align-items-center rounded-top-12">
                            <div class="d-flex align-items-center">
                                <div class="mr-2" style="background: #fff8e6; color: #f59e0b; padding: 6px 10px; border-radius: 8px;">
                                    <i class="far fa-file-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.9rem;">Lampiran Dokumen</h6>
                                    <div class="text-muted text-xs text-uppercase font-weight-bold mt-1">Laporan #${data.id}</div>
                                </div>
                            </div>
                            <div class="d-flex text-muted">
                                <i class="fas fa-search-plus cursor-pointer p-1 mr-2"></i>
                                <i class="fas fa-download cursor-pointer p-1"></i>
                            </div>
                        </div>
                        <div class="card-body p-2 p-md-3" style="background-color: #f8fafc; border-radius: 0 0 12px 12px;">
                            <div class="position-relative mb-3 rounded-12 overflow-hidden border bg-white d-flex align-items-center justify-content-center shadow-sm" style="height: 350px;">
                                <img src="${mainPhoto}" class="mw-100 mh-100 p-2" style="object-fit: contain;">
                                ${(data.foto && data.foto.length > 0) ? `
                                <button class="btn btn-dark btn-sm position-absolute rounded-pill px-4 py-2 shadow" onclick="window.openLightbox('${allUrlsStr}', 0)" style="bottom: 15px; right: 15px; background: rgba(30,41,59,0.85); border: none; font-weight: 600;">
                                    <i class="fas fa-search mr-2"></i> Lihat Penuh
                                </button>
                                ` : ''}
                            </div>
                            <div class="d-flex flex-wrap">
                                ${photosHtml}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: Detail Info & History -->
                <div class="col-md-7">
                    
                    <!-- Alert Deskripsi -->
                    <div class="alert alert-danger d-flex p-2 p-md-3 rounded-12 mb-3 shadow-sm border-0" style="background-color: #fff1f1;">
                        <div class="mr-3 mt-1">
                            <div style="background: #fecdd3; color: #e11d48; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 1rem;"></i>
                            </div>
                        </div>
                        <div>
                            <div class="font-weight-bold text-danger mb-1 text-xs text-uppercase" style="letter-spacing: 0.5px;">Deskripsi Masalah</div>
                            <div class="text-dark" style="font-size: 0.95rem; font-weight: 500;">${data.keterangan}</div>
                        </div>
                    </div>

                    <!-- 3 Columns Info -->
                    <div class="row mb-4">
                        <div class="col-12 col-md-4 mb-2 mb-md-0 pr-md-1">
                            <div class="card bg-white shadow-sm border-0 rounded-12 p-2 p-md-3 h-100">
                                <div class="tm-detail-label mb-2" style="font-size: 0.65rem; color: #94a3b8;">PENANGGUNG JAWAB</div>
                                <div class="d-flex align-items-center mt-auto">
                                    <div class="avatar-circle mr-2" style="width:28px; height:28px; font-size:12px; background: #2b5cbe;">${data.pic_username.charAt(0).toUpperCase()}</div>
                                    <span class="font-weight-bold text-dark text-sm">${data.pic_username}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-2 mb-md-0 px-md-1">
                            <div class="card bg-white shadow-sm border-0 rounded-12 p-2 p-md-3 h-100">
                                <div class="tm-detail-label mb-2" style="font-size: 0.65rem; color: #94a3b8;">TANGGAL DIBUAT</div>
                                <div class="d-flex align-items-center text-dark font-weight-bold text-sm mt-auto">
                                    <i class="far fa-calendar-alt text-muted mr-2" style="font-size: 1.1rem;"></i> ${formattedDate}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 pl-md-1">
                            <div class="card bg-white shadow-sm border-0 rounded-12 p-2 p-md-3 h-100">
                                <div class="tm-detail-label mb-2" style="font-size: 0.65rem; color: #94a3b8;">USER DILIBATKAN</div>
                                <div class="d-flex flex-wrap mt-auto">
                                    ${assignedHtml}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History Progress Card -->
                    <div class="card border-0 shadow-sm rounded-12 mb-4">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center rounded-top-12">
                            <div class="d-flex align-items-center">
                                <div style="width: 4px; height: 20px; background: #2057a3; border-radius: 2px;" class="mr-2"></div>
                                <h5 class="mb-0 font-weight-bold text-dark">History Progress</h5>
                            </div>
                            <div class="text-muted text-xs font-weight-bold" id="tm_progress_count">0 Catatan Aktivitas</div>
                        </div>
                        <div class="card-body p-3 p-md-4" style="background-color: #f8fafc; border-radius: 0 0 12px 12px;">
                            <div id="tm_form_progress_container" class="mb-4 d-none"></div>
                            <div id="tm_progress_list"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sticky Footer Tombol -->
            <div class="position-sticky bg-white p-2 px-md-4 border-top shadow-lg d-flex flex-column flex-md-row justify-content-between align-items-center" style="bottom: 0; margin: 0 -16px -16px -16px; z-index: 1020; border-radius: 0 0 0.3rem 0.3rem;">
                <div class="text-muted text-xs mb-2 mb-md-0">
                    Terakhir diubah: <strong>${formattedDate} WIB</strong> oleh <strong>${data.pic_username}</strong>
                </div>
                <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
                    ${editBtnHtml}
                    ${actionBtnHtml}
                </div>
            </div>
        `;

        $('#tm_detail_content').html(styleHtml + html);

        $('#btn_back_to_list_custom').click(function() {
            $('#view_detail_tiket').addClass('d-none');
            $('#view_list_tiket').removeClass('d-none');
            loadTiketList();
        });

        // Setup Form Progress logic
        if(!isClosed) {
            setupProgressForm(data);
            $('#btn_toggle_add_progress').click(function() {
                let $container = $('#tm_form_progress_container');
                let isHidden = $container.hasClass('d-none') || $container.is(':hidden');
                
                if (isHidden) {
                    $container.hide().removeClass('d-none').slideDown(300);
                    $(this).html('<i class="fas fa-times mr-1"></i> <span class="btn-text">Batal</span>');
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
        
        // Setup Edit Draft logic
        $('#btn_edit_draft_tiket').click(function() {
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
                        html = '<div class="text-muted text-center p-4">Belum ada riwayat progress.</div>';
                    } else {
                        html = '<div class="tm-timeline">';
                        res.data.forEach(function(p, idx) {
                            let photos = '';
                            if(p.foto_urls && p.foto_urls.length > 0) {
                                photos = '<div class="d-flex flex-wrap mt-2">';
                                let allUrlsStr = encodeURIComponent(JSON.stringify(p.foto_urls));
                                p.foto_urls.forEach((url, index) => {
                                    photos += `<a href="javascript:void(0)" onclick="window.openLightbox('${allUrlsStr}', ${index})" class="mr-2 mb-2"><img src="${url}" class="img-thumb-grid shadow-sm"></a>`;
                                });
                                photos += '</div>';
                            }

                            let statusChange = '';
                            if(p.status_sesudah && p.status_sesudah !== p.status_sebelum) {
                                statusChange = `
                                    <div class="mt-2 text-xs text-muted">
                                        <i class="feather icon-check-circle text-success mr-1"></i> Ubah status: <strong>${p.status_sebelum}</strong> &rarr; <strong>${p.status_sesudah}</strong>
                                    </div>
                                `;
                            }
                            
                            let pinHtml = '';
                            if (p.is_pinned == 1) {
                                pinHtml = `<span class="badge badge-light-warning border border-warning text-xs font-weight-bold ml-2 shadow-sm rounded-pill px-2"><i class="fas fa-thumbtack mr-1"></i> Pinned</span>`;
                            } else if (p.is_pin_requested == 1) {
                                pinHtml = `<span class="badge badge-light-info border border-info text-xs font-weight-bold ml-2 shadow-sm rounded-pill px-2"><i class="fas fa-hand-paper mr-1"></i> Req Pin</span>`;
                            } else if (idx === 0) {
                                pinHtml = `<span class="badge bg-white border border-warning text-warning text-xs font-weight-bold ml-2 shadow-sm rounded-pill px-2" style="font-size: 0.65rem;">Terbaru</span>`;
                            }
                            
                            let pinActionHtml = '';
                            if (isPic) {
                                if (p.is_pinned == 1) {
                                    pinActionHtml = `<button type="button" class="btn btn-sm btn-light border text-danger ml-2 rounded-pill px-3 shadow-sm" onclick="window.togglePinProgress(${p.id}, ${id})"><i class="fas fa-thumbtack" style="transform: rotate(45deg);"></i> Unpin</button>`;
                                } else {
                                    pinActionHtml = `<button type="button" class="btn btn-sm btn-light border text-warning ml-2 rounded-pill px-3 shadow-sm" onclick="window.togglePinProgress(${p.id}, ${id})"><i class="fas fa-thumbtack"></i> Pin</button>`;
                                }
                            }
                            
                            let bgClass = p.is_pinned == 1 ? 'bg-light-warning' : 'bg-white';

                            let tglProgress = new Date(p.created_at);
                            let formattedDate = tglProgress.toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit'}) + ', ' + tglProgress.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});

                            html += `
                            <div class="tm-timeline-item">
                                <div class="tm-timeline-dot bg-white" style="width: 14px; height: 14px; left: -24px; top: 4px; border: 2px solid #10b981 !important; box-shadow: none;"></div>
                                <div class="tm-timeline-header mb-2">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle mr-2" style="width:20px; height:20px; font-size:9px; background: #2057a3;">${p.user_username.charAt(0).toUpperCase()}</div>
                                            <span class="tm-timeline-user text-dark" style="font-size: 0.85rem;">${p.user_username}</span>
                                            <span class="text-muted mx-2" style="font-size: 0.6rem;">•</span>
                                            <span class="tm-timeline-time">${formattedDate}</span>
                                        </div>
                                        <div>
                                            ${pinHtml}
                                        </div>
                                    </div>
                                </div>
                                <div class="tm-timeline-card ${bgClass} shadow-sm border rounded-12 p-3 d-flex justify-content-between align-items-center" style="background-color: #f8fafc !important;">
                                    <div class="flex-grow-1">
                                        <p class="mb-0 text-dark" style="font-size: 0.9rem; line-height: 1.5;">${p.keterangan}</p>
                                        ${statusChange}
                                        ${photos}
                                    </div>
                                    <div class="ml-3">
                                        ${pinActionHtml}
                                    </div>
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
    function getPriorityBadgeHtml(prio) {
        let cls = 'badge-prio-normal';
        if (prio === 'urgent') cls = 'badge-prio-urgent';
        else if (prio === 'medium') cls = 'badge-prio-medium';
        else if (prio === 'low') cls = 'badge-prio-low';
        else if (prio === 'laporan') cls = 'badge-prio-laporan';

        return `<span class="badge-prio ${cls}">${prio.toUpperCase()}</span>`;
    }

    function getStatusBadgeHtml(status) {
        let cls = 'badge-status-dibuat';
        let label = status.replace('_', ' ').toUpperCase();

        if (status === 'selesai') cls = 'badge-status-selesai';
        else if (status === 'dalam_proses') cls = 'badge-status-proses';
        else if (status === 'hold') cls = 'badge-status-hold';
        else if (status === 'batal') cls = 'badge-status-batal';
        else if (status === 'draft') cls = 'badge-status-draft';

        return `<span class="badge-status-pill ${cls}"><span class="dot"></span> ${label}</span>`;
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
