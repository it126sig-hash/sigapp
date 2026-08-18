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
                            <i class="fas fa-check"></i> Pilih Seleksi
                        </button>
                        <button type="button" class="btn btn-danger btn-round btn-sm" id="tm_btn_batal_seleksi_manual">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        
                        <div class="border-left mx-1" style="height: 24px;"></div>
                        
                        <div class="custom-control custom-switch m-0" style="padding-left: 2.25rem;">
                            <input type="checkbox" value="1" class="custom-control-input" id="tm_tambah_jalan" name="tm_tambah_jalan" onchange="if($('#tambah_jalan').length === 0){ $('body').append('<input type=\\'checkbox\\' class=\\'d-none\\' id=\\'tambah_jalan\\' name=\\'tambah_jalan\\' />'); } $('#tambah_jalan').prop('checked', this.checked).trigger('change'); if(typeof hapus_seleksi === 'function') hapus_seleksi();" />
                            <label class="custom-control-label font-weight-bold" for="tm_tambah_jalan" style="cursor: pointer; padding-top: 2px;">Manual Seleksi</label>
                        </div>
                        
                        <div class="border-left mx-1" style="height: 24px;"></div>
                        
                        <button type="button" class="btn btn-warning btn-round btn-sm" id="tm_btn_undo_seleksi_manual" onclick="if(typeof undo_manual_selection === 'function') undo_manual_selection();">
                            <i class="fas fa-undo"></i> Undo Titik
                        </button>
                    </div>
                `;
                $('body').append(html);
            } else {
                $('#tm_container_manual_seleksi').show();
            }
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

    window.editTiketDraft = function(data) {
        window.currentEditTiketId = data.id;
        
        // Beralih view dari detail ke form
        $('#view_detail_tiket').addClass('d-none');
        $('#form_buat_tiket').removeClass('d-none');
        
        // Reset file queue karena saat edit belum support hapus foto lama via form ini
        selectedFiles = [];
        if (typeof renderFilePreviews === 'function') renderFilePreviews();

        // Populate field standar
        $('#form_buat_tiket_form [name="tanggal_masalah"]').val(data.tanggal_masalah);
        if (typeof $.fn.flatpickr === 'function') {
            $('#form_buat_tiket_form [name="tanggal_masalah"]').flatpickr({ dateFormat: 'Y-m-d' });
        }
        
        if (data.tanggal_kunjungan) {
            $('#form_buat_tiket_form [name="tanggal_kunjungan"]').val(data.tanggal_kunjungan);
            if (typeof $.fn.flatpickr === 'function') {
                $('#form_buat_tiket_form [name="tanggal_kunjungan"]').flatpickr({ dateFormat: 'Y-m-d' });
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

        // Assigned users diabaikan dulu untuk simplicity di sisi UI (bisa ditambahkan jika perlu)
        initAssignedUsersSelect2();
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
            html = `
                <div class="text-center p-5 bg-white rounded-12 border">
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
                    thumbHtml = `<img src="${item.foto_url_1}" class="rounded border mr-2" style="width: 48px; height: 48px; object-fit: cover;">`;
                }

                let lastUpdateHtml = '';
                if (item.last_progress_keterangan) {
                    let lastDate = new Date(item.last_progress_date);
                    let formattedLastDate = lastDate.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'});
                    lastUpdateHtml = `<div class="text-xs text-muted mt-1 text-truncate" style="max-width: 380px;"><strong>Last update (${formattedLastDate}):</strong> ${item.last_progress_keterangan}</div>`;
                }

                html += `
                <div class="tm-list-card prio-${item.prioritas} mb-3 p-3 cursor-pointer" onclick="loadTiketDetail(${item.id})">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 pr-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                ${getPriorityBadgeHtml(item.prioritas)}
                                <span class="text-xs text-muted font-weight-medium">
                                    <i class="feather icon-calendar mr-1"></i>${formattedDate}
                                </span>
                                <span class="text-xs text-muted font-weight-bold ml-1">#TKT-${item.id}</span>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-2">${item.keterangan}</h6>
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                ${thumbHtml}
                                <div>
                                    <div class="d-flex gap-2">
                                        <span class="badge-meta">
                                            <i class="fas fa-image"></i> ${item.foto_count} Foto
                                        </span>
                                        <span class="badge-meta ml-1">
                                            <i class="fas fa-user"></i> PIC: ${item.pic_username}
                                        </span>
                                        ${item.assigned_users_list ? `<span class="badge-meta ml-1"><i class="fas fa-users"></i> Dilibatkan: ${item.assigned_users_list}</span>` : ''}
                                    </div>
                                    ${lastUpdateHtml}
                                </div>
                            </div>
                        </div>
                        <div class="text-right d-flex flex-column align-items-end justify-content-between" style="min-height: 80px;">
                            <div>
                                <span class="text-xs font-weight-bold text-muted d-block mb-1 text-right">STATUS</span>
                                ${getStatusBadgeHtml(item.status)}
                            </div>
                            <div class="mt-2 text-muted">
                                <i class="feather icon-chevron-right font-medium-3"></i>
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
            $('.flatpickr').flatpickr({ dateFormat: 'Y-m-d' });
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
        if (typeof removeModalListener === 'function') {
            removeModalListener('#modal_tiket_masalah');
        }
        $('#form_buat_tiket').addClass('d-none');
        $('#view_list_tiket').removeClass('d-none');
        window.currentEditTiketId = null; // Clear edit ID on cancel
    });

    function initAssignedUsersSelect2() {
        if ($('#tm_assigned_users').hasClass('select2-hidden-accessible')) {
            return;
        }
        $.ajax({
            url: base_url + 'api/tiket-masalah/users',
            type: 'GET',
            success: function(res) {
                if(res.success) {
                    let opts = '';
                    res.data.forEach(function(u) {
                        opts += `<option value="${u.id}">${u.name} (${u.username})</option>`;
                    });
                    $('#tm_assigned_users').html(opts).select2({
                        placeholder: "Pilih atau cari user...",
                        allowClear: true,
                        dropdownParent: $('#modal_tiket_masalah')
                    });
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

        let submitBtn = $(this).find('button[type="submit"]');
        let draftBtn = $('#btn_simpan_draft');
        submitBtn.prop('disabled', true);
        draftBtn.prop('disabled', true);
        
        let originalSubmitText = submitBtn.html();
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

            const processSubmitTiket = (fd) => {
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
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal membuat tiket', 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalSubmitText);
                        draftBtn.prop('disabled', false).html('Save as Draft');
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
                            submitBtn.prop('disabled', false).html('Simpan Tiket');
                            Swal.fire('Error', res.message || 'Gagal menyimpan area', 'error');
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html('Simpan Tiket');
                        let msg = xhr.responseJSON?.message || 'Gagal menghubungi server untuk menyimpan area';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            } else {
                processSubmitTiket(formData);
            }

        } catch (error) {
            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            submitBtn.prop('disabled', false).html('Simpan Tiket');
        }
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
                    renderTiketDetail(res.data);
                    loadTiketProgress(id);
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

        let isClosed = data.status === 'batal' || data.status === 'selesai';
        let actionBtnHtml = '';
        if (!isClosed) {
            actionBtnHtml = `
                <button class="btn btn-primary btn-block font-weight-bold py-2 mt-3 shadow-sm rounded-12" id="btn_toggle_add_progress">
                    <i class="feather icon-plus-circle mr-1"></i> Tambah Progres Laporan
                </button>
            `;
        } else {
            actionBtnHtml = `
                <div class="alert alert-secondary text-center text-xs mt-3 mb-0">
                    <i class="feather icon-lock mr-1"></i> Tiket sudah ${data.status.toUpperCase()} (Terkunci)
                </div>
            `;
        }

        let isCreator = (window.current_user_id == data.pic_user_id);
        let isSupervisor = (typeof window.is_supervisor_manager !== 'undefined' && window.is_supervisor_manager);
        let editBtnHtml = '';
        if (data.status === 'draft' && (isCreator || isSupervisor)) {
            let labelEdit = isCreator ? "Edit Draft" : "Edit & Ambil Alih Draft";
            editBtnHtml = `
                <button class="btn btn-warning btn-block font-weight-bold py-2 mt-2 shadow-sm rounded-12" id="btn_edit_draft_tiket" data-id="${data.id}">
                    <i class="feather icon-edit mr-1"></i> ${labelEdit}
                </button>
            `;
        }

        let lokasiText = currentRefData ? `${currentRefData.nama_cluster} - ${currentRefData.nama_jalan}` : '-';

        let html = `
            <div class="row">
                <!-- SIDEBAR DETAIL TIKET -->
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="tm-sidebar-card shadow-sm">
                        <div class="d-flex gap-2 mb-3">
                            ${getPriorityBadgeHtml(data.prioritas)}
                            <div class="ml-1">${getStatusBadgeHtml(data.status)}</div>
                        </div>

                        <h5 class="tm-detail-title mb-4">${data.keterangan}</h5>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="tm-detail-label mb-1">PENANGGUNG JAWAB</div>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-2" style="width:24px; height:24px; font-size:10px;">${data.pic_username.charAt(0).toUpperCase()}</div>
                                    <span class="tm-detail-val text-truncate">${data.pic_username}</span>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="tm-detail-label mb-1">TANGGAL DIBUAT</div>
                                <div class="tm-detail-val">${formattedDate}</div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="tm-detail-label mb-1">LOKASI</div>
                                <div class="tm-detail-val text-muted font-weight-normal">${lokasiText}</div>
                            </div>

                            ${data.assigned_users && data.assigned_users.length > 0 ? `
                            <div class="col-12 mb-3">
                                <div class="tm-detail-label mb-1">USER YANG DILIBATKAN</div>
                                <div class="d-flex flex-wrap gap-2">
                                    ${data.assigned_users.map(u => `<span class="badge badge-light-primary"><i class="feather icon-user mr-1"></i>${u.username}</span>`).join('')}
                                </div>
                            </div>
                            ` : ''}
                        </div>

                        <hr class="my-3">

                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="tm-detail-label">LAMPIRAN VISUAL</span>
                                <span class="text-xs text-primary font-weight-bold">${data.foto ? data.foto.length : 0} Foto</span>
                            </div>
                            ${photos}
                        </div>

                        ${actionBtnHtml}
                        ${editBtnHtml}
                    </div>
                </div>

                <!-- HISTORY TIMELINE KANAN -->
                <div class="col-md-8">
                    <div class="bg-white p-3 rounded-12 border shadow-sm h-100">
                        <div class="d-flex align-items-center mb-4">
                            <div style="width: 4px; height: 20px; background: #2057a3; border-radius: 2px;" class="mr-2"></div>
                            <h5 class="mb-0 font-weight-bold text-dark">History Progress</h5>
                        </div>

                        <!-- Form Progress (Hidden by default, toggled via button) -->
                        <div id="tm_form_progress_container" class="mb-4 d-none"></div>

                        <!-- Timeline Items -->
                        <div id="tm_progress_list">
                            <!-- Injected via JS -->
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#tm_detail_content').html(html);

        // Setup Form Progress logic
        if(!isClosed) {
            setupProgressForm(data);
            $('#btn_toggle_add_progress').click(function() {
                $('#tm_form_progress_container').toggleClass('d-none');
            });
        }
        
        // Setup Edit Draft logic
        $('#btn_edit_draft_tiket').click(function() {
            window.editTiketDraft(data);
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
            <div class="card bg-light border-primary mb-3">
                <div class="card-body p-3">
                    <h6 class="font-weight-bold text-primary mb-2">
                        <i class="feather icon-edit-3 mr-1"></i> Tambah Catatan Progress
                    </h6>
                    <form id="form_add_progress">
                        <input type="hidden" name="id_tiket_masalah" value="${tiket.id}">
                        <div class="form-group mb-2">
                            <textarea name="keterangan" id="progress_keterangan" class="form-control richtext" rows="3" placeholder="Tuliskan perkembangan perbaikan masalah..." required></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <label class="tm-detail-label">Foto Progress (Opsional)</label>

                            <div class="drag-drop-zone p-2 mb-1" id="tm_progress_dropzone">
                                <p class="mb-0 text-xs font-weight-bold text-muted">Tarik & Lepas Foto di sini, atau Paste (Ctrl + V)</p>
                                <div class="mt-1">
                                    <button type="button" class="btn btn-xs btn-outline-primary mr-1" onclick="$('#foto_progress').click()"><i class="fas fa-folder-open mr-1"></i> Pilih File</button>
                                    <button type="button" class="btn btn-xs btn-outline-info" onclick="$('#foto_progress_camera').click()"><i class="fas fa-camera mr-1"></i> Kamera</button>
                                </div>
                            </div>

                            <input type="file" id="foto_progress" class="d-none" multiple accept="image/*">
                            <input type="file" id="foto_progress_camera" class="d-none" accept="image/*" capture="environment">
                            <div id="tm_progress_preview_container" class="upload-preview-container"></div>
                        </div>
                        ${statusOptions}
                        <div class="d-flex flex-column flex-md-row justify-content-end mt-3 gap-2">
                            <button type="button" class="btn btn-light border mb-2 mb-md-0 order-2 order-md-1" onclick="$('#tm_form_progress_container').addClass('d-none')">Batal</button>
                            <button type="submit" class="btn btn-primary px-3 order-1 order-md-2">Simpan Progress</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        $('#tm_form_progress_container').html(formHtml);

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
        });
    }

    function loadTiketProgress(id) {
        $.ajax({
            url: base_url + 'api/tiket-masalah/progress',
            type: 'POST',
            data: { id_tiket_masalah: id },
            success: function(res) {
                if(res.success) {
                    let html = '';
                    if(res.data.length === 0) {
                        html = '<div class="text-muted text-center p-4">Belum ada riwayat progress.</div>';
                    } else {
                        html = '<div class="tm-timeline">';
                        res.data.forEach(function(p) {
                            let photos = '';
                            if(p.foto_urls && p.foto_urls.length > 0) {
                                photos = '<div class="d-flex flex-wrap gap-2 mt-2">';
                                let allUrlsStr = encodeURIComponent(JSON.stringify(p.foto_urls));
                                p.foto_urls.forEach((url, index) => {
                                    photos += `<a href="javascript:void(0)" onclick="window.openLightbox('${allUrlsStr}', ${index})"><img src="${url}" class="img-thumb-grid"></a>`;
                                });
                                photos += '</div>';
                            }

                            let statusBadgeHeader = '';
                            let statusChange = '';
                            if(p.status_sesudah && p.status_sesudah !== p.status_sebelum) {
                                statusBadgeHeader = `<span class="badge badge-light-secondary text-uppercase text-xs font-weight-bold ml-2">STATUS UPDATED</span>`;
                                statusChange = `
                                    <div class="tm-status-change-pill">
                                        <i class="feather icon-check-circle"></i> Ubah status: <strong>${p.status_sebelum}</strong> &rarr; <strong>${p.status_sesudah}</strong>
                                    </div>
                                `;
                            }
                            let tglProgress = new Date(p.created_at);
                            let formattedDate = tglProgress.toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'});

                            html += `
                            <div class="tm-timeline-item">
                                <div class="tm-timeline-dot"></div>
                                <div class="tm-timeline-header">
                                    <div>
                                        <span class="tm-timeline-user">${p.user_username}</span>
                                        <span class="text-muted mx-1">•</span>
                                        <span class="tm-timeline-time">${formattedDate}</span>
                                    </div>
                                    <div>${statusBadgeHeader}</div>
                                </div>
                                <div class="tm-timeline-card">
                                    <p class="mb-0 text-dark" style="font-size: 0.9rem; line-height: 1.5;">${p.keterangan}</p>
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
