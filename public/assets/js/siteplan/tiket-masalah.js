$(document).ready(function() {
    // Inisialisasi elemen
    const modalMasalah = $('#modal_tiket_masalah');
    let currentRefType = '';
    let currentRefId = 0;
    
    // --- BUKA MODAL ---
    window.openTiketMasalahAction = function() {
        if (typeof editdtt !== 'undefined' && editdtt.length > 0) {
            let sh = editdtt[0];
            let id = sh.id.substr(3); // remove 'kav' prefix
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
        $('#list_tiket_masalah').html('<div class="text-center p-3"><span class="spinner-border text-primary"></span></div>');
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
                    let info = res.data;
                    let title = `${info.nama_proyek} > ${info.nama_cluster} > ${info.nama_jalan}`;
                    if(refType === 'kavling') {
                        title += ` > Kavling ${info.no_kavling}`;
                    } else {
                        title += ` > ${info.no_kavling}`;
                    }
                    $('#tm_ref_title').text(title);
                    $('#tm_ref_tag').text(refType === 'kavling' ? 'Kavling' : info.tipe.toUpperCase());
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
                $('#list_tiket_masalah').html('<div class="alert alert-danger">Gagal memuat data</div>');
            }
        });
    }

    function renderTiketList(data) {
        let html = '';
        if(data.length === 0) {
            html = '<div class="alert alert-info">Belum ada tiket masalah untuk item ini.</div>';
        } else {
            data.forEach(function(item) {
                let badgeClass = getPriorityBadgeClass(item.prioritas);
                let statusBadge = getStatusBadgeClass(item.status);
                let tgl = new Date(item.created_at);
                let formattedDate = tgl.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'});
                
                html += `
                <div class="card tm-card mb-2 cursor-pointer" onclick="loadTiketDetail(${item.id})">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge ${badgeClass}">${item.prioritas.toUpperCase()}</span>
                            <small class="text-muted">${formattedDate}</small>
                        </div>
                        <p class="mb-1 text-truncate">${item.keterangan}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small>
                                <i class="feather icon-image"></i> ${item.foto_count} foto | PIC: <b>${item.pic_username}</b>
                            </small>
                            <span class="badge ${statusBadge}">${item.status.toUpperCase().replace('_', ' ')}</span>
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
            formData.append('id_proyek', getSelectedProyekId()); // pastikan id proyek diambil dengan benar dari filter atau refInfo
            
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
                        formData.append('foto[]', file, file.name); // fallback ke original jika gagal kompres
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
                    submitBtn.prop('disabled', false).html('Simpan');
                }
            });
        } catch (error) {
            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            submitBtn.prop('disabled', false).html('Simpan');
        }
    });

    // --- DETAIL & PROGRESS ---
    window.loadTiketDetail = function(id) {
        $('#view_list_tiket').addClass('d-none');
        $('#view_detail_tiket').removeClass('d-none');
        $('#tm_detail_content').html('<div class="text-center p-3"><span class="spinner-border text-primary"></span></div>');
        
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
        let badgeClass = getPriorityBadgeClass(data.prioritas);
        let statusBadge = getStatusBadgeClass(data.status);
        
        let assigned = data.assigned_users.map(u => u.username).join(', ');
        if(!assigned) assigned = '-';

        let photos = '';
        if(data.foto && data.foto.length > 0) {
            photos = '<div class="d-flex flex-wrap gap-2 mt-2">';
            data.foto.forEach(f => {
                photos += `<a href="${f.url}" target="_blank"><img src="${f.url}" class="img-thumbnail" style="height: 100px; object-fit: cover;"></a>`;
            });
            photos += '</div>';
        }
        
        let tglDetail = new Date(data.created_at);
        let html = `
            <div class="mb-3">
                <h5>${data.keterangan}</h5>
                <div class="row text-sm">
                    <div class="col-6">
                        <div>Prioritas: <span class="badge ${badgeClass}">${data.prioritas.toUpperCase()}</span></div>
                        <div class="mt-1">Status: <span class="badge ${statusBadge}">${data.status.toUpperCase().replace('_', ' ')}</span></div>
                    </div>
                    <div class="col-6 text-right">
                        <div>PIC: <b>${data.pic_username}</b></div>
                        <div>Tgl: ${tglDetail.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'})}</div>
                    </div>
                </div>
                <div class="mt-2 text-sm text-muted">User dilibatkan: ${assigned}</div>
                ${photos}
            </div>
            
            <div class="divider divider-left my-3">
                <div class="divider-text font-weight-bold">History Progress</div>
            </div>
            <div id="tm_progress_list"></div>
            
            <div id="tm_form_progress_container" class="mt-3"></div>
        `;
        
        $('#tm_detail_content').html(html);

        // Setup Form Progress
        if(data.status !== 'batal' && data.status !== 'selesai') {
            setupProgressForm(data);
        }
    }

    function setupProgressForm(tiket) {
        let activeUserId = (typeof current_user_id !== 'undefined') ? current_user_id : (window.current_user_id || 0);
        let isPic = tiket.pic_user_id == activeUserId;
        
        let statusOptions = '';
        if(isPic) {
            statusOptions = `
                <div class="form-group mb-2">
                    <label>Update Status (Opsional)</label>
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
            <div class="card bg-light">
                <div class="card-body p-2">
                    <h6 class="mb-2">Tambah Progress</h6>
                    <form id="form_add_progress">
                        <input type="hidden" name="id_tiket_masalah" value="${tiket.id}">
                        <div class="form-group mb-2">
                            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Catatan progress..." required></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <label>Foto (Opsional)</label>
                            <input type="file" name="foto_progress[]" id="foto_progress" class="form-control-file form-control-sm" multiple accept="image/*">
                        </div>
                        ${statusOptions}
                        <button type="submit" class="btn btn-primary btn-sm btn-block">Simpan Progress</button>
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
                            loadTiketDetail(tiket.id);
                        } else {
                            Swal.fire('Error', res.message, 'error');
                            submitBtn.prop('disabled', false).html('Simpan Progress');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyimpan', 'error');
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
                        html = '<p class="text-muted text-center text-sm">Belum ada progress.</p>';
                    } else {
                        html = '<div class="timeline">';
                        res.data.forEach(function(p) {
                            let photos = '';
                            if(p.foto_urls && p.foto_urls.length > 0) {
                                photos = '<div class="d-flex flex-wrap gap-1 mt-1">';
                                p.foto_urls.forEach(url => {
                                    photos += `<a href="${url}" target="_blank"><img src="${url}" class="img-thumbnail" style="height: 60px; object-fit: cover;"></a>`;
                                });
                                photos += '</div>';
                            }

                            let statusChange = '';
                            if(p.status_sesudah && p.status_sesudah !== p.status_sebelum) {
                                statusChange = `<div class="badge badge-light-primary text-xs mt-1">Ubah status: ${p.status_sebelum} &rarr; ${p.status_sesudah}</div>`;
                            }
                            let tglProgress = new Date(p.created_at);

                            html += `
                            <div class="timeline-item pb-2 border-bottom mb-2">
                                <div class="d-flex justify-content-between">
                                    <small class="font-weight-bold">${p.user_username}</small>
                                    <small class="text-muted">${tglProgress.toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'})}</small>
                                </div>
                                <div class="text-sm mt-1">${p.keterangan}</div>
                                ${statusChange}
                                ${photos}
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

    // --- UTILS ---
    function getPriorityBadgeClass(prio) {
        switch(prio) {
            case 'urgent': return 'badge-danger';
            case 'medium': return 'badge-warning';
            case 'normal': return 'badge-info';
            case 'low': return 'badge-secondary';
            default: return 'badge-secondary';
        }
    }

    function getStatusBadgeClass(status) {
        switch(status) {
            case 'dibuat': return 'badge-secondary';
            case 'dalam_proses': return 'badge-primary';
            case 'selesai': return 'badge-success';
            case 'hold': return 'badge-warning';
            case 'batal': return 'badge-dark';
            default: return 'badge-secondary';
        }
    }

    function getSelectedProyekId() {
        return $('#f_id_proyek').val() || 1; // Fallback jika tidak ditemukan
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
