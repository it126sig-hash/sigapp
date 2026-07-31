$(document).ready(function() {
    // Inisialisasi elemen
    const modalMasalah = $('#modal_tiket_masalah');
    let currentRefType = '';
    let currentRefId = 0;
    let currentRefData = null;
    
    // --- BUKA MODAL ---
    window.openTiketMasalahAction = function() {
        if (typeof editdtt !== 'undefined' && editdtt.length > 0) {
            let sh = editdtt[0];
            let id = sh.id.replace(/\D/g, ''); // Extract numeric ID
            let tipe = sh.data && sh.data.tipe ? sh.data.tipe : 'kavling';
            let refType = (tipe === 'kavling') ? 'kavling' : 'others';
            window.openTiketMasalah(refType, id);
        } else {
            Swal.fire('Error', 'Silakan pilih objek di siteplan terlebih dahulu', 'error');
        }
    };

    window.openTiketMasalah = function(refType, refId) {
        currentRefType = refType;
        currentRefId = refId;
        
        // Reset view
        $('#list_tiket_masalah').html('<div class="text-center p-4"><span class="spinner-border text-primary"></span></div>');
        $('#view_list_tiket').removeClass('d-none');
        $('#view_detail_tiket, #form_buat_tiket').addClass('d-none');
        
        // Tampilkan modal
        modalMasalah.modal('show');
        
        // Load info header
        $.ajax({
            url: base_url + 'api/tiket-masalah/ref-info',
            type: 'POST',
            data: { ref_type: refType, ref_id: refId },
            success: function(res) {
                if(res.success) {
                    currentRefData = res.data;
                    let info = res.data;
                    let title = `${info.nama_proyek} > ${info.nama_cluster} > ${info.nama_jalan}`;
                    if(refType === 'kavling') {
                        title += ` > Kavling ${info.no_kavling}`;
                    } else {
                        title += ` > ${info.no_kavling}`;
                    }
                    
                    let progress = parseInt(info.progres_bangunan) || 0;
                    $('#tm_ref_tag_text').text(refType === 'kavling' ? 'KAVLING' : info.tipe.toUpperCase());
                    $('#tm_ref_title').text(title);
                    $('#tm_hero_progress_text').text(progress + '%');
                    $('#tm_hero_progress_bar').css('width', progress + '%');
                }
            }
        });
        
        loadTiketList();
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
                                            <i class="feather icon-image"></i> ${item.foto_count} Foto
                                        </span>
                                        <span class="badge-meta ml-1">
                                            <i class="feather icon-user"></i> PIC: ${item.pic_username}
                                        </span>
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

    // --- FORM BUAT TIKET ---
    $('#btn_show_buat_tiket').click(function() {
        $('#view_list_tiket').addClass('d-none');
        $('#form_buat_tiket').removeClass('d-none');
        
        // Reset form
        $('#form_buat_tiket_form')[0].reset();
        $('#tm_assigned_users').val(null).trigger('change');
        
        // Load users for select2 if empty
        if($('#tm_assigned_users option').length === 0) {
            $.ajax({
                url: base_url + 'api/tiket-masalah/users',
                type: 'GET',
                success: function(res) {
                    if(res.success) {
                        let opts = '';
                        res.data.forEach(function(u) {
                            opts += `<option value="${u.id}">${u.name} (${u.username})</option>`;
                        });
                        $('#tm_assigned_users').html(opts);
                    }
                }
            });
        }
    });

    $('#btn_batal_buat_tiket').click(function() {
        $('#form_buat_tiket').addClass('d-none');
        $('#view_list_tiket').removeClass('d-none');
    });

    $('#form_buat_tiket_form').submit(async function(e) {
        e.preventDefault();
        
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
        
        try {
            let formData = new FormData(this);
            formData.append('ref_type', currentRefType);
            formData.append('ref_id', currentRefId);
            formData.append('id_proyek', getSelectedProyekId());
            
            // Handle file compression
            let fileInput = document.getElementById('tm_foto');
            formData.delete('foto[]');
            if(fileInput.files.length > 0) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    let file = fileInput.files[i];
                    try {
                        let compressedFile = await compressImage(file);
                        formData.append('foto[]', compressedFile, compressedFile.name);
                    } catch (err) {
                        formData.append('foto[]', file, file.name);
                    }
                }
            }

            $.ajax({
                url: base_url + 'api/tiket-masalah/store',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    $('#form_buat_tiket').addClass('d-none');
                    $('#view_list_tiket').removeClass('d-none');
                    loadTiketList();
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Gagal membuat tiket', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('Simpan Tiket');
                }
            });
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
            data.foto.forEach(f => {
                photos += `<a href="${f.url}" target="_blank"><img src="${f.url}" class="img-thumb-grid"></a>`;
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
                    <i class="feather icon-plus-circle mr-1"></i> Tambah Log Perbaikan
                </button>
            `;
        } else {
            actionBtnHtml = `
                <div class="alert alert-secondary text-center text-xs mt-3 mb-0">
                    <i class="feather icon-lock mr-1"></i> Tiket sudah ${data.status.toUpperCase()} (Terkunci)
                </div>
            `;
        }

        let lokasiText = currentRefData ? `${currentRefData.nama_cluster} - ${currentRefData.nama_jalan}` : '-';

        let html = `
            <div class="row">
                <!-- SIDEBAR KIRI (Gambar 2 Layout) -->
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="tm-sidebar-card shadow-sm">
                        <div class="d-flex gap-2 mb-3">
                            ${getPriorityBadgeHtml(data.prioritas)}
                            <div class="ml-1">${getStatusBadgeHtml(data.status)}</div>
                        </div>

                        <h5 class="tm-detail-title mb-4">${data.keterangan}</h5>

                        <div class="mb-3">
                            <div class="tm-detail-label mb-1">PENANGGUNG JAWAB</div>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle mr-2">${data.pic_username.charAt(0).toUpperCase()}</div>
                                <span class="tm-detail-val">${data.pic_username}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="tm-detail-label mb-1">TANGGAL DIBUAT</div>
                            <div class="tm-detail-val">${formattedDate}</div>
                        </div>

                        <div class="mb-4">
                            <div class="tm-detail-label mb-1">LOKASI</div>
                            <div class="tm-detail-val text-muted font-weight-normal">${lokasiText}</div>
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
                    </div>
                </div>

                <!-- HISTORY TIMELINE KANAN (Gambar 2 Layout) -->
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
                if(!$('#tm_form_progress_container').hasClass('d-none')) {
                    $('html, body, #modal_tiket_masalah .modal-body').animate({
                        scrollTop: $('#tm_form_progress_container').offset().top - 100
                    }, 300);
                }
            });
        }
    }

    function setupProgressForm(tiket) {
        let activeUserId = (typeof current_user_id !== 'undefined') ? current_user_id : (window.current_user_id || 0);
        let isPic = tiket.pic_user_id == activeUserId;
        
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
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Tuliskan perkembangan perbaikan masalah..." required></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <label class="tm-detail-label">Foto Progress (Opsional)</label>
                            <input type="file" name="foto_progress[]" id="foto_progress" class="form-control-file" multiple accept="image/*">
                        </div>
                        ${statusOptions}
                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-sm btn-light border mr-2" onclick="$('#tm_form_progress_container').addClass('d-none')">Batal</button>
                            <button type="submit" class="btn btn-sm btn-primary px-3">Simpan Progress</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        $('#tm_form_progress_container').html(formHtml);

        $('#form_add_progress').submit(async function(e) {
            e.preventDefault();
            let submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
            
            try {
                let formData = new FormData(this);
                
                // Handle compression
                let fileInput = document.getElementById('foto_progress');
                formData.delete('foto_progress[]');
                if(fileInput.files.length > 0) {
                    for (let i = 0; i < fileInput.files.length; i++) {
                        let file = fileInput.files[i];
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
                                p.foto_urls.forEach(url => {
                                    photos += `<a href="${url}" target="_blank"><img src="${url}" class="img-thumb-grid"></a>`;
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
        
        return `<span class="badge-prio ${cls}">${prio.toUpperCase()}</span>`;
    }

    function getStatusBadgeHtml(status) {
        let cls = 'badge-status-dibuat';
        let label = status.replace('_', ' ').toUpperCase();
        
        if (status === 'selesai') cls = 'badge-status-selesai';
        else if (status === 'dalam_proses') cls = 'badge-status-proses';
        else if (status === 'hold') cls = 'badge-status-hold';
        else if (status === 'batal') cls = 'badge-status-batal';
        
        return `<span class="badge-status-pill ${cls}"><span class="dot"></span> ${label}</span>`;
    }

    function getSelectedProyekId() {
        return $('#f_id_proyek').val() || 1;
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
});
